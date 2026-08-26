<?php

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Dotenv\Dotenv;
use Psr\Http\Message\RequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

require __DIR__ . '/../vendor/autoload.php';

// Cargar variables de entorno desde el .env
Dotenv::createImmutable(__DIR__ . '/..')->safeLoad();

$env = $_ENV["APP_ENV"] ?? "prod";
$allowedEnvs = ["dev", "prod"];

if (!in_array($env, $allowedEnvs, true)) {
  throw new RuntimeException("APP_ENV inválido: $env");
}

$debug = $env === "dev";

// Crear la aplicacion de Slim
$app = AppFactory::create();

// Crear el motor de plantillas
$renderer = new PhpRenderer(
  templatePath: __DIR__ . "/views",
  attributes: ["title" => "PDI | Slim Template 2026"],
);

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
  use ($renderer) {
   return view($renderer, $response, "usuarios/index.php");
});
  
$app->get("/usuarios/create", function (
  Request $request, 
  Response $response) 
  use ($renderer) {
 return view($renderer, $response, "usuarios/create.php");
});

$app->get("/usuarios/update/{id}", function (
  Request $request, 
  Response $response) 
  use ($renderer) {
 return view($renderer, $response, "usuarios/update.php");
});
  
$app->get("/usuarios/{id}", function (
  Request $request, 
  Response $response) 
  use ($renderer) {
 return view($renderer, $response, "usuarios/show.php");
});

$app->post("/usuarios}", function (
  Request $request, 
  Response $response) 
  use ($renderer) {
  $body = $request->getParsedBody();

  return view($renderer, $response, "usuarios/create.php", [
    "nombre" => $body["nombre"] ?? "",
    "apellido" => $body["apellido"] ?? "",
    "email" => $body["email"] ?? "",
    "contraseña" => $body["contraseña"] ?? "",
    "rol" => $body["rol"] ?? null,   //para que se pueda quedar en null tiene que decir "rol" => $body["rol"] ?? null
  ]);
}); 


$app->put("/usuarios/{id}", function (
    Request $request,
    Response $response,
    array $args
) use ($renderer) {
    $id = $args["id"];
    $body = $request->getParsedBody();

    return view($renderer, $response, "usuarios/update.php", [
        "nombre" => $body["nombre"] ?? "",
        "apellido" => $body["apellido"] ?? "",
        "email" => $body["email"] ?? "",
        "contraseña" => $body["contraseña"] ?? "",
        "rol" => $body["rol"] ?? null,
    ]);
});

$app->delete("/usuarios/{id}", function (
    Request $request,
    Response $response,
    array $args
) use ($renderer) {
    $id = $args["id"];
    $body = $request->getParsedBody();
});

$app->addErrorMiddleware($debug, true, true);

return $app;
