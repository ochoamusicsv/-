<?php

/**
 * Enrutador con soporte para parámetros dinámicos ({id}), middlewares y
 * detección automática del subdirectorio de instalación (ej. /DECEMWEB).
 */
class Router
{
    private array $rutas = [];
    private string $base = '';

    public function __construct()
    {
        // SCRIPT_NAME tras el rewrite: /DECEMWEB/public/index.php
        // Base de la app: /DECEMWEB
        $dir = str_replace('\\', '/', dirname($_SERVER['SCRIPT_NAME'] ?? ''));
        $dir = preg_replace('#/public$#', '', $dir);

        $this->base = ($dir === '/' || $dir === '.') ? '' : rtrim($dir, '/');
    }

    public function base(): string
    {
        return $this->base;
    }

    public function get(string $ruta, array $accion, array $middleware = []): void
    {
        $this->agregar('GET', $ruta, $accion, $middleware);
    }

    public function post(string $ruta, array $accion, array $middleware = []): void
    {
        $this->agregar('POST', $ruta, $accion, $middleware);
    }

    public function put(string $ruta, array $accion, array $middleware = []): void
    {
        $this->agregar('PUT', $ruta, $accion, $middleware);
    }

    public function delete(string $ruta, array $accion, array $middleware = []): void
    {
        $this->agregar('DELETE', $ruta, $accion, $middleware);
    }

    private function agregar(string $metodo, string $ruta, array $accion, array $middleware): void
    {
        $this->rutas[$metodo][] = [
            'patron'     => $this->compilar($ruta),
            'accion'     => $accion,
            'middleware' => $middleware,
        ];
    }

    /**
     * Convierte "/usuarios/{id}" en una expresión regular con grupos nombrados.
     */
    private function compilar(string $ruta): string
    {
        $ruta = '/' . trim($ruta, '/');
        $ruta = ($ruta === '/') ? '/' : rtrim($ruta, '/');

        $patron = preg_replace('#\{([a-zA-Z_][a-zA-Z0-9_]*)\}#', '(?P<$1>[^/]+)', $ruta);

        return '#^' . $patron . '$#';
    }

    public function dispatch(): void
    {
        $metodo = Request::metodo();
        $uri    = $this->normalizarUri();

        foreach ($this->rutas[$metodo] ?? [] as $ruta) {
            if (preg_match($ruta['patron'], $uri, $coincidencias)) {
                $parametros = array_filter(
                    $coincidencias,
                    'is_string',
                    ARRAY_FILTER_USE_KEY
                );

                $this->ejecutarMiddleware($ruta['middleware']);
                $this->ejecutar($ruta['accion'], array_values($parametros));
                return;
            }
        }

        $this->noEncontrado();
    }

    private function normalizarUri(): string
    {
        $uri = Request::uri();

        if ($this->base !== '' && str_starts_with($uri, $this->base)) {
            $uri = substr($uri, strlen($this->base));
        }

        $uri = '/' . trim($uri, '/');

        return $uri === '//' ? '/' : $uri;
    }

    private function ejecutarMiddleware(array $middleware): void
    {
        foreach ($middleware as $clase) {
            (new $clase())->manejar();
        }
    }

    private function ejecutar(array $accion, array $parametros): void
    {
        [$controlador, $metodo] = $accion;

        $obj = new $controlador();

        if (!method_exists($obj, $metodo)) {
            http_response_code(500);
            die("El método {$metodo} no existe en {$controlador}.");
        }

        $obj->{$metodo}(...$parametros);
    }

    private function noEncontrado(): void
    {
        http_response_code(404);

        $vista = ROOT_PATH . '/app/Views/errors/404.php';
        $layout = ROOT_PATH . '/app/Views/layouts/auth.php';

        if (is_file($vista) && is_file($layout)) {
            ob_start();
            require $vista;
            $contenido = ob_get_clean();
            require $layout;
            return;
        }

        echo '404 - Página no encontrada';
    }
}
