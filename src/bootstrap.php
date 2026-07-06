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
$app->addErrorMiddleware($debug, true, true);

return $app;
