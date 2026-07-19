<?php

/**
 * Controlador base: renderizado de vistas con layout, redirecciones y JSON.
 */
abstract class Controller
{
    /**
     * Renderiza una vista dentro de un layout.
     *
     * @param string $vista  Ruta relativa dentro de app/Views (sin .php).
     * @param array  $datos  Variables disponibles en la vista.
     * @param string $layout Layout a utilizar (dentro de Views/layouts).
     */
    protected function view(string $vista, array $datos = [], string $layout = 'app'): void
    {
        $archivo = ROOT_PATH . '/app/Views/' . $vista . '.php';

        if (!is_file($archivo)) {
            http_response_code(500);
            die('No existe la vista: ' . htmlspecialchars($vista, ENT_QUOTES));
        }

        extract($datos, EXTR_SKIP);

        // Capturar el contenido de la vista.
        ob_start();
        require $archivo;
        $contenido = ob_get_clean();

        $archivoLayout = ROOT_PATH . '/app/Views/layouts/' . $layout . '.php';

        if (!is_file($archivoLayout)) {
            echo $contenido;
            return;
        }

        require $archivoLayout;
    }

    /**
     * Renderiza una vista sin layout (por ejemplo para fragmentos).
     */
    protected function partial(string $vista, array $datos = []): void
    {
        $this->view($vista, $datos, '');
    }

    protected function redirect(string $ruta): void
    {
        header('Location: ' . url($ruta));
        exit;
    }

    protected function json($datos, int $codigo = 200): void
    {
        http_response_code($codigo);
        header('Content-Type: application/json; charset=utf-8');
        echo json_encode($datos, JSON_UNESCAPED_UNICODE);
        exit;
    }

    protected function abort(int $codigo = 404): void
    {
        http_response_code($codigo);
        $this->view('errors/' . $codigo, [], 'auth');
        exit;
    }
}
