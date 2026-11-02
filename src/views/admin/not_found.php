<?php
/*
 * CAPA: VISTA (lo que ve el usuario)
 * QUÉ MUESTRA: el aviso "Formulario no encontrado" (404).
 * Solo muestra datos que le pasa el controlador; no consulta la base.
 */
?>
<main class="container py-5 text-center">
  <h1 class="display-6">Formulario no encontrado</h1>
  <p class="text-muted">El formulario que buscás no existe.</p>
  <a href="/admin/formularios" class="btn btn-primary">Volver al listado</a>
</main>
