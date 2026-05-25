# RESTful API - Hotel

API REST para gestión de reservas de hotel. Desarrollada en PHP puro con PDO y MariaDB.

## Requisitos

- XAMPP (Apache + MySQL/MariaDB)
- PHP 8.x
- phpMyAdmin

## Instalación

1. Clonar el repositorio en `c:/xampp/htdocs/hotel-api`
2. Importar `db/hotel.sql` en phpMyAdmin
3. Acceder a `http://localhost/hotel-api/api/reservas.php`

## Credenciales de prueba

- Email: `admin@hotel.com`
- Password: `1234`

## Endpoints

### Autenticación

| Método | URL | Descripción |
|--------|-----|-------------|
| POST | `/api/auth.php` | Login → devuelve token |

**Body:**
```json
{ "email": "admin@hotel.com", "password": "1234" }
```

---

### Reservas

| Método | URL | Auth | Descripción |
|--------|-----|------|-------------|
| GET | `/api/reservas.php` | No | Listar todas |
| GET | `/api/reservas.php?id=1` | No | Ver una reserva |
| POST | `/api/reservas.php` | Sí | Crear reserva |
| PUT | `/api/reservas.php` | Sí | Modificar reserva |
| DELETE | `/api/reservas.php` | Sí | Eliminar reserva |

Las peticiones con **Auth: Sí** requieren cabecera:
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
{ "id": 1, "estado": "confirmada" }
```

**Eliminar reserva (DELETE):**
```json
{ "id": 1 }
```
