<?php

/*
 * =====================================================================
 * CAPA: UTILIDADES (ayudantes que usan las vistas)
 * =====================================================================
 * QUÉ HACE: html() convierte caracteres peligrosos (como < y >) en texto común antes
 * de mostrarlos. Así, si alguien escribe código en un formulario, se ve como
 * texto y no se ejecuta (protege contra XSS).
 * =====================================================================
 */

function html(?string $text = null): string
{
  return htmlspecialchars($text ?? "", ENT_QUOTES | ENT_SUBSTITUTE, "UTF-8");
}
