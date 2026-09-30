<?php

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Dotenv\Dotenv;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';
require __DIR__ . '/database/database.php';

// Cargar variables de entorno desde el .env
Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

$env = $_ENV["APP_ENV"] ?? "prod";
$allowedEnvs = ["dev", "prod"];

if (!in_array($env, $allowedEnvs, true)) {
  throw new RuntimeException("APP_ENV inválido: $env");
}

$debug = $env === "dev";

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

//login
$app->get("/create/login", function (
  Request $request,
  Response $response,
) use ($renderer){
  return view($renderer, $response, "entidad/login.php");
});

$app->get("/create/register", function (
  Request $request,
  Response $response,
) use ($renderer){
  return view($renderer, $response, "entidad/register.php");
});

$app->post("/auth/login", function (
  Request $request,
  Response $response,
) use ($renderer){
  $body = $request->getParsedBody();

  return view($renderer, $response, "entidad/createdusuario.php", [
    "email" => $body["email"] ?? "",
    "contraseña" => $body["contraseña"] ?? "",
  ]);
}); 

/*  TPN11
GET /entidad/ -> Renderiza la vista '/entidad/index.php' con el listado completo de datos.
GET /entidad/create -> Renderiza la vista '/entidad/create.php' con el formulario para crear un nuevo registro.
GET /entidad/update/{id} -> Renderiza la vista '/entidad/update.php' con el formulario para editar un registro existente.
GET /entidad/{id} -> Renderiza la vista '/entidad/show.php' con el detalle de la instancia. Si el id no existe en la base de datos, renderiza '/entidad/not_found.php'.
POST /entidad -> Recibe los datos del formulario de creación ('create.php') y los guarda en la base de datos.
PUT /entidad/{id} -> Recibe los datos del formulario de edición ('update.php') y actualiza el registro correspondiente en la base de datos.
DELETE /entidad/{id} -> Elimina de la base de datos el registro asociado al ID proporcionado.
*/
$app->get("/usuarios", function (
  Request $request, 
  Response $response) 
  use ($renderer, $database) {
  /*
   * 1. Obtener la conexion
   * 2. Preparar query
   * 3. Ejecutar
   * 4. Fetch
   */

  $pdo = $database->getConnection();
  
  $query = $pdo->prepare("SELECT * FROM USUARIO");
  $query->execute();

  $usuarios = $query->fetchAll();

   return view($renderer, $response, "usuarios/index.php", [
    "usuarios" => $usuarios
   ]);
});
  
$app->get("/usuarios/create", function (
  Request $request, 
  Response $response) 
  use ($renderer) {
 return view($renderer, $response, "usuarios/create.php", [
  "roles" => ROLES_VALIDOS,
 ]);
});

$app->get("/usuarios/update/{id}", function (
  Request $request, 
  Response $response,
  array $args
  ) use ($renderer) {
    $pdo = $database->getConnection();
    $query = $pdo->prepare("SELECT * FROM USUARIO WHERE id = ?");
    $query->execute([$args["id"]]);
    $usuario = $query->fetch();

    if (!$usuario) {
    return view($renderer, $response->withStatus(404), "usuarios/not_found.php");
  }

 return view($renderer, $response, "usuarios/update.php", [
    "usuario" => $usuario
   ]);
});
  
$app->get("/usuarios/{id}", function (
  Request $request, 
  Response $response,
  array $args
  ) use ($renderer, $database) {
    $pdo = $database->getConnection();
    $query = $pdo->prepare("SELECT * FROM USUARIO WHERE id = ?");
    $query->execute([$args["id"]]);
    $usuario = $query->fetch();
    if (!$usuario) {
    return view($renderer, $response->withStatus(404), "usuarios/not_found.php");
  }
 return view($renderer, $response, "usuarios/show.php", [
    "usuario" => $usuario
   ]);
});


// POST /usuarios -> guarda un usuario nuevo (con transacción)
$app->post("/usuarios", function (
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
 
  // Validación básica: si falta algo, volvemos al formulario
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
        password_hash($contrasena, PASSWORD_DEFAULT), // nunca guardar en texto plano
        $rol,
      ]);
    });
  } catch (PDOException $e) {
    // 23000 = violación de restricción (email UNIQUE repetido)
    if ($e->getCode() === "23000") {
      return view($renderer, $response->withStatus(409), "usuarios/create.php", [
        "roles" => ROLES_VALIDOS,
        "error" => "Ya existe un usuario con ese email.",
        "old" => $body,
      ]);
    }
    throw $e;
  }
 
  // 303: el navegador sigue con GET al listado
  return $response->withHeader("Location", "/usuarios")->withStatus(303);
});

// PUT /usuarios/{id} -> actualiza un usuario (con transacción)
$app->put("/usuarios/{id}", function (
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
 
  // Para volver a mostrar el formulario con lo ingresado si hay error
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
        // Se cambió la contraseña
        $query = $pdo->prepare(
          "UPDATE USUARIO
           SET nombre = ?, apellido = ?, email = ?, contrasena = ?, rol = ?
           WHERE id_usuario = ?"
        );
        $query->execute([$nombre, $apellido, $email, password_hash($contrasena, PASSWORD_DEFAULT), $rol, $id]);
      } else {
        // Contraseña vacía = se mantiene la actual
        $query = $pdo->prepare(
          "UPDATE USUARIO
           SET nombre = ?, apellido = ?, email = ?, rol = ?
           WHERE id_usuario = ?"
        );
        $query->execute([$nombre, $apellido, $email, $rol, $id]);
      }
    });
  } catch (PDOException $e) {
    if ($e->getCode() === "23000") {
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

// DELETE /usuarios/{id} -> elimina un usuario (con transacción)
$app->delete("/usuarios/{id}", function (
  Request $request,
  Response $response,
  array $args
) use ($database) {
  $id = $args["id"];
 
  $database->runTransaction(function ($pdo) use ($id) {
    // Primero la fila hija (CLIENTE tiene FK a USUARIO), después el usuario.
    // Si algo falla, se revierte todo.
    $query = $pdo->prepare("DELETE FROM CLIENTE WHERE id_usuario = ?");
    $query->execute([$id]);
 
    $query = $pdo->prepare("DELETE FROM USUARIO WHERE id_usuario = ?");
    $query->execute([$id]);
  });
 
  return $response->withHeader("Location", "/usuarios")->withStatus(303);
});

$app->addErrorMiddleware($debug, true, true);
$app->addRoutingMiddleware();
$app->add(new MethodOverrideMiddleware());
$app->addErrorMiddleware($debug, true, true);

return $app;
