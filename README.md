# DPESEC Web — Dirección de Proyección Social y Extensión Cultural (UNA Puno)

Este es el sistema web oficial para la **Dirección de Proyección Social y Extensión Cultural (DPESEC)** de la Universidad Nacional del Altiplano (UNA Puno). El proyecto está construido bajo una arquitectura moderna utilizando Laravel como Backend e Inertia.js con Vue 3 en el Frontend.

---

## 🛠️ Stack Tecnológico
* **Backend**: Laravel 11+
* **Frontend**: Vue 3 (Composition API con `<script setup lang="ts">`)
* **Puente**: Inertia.js (SPA de alto rendimiento sin recargas de página)
* **Estilos**: Tailwind CSS con base en Shadcn UI / Radix UI
* **Lenguaje**: TypeScript
* **Compilador**: Vite

---

## ⚙️ Guía de Inicialización del Proyecto

Sigue estos pasos para instalar y ejecutar el proyecto en tu entorno local (ej. Laragon o servidor apache local):

### 1. Clonar e Instalar Dependencias
Instala los paquetes de PHP y las dependencias de Node.js en la raíz del proyecto:
```bash
# Instalar dependencias de PHP
composer install

# Instalar dependencias de Javascript
npm install
```

### 2. Configurar el Entorno (.env)
Duplica el archivo `.env.example` y renómbralo a `.env`:
```bash
copy .env.example .env
```

Abre el archivo `.env` y configura tu base de datos:
```env
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=dpesec
DB_USERNAME=root
DB_PASSWORD=
```

### 3. Generar la Clave de la Aplicación
```bash
php artisan key:generate
```

### 4. Ejecutar Migraciones
Crea la base de datos `dpesec` en tu gestor (ej. phpMyAdmin / HeidiSQL) y corre las migraciones:
```bash
php artisan migrate
```

### 5. Levantar Servidores de Desarrollo
Debes ejecutar ambos comandos en terminales separadas para habilitar el Hot Reload en caliente:
```bash
# Iniciar servidor local PHP (Laravel)
php artisan serve

# Iniciar servidor local de compilación (Vite + Vue 3)
npm run dev
```

---

## 📂 Estructura Clave del Frontend

Toda la lógica de vistas del sitio público y administrativo se encuentra estructurada en `resources/js/`:

* **`resources/js/pages/public/` (Vistas Públicas)**:
  * [Home.vue](file:///D:/Program_Files/laragon/www/Dsesign_Web/dpesec/resources/js/pages/public/Home.vue): Página de inicio. Incluye un carrusel dinámico, actividades destacadas y sección de videos de YouTube integrados.
  * [AboutUs.vue](file:///D:/Program_Files/laragon/www/Dsesign_Web/dpesec/resources/js/pages/public/AboutUs.vue): Página "Nosotros". Contiene la misión/visión, organigrama interactivo y un panel circular animado con los objetivos estratégicos.
  * [ProyeccionSocial.vue](file:///D:/Program_Files/laragon/www/Dsesign_Web/dpesec/resources/js/pages/public/ProyeccionSocial.vue): Listado dinámico filtrado de actividades del área.
  * [Eventos.vue](file:///D:/Program_Files/laragon/www/Dsesign_Web/dpesec/resources/js/pages/public/Eventos.vue): Calendario oficial y cronograma clasificado (*Próximos*, *En Curso*, *Pasados*).
  * [Documentos.vue](file:///D:/Program_Files/laragon/www/Dsesign_Web/dpesec/resources/js/pages/public/Documentos.vue): Descarga de directivas y guías de la oficina.

* **`resources/js/pages/auth/` (Autenticación)**:
  * [Login.vue](file:///D:/Program_Files/laragon/www/Dsesign_Web/dpesec/resources/js/pages/auth/Login.vue): Formulario de acceso interno, completamente traducido al español y personalizado estéticamente.

* **`resources/js/pages/` (Administración)**:
  * [Dashboard.vue](file:///D:/Program_Files/laragon/www/Dsesign_Web/dpesec/resources/js/pages/Dashboard.vue): Panel administrativo. Incluye control de listas, búsquedas, filtros y formularios modales interactivos para insertar y eliminar eventos/documentos.

* **`resources/js/lib/` (Mapeo de Datos Base)**:
  * [eventsData.ts](file:///D:/Program_Files/laragon/www/Dsesign_Web/dpesec/resources/js/lib/eventsData.ts): **IMPORTANTE**. Archivo TypeScript que exporta el listado unificado `eventsList` de **17 publicaciones reales** del Facebook de la oficina. Sirve como base para que crees tu Seeder o llenes la tabla de la base de datos.

---

## 📌 Guía de Integración para el Desarrollador Backend

Para culminar la integración dinámica con la base de datos MySQL, sigue los siguientes pasos recomendados:

### 1. Migraciones y Seeders
Crea los modelos y tablas para **Eventos** (`events`) y **Documentos** (`documents`). 
* La estructura de datos recomendada para la tabla `events` (basada en la interfaz `EventItem` de `eventsData.ts`) es:
  * `id` (int primary key)
  * `title` (string)
  * `type` (string) - *Ej. Cultural, Ambiental, Campaña Social*
  * `category` (string) - *Ej. Proyección Social, Extensión Cultural*
  * `status` (string/enum) - *'Proximos' | 'EnCurso' | 'Pasados'*
  * `date` (string)
  * `time` (string)
  * `location` (string)
  * `organizer` (string)
  * `description` (text)
  * `image` (string/text)
  * `fbLink` (string/text)
  * `isProyeccionSocial` (boolean)

> **💡 Consejo**: Puedes usar la constante `eventsList` de [eventsData.ts](file:///D:/Program_Files/laragon/www/Dsesign_Web/dpesec/resources/js/lib/eventsData.ts) para escribir un Seeder rápido y tener datos reales de inicio.

### 2. Controladores e Inertia Shared Props
Actualmente, las vistas públicas cargan los datos de forma estática importando `eventsList`.
Debes reemplazar estas importaciones estáticas inyectando los datos como Props desde los controladores de Laravel utilizando Inertia:
```php
// En PublicController.php
public function home() {
    return Inertia::render('public/Home', [
        'events' => Event::latest()->take(3)->get()
    ]);
}
```
Y en los archivos `.vue` recibir la propiedad:
```typescript
const props = defineProps<{
    events: any[]
}>();
```

### 3. Formularios de Gestión en el Dashboard
En [Dashboard.vue](file:///D:/Program_Files/laragon/www/Dsesign_Web/dpesec/resources/js/pages/Dashboard.vue#L60-L150) encontrarás las funciones `handleAddEventSubmit`, `handleAddDocSubmit`, `deleteEvent` y `deleteDoc`. Reemplaza la simulación local reactiva conectando los formularios de Inertia:
```typescript
import { useForm } from '@inertiajs/vue3';

const form = useForm({
    title: '',
    category: 'Proyección Social',
    // ...
});

const handleAddEventSubmit = () => {
    form.post(route('admin.events.store'), {
        onSuccess: () => {
            isAddEventOpen.value = false;
            form.reset();
        }
    });
};
```

---

## ⚡ Comandos Útiles de Mantenimiento
* **Formatear y Validar Linting**: `npm run lint` (Ejecuta ESLint para asegurar coherencia de tipos y formato limpio en producción).
* **Compilar para Producción**: `npm run build` (Minifica y empaqueta los assets con Vite en la carpeta `public/build`).
