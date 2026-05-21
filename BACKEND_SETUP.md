# Backend y Base de Datos para Atessa Technologies

Este sitio ahora incluye una capa de backend PHP y persistencia en base de datos MySQL.

## Archivos agregados

- `forms/db.php` - conexión PDO y creación automática de tablas.
- `admin.php` - panel básico de administración protegido con autenticación básica HTTP.
- `database.sql` - script SQL para crear la base de datos y las tablas manualmente si lo prefieres.

## Cómo usarlo

1. Asegúrate de tener un servidor PHP con MySQL o MariaDB.
2. Configura los datos de conexión en `forms/db.php`:
   - `DB_HOST`
   - `DB_NAME`
   - `DB_USER`
   - `DB_PASS`
3. Si usas un servidor con permisos limitados, crea la base de datos manualmente con `database.sql`.
4. Abre `admin.php` en el navegador para ver las solicitudes guardadas.

## Credenciales de administrador

- Usuario: `admin`
- Contraseña: `Atessa123!`

> Cambia estas credenciales a valores seguros antes de usar el sitio en producción.

## Qué guarda la base de datos

- Formulario de cotización: `quotes`
- Formulario de contacto: `contacts`

## Comportamiento

- El sistema guarda los envíos en la base de datos.
- También envía el correo como antes.
- Si el correo falla, el registro se guarda igualmente cuando la base de datos está disponible.
