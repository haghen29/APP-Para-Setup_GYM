<?php

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Slim\Middleware\MethodOverrideMiddleware;
use Slim\Routing\RouteCollectorProxy;
use Dotenv\Dotenv;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/database/database.php';
require __DIR__ . '/middlewares/authMiddleware.php';
require __DIR__ . '/middlewares/adminMiddleware.php';
require __DIR__ . '/middlewares/logMiddleware.php';

// Cargar variables de entorno desde el .env
Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

$env = $_ENV["APP_ENV"] ?? "prod";
$allowedEnvs = ["dev", "prod"];

if (!in_array($env, $allowedEnvs, true)) {
  throw new RuntimeException("APP_ENV inválido: $env");
}

$debug = $env === "dev";

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

// Cantidad de caracteres (no bytes) de un texto UTF-8. No depende de la extensión mbstring, que no siempre está activada.
function largoTexto(string $texto): int
{
  return (int) preg_match_all('/./su', $texto);
}

// A dónde va cada rol cuando inicia sesión sin una página pendiente: el admin al panel, el resto a la landing
function rutaInicio(?string $rol): string
{
  return $rol === "admin" ? "/admin" : "/index";
}

// Crear la aplicacion de Slim
$app = AppFactory::create();

// Crear el motor de plantillas
$renderer = new PhpRenderer(
  templatePath: __DIR__ . "/views",
  attributes: ["title" => "PDI | Slim Template 2026"],
);

$database = new Database();

// Ruta/Vista principal
$app->get("/index", function ($request, $response) use ($renderer) {
  return view($renderer, $response, "index.php");
});

// La raíz del sitio lleva a la landing
$app->get("/", function ($request, $response) {
  return $response->withHeader("Location", "/index")->withStatus(302);
});

$app->get("/auth/register", function (
  Request $request,
  Response $response
) use ($renderer) {
  return view($renderer, $response, "entidad/register.php", [
    "bodyClass" => "pagina-auth", // fondo del gym (ver public/css/tema.css)
  ]);
});
 
$app->post("/auth/register", function (
  Request $request,
  Response $response
) use ($renderer, $database) {
  $body = $request->getParsedBody();
 
  $nombre = trim($body["nombre"] ?? "");
  $apellido = trim($body["apellido"] ?? "");
  $email = trim($body["email"] ?? "");
  $contrasena = $body["contrasena"] ?? "";
  $confirmar = $body["confirmar"] ?? "";
 
  // Validaciones: si algo falla, volvemos al formulario con el motivo
  $error = null;
  if ($nombre === "" || $apellido === "" || $email === "" || $contrasena === "") {
    $error = "Completá todos los campos.";
  } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
    $error = "El email no es válido.";
  } elseif (strlen($contrasena) < 6) {
    $error = "La contraseña debe tener al menos 6 caracteres.";
  } elseif ($contrasena !== $confirmar) {
    $error = "Las contraseñas no coinciden.";
  }
 
  if ($error !== null) {
    return view($renderer, $response->withStatus(422), "entidad/register.php", [
      "bodyClass" => "pagina-auth", // fondo del gym (ver public/css/tema.css)
      "error" => $error,
      "old" => $body,
    ]);
  }
 
  try {
    $database->runTransaction(function ($pdo) use ($nombre, $apellido, $email, $contrasena) {
      // El rol NO viene del formulario: quien se registra solo puede ser "cliente".
      $query = $pdo->prepare(
        "INSERT INTO USUARIO (nombre, apellido, email, contrasena, rol, fecha_registro)
         VALUES (?, ?, ?, ?, 'cliente', CURDATE())"
      );
      $query->execute([
        $nombre,
        $apellido,
        $email,
        password_hash($contrasena, PASSWORD_DEFAULT),
      ]);
    });
  } catch (PDOException $e) {
    // 1062 = entrada duplicada en MySQL (email UNIQUE repetido)
    if (($e->errorInfo[1] ?? null) === 1062) {
      return view($renderer, $response->withStatus(409), "entidad/register.php", [
        "bodyClass" => "pagina-auth", // fondo del gym (ver public/css/tema.css)
        "error" => "Ya existe una cuenta con ese email.",
        "old" => $body,
      ]);
    }
    throw $e;
  }
 
  return $response->withHeader("Location", "/auth/login?registrado=1")->withStatus(303);
});

$app->get("/auth/login", function (
  Request $request,
  Response $response
) use ($renderer) {
  // Si ya inició sesión, no tiene sentido mostrar el login
  if (!empty($_SESSION["user_id"])) {
    return $response->withHeader("Location", rutaInicio($_SESSION["user_rol"] ?? null))->withStatus(302);
  }
 
  $registrado = isset($request->getQueryParams()["registrado"]);
 
  return view($renderer, $response, "entidad/login.php", [
    "bodyClass" => "pagina-auth", // fondo del gym (ver public/css/tema.css)
    "registrado" => $registrado,
    // authMiddleware guardó "volver_a": el usuario venía de una página protegida
    "aviso" => !empty($_SESSION["volver_a"]),
  ]);
});

$app->post("/auth/login", function (
  Request $request,
  Response $response
) use ($renderer, $database) {
  $body = $request->getParsedBody();
  $email = trim($body["email"] ?? "");
  $contrasena = $body["contrasena"] ?? "";
  $pdo = $database->getConnection();
  $query = $pdo->prepare(
    "SELECT id_usuario, nombre, contrasena, rol FROM USUARIO WHERE email = ?"
  );
  $query->execute([$email]);
  $usuario = $query->fetch();
 
  // Mismo mensaje si falla el email o la contraseña: no revelamos cuál de los dos
  if (!$usuario || !password_verify($contrasena, $usuario["contrasena"])) {
    return view($renderer, $response->withStatus(401), "entidad/login.php", [
      "bodyClass" => "pagina-auth", // fondo del gym (ver public/css/tema.css)
      "error" => "Email o contraseña incorrectos.",
      "old" => $body,
      "aviso" => !empty($_SESSION["volver_a"]),
    ]);
  }
 
  // Credenciales correctas: iniciamos la sesión
  session_regenerate_id(true); // evita la fijación de sesión
  $_SESSION["user_id"] = $usuario["id_usuario"];
  $_SESSION["user_nombre"] = $usuario["nombre"];
  $_SESSION["user_rol"] = $usuario["rol"];
 
  // Si venía de una página protegida (ej. "Pedir servicio"), lo devolvemos ahí;
  // si no, a la página de inicio de su rol.
  $destino = $_SESSION["volver_a"] ?? rutaInicio($usuario["rol"]);
  unset($_SESSION["volver_a"]);
 
  return $response->withHeader("Location", $destino)->withStatus(303);
});

// GET /auth/logout -> cierra la sesión
$app->get("/auth/logout", function (
  Request $request,
  Response $response
) {
  $_SESSION = [];
  session_destroy();
 
  return $response->withHeader("Location", "/auth/login")->withStatus(302);
});

/*  TPN11: CRUD de usuarios. Está dentro del panel: /admin/usuarios (solo admin) */
$app->group("/admin/usuarios", function (RouteCollectorProxy $group) use ($renderer, $database) {
 
  $group->get("", function (
    Request $request,
    Response $response
  ) use ($renderer, $database) {
    $pdo = $database->getConnection();
    // No se pide la columna contrasena: el listado no necesita el hash.
    $query = $pdo->query(
      "SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.rol, u.fecha_registro,
              (SELECT COUNT(*) FROM SOLICITUD_SERVICIO s WHERE s.id_usuario = u.id_usuario) AS formularios
       FROM USUARIO u
       ORDER BY u.id_usuario"
    );
 
    return view($renderer, $response, "admin/usuarios.php", [
      "usuarios" => $query->fetchAll(),
    ]);
  });
 
  $group->get("/create", function (
    Request $request,
    Response $response
  ) use ($renderer) {
    return view($renderer, $response, "usuarios/create.php", [
      "roles" => ROLES_VALIDOS,
    ]);
  });
 
 $group->get("/update/{id}", function (
    Request $request,
    Response $response,
    array $args
  ) use ($renderer, $database) {
    $pdo = $database->getConnection();
    $query = $pdo->prepare("SELECT * FROM USUARIO WHERE id_usuario = ?");
    $query->execute([$args["id"]]);
    $usuario = $query->fetch();
 
    if (!$usuario) {
      return view($renderer, $response->withStatus(404), "usuarios/not_found.php");
    }
 
    return view($renderer, $response, "usuarios/update.php", [
      "usuario" => $usuario,
      "roles" => ROLES_VALIDOS,
    ]);
  });
 
  $group->get("/{id}", function (
    Request $request,
    Response $response,
    array $args
  ) use ($renderer, $database) {
    $pdo = $database->getConnection();
    $query = $pdo->prepare("SELECT * FROM USUARIO WHERE id_usuario = ?");
    $query->execute([$args["id"]]);
    $usuario = $query->fetch();
 
    if (!$usuario) {
      return view($renderer, $response->withStatus(404), "usuarios/not_found.php");
    }
 
    return view($renderer, $response, "usuarios/show.php", [
      "usuario" => $usuario,
    ]);
  });
 
  $group->post("", function (
    Request $request,
    Response $response
  ) use ($renderer, $database) {
    $body = $request->getParsedBody();
 
    $nombre = trim($body["nombre"] ?? "");
    $apellido = trim($body["apellido"] ?? "");
    $email = trim($body["email"] ?? "");
    $contrasena = $body["contrasena"] ?? "";
    $rol = $body["rol"] ?? "cliente";
    if (!in_array($rol, ROLES_VALIDOS, true)) {
      $rol = "cliente";
    }
 
    if ($nombre === "" || $apellido === "" || $email === "" || $contrasena === "") {
      return view($renderer, $response->withStatus(422), "usuarios/create.php", [
        "roles" => ROLES_VALIDOS,
        "error" => "Completá todos los campos obligatorios.",
        "old" => $body,
      ]);
    }
 
    try {
      $database->runTransaction(function ($pdo) use ($nombre, $apellido, $email, $contrasena, $rol) {
        $query = $pdo->prepare(
          "INSERT INTO USUARIO (nombre, apellido, email, contrasena, rol, fecha_registro)
           VALUES (?, ?, ?, ?, ?, CURDATE())"
        );
        $query->execute([
          $nombre,
          $apellido,
          $email,
          password_hash($contrasena, PASSWORD_DEFAULT),
          $rol,
        ]);
      });
    } catch (PDOException $e) {
      // 1062 = entrada duplicada en MySQL (email UNIQUE repetido)
      if (($e->errorInfo[1] ?? null) === 1062) {
        return view($renderer, $response->withStatus(409), "usuarios/create.php", [
          "roles" => ROLES_VALIDOS,
          "error" => "Ya existe un usuario con ese email.",
          "old" => $body,
        ]);
      }
      throw $e;
    }
 
    return $response->withHeader("Location", "/admin/usuarios")->withStatus(303);
  });
 
  $group->put("/{id}", function (
    Request $request,
    Response $response,
    array $args
  ) use ($renderer, $database) {
    $id = $args["id"];
    $body = $request->getParsedBody();
 
    $nombre = trim($body["nombre"] ?? "");
    $apellido = trim($body["apellido"] ?? "");
    $email = trim($body["email"] ?? "");
    $contrasena = $body["contrasena"] ?? "";
    $rol = $body["rol"] ?? "cliente";
    if (!in_array($rol, ROLES_VALIDOS, true)) {
      $rol = "cliente";
    }
 
    $usuarioForm = [
      "id_usuario" => $id,
      "nombre" => $nombre,
      "apellido" => $apellido,
      "email" => $email,
      "rol" => $rol,
    ];
 
    if ($nombre === "" || $apellido === "" || $email === "") {
      return view($renderer, $response->withStatus(422), "usuarios/update.php", [
        "usuario" => $usuarioForm,
        "roles" => ROLES_VALIDOS,
        "error" => "Nombre, apellido y email son obligatorios.",
      ]);
    }
 
    try {
      $database->runTransaction(function ($pdo) use ($id, $nombre, $apellido, $email, $contrasena, $rol) {
        if ($contrasena !== "") {
          $query = $pdo->prepare(
            "UPDATE USUARIO
             SET nombre = ?, apellido = ?, email = ?, contrasena = ?, rol = ?
             WHERE id_usuario = ?"
          );
          $query->execute([$nombre, $apellido, $email, password_hash($contrasena, PASSWORD_DEFAULT), $rol, $id]);
        } else {
          $query = $pdo->prepare(
            "UPDATE USUARIO
             SET nombre = ?, apellido = ?, email = ?, rol = ?
             WHERE id_usuario = ?"
          );
          $query->execute([$nombre, $apellido, $email, $rol, $id]);
        }
      });
    } catch (PDOException $e) {
      if (($e->errorInfo[1] ?? null) === 1062) {
        return view($renderer, $response->withStatus(409), "usuarios/update.php", [
          "usuario" => $usuarioForm,
          "roles" => ROLES_VALIDOS,
          "error" => "Ya existe otro usuario con ese email.",
        ]);
      }
      throw $e;
    }
 
    return $response->withHeader("Location", "/admin/usuarios/" . $id)->withStatus(303);
  });
 
  $group->delete("/{id}", function (
    Request $request,
    Response $response,
    array $args
  ) use ($database) {
    $id = $args["id"];
 
    // Evita que el admin se borre a sí mismo y se quede sin acceso al panel
    if ((string) $id === (string) ($_SESSION["user_id"] ?? "")) {
      return $response->withHeader("Location", "/admin/usuarios")->withStatus(303);
    }
 
    $database->runTransaction(function ($pdo) use ($id) {
      // Primero la fila hija (CLIENTE tiene FK a USUARIO), después el usuario.
      $query = $pdo->prepare("DELETE FROM CLIENTE WHERE id_usuario = ?");
      $query->execute([$id]);
 
      $query = $pdo->prepare("DELETE FROM USUARIO WHERE id_usuario = ?");
      $query->execute([$id]);
    });
 
    return $response->withHeader("Location", "/admin/usuarios")->withStatus(303);
  });
 
})->add("adminMiddleware")->add("authMiddleware");

// /usuarios ya no existe como pantalla: la gestión está en el panel
$app->get("/usuarios", function ($request, $response) {
  return $response->withHeader("Location", "/admin/usuarios")->withStatus(302);
});

/* Pedir servicio: formulario para clientes con sesión iniciada */
$app->group("/pedir-servicio", function (RouteCollectorProxy $group) use ($renderer, $database) {

  $group->get("", function (
    Request $request,
    Response $response
  ) use ($renderer) {
    return view($renderer, $response, "servicio/form.php", [
      "tipos" => TIPOS_SERVICIO,
      "enviado" => isset($request->getQueryParams()["enviado"]),
    ]);
  });

  $group->post("", function (
    Request $request,
    Response $response
  ) use ($renderer, $database) {
    $body = $request->getParsedBody();

    $tipo = $body["tipo_servicio"] ?? "";
    $empresa = trim($body["empresa"] ?? "");
    $telefono = trim($body["telefono"] ?? "");
    $descripcion = trim($body["descripcion"] ?? "");

    $error = null;
    if (!array_key_exists($tipo, TIPOS_SERVICIO)) {
      $error = "Elegí un tipo de servicio.";
    } elseif (largoTexto($descripcion) < 10) {
      $error = "Contanos un poco más de lo que necesitás (mínimo 10 caracteres).";
    } elseif (largoTexto($descripcion) > 2000) {
      $error = "La descripción es demasiado larga (máximo 2000 caracteres).";
    } elseif (largoTexto($empresa) > 200) {
      $error = "El nombre de la empresa es demasiado largo.";
    } elseif ($telefono !== "" && !preg_match('/^[0-9+()\-\s]{6,30}$/', $telefono)) {
      $error = "El teléfono no es válido.";
    }

    if ($error !== null) {
      return view($renderer, $response->withStatus(422), "servicio/form.php", [
        "tipos" => TIPOS_SERVICIO,
        "error" => $error,
        "old" => $body,
      ]);
    }

    // El usuario sale de la sesión (lo dejó authMiddleware), nunca del formulario.
    $idUsuario = $request->getAttribute("user_id");

    $guardado = $database->runTransaction(function ($pdo) use ($idUsuario, $tipo, $empresa, $telefono, $descripcion) {
      // La sesión puede ser vieja (ej. borraron al usuario): si ya no existe, no se puede guardar.
      $existe = $pdo->prepare("SELECT 1 FROM USUARIO WHERE id_usuario = ?");
      $existe->execute([$idUsuario]);
      if (!$existe->fetch()) {
        return false;
      }

      $query = $pdo->prepare(
        "INSERT INTO SOLICITUD_SERVICIO (id_usuario, tipo_servicio, empresa, telefono, descripcion)
         VALUES (?, ?, ?, ?, ?)"
      );
      $query->execute([
        $idUsuario,
        $tipo,
        $empresa !== "" ? $empresa : null,
        $telefono !== "" ? $telefono : null,
        $descripcion,
      ]);

      return true;
    });

    if (!$guardado) {
      $_SESSION = [];
      session_destroy();

      return $response->withHeader("Location", "/auth/login")->withStatus(303);
    }

    return $response->withHeader("Location", "/pedir-servicio?enviado=1")->withStatus(303);
  });

})->add("authMiddleware");

/* Panel de administración: solo usuarios con rol admin */
$app->group("/admin", function (RouteCollectorProxy $group) use ($renderer, $database) {

  $group->get("", function (
    Request $request,
    Response $response
  ) use ($renderer, $database) {
    $pdo = $database->getConnection();
    $query = $pdo->query(
      "SELECT (SELECT COUNT(*) FROM SOLICITUD_SERVICIO) AS formularios,
              (SELECT COUNT(*) FROM USUARIO) AS usuarios"
    );

    return view($renderer, $response, "admin/index.php", [
      "totales" => $query->fetch(),
    ]);
  });

  $group->get("/formularios", function (
    Request $request,
    Response $response
  ) use ($renderer, $database) {
    $pdo = $database->getConnection();
    $query = $pdo->query(
      "SELECT s.id_solicitud, s.tipo_servicio, s.empresa, s.fecha_solicitud,
              u.nombre, u.apellido, u.email
       FROM SOLICITUD_SERVICIO s
       JOIN USUARIO u ON u.id_usuario = s.id_usuario
       ORDER BY s.fecha_solicitud DESC, s.id_solicitud DESC"
    );

    return view($renderer, $response, "admin/formularios.php", [
      "formularios" => $query->fetchAll(),
    ]);
  });

  $group->get("/formularios/{id}", function (
    Request $request,
    Response $response,
    array $args
  ) use ($renderer, $database) {
    $pdo = $database->getConnection();
    $query = $pdo->prepare(
      "SELECT s.*, u.nombre, u.apellido, u.email
       FROM SOLICITUD_SERVICIO s
       JOIN USUARIO u ON u.id_usuario = s.id_usuario
       WHERE s.id_solicitud = ?"
    );
    $query->execute([$args["id"]]);
    $formulario = $query->fetch();

    if (!$formulario) {
      return view($renderer, $response->withStatus(404), "admin/not_found.php");
    }

    return view($renderer, $response, "admin/formulario.php", [
      "formulario" => $formulario,
    ]);
  });

})->add("adminMiddleware")->add("authMiddleware");

$app->addBodyParsingMiddleware();
$app->addRoutingMiddleware();
$app->addErrorMiddleware($debug, true, true);
$app->add("logMiddleware");
$app->add(new MethodOverrideMiddleware());

return $app;



  /*
$app->get("/usuarios", function (
  Request $request, 
  Response $response) 
  use ($renderer, $database) {
  
   * 1. Obtener la conexion
   * 2. Preparar query
   * 3. Ejecutar
   * 4. Fetch
   

  $pdo = $database->getConnection();
  
  $query = $pdo->prepare("SELECT * FROM USUARIO");
  $query->execute();

  $usuarios = $query->fetchAll();

   return view($renderer, $response, "usuarios/index.php", [
    "usuarios" => $usuarios
   ]);
});
*/