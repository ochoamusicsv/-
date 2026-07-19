<?php

/**
 * Validador de datos de formularios muy sencillo.
 *
 * Reglas soportadas: required, min:n, max:n, email, confirmed.
 */
class Validator
{
    private array $datos;
    private array $errores = [];

    public function __construct(array $datos)
    {
        $this->datos = $datos;
    }

    public function validar(array $reglas, array $etiquetas = []): bool
    {
        foreach ($reglas as $campo => $listaReglas) {
            $valor    = $this->datos[$campo] ?? null;
            $etiqueta = $etiquetas[$campo] ?? ucfirst($campo);

            foreach (explode('|', $listaReglas) as $regla) {
                [$nombre, $parametro] = array_pad(explode(':', $regla, 2), 2, null);

                switch ($nombre) {
                    case 'required':
                        if ($valor === null || trim((string) $valor) === '') {
                            $this->agregar($campo, "El campo {$etiqueta} es obligatorio.");
                        }
                        break;

                    case 'min':
                        if ($valor !== null && mb_strlen((string) $valor) < (int) $parametro) {
                            $this->agregar($campo, "El campo {$etiqueta} debe tener al menos {$parametro} caracteres.");
                        }
                        break;

                    case 'max':
                        if ($valor !== null && mb_strlen((string) $valor) > (int) $parametro) {
                            $this->agregar($campo, "El campo {$etiqueta} no puede superar {$parametro} caracteres.");
                        }
                        break;

                    case 'email':
                        if ($valor !== null && $valor !== '' && !filter_var($valor, FILTER_VALIDATE_EMAIL)) {
                            $this->agregar($campo, "El campo {$etiqueta} debe ser un correo válido.");
                        }
                        break;

                    case 'confirmed':
                        if (($this->datos[$campo . '_confirmation'] ?? null) !== $valor) {
                            $this->agregar($campo, "La confirmación de {$etiqueta} no coincide.");
                        }
                        break;
                }
            }
        }

        return empty($this->errores);
    }

    private function agregar(string $campo, string $mensaje): void
    {
        $this->errores[$campo][] = $mensaje;
    }

    public function errores(): array
    {
        return $this->errores;
    }

    /**
     * Devuelve todos los mensajes de error aplanados en un solo arreglo.
     */
    public function mensajes(): array
    {
        return array_merge(...array_values($this->errores)) ?: [];
    }
}
