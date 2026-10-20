
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap_movements.php';

final class MovementsTypeTest extends TestCase
{
    // C1: 1-2-3-4-7
    // Asignación inicial de responsable
    public function testAsignacionInicial(): void
    {
        $old = [
            'estado' => 'disponible',
            'responsable' => null
        ];

        $new = [
            'estado' => 'disponible',
            'responsable' => 5
        ];

        $this->assertSame(
            'asignacion',
            movement_type($old, $new)
        );
    }

    // C2: 1-2-3-5-7
    // Transferencia entre responsables
    public function testTransferenciaResponsable(): void
    {
        $old = [
            'estado' => 'disponible',
            'responsable' => 3
        ];

        $new = [
            'estado' => 'disponible',
            'responsable' => 5
        ];

        $this->assertSame(
            'transferencia',
            movement_type($old, $new)
        );
    }

    // C3: 1-2-6-7
    // Responsable sin modificaciones
    public function testResponsableSinCambios(): void
    {
        $old = [
            'estado' => 'disponible',
            'responsable' => 3
        ];

        $new = [
            'estado' => 'disponible',
            'responsable' => 3
        ];

        $this->assertSame(
            'transferencia',
            movement_type($old, $new)
        );
    }
}
