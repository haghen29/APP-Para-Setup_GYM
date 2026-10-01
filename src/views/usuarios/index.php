<main class="container py-4">
  <div class="d-flex justify-content-between align-items-center mb-3">
    <h1 class="h3 mb-0">Usuarios</h1>
    <div>
      <a href="/usuarios/create" class="btn btn-primary">Nuevo usuario</a>
      <a href="/auth/logout" class="btn btn-outline-secondary">Cerrar sesión</a>
    </div>
  </div>

  <table class="table table-striped align-middle">
    <thead>
      <tr>
        <th>ID</th><th>Nombre</th><th>Apellido</th><th>Email</th><th>Rol</th><th>Registro</th><th class="text-end">Acciones</th>
      </tr>
    </thead>
    <tbody>
      <?php if (empty($usuarios)): ?>
        <tr><td colspan="7" class="text-center text-muted">No hay usuarios cargados.</td></tr>
      <?php endif; ?>

      <?php foreach ($usuarios as $usuario): ?>
        <tr>
          <td><?= html((string) $usuario["id_usuario"]) ?></td>
          <td><?= html($usuario["nombre"]) ?></td>
          <td><?= html($usuario["apellido"]) ?></td>
          <td><?= html($usuario["email"]) ?></td>
          <td><?= html($usuario["rol"]) ?></td>
          <td><?= html($usuario["fecha_registro"]) ?></td>
          <td class="text-end">
            <a href="/usuarios/<?= html((string) $usuario["id_usuario"]) ?>" class="btn btn-sm btn-outline-secondary">Ver</a>
            <a href="/usuarios/update/<?= html((string) $usuario["id_usuario"]) ?>" class="btn btn-sm btn-outline-primary">Editar</a>
            <form action="/usuarios/<?= html((string) $usuario["id_usuario"]) ?>" method="POST" class="d-inline"
                  onsubmit="return confirm('¿Eliminar este usuario?');">
              <input type="hidden" name="_METHOD" value="DELETE">
              <button type="submit" class="btn btn-sm btn-outline-danger">Eliminar</button>
            </form>
          </td>
        </tr>
      <?php endforeach; ?>
    </tbody>
  </table>
</main>