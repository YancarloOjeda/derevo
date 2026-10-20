<?php

declare(strict_types=1);

require_once __DIR__ . '/config.php';
require_once __DIR__ . '/database.php';
require_once __DIR__ . '/password_policy.php';

function start_secure_session(): void
{
    if (session_status() === PHP_SESSION_ACTIVE) {
        return;
    }

    if (headers_sent()) {
        throw new RuntimeException('No se puede iniciar la sesión después de enviar contenido.');
    }

    ini_set('session.use_strict_mode', '1');
    ini_set('session.use_only_cookies', '1');
    ini_set('session.cookie_httponly', '1');

    session_name('derevo_session');
    session_set_cookie_params([
        'lifetime' => 0,
        'path' => APP_BASE_PATH === '' ? '/' : APP_BASE_PATH . '/',
        'secure' => !empty($_SERVER['HTTPS']) && $_SERVER['HTTPS'] !== 'off',
        'httponly' => true,
        'samesite' => 'Lax',
    ]);

    session_start();
}

function csrf_token(): string
{
    start_secure_session();

    if (!isset($_SESSION['csrf_token'])) {
        $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
    }

    return $_SESSION['csrf_token'];
}

function csrf_valid(?string $token): bool
{
    return is_string($token) && hash_equals(csrf_token(), $token);
}

function current_user(): ?array
{
    start_secure_session();

    $id = $_SESSION['user_id'] ?? null;

    if (!is_int($id) || $id < 1) {
        return null;
    }

    $query = database()->prepare(
        'SELECT u.id_usuario, u.nombre, u.usuario, u.correo, u.estado,
                u.session_version, u.must_change_password, r.nombre_rol
         FROM usuarios AS u
         INNER JOIN roles AS r ON r.id_rol = u.id_rol
         WHERE u.id_usuario = :id
         LIMIT 1'
    );
    $query->execute(['id' => $id]);
    $user = $query->fetch();

    if (!$user || !in_array(strtolower((string) $user['estado']), ['activo', '1'], true)) {
        unset($_SESSION['user_id']);
        return null;
    }

    if (!isset($_SESSION['session_version']) ||
        (int) $_SESSION['session_version'] !== (int) $user['session_version']) {
        unset($_SESSION['user_id'], $_SESSION['session_version']);
        return null;
    }

    return $user;
}

function require_login(): array
{
    $user = current_user();

    if ($user === null) {
        header('Location: ' . app_url('login.php'), true, 302);
        exit;
    }

    if ((int) $user['must_change_password'] === 1 &&
        basename((string) ($_SERVER['SCRIPT_NAME'] ?? '')) !== 'cambiar_mi_contrasena.php') {
        header('Location: ' . app_url('cambiar_mi_contrasena.php'), true, 303);
        exit;
    }

    return $user;
}

function require_role(string $role): array
{
    $user = require_login();

    if ($user['nombre_rol'] !== $role) {
        http_response_code(403);
        exit('Acceso denegado.');
    }

    return $user;
}

function record_access(int $userId, string $action): void
{
    try {
        $query = database()->prepare(
            'INSERT INTO bitacora_accesos (id_usuario, fecha_hora, direccion_ip, accion)
             VALUES (:id_usuario, NOW(), :direccion_ip, :accion)'
        );
        $query->execute([
            'id_usuario' => $userId,
            'direccion_ip' => substr((string) ($_SERVER['REMOTE_ADDR'] ?? ''), 0, 45),
            'accion' => $action,
        ]);
    } catch (PDOException $exception) {
        error_log('DEREVO: no se pudo registrar un acceso: ' . $exception->getCode());
    }
}
