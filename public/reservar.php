<?php
require_once '../config/database.php';

$pdo = conectar();

// Cargamos las habitaciones para el selector
$stmt       = $pdo->query('SELECT * FROM habitaciones ORDER BY numero ASC');
$habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Valores prerellenados si vienen desde disponibilidad.php
$habitacion_id_pre = $_GET['habitacion_id'] ?? '';
$fecha_entrada_pre = $_GET['entrada']       ?? '';
$fecha_salida_pre  = $_GET['salida']        ?? '';

$mensaje = '';
$error   = '';

// Procesamos el formulario cuando se envía
if ($_SERVER['REQUEST_METHOD'] === 'POST') {
    $habitacion_id  = $_POST['habitacion_id']  ?? '';
    $cliente_nombre = trim($_POST['cliente_nombre'] ?? '');
    $cliente_email  = trim($_POST['cliente_email']  ?? '');
    $fecha_entrada  = $_POST['fecha_entrada']  ?? '';
    $fecha_salida   = $_POST['fecha_salida']   ?? '';

    // Validaciones básicas
    if (!$habitacion_id || !$cliente_nombre || !$cliente_email || !$fecha_entrada || !$fecha_salida) {
        $error = 'Todos los campos son obligatorios.';

    } elseif ($fecha_salida <= $fecha_entrada) {
        $error = 'La fecha de salida debe ser posterior a la de entrada.';

    } elseif (!filter_var($cliente_email, FILTER_VALIDATE_EMAIL)) {
        $error = 'El email no tiene un formato válido.';

    } else {
        // Comprobamos que la habitación no esté ocupada en esas fechas
        $stmt = $pdo->prepare('
            SELECT COUNT(*) FROM reservas
            WHERE habitacion_id = ?
              AND estado != "cancelada"
              AND fecha_entrada < ?
              AND fecha_salida  > ?
        ');
        $stmt->execute([$habitacion_id, $fecha_salida, $fecha_entrada]);
        $solapadas = $stmt->fetchColumn();

        if ($solapadas > 0) {
            $error = 'Esa habitación ya está ocupada en las fechas seleccionadas.';
        } else {
            // Insertamos la reserva — usuario_id 1 es el admin (único usuario de prueba)
            $stmt = $pdo->prepare('
                INSERT INTO reservas (usuario_id, habitacion_id, cliente_nombre, cliente_email, fecha_entrada, fecha_salida)
                VALUES (1, ?, ?, ?, ?, ?)
            ');
            $stmt->execute([$habitacion_id, $cliente_nombre, $cliente_email, $fecha_entrada, $fecha_salida]);
            $mensaje = '¡Reserva creada correctamente! ID: ' . $pdo->lastInsertId();
        }
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel API — Nueva Reserva</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header>
        <div class="header-inner">
            <h1>Grand Hotel API</h1>
            <nav>
                <a href="index.php">Reservas</a>
                <a href="disponibilidad.php">Disponibilidad</a>
                <a href="reservar.php" class="activo">Nueva Reserva</a>
            </nav>
        </div>
    </header>

    <main>

        <?php if ($mensaje): ?>
            <div class="alerta exito"><?= htmlspecialchars($mensaje) ?></div>
        <?php endif; ?>

        <?php if ($error): ?>
            <div class="alerta error"><?= htmlspecialchars($error) ?></div>
        <?php endif; ?>

        <div class="form-card">
            <h2>Nueva reserva</h2>

            <form method="POST" action="reservar.php">

                <div class="form-grupo">
                    <label for="habitacion_id">Habitación</label>
                    <select id="habitacion_id" name="habitacion_id" required>
                        <option value="">— Selecciona una habitación —</option>
                        <?php foreach ($habitaciones as $h): ?>
                            <option value="<?= $h['id'] ?>"
                                <?= ($habitacion_id_pre == $h['id']) ? 'selected' : '' ?>>
                                Hab. <?= htmlspecialchars($h['numero']) ?>
                                — <?= ucfirst($h['tipo']) ?>
                                (<?= number_format($h['precio'], 2) ?> €/noche)
                            </option>
                        <?php endforeach; ?>
                    </select>
                </div>

                <div class="form-grupo">
                    <label for="cliente_nombre">Nombre del cliente</label>
                    <input type="text" id="cliente_nombre" name="cliente_nombre"
                           placeholder="Ej: Juan García" required>
                </div>

                <div class="form-grupo">
                    <label for="cliente_email">Email del cliente</label>
                    <input type="email" id="cliente_email" name="cliente_email"
                           placeholder="Ej: juan@ejemplo.com" required>
                </div>

                <div class="form-fila">
                    <div class="form-grupo">
                        <label for="fecha_entrada">Fecha de entrada</label>
                        <input type="date" id="fecha_entrada" name="fecha_entrada"
                               value="<?= htmlspecialchars($fecha_entrada_pre) ?>"
                               min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-grupo">
                        <label for="fecha_salida">Fecha de salida</label>
                        <input type="date" id="fecha_salida" name="fecha_salida"
                               value="<?= htmlspecialchars($fecha_salida_pre) ?>"
                               min="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>

                <button type="submit" class="btn">Crear reserva</button>
                <a href="index.php" class="btn btn-secundario">Ver reservas</a>

            </form>
        </div>

    </main>


</body>
</html>
