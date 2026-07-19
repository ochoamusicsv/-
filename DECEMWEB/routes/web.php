<?php

/**
 * Definición de rutas de la aplicación.
 *
 * @var Router $router
 */

// Página de inicio: redirige según el estado de sesión.
$router->get('/', ['AuthController', 'mostrarLogin'], [GuestMiddleware::class]);

// Autenticación.
$router->get('/login', ['AuthController', 'mostrarLogin'], [GuestMiddleware::class]);
$router->post('/login', ['AuthController', 'login'], [GuestMiddleware::class]);
$router->post('/logout', ['AuthController', 'logout'], [AuthMiddleware::class]);

// Panel principal.
$router->get('/dashboard', ['DashboardController', 'index'], [AuthMiddleware::class]);

// CRUD de usuarios (requiere autenticación).
$router->get('/usuarios', ['UsersController', 'index'], [AuthMiddleware::class]);
$router->get('/usuarios/crear', ['UsersController', 'crear'], [AuthMiddleware::class]);
$router->post('/usuarios', ['UsersController', 'guardar'], [AuthMiddleware::class]);
$router->get('/usuarios/{id}/editar', ['UsersController', 'editar'], [AuthMiddleware::class]);
$router->post('/usuarios/{id}', ['UsersController', 'actualizar'], [AuthMiddleware::class]);
$router->post('/usuarios/{id}/eliminar', ['UsersController', 'eliminar'], [AuthMiddleware::class]);
