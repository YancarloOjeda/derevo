
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap_reports.php';

final class ReportsDatesTest extends TestCase
{
    // Caso 1: Ambas fechas omitidas
    public function testFechasOmitidas(): void
    {
        $result = reports_filters([]);

        $this->assertSame('', $result['desde']);
        $this->assertSame('', $result['hasta']);
    }

    // Caso 2: Fecha inicial válida
    public function testFechaInicialValida(): void
    {
        $result = reports_filters([
            'desde' => '2026-10-20'
        ]);

        $this->assertSame(
            '2026-10-20',
            $result['desde']
        );
    }

    // Caso 3: Fecha inexistente
    public function testFechaInexistente(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'La fecha desde no es válida.'
        );

        reports_filters([
            'desde' => '2026-02-30'
        ]);
    }

    // Caso 4: Formato incorrecto
    public function testFormatoIncorrecto(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'La fecha hasta no es válida.'
        );

        reports_filters([
            'hasta' => '20/10/2026'
        ]);
    }

    // Caso 5: Fecha final válida
    public function testFechaFinalValida(): void
    {
        $result = reports_filters([
            'hasta' => '2026-10-20'
        ]);

        $this->assertSame(
            '2026-10-20',
            $result['hasta']
        );
    }

    // Caso 6: Ambas fechas válidas
    public function testAmbasFechasValidas(): void
    {
        $result = reports_filters([
            'desde' => '2026-10-01',
            'hasta' => '2026-10-20'
        ]);

        $this->assertSame(
            '2026-10-01',
            $result['desde']
        );

        $this->assertSame(
            '2026-10-20',
            $result['hasta']
        );
    }

    // Caso 7: Fecha inicial con formato incorrecto
    public function testFechaInicialFormatoIncorrecto(): void
    {
        $this->expectException(
            InvalidArgumentException::class
        );

        $this->expectExceptionMessage(
            'La fecha desde no es válida.'
        );

        reports_filters([
            'desde' => '2026/10/20'
        ]);
    }
}
