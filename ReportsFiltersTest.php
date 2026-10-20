
<?php

declare(strict_types=1);

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/../includes/reports_data.php';

final class ReportsFiltersTest extends TestCase
{
    // Camino 1: Sin fecha inicial
    public function testSinFechaInicial(): void
    {
        $result = reports_filters([
            'hasta' => '2026-10-20'
        ]);

        $this->assertSame('', $result['desde']);
        $this->assertSame('2026-10-20', $result['hasta']);
    }

    // Camino 2: Solo fecha inicial
    public function testSoloFechaInicial(): void
    {
        $result = reports_filters([
            'desde' => '2026-10-01'
        ]);

        $this->assertSame('2026-10-01', $result['desde']);
        $this->assertSame('', $result['hasta']);
    }

    // Camino 3: Rango cronológico válido
    public function testRangoValido(): void
    {
        $result = reports_filters([
            'desde' => '2026-10-01',
            'hasta' => '2026-10-20'
        ]);

        $this->assertSame('2026-10-01', $result['desde']);
        $this->assertSame('2026-10-20', $result['hasta']);
    }

    // Camino 4: Rango cronológico inválido
    public function testRangoInvalido(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'La fecha inicial no puede ser posterior a la fecha final.'
        );

        reports_filters([
            'desde' => '2026-10-20',
            'hasta' => '2026-10-01'
        ]);
    }
}
