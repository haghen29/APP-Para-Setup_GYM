<?php

/*
 * =====================================================================
 * CAPA: CONTROLLERS (implementación)
 * =====================================================================
 * QUÉ HACE: atiende las páginas públicas más simples: la landing de
 * Codegym Net y las redirecciones de direcciones viejas.
 *
 * Se saltea Service: no hay reglas de negocio ni datos que leer, solo se
 * muestra una vista fija o se redirige. Si la landing llega a mostrar datos
 * de la base (por ejemplo, servicios destacados), crear un InicioService.
 * =====================================================================
 */

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

class InicioControllerImpl extends BaseController implements InicioController
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
