<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Psr7\Response as SlimResponse;

/**
 * Protege rutas: si no hay un usuario en la sesión, redirige al login.
 * Si lo hay, deja pasar el pedido y le agrega el atributo "user_id".
 *
 * Si el pedido era un GET, guarda la ruta en la sesión ("volver_a") para
 * que después de iniciar sesión el usuario vuelva a la página que quería
 * (por ejemplo, el formulario de "Pedir servicio").
 */
function authMiddleware(Request $request, RequestHandler $handler): Response
{
  if (session_status() !== PHP_SESSION_ACTIVE) {
    session_start();
  }

  $userId = $_SESSION["user_id"] ?? null;

  if ($userId === null || $userId === "") {
    if ($request->getMethod() === "GET") {
      $_SESSION["volver_a"] = $request->getUri()->getPath();
    }

    return (new SlimResponse())
      ->withHeader("Location", "/auth/login")
      ->withStatus(302);
  }

  $request = $request->withAttribute("user_id", $userId);

  return $handler->handle($request);
}
