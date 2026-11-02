<?php

/*
 * =====================================================================
 * CAPA: RUTAS (la entrada de la aplicación)
 * =====================================================================
 * QUÉ HACE: es la "lista de direcciones" del sitio. Para cada dirección
 * (por ejemplo GET /auth/login) dice qué método de qué controlador la atiende
 * y qué middlewares (porteros) tienen que pasar antes.
 *
 * QUÉ NO HACE: no valida datos, no consulta la base y no arma HTML.
 * Eso es trabajo de los controladores, los modelos y las vistas.
 *
 * Grupos de rutas:
 *   - Públicas:            cualquiera puede entrar.
 *   - Con sesión:          pasan por authMiddleware.
 *   - Solo admin:          pasan por authMiddleware y después adminMiddleware.
 *
 * Nota: en Slim el ÚLTIMO ->add() es el PRIMERO que corre. Por eso se escribe
 * ->add("adminMiddleware")->add("authMiddleware"): primero se revisa la sesión
 * y después el rol.
 * =====================================================================
 */

use Slim\App;
use Slim\Routing\RouteCollectorProxy;

/**
 * Registra todas las rutas en la app.
 *
 * @param App   $app           La aplicación de Slim.
 * @param array $controladores Los controladores creados en bootstrap.php.
 */
return function (App $app, array $controladores): void {
  $inicio = $controladores["inicio"];
  $auth = $controladores["auth"];
  $solicitud = $controladores["solicitud"];
  $admin = $controladores["admin"];
  $usuario = $controladores["usuario"];

  // --- Rutas públicas ---------------------------------------------------------
  $app->get("/", [$inicio, "redirigirALanding"]);   // la raíz lleva a la landing
  $app->get("/index", [$inicio, "landing"]);         // la landing

  $app->get("/auth/register", [$auth, "mostrarRegistro"]);
  $app->post("/auth/register", [$auth, "registrar"]);
  $app->get("/auth/login", [$auth, "mostrarLogin"]);
  $app->post("/auth/login", [$auth, "iniciarSesion"]);
  $app->get("/auth/logout", [$auth, "cerrarSesion"]);

  // Ruta vieja: la gestión de usuarios ahora está en el panel admin
  $app->get("/usuarios", [$inicio, "redirigirAUsuariosAdmin"]);

  // --- Con sesión iniciada: "Pedir servicio" ----------------------------------
  $app->group("/pedir-servicio", function (RouteCollectorProxy $group) use ($solicitud) {
    $group->get("", [$solicitud, "mostrarFormulario"]);
    $group->post("", [$solicitud, "enviar"]);
  })->add("authMiddleware");

  // --- Solo admin: panel de administración -----------------------------------
  $app->group("/admin", function (RouteCollectorProxy $group) use ($admin, $usuario) {
    // Resumen y formularios recibidos
    $group->get("", [$admin, "resumen"]);
    $group->get("/formularios", [$admin, "listarFormularios"]);
    $group->get("/formularios/{id}", [$admin, "verFormulario"]);

    // CRUD de usuarios (TPN11)
    $group->get("/usuarios", [$usuario, "listar"]);
    $group->get("/usuarios/create", [$usuario, "mostrarCrear"]);
    $group->get("/usuarios/update/{id}", [$usuario, "mostrarEditar"]);
    $group->get("/usuarios/{id}", [$usuario, "ver"]);
    $group->post("/usuarios", [$usuario, "crear"]);
    $group->put("/usuarios/{id}", [$usuario, "actualizar"]);
    $group->delete("/usuarios/{id}", [$usuario, "eliminar"]);
  })->add("adminMiddleware")->add("authMiddleware");
};
