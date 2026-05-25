<?php
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');

// Solo aceptamos POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
    exit;
}

// Recibimos el JSON del body
$datos = json_decode(file_get_contents('php://input'), true);

$email    = $datos['email']    ?? '';
$password = $datos['password'] ?? '';

if (empty($email) || empty($password)) {
    http_response_code(400);
    echo json_encode(['error' => 'Email y password son obligatorios']);
    exit;
}

$pdo = conectar();

// Buscamos el usuario por email
$stmt = $pdo->prepare('SELECT * FROM usuarios WHERE email = ?');
$stmt->execute([$email]);
$usuario = $stmt->fetch(PDO::FETCH_ASSOC);

// Verificamos la contraseña
if (!$usuario || !password_verify($password, $usuario['password'])) {
    http_response_code(401);
    echo json_encode(['error' => 'Credenciales incorrectas']);
    exit;
}

// Generamos un token aleatorio y lo guardamos en BD
$token = bin2hex(random_bytes(32));
$stmt = $pdo->prepare('UPDATE usuarios SET token = ? WHERE id = ?');
$stmt->execute([$token, $usuario['id']]);

echo json_encode([
    'mensaje' => 'Login correcto',
    'token'   => $token,
    'usuario' => $usuario['nombre']
]);
