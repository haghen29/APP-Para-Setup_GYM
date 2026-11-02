<?php
/*
 * CAPA: VISTA (lo que ve el usuario)
 * QUÉ MUESTRA: el formulario "Pedir servicio" para clientes con sesión: tipo de servicio, empresa, teléfono y descripción.
 * Solo muestra datos que le pasa el controlador; no consulta la base.
 */
?>
<?php
$old = $old ?? [];
$enviado = $enviado ?? false;
?>
<main class="container py-4" style="max-width: 700px;">
  <h1 class="h3 mb-1">Pedir servicio</h1>
  <p class="text-muted">
    Contanos qué necesitás y el equipo de CodeGYM Net te contacta con un presupuesto.
    Lo enviás como <strong><?= html(($_SESSION["user_nombre"] ?? "")) ?></strong>.
  </p>

  <?php if ($enviado): ?>
    <div class="alert alert-success">
      ¡Recibimos tu pedido! Te vamos a contactar por email para armar el presupuesto.
    </div>
  <?php endif; ?>

  <?php if (!empty($error)): ?>
    <div class="alert alert-danger"><?= html($error) ?></div>
  <?php endif; ?>

  <form action="/pedir-servicio" method="POST">
    <div class="mb-3">
      <label for="tipo_servicio" class="form-label">Tipo de servicio</label>
      <select class="form-select" id="tipo_servicio" name="tipo_servicio" required>
        <option value="">Elegí una opción</option>
        <?php foreach ($tipos as $valor => $etiqueta): ?>
          <option value="<?= html($valor) ?>" <?= ($old["tipo_servicio"] ?? "") === $valor ? "selected" : "" ?>><?= html($etiqueta) ?></option>
        <?php endforeach; ?>
      </select>
    </div>
    <div class="mb-3">
      <label for="empresa" class="form-label">Empresa o emprendimiento <span class="text-muted">(opcional)</span></label>
      <input type="text" class="form-control" id="empresa" name="empresa" maxlength="200" value="<?= html($old["empresa"] ?? "") ?>">
    </div>
    <div class="mb-3">
      <label for="telefono" class="form-label">Teléfono / WhatsApp <span class="text-muted">(opcional)</span></label>
      <input type="tel" class="form-control" id="telefono" name="telefono" maxlength="30" value="<?= html($old["telefono"] ?? "") ?>">
    </div>
    <div class="mb-3">
      <label for="descripcion" class="form-label">¿Qué necesitás?</label>
      <textarea class="form-control" id="descripcion" name="descripcion" rows="6" minlength="10" maxlength="2000" required><?= html($old["descripcion"] ?? "") ?></textarea>
    </div>
    <button type="submit" class="btn btn-primary">Enviar pedido</button>
    <a href="/index" class="btn btn-link">Volver al inicio</a>
  </form>
</main>
