<?php
require_once '../config/database.php';

$pdo = conectar();

// Consulta todas las reservas con datos de la habitación
$stmt = $pdo->query('
    SELECT r.*, h.numero, h.tipo, h.precio
    FROM reservas r
    JOIN habitaciones h ON r.habitacion_id = h.id
    ORDER BY r.fecha_entrada ASC
');
$reservas = $stmt->fetchAll(PDO::FETCH_ASSOC);

// Totales para las tarjetas del panel
$total_reservas    = count($reservas);
$confirmadas       = count(array_filter($reservas, fn($r) => $r['estado'] === 'confirmada'));
$pendientes        = count(array_filter($reservas, fn($r) => $r['estado'] === 'pendiente'));
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel API — Panel de Reservas</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header>
        <div class="header-inner">
            <h1>Grand Hotel API</h1>
            <nav>
                <a href="index.php" class="activo">Reservas</a>
                <a href="disponibilidad.php">Disponibilidad</a>
                <a href="reservar.php">Nueva Reserva</a>
            </nav>
        </div>
    </header>

    <main>

        <!-- Tarjetas de resumen -->
        <section class="tarjetas">
            <div class="tarjeta">
                <span class="tarjeta-numero"><?= $total_reservas ?></span>
                <span class="tarjeta-label">Total reservas</span>
            </div>
            <div class="tarjeta verde">
                <span class="tarjeta-numero"><?= $confirmadas ?></span>
                <span class="tarjeta-label">Confirmadas</span>
            </div>
            <div class="tarjeta amarillo">
                <span class="tarjeta-numero"><?= $pendientes ?></span>
                <span class="tarjeta-label">Pendientes</span>
            </div>
        </section>

        <!-- Tabla de reservas -->
        <section class="tabla-seccion">
            <h2>Reservas actuales</h2>

            <?php if (empty($reservas)): ?>
                <p class="sin-datos">No hay reservas registradas.</p>
            <?php else: ?>
                <table>
                    <thead>
                        <tr>
                            <th>#</th>
                            <th>Cliente</th>
                            <th>Habitación</th>
                            <th>Tipo</th>
                            <th>Entrada</th>
                            <th>Salida</th>
                            <th>Precio/noche</th>
                            <th>Estado</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($reservas as $r): ?>
                        <tr>
                            <td><?= $r['id'] ?></td>
                            <td>
                                <strong><?= htmlspecialchars($r['cliente_nombre']) ?></strong><br>
                                <small><?= htmlspecialchars($r['cliente_email']) ?></small>
                            </td>
                            <td>Hab. <?= htmlspecialchars($r['numero']) ?></td>
                            <td><?= ucfirst($r['tipo']) ?></td>
                            <td><?= $r['fecha_entrada'] ?></td>
                            <td><?= $r['fecha_salida'] ?></td>
                            <td><?= number_format($r['precio'], 2) ?> €</td>
                            <td>
                                <span class="estado <?= $r['estado'] ?>">
                                    <?= ucfirst($r['estado']) ?>
                                </span>
                            </td>
                        </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php endif; ?>
        </section>

    </main>


</body>
</html>
