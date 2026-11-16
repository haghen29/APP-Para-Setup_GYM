<?php

/*
 * =====================================================================
 * CAPA: ROUTES (interfaz)
 * =====================================================================
 * QUÉ ES: el "contrato" de la capa de rutas: registrar todas las
 * direcciones del sitio en la app de Slim.
 * La implementación está en impl/WebRoutesImpl.php.
 * =====================================================================
 */

use Slim\App;

interface WebRoutes
{
  /** Registra todas las rutas (y sus middlewares) en la app. */
  public function registrar(App $app): void;
}
