<?php
require_once '../config/database.php';

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: GET, POST, PUT, DELETE');
header('Access-Control-Allow-Headers: Authorization, Content-Type');

$pdo    = conectar();
$metodo = $_SERVER['REQUEST_METHOD'];

// --- Función para verificar el token ---
function verificarToken($pdo) {
    $cabecera = $_SERVER['HTTP_AUTHORIZATION'] ?? '';

    // Esperamos "Bearer <token>"
    if (!str_starts_with($cabecera, 'Bearer ')) {
        http_response_code(401);
        echo json_encode(['error' => 'Token requerido']);
        exit;
    }

    $token = substr($cabecera, 7);
    $stmt  = $pdo->prepare('SELECT id FROM usuarios WHERE token = ?');
    $stmt->execute([$token]);
    $usuario = $stmt->fetch(PDO::FETCH_ASSOC);

    if (!$usuario) {
        http_response_code(401);
        echo json_encode(['error' => 'Token inválido']);
        exit;
    }

    return $usuario['id'];
}

// ==================== GET ====================
if ($metodo === 'GET') {

    if (isset($_GET['id'])) {
        // GET /api/reservas.php?id=X → una reserva
        $stmt = $pdo->prepare('
            SELECT r.*, h.numero, h.tipo, h.precio
            FROM reservas r
            JOIN habitaciones h ON r.habitacion_id = h.id
            WHERE r.id = ?
        ');
        $stmt->execute([$_GET['id']]);
        $reserva = $stmt->fetch(PDO::FETCH_ASSOC);

        if (!$reserva) {
            http_response_code(404);
            echo json_encode(['error' => 'Reserva no encontrada']);
        } else {
            echo json_encode($reserva);
        }

    } else {
        // GET /api/reservas.php → todas las reservas
        $stmt = $pdo->query('
            SELECT r.*, h.numero, h.tipo, h.precio
            FROM reservas r
            JOIN habitaciones h ON r.habitacion_id = h.id
            ORDER BY r.created_at DESC
        ');
        $reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);
        echo json_encode($reservas);
    }

// ==================== POST ====================
} elseif ($metodo === 'POST') {

    verificarToken($pdo);
    $usuario_id = verificarToken($pdo);

    $datos = json_decode(file_get_contents('php://input'), true);

    $habitacion_id   = $datos['habitacion_id']   ?? null;
    $cliente_nombre  = $datos['cliente_nombre']  ?? '';
    $cliente_email   = $datos['cliente_email']   ?? '';
    $fecha_entrada   = $datos['fecha_entrada']   ?? '';
    $fecha_salida    = $datos['fecha_salida']    ?? '';

    if (!$habitacion_id || !$cliente_nombre || !$cliente_email || !$fecha_entrada || !$fecha_salida) {
        http_response_code(400);
        echo json_encode(['error' => 'Faltan campos obligatorios']);
        exit;
    }

    // Comprobar que la habitación existe
    $stmt = $pdo->prepare('SELECT id FROM habitaciones WHERE id = ?');
    $stmt->execute([$habitacion_id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Habitación no encontrada']);
        exit;
    }

    $stmt = $pdo->prepare('
        INSERT INTO reservas (usuario_id, habitacion_id, cliente_nombre, cliente_email, fecha_entrada, fecha_salida)
        VALUES (?, ?, ?, ?, ?, ?)
    ');
    $stmt->execute([$usuario_id, $habitacion_id, $cliente_nombre, $cliente_email, $fecha_entrada, $fecha_salida]);

    http_response_code(201);
    echo json_encode(['mensaje' => 'Reserva creada', 'id' => $pdo->lastInsertId()]);

// ==================== PUT ====================
} elseif ($metodo === 'PUT') {

    verificarToken($pdo);

    $datos = json_decode(file_get_contents('php://input'), true);
    $id    = $datos['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de reserva requerido']);
        exit;
    }

    // Comprobamos que existe
    $stmt = $pdo->prepare('SELECT id FROM reservas WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Reserva no encontrada']);
        exit;
    }

    // Actualizamos solo los campos que llegan
    $campos = [];
    $valores = [];

    if (isset($datos['cliente_nombre']))  { $campos[] = 'cliente_nombre = ?';  $valores[] = $datos['cliente_nombre']; }
    if (isset($datos['cliente_email']))   { $campos[] = 'cliente_email = ?';   $valores[] = $datos['cliente_email']; }
    if (isset($datos['fecha_entrada']))   { $campos[] = 'fecha_entrada = ?';   $valores[] = $datos['fecha_entrada']; }
    if (isset($datos['fecha_salida']))    { $campos[] = 'fecha_salida = ?';    $valores[] = $datos['fecha_salida']; }
    if (isset($datos['estado']))          { $campos[] = 'estado = ?';          $valores[] = $datos['estado']; }
    if (isset($datos['habitacion_id']))   { $campos[] = 'habitacion_id = ?';   $valores[] = $datos['habitacion_id']; }

    if (empty($campos)) {
        http_response_code(400);
        echo json_encode(['error' => 'No hay campos para actualizar']);
        exit;
    }

    $valores[] = $id;
    $sql = 'UPDATE reservas SET ' . implode(', ', $campos) . ' WHERE id = ?';
    $stmt = $pdo->prepare($sql);
    $stmt->execute($valores);

    echo json_encode(['mensaje' => 'Reserva actualizada']);

// ==================== DELETE ====================
} elseif ($metodo === 'DELETE') {

    verificarToken($pdo);

    $datos = json_decode(file_get_contents('php://input'), true);
    $id    = $datos['id'] ?? null;

    if (!$id) {
        http_response_code(400);
        echo json_encode(['error' => 'ID de reserva requerido']);
        exit;
    }

    $stmt = $pdo->prepare('SELECT id FROM reservas WHERE id = ?');
    $stmt->execute([$id]);
    if (!$stmt->fetch()) {
        http_response_code(404);
        echo json_encode(['error' => 'Reserva no encontrada']);
        exit;
    }

    $stmt = $pdo->prepare('DELETE FROM reservas WHERE id = ?');
    $stmt->execute([$id]);

    echo json_encode(['mensaje' => 'Reserva eliminada']);

} else {
    http_response_code(405);
    echo json_encode(['error' => 'Método no permitido']);
}
