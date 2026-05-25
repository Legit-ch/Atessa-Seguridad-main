# Atessa Seguridad - Arquitectura Separada Frontend/Backend

La aplicación ha sido refactorizada en una arquitectura de capas clara:

## Estructura

```
frontend/          → HTML, CSS, JS del cliente
backend/           → API REST en PHP
└─ app/            → Lógica de negocio
   ├─ Controllers/ → Reciben peticiones HTTP
   ├─ Services/    → Lógica de negocio
   ├─ Repositories → Acceso a datos (BD)
   └─ Middleware/  → Autenticación, validación
```

## Inicio rápido

### 1. Configurar entorno

```bash
# Copiar plantilla de configuración
cp .env.example .env

# Generar contraseña admin hasheada
php -r "echo password_hash('tuContraseña123', PASSWORD_DEFAULT) . PHP_EOL;"

# Editar .env con tus valores
# Reemplaza ADMIN_PASS_HASH con el hash generado
```

### 2. Verificar permisos

```bash
# Asegurar que el servidor web puede escribir logs/uploads
chmod 755 backend/
chmod 755 backend/app/
```

### 3. Configurar servidor web

**Apache** (mod_rewrite habilitado):
```bash
a2enmod rewrite
systemctl restart apache2
```

**Nginx**:
```nginx
location /backend/public {
    try_files $uri /backend/public/index.php?$query_string;
}
```

### 4. Crear usuario admin en BD

```sql
INSERT INTO admin_users (username, password_hash, email, role) 
VALUES ('admin', '$2y$10$HASH_AQUI', 'admin@example.com', 'admin');
```

Reemplaza `$2y$10$HASH_AQUI` con el hash que generaste.

## Cómo funciona

### Frontend → Backend

1. El HTML llama a `frontend/js/api.js`
2. El cliente JavaScript hace peticiones HTTP a `/backend/public/api/*`
3. El backend router (`backend/routes/api.php`) dirige la petición
4. El controlador valida y llama al servicio
5. El servicio implementa lógica, usa repositorio para acceder a BD
6. Respuesta JSON al frontend

### Ejemplo: Enviar cotización

```javascript
// Frontend (JavaScript)
ApiClient.post('/api/quotes', {
  name: 'Juan',
  email: 'juan@example.com',
  phone: '1234567890',
  property_type: 'Casa',
  service: 'Cámaras',
  urgency: 'Alta',
  message: 'Quiero instalar cámaras'
});
```

Esto invoca:
1. `QuoteController::store()` - recibe datos
2. `QuoteService::create()` - valida, guarda, envía email
3. `QuoteRepository::create()` - inserta en BD
4. Respuesta JSON al frontend

## Estructura del proyecto

```
Atessa-Seguridad-main/
├── frontend/                      # Cliente (HTML/CSS/JS)
│   ├── index.html                 # Página principal
│   ├── css/
│   ├── js/
│   │   └── api.js                 # Cliente HTTP para API
│   └── assets/
│
├── backend/                       # API REST
│   ├── config/
│   │   └── Database.php           # Singleton PDO
│   ├── app/
│   │   ├── Controllers/           # Reciben peticiones
│   │   │   ├── QuoteController.php
│   │   │   ├── ContactController.php
│   │   │   └── AuthController.php
│   │   ├── Services/              # Lógica de negocio
│   │   │   ├── QuoteService.php
│   │   │   ├── ContactService.php
│   │   │   └── AuthService.php
│   │   ├── Repositories/          # Acceso a datos
│   │   │   ├── QuoteRepository.php
│   │   │   ├── ContactRepository.php
│   │   │   └── AdminUserRepository.php
│   │   └── Middleware/            # Autenticación, CSRF
│   │       └── AuthMiddleware.php
│   ├── routes/
│   │   └── api.php                # Definición de rutas
│   └── public/
│       ├── index.php              # Entry point (router)
│       └── .htaccess              # Configuración Apache
│
├── forms/                         # Antiguos (deprecado, reemplazado por backend/)
│   ├── appointment.php            # ← Usar API en su lugar
│   ├── contact.php                # ← Usar API en su lugar
│   └── db.php                     # Configuración BD (compatibilidad)
│
├── .env                           # Variables de entorno (NO en repo)
├── .env.example                   # Plantilla .env
├── .gitignore                     # Excluir .env, vendor/
├── ARCHITECTURE.md                # Documentación de capas
└── README.md                      # Este archivo
```

## Características de seguridad

✅ Credenciales en `.env` (no en código)  
✅ HTTPS requerido en producción  
✅ CSRF tokens en formularios  
✅ Autenticación con sesiones seguras  
✅ Contraseñas hasheadas (bcrypt)  
✅ SQL injection prevenida (prepared statements)  
✅ XSS prevenido (htmlspecialchars, filter_var)  
✅ CORS configurado  

## Migración de formularios existentes

Los formularios antiguos en `forms/` siguen funcionando, pero deberían actualizarse para usar la API:

### Antes (PHP tradicional)

```html
<form action="forms/appointment.php" method="POST">
  <input name="name" required>
  <button>Enviar</button>
</form>
```

### Después (API REST)

```html
<form class="php-email-form" id="quote">
  <input name="name" required>
  <button>Enviar</button>
</form>

<script src="frontend/js/api.js"></script>
```

El cliente JavaScript maneja todo automáticamente.

## Próximos pasos

1. Crear panel de admin con SPA (Vue/React/Alpine)
2. Agregar búsqueda/filtrado en cotizaciones
3. Implementar logs de auditoría
4. Agregar caché (Redis)
5. Tests automatizados (PHPUnit, Jest)
6. Documentación API (OpenAPI/Swagger)

## Troubleshooting

### Error "Ruta no encontrada"
- Verificar que mod_rewrite está habilitado
- Comprobar .htaccess en backend/public/

### Error "HTTPS requerido"
- En producción, desplegar con SSL/TLS
- En desarrollo, desabilitar en `.env`: `APP_ENV=development`

### Error "PDO Exception"
- Verificar variables en `.env`
- Comprobar que MySQL está corriendo
- Crear BD: `php setup.php`

## Documentación completa

Ver [ARCHITECTURE.md](ARCHITECTURE.md) para detalles de rutas, ejemplos de API y configuración.
