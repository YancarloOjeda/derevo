<?php

declare(strict_types=1);

/** Fechas de movimientos almacenadas como DATETIME en UTC. */
function movement_local_date(?string $utcDate): string
{
    if ($utcDate === null || $utcDate === '') {
        return '—';
    }

    $utc = new DateTimeZone('UTC');
    $local = new DateTimeZone('America/Mexico_City');
    return (new DateTimeImmutable($utcDate, $utc))->setTimezone($local)->format('Y-m-d H:i:s');
}

/** Convierte el inicio de un día local a UTC para comparar con DATETIME. */
function movement_local_day_start_utc(string $day): string
{
    $local = new DateTimeZone('America/Mexico_City');
    $date = DateTimeImmutable::createFromFormat('!Y-m-d', $day, $local);
    if (!$date || $date->format('Y-m-d') !== $day) {
        throw new InvalidArgumentException('La fecha proporcionada no es válida.');
    }
    return $date->setTimezone(new DateTimeZone('UTC'))->format('Y-m-d H:i:s');
}
