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

## Arquitectura

- Punto de entrada: `public/index.php` → `src/bootstrap.php`. **Todas las rutas están definidas como closures inline en `src/bootstrap.php`** — `src/routes/` y `src/controllers/` existen pero son placeholders vacíos (`gitkeep`); no asumas que están conectados.
- El middleware de errores (`$app->addErrorMiddleware(...)`) está registrado al **final** de `bootstrap.php`, así que cualquier ruta/middleware nuevo debe agregarse antes de esa última línea.
- Acceso a BD mediante la clase `Database` (`src/database/database.php`): `getConnection()` para lecturas, `runTransaction(Closure)` para escrituras. Nunca llames a `runTransaction` dentro de otra (las transacciones anidadas lanzan excepción).
- Las vistas son PHP plano bajo `src/views/`, renderizadas con el helper global `view($renderer, $response, $template, $data, $layout)` (`src/utils/view.php`), que envuelve cada plantilla en `layouts/base.php`. Escapá la salida con `html(...)` (`src/utils/html.php`). Ambos helpers se autoloadan vía `autoload.files` de `composer.json`.
- Convención ruta → vista (ver el bloque de comentarios TPN11 en `bootstrap.php`): `GET /usuarios`, `/usuarios/create`, `/usuarios/update/{id}`, `/usuarios/{id}`, más handlers POST/PUT/DELETE.

## Migraciones

- Los archivos SQL viven en `src/database/migrations/`, se aplican en orden de ordenación natural (por nombre de archivo) y se registran por nombre en la tabla `schema_migrations`. Prefacé los archivos nuevos con un número incremental (ej. `004_...sql`).
- Los archivos aplicados nunca se vuelven a ejecutar; edites migraciones existentes solo si estás seguro de que no fueron aplicadas en ningún entorno.
- Particularidad del driver: MySQL ejecuta las migraciones **sin transacción** (el DDL hace commit automático); PostgreSQL envuelve cada una en una transacción. Mantené los archivos compatibles con el driver que apunte `.env` (las migraciones acá usan sintaxis específica de MySQL como `AUTO_INCREMENT`, `ENUM`, `curdate()`).

## Estilo

- Indentación de 2 espacios en PHP, finales de línea LF, UTF-8 (ver `.editorconfig`). Identificadores/comentarios en español en código de cara al usuario y en esquema.
- Aún no hay auth en backend: `/auth/login` y `/create/register` son vistas estáticas; no asumas que existe lógica de login/sesión.