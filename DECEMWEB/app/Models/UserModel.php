<?php

/**
 * Modelo de usuarios del sistema.
 */
class UserModel extends Model
{
    protected string $tabla = 'usuarios';

    /**
     * Busca un usuario activo por su nombre de usuario.
     */
    public function porUsuario(string $usuario): ?array
    {
        $stmt = $this->db->prepare(
            'SELECT * FROM usuarios WHERE usuario = ? AND activo = 1 LIMIT 1'
        );
        $stmt->execute([$usuario]);

        $fila = $stmt->fetch();

        return $fila === false ? null : $fila;
    }

    /**
     * Verifica credenciales. Devuelve el usuario si son válidas, o null.
     */
    public function autenticar(string $usuario, string $clave): ?array
    {
        $registro = $this->porUsuario($usuario);

        if ($registro === null) {
            return null;
        }

        if (!password_verify($clave, $registro['clave'])) {
            return null;
        }

        // Rehash transparente si el algoritmo por defecto cambió.
        if (password_needs_rehash($registro['clave'], PASSWORD_DEFAULT)) {
            $this->actualizar($registro['id'], [
                'clave' => password_hash($clave, PASSWORD_DEFAULT),
            ]);
        }

        return $registro;
    }

    public function usuarioExiste(string $usuario, ?int $exceptoId = null): bool
    {
        $sql    = 'SELECT COUNT(*) FROM usuarios WHERE usuario = ?';
        $params = [$usuario];

        if ($exceptoId !== null) {
            $sql     .= ' AND id <> ?';
            $params[] = $exceptoId;
        }

        $stmt = $this->db->prepare($sql);
        $stmt->execute($params);

        return (int) $stmt->fetchColumn() > 0;
    }

    /**
     * Crea un usuario aplicando hash a la contraseña.
     */
    public function registrar(array $datos): int
    {
        $datos['clave'] = password_hash($datos['clave'], PASSWORD_DEFAULT);

        return $this->crear($datos);
    }

    /**
     * Actualiza un usuario. Solo aplica hash a la clave si viene informada.
     */
    public function actualizarUsuario(int $id, array $datos): bool
    {
        if (empty($datos['clave'])) {
            unset($datos['clave']);
        } else {
            $datos['clave'] = password_hash($datos['clave'], PASSWORD_DEFAULT);
        }

        return $this->actualizar($id, $datos);
    }
}
