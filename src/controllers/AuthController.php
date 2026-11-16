<?php

/*
 * =====================================================================
 * CAPA: CONTROLLERS (interfaz)
 * =====================================================================
 * QUÉ ES: el "contrato" de registro, login y logout.
 * La implementación está en impl/AuthControllerImpl.php.
 * =====================================================================
 */

use Psr\Http\Message\ServerRequestInterface as Request;
use Psr\Http\Message\ResponseInterface as Response;

interface AuthController
{
  /** GET /auth/register -> formulario de registro. */
  public function mostrarRegistro(Request $request, Response $response): Response;

  /** POST /auth/register -> valida y crea la cuenta. */
  public function registrar(Request $request, Response $response): Response;

  /** GET /auth/login -> formulario de login (o redirige si ya hay sesión). */
  public function mostrarLogin(Request $request, Response $response): Response;

  /** POST /auth/login -> comprueba los datos e inicia la sesión. */
  public function iniciarSesion(Request $request, Response $response): Response;

  /** GET /auth/logout -> cierra la sesión. */
  public function cerrarSesion(Request $request, Response $response): Response;
}
