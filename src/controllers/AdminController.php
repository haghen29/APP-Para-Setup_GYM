<?php

/*
 * =====================================================================
 * CAPA: CONTROLADOR (lógica de la aplicación)
 * =====================================================================
 * QUÉ HACE: maneja el panel de administración (solo admin; las rutas pasan
 * por authMiddleware y adminMiddleware).
 *   - El resumen con los totales.
 *   - La lista de formularios ("Pedir servicio") recibidos y el detalle de cada uno.
 * Le pide los datos a UsuarioModel y SolicitudModel y elige la vista.
 * (El CRUD de usuarios del panel está en UsuarioController.)
 * =====================================================================
 */

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

class AdminController extends Controller
{
  /**
   * Recibe el motor de vistas y los dos modelos que usa el panel.
   */
  public function __construct(
    PhpRenderer $renderer,
    private UsuarioModel $usuarios,
    private SolicitudModel $solicitudes,
  ) {
    parent::__construct($renderer);
  }

  /**
   * GET /admin -> resumen: cuántos formularios y cuántos usuarios hay.
   */
  public function resumen(Request $request, Response $response): Response
  {
    return $this->render($response, "admin/index.php", [
      "totales" => [
        "formularios" => $this->solicitudes->contar(),
        "usuarios" => $this->usuarios->contar(),
      ],
    ]);
  }

  /**
   * GET /admin/formularios -> tabla con todos los pedidos, el más nuevo arriba.
   */
  public function listarFormularios(Request $request, Response $response): Response
  {
    return $this->render($response, "admin/formularios.php", [
      "formularios" => $this->solicitudes->listarConCliente(),
    ]);
  }

  /**
   * GET /admin/formularios/{id} -> detalle de un pedido.
   * Si el número no existe, muestra "Formulario no encontrado" (404).
   */
  public function verFormulario(Request $request, Response $response, array $args): Response
  {
    $formulario = $this->solicitudes->buscarPorId($args["id"]);

    if ($formulario === null) {
      return $this->render($response, "admin/not_found.php", [], 404);
    }

    return $this->render($response, "admin/formulario.php", ["formulario" => $formulario]);
  }
}
