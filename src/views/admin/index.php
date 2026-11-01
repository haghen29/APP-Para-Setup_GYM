<main class="container py-4">
  <h1 class="h3 mb-3">Panel de administración</h1>
  <?php $seccion = "resumen"; require __DIR__ . "/_menu.php"; ?>

  <div class="row g-3">
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <h2 class="h5">Formularios</h2>
          <p class="display-6 mb-1"><?= html((string) $totales["formularios"]) ?></p>
          <p class="text-muted">Pedidos de servicio enviados por los clientes.</p>
          <a href="/admin/formularios" class="btn btn-primary">Ver formularios</a>
        </div>
      </div>
    </div>
    <div class="col-md-6">
      <div class="card h-100">
        <div class="card-body">
          <h2 class="h5">Usuarios registrados</h2>
          <p class="display-6 mb-1"><?= html((string) $totales["usuarios"]) ?></p>
          <p class="text-muted">Cuentas creadas en el sitio.</p>
          <a href="/admin/usuarios" class="btn btn-primary">Ver usuarios</a>
        </div>
      </div>
    </div>
  </div>
</main>
