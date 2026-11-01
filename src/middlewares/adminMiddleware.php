<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;
use Slim\Views\PhpRenderer;

/**
 * Deja pasar solo a los usuarios con rol "admin".
 *
 * - Sin sesión: redirige al login.
 * - Con sesión pero sin rol admin: responde 403 con una pantalla de acceso denegado.
 *
 * Se usa junto con authMiddleware (que corre primero).
 */
function adminMiddleware(Request $request, RequestHandler $handler): Response
{
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }

  if (empty($_SESSION["user_id"])) {
    return (new SlimResponse())
      ->withHeader("Location", "/auth/login")
      ->withStatus(302);
  }

  if (($_SESSION["user_rol"] ?? "") !== "admin") {
    $renderer = new PhpRenderer(
      templatePath: __DIR__ . "/../views",
      attributes: ["title" => "Acceso denegado | CodeGYM Net"],
    );

    return view($renderer, (new SlimResponse())->withStatus(403), "errores/403.php");
  }

  return $handler->handle($request);
}
