<?php

/*
 * =====================================================================
 * CAPA: CONTROLADOR (lógica de la aplicación)
 * =====================================================================
 * QUÉ HACE: maneja las cuentas de los visitantes: registrarse, iniciar
 * sesión y cerrar sesión.
 *   - Valida lo que llega de los formularios.
 *   - Le pide a UsuarioModel que busque o guarde usuarios.
 *   - Guarda en la sesión quién entró (user_id, user_nombre, user_rol).
 *   - Elige qué vista mostrar o a dónde redirigir.
 * =====================================================================
 */

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;
use Slim\Views\PhpRenderer;

class AuthController extends Controller
{
  /**
   * Recibe el motor de vistas y el modelo de usuarios.
   */
  public function __construct(PhpRenderer $renderer, private UsuarioModel $usuarios)
  {
    parent::__construct($renderer);
  }

  /**
   * GET /auth/register -> muestra el formulario de registro.
   * "pagina-auth" le pone el fondo del login (ver public/css/tema.css).
   */
  public function mostrarRegistro(Request $request, Response $response): Response
  {
    return $this->render($response, "entidad/register.php", ["bodyClass" => "pagina-auth"]);
  }

  /**
   * POST /auth/register -> crea la cuenta.
   * 1. Valida: campos completos, email válido, contraseña de 6 o más y que coincidan.
   * 2. Si algo falla, vuelve al formulario con el error y lo ya escrito (422).
   * 3. Guarda el usuario SIEMPRE como "cliente" (el rol no viene del formulario).
   * 4. Si el email ya existe, avisa (409). Si sale bien, manda al login.
   */
  public function registrar(Request $request, Response $response): Response
  {
    $body = $request->getParsedBody();

    $nombre = trim($body["nombre"] ?? "");
    $apellido = trim($body["apellido"] ?? "");
    $email = trim($body["email"] ?? "");
    $contrasena = $body["contrasena"] ?? "";
    $confirmar = $body["confirmar"] ?? "";

    $error = null;
    if ($nombre === "" || $apellido === "" || $email === "" || $contrasena === "") {
      $error = "Completá todos los campos.";
    } elseif (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
      $error = "El email no es válido.";
    } elseif (strlen($contrasena) < 6) {
      $error = "La contraseña debe tener al menos 6 caracteres.";
    } elseif ($contrasena !== $confirmar) {
      $error = "Las contraseñas no coinciden.";
    }

    if ($error !== null) {
      return $this->render($response, "entidad/register.php", [
        "bodyClass" => "pagina-auth",
        "error" => $error,
        "old" => $body,
      ], 422);
    }

    $guardado = $this->usuarios->crear($nombre, $apellido, $email, $contrasena, "cliente");

    if (!$guardado) {
      return $this->render($response, "entidad/register.php", [
        "bodyClass" => "pagina-auth",
        "error" => "Ya existe una cuenta con ese email.",
        "old" => $body,
      ], 409);
    }

    return $this->redirigir($response, "/auth/login?registrado=1", 303);
  }

  /**
   * GET /auth/login -> muestra el login.
   * Si ya hay sesión, no tiene sentido: lo manda a su página de inicio.
   * Muestra "Cuenta creada" si viene del registro, o "Iniciá sesión para continuar"
   * si authMiddleware lo mandó acá desde una página protegida.
   */
  public function mostrarLogin(Request $request, Response $response): Response
  {
    if (!empty($_SESSION["user_id"])) {
      return $this->redirigir($response, $this->rutaInicio($_SESSION["user_rol"] ?? null));
    }

    return $this->render($response, "entidad/login.php", [
      "bodyClass" => "pagina-auth",
      "registrado" => isset($request->getQueryParams()["registrado"]),
      "aviso" => !empty($_SESSION["volver_a"]),
    ]);
  }

  /**
   * POST /auth/login -> revisa email y contraseña e inicia la sesión.
   * - Si falla el email o la contraseña, el mensaje es el mismo (no da pistas). Código 401.
   * - Si anda: cambia el id de sesión (evita la fijación de sesión), guarda los datos
   *   y lo manda a la página que quería ver antes ("volver_a") o a su inicio.
   */
  public function iniciarSesion(Request $request, Response $response): Response
  {
    $body = $request->getParsedBody();
    $email = trim($body["email"] ?? "");
    $contrasena = $body["contrasena"] ?? "";

    $usuario = $this->usuarios->buscarPorEmail($email);

    if ($usuario === null || !password_verify($contrasena, $usuario["contrasena"])) {
      return $this->render($response, "entidad/login.php", [
        "bodyClass" => "pagina-auth",
        "error" => "Email o contraseña incorrectos.",
        "old" => $body,
        "aviso" => !empty($_SESSION["volver_a"]),
      ], 401);
    }

    session_regenerate_id(true);
    $_SESSION["user_id"] = $usuario["id_usuario"];
    $_SESSION["user_nombre"] = $usuario["nombre"];
    $_SESSION["user_rol"] = $usuario["rol"];

    $destino = $_SESSION["volver_a"] ?? $this->rutaInicio($usuario["rol"]);
    unset($_SESSION["volver_a"]);

    return $this->redirigir($response, $destino, 303);
  }

  /**
   * GET /auth/logout -> borra la sesión y vuelve al login.
   */
  public function cerrarSesion(Request $request, Response $response): Response
  {
    $_SESSION = [];
    session_destroy();

    return $this->redirigir($response, "/auth/login");
  }

  /**
   * Decide a dónde va cada rol al iniciar sesión: el admin al panel, el resto a la landing.
   */
  private function rutaInicio(?string $rol): string
  {
    return $rol === "admin" ? "/admin" : "/index";
  }
}
