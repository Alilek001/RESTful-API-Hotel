-- Base de datos Hotel API
CREATE DATABASE IF NOT EXISTS hotel_api CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE hotel_api;

-- Tabla de usuarios (para autenticación)
CREATE TABLE usuarios (
    id INT AUTO_INCREMENT PRIMARY KEY,
    nombre VARCHAR(100) NOT NULL,
    email VARCHAR(100) NOT NULL UNIQUE,
    password VARCHAR(255) NOT NULL,
    token VARCHAR(64) DEFAULT NULL,
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
);

-- Tabla de habitaciones
CREATE TABLE habitaciones (
    id INT AUTO_INCREMENT PRIMARY KEY,
    numero VARCHAR(10) NOT NULL UNIQUE,
    tipo ENUM('individual','doble','suite') NOT NULL,
    precio DECIMAL(8,2) NOT NULL,
    descripcion VARCHAR(255)
);

-- Tabla de reservas
CREATE TABLE reservas (
    id INT AUTO_INCREMENT PRIMARY KEY,
    usuario_id INT NOT NULL,
    habitacion_id INT NOT NULL,
    cliente_nombre VARCHAR(100) NOT NULL,
    cliente_email VARCHAR(100) NOT NULL,
    fecha_entrada DATE NOT NULL,
    fecha_salida DATE NOT NULL,
    estado ENUM('pendiente','confirmada','cancelada') DEFAULT 'pendiente',
    created_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
    FOREIGN KEY (usuario_id) REFERENCES usuarios(id),
    FOREIGN KEY (habitacion_id) REFERENCES habitaciones(id)
);

-- Usuario de prueba: password = "1234"
INSERT INTO usuarios (nombre, email, password) VALUES
('Admin Hotel', 'admin@hotel.com', '$2y$10$92IXUNpkjO0rOQ5byMi.Ye4oKoEa3Ro9llC/.og/at2.uheWG/igi');

-- Habitaciones de ejemplo
INSERT INTO habitaciones (numero, tipo, precio, descripcion) VALUES
('101', 'individual', 55.00, 'Habitación individual con baño privado'),
('102', 'doble', 85.00, 'Habitación doble con vista al jardín'),
('201', 'suite', 150.00, 'Suite con salón y jacuzzi');

-- Reservas de ejemplo
INSERT INTO reservas (usuario_id, habitacion_id, cliente_nombre, cliente_email, fecha_entrada, fecha_salida, estado) VALUES
(1, 1, 'Juan García', 'juan@ejemplo.com', '2026-06-01', '2026-06-05', 'confirmada'),
(1, 2, 'María López', 'maria@ejemplo.com', '2026-06-10', '2026-06-15', 'pendiente');
