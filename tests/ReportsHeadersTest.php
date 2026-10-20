
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap_reports_headers.php';

final class ReportsHeadersTest extends TestCase
{
    // C1: 1-2-3-5
    // Encabezados de inventario
    public function testEncabezadosInventario(): void
    {
        $expected = [
            'ID',
            'Activo',
            'Número de serie',
            'Estado',
            'Categoría',
            'Ubicación',
            'Responsable'
        ];

        $actual = reports_headers_for_test('inventario');

        $this->assertSame($expected, $actual);
    }

    // C2: 1-2-4-5
    // Encabezados de movimientos
    public function testEncabezadosMovimientos(): void
    {
        $expected = [
            'ID',
            'Fecha local (México)',
            'Activo',
            'Número de serie',
            'Tipo',
            'Registró',
            'Observaciones'
        ];

        $actual = reports_headers_for_test('movimientos');

        $this->assertSame($expected, $actual);
    }
}
