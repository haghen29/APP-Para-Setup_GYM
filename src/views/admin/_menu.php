<?php
// Menú del panel de administración. $seccion marca la pestaña activa.
$seccion = $seccion ?? "";
?>
<ul class="nav nav-pills mb-4">
  <li class="nav-item"><a class="nav-link <?= $seccion === "resumen" ? "active" : "" ?>" href="/admin">Resumen</a></li>
  <li class="nav-item"><a class="nav-link <?= $seccion === "formularios" ? "active" : "" ?>" href="/admin/formularios">Formularios</a></li>
  <li class="nav-item"><a class="nav-link <?= $seccion === "usuarios" ? "active" : "" ?>" href="/admin/usuarios">Usuarios registrados</a></li>
</ul>
