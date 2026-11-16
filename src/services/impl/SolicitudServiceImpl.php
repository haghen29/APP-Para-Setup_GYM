<?php

/*
 * =====================================================================
 * CAPA: SERVICES (implementación)
 * =====================================================================
 * QUÉ HACE: aplica las reglas de negocio de los pedidos de "Pedir servicio".
 * Usa SolicitudDAO y UsuarioDAO (por sus interfaces).
 *
 * QUÉ NO HACE: no valida el formulario (SolicitudController), no toca la
 * sesión y no escribe SQL (SolicitudDAOImpl).
 * =====================================================================
 */

class SolicitudServiceImpl implements SolicitudService
{
  public function __construct(
    private SolicitudDAO $solicitudes,
    private UsuarioDAO $usuarios,
  ) {
  }

  /*
   * REGLA: el usuario tiene que seguir existiendo. La sesión puede ser vieja
   * (por ejemplo, si un admin lo borró); en ese caso no se guarda nada.
   * Si lo borran justo entre el chequeo y el INSERT, el DAO también devuelve false.
   * REGLA: empresa y teléfono vacíos se guardan como null ("sin dato").
   */
  public function crear(string $idUsuario, string $tipo, string $empresa, string $telefono, string $descripcion): bool
  {
    if (!$this->usuarios->existe($idUsuario)) {
      return false;
    }

    return $this->solicitudes->crear(
      $idUsuario,
      $tipo,
      $empresa !== "" ? $empresa : null,
      $telefono !== "" ? $telefono : null,
      $descripcion,
    );
  }

  /*
   * Sin reglas de negocio: solo le pide los datos al DAO.
   * Pasa por el Service igual para que el controller nunca hable con el DAO.
   */
  public function listar(): array
  {
    return $this->solicitudes->listarConCliente();
  }

  /*
   * Sin reglas de negocio: solo le pide los datos al DAO.
   */
  public function buscar(string $id): ?array
  {
    return $this->solicitudes->buscarPorId($id);
  }

  /*
   * Sin reglas de negocio: solo le pide los datos al DAO.
   */
  public function contar(): int
  {
    return $this->solicitudes->contar();
  }
}
