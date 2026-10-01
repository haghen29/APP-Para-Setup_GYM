<?php

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Server\RequestHandlerInterface as RequestHandler;
use Psr\Http\Message\ResponseInterface as Response;

/**
 * Middleware global: registra cada pedido (fecha, método, ruta, estado y tiempo).
 */
function logMiddleware(Request $request, RequestHandler $handler): Response
{
  // 1. Registrar el tiempo inicial ANTES de ejecutar la ruta.
  $inicio = hrtime(true);

  // 2. Permitir que la ruta se ejecute y obtener su respuesta.
  $response = $handler->handle($request);

  // 3. Calcular cuánto tardó, en milisegundos (hrtime devuelve nanosegundos).
  $milisegundos = (hrtime(true) - $inicio) / 1_000_000;

  // 4. Construir la línea de log.
  $linea = sprintf(
    "[%s] %s %s %d %.2fms\n",
    date("Y-m-d H:i:s"),
    $request->getMethod(),
    $request->getUri()->getPath(),
    $response->getStatusCode(),
    $milisegundos,
  );

  // 5. Imprimir en la consola (donde corre composer serve)
  //    y agregar la línea al final de logs/app.log.
  file_put_contents("php://stderr", $linea);

  $directorio = __DIR__ . "/../../logs";
  if (!is_dir($directorio)) {
    mkdir($directorio, 0777, true);
  }
  file_put_contents($directorio . "/app.log", $linea, FILE_APPEND | LOCK_EX);

  // 6. Devolver la respuesta SIN MODIFICAR.
  return $response;
}
