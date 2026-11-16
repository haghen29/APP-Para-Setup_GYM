<?php

/*
 * =====================================================================
 * CAPA: CONTROLLERS (interfaz)
 * =====================================================================
 * QUÉ ES: el "contrato" del CRUD de usuarios del panel admin.
 * La implementación está en impl/UsuarioControllerImpl.php.
 * =====================================================================
 */

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

interface UsuarioController
{
  /** GET /admin/usuarios -> tabla de usuarios. */
  public function listar(Request $request, Response $response): Response;

  /** GET /admin/usuarios/{id} -> datos de un usuario (404 si no existe). */
  public function ver(Request $request, Response $response, array $args): Response;

  /** GET /admin/usuarios/create -> formulario para crear. */
  public function mostrarCrear(Request $request, Response $response): Response;

  /** POST /admin/usuarios -> valida y crea el usuario. */
  public function crear(Request $request, Response $response): Response;

  /** GET /admin/usuarios/update/{id} -> formulario de edición (404 si no existe). */
  public function mostrarEditar(Request $request, Response $response, array $args): Response;

  /** PUT /admin/usuarios/{id} -> valida y guarda los cambios. */
  public function actualizar(Request $request, Response $response, array $args): Response;

  /** DELETE /admin/usuarios/{id} -> borra el usuario. */
  public function eliminar(Request $request, Response $response, array $args): Response;
}
