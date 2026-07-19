<?php

/**
 * CRUD de usuarios del sistema.
 */
class UsersController extends Controller
{
    private UserModel $usuarios;

    public function __construct()
    {
        $this->usuarios = new UserModel();
    }

    public function index(): void
    {
        $this->view('usuarios/index', [
            'titulo'   => 'Usuarios',
            'usuarios' => $this->usuarios->todos('id DESC'),
        ]);
    }

    public function crear(): void
    {
        $this->view('usuarios/form', [
            'titulo'  => 'Nuevo usuario',
            'usuario' => null,
            'accion'  => url('/usuarios'),
        ]);
    }

    public function guardar(): void
    {
        $this->verificarCsrf('/usuarios/crear');

        $datos = $this->datosFormulario();

        $reglas = [
            'nombre'  => 'required|max:150',
            'usuario' => 'required|min:3|max:100',
            'clave'   => 'required|min:6|confirmed',
        ];

        $validador = new Validator($datos);

        if (!$validador->validar($reglas, $this->etiquetas())
            || $this->usuarios->usuarioExiste($datos['usuario'])) {
            if ($this->usuarios->usuarioExiste($datos['usuario'])) {
                Session::flash('danger', 'El nombre de usuario ya está en uso.');
            }
            foreach ($validador->mensajes() as $mensaje) {
                Session::flash('danger', $mensaje);
            }
            $this->redirect('/usuarios/crear');
        }

        $this->usuarios->registrar([
            'nombre'  => $datos['nombre'],
            'usuario' => $datos['usuario'],
            'clave'   => $datos['clave'],
            'activo'  => isset($_POST['activo']) ? 1 : 0,
        ]);

        Session::flash('success', 'Usuario creado correctamente.');
        $this->redirect('/usuarios');
    }

    public function editar(string $id): void
    {
        $usuario = $this->usuarios->encontrar((int) $id);

        if ($usuario === null) {
            $this->abort(404);
        }

        $this->view('usuarios/form', [
            'titulo'  => 'Editar usuario',
            'usuario' => $usuario,
            'accion'  => url('/usuarios/' . $usuario['id']),
        ]);
    }

    public function actualizar(string $id): void
    {
        $this->verificarCsrf('/usuarios/' . $id . '/editar');

        $usuario = $this->usuarios->encontrar((int) $id);

        if ($usuario === null) {
            $this->abort(404);
        }

        $datos  = $this->datosFormulario();
        $reglas = [
            'nombre'  => 'required|max:150',
            'usuario' => 'required|min:3|max:100',
        ];

        if (!empty($datos['clave'])) {
            $reglas['clave'] = 'min:6|confirmed';
        }

        $validador = new Validator($datos);

        if (!$validador->validar($reglas, $this->etiquetas())
            || $this->usuarios->usuarioExiste($datos['usuario'], (int) $id)) {
            if ($this->usuarios->usuarioExiste($datos['usuario'], (int) $id)) {
                Session::flash('danger', 'El nombre de usuario ya está en uso.');
            }
            foreach ($validador->mensajes() as $mensaje) {
                Session::flash('danger', $mensaje);
            }
            $this->redirect('/usuarios/' . $id . '/editar');
        }

        $this->usuarios->actualizarUsuario((int) $id, [
            'nombre'  => $datos['nombre'],
            'usuario' => $datos['usuario'],
            'clave'   => $datos['clave'],
            'activo'  => isset($_POST['activo']) ? 1 : 0,
        ]);

        Session::flash('success', 'Usuario actualizado correctamente.');
        $this->redirect('/usuarios');
    }

    public function eliminar(string $id): void
    {
        $this->verificarCsrf('/usuarios');

        if ((int) $id === Auth::id()) {
            Session::flash('danger', 'No puedes eliminar tu propio usuario.');
            $this->redirect('/usuarios');
        }

        $this->usuarios->eliminar((int) $id);
        Session::flash('success', 'Usuario eliminado.');
        $this->redirect('/usuarios');
    }

    private function datosFormulario(): array
    {
        return [
            'nombre'               => Request::post('nombre', ''),
            'usuario'              => Request::post('usuario', ''),
            'clave'                => Request::post('clave', ''),
            'clave_confirmation'   => Request::post('clave_confirmation', ''),
        ];
    }

    private function etiquetas(): array
    {
        return [
            'nombre'  => 'Nombre',
            'usuario' => 'Usuario',
            'clave'   => 'Contraseña',
        ];
    }

    private function verificarCsrf(string $rutaFallo): void
    {
        if (!Csrf::validar(Request::post('_token'))) {
            Session::flash('danger', 'Sesión expirada. Intenta nuevamente.');
            $this->redirect($rutaFallo);
        }
    }
}
