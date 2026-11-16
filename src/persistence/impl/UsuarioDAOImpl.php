<?php

/*
 * =====================================================================
 * CAPA: PERSISTENCE (implementación)
 * =====================================================================
 * QUÉ HACE: es el único lugar que habla con las tablas USUARIO y CLIENTE.
 * Una función por cada consulta: buscar, listar, contar, crear,
 * actualizar y borrar.
 *
 * QUÉ NO HACE: no decide reglas de negocio (eso es UsuarioService).
 * Por ejemplo, no encripta contraseñas: las recibe ya encriptadas.
 *
 * Todas las consultas son "preparadas" (prepare + ?), así lo que escribe el
 * usuario nunca se mezcla con el SQL (protege contra inyección SQL).
 * =====================================================================
 */

class UsuarioDAOImpl implements UsuarioDAO
{
  // Código de error de MySQL cuando se repite un valor UNIQUE (el email)
  private const ERROR_DUPLICADO = 1062;

  public function __construct(private Database $database)
  {
  }

  public function buscarPorEmail(string $email): ?array
  {
    $query = $this->database->getConnection()->prepare(
      "SELECT id_usuario, nombre, contrasena, rol FROM USUARIO WHERE email = ?"
    );
    $query->execute([$email]);

    return $query->fetch() ?: null;
  }

  public function buscarPorId(string $id): ?array
  {
    $query = $this->database->getConnection()->prepare(
      "SELECT * FROM USUARIO WHERE id_usuario = ?"
    );
    $query->execute([$id]);

    return $query->fetch() ?: null;
  }

  public function listarConCantidadDeFormularios(): array
  {
    $query = $this->database->getConnection()->query(
      "SELECT u.id_usuario, u.nombre, u.apellido, u.email, u.rol, u.fecha_registro,
              (SELECT COUNT(*) FROM SOLICITUD_SERVICIO s WHERE s.id_usuario = u.id_usuario) AS formularios
       FROM USUARIO u
       ORDER BY u.id_usuario"
    );

    return $query->fetchAll();
  }

  public function contar(): int
  {
    return (int) $this->database->getConnection()
      ->query("SELECT COUNT(*) FROM USUARIO")
      ->fetchColumn();
  }

  public function existe(string $id): bool
  {
    $query = $this->database->getConnection()->prepare(
      "SELECT 1 FROM USUARIO WHERE id_usuario = ?"
    );
    $query->execute([$id]);

    return (bool) $query->fetch();
  }

  public function crear(string $nombre, string $apellido, string $email, string $contrasenaHash, string $rol): bool
  {
    try {
      $this->database->runTransaction(function (PDO $pdo) use ($nombre, $apellido, $email, $contrasenaHash, $rol) {
        $query = $pdo->prepare(
          "INSERT INTO USUARIO (nombre, apellido, email, contrasena, rol, fecha_registro)
           VALUES (?, ?, ?, ?, ?, CURDATE())"
        );
        $query->execute([$nombre, $apellido, $email, $contrasenaHash, $rol]);
      });
    } catch (PDOException $e) {
      if ($this->esEmailRepetido($e)) {
        return false;
      }
      throw $e;
    }

    return true;
  }

  public function actualizar(string $id, string $nombre, string $apellido, string $email, ?string $contrasenaHash, string $rol): bool
  {
    try {
      $this->database->runTransaction(function (PDO $pdo) use ($id, $nombre, $apellido, $email, $contrasenaHash, $rol) {
        if ($contrasenaHash !== null) {
          $query = $pdo->prepare(
            "UPDATE USUARIO
             SET nombre = ?, apellido = ?, email = ?, contrasena = ?, rol = ?
             WHERE id_usuario = ?"
          );
          $query->execute([$nombre, $apellido, $email, $contrasenaHash, $rol, $id]);
        } else {
          $query = $pdo->prepare(
            "UPDATE USUARIO
             SET nombre = ?, apellido = ?, email = ?, rol = ?
             WHERE id_usuario = ?"
          );
          $query->execute([$nombre, $apellido, $email, $rol, $id]);
        }
      });
    } catch (PDOException $e) {
      if ($this->esEmailRepetido($e)) {
        return false;
      }
      throw $e;
    }

    return true;
  }

  /**
   * Primero borra su fila de CLIENTE (que depende de USUARIO) y después el usuario.
   * Sus formularios se borran solos (ON DELETE CASCADE).
   * Todo va en una transacción: o se borra todo, o nada.
   */
  public function eliminar(string $id): void
  {
    $this->database->runTransaction(function (PDO $pdo) use ($id) {
      $query = $pdo->prepare("DELETE FROM CLIENTE WHERE id_usuario = ?");
      $query->execute([$id]);

      $query = $pdo->prepare("DELETE FROM USUARIO WHERE id_usuario = ?");
      $query->execute([$id]);
    });
  }

  /**
   * Dice si un error de la base fue por un email repetido (error 1062 de MySQL).
   */
  private function esEmailRepetido(PDOException $e): bool
  {
    return ($e->errorInfo[1] ?? null) === self::ERROR_DUPLICADO;
  }
}
