<?php

/*
 * =====================================================================
 * CAPA: CONTROLLERS (interfaz)
 * =====================================================================
 * QUÉ ES: el "contrato" del formulario "Pedir servicio".
 * La implementación está en impl/SolicitudControllerImpl.php.
 * =====================================================================
 */

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

interface SolicitudController
{
  /** GET /pedir-servicio -> muestra el formulario. */
  public function mostrarFormulario(Request $request, Response $response): Response;

  /** POST /pedir-servicio -> valida y guarda el pedido. */
  public function enviar(Request $request, Response $response): Response;
}
