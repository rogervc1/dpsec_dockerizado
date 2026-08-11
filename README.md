# DPSEC Web - Direccion de Proyeccion Social y Extension Cultural

Sistema web para la Direccion de Proyeccion Social y Extension Cultural de la Universidad Nacional del Altiplano de Puno.

El proyecto usa Laravel, Inertia.js, Vue 3, TypeScript, Tailwind CSS y Vite. Incluye sitio publico, panel administrativo, gestion de eventos/documentos y modulo de certificados.

## Requisitos

Antes de levantar el proyecto instala:

- PHP 8.3 o superior.
- Composer 2.
- Node.js 22 o superior y npm.
- MySQL 8 o MariaDB compatible.
- Git.

Extensiones PHP recomendadas:

- `pdo_mysql`
- `mbstring`
- `openssl`
- `fileinfo`
- `gd`
- `zip`
- `xml`
- `curl`

En Windows puedes usar Laragon, XAMPP o PHP instalado manualmente. Si usas Laragon, confirma que la terminal usa PHP 8.3+ con:

```bash
php -v
composer -V
node -v
npm -v
```

## Instalacion Local

### 1. Clonar el repositorio

```bash
git clone <url-del-repositorio>
cd oscar_server_DPSEC
```

### 2. Instalar dependencias

```bash
composer install
npm install
```

En PowerShell, si `npm run ...` falla por politica de ejecucion de scripts, usa `npm.cmd`:

```bash
npm.cmd install
```

### 3. Crear el archivo `.env`

```bash
copy .env.example .env
```

En Linux/macOS o Git Bash:

```bash
cp .env.example .env
```

Configura la conexion a base de datos en `.env`:

```env
APP_NAME="DPSEC Web"
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000

DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dpesec
DB_USERNAME=root
DB_PASSWORD=
```

Crea la base de datos `dpesec` en MySQL antes de migrar.

### 4. Generar clave de aplicacion

```bash
php artisan key:generate
```

### 5. Crear tablas y datos iniciales

```bash
php artisan migrate --seed
```

Los seeders cargan datos iniciales para:

- eventos
- documentos
- equipo
- objetivos
- valores institucionales
- videos
- preguntas frecuentes
- estadisticas
- subunidades
- secciones de pagina

Tambien se crea un usuario inicial:

```text
Email: test@example.com
Password: password
```

### 6. Crear enlace de almacenamiento publico

```bash
php artisan storage:link
```

Este paso es necesario para que Laravel sirva archivos subidos desde `storage/app/public` mediante `/storage`.

### 7. Levantar el proyecto

Opcion recomendada, usando el script de Composer:

```bash
composer dev
```

Ese comando levanta en paralelo:

- servidor Laravel (`php artisan serve`)
- cola (`php artisan queue:listen --tries=1`)
- Vite (`npm run dev`)

Si prefieres terminales separadas:

```bash
php artisan serve
php artisan queue:listen --tries=1
npm run dev
```

En PowerShell puedes usar:

```bash
npm.cmd run dev
```

Abre la aplicacion en:

```text
http://127.0.0.1:8000
```

El panel administrativo queda disponible despues de iniciar sesion:

```text
http://127.0.0.1:8000/login
http://127.0.0.1:8000/admin
```

## Rutas Principales

Sitio publico:

- `/` - Inicio
- `/nosotros` - Nosotros
- `/proyeccion-social` - Proyeccion Social
- `/seguimiento-graduado` - Seguimiento al Graduado
- `/documentos` - Documentos de gestion
- `/eventos` - Eventos
- `/certificados` - Busqueda publica de certificados
- `/verificar-certificado/{identifier}` - Verificacion publica de certificados

Administracion:

- `/login` - Acceso
- `/admin` - Panel administrativo
- `/dashboard` - Dashboard protegido

## Comandos Utiles

Frontend:

```bash
npm run dev
npm run build
npm run lint:check
npm run format:check
npm run types:check
```

En PowerShell:

```bash
npm.cmd run build
npm.cmd run lint:check
```

Backend:

```bash
php artisan migrate
php artisan migrate:fresh --seed
php artisan storage:link
php artisan optimize:clear
php artisan test
composer lint:check
composer types:check
composer test
```

Nota: `npm run lint` y `npm run format` reescriben archivos. Para solo validar, usa `npm run lint:check` y `npm run format:check`.

## Verificacion Antes de Entregar Cambios

Ejecuta al menos:

```bash
npm run build
npm run lint:check
php artisan test
```

Si necesitas revision estatica completa:

```bash
npm run types:check
composer types:check
```

Actualmente `npm run types:check` puede reportar errores TypeScript en `resources/js/components/CertificateTab.vue`. Si esos errores siguen presentes, revisalos antes de exigir ese comando como condicion de CI.

## Estructura Importante

```text
app/                         Controladores, modelos y logica Laravel
database/migrations/         Migraciones de base de datos
database/seeders/            Datos iniciales
resources/js/                Aplicacion Vue/Inertia
resources/js/pages/public/   Paginas publicas
resources/js/pages/auth/     Pantallas de autenticacion
resources/js/components/     Componentes reutilizables
resources/views/             Vistas Blade base y PDF
routes/web.php               Rutas publicas principales
routes/admin.php             Rutas administrativas
public/build/                Assets compilados por Vite
storage/app/public/          Archivos publicos subidos
```

Archivos publicos relevantes:

- `resources/js/layouts/PublicLayout.vue`: layout publico, navegacion y footer.
- `resources/js/pages/public/Home.vue`: pagina de inicio.
- `resources/js/pages/public/AboutUs.vue`: pagina "Nosotros".
- `resources/js/pages/public/Eventos.vue`: listado publico de eventos.
- `resources/js/pages/public/Documentos.vue`: listado publico de documentos.
- `resources/js/pages/public/ProyeccionSocial.vue`: pagina de subunidad.
- `resources/js/pages/public/SeguimientoGraduado.vue`: pagina de seguimiento al graduado.
- `resources/js/pages/public/Certificados.vue`: busqueda publica de certificados.

## Flujo con Docker

El repositorio incluye `Dockerfile` y `docker-compose.yml`.

### 1. Preparar `.env`

```bash
copy .env.example .env
php artisan key:generate --show
```

Copia la clave generada en `APP_KEY` dentro de `.env`.

Para Docker usa valores compatibles con el servicio `db`:

```env
APP_ENV=production
APP_DEBUG=false
APP_URL=http://localhost:8090

DB_CONNECTION=mysql
DB_HOST=db
DB_PORT=3306
DB_DATABASE=dpesec
DB_USERNAME=laravel
DB_PASSWORD=laravel_secret
DB_ROOT_PASSWORD=root_secret
```

### 2. Construir y levantar servicios

```bash
docker compose up -d --build
```

La aplicacion queda en:

```text
http://localhost:8090
```

### 3. Ejecutar migraciones dentro del contenedor

```bash
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
```

Para ver logs:

```bash
docker compose logs -f app
docker compose logs -f db
```

Para detener:

```bash
docker compose down
```

Para eliminar tambien la base de datos Docker:

```bash
docker compose down -v
```

## Solucion de Problemas

### `npm.ps1 cannot be loaded`

En PowerShell, Windows puede bloquear `npm.ps1`. Usa:

```bash
npm.cmd run dev
npm.cmd run build
```

Tambien puedes usar CMD, Git Bash o ajustar la politica de ejecucion de PowerShell si tu entorno lo permite.

### Error de conexion a MySQL

Verifica:

- que MySQL este iniciado
- que exista la base de datos `dpesec`
- que `DB_HOST`, `DB_PORT`, `DB_DATABASE`, `DB_USERNAME` y `DB_PASSWORD` coincidan con tu entorno

Despues de cambiar `.env`, limpia cache:

```bash
php artisan optimize:clear
```

### No cargan imagenes o documentos subidos

Ejecuta:

```bash
php artisan storage:link
```

Confirma que exista:

```text
public/storage -> storage/app/public
```

### Cambios Vue no aparecen

Confirma que Vite este corriendo:

```bash
npm run dev
```

Si estas en produccion o no usas Vite dev server, recompila:

```bash
npm run build
```

### Reiniciar base de datos local

Esto borra y recrea todas las tablas con datos iniciales:

```bash
php artisan migrate:fresh --seed
```

## Notas para Colaboradores

- No subas `.env`, `vendor/`, `node_modules/`, `public/hot` ni archivos temporales.
- Evita editar directamente `public/build`; se genera con `npm run build`.
- Si agregas uploads publicos, deben vivir bajo `storage/app/public` y servirse con `php artisan storage:link`.
- Antes de abrir un PR, revisa `git status` y deja fuera cambios generados que no correspondan.
