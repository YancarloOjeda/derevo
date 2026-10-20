
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap_movements_insert.php';

final class MovementsInsertTest extends TestCase
{
    // C1: 1-2-3-4-5-7-8
    // Registro con observaciones
    public function testRegistroConObservaciones(): void
    {
        $stmt = $this->createMock(PDOStatement::class);

        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return $params['observaciones'] === 'Revisión';
            }));

        $db = $this->createMock(PDO::class);

        $db->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO movimientos'))
            ->willReturn($stmt);

        movement_insert_for_test(
            $db,
            true,
            'Revisión'
        );
    }

    // C2: 1-2-3-4-6-7-8
    // Registro sin observaciones
    public function testRegistroSinObservaciones(): void
    {
        $stmt = $this->createMock(PDOStatement::class);

        $stmt->expects($this->once())
            ->method('execute')
            ->with($this->callback(function ($params) {
                return array_key_exists('observaciones', $params)
                    && $params['observaciones'] === null;
            }));

        $db = $this->createMock(PDO::class);

        $db->expects($this->once())
            ->method('prepare')
            ->with($this->stringContains('INSERT INTO movimientos'))
            ->willReturn($stmt);

        movement_insert_for_test(
            $db,
            true,
            ''
        );
    }

    // C3: 1-2-8
    // Omisión del registro
    public function testSinRegistroDeMovimiento(): void
    {
        $db = $this->createMock(PDO::class);

        $db->expects($this->never())
            ->method('prepare');

        movement_insert_for_test(
            $db,
            false,
            'Revisión'
        );
    }
}
