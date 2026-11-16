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

- Flujo: Routes → Controllers → Services → Persistence → base. Cada capa depende solo de la de abajo y **por su interfaz**. La interfaz va en la raíz de la carpeta (`XService.php`) y la implementación en `impl/` (`impl/XServiceImpl.php`). Saltear una capa solo si no hace falta, y con un comentario que explique por qué (ej. `InicioControllerImpl` no usa Service).
- Punto de entrada: `public/index.php` → `src/bootstrap.php`. `bootstrap.php` solo arma la app: carga config, crea `Database`, `PhpRenderer`, DAO → Services → Controllers → `WebRoutesImpl`, registra las rutas y agrega los middlewares globales (el de errores va al final). Es el único lugar donde se nombran las clases `Impl`.
- **Routes** (`src/routes/WebRoutes.php` + `impl/WebRoutesImpl.php`): recibe los controllers por constructor y mapea cada dirección a `[$controller, "metodo"]`. Acá se agregan `authMiddleware` / `adminMiddleware` a los grupos. No hay lógica.
- **Controllers** (`src/controllers/` + `impl/`): las `Impl` heredan de `impl/BaseController` (`render()` y `redirigir()`). Leen y validan la entrada (campos completos, formatos, largos), manejan la sesión, llaman al Service y eligen la vista. No tienen reglas de negocio ni usan DAO. `InicioController`, `AuthController`, `SolicitudController`, `AdminController`, `UsuarioController`.
- **Services** (`src/services/` + `impl/`): reglas de negocio (`AuthService`, `UsuarioService`, `SolicitudService`): encriptar contraseñas, registro siempre `cliente`, rol inválido → `cliente`, el admin no se borra a sí mismo, el usuario tiene que existir para pedir servicio. No conocen Request/Response, sesión ni SQL. Cada regla está comentada con `REGLA:` arriba de su función.
- **Persistence** (`src/persistence/` + `impl/`): todo el SQL vive acá (`UsuarioDAO`, `SolicitudDAO`), siempre con consultas preparadas. Es lo único que usa `Database`. Escrituras con `Database::runTransaction()`. Nunca llames a `runTransaction` dentro de otra (las transacciones anidadas lanzan excepción). `crear()`/`actualizar()` de usuario reciben la contraseña ya encriptada y devuelven `false` si el email está repetido (error 1062); `SolicitudDAO::crear()` devuelve `false` si el usuario ya no existe (error 1452).
- **Vistas** (`src/views/`): solo las páginas de cada entidad, en PHP plano, renderizadas con `view()` dentro del layout base. Escapá la salida con `html(...)`.
- **Compartido** (`src/compartido/`): lo que usan todas las páginas. `middlewares/` (auth, admin, log), `layouts/base.php`, `partials/` (navbar, footer), `errores/403.php` y `utils/` (`html()`, `view()`; se autoloadan vía `autoload.files` de `composer.json`, después de moverlos correr `composer dump-autoload`). `PhpRenderer` busca relativo a `src/views` (`VISTAS_DIR`), por eso las plantillas compartidas se nombran con las constantes `LAYOUT_BASE` y `VISTA_403` de `compartido/utils/view.php`.
- Los archivos de todas las capas se cargan con `require` en `bootstrap.php` (primero la interfaz, después la `Impl`): si agregás uno nuevo, sumalo ahí.
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
- La navbar compartida está en `src/compartido/partials/navbar.php`.