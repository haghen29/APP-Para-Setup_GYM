<?php

/*
 * =====================================================================
 * CAPA: CONTROLADOR (lógica de la aplicación) - clase base
 * =====================================================================
 * QUÉ HACE: tiene lo que comparten TODOS los controladores, para no repetirlo:
 *   - mostrar una vista (render)
 *   - mandar al usuario a otra dirección (redirigir)
 * Los demás controladores "heredan" de esta clase (extends Controller).
 * =====================================================================
 */

use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

abstract class Controller
{
  /**
   * Guarda el motor de vistas para que lo usen los controladores hijos.
   */
  public function __construct(protected PhpRenderer $renderer)
  {
  }

  /**
   * Arma una página: mete la vista dentro del molde layouts/base.php
   * (con navbar y footer) usando el helper view() de src/utils/view.php.
   *
   * @param Response $response La respuesta donde se escribe el HTML.
   * @param string   $vista    El archivo de src/views a mostrar (ej. "entidad/login.php").
   * @param array    $datos    Las variables que usa la vista.
   * @param int      $estado   El código HTTP (200 bien, 404 no existe, 422 datos mal, etc.).
   */
  protected function render(Response $response, string $vista, array $datos = [], int $estado = 200): Response
  {
    return view($this->renderer, $response->withStatus($estado), $vista, $datos);
  }

  /**
   * Manda al navegador a otra dirección.
   * 302 = redirección común; 303 = "ya procesé tu formulario, ahora andá acá"
   * (evita que al recargar la página se reenvíe el formulario).
   */
  protected function redirigir(Response $response, string $url, int $estado = 302): Response
  {
    return $response->withHeader("Location", $url)->withStatus($estado);
  }
}
