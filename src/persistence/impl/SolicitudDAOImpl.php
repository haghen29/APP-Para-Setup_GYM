<?php

/*
 * =====================================================================
 * CAPA: PERSISTENCE (implementación)
 * =====================================================================
 * QUÉ HACE: es el único lugar que habla con la tabla SOLICITUD_SERVICIO.
 * Guarda pedidos nuevos y los lee para el panel admin.
 *
 * QUÉ NO HACE: no valida el formulario (SolicitudController) ni decide si
 * el usuario puede pedir (SolicitudService).
 * =====================================================================
 */

class SolicitudDAOImpl implements SolicitudDAO
{
  // Código de error de MySQL cuando la clave foránea no existe (el usuario fue borrado)
  private const ERROR_CLAVE_FORANEA = 1452;

  public function __construct(private Database $database)
  {
  }

  public function crear(string $idUsuario, string $tipo, ?string $empresa, ?string $telefono, string $descripcion): bool
  {
    try {
      $query = $this->database->getConnection()->prepare(
        "INSERT INTO SOLICITUD_SERVICIO (id_usuario, tipo_servicio, empresa, telefono, descripcion)
         VALUES (?, ?, ?, ?, ?)"
      );
      $query->execute([$idUsuario, $tipo, $empresa, $telefono, $descripcion]);
    } catch (PDOException $e) {
      if (($e->errorInfo[1] ?? null) === self::ERROR_CLAVE_FORANEA) {
        return false;
      }
      throw $e;
    }

    return true;
  }

  public function listarConCliente(): array
  {
    $query = $this->database->getConnection()->query(
      "SELECT s.id_solicitud, s.tipo_servicio, s.empresa, s.fecha_solicitud,
              u.nombre, u.apellido, u.email
       FROM SOLICITUD_SERVICIO s
       JOIN USUARIO u ON u.id_usuario = s.id_usuario
       ORDER BY s.fecha_solicitud DESC, s.id_solicitud DESC"
    );

    return $query->fetchAll();
  }

  public function buscarPorId(string $id): ?array
  {
    $query = $this->database->getConnection()->prepare(
      "SELECT s.*, u.nombre, u.apellido, u.email
       FROM SOLICITUD_SERVICIO s
       JOIN USUARIO u ON u.id_usuario = s.id_usuario
       WHERE s.id_solicitud = ?"
    );
    $query->execute([$id]);

    return $query->fetch() ?: null;
  }

  public function contar(): int
  {
    return (int) $this->database->getConnection()
      ->query("SELECT COUNT(*) FROM SOLICITUD_SERVICIO")
      ->fetchColumn();
  }
}
