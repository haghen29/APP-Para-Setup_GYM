<?php

/*
 * =====================================================================
 * CAPA: SERVICES (interfaz)
 * =====================================================================
 * QUÉ ES: el "contrato" de las reglas de negocio de los pedidos de
 * "Pedir servicio". La implementación está en impl/SolicitudServiceImpl.php.
 * =====================================================================
 */

interface SolicitudService
{
  /**
   * Guarda un pedido a nombre de un usuario.
   * REGLA: el usuario tiene que seguir existiendo (la sesión puede ser vieja).
   * REGLA: empresa y teléfono vacíos se guardan como "sin dato" (null).
   * Devuelve false si el usuario ya no existe.
   */
  public function crear(string $idUsuario, string $tipo, string $empresa, string $telefono, string $descripcion): bool;

  /** Lista todos los pedidos con los datos del cliente, el más nuevo primero. */
  public function listar(): array;

  /** Busca un pedido por su número. Devuelve la fila o null. */
  public function buscar(string $id): ?array;

  /** Cuenta cuántos pedidos hay. */
  public function contar(): int;
}
