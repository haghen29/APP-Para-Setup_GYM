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

$app->get("/auth/register", function (
  Request $request,
  Response $response
) use ($renderer) {
  return view($renderer, $response, "entidad/register.php");
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
    return $response->withHeader("Location", "/usuarios")->withStatus(302);
  }
 
  $registrado = isset($request->getQueryParams()["registrado"]);
 
  return view($renderer, $response, "entidad/login.php", [
    "registrado" => $registrado,
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
      "error" => "Email o contraseña incorrectos.",
      "old" => $body,
    ]);
  }
 
  // Credenciales correctas: iniciamos la sesión
  session_regenerate_id(true); // evita la fijación de sesión
  $_SESSION["user_id"] = $usuario["id_usuario"];
  $_SESSION["user_nombre"] = $usuario["nombre"];
  $_SESSION["user_rol"] = $usuario["rol"];
 
  return $response->withHeader("Location", "/usuarios")->withStatus(303);
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

/*  TPN11 */
$app->group("/usuarios", function (RouteCollectorProxy $group) use ($renderer, $database) {
 
  $group->get("", function (
    Request $request,
    Response $response
  ) use ($renderer, $database) {
    $pdo = $database->getConnection();
    $query = $pdo->prepare("SELECT * FROM USUARIO");
    $query->execute();
    $usuarios = $query->fetchAll();
 
    return view($renderer, $response, "usuarios/index.php", [
      "usuarios" => $usuarios,
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
 
    return $response->withHeader("Location", "/usuarios")->withStatus(303);
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
 
    return $response->withHeader("Location", "/usuarios/" . $id)->withStatus(303);
  });
 
  $group->delete("/{id}", function (
    Request $request,
    Response $response,
    array $args
  ) use ($database) {
    $id = $args["id"];
 
    $database->runTransaction(function ($pdo) use ($id) {
      // Primero la fila hija (CLIENTE tiene FK a USUARIO), después el usuario.
      $query = $pdo->prepare("DELETE FROM CLIENTE WHERE id_usuario = ?");
      $query->execute([$id]);
 
      $query = $pdo->prepare("DELETE FROM USUARIO WHERE id_usuario = ?");
      $query->execute([$id]);
    });
 
    return $response->withHeader("Location", "/usuarios")->withStatus(303);
  });
 
})->add("authMiddleware");

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