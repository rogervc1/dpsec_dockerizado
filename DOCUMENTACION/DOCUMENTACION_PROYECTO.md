# DocumentaciÃ³n tÃ©cnica del proyecto DPSEC Web

**Proyecto:** DPSEC Web â€“ DirecciÃ³n de ProyecciÃ³n Social y ExtensiÃ³n Cultural  
**InstituciÃ³n:** Universidad Nacional del Altiplano de Puno  
**Tipo:** AplicaciÃ³n web institucional con sitio pÃºblico e intranet administrativa  
**RevisiÃ³n:** 30 de septiembre de 2026  
**Directorio:** oscar_server_DPSEC

## 1. PropÃ³sito y alcance

DPSEC Web publica informaciÃ³n institucional y ofrece una intranet para administrar eventos, documentos y certificados. Incluye pÃ¡ginas sobre la instituciÃ³n, proyecciÃ³n social y seguimiento al graduado.

Funcionalidades implementadas:

- Sitio pÃºblico responsive con Vue 3 e Inertia.
- CRUD de eventos con clasificaciÃ³n, fechas, enlaces, galerÃ­a y portada.
- CRUD de documentos de gestiÃ³n y descarga pÃºblica.
- Consulta, verificaciÃ³n y descarga de certificados PDF.
- ImportaciÃ³n masiva de certificados desde CSV/TXT.
- Plantillas con fondos, posiciones, colores y tipografÃ­as personalizadas.
- AutenticaciÃ³n Fortify: verificaciÃ³n de correo, recuperaciÃ³n, 2FA y passkeys.
- Perfil, contraseÃ±a y apariencia.
- EjecuciÃ³n local con Vite y despliegue Docker con PHP-FPM/Nginx y MySQL.

No existe un CRUD administrativo para equipo, objetivos, valores, videos, preguntas frecuentes, estadÃ­sticas, subunidades ni secciones de pÃ¡ginas; esos contenidos se cargan mediante seeders o base de datos.

## 2. Arquitectura

La soluciÃ³n es un monolito Laravel con frontend Vue/Inertia:

~~~text
Navegador
  â”œâ”€ Sitio pÃºblico Vue 3
  â””â”€ Intranet Vue 3
       â”‚ Inertia / HTTP
       â–¼
Laravel 13
  â”œâ”€ Rutas, Fortify y controladores
  â”œâ”€ Modelos Eloquent y validaciÃ³n
  â”œâ”€ MySQL: contenido, usuarios, sesiones, cachÃ© y colas
  â”œâ”€ storage/app/public: archivos subidos
  â””â”€ Dompdf: certificados PDF
~~~

### TecnologÃ­as

| Capa | TecnologÃ­a |
|---|---|
| Backend | PHP 8.3+, Laravel 13.17+ |
| Frontend | Vue 3.5, TypeScript, Inertia.js |
| Estilos | Tailwind CSS 4 |
| Build | Vite 8, npm |
| Base de datos | MySQL 8 o MariaDB compatible |
| AutenticaciÃ³n | Laravel Fortify, 2FA, passkeys |
| PDF | barryvdh/laravel-dompdf |
| Calidad | Pest, Pint, Larastan/PHPStan, ESLint, Prettier, vue-tsc |
| Despliegue | Docker Compose, PHP-FPM/Nginx |

## 3. Estructura del repositorio

~~~text
app/                    Controladores, modelos, middleware y providers
bootstrap/               Arranque de Laravel
config/                  ConfiguraciÃ³n
database/migrations/     EvoluciÃ³n del esquema
database/seeders/        Datos iniciales
docker/                  Script de inicio de producciÃ³n
public/                  Entrada HTTP, favicon y build
resources/js/            Componentes, layouts, pÃ¡ginas y utilidades Vue
resources/views/         Vista Inertia y plantilla PDF
routes/                  Rutas pÃºblicas, admin y settings
storage/                 Logs, cachÃ©s, sesiones y archivos
tests/                   Pruebas Feature y Unit
assets/                  ImÃ¡genes de referencia
~~~

Archivos principales:

- routes/web.php: pÃ¡ginas pÃºblicas, certificados y dashboard.
- routes/admin.php: operaciones de eventos, documentos y certificados.
- app/Http/Controllers/Admin/AdminDashboardController.php: datos del panel.
- resources/js/pages/Dashboard.vue: interfaz administrativa.
- resources/js/components/CertificateTab.vue: administraciÃ³n de certificados.
- app/Http/Controllers/PublicCertificateController.php: bÃºsqueda, verificaciÃ³n y PDF.
- resources/views/pdf/certificate.blade.php: composiciÃ³n del certificado.
- database/seeders/DatabaseSeeder.php: carga inicial.

## 4. MÃ³dulos pÃºblicos

| URL | Controlador/pÃ¡gina | FunciÃ³n |
|---|---|---|
| / | HomeController / public/Home.vue | Hero, estadÃ­sticas, eventos, documentos, videos y subunidades |
| /nosotros | AboutUsController / public/AboutUs.vue | MisiÃ³n, equipo, objetivos y valores |
| /proyeccion-social | ProyeccionSocialController / public/ProyeccionSocial.vue | Eventos de proyecciÃ³n social y FAQ |
| /seguimiento-graduado | SeguimientoGraduadoController / public/SeguimientoGraduado.vue | InformaciÃ³n del mÃ³dulo; portal en desarrollo |
| /documentos | DocumentosController / public/Documentos.vue | Documentos activos |
| /eventos | EventosController / public/Eventos.vue | Eventos publicados |
| /certificados | PublicCertificateController / public/Certificados.vue | BÃºsqueda por DNI/documento |
| /verificar-certificado/{identifier} | PublicCertificateController / public/VerificarCertificado.vue | VerificaciÃ³n por UUID o cÃ³digo |

Home carga seis eventos, tres documentos activos y el resto de contenido activo. Las secciones textuales se almacenan en page_sections y se agrupan por page_slug y section_key.

## 5. Panel administrativo

/dashboard y /admin requieren auth y verified. El panel tiene resumen, eventos, documentos y certificados. El resumen calcula eventos, documentos, eventos de proyecciÃ³n social, certificados y plantillas. Las bÃºsquedas de eventos/documentos se ejecutan en el navegador.

### Eventos

Campos obligatorios: title, type, category, event_date, location, organizer y description. Opcionales: time, enlaces, imÃ¡genes y sort_order.

- Acepta hasta 10 imÃ¡genes, 5 MB por archivo.
- Extensiones aceptadas: JPG, JPEG, PNG y WebP.
- La interfaz crea una portada WebP 800Ã—450 con recorte 16:9.
- image_path es la imagen principal, event_images la galerÃ­a y cover_image_path la portada.
- is_proyeccion_social determina si aparece en la pÃ¡gina de proyecciÃ³n social.
- El estado se calcula segÃºn la fecha: Proximos, EnCurso o Pasados.

### Documentos

Al crear requiere tÃ­tulo, cÃ³digo Ãºnico, categorÃ­a, fecha y archivo. El archivo admite 50 MB. is_active controla la visibilidad pÃºblica y sort_order la posiciÃ³n. Al reemplazar o eliminar se borra el archivo local anterior.

### Certificados

Tiene cuatro subÃ¡reas:

1. Plantillas: fondo JPG/JPEG/PNG de hasta 5 MB y configuraciÃ³n JSON.
2. TipografÃ­as: archivos TTF de hasta 10 MB.
3. ImportaciÃ³n: CSV/TXT de hasta 5 MB, plantilla y fecha.
4. Registros: paginaciÃ³n de 15 y filtro por plantilla.

La importaciÃ³n detecta coma o punto y coma, elimina BOM y reconoce nombre/name, dni/documento/codigo/document, rol/role y email/correo. Omite filas vacÃ­as, sin nombre/documento y duplicados de documento dentro de la misma plantilla.

## 6. Rutas HTTP

### AdministraciÃ³n

Todas requieren auth y verified.

| MÃ©todo | Ruta | OperaciÃ³n |
|---|---|---|
| POST | /admin/eventos | Crear evento |
| PUT | /admin/eventos/{event} | Actualizar evento |
| DELETE | /admin/eventos/{event} | Eliminar evento |
| POST | /admin/documentos | Crear documento |
| PUT | /admin/documentos/{document} | Actualizar documento |
| DELETE | /admin/documentos/{document} | Eliminar documento |
| POST | /admin/certificados/plantilla | Crear plantilla |
| POST | /admin/certificados/plantilla/{template} | Actualizar plantilla |
| DELETE | /admin/certificados/plantilla/{template} | Eliminar plantilla y certificados asociados |
| POST | /admin/certificados/tipografia | Subir fuente |
| DELETE | /admin/certificados/tipografia/{font} | Eliminar fuente |
| POST | /admin/certificados/importar | Importar participantes |
| DELETE | /admin/certificados/{certificate} | Eliminar certificado |

### ConfiguraciÃ³n y autenticaciÃ³n

Fortify registra login, logout, registro, verificaciÃ³n de correo, recuperaciÃ³n, 2FA y passkeys. AdemÃ¡s:

- GET/PATCH /settings/profile: consultar y guardar perfil.
- DELETE /settings/profile: eliminar cuenta.
- GET /settings/security: seguridad, con contraseÃ±a confirmada.
- PUT /settings/password: cambio de contraseÃ±a, throttle 6/min.
- GET /settings/appearance: apariencia.

## 7. Modelo de datos

| Tabla | Contenido |
|---|---|
| events | tÃ­tulo, tipo, categorÃ­a, fecha, lugar, organizador, descripciÃ³n, imÃ¡genes, enlaces, estado y orden |
| documents | tÃ­tulo, cÃ³digo Ãºnico, categorÃ­a, fecha, tamaÃ±o, descripciÃ³n, ruta, activo y orden |
| team_members | equipo, cargo, dependencia, iniciales, imagen, activo y orden |
| objectives | objetivos, descripciÃ³n, icono, clases visuales, activo y orden |
| institutional_values | valores, descripciÃ³n, icono, clases visuales, activo y orden |
| videos | tÃ­tulo, descripciÃ³n, YouTube, embed, miniatura, activo y orden |
| faqs | pÃ¡gina, pregunta, respuesta, activo y orden |
| statistics | etiqueta, valor, descripciÃ³n, icono, clase, activo y orden |
| sub_units | nombre, descripciÃ³n, enlace, externo, logo, Facebook, activo y orden |
| page_sections | pÃ¡gina, clave Ãºnica, textos, fondo y extra_data JSON |
| users | nombre, correo, contraseÃ±a, verificaciÃ³n, 2FA y sesiÃ³n |
| passkeys | credenciales WebAuthn asociadas a usuarios |
| certificate_fonts | nombre y ruta TTF |
| certificate_templates | nombre, fondo y settings JSON |
| certificates | plantilla, UUID, cÃ³digo, destinatario, documento, correo, rol y fecha |

Relaciones: una plantilla tiene muchos certificados; al borrar una plantilla, la clave forÃ¡nea elimina sus certificados en cascada. Certificate genera UUID y cÃ³digo DPSEC-AAAA-XXXXXX. Los scopes active() filtran contenido pÃºblico. sort_order ordena la presentaciÃ³n.

## 8. Almacenamiento

~~~text
storage/app/public/events/
storage/app/public/documents/
storage/app/public/certificate_templates/
storage/app/public/fonts/
~~~

Debe crearse el enlace pÃºblico:

~~~bash
php artisan storage:link
~~~

El PDF busca fondos y fuentes en storage/app/public y luego en public/storage. Las URL externas no se borran al eliminar registros.

## 9. InstalaciÃ³n local

Requisitos: PHP 8.3+, Composer 2, Node 22+, npm, MySQL 8/MariaDB y extensiones pdo_mysql, mbstring, openssl, fileinfo, gd, zip, xml y curl.

~~~bash
composer install
copy .env.example .env
php artisan key:generate
npm install
php artisan migrate --seed
php artisan storage:link
composer dev
~~~

En PowerShell puede utilizarse npm.cmd si npm.ps1 estÃ¡ bloqueado. La aplicaciÃ³n queda en http://127.0.0.1:8000.

Variables esenciales:

~~~env
APP_ENV=local
APP_DEBUG=true
APP_URL=http://127.0.0.1:8000
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dpesec
DB_USERNAME=root
DB_PASSWORD=
SESSION_DRIVER=database
CACHE_STORE=database
QUEUE_CONNECTION=database
~~~

composer dev ejecuta servidor Laravel, queue:listen y Vite. Como alternativa se pueden ejecutar por separado php artisan serve, php artisan queue:listen --tries=1 y npm run dev.

Los seeders cargan eventos, documentos, equipo, objetivos, valores, videos, FAQ, estadÃ­sticas, subunidades y secciones. El README documenta el usuario de prueba test@example.com / password; no usarlo en producciÃ³n.

## 10. Docker

El Dockerfile compila dependencias y frontend en una primera etapa y sirve con serversideup/php:8.3-fpm-nginx-alpine. Docker Compose crea app en localhost:8090, MySQL 8, y volÃºmenes db_data y storage_data.

~~~bash
docker compose up -d --build
docker compose exec app php artisan migrate --seed
docker compose exec app php artisan storage:link
docker compose logs -f app
docker compose down
~~~

docker compose down -v elimina los volÃºmenes de base de datos y almacenamiento: es destructivo. ProducciÃ³n debe usar APP_DEBUG=false, APP_KEY real, HTTPS, contraseÃ±as seguras, correo y copias de seguridad.

## 11. Pruebas y calidad

~~~bash
npm run build
npm run lint:check
npm run format:check
npm run types:check
php artisan test
composer lint:check
composer types:check
composer test
~~~

VerificaciÃ³n realizada el 30/09/2026:

- php artisan test: 39 pruebas aprobadas y 136 aserciones.
- php artisan route:list: 68 rutas registradas.
- Las pruebas cubren autenticaciÃ³n, correo, contraseÃ±a, registro, 2FA, dashboard, perfil y seguridad.

## 12. Riesgos y observaciones

1. Registro abierto: Fortify tiene activa Features::registration(). Cualquier persona puede crear una cuenta y las rutas administrativas solo exigen auth + verified; no existe rol de administrador. En producciÃ³n conviene cerrar el registro o implementar roles/policies.
2. Certificados pÃºblicos: bÃºsqueda, verificaciÃ³n y descarga estÃ¡n expuestas intencionalmente, pero debe revisarse la privacidad del DNI.
3. El PDF se genera bajo demanda y puede consumir CPU/memoria con fuentes incrustadas.
4. Algunas cargas validan extensiÃ³n manualmente; conviene complementar con MIME real, dimensiones y antivirus.
5. Eliminar una fuente usada por una plantilla puede causar fallback en el PDF.
6. status, status_label y status_color existen en events y tambiÃ©n son atributos derivados; la fecha es la fuente real.
7. No hay CRUD para todos los modelos de contenido.
8. Varias imÃ¡genes de seeders son remotas y podrÃ­an dejar de funcionar.
9. Hay textos con mojibake (Ãƒ, Ã‚) en algunos archivos; normalizar todo a UTF-8.
10. Passkeys depende de que APP_URL coincida exactamente con el dominio HTTPS.
11. Respaldar MySQL y storage_data juntos: certificados dependen de plantillas y fondos.

## 13. Despliegue recomendado

Configurar secretos y dominio, restringir registro, ejecutar migraciones con --force, crear storage link, revisar permisos, crear solo usuarios autorizados, activar HTTPS, probar login/evento/documento/certificado/PDF, y configurar backups y logs.



