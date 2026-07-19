<?php

/**
 * Middleware para rutas de invitado (login). Si ya hay sesión, va al panel.
 */
class GuestMiddleware
{
    public function manejar(): void
    {
        if (Auth::check()) {
            header('Location: ' . url('/dashboard'));
            exit;
        }
    }
}
