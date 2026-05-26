# Hotel API — Sistema de gestión de reservas

API REST para la gestión de reservas de hotel, desarrollada en PHP puro con PDO y MariaDB. Incluye una interfaz web para gestionar reservas, consultar disponibilidad y realizar nuevas reservas.

---

## Tecnologías utilizadas

- PHP 8.x (sin frameworks)
- MariaDB + PDO
- Apache (XAMPP)
- HTML + CSS

---

## Requisitos

- XAMPP con Apache y MySQL/MariaDB activos
- PHP 8.x
- phpMyAdmin

---

## Instalación

1. Clona el repositorio en `c:/xampp/htdocs/`:
   ```
   git clone https://github.com/Alilek001/RESTful-API-Hotel
   ```
2. Importa el archivo `db/hotel.sql` en phpMyAdmin — crea la base de datos y los datos de ejemplo automáticamente.
3. Accede a `http://localhost/RESTful-API-Hotel/public/index.php`

---

## Credenciales de prueba

| Campo | Valor |
|-------|-------|
| Email | `admin@hotel.com` |
| Contraseña | `1234` |

---

## Estructura del proyecto

```
RESTful-API-Hotel/
├── api/
│   ├── auth.php        # Login y generación de token
│   └── reservas.php    # CRUD de reservas
├── config/
│   └── database.php    # Conexión a la base de datos
├── db/
│   └── hotel.sql       # Script SQL con la base de datos
└── public/
    ├── index.php        # Panel de reservas
    ├── disponibilidad.php  # Buscador de disponibilidad por fechas
    ├── reservar.php     # Formulario de nueva reserva
    └── css/
        └── style.css
```

---

## Endpoints de la API

### Autenticación

| Método | URL | Descripción |
|--------|-----|-------------|
| POST | `/api/auth.php` | Login — devuelve token |

**Body:**
```json
{
  "email": "admin@hotel.com",
  "password": "1234"
}
```

**Respuesta:**
```json
{
  "mensaje": "Login correcto",
  "token": "...",
  "usuario": "Admin Hotel"
}
```

---

### Reservas

| Método | URL | Auth | Descripción |
|--------|-----|------|-------------|
| GET | `/api/reservas.php` | No | Listar todas las reservas |
| GET | `/api/reservas.php?id=1` | No | Ver una reserva concreta |
| POST | `/api/reservas.php` | Sí | Crear una reserva nueva |
| PUT | `/api/reservas.php` | Sí | Modificar una reserva |
| DELETE | `/api/reservas.php` | Sí | Eliminar una reserva |

Las peticiones marcadas con **Auth: Sí** requieren la siguiente cabecera:
```
Authorization: Bearer <token>
```

**Crear reserva (POST):**
```json
{
  "habitacion_id": 1,
  "cliente_nombre": "Juan García",
  "cliente_email": "juan@ejemplo.com",
  "fecha_entrada": "2026-07-01",
  "fecha_salida": "2026-07-05"
}
```

**Modificar reserva (PUT):**
```json
{
  "id": 1,
  "estado": "confirmada"
}
```

**Eliminar reserva (DELETE):**
```json
{
  "id": 1
}
```

---

## Interfaz web

| Página | URL | Descripción |
|--------|-----|-------------|
| Panel de reservas | `/public/index.php` | Lista todas las reservas con contadores |
| Disponibilidad | `/public/disponibilidad.php` | Busca habitaciones libres por fechas |
| Nueva reserva | `/public/reservar.php` | Formulario para crear una reserva |
