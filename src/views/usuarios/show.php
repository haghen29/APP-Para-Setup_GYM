<main class="container py-4" style="max-width: 600px;">
  <h1 class="h3 mb-3"><?= html($usuario["nombre"] . " " . $usuario["apellido"]) ?></h1>

  <dl class="row">
    <dt class="col-sm-4">ID</dt>
    <dd class="col-sm-8"><?= html((string) $usuario["id_usuario"]) ?></dd>
    <dt class="col-sm-4">Email</dt>
    <dd class="col-sm-8"><?= html($usuario["email"]) ?></dd>
    <dt class="col-sm-4">Rol</dt>
    <dd class="col-sm-8"><?= html($usuario["rol"]) ?></dd>
    <dt class="col-sm-4">Fecha de registro</dt>
    <dd class="col-sm-8"><?= html($usuario["fecha_registro"]) ?></dd>
  </dl>

  <a href="/usuarios/update/<?= html((string) $usuario["id_usuario"]) ?>" class="btn btn-primary">Editar</a>
  <a href="/usuarios" class="btn btn-link">Volver al listado</a>
</main>