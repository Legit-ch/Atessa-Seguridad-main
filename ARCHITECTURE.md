# Documentación de la API REST de Atessa Technologies

## Estructura de directorios

```
Atessa-Seguridad-main/
├── frontend/              # Cliente HTML/CSS/JS
│   └── js/api.js         # Cliente HTTP para consumir API
├── backend/              # Servidor API REST
│   ├── config/           # Configuración
│   │   └── Database.php  # Conexión a BD
│   ├── app/
│   │   ├── Controllers/  # Controladores (reciben peticiones)
│   │   ├── Services/     # Lógica de negocio
│   │   ├── Repositories/ # Acceso a datos
│   │   └── Middleware/   # Autenticación, validación
│   ├── routes/           # Definición de rutas
│   │   └── api.php
│   └── public/           # Entry point
│       ├── index.php     # Router principal
│       └── .htaccess     # Configuración Apache
├── .env                  # Variables de entorno (no en repo)
├── .env.example          # Plantilla .env
└── .gitignore            # Excluir .env, vendor, etc.
```

## Configuración

### 1. Crear archivo `.env` en la raíz del proyecto

```bash
cp .env.example .env
```

Editar `.env` con tus valores:
```env
DB_HOST=127.0.0.1
DB_NAME=atessa_security
DB_USER=tu_usuario
DB_PASS=tu_contraseña
DB_CHARSET=utf8mb4

ADMIN_USER=admin
ADMIN_PASS_HASH=$2y$...  # Generado con: php -r "echo password_hash('password', PASSWORD_DEFAULT);"
ADMIN_EMAIL=info@atessatechnologies.com

APP_ENV=development        # development o production
APP_CORS_ORIGIN=http://localhost
```

### 2. Generar hash para admin

```bash
php -r "echo password_hash('TU_PASSWORD', PASSWORD_DEFAULT) . PHP_EOL;"
```

### 3. Configurar servidor web

#### Apache

Crear `.htaccess` en `backend/public/` (ya incluido):

```apache
<IfModule mod_rewrite.c>
    RewriteEngine On
    RewriteCond %{REQUEST_FILENAME} !-f
    RewriteCond %{REQUEST_FILENAME} !-d
    RewriteRule ^ index.php [QSA,L]
</IfModule>
```

Asegurar que `mod_rewrite` está habilitado:
```bash
a2enmod rewrite
systemctl restart apache2
```

#### Nginx

```nginx
location /backend/public {
    try_files $uri /backend/public/index.php?$query_string;
}
```

## Rutas de la API

### Rutas públicas (sin autenticación)

#### Crear cotización
```
POST /backend/public/api/quotes
Content-Type: application/json

{
  "name": "Juan Pérez",
  "email": "juan@example.com",
  "phone": "1234567890",
  "property_type": "Casa",
  "service": "Cámaras de vigilancia",
  "urgency": "Media",
  "message": "Quisiera instalar cámaras en mi casa"
}

Response:
{
  "success": true,
  "message": "Su solicitud ha sido recibida.",
  "data": {
    "id": 1,
    "name": "Juan Pérez",
    ...
  }
}
```

#### Crear contacto
```
POST /backend/public/api/contacts
Content-Type: application/json

{
  "name": "María García",
  "email": "maria@example.com",
  "subject": "Consulta sobre servicios",
  "message": "¿Cuál es el precio de instalación?"
}

Response:
{
  "success": true,
  "message": "Su mensaje ha sido recibido.",
  "data": { ... }
}
```

#### Login
```
POST /backend/public/api/auth/login
Content-Type: application/json

{
  "username": "admin",
  "password": "tu_contraseña"
}

Response:
{
  "success": true,
  "user": {
    "id": 1,
    "username": "admin",
    "email": "info@atessatechnologies.com",
    "role": "admin"
  }
}
```

### Rutas protegidas (requieren autenticación)

#### Obtener todas las cotizaciones
```
GET /backend/public/api/quotes
Authorization: Bearer TOKEN_DE_SESION

Response:
{
  "success": true,
  "data": [
    { id: 1, name: "Juan Pérez", ... },
    ...
  ]
}
```

#### Obtener todos los contactos
```
GET /backend/public/api/contacts
Authorization: Bearer TOKEN_DE_SESION

Response:
{
  "success": true,
  "data": [ ... ]
}
```

#### Obtener usuario actual
```
GET /backend/public/api/auth/user
Authorization: Bearer TOKEN_DE_SESION

Response:
{
  "success": true,
  "data": {
    "id": 1,
    "username": "admin",
    "email": "info@atessatechnologies.com",
    "role": "admin"
  }
}
```

## Cliente JavaScript (Frontend)

### Incluir en el HTML

```html
<script src="frontend/js/api.js"></script>
```

### Usar en formularios

El cliente se inicializa automáticamente y busca formularios con clase `php-email-form`.

#### Ejemplos de uso manual

```javascript
// Crear cotización
ApiClient.post('/api/quotes', {
  name: 'Juan',
  email: 'juan@example.com',
  phone: '1234567890',
  property_type: 'Casa',
  service: 'Cámaras',
  urgency: 'Media',
  message: 'Mensaje de prueba'
}).then(response => {
  console.log('Éxito:', response);
}).catch(error => {
  console.error('Error:', error);
});

// Obtener cotizaciones (requiere autenticación)
ApiClient.get('/api/quotes')
  .then(response => console.log(response.data))
  .catch(error => console.error(error));
```

## Arquitectura de capas

```
Frontend (HTML/CSS/JS)
    ↓ (HTTP Request)
    → Backend API (index.php)
    → Router (routes/api.php)
    → Controller (recibe y valida entrada)
    → Service (lógica de negocio)
    → Repository (accede a BD)
    ↓ (HTTP Response JSON)
    ← Frontend procesa respuesta
```

### Ejemplo: Crear cotización

1. **Frontend** (`frontend/js/api.js`): Recibe formulario, valida, envía POST a `/api/quotes`
2. **Router** (`routes/api.php`): Detecta ruta POST /api/quotes
3. **Controller** (`QuoteController.php`): Obtiene datos, llama a servicio
4. **Service** (`QuoteService.php`): Valida datos, guarda en BD, envía email
5. **Repository** (`QuoteRepository.php`): Inserta en tabla `quotes`
6. **Respuesta**: JSON con éxito/error

## Seguridad

- ✅ Credenciales en `.env` (no en código fuente)
- ✅ HTTPS requerido en producción
- ✅ CSRF tokens en formularios
- ✅ Autenticación con sesiones seguras
- ✅ Contraseñas hasheadas con bcrypt
- ✅ SQL injection prevenida con prepared statements
- ✅ XSS prevenido con htmlspecialchars/filter_var
- ✅ CORS configurado

## Próximos pasos

1. Adaptar HTML para incluir `frontend/js/api.js`
2. Cambiar formularios para usar API en lugar de POST directo
3. Crear panel de admin en SPA (Single Page Application) con Vue/React/Alpine
4. Agregar más validaciones y reglas de negocio en Services
5. Implementar logs de auditoría en Repositories
6. Agregar caché (Redis) para peticiones frecuentes
