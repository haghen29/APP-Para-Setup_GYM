<?php

/*
 * =====================================================================
 * CAPA: CONTROLADOR (lógica de la aplicación)
 * =====================================================================
 * QUÉ HACE: atiende las páginas públicas más simples: la landing de
 * Codegym Net y las redirecciones de direcciones viejas.
 * No necesita la base de datos.
 * =====================================================================
 */

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class InicioController extends Controller
{
  /**
   * GET /index -> muestra la landing (src/views/index.php).
   */
  public function landing(Request $request, Response $response): Response
  {
    return $this->render($response, "index.php");
  }

  /**
   * GET / -> la raíz del sitio lleva a la landing.
   */
  public function redirigirALanding(Request $request, Response $response): Response
  {
    return $this->redirigir($response, "/index");
  }

  /**
   * GET /usuarios -> dirección vieja: la lista de usuarios ahora está en el panel admin.
   */
  public function redirigirAUsuariosAdmin(Request $request, Response $response): Response
  {
    return $this->redirigir($response, "/admin/usuarios");
  }
}
