<?php

declare(strict_types=1);

namespace App\Core;

final class Router
{
    /** @var list<array{method:string, pattern:string, regex:string, handler:array, middleware:list<string>}> */
    private array $routes = [];
    private string $prefix = '';
    /** @var list<string> */
    private array $groupMiddleware = [];

    public function get(string $path, array $handler, array $middleware = []): void
    {
        $this->add('GET', $path, $handler, $middleware);
    }

    public function post(string $path, array $handler, array $middleware = []): void
    {
        $this->add('POST', $path, $handler, $middleware);
    }

    /** @param array{prefix?:string, middleware?:list<string>} $attributes */
    public function group(array $attributes, callable $routes): void
    {
        [$prevPrefix, $prevMiddleware] = [$this->prefix, $this->groupMiddleware];
        $this->prefix .= $attributes['prefix'] ?? '';
        $this->groupMiddleware = [...$this->groupMiddleware, ...($attributes['middleware'] ?? [])];
        $routes($this);
        [$this->prefix, $this->groupMiddleware] = [$prevPrefix, $prevMiddleware];
    }

    private function add(string $method, string $path, array $handler, array $middleware): void
    {
        $pattern = $this->prefix . $path;
        $pattern = $pattern === '/' ? '/' : rtrim($pattern, '/');
        if ($pattern === '') {
            $pattern = '/';
        }
        // "/contenidos/{section}" → "/contenidos/(?P<section>[^/]+)"
        $regex = preg_replace_callback(
            '#\{([a-z_]+)\}|[^{]+#',
            static fn (array $m): string => ($m[1] ?? '') !== ''
                ? '(?P<' . $m[1] . '>[^/]+)'
                : preg_quote($m[0], '#'),
            $pattern,
        );

        $this->routes[] = [
            'method'     => $method,
            'pattern'    => $pattern,
            'regex'      => '#^' . $regex . '$#',
            'handler'    => $handler,
            'middleware' => [...$this->groupMiddleware, ...$middleware],
        ];
    }

    public function dispatch(Request $request): void
    {
        $allowed = false;
        foreach ($this->routes as $route) {
            if (!preg_match($route['regex'], $request->path, $matches)) {
                continue;
            }
            if ($route['method'] !== $request->method) {
                $allowed = true;
                continue;
            }

            $params = array_filter($matches, 'is_string', ARRAY_FILTER_USE_KEY);
            $middleware = $route['middleware'];
            if ($request->isPost() && !in_array('nocsrf', $middleware, true)) {
                $middleware[] = 'csrf';
            }
            foreach ($middleware as $name) {
                Middleware::handle($name, $request);
            }

            [$class, $method] = $route['handler'];
            (new $class($request))->{$method}(...$params);
            return;
        }

        throw new HttpException($allowed ? 405 : 404);
    }
}
