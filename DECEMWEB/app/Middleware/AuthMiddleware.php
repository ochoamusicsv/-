<?php

/**
 * Middleware que exige un usuario autenticado. Si no lo hay, redirige al login.
 */
class AuthMiddleware
{
    public function manejar(): void
    {
        if (!Auth::check()) {
            Session::flash('warning', 'Debes iniciar sesión para continuar.');
            header('Location: ' . url('/login'));
            exit;
        }
    }
}
