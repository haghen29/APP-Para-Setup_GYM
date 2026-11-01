<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Panel de administración</h1>
    <a href="/admin/usuarios/create" class="btn btn-primary">Nuevo usuario</a>
  </div>
  <?php $seccion = "usuarios"; require __DIR__ . "/_menu.php"; ?>

  <div class="table-responsive">
    <table class="table table-striped align-middle">
      <thead>
        <tr>
          <th>ID</th><th>Nombre</th><th>Apellido</th><th>Email</th><th>Rol</th><th>Registro</th><th>Formularios</th><th class="text-end">Acciones</th>
        </tr>
      </thead>
      <tbody>
        <?php if (empty($usuarios)): ?>
          <tr><td colspan="8" class="text-center text-muted">No hay usuarios registrados.</td></tr>
        <?php endif; ?>

        <?php foreach ($usuarios as $u): ?>
          <?php $id = html((string) $u["id_usuario"]); ?>
          <tr>
            <td><?= $id ?></td>
            <td><?= html($u["nombre"]) ?></td>
            <td><?= html($u["apellido"]) ?></td>
            <td><?= html($u["email"]) ?></td>
            <td><span class="badge text-bg-<?= $u["rol"] === "admin" ? "danger" : ($u["rol"] === "empleado" ? "info" : "secondary") ?>"><?= html($u["rol"]) ?></span></td>
            <td><?= html($u["fecha_registro"]) ?></td>
            <td><?= html((string) $u["formularios"]) ?></td>
            <td class="text-end text-nowrap">
              <a href="/admin/usuarios/<?= $id ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
              <a href="/admin/usuarios/update/<?= $id ?>" class="btn btn-sm btn-outline-primary">Editar</a>
              <?php if ((string) $u["id_usuario"] !== (string) ($_SESSION["user_id"] ?? "")): ?>
                <form action="/admin/usuarios/<?= $id ?>" method="POST" class="d-inline"
                      onsubmit="return confirm('¿Eliminar este usuario? También se borran sus formularios.');">
                  <input type="hidden" name="_METHOD" value="DELETE">
                  <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
                </form>
              <?php endif; ?>
            </td>
          </tr>
        <?php endforeach; ?>
      </tbody>
    </table>
  </div>
</main>
