<?php

/*
 * =====================================================================
 * CAPA: MODELO (acceso a datos)
 * =====================================================================
 * QUÉ HACE: es el único lugar que habla con la tabla SOLICITUD_SERVICIO,
 * donde se guardan los pedidos que mandan los clientes desde "Pedir servicio".
 * Guarda pedidos nuevos y los lee para el panel admin.
 *
 * QUÉ NO HACE: no valida el formulario (eso lo hace SolicitudController).
 * =====================================================================
 */

class SolicitudModel
{
  /**
   * Guarda la conexión a la base para usarla en todas las funciones.
   */
  public function __construct(private Database $database)
  {
  }

  /**
   * Guarda un pedido de servicio a nombre de un usuario.
   * Antes revisa que el usuario siga existiendo (la sesión puede ser vieja,
   * por ejemplo si un admin lo borró). Empresa y teléfono vacíos se guardan como NULL.
   * Devuelve true si se guardó, o false si el usuario ya no existe.
   */
  public function crear(string $idUsuario, string $tipo, string $empresa, string $telefono, string $descripcion): bool
  {
    return $this->database->runTransaction(function (PDO $pdo) use ($idUsuario, $tipo, $empresa, $telefono, $descripcion) {
      $existe = $pdo->prepare("SELECT 1 FROM USUARIO WHERE id_usuario = ?");
      $existe->execute([$idUsuario]);
      if (!$existe->fetch()) {
        return false;
      }

      $query = $pdo->prepare(
        "INSERT INTO SOLICITUD_SERVICIO (id_usuario, tipo_servicio, empresa, telefono, descripcion)
         VALUES (?, ?, ?, ?, ?)"
      );
      $query->execute([
        $idUsuario,
        $tipo,
        $empresa !== "" ? $empresa : null,
        $telefono !== "" ? $telefono : null,
        $descripcion,
      ]);

      return true;
    });
  }

  /**
   * Lista todos los pedidos con los datos de quién lo mandó, el más nuevo primero.
   */
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

  /**
   * Busca un pedido por su número, con los datos del cliente.
   * Devuelve la fila o null si no existe.
   */
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

  /**
   * Cuenta cuántos pedidos hay. Lo usa el resumen del panel admin.
   */
  public function contar(): int
  {
    return (int) $this->database->getConnection()
      ->query("SELECT COUNT(*) FROM SOLICITUD_SERVICIO")
      ->fetchColumn();
  }
}
