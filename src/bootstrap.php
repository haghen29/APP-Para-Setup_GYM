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
$app->get("/", function ($request, $response) use ($renderer) {
  return view($renderer, $response, "index.php");
});

/*
// tp7
// ruta productos
$app->get("/productos", function (
  $request,
  $response,
) use ($renderer){
  return view($renderer, $response, "entidad/index2.php");
});

// ruta productos/id
$app->get("/productos/{id}", function (
  $request,
  $response,
  array $params,
) use ($renderer){
  return view($renderer, $response, "entidad/show.php", [
    "id" => $params["id"]
  ]);
});

// ruta create/entidades
$app->get("/create/entidades", function (
  Request $request,
  Response $response,
) use ($renderer){
  return view($renderer, $response, "entidad/store.php");
});

//tp8
$app->post("/entidad", function (
  Request $request,
  Response $response,
) use ($renderer){
  $body = $request->getParsedBody();

  return view($renderer, $response, "entidad/created.php", [
    "nombre"      => $body["nombre"] ?? "",
    "precio"      => $body["precio"] ?? "",
    "descripcion" => $body["descripcion"] ?? "",
  ]);
});

//tp9
$app->get("/entidad", function (
  Request $request,
  Response $response,
) use ($renderer){

  $productos = [
    [ 'id' => 1, 'name' => 'Camiseta de futbol', 'price' => 15000],
    [ 'id' => 2, 'name' => 'Botines', 'price' => 45000],
    [ 'id' => 3, 'name' => 'Pelota', 'price' => 2000],
    [ 'id' => 4, 'name' => 'Guantes de arquero', 'price' => 8000],
    [ 'id' => 5, 'name' => 'Medias', 'price' => 1500],
  ];

  $queryParams = $request->getQueryParams();
  $limit = $queryParams['limit'] ?? null;

  if ($limit !== null && is_numeric($limit)) {
    $productos = array_slice($productos, 0, (int)$limit);
  }

  return view($renderer, $response, "entidad/index2.php", [
    "productos" => $productos
  ]);
});
*/

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
$app->addErrorMiddleware($debug, true, true);

return $app;
