<?php

/*
 * =====================================================================
 * CAPA: SERVICES (implementación)
 * =====================================================================
 * QUÉ HACE: aplica las reglas de negocio del CRUD de usuarios del panel admin.
 * Usa UsuarioDAO (por su interfaz) para leer y guardar.
 *
 * QUÉ NO HACE: no valida que los campos estén completos (UsuarioController)
 * y no escribe SQL (UsuarioDAOImpl).
 * =====================================================================
 */

class UsuarioServiceImpl implements UsuarioService
{
  // REGLA: rol que se usa cuando llega uno que no existe
  private const ROL_POR_DEFECTO = "cliente";

  public function __construct(private UsuarioDAO $usuarios)
  {
  }

  /*
   * Sin reglas de negocio: solo le pide los datos al DAO.
   * Pasa por el Service igual para que el controller nunca hable con el DAO.
   */
  public function listar(): array
  {
    return $this->usuarios->listarConCantidadDeFormularios();
  }

  /*
   * Sin reglas de negocio: solo le pide los datos al DAO.
   */
  public function buscar(string $id): ?array
  {
    return $this->usuarios->buscarPorId($id);
  }

  /*
   * Sin reglas de negocio: solo le pide los datos al DAO.
   */
  public function contar(): int
  {
    return $this->usuarios->contar();
  }

  /*
   * REGLA: si el rol no es uno de ROLES_VALIDOS, se usa "cliente".
   * REGLA: la contraseña se encripta (password_hash) antes de guardarla.
   */
  public function crear(string $nombre, string $apellido, string $email, string $contrasena, string $rol): bool
  {
    return $this->usuarios->crear(
      $nombre,
      $apellido,
      $email,
      password_hash($contrasena, PASSWORD_DEFAULT),
      $this->rolValido($rol),
    );
  }

  /*
   * REGLA: si el rol no es uno de ROLES_VALIDOS, se usa "cliente".
   * REGLA: contraseña vacía = no se cambia (se le pasa null al DAO);
   * si viene una nueva, se encripta.
   */
  public function actualizar(string $id, string $nombre, string $apellido, string $email, string $contrasena, string $rol): bool
  {
    $contrasenaHash = $contrasena !== "" ? password_hash($contrasena, PASSWORD_DEFAULT) : null;

    return $this->usuarios->actualizar(
      $id,
      $nombre,
      $apellido,
      $email,
      $contrasenaHash,
      $this->rolValido($rol),
    );
  }

  /*
   * REGLA: el admin no se puede borrar a sí mismo; si lo intenta, no pasa nada.
   */
  public function eliminar(string $id, string $idActual): void
  {
    if ($id === $idActual) {
      return;
    }

    $this->usuarios->eliminar($id);
  }

  /*
   * REGLA: solo se aceptan los roles de ROLES_VALIDOS (los mismos del ENUM
   * de USUARIO.rol); cualquier otro valor se convierte en "cliente".
   */
  private function rolValido(string $rol): string
  {
    return in_array($rol, ROLES_VALIDOS, true) ? $rol : self::ROL_POR_DEFECTO;
  }
}
