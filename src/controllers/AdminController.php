<?php

/*
 * =====================================================================
 * CAPA: CONTROLLERS (interfaz)
 * =====================================================================
 * QUÉ ES: el "contrato" del panel admin (resumen y formularios recibidos).
 * La implementación está en impl/AdminControllerImpl.php.
 * =====================================================================
 */

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

interface AdminController
{
  /** GET /admin -> resumen con los totales. */
  public function resumen(Request $request, Response $response): Response;

  /** GET /admin/formularios -> lista de pedidos recibidos. */
  public function listarFormularios(Request $request, Response $response): Response;

  /** GET /admin/formularios/{id} -> detalle de un pedido (404 si no existe). */
  public function verFormulario(Request $request, Response $response, array $args): Response;
}
