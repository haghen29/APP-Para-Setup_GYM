<?php
/*
 * CAPA: VISTA (lo que ve el usuario)
 * QUÉ MUESTRA: la pantalla "Acceso denegado" para quien no es admin.
 * Solo muestra datos que le pasa el controlador; no consulta la base.
 */
?>
<main class="container py-5 text-center">
  <h1 class="display-6">Acceso denegado</h1>
  <p class="text-muted">Esta sección es solo para administradores.</p>
  <a href="/index" class="btn btn-primary">Volver al inicio</a>
</main>
