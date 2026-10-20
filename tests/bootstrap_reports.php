
<?php

// Cargar exclusivamente la función reports_filters()
// sin cargar las dependencias de autenticación o BD.

$source = file_get_contents(
    __DIR__ . '/../includes/reports_data.php'
);

if ($source === false) {
    throw new RuntimeException(
        'No se pudo leer reports_data.php'
    );
}

// Extraer el código de reports_filters() mediante tokens PHP.
$tokens = token_get_all($source);

$capturing = false;
$found = false;
$depth = 0;
$functionCode = '';

foreach ($tokens as $token) {
    $text = is_array($token) ? $token[1] : $token;

    if (!$capturing) {
        if (
            is_array($token)
            && $token[0] === T_FUNCTION
        ) {
            $capturing = true;
            $functionCode = $text;
        }
        continue;
    }

    $functionCode .= $text;

    if ($text === '{') {
        $depth++;
        $found = true;
    } elseif ($text === '}') {
        $depth--;

        if ($found && $depth === 0) {
            break;
        }
    }
}

if (!$found || $depth !== 0) {
    throw new RuntimeException(
        'No se pudo extraer la función.'
    );
}

eval($functionCode);
