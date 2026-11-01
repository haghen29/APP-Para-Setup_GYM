<main class="container py-4">
  <h1 class="h3 mb-3">Panel de administración</h1>
  <?php $seccion = "formularios"; require __DIR__ . "/_menu.php"; ?>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>N°</th><th>Fecha</th><th>Cliente</th><th>Email</th><th>Servicio</th><th>Empresa</th><th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($formularios)): ?>
          <tr><td colspan="7" class="text-center text-muted">Todavía no hay formularios enviados.</td></tr>
        <?php endif; ?>

        <?php foreach ($formularios as $f): ?>
          <tr>
            <td><?= html((string) $f["id_solicitud"]) ?></td>
            <td><?= html($f["fecha_solicitud"]) ?></td>
            <td><?= html($f["nombre"] . " " . $f["apellido"]) ?></td>
            <td><?= html($f["email"]) ?></td>
            <td><?= html(TIPOS_SERVICIO[$f["tipo_servicio"]] ?? $f["tipo_servicio"]) ?></td>
            <td><?= html($f["empresa"]) ?></td>
            <td class="text-end">
              <a href="/admin/formularios/<?= html((string) $f["id_solicitud"]) ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
