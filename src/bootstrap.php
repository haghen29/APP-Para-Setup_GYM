<?php

use Slim\Factory\AppFactory;
use Slim\Views\PhpRenderer;
use Dotenv\Dotenv;

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

$app->addErrorMiddleware($debug, true, true);

return $app;



// tp7
// ruta productos
$app->get("/productos", function (
  Request $request,
  Response $response,
) use ($renderer){
  return view($renderer, $response, "productos/index.php");
});

// ruta productos/id
$app->get("/productos/{id}", function (
  Request $request,
  Response $response,
  array $params,
) use ($renderer){
  return view($renderer, $response, "productos/show.php", [
    "id" => $params["id"]
  ]);
});

// ruta create/entidades
$app->get("/create/entidades", function (
  Request $request,
  Response $response,
) use ($renderer){
  return view($renderer, $response, "productos/store.php");
});


//tp8
$app->post("/auth/register", function (
Request $request,
Response $response
) use ($renderer){
  $body = $request->getParsedBody();

  echo var_dump($body);
});
