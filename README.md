# Comunidad en Tránsito

Sitio, área de miembros y panel de administración de Comunidad en Tránsito.
PHP 8.2+ sin framework, MVC propio, HTML/CSS/JS sin dependencias. Estilo “Acuarela y mar” (ver la guía de diseño de Mily).

## Cómo correrlo

```bash
cp .env.example .env
composer dump-autoload            # opcional: también funciona sin Composer
php bin/migrate.php --fresh --demo  # tablas + datos base + miembros de prueba
composer serve                    # http://localhost:8000
```

- Administración (con `--demo`): `admin@ejemplo.com` / `admin-de-prueba` en **/admin/ingresar**.
- Miembros de prueba: por ejemplo `juana-perez@ejemplo.com` / `miembro123` en /ingresar.
- En desarrollo se usa SQLite (`storage/database.sqlite`). Para MySQL: `DB_DRIVER=mysql` y los datos de conexión en `.env`, después `php bin/migrate.php --seed`.
- Sin credenciales de pago y con `APP_ENV=local`, la compra muestra un **pago simulado** (aprobar/rechazar) para probar todo el circuito.
- Los emails se guardan en `storage/logs/mail.log` mientras `MAIL_DRIVER=log`.

## Subir la base a Hostinger

`php bin/export-mysql.php database/export/comunidad_en_transito.sql` genera un `.sql` para importar en phpMyAdmin
(ver el encabezado del script para armar una base limpia sin miembros de prueba). La carpeta `database/export/` no se sube a git.
En el hosting, el `.env` lleva `DB_DRIVER=mysql` y `DB_HOST=localhost`.

### Deploy automático (Git en Hostinger)

- PHP 8.2 o más nuevo (hPanel → Avanzado → Configuración de PHP). Recomendado: 8.3.
- Hostinger publica la raíz del repositorio; el `.htaccess` de la raíz manda todo a `public/` y bloquea `.env`, `app/`, `config/`, `storage/`, etc.
- El `.env` no está en git: se crea a mano en el servidor (Administrador de archivos), junto a `composer.json`, con `APP_ENV=production`, `APP_DEBUG=false`, `APP_URL=https://tu-dominio`, los datos de la base y las credenciales de pago.
- `storage/logs/` tiene que poder escribirse.
- Cron diario: `php /home/USUARIO/domains/TU-DOMINIO/public_html/bin/expire.php`.

## Estructura

```
app/
  Core/          Router, Request, Controller, View, Database (PDO), Model, Auth, Session, Csrf, Validator
  Controllers/   Sitio público, contenidos, compra, miembros, webhooks
  Controllers/Admin/  Resumen, usuarios, membresías, pagos, calendario, planes, secciones, contenidos, mensajes
  Models/        User, Plan, Membership, Payment, Section, Entry, Rune, Book, ContactMessage, Subscriber
  Services/      AccessService (quién ve qué), MembershipService (altas, renovaciones, vencimientos), Mailer
  Services/Payments/  MercadoPagoGateway, PayPalGateway, SimulatedGateway
  Views/         layouts, partials, pages (A), content (B), checkout + auth (C), member (D), admin
config/          app, database, payments, site (textos, fotos, preguntas frecuentes)
routes/web.php   todas las rutas
database/        schema.sql (SQLite y MySQL) y seed.php
bin/             migrate.php, expire.php (cron diario)
public/          index.php (único punto de entrada) y assets
```

## Administración

- El panel tiene su **propio ingreso** en `/admin/ingresar` y su propia sesión: entrar o salir del sitio no afecta al panel, y al revés.
- Las cuentas de administración están en la tabla `admins` (no en `users` ni en el `.env`). Se agregan, editan y quitan desde **Administradores** en el panel; siempre queda al menos una.
- Primera cuenta: mientras no haya ninguna, `/admin/ingresar` ofrece crearla. También por consola: `php bin/create-admin.php` (sirve además para resetear una contraseña olvidada).
- Con sesión de administración se ve todo el contenido del sitio, para revisarlo como miembro.

## Cómo funciona el acceso

- Cada plan tiene un **nivel** (`orilla` o `inmersion`) y una duración en meses.
- Cada sección tiene la lista de niveles que la abren (admin → *Secciones y acceso*).
- Quien tiene una membresía activa de un nivel incluido ve todo; si no, ve solo la entrega marcada como **muestra** (“Así lo ve un miembro”) y las demás llevan a Membresías.

## Pagos

| | Mercado Pago | PayPal |
|---|---|---|
| Moneda | ARS | USD |
| Tipo | Suscripción (preapproval), renovación automática | Orden de pago por período |
| Baja | Desde Mi cuenta (cancela en MP) | No hay renovación |
| Webhook | `/webhooks/mercadopago` (firma `x-signature`) | `/webhooks/paypal` (verificación por API) |

Los webhooks y las URLs de retorno necesitan una URL pública con HTTPS (`APP_URL`). Para probar en local, usá un túnel (por ejemplo `cloudflared` o `ngrok`).

Programar una vez por día: `php bin/expire.php` (marca como vencidas las membresías terminadas).

## Diseño

- Las capturas de Figma están en `design/figma/` (no se suben a git). Las fotos del sitio salieron de ahí, en baja calidad: reemplazalas por los originales con el mismo nombre en `public/assets/img/fotos/` y `public/assets/img/libros/`.
- Textos y fotos de cada sección de Contenidos: `config/sections.php`. Textos generales, testimonios, preguntas y redes: `config/site.php`.
- Texto de las entregas: el admin tiene un **editor por bloques** (párrafo con negrita, cursiva y links; imagen; dos imágenes; cita; recuadro “para probar en casa”). Por dentro se guarda en un formato de texto simple que interpreta `rich_text()` en `app/helpers.php`; el botón “Editar como texto” lo muestra tal cual.

## Archivos que sube el admin

- Imágenes, audios, videos y PDF de las entregas se guardan en `public/uploads/AAAA/MM/` (no se suben a git). Se verifica el tipo real de cada archivo y la carpeta no ejecuta código.
- Límite por archivo: lo define PHP. `public/.user.ini` pide 512 MB; si Hostinger no lo toma, cambialo en hPanel → Avanzado → Configuración de PHP (`upload_max_filesize` y `post_max_size`). En local, `composer serve` ya arranca con 512 MB.
- Videos largos: conviene YouTube (no lista o privado) para no llenar el espacio del hosting.
- Ojo con el deploy automático: comprobá que no borre `public/uploads/` (subí una imagen de prueba, hacé un deploy y fijate que siga). Hacé backup de esa carpeta junto con la base.

## Pendiente

- Fotos originales en buena calidad (ver Diseño).
- Textos "a confirmar con Silvina": entregas, sinopsis de libros, consignas de las runas.
- Precios semestrales y en dólares (los mensuales y el anual son los de Figma).
- Link del grupo de WhatsApp de la Cafetería y video de "¿Qué es la terapia ocupacional?" (`config/site.php`).
- Recuperar contraseña por email (hoy el link lleva a WhatsApp) y proveedor de email en producción.
