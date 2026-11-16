<?php

/*
 * =====================================================================
 * CAPA: PERSISTENCE (interfaz)
 * =====================================================================
 * QUÉ ES: el "contrato" de acceso a la tabla SOLICITUD_SERVICIO
 * (los pedidos de "Pedir servicio"). El SQL está en impl/SolicitudDAOImpl.php.
 * =====================================================================
 */

interface SolicitudDAO
{
  /**
   * Inserta un pedido. Empresa y teléfono llegan como null si están vacíos.
   * Devuelve false si el usuario ya no existe en la base.
   */
  public function crear(string $idUsuario, string $tipo, ?string $empresa, ?string $telefono, string $descripcion): bool;

  /** Lista todos los pedidos con los datos de quién lo mandó, el más nuevo primero. */
  public function listarConCliente(): array;

  /** Busca un pedido por su número, con los datos del cliente. Devuelve la fila o null. */
  public function buscarPorId(string $id): ?array;

  /** Cuenta cuántos pedidos hay. */
  public function contar(): int;
}
