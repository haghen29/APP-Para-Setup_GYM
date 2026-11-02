<?php

/*
 * =====================================================================
 * CAPA: MODELO (acceso a datos)
 * =====================================================================
 * QUÉ HACE: es el único lugar que habla con las tablas USUARIO y CLIENTE.
 * Tiene una función por cada cosa que la app necesita hacer con los usuarios:
 * buscar, listar, contar, crear, actualizar y borrar.
 *
 * QUÉ NO HACE: no valida formularios ni arma pantallas. Recibe datos ya
 * revisados por el controlador y devuelve filas de la base.
 *
 * Todas las consultas son "preparadas" (prepare + ?), así lo que escribe el
 * usuario nunca se mezcla con el SQL (protege contra inyección SQL).
 * =====================================================================
 */

class UsuarioModel
{
  // Código de error de MySQL cuando se repite un valor UNIQUE (el email)
  private const ERROR_DUPLICADO = 1062;

  /**
   * Guarda la conexión a la base para usarla en todas las funciones.
   */
  public function __construct(private Database $database)
  {
  }

  /**
   * Busca un usuario por email. Lo usa el login.
   * Devuelve la fila (id, nombre, contraseña encriptada y rol) o null si no existe.
   */
  public function buscarPorEmail(string $email): ?array
  {
    $query = $this->database->getConnection()->prepare(
      "SELECT id_usuario, nombre, contrasena, rol FROM USUARIO WHERE email = ?"
    );
    $query->execute([$email]);

    return $query->fetch() ?: null;
  }

  /**
   * Busca un usuario por su id. Lo usan "ver" y "editar" del panel admin.
   * Devuelve la fila completa o null si no existe.
   */
  public function buscarPorId(string $id): ?array
  {
    $query = $this->database->getConnection()->prepare(
      "SELECT * FROM USUARIO WHERE id_usuario = ?"
    );
    $query->execute([$id]);

    return $query->fetch() ?: null;
  }

  /**
   * Lista todos los usuarios con la cantidad de formularios que mandó cada uno.
   * No trae la contraseña: el listado no la necesita.
   */
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

  /**
   * Cuenta cuántos usuarios hay. Lo usa el resumen del panel admin.
   */
  public function contar(): int
  {
    return (int) $this->database->getConnection()
      ->query("SELECT COUNT(*) FROM USUARIO")
      ->fetchColumn();
  }

  /**
   * Crea un usuario nuevo con la contraseña encriptada (password_hash).
   * Devuelve true si se guardó, o false si ya existía alguien con ese email.
   */
  public function crear(string $nombre, string $apellido, string $email, string $contrasena, string $rol): bool
  {
    try {
      $this->database->runTransaction(function (PDO $pdo) use ($nombre, $apellido, $email, $contrasena, $rol) {
        $query = $pdo->prepare(
          "INSERT INTO USUARIO (nombre, apellido, email, contrasena, rol, fecha_registro)
           VALUES (?, ?, ?, ?, ?, CURDATE())"
        );
        $query->execute([
          $nombre,
          $apellido,
          $email,
          password_hash($contrasena, PASSWORD_DEFAULT),
          $rol,
        ]);
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
   * Actualiza los datos de un usuario.
   * Si $contrasena viene vacía, la contraseña NO se cambia.
   * Devuelve true si se guardó, o false si el email ya lo usa otro usuario.
   */
  public function actualizar(string $id, string $nombre, string $apellido, string $email, string $contrasena, string $rol): bool
  {
    try {
      $this->database->runTransaction(function (PDO $pdo) use ($id, $nombre, $apellido, $email, $contrasena, $rol) {
        if ($contrasena !== "") {
          $query = $pdo->prepare(
            "UPDATE USUARIO
             SET nombre = ?, apellido = ?, email = ?, contrasena = ?, rol = ?
             WHERE id_usuario = ?"
          );
          $query->execute([$nombre, $apellido, $email, password_hash($contrasena, PASSWORD_DEFAULT), $rol, $id]);
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
   * Borra un usuario. Primero borra su fila de CLIENTE (que depende de USUARIO)
   * y después el usuario. Sus formularios se borran solos (ON DELETE CASCADE).
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
