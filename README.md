# Hotel API

Proyecto de gestión de reservas de hotel hecho en PHP con PDO y MariaDB. Tiene una API REST y una interfaz web para ver y crear reservas.

## Lo que necesitas

- XAMPP (con Apache y MySQL encendidos)
- PHP 8
- phpMyAdmin

## Cómo instalarlo

1. Clona el repositorio en `c:/xampp/htdocs/`
2. Abre phpMyAdmin e importa el archivo `db/hotel.sql`
3. Entra en `http://localhost/RESTful-API-Hotel/public/index.php`

## Usuario de prueba

- Email: `admin@hotel.com`
- Contraseña: `1234`

## Cómo funciona la API

Primero hay que hacer login para obtener el token:

```
POST /api/auth.php
```
```json
{ "email": "admin@hotel.com", "password": "1234" }
```

Con el token ya puedes usar el resto de endpoints:

| Método | URL | Necesita token |
|--------|-----|----------------|
| GET | `/api/reservas.php` | No |
| GET | `/api/reservas.php?id=1` | No |
| POST | `/api/reservas.php` | Sí |
| PUT | `/api/reservas.php` | Sí |
| DELETE | `/api/reservas.php` | Sí |

El token se manda en la cabecera así:
```
Authorization: Bearer <token>
```

## Interfaz web

- `/public/index.php` — panel con todas las reservas
- `/public/disponibilidad.php` — buscar habitaciones libres por fechas
- `/public/reservar.php` — formulario para hacer una reserva
