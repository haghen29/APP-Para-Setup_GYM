<?php
/*
 * CAPA: VISTA (lo que ve el usuario)
 * QUÉ MUESTRA: la barra de arriba. Muestra "Iniciar sesión" o el nombre del usuario, "Cerrar sesión" y, si es admin, "Panel admin".
 * Solo muestra datos que le pasa el controlador; no consulta la base.
 */
?>
<?php
// Barra de navegación compartida. Usa los datos de la sesión para mostrar
// "Iniciar sesión" (visitante) o el nombre del usuario + "Cerrar sesión".
$logueado = !empty($_SESSION["user_id"]);
$esAdmin = ($_SESSION["user_rol"] ?? "") === "admin";
?>
<nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
  <div class="container-fluid">
    <a class="navbar-brand" href="/index">
      <img src="/img/ots.png" alt="CodeGYM Net" width="80" height="80">
    </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <div class="navbar-nav">
        <a class="nav-link active" href="/index#seccion1">Servicio</a>
        <a class="nav-link active" href="/index#seccion2">Nosotros</a>
        <a class="nav-link active" href="/index#seccion3">Clientes</a>
      </div>
      <div class="navbar-nav ms-auto align-items-lg-center gap-2">
        <?php if ($logueado): ?>
          <span class="navbar-text">Hola, <?= html($_SESSION["user_nombre"] ?? "") ?></span>
          <?php if ($esAdmin): ?>
            <a class="btn btn-outline-light btn-sm" href="/admin">Panel admin</a>
          <?php endif; ?>
          <a class="btn btn-secondary btn-sm" href="/auth/logout">Cerrar sesión</a>
        <?php else: ?>
          <a class="btn btn-outline-light btn-sm" href="/auth/login">Iniciar sesión</a>
        <?php endif; ?>
      </div>
    </div>
  </div>
</nav>
