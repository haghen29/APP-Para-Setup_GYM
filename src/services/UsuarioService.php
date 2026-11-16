<?php

/*
 * =====================================================================
 * CAPA: SERVICES (interfaz)
 * =====================================================================
 * QUÉ ES: el "contrato" de las reglas de negocio del CRUD de usuarios
 * que usa el panel admin. La implementación está en impl/UsuarioServiceImpl.php.
 * =====================================================================
 */

interface UsuarioService
{
  /** Lista todos los usuarios con cuántos formularios mandó cada uno. */
  public function listar(): array;

  /** Busca un usuario por id. Devuelve la fila o null. */
  public function buscar(string $id): ?array;

  /** Cuenta cuántos usuarios hay. */
  public function contar(): int;

  /**
   * Crea un usuario desde el panel admin.
   * REGLA: si el rol no es uno de ROLES_VALIDOS, se usa "cliente".
   * REGLA: la contraseña se guarda encriptada.
   * Devuelve false si el email ya existe.
   */
  public function crear(string $nombre, string $apellido, string $email, string $contrasena, string $rol): bool;

  /**
   * Actualiza un usuario.
   * REGLA: si el rol no es uno de ROLES_VALIDOS, se usa "cliente".
   * REGLA: si la contraseña viene vacía, NO se cambia; si viene, se encripta.
   * Devuelve false si el email ya lo usa otro usuario.
   */
  public function actualizar(string $id, string $nombre, string $apellido, string $email, string $contrasena, string $rol): bool;

  /**
   * Borra un usuario.
   * REGLA: el admin no se puede borrar a sí mismo (se quedaría sin acceso al panel).
   * $idActual es el id de quien está usando el panel.
   */
  public function eliminar(string $id, string $idActual): void;
}
