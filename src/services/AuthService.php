<?php

/*
 * =====================================================================
 * CAPA: SERVICES (interfaz)
 * =====================================================================
 * QUÉ ES: el "contrato" de las reglas de negocio de las cuentas:
 * registrarse, comprobar email y contraseña, y a dónde va cada rol.
 * La implementación está en impl/AuthServiceImpl.php.
 * No conoce rutas, Request/Response ni SQL.
 * =====================================================================
 */

interface AuthService
{
  /**
   * Crea una cuenta pública.
   * REGLA: el rol es SIEMPRE "cliente" (nadie puede registrarse como admin).
   * REGLA: la contraseña se guarda encriptada, nunca en texto plano.
   * Devuelve false si el email ya existe.
   */
  public function registrar(string $nombre, string $apellido, string $email, string $contrasena): bool;

  /**
   * Comprueba email y contraseña.
   * REGLA: si el email no existe o la contraseña no coincide, el resultado es el
   * mismo (null), así no se da pista de cuál de los dos falló.
   * Devuelve ["id_usuario", "nombre", "rol"] o null.
   */
  public function autenticar(string $email, string $contrasena): ?array;

  /**
   * REGLA: el admin entra al panel (/admin); cualquier otro rol, a la landing (/index).
   */
  public function rutaInicio(?string $rol): string;
}
