<?php
/*
 * CAPA: VISTA (lo que ve el usuario)
 * QUÉ MUESTRA: el detalle de un pedido de servicio.
 * Solo muestra datos que le pasa el controlador; no consulta la base.
 */
?>
<main class="container py-4" style="max-width: 800px;">
  <h1 class="h3 mb-3">Formulario #<?= html((string) $formulario["id_solicitud"]) ?></h1>

  <dl class="row">
    <dt class="col-sm-4">Fecha</dt>
    <dd class="col-sm-8"><?= html($formulario["fecha_solicitud"]) ?></dd>
    <dt class="col-sm-4">Cliente</dt>
    <dd class="col-sm-8"><?= html($formulario["nombre"] . " " . $formulario["apellido"]) ?></dd>
    <dt class="col-sm-4">Email</dt>
    <dd class="col-sm-8"><?= html($formulario["email"]) ?></dd>
    <dt class="col-sm-4">Teléfono</dt>
    <dd class="col-sm-8"><?= html($formulario["telefono"]) ?></dd>
    <dt class="col-sm-4">Empresa</dt>
    <dd class="col-sm-8"><?= html($formulario["empresa"]) ?></dd>
    <dt class="col-sm-4">Servicio</dt>
    <dd class="col-sm-8"><?= html(TIPOS_SERVICIO[$formulario["tipo_servicio"]] ?? $formulario["tipo_servicio"]) ?></dd>
    <dt class="col-sm-4">Descripción</dt>
    <dd class="col-sm-8" style="white-space: pre-wrap;"><?= html($formulario["descripcion"]) ?></dd>
  </dl>

  <a href="/admin/formularios" class="btn btn-link">Volver al listado</a>
</main>
