<?php

/**
 * Modelo base con acceso a la conexión PDO y utilidades CRUD sencillas.
 */
abstract class Model
{
    protected PDO $db;

    /** Nombre de la tabla asociada (definido por cada modelo hijo). */
    protected string $tabla = '';

    /** Clave primaria. */
    protected string $pk = 'id';

    public function __construct()
    {
        $this->db = Database::conectar();
    }

    public function todos(string $orden = null): array
    {
        $sql = "SELECT * FROM {$this->tabla}";

        if ($orden !== null) {
            $sql .= " ORDER BY {$orden}";
        }

        return $this->db->query($sql)->fetchAll();
    }

    public function encontrar($id): ?array
    {
        $stmt = $this->db->prepare("SELECT * FROM {$this->tabla} WHERE {$this->pk} = ? LIMIT 1");
        $stmt->execute([$id]);

        $fila = $stmt->fetch();

        return $fila === false ? null : $fila;
    }

    public function crear(array $datos): int
    {
        $columnas      = array_keys($datos);
        $marcadores    = array_map(static fn ($c) => ':' . $c, $columnas);
        $listaColumnas = implode(', ', $columnas);
        $listaValores  = implode(', ', $marcadores);

        $stmt = $this->db->prepare("INSERT INTO {$this->tabla} ({$listaColumnas}) VALUES ({$listaValores})");
        $stmt->execute($datos);

        return (int) $this->db->lastInsertId();
    }

    public function actualizar($id, array $datos): bool
    {
        $asignaciones = implode(', ', array_map(static fn ($c) => "{$c} = :{$c}", array_keys($datos)));

        $datos[$this->pk] = $id;

        $stmt = $this->db->prepare("UPDATE {$this->tabla} SET {$asignaciones} WHERE {$this->pk} = :{$this->pk}");

        return $stmt->execute($datos);
    }

    public function eliminar($id): bool
    {
        $stmt = $this->db->prepare("DELETE FROM {$this->tabla} WHERE {$this->pk} = ?");

        return $stmt->execute([$id]);
    }
}
