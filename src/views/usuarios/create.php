<?php $old = $old ?? []; ?>
<main class="container py-4" style="max-width: 600px;">
  <h1 class="h3 mb-3">Crear usuario</h1>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= html($error) ?></div>
  <?php endif; ?>

  <form action="/usuarios" method="POST">
    <div class="mb-3">
      <label for="nombre" class="form-label">Nombre</label>
      <input type="text" class="form-control" id="nombre" name="nombre" value="<?= html($old["nombre"] ?? "") ?>" required>
    </div>
    <div class="mb-3">
      <label for="apellido" class="form-label">Apellido</label>
      <input type="text" class="form-control" id="apellido" name="apellido" value="<?= html($old["apellido"] ?? "") ?>" required>
    </div>
    <div class="mb-3">
      <label for="email" class="form-label">Email</label>
      <input type="email" class="form-control" id="email" name="email" value="<?= html($old["email"] ?? "") ?>" required>
    </div>
    <div class="mb-3">
      <label for="contrasena" class="form-label">Contraseña</label>
      <input type="password" class="form-control" id="contrasena" name="contrasena" required>
    </div>
    <div class="mb-3">
      <label for="rol" class="form-label">Rol</label>
      <select class="form-select" id="rol" name="rol">
        <?php foreach ($roles as $rol): ?>
          <option value="<?= html($rol) ?>" <?= ($old["rol"] ?? "cliente") === $rol ? "selected" : "" ?>><?= html(ucfirst($rol)) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <button type="submit" class="btn btn-primary">Guardar</button>
    <a href="/usuarios" class="btn btn-link">Cancelar</a>
  </form>
</main>