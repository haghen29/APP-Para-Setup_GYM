<main class="container py-4" style="max-width: 600px;">
  <h1 class="h3 mb-3">Editar usuario #<?= html((string) $usuario["id_usuario"]) ?></h1>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= html($error) ?></div>
  <?php endif; ?>

  <form action="/usuarios/<?= html((string) $usuario["id_usuario"]) ?>" method="POST">
    <input type="hidden" name="_METHOD" value="PUT">

    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre</label>
      <input type="text" class="form-control" id="nombre" name="nombre" value="<?= html($usuario["nombre"]) ?>" required>
    </div>
    <div class="mb-3">
      <label for="apellido" class="form-label">Apellido</label>
      <input type="text" class="form-control" id="apellido" name="apellido" value="<?= html($usuario["apellido"]) ?>" required>
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" class="form-control" id="email" name="email" value="<?= html($usuario["email"]) ?>" required>
    </div>
    <div class="mb-3">
      <label for="contrasena" class="form-label">Nueva contraseña</label>
      <input type="password" class="form-control" id="contrasena" name="contrasena" placeholder="Dejar vacío para no cambiarla">
    </div>
    <div class="mb-3">
      <label for="rol" class="form-label">Rol</label>
      <select class="form-select" id="rol" name="rol">
        <?php foreach ($roles as $rol): ?>
          <option value="<?= html($rol) ?>" <?= $usuario["rol"] === $rol ? "selected" : "" ?>><?= html(ucfirst($rol)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Actualizar</button>
    <a href="/usuarios" class="btn btn-link">Cancelar</a>
  </form>
</main>