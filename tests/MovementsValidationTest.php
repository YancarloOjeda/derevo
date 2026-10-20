
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap_movements_validation.php';

final class MovementsValidationTest extends TestCase
{
    // C1: 1-2-3-5
    // Tipo de movimiento válido
    public function testTipoMovimientoValido(): void
    {
        $this->assertTrue(
            movement_validate_type_for_test('asignacion')
        );
    }

    // C2: 1-2-4-5
    // Tipo de movimiento inválido
    public function testTipoMovimientoInvalido(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'Tipo de movimiento inválido.'
        );

        movement_validate_type_for_test('eliminacion');
    }
}
