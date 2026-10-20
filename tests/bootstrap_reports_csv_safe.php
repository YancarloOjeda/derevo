
<?php

// PCB-016: Cargar únicamente reports_csv_safe().

$sourceFile = __DIR__ . '/../includes/reports_data.php';

if (!is_file($sourceFile)) {
    throw new RuntimeException(
        'No se encontró includes/reports_data.php.'
    );
}

$source = file_get_contents($sourceFile);

if ($source === false) {
    throw new RuntimeException(
        'No fue posible leer reports_data.php.'
    );
}

// Localizar la declaración original de la función.
$start = strpos(
    $source,
    'function reports_csv_safe('
);

if ($start === false) {
    throw new RuntimeException(
        'No se encontró reports_csv_safe().'
    );
}

// Extraer la función completa mediante sus llaves.
$openBrace = strpos($source, '{', $start);

if ($openBrace === false) {
    throw new RuntimeException(
        'No se encontró el inicio de la función.'
    );
}

$depth = 0;
$end = null;
$length = strlen($source);

for ($i = $openBrace; $i < $length; $i++) {
    if ($source[$i] === '{') {
        $depth++;
    } elseif ($source[$i] === '}') {
        $depth--;

        if ($depth === 0) {
            $end = $i + 1;
            break;
        }
    }
}

if ($end === null) {
    throw new RuntimeException(
        'No se encontró el cierre de reports_csv_safe().'
    );
}

$functionCode = substr(
    $source,
    $start,
    $end - $start
);

// Evaluar exclusivamente la función original.
eval($functionCode);
