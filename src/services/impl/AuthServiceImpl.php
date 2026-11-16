<?php

/*
 * =====================================================================
 * CAPA: SERVICES (implementación)
 * =====================================================================
 * QUÉ HACE: aplica las reglas de negocio de las cuentas.
 * Usa UsuarioDAO (por su interfaz) para leer y guardar.
 *
 * QUÉ NO HACE: no valida formularios (AuthController), no toca la sesión
 * (AuthController) y no escribe SQL (UsuarioDAOImpl).
 * =====================================================================
 */

class AuthServiceImpl implements AuthService
{
  // REGLA: el registro público siempre crea clientes
  private const ROL_REGISTRO = "cliente";

  public function __construct(private UsuarioDAO $usuarios)
  {
  }

  /*
   * REGLA: el rol es SIEMPRE "cliente"; no viene del formulario,
   * así nadie puede registrarse como admin o empleado.
   * REGLA: la contraseña se encripta (password_hash) antes de guardarla.
   */
  public function registrar(string $nombre, string $apellido, string $email, string $contrasena): bool
  {
    return $this->usuarios->crear(
      $nombre,
      $apellido,
      $email,
      password_hash($contrasena, PASSWORD_DEFAULT),
      self::ROL_REGISTRO,
    );
  }

  /*
   * REGLA: la contraseña se compara con password_verify contra la encriptada.
   * REGLA: email inexistente y contraseña incorrecta dan el mismo resultado (null),
   * para no revelar qué emails están registrados.
   * Solo devuelve lo que necesita la sesión (nunca la contraseña).
   */
  public function autenticar(string $email, string $contrasena): ?array
  {
    $usuario = $this->usuarios->buscarPorEmail($email);

    if ($usuario === null || !password_verify($contrasena, $usuario["contrasena"])) {
      return null;
    }

    return [
      "id_usuario" => $usuario["id_usuario"],
      "nombre" => $usuario["nombre"],
      "rol" => $usuario["rol"],
    ];
  }

  /*
   * REGLA: el admin va al panel (/admin); el resto de los roles, a la landing (/index).
   */
  public function rutaInicio(?string $rol): string
  {
    return $rol === "admin" ? "/admin" : "/index";
  }
}
