<?php

/*
 * =====================================================================
 * CAPA: PERSISTENCE (interfaz)
 * =====================================================================
 * QUÉ ES: el "contrato" de acceso a las tablas USUARIO y CLIENTE.
 * Dice QUÉ se puede hacer con los datos de usuarios, no CÓMO.
 * El "cómo" (el SQL) está en impl/UsuarioDAOImpl.php.
 *
 * Las capas de arriba (Services) dependen de esta interfaz, nunca de la
 * clase Impl: así se podría cambiar la base sin tocar el resto.
 * =====================================================================
 */

interface UsuarioDAO
{
  /** Busca un usuario por email (para el login). Devuelve la fila o null. */
  public function buscarPorEmail(string $email): ?array;

  /** Busca un usuario por id. Devuelve la fila completa o null. */
  public function buscarPorId(string $id): ?array;

  /** Lista todos los usuarios con cuántos formularios mandó cada uno (sin contraseña). */
  public function listarConCantidadDeFormularios(): array;

  /** Cuenta cuántos usuarios hay. */
  public function contar(): int;

  /** Dice si existe un usuario con ese id. */
  public function existe(string $id): bool;

  /**
   * Inserta un usuario. La contraseña llega YA encriptada.
   * Devuelve false si el email ya existe.
   */
  public function crear(string $nombre, string $apellido, string $email, string $contrasenaHash, string $rol): bool;

  /**
   * Actualiza un usuario. Si $contrasenaHash es null, la contraseña no se cambia.
   * Devuelve false si el email ya lo usa otro usuario.
   */
  public function actualizar(string $id, string $nombre, string $apellido, string $email, ?string $contrasenaHash, string $rol): bool;

  /** Borra un usuario (y su fila de CLIENTE) en una transacción. */
  public function eliminar(string $id): void;
}
