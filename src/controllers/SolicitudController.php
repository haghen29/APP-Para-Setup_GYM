<?php

/*
 * =====================================================================
 * CAPA: CONTROLADOR (lógica de la aplicación)
 * =====================================================================
 * QUÉ HACE: maneja el formulario "Pedir servicio" que usan los clientes
 * con sesión iniciada (la ruta está protegida por authMiddleware).
 *   - Muestra el formulario.
 *   - Valida lo que mandó el cliente.
 *   - Le pide a SolicitudModel que guarde el pedido.
 * =====================================================================
 */

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

class SolicitudController extends Controller
{
  /**
   * Recibe el motor de vistas y el modelo de pedidos.
   */
  public function __construct(PhpRenderer $renderer, private SolicitudModel $solicitudes)
  {
    parent::__construct($renderer);
  }

  /**
   * GET /pedir-servicio -> muestra el formulario con la lista de tipos de servicio.
   * Si la dirección trae ?enviado=1, la vista muestra "¡Recibimos tu pedido!".
   */
  public function mostrarFormulario(Request $request, Response $response): Response
  {
    return $this->render($response, "servicio/form.php", [
      "tipos" => TIPOS_SERVICIO,
      "enviado" => isset($request->getQueryParams()["enviado"]),
    ]);
  }

  /**
   * POST /pedir-servicio -> valida y guarda el pedido.
   * 1. Valida: tipo de la lista, descripción de 10 a 2000 caracteres,
   *    empresa hasta 200 y teléfono solo con números, espacios, +, - y ( ).
   * 2. Si algo falla, vuelve al formulario con el error (422).
   * 3. El id del usuario sale de la SESIÓN (lo dejó authMiddleware), nunca del
   *    formulario: así nadie puede mandar un pedido a nombre de otro.
   * 4. Si el usuario ya no existe (lo borraron), cierra la sesión y manda al login.
   */
  public function enviar(Request $request, Response $response): Response
  {
    $body = $request->getParsedBody();

    $tipo = $body["tipo_servicio"] ?? "";
    $empresa = trim($body["empresa"] ?? "");
    $telefono = trim($body["telefono"] ?? "");
    $descripcion = trim($body["descripcion"] ?? "");

    $error = null;
    if (!array_key_exists($tipo, TIPOS_SERVICIO)) {
      $error = "Elegí un tipo de servicio.";
    } elseif ($this->largoTexto($descripcion) < 10) {
      $error = "Contanos un poco más de lo que necesitás (mínimo 10 caracteres).";
    } elseif ($this->largoTexto($descripcion) > 2000) {
      $error = "La descripción es demasiado larga (máximo 2000 caracteres).";
    } elseif ($this->largoTexto($empresa) > 200) {
      $error = "El nombre de la empresa es demasiado largo.";
    } elseif ($telefono !== "" && !preg_match('/^[0-9+()\-\s]{6,30}$/', $telefono)) {
      $error = "El teléfono no es válido.";
    }

    if ($error !== null) {
      return $this->render($response, "servicio/form.php", [
        "tipos" => TIPOS_SERVICIO,
        "error" => $error,
        "old" => $body,
      ], 422);
    }

    $idUsuario = (string) $request->getAttribute("user_id");

    $guardado = $this->solicitudes->crear($idUsuario, $tipo, $empresa, $telefono, $descripcion);

    if (!$guardado) {
      $_SESSION = [];
      session_destroy();

      return $this->redirigir($response, "/auth/login", 303);
    }

    return $this->redirigir($response, "/pedir-servicio?enviado=1", 303);
  }

  /**
   * Cuenta las letras de un texto (no los bytes), así "ñ" o "á" cuentan como 1.
   * No depende de la extensión mbstring, que no siempre está activada.
   */
  private function largoTexto(string $texto): int
  {
    return (int) preg_match_all('/./su', $texto);
  }
}
