<?php

/*
 * =====================================================================
 * CAPA: CONTROLLERS (implementación)
 * =====================================================================
 * QUÉ HACE: el CRUD de usuarios del panel admin (TPN11):
 *   C = crear, R = ver/listar, U = actualizar, D = borrar.
 * Solo lo usa el admin (las rutas pasan por authMiddleware y adminMiddleware).
 * Valida los formularios, le pide a UsuarioService que lea o guarde
 * (las reglas de negocio están ahí) y elige qué vista mostrar.
 * A diferencia del registro público, acá el admin SÍ elige el rol.
 * =====================================================================
 */

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

class UsuarioControllerImpl extends BaseController implements UsuarioController
{
  /**
   * Recibe el motor de vistas y el servicio de usuarios (por su interfaz).
   */
  public function __construct(PhpRenderer $renderer, private UsuarioService $usuarios)
  {
    parent::__construct($renderer);
  }

  /**
   * GET /admin/usuarios -> tabla de usuarios con cuántos formularios mandó cada uno.
   */
  public function listar(Request $request, Response $response): Response
  {
    return $this->render($response, "admin/usuarios.php", [
      "usuarios" => $this->usuarios->listar(),
    ]);
  }

  /**
   * GET /admin/usuarios/{id} -> datos de un usuario. Si no existe, 404.
   */
  public function ver(Request $request, Response $response, array $args): Response
  {
    $usuario = $this->usuarios->buscar($args["id"]);

    if ($usuario === null) {
      return $this->render($response, "usuarios/not_found.php", [], 404);
    }

    return $this->render($response, "usuarios/show.php", ["usuario" => $usuario]);
  }

  /**
   * GET /admin/usuarios/create -> formulario vacío para crear un usuario.
   */
  public function mostrarCrear(Request $request, Response $response): Response
  {
    return $this->render($response, "usuarios/create.php", ["roles" => ROLES_VALIDOS]);
  }

  /**
   * POST /admin/usuarios -> guarda el usuario nuevo.
   * Si falta un dato, vuelve al formulario (422). Si el email ya existe, avisa (409).
   * Si sale bien, vuelve a la lista.
   */
  public function crear(Request $request, Response $response): Response
  {
    $body = $request->getParsedBody();
    $datos = $this->leerFormulario($body);

    if ($datos["nombre"] === "" || $datos["apellido"] === "" || $datos["email"] === "" || $datos["contrasena"] === "") {
      return $this->render($response, "usuarios/create.php", [
        "roles" => ROLES_VALIDOS,
        "error" => "Completá todos los campos obligatorios.",
        "old" => $body,
      ], 422);
    }

    $guardado = $this->usuarios->crear(
      $datos["nombre"],
      $datos["apellido"],
      $datos["email"],
      $datos["contrasena"],
      $datos["rol"],
    );

    if (!$guardado) {
      return $this->render($response, "usuarios/create.php", [
        "roles" => ROLES_VALIDOS,
        "error" => "Ya existe un usuario con ese email.",
        "old" => $body,
      ], 409);
    }

    return $this->redirigir($response, "/admin/usuarios", 303);
  }

  /**
   * GET /admin/usuarios/update/{id} -> formulario de edición con los datos actuales.
   * Si el usuario no existe, 404.
   */
  public function mostrarEditar(Request $request, Response $response, array $args): Response
  {
    $usuario = $this->usuarios->buscar($args["id"]);

    if ($usuario === null) {
      return $this->render($response, "usuarios/not_found.php", [], 404);
    }

    return $this->render($response, "usuarios/update.php", [
      "usuario" => $usuario,
      "roles" => ROLES_VALIDOS,
    ]);
  }

  /**
   * PUT /admin/usuarios/{id} -> guarda los cambios.
   * (El formulario manda POST con _METHOD=PUT y MethodOverrideMiddleware lo convierte.)
   * Nombre, apellido y email son obligatorios; si la contraseña viene vacía no se cambia.
   */
  public function actualizar(Request $request, Response $response, array $args): Response
  {
    $id = $args["id"];
    $datos = $this->leerFormulario($request->getParsedBody());

    // Lo que se vuelve a mostrar si hay un error (sin la contraseña)
    $usuarioForm = [
      "id_usuario" => $id,
      "nombre" => $datos["nombre"],
      "apellido" => $datos["apellido"],
      "email" => $datos["email"],
      "rol" => $datos["rol"],
    ];

    if ($datos["nombre"] === "" || $datos["apellido"] === "" || $datos["email"] === "") {
      return $this->render($response, "usuarios/update.php", [
        "usuario" => $usuarioForm,
        "roles" => ROLES_VALIDOS,
        "error" => "Nombre, apellido y email son obligatorios.",
      ], 422);
    }

    $guardado = $this->usuarios->actualizar(
      $id,
      $datos["nombre"],
      $datos["apellido"],
      $datos["email"],
      $datos["contrasena"],
      $datos["rol"],
    );

    if (!$guardado) {
      return $this->render($response, "usuarios/update.php", [
        "usuario" => $usuarioForm,
        "roles" => ROLES_VALIDOS,
        "error" => "Ya existe otro usuario con ese email.",
      ], 409);
    }

    return $this->redirigir($response, "/admin/usuarios/" . $id, 303);
  }

  /**
   * DELETE /admin/usuarios/{id} -> borra al usuario y vuelve a la lista.
   * Le pasa al Service quién está usando el panel: la regla de "no borrarse
   * a sí mismo" está en UsuarioService.
   */
  public function eliminar(Request $request, Response $response, array $args): Response
  {
    $this->usuarios->eliminar((string) $args["id"], (string) ($_SESSION["user_id"] ?? ""));

    return $this->redirigir($response, "/admin/usuarios", 303);
  }

  /**
   * Saca los datos del formulario de usuario y los limpia (trim).
   * El rol se pasa tal cual: UsuarioService decide si es válido.
   */
  private function leerFormulario(?array $body): array
  {
    return [
      "nombre" => trim($body["nombre"] ?? ""),
      "apellido" => trim($body["apellido"] ?? ""),
      "email" => trim($body["email"] ?? ""),
      "contrasena" => $body["contrasena"] ?? "",
      "rol" => (string) ($body["rol"] ?? "cliente"),
    ];
  }
}
