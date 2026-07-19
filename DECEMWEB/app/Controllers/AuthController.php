<?php

/**
 * Controlador de autenticación: mostrar login, procesar login y cerrar sesión.
 */
class AuthController extends Controller
{
    private UserModel $usuarios;

    public function __construct()
    {
        $this->usuarios = new UserModel();
    }

    /** Muestra el formulario de inicio de sesión. */
    public function mostrarLogin(): void
    {
        $this->view('auth/login', [
            'titulo' => 'Iniciar sesión',
        ], 'auth');
    }

    /** Procesa el envío del formulario de inicio de sesión. */
    public function login(): void
    {
        if (!Csrf::validar(Request::post('_token'))) {
            Session::flash('danger', 'Sesión expirada. Intenta nuevamente.');
            $this->redirect('/login');
        }

        $usuario = Request::post('usuario', '');
        $clave   = Request::post('clave', '');

        $validador = new Validator(['usuario' => $usuario, 'clave' => $clave]);

        if (!$validador->validar(
            ['usuario' => 'required', 'clave' => 'required'],
            ['usuario' => 'Usuario', 'clave' => 'Contraseña']
        )) {
            foreach ($validador->mensajes() as $mensaje) {
                Session::flash('danger', $mensaje);
            }
            $_SESSION['_old']['usuario'] = $usuario;
            $this->redirect('/login');
        }

        $registro = $this->usuarios->autenticar($usuario, $clave);

        if ($registro === null) {
            Session::flash('danger', 'Usuario o contraseña incorrectos.');
            $_SESSION['_old']['usuario'] = $usuario;
            $this->redirect('/login');
        }

        Auth::login($registro);
        unset($_SESSION['_old']);

        Session::flash('success', 'Bienvenido, ' . ($registro['nombre'] ?: $registro['usuario']) . '.');
        $this->redirect('/dashboard');
    }

    /** Cierra la sesión del usuario. */
    public function logout(): void
    {
        Auth::logout();
        Session::flash('success', 'Has cerrado sesión correctamente.');
        $this->redirect('/login');
    }
}
