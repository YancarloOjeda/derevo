
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap_movements.php';

final class MovementsStatusTest extends TestCase
{
    // C1: 1-2-3-7
    // Cambio de estado a baja
    public function testCambioEstadoBaja(): void
    {
        $old = [
            'estado' => 'disponible',
            'responsable' => 3
        ];

        $new = [
            'estado' => 'baja',
            'responsable' => 3
        ];

        $this->assertSame(
            'baja',
            movement_type($old, $new)
        );
    }

    // C2: 1-2-4-5-7
    // Cambio de estado a mantenimiento
    public function testCambioEstadoMantenimiento(): void
    {
        $old = [
            'estado' => 'disponible',
            'responsable' => 3
        ];

        $new = [
            'estado' => 'mantenimiento',
            'responsable' => 3
        ];

        $this->assertSame(
            'mantenimiento',
            movement_type($old, $new)
        );
    }

    // C3: 1-2-4-6-7
    // Cambio a otro estado
    public function testOtroCambioEstado(): void
    {
        $old = [
            'estado' => 'disponible',
            'responsable' => 3
        ];

        $new = [
            'estado' => 'asignado',
            'responsable' => 3
        ];

        $this->assertSame(
            'cambio_estado',
            movement_type($old, $new)
        );
    }
}
