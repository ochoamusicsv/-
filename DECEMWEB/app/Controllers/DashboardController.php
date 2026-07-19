<?php

/**
 * Panel principal tras iniciar sesión.
 */
class DashboardController extends Controller
{
    public function index(): void
    {
        $usuarios = new UserModel();
        $total    = count($usuarios->todos());

        $this->view('dashboard/index', [
            'titulo'        => 'Panel',
            'totalUsuarios' => $total,
        ]);
    }
}
