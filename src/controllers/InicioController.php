<?php

/*
 * =====================================================================
 * CAPA: CONTROLLERS (interfaz)
 * =====================================================================
 * QUÉ ES: el "contrato" de las páginas públicas simples (landing y
 * redirecciones). La implementación está en impl/InicioControllerImpl.php.
 * =====================================================================
 */

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

interface InicioController
{
  /** GET /index -> muestra la landing. */
  public function landing(Request $request, Response $response): Response;

  /** GET / -> redirige a la landing. */
  public function redirigirALanding(Request $request, Response $response): Response;

  /** GET /usuarios -> dirección vieja: redirige al panel admin. */
  public function redirigirAUsuariosAdmin(Request $request, Response $response): Response;
}
