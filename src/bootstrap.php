<?php

/*
 * =====================================================================
 * ARRANQUE DE LA APLICACIÓN (no es una capa: es el "armado")
 * =====================================================================
 * Este archivo junta todas las capas y deja la app lista para correr:
 *   1. Carga las librerías, la configuración (.env) y los archivos de cada capa.
 *   2. Crea los objetos que se comparten: la base de datos, el motor de vistas,
 *      los DAO, los servicios y los controladores.
 *   3. Le pide a la capa de rutas (WebRoutes) que registre las direcciones.
 *   4. Agrega los middlewares globales y devuelve la app a public/index.php.
 *
 * Las capas del proyecto, de afuera hacia adentro:
 *   Rutas        (src/routes)       -> qué dirección va a qué controlador
 *   Compartido   (src/compartido)   -> lo de todas las páginas: middlewares
 *                                      (sesión, admin, log), layout, navbar,
 *                                      footer, 403 y helpers html()/view()
 *   Controladores(src/controllers)  -> valida y parsea la entrada, elige la vista
 *   Services     (src/services)     -> reglas de negocio
 *   Persistence  (src/persistence)  -> datos: todas las consultas SQL
 *   Vistas       (src/views)        -> lo que ve el usuario (HTML)
 *
 * Cada capa depende de la de abajo a través de su INTERFAZ; las clases
 * Impl solo se nombran acá, al crear los objetos.
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

// Compartido: middlewares (porteros) que usan todas las páginas.
// (Los helpers html() y view() de compartido/utils se cargan solos por composer.json)
require __DIR__ . '/compartido/middlewares/authMiddleware.php';
require __DIR__ . '/compartido/middlewares/adminMiddleware.php';
require __DIR__ . '/compartido/middlewares/logMiddleware.php';

// Capa Persistence: interfaces (contratos) y sus implementaciones (SQL)
require __DIR__ . '/persistence/UsuarioDAO.php';
require __DIR__ . '/persistence/SolicitudDAO.php';
require __DIR__ . '/persistence/impl/UsuarioDAOImpl.php';
require __DIR__ . '/persistence/impl/SolicitudDAOImpl.php';

// Capa Services: interfaces (contratos) y sus implementaciones (reglas de negocio)
require __DIR__ . '/services/AuthService.php';
require __DIR__ . '/services/UsuarioService.php';
require __DIR__ . '/services/SolicitudService.php';
require __DIR__ . '/services/impl/AuthServiceImpl.php';
require __DIR__ . '/services/impl/UsuarioServiceImpl.php';
require __DIR__ . '/services/impl/SolicitudServiceImpl.php';

// Capa Controllers: interfaces (contratos), la clase base común y las implementaciones
require __DIR__ . '/controllers/InicioController.php';
require __DIR__ . '/controllers/AuthController.php';
require __DIR__ . '/controllers/SolicitudController.php';
require __DIR__ . '/controllers/AdminController.php';
require __DIR__ . '/controllers/UsuarioController.php';
require __DIR__ . '/controllers/impl/BaseController.php';
require __DIR__ . '/controllers/impl/InicioControllerImpl.php';
require __DIR__ . '/controllers/impl/AuthControllerImpl.php';
require __DIR__ . '/controllers/impl/SolicitudControllerImpl.php';
require __DIR__ . '/controllers/impl/AdminControllerImpl.php';
require __DIR__ . '/controllers/impl/UsuarioControllerImpl.php';

// Capa Routes: interfaz (contrato) y su implementación
require __DIR__ . '/routes/WebRoutes.php';
require __DIR__ . '/routes/impl/WebRoutesImpl.php';

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

// El motor de plantillas: busca las vistas en src/views (VISTAS_DIR, en compartido/utils/view.php)
$renderer = new PhpRenderer(
  templatePath: VISTAS_DIR,
  attributes: ["title" => "PDI | Slim Template 2026"],
);

// La conexión a la base (se abre recién cuando se usa)
$database = new Database();

// Persistence: se guardan en variables del tipo de la INTERFAZ (UsuarioDAO, SolicitudDAO)
/** @var UsuarioDAO $usuarioDAO */
$usuarioDAO = new UsuarioDAOImpl($database);
/** @var SolicitudDAO $solicitudDAO */
$solicitudDAO = new SolicitudDAOImpl($database);

// Services: reciben los DAO que necesitan (por su interfaz)
/** @var AuthService $authService */
$authService = new AuthServiceImpl($usuarioDAO);
/** @var UsuarioService $usuarioService */
$usuarioService = new UsuarioServiceImpl($usuarioDAO);
/** @var SolicitudService $solicitudService */
$solicitudService = new SolicitudServiceImpl($solicitudDAO, $usuarioDAO);

// Controladores: reciben el motor de vistas y los servicios que necesitan (por su interfaz)
/** @var InicioController $inicioController */
$inicioController = new InicioControllerImpl($renderer);
/** @var AuthController $authController */
$authController = new AuthControllerImpl($renderer, $authService);
/** @var SolicitudController $solicitudController */
$solicitudController = new SolicitudControllerImpl($renderer, $solicitudService);
/** @var AdminController $adminController */
$adminController = new AdminControllerImpl($renderer, $usuarioService, $solicitudService);
/** @var UsuarioController $usuarioController */
$usuarioController = new UsuarioControllerImpl($renderer, $usuarioService);

// --- 4. Rutas -----------------------------------------------------------------
/** @var WebRoutes $rutas */
$rutas = new WebRoutesImpl(
  $inicioController,
  $authController,
  $solicitudController,
  $adminController,
  $usuarioController,
);
$rutas->registrar($app);

// --- 5. Middlewares globales (corren en todos los pedidos) ---------------------
$app->addBodyParsingMiddleware();          // lee los datos de los formularios
$app->addRoutingMiddleware();              // busca qué ruta coincide
$app->addErrorMiddleware($debug, true, true); // maneja los errores
$app->add("logMiddleware");                // anota cada pedido en logs/app.log
$app->add(new MethodOverrideMiddleware()); // permite PUT y DELETE desde formularios (_METHOD)

return $app;
