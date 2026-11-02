<?php

/*
 * =====================================================================
 * ARRANQUE DE LA APLICACIÓN (no es una capa: es el "armado")
 * =====================================================================
 * Este archivo junta todas las capas y deja la app lista para correr:
 *   1. Carga las librerías, la configuración (.env) y los archivos de cada capa.
 *   2. Crea los objetos que se comparten: la base de datos, el motor de vistas,
 *      los modelos y los controladores.
 *   3. Le pide a la capa de rutas (src/routes/web.php) que registre las direcciones.
 *   4. Agrega los middlewares globales y devuelve la app a public/index.php.
 *
 * Las capas del proyecto, de afuera hacia adentro:
 *   Rutas        (src/routes)       -> qué dirección va a qué controlador
 *   Middlewares  (src/middlewares)  -> porteros: sesión, admin, log
 *   Controladores(src/controllers)  -> lógica: valida, decide, elige la vista
 *   Modelos      (src/models)       -> datos: todas las consultas SQL
 *   Vistas       (src/views)        -> lo que ve el usuario (HTML)
 * =====================================================================
 */

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Slim\Middleware\MethodOverrideMiddleware;
use Dotenv\Dotenv;

// --- 1. Librerías y archivos de cada capa ---------------------------------
require __DIR__ . '/../vendor/autoload.php';

// Capa de acceso a la base (conexión y transacciones)
require __DIR__ . '/database/database.php';

// Middlewares (porteros)
require __DIR__ . '/middlewares/authMiddleware.php';
require __DIR__ . '/middlewares/adminMiddleware.php';
require __DIR__ . '/middlewares/logMiddleware.php';

// Capa de modelos (datos)
require __DIR__ . '/models/UsuarioModel.php';
require __DIR__ . '/models/SolicitudModel.php';

// Capa de controladores (lógica). Primero la clase base que heredan todos.
require __DIR__ . '/controllers/Controller.php';
require __DIR__ . '/controllers/InicioController.php';
require __DIR__ . '/controllers/AuthController.php';
require __DIR__ . '/controllers/SolicitudController.php';
require __DIR__ . '/controllers/AdminController.php';
require __DIR__ . '/controllers/UsuarioController.php';

// --- 2. Configuración -------------------------------------------------------

// Cargar variables de entorno desde el .env
Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

$env = $_ENV["APP_ENV"] ?? "prod";
$allowedEnvs = ["dev", "prod"];

if (!in_array($env, $allowedEnvs, true)) {
  throw new RuntimeException("APP_ENV inválido: $env");
}

// En "dev" se muestran los errores con detalle; en "prod" se esconden
$debug = $env === "dev";

// La sesión es lo que recuerda quién inició sesión entre una página y otra
if (session_status() !== PHP_SESSION_ACTIVE) {
  session_start();
}

// Roles permitidos: son los mismos valores del ENUM de la columna USUARIO.rol
const ROLES_VALIDOS = ["cliente", "empleado", "admin"];

// Tipos de servicio del formulario "Pedir servicio": son los mismos valores del ENUM de SOLICITUD_SERVICIO.tipo_servicio
const TIPOS_SERVICIO = [
  "desarrollo_web" => "Desarrollo web a medida",
  "ecommerce" => "E-commerce / tienda online",
  "sistema_gestion" => "Sistema de gestión interna",
  "app_administracion" => "Aplicación de administración",
  "mantenimiento" => "Mantenimiento y soporte",
  "otro" => "Otro",
];

// --- 3. Objetos compartidos ---------------------------------------------------

// La aplicación de Slim
$app = AppFactory::create();

// El motor de plantillas: busca las vistas en src/views
$renderer = new PhpRenderer(
  templatePath: __DIR__ . "/views",
  attributes: ["title" => "PDI | Slim Template 2026"],
);

// La conexión a la base (se abre recién cuando se usa)
$database = new Database();

// Modelos: cada uno maneja una tabla
$usuarioModel = new UsuarioModel($database);
$solicitudModel = new SolicitudModel($database);

// Controladores: reciben el motor de vistas y los modelos que necesitan
$controladores = [
  "inicio" => new InicioController($renderer),
  "auth" => new AuthController($renderer, $usuarioModel),
  "solicitud" => new SolicitudController($renderer, $solicitudModel),
  "admin" => new AdminController($renderer, $usuarioModel, $solicitudModel),
  "usuario" => new UsuarioController($renderer, $usuarioModel),
];

// --- 4. Rutas -----------------------------------------------------------------
$registrarRutas = require __DIR__ . '/routes/web.php';
$registrarRutas($app, $controladores);

// --- 5. Middlewares globales (corren en todos los pedidos) ---------------------
$app->addBodyParsingMiddleware();          // lee los datos de los formularios
$app->addRoutingMiddleware();              // busca qué ruta coincide
$app->addErrorMiddleware($debug, true, true); // maneja los errores
$app->add("logMiddleware");                // anota cada pedido en logs/app.log
$app->add(new MethodOverrideMiddleware()); // permite PUT y DELETE desde formularios (_METHOD)

return $app;
