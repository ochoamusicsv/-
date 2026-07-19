# DECEM · Sistema de Recaudación

Aplicación web en PHP (arquitectura MVC ligera, sin frameworks) para el
sistema de recaudación DECEM. Incluye autenticación segura, panel de control y
administración de usuarios.

## Requisitos

- PHP 8.0 o superior
- MySQL / MariaDB
- Apache con `mod_rewrite` habilitado (incluido en XAMPP)

## Instalación con XAMPP

1. **Copiar el proyecto**

   Coloca la carpeta `DECEMWEB` dentro de `htdocs`:

   ```
   C:\xampp\htdocs\DECEMWEB
   ```

2. **Iniciar servicios**

   Abre el panel de XAMPP e inicia **Apache** y **MySQL**.

3. **Crear la base de datos**

   Abre <http://localhost/phpmyadmin>, ve a la pestaña **Importar** y selecciona
   el archivo `database/schema.sql`. Esto crea la base `decem`, la tabla
   `usuarios` y un usuario administrador de ejemplo.

4. **(Opcional) Configurar variables de entorno**

   Los valores por defecto ya funcionan con XAMPP. Si necesitas cambiarlos,
   copia `.env.example` como `.env` y ajusta los datos de conexión:

   ```bash
   cp .env.example .env
   ```

5. **Abrir la aplicación**

   Visita <http://localhost/DECEMWEB>.

## Credenciales por defecto

| Usuario | Contraseña |
| ------- | ---------- |
| `admin` | `admin123` |

> Cambia esta contraseña después del primer inicio de sesión (menú de usuario
> → Usuarios → Editar).

## Estructura del proyecto

```
DECEMWEB/
├── app/
│   ├── Controllers/     Controladores (Auth, Dashboard, Users)
│   ├── Core/            Núcleo: Router, Controller, Model, Session, Auth, Csrf...
│   ├── Helpers/         Funciones globales (url, asset, e, old)
│   ├── Middleware/      Middlewares (Auth, Guest)
│   ├── Models/          Modelos de datos
│   └── Views/           Vistas y layouts
├── config/              Configuración y conexión PDO
├── database/            schema.sql (esquema + datos iniciales)
├── public/              Front controller (index.php) y assets
├── routes/              Definición de rutas (web.php)
└── .htaccess            Redirección hacia public/
```

## Características

- **MVC** con enrutador propio (soporta parámetros `{id}` y middlewares).
- **Autoloader** de clases (sin `require_once` manuales).
- **Autenticación segura**: contraseñas con `password_hash` / `password_verify`,
  regeneración de ID de sesión y rehash transparente.
- **Protección CSRF** en todos los formularios.
- **Consultas preparadas (PDO)** para prevenir inyección SQL.
- **Validación** de formularios y **mensajes flash**.
- **Escape de salida** (`htmlspecialchars`) para prevenir XSS.
- Interfaz responsive con Bootstrap 5.

## Módulos previstos

La estructura ya contempla los módulos de **personas**, **matrículas**,
**recibos**, **cartas** y **solvencias** (carpetas creadas en `app/Views`),
listos para implementarse siguiendo el mismo patrón que el módulo de usuarios.
