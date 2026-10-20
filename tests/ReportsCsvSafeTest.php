
<?php

use PHPUnit\Framework\TestCase;

require_once __DIR__ . '/bootstrap_reports_csv_safe.php';

final class ReportsCsvSafeTest extends TestCase
{
    // C1: 1-2-3-4-6
    // Valor potencialmente interpretable como fórmula
    public function testProteccionContraFormula(): void
    {
        $input = '=SUM(A1:A3)';
        $expected = "'" . $input;

        $this->assertSame(
            $expected,
            reports_csv_safe($input)
        );
    }

    // C2: 1-2-3-5-6
    // Valor ordinario sin modificaciones
    public function testTextoSinModificaciones(): void
    {
        $input = 'Computadora Dell';

        $this->assertSame(
            $input,
            reports_csv_safe($input)
        );
    }
}
