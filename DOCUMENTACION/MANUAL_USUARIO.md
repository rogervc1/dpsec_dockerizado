# Manual de usuario â€“ DPSEC Web

## 1. DescripciÃ³n

DPSEC Web es el portal de la DirecciÃ³n de ProyecciÃ³n Social y ExtensiÃ³n Cultural de la Universidad Nacional del Altiplano de Puno. Tiene un sitio pÃºblico y una intranet para usuarios autenticados que administran eventos, documentos y certificados.

En desarrollo normalmente se accede por http://127.0.0.1:8000; en Docker, por http://localhost:8090.

## 2. Sitio pÃºblico

El menÃº contiene:

- Inicio: resumen, estadÃ­sticas, actividades, documentos, videos y subunidades.
- Nosotros: misiÃ³n, equipo, objetivos y valores.
- ProyecciÃ³n Social: actividades y preguntas frecuentes.
- Seguimiento al Graduado: informaciÃ³n del mÃ³dulo, actualmente en desarrollo.
- Documentos: consulta y descarga de documentos activos.
- Eventos: consulta de actividades.
- Certificados: bÃºsqueda por DNI o documento.

### Documentos

1. Abra Documentos.
2. Revise tÃ­tulo, cÃ³digo, categorÃ­a, fecha y descripciÃ³n.
3. Seleccione el enlace de descarga.
4. Si no carga, comunÃ­quelo al administrador.

Solo aparecen documentos activos.

### Eventos

1. Abra Eventos.
2. Revise fecha, hora, lugar, organizador y descripciÃ³n.
3. Interprete el estado:
   - PrÃ³ximo: la fecha todavÃ­a no llega.
   - En Curso: coincide con el dÃ­a actual.
   - Finalizado: la fecha ya pasÃ³.
4. Navegue por la galerÃ­a cuando exista.
5. Use el enlace de inscripciÃ³n o Facebook si aparece.

### Certificados

Para buscar:

1. Abra Certificados.
2. Escriba el DNI, cÃ³digo de estudiante u otro documento registrado.
3. Ejecute la bÃºsqueda.
4. Seleccione el resultado.
5. Verifique o descargue el PDF.

TambiÃ©n se puede abrir directamente /verificar-certificado/{identifier} con el UUID o cÃ³digo DPSEC-AAAA-XXXXXX. El PDF se genera en A4 horizontal con el fondo, nombre y rol configurados.

## 3. Inicio de sesiÃ³n

1. Abra /login.
2. Introduzca correo y contraseÃ±a.
3. Marque Recordar mi cuenta solo en un equipo confiable.
4. Pulse Ingresar al Sistema.
5. Complete 2FA si se solicita.

TambiÃ©n puede usar passkey si estÃ¡ registrada.

Problemas frecuentes:

- Â¿Olvidaste tu contraseÃ±a? inicia el restablecimiento.
- Si el correo no estÃ¡ verificado, solicite otro enlace.
- Si 2FA falla, use un cÃ³digo de recuperaciÃ³n.
- Si no aparece una passkey, revise que el dominio sea el configurado para la aplicaciÃ³n.

## 4. Panel administrativo

Entre a /admin o /dashboard. El panel incluye:

- Resumen de eventos, documentos, proyecciÃ³n social, certificados y plantillas.
- Eventos.
- Documentos.
- Certificados.

Las listas permiten buscar y muestran mensajes despuÃ©s de cada operaciÃ³n.

## 5. Crear y administrar eventos

1. Abra Eventos y pulse Agregar evento.
2. Complete tÃ­tulo, tipo, categorÃ­a, fecha, lugar, organizador y descripciÃ³n.
3. AÃ±ada hora, enlaces y orden si corresponde.
4. Marque ProyecciÃ³n social cuando deba aparecer en esa pÃ¡gina.
5. Seleccione imÃ¡genes.
6. Revise el recorte 16:9 de la portada.
7. Guarde.

Reglas:

- Cada imagen puede pesar hasta 5 MB.
- La galerÃ­a acepta mÃ¡ximo 10 imÃ¡genes.
- Se aceptan JPG, JPEG, PNG y WebP.
- La primera imagen es la principal.
- El estado se calcula automÃ¡ticamente desde la fecha.

Para editar, busque el evento, pulse editar, cambie sus datos y guarde. Para eliminarlo, pulse eliminar y confirme; se borra el registro y los archivos locales asociados.

## 6. Registrar y administrar documentos

1. Abra Documentos.
2. Pulse Agregar documento.
3. Complete tÃ­tulo, cÃ³digo Ãºnico, categorÃ­a, fecha y descripciÃ³n.
4. Seleccione el archivo.
5. Active o desactive la visibilidad pÃºblica.
6. Indique el orden.
7. Guarde.

El archivo puede pesar hasta 50 MB. Para reemplazarlo, edite el registro y seleccione uno nuevo. El archivo anterior se elimina del almacenamiento.

## 7. Administrar certificados

### Subir tipografÃ­a

1. Abra Certificados > TipografÃ­as.
2. Indique el nombre de la familia.
3. Seleccione un archivo .ttf de hasta 10 MB.
4. Guarde.

El nombre debe ser Ãºnico y debe coincidir con el que se seleccione en la plantilla.

### Crear plantilla

1. Abra Certificados > Plantillas.
2. Pulse Nueva plantilla.
3. Escriba el nombre.
4. Suba un fondo JPG, JPEG o PNG de hasta 5 MB.
5. Configure para el nombre y rol: posiciÃ³n horizontal/vertical, tamaÃ±o, color y tipografÃ­a.
6. Revise la vista previa.
7. Guarde.

El PDF usa A4 horizontal y extiende el fondo a toda la pÃ¡gina.

### Preparar e importar participantes

Se recomienda un archivo como:

~~~csv
nombre,dni,rol,email
Ana Luz Ticona Choque,12345678,Participante,ana@example.com
~~~

Se reconocen tambiÃ©n name, documento, codigo, document, role y correo. El separador puede ser coma o punto y coma.

Pasos:

1. Abra Certificados > Importar.
2. Seleccione la plantilla.
3. Indique la fecha de emisiÃ³n.
4. Seleccione CSV/TXT de hasta 5 MB.
5. EnvÃ­e.
6. Revise Registros.

Se omiten filas vacÃ­as, filas sin nombre/documento y duplicados del mismo documento dentro de una plantilla. Si faltan encabezados reconocibles, se usa la primera columna como nombre y la segunda como documento.

### Registros

Registros muestra certificados paginados. Puede filtrar por plantilla, revisar cÃ³digo, destinatario, documento, rol y fecha, o eliminar un registro. Eliminarlo impide su bÃºsqueda pÃºblica.

Eliminar una plantilla elimina tambiÃ©n sus certificados asociados; confirme solo si estÃ¡ seguro.

## 8. Perfil y seguridad

En el menÃº de usuario:

- Perfil: cambiar nombre y correo.
- Seguridad: cambiar contraseÃ±a, 2FA, cÃ³digos de recuperaciÃ³n y passkeys.
- Apariencia: cambiar el tema.
- Cerrar sesiÃ³n.

Para cambiar la contraseÃ±a se requiere la actual. No comparta credenciales.

## 9. Incidencias frecuentes

| Problema | SoluciÃ³n |
|---|---|
| No aparecen imÃ¡genes/documentos | Ejecutar php artisan storage:link y revisar storage/app/public |
| Cambios Vue no aparecen | Mantener npm run dev o ejecutar npm run build |
| Error de MySQL | Revisar servicio y variables DB_* |
| PowerShell bloquea npm | Usar npm.cmd run dev o npm.cmd run build |
| PDF sin fondo | Revisar archivo de plantilla y storage link |
| PDF usa otra fuente | Revisar que exista el TTF y coincida el nombre |
| Evento no aparece | Revisar fecha y guardar correctamente |
| Documento no aparece | Activar is_active |
| No se puede entrar | Revisar correo, contraseÃ±a, verificaciÃ³n y 2FA |

## 10. Recomendaciones

- No compartir credenciales.
- No eliminar plantillas con certificados sin autorizaciÃ³n.
- Conservar los CSV y documentos originales.
- Usar nombres claros y cÃ³digos Ãºnicos.
- Comprobar el sitio pÃºblico despuÃ©s de publicar.
- No usar el usuario de prueba en producciÃ³n.



