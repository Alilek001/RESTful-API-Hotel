<?php
require_once '../config/database.php';

$pdo = conectar();

$habitaciones  = [];
$fecha_entrada = $_GET['entrada'] ?? '';
$fecha_salida  = $_GET['salida']  ?? '';
$buscado       = false;
$error         = '';

if ($fecha_entrada && $fecha_salida) {
    $buscado = true;

    if ($fecha_salida <= $fecha_entrada) {
        $error = 'La fecha de salida debe ser posterior a la de entrada.';
    } else {
        // Para cada habitación comprobamos si tiene reservas que se solapen con las fechas
        $stmt = $pdo->prepare('
            SELECT h.*,
                   COUNT(r.id) AS reservas_solapadas
            FROM habitaciones h
            LEFT JOIN reservas r
                ON r.habitacion_id = h.id
                AND r.estado != "cancelada"
                AND r.fecha_entrada < ?
                AND r.fecha_salida  > ?
            GROUP BY h.id
        ');
        $stmt->execute([$fecha_salida, $fecha_entrada]);
        $habitaciones = $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
}
?>
<!DOCTYPE html>
<html lang="es">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Hotel API — Disponibilidad</title>
    <link rel="stylesheet" href="css/style.css">
</head>
<body>

    <header>
        <div class="header-inner">
            <h1>🏨 Grand Hotel API</h1>
            <nav>
                <a href="index.php">Reservas</a>
                <a href="disponibilidad.php" class="activo">Disponibilidad</a>
                <a href="reservar.php">Nueva Reserva</a>
            </nav>
        </div>
    </header>

    <main>

        <!-- Buscador de fechas -->
        <section class="form-card" style="max-width:100%; margin-bottom:2rem;">
            <h2>Buscar habitaciones disponibles</h2>
            <form method="GET" action="disponibilidad.php">
                <div class="form-fila">
                    <div class="form-grupo">
                        <label for="entrada">Fecha de entrada</label>
                        <input type="date" id="entrada" name="entrada"
                               value="<?= htmlspecialchars($fecha_entrada) ?>"
                               min="<?= date('Y-m-d') ?>" required>
                    </div>
                    <div class="form-grupo">
                        <label for="salida">Fecha de salida</label>
                        <input type="date" id="salida" name="salida"
                               value="<?= htmlspecialchars($fecha_salida) ?>"
                               min="<?= date('Y-m-d') ?>" required>
                    </div>
                </div>
                <button type="submit" class="btn">Buscar disponibilidad</button>
            </form>
        </section>

        <!-- Resultado -->
        <?php if ($error): ?>
            <div class="alerta error"><?= $error ?></div>

        <?php elseif ($buscado): ?>
            <?php
                $libres   = array_filter($habitaciones, fn($h) => $h['reservas_solapadas'] == 0);
                $ocupadas = array_filter($habitaciones, fn($h) => $h['reservas_solapadas'] >  0);

                // Calcular noches para mostrar precio total
                $noches = (new DateTime($fecha_entrada))->diff(new DateTime($fecha_salida))->days;
            ?>

            <p style="color:#888; margin-bottom:0.5rem;">
                Del <strong style="color:#c9a84c"><?= $fecha_entrada ?></strong>
                al <strong style="color:#c9a84c"><?= $fecha_salida ?></strong>
                — <?= $noches ?> noche<?= $noches > 1 ? 's' : '' ?>
            </p>

            <div class="habitaciones-grid">
                <?php foreach ($habitaciones as $h):
                    $libre = $h['reservas_solapadas'] == 0;
                ?>
                <div class="hab-card <?= $libre ? 'libre' : 'ocupada' ?>">
                    <div class="hab-numero">Hab. <?= htmlspecialchars($h['numero']) ?></div>
                    <div class="hab-tipo"><?= ucfirst($h['tipo']) ?></div>
                    <div class="hab-precio"><?= number_format($h['precio'], 2) ?> € / noche</div>

                    <?php if ($libre): ?>
                        <p style="color:#666; font-size:0.85rem; margin-bottom:1rem;">
                            Total <?= $noches ?> noches:
                            <strong style="color:#c9a84c">
                                <?= number_format($h['precio'] * $noches, 2) ?> €
                            </strong>
                        </p>
                        <a href="reservar.php?habitacion_id=<?= $h['id'] ?>&entrada=<?= $fecha_entrada ?>&salida=<?= $fecha_salida ?>"
                           class="btn" style="display:block; text-align:center;">
                            Reservar
                        </a>
                    <?php else: ?>
                        <p style="color:#e05050; font-size:0.85rem; margin-bottom:1rem;">
                            <?= htmlspecialchars($h['descripcion']) ?>
                        </p>
                    <?php endif; ?>

                    <span class="hab-estado <?= $libre ? 'libre' : 'ocupada' ?>">
                        <?= $libre ? 'Disponible' : 'Ocupada' ?>
                    </span>
                </div>
                <?php endforeach; ?>
            </div>

        <?php endif; ?>

    </main>

    <footer>
        <p>Grand Hotel API &copy; <?= date('Y') ?> — Desarrollado con PHP + PDO + MariaDB</p>
    </footer>

</body>
</html>
