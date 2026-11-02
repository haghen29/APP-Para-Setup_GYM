# AGENTS.md

Aplicación web de gestión de gimnasios con Slim 4 (PHP >= 8.1). Sin andamiaje de framework más allá de Slim: rutas, plantillas PHP plano, PDO. El código, las vistas y los mensajes de commit están en español — mantenelo así.

## Comandos

```bash
composer install          # vendor/ (gitignored)
composer serve            # php -S localhost:8000 -t public, el servidor de desarrollo
composer migrate          # php scripts/migrate.php — aplicar migraciones .sql pendientes
```

No hay suite de tests, linter, typechecker ni CI. Verificá los cambios ejecutando `composer serve` y visitando las rutas en el navegador.

## Setup

- Copiá `.env.example` a `.env` (`.env` está en gitignore). Los valores por defecto apuntan a un MySQL local root/`slim_php`; ajustá las variables `DB_*` y `APP_ENV` (solo admite `dev`/`prod`).
- `APP_ENV=dev` habilita el debugger de errores de Slim; `prod` lo deshabilita.
- Requiere PDO con driver `mysql` o `pgsql`.

## Arquitectura (en capas)

- Punto de entrada: `public/index.php` → `src/bootstrap.php`. `bootstrap.php` solo arma la app: carga config, crea `Database`, `PhpRenderer`, los modelos y los controladores, registra las rutas y agrega los middlewares globales (el de errores va al final).
- **Rutas** (`src/routes/web.php`): devuelve una función que recibe `$app` y los controladores y mapea cada dirección a `[$controlador, "metodo"]`. Acá se agregan `authMiddleware` / `adminMiddleware` a los grupos. No hay lógica.
- **Controladores** (`src/controllers/`): heredan de `Controller` (`render()` y `redirigir()`). Validan lo que llega, llaman a los modelos y eligen la vista. `InicioController`, `AuthController`, `SolicitudController`, `AdminController`, `UsuarioController`.
- **Modelos** (`src/models/`): todo el SQL vive acá (`UsuarioModel`, `SolicitudModel`), siempre con consultas preparadas. Escrituras con `Database::runTransaction()`. Nunca llames a `runTransaction` dentro de otra (las transacciones anidadas lanzan excepción). `crear()`/`actualizar()` de usuario devuelven `false` si el email está repetido (error 1062).
- **Vistas** (`src/views/`): PHP plano, renderizadas con `view()` (`src/utils/view.php`) dentro de `layouts/base.php`. Escapá la salida con `html(...)`. Ambos helpers se autoloadan vía `autoload.files` de `composer.json`.
- Los archivos de controladores y modelos se cargan con `require` en `bootstrap.php`: si agregás uno nuevo, sumalo ahí.
- Cada archivo tiene arriba un comentario que dice a qué capa pertenece y qué hace.

## Migraciones

- Los archivos SQL viven en `src/database/migrations/`, se aplican en orden de ordenación natural (por nombre de archivo) y se registran por nombre en la tabla `schema_migrations`. Prefacé los archivos nuevos con un número incremental (ej. `004_...sql`).
- Los archivos aplicados nunca se vuelven a ejecutar; edites migraciones existentes solo si estás seguro de que no fueron aplicadas en ningún entorno.
- Particularidad del driver: MySQL ejecuta las migraciones **sin transacción** (el DDL hace commit automático); PostgreSQL envuelve cada una en una transacción. Mantené los archivos compatibles con el driver que apunte `.env` (las migraciones acá usan sintaxis específica de MySQL como `AUTO_INCREMENT`, `ENUM`, `curdate()`).

## Estilo

- Indentación de 2 espacios en PHP, finales de línea LF, UTF-8 (ver `.editorconfig`). Identificadores/comentarios en español en código de cara al usuario y en esquema.
## Autenticación y roles

- Hay login/registro con sesión PHP (`$_SESSION["user_id"|"user_nombre"|"user_rol"]`). El registro público siempre crea `cliente`.
- `authMiddleware` exige sesión; si falta, guarda la ruta GET pedida en `$_SESSION["volver_a"]` y el login devuelve ahí al usuario (así "Pedir servicio" pasa por el login y vuelve al formulario).
- `adminMiddleware` exige rol `admin` (403 si no). Registrarlo **antes** que `authMiddleware` en el `->add(...)` para que auth corra primero: `->add("adminMiddleware")->add("authMiddleware")`.
- Rutas: `/pedir-servicio` (auth), `/admin`, `/admin/formularios`, `/admin/formularios/{id}`, `/admin/usuarios` y su CRUD (admin). Tabla `SOLICITUD_SERVICIO` en la migración `004`.
- El primer admin no se crea desde la web: registrarse y luego `UPDATE USUARIO SET rol = 'admin' WHERE email = '...';`.
- La navbar compartida está en `src/views/partials/navbar.php`.