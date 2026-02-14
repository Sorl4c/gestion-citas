
  PRD DEFINITIVO – Sistema de Gestión de Citas (Peluquería)

  Versión: 1.1 (Consolidada)
  Filosofía: "Minimal Techno" (Arquitectura robusta, dependencia mínima, despliegue simple).

  ---


  1. Visión del Producto
  Desarrollar una aplicación web mobile-first, auto-alojable y anti-fricción que permita:
   * Cliente: Reservar cita en < 60 segundos sin registro complejo.
   * Peluquero: Gestionar agenda sin curvas de aprendizaje, alojado en su propio servidor.

  Principio Rector: El sistema debe ser instalable copiando archivos vía FTP a cualquier hosting compartido con PHP/Apache, sin
  necesidad de SSH ni Node.js en el servidor.

  ---

  2. Alcance del MVP


  ✅ Incluye
   * Instalación Single-Tenant: Un dominio = Una peluquería.
   * Agenda Única: Un calendario compartido (ideal para peluquerías pequeñas/unipersonales).
   * Slots Fijos: Duración de servicio estandarizada o basada en bloques.
   * Gestión de Disponibilidad: Horario comercial + Pausa mediodía + Festivos.
   * Notificaciones: Enlace directo a WhatsApp (pre-llenado) + Confirmación en pantalla.
   * Seguridad: Cancelación por Token UUID.
   * Admin: Panel protegido para ver/cancelar/bloquear citas.


  ❌ No Incluye (v1.0)
   * Pagos online (Pasarelas).
   * Multi-usuario (Roles complejos).
   * Sincronización bidireccional (Google Calendar).
   * Gestión de inventario.
   * Envío de SMS/Email automáticos (para mantener coste cero).

  ---

  3. Stack Tecnológico


  Backend
   * Lenguaje: PHP 8.2+ (Estricto tipado).
   * Base de Datos: SQLite 3 (Modo WAL activado para concurrencia).
   * Servidor Web: Apache (configuración vía .htaccess).
   * Gestión de Dependencias: Composer (para librerías vendor).


  Frontend
   * Lógica UI: Alpine.js (vía archivo local, sin bundlers complejos).
   * Estilos: Tailwind CSS (Generado localmente con Tailwind CLI Standalone, subiendo solo el .css final).
   * Comunicación: Fetch API nativa.


  Librerías PHP Sugeridas
   * vlucas/phpdotenv: Variables de entorno.
   * nikic/fast-route: Routing ligero y rápido.
   * ramsey/uuid: Generación de IDs y tokens.
   * monolog/monolog: Logging de errores y auditoría.

  ---


  4. Arquitectura del Sistema

  Estructura de Directorios (PSR-4)


    1 /
    2 ├── .env                # Configuración sensible (NO accesible web)
    3 ├── composer.json
    4 ├── database/
    5 │   └── peluqueria.db   # Fuera del root público (SEGURIDAD CRÍTICA)
    6 ├── bin/
    7 │   └── tailwindcss     # Ejecutable standalone (dev local)
    8 ├── src/                # Lógica de Negocio
    9 │   ├── Controllers/
   10 │   ├── Models/         # Repositorios y Entidades
   11 │   ├── Services/       # Lógica (Ej: Calculadora de Slots)
   12 │   └── Middleware/     # Auth, CSRF, JSON parsing
   13 ├── public/             # DocumentRoot del servidor
   14 │   ├── index.php       # Front Controller único
   15 │   ├── assets/
   16 │   │   ├── css/style.css
   17 │   │   └── js/app.js (Alpine components)
   18 │   └── .htaccess       # Redirección a index.php
   19 └── views/              # Plantillas HTML (o componentes JSON para API)


  Autenticación
   * Admin: Sesiones PHP nativas (session_start). Cookie HttpOnly y SameSite=Strict.
   * Cliente: Sin autenticación (Guest checkout), validación por teléfono y Token UUID para gestión posterior.

  ---

  5. Diseño de API (REST v1)

  Todas las respuestas deben ser JSON.

  Endpoints Públicos


   * GET /api/v1/availability?date=YYYY-MM-DD
       * Lógica: Genera array de slots totales según horario -> Resta citas existentes en DB -> Devuelve slots libres.
   * POST /api/v1/appointments
       * Payload: { name, phone, date, time, service_id }
       * Validación: Teléfono (formato 9 dígitos), Slot libre, Límite semanal no excedido.
   * DELETE /api/v1/appointments/{token}
       * Cancela la cita asociada al token de seguridad.

  Endpoints Admin (Requieren Session Cookie)


   * POST /api/v1/auth/login
   * POST /api/v1/auth/logout
   * GET /api/v1/admin/appointments (Filtros: fecha rango)
   * PATCH /api/v1/admin/appointments/{id}/status (Estado: attended, no-show)
   * PUT /api/v1/admin/settings (Actualizar horario apertura/cierre)

  ---


  6. Modelo de Datos (Schema)

  Configuración Global
   1 PRAGMA journal_mode = WAL;
   2 PRAGMA foreign_keys = ON;


  Tabla appointments

  ┌────────────────┬─────────────┬───────────────────────────────────┐
  │ Campo          │ Tipo        │ Notas                             │
  ├────────────────┼─────────────┼───────────────────────────────────┤
  │ id             │ TEXT (UUID) │ PK                                │
  │ customer_name  │ TEXT        │                                   │
  │ customer_phone │ TEXT        │ Indexado para búsquedas rápidas   │
  │ date           │ TEXT        │ YYYY-MM-DD (Indexado)             │
  │ time           │ TEXT        │ HH:MM                             │
  │ status         │ TEXT        │ 'booked', 'cancelled', 'attended' │
  │ cancel_token   │ TEXT (UUID) │ Unique. Para URLs de cancelación  │
  │ created_at     │ INTEGER     │ Timestamp UTC                     │
  └────────────────┴─────────────┴───────────────────────────────────┘



  Tabla schedule_config

  ┌─────────────┬─────────┬───────────────────┐
  │ Campo       │ Tipo    │ Notas             │
  ├─────────────┼─────────┼───────────────────┤
  │ day_of_week   │ INTEGER │ 0 (Dom) - 6 (Sab) │
  │ is_open     │ INTEGER │ Boolean           │
  │ start_time  │ TEXT    │ HH:MM             │
  │ end_time    │ TEXT    │ HH:MM             │
  │ break_start │ TEXT    │ HH:MM (Nullable)  │
  │ break_end   │ TEXT    │ HH:MM (Nullable)  │
  └─────────────┴─────────┴───────────────────┘



  Tabla admin_users

  ┌───────────────┬──────┬───────────────┐
  │ Campo         │ Tipo │ Notas         │
  ├───────────────┼──────┼───────────────┤
  │ username      │ TEXT │ PK            │
  │ password_hash │ TEXT │ Bcrypt/Argon2 │
  └───────────────┴──────┴───────────────┘

  ---

  7. Reglas de Negocio Críticas


   1. Anti-Solapamiento (Atomicidad):
       * Toda reserva debe usar una transacción.
       * BEGIN TRANSACTION -> Verificar disponibilidad -> INSERT -> COMMIT.
       * Si falla la verificación -> ROLLBACK.
   2. Timezone:
       * La aplicación debe forzar date_default_timezone_set('Europe/Madrid') (o la config del cliente) en el bootstrap. No confiar en
         la hora del servidor.
   3. Límite Anti-Abuso:
       * Un número de teléfono no puede tener más de X citas activas en la semana actual (Configurable, default: 2).
   4. Validación de Teléfono:
       * Limpiar input (quitar espacios/guiones). Validar longitud mínima para asegurar que el enlace de WhatsApp funcione.

  ---


  8. Seguridad y Despliegue


   * Protección de DB: Regla en Apache (.htaccess o configuración vhost) para denegar acceso a *.db y *.env, además de estar
     físicamente fuera de public/.
   * Sanitización: Uso estricto de Prepared Statements (PDO) para todo query SQL.
   * Tailwind: En desarrollo se usa el CLI ./bin/tailwindcss -i src/input.css -o public/assets/css/style.css --watch. En producción
     solo se sube el archivo CSS generado.

  ---


  9. Siguientes Pasos (Roadmap Inmediato)


   1. Inicialización: composer init, estructura de carpetas y .htaccess.
   2. Database Seeding: Script setup.php que crea las tablas y un usuario admin por defecto.
   3. Core Backend: Implementar Database.php (Singleton PDO) y Router.
   4. UI Prototipado: HTML estático con Tailwind para definir el flujo de reserva.