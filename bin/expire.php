<?php

declare(strict_types=1);

/*
 * Marca como vencidas las membresías cuyo período terminó.
 * Programalo una vez por día, por ejemplo:
 *   5 0 * * * php /ruta/al/proyecto/bin/expire.php
 */

require dirname(__DIR__) . '/app/bootstrap.php';

$count = App\Services\MembershipService::expireOverdue();
echo "Membresías vencidas: {$count}\n";
