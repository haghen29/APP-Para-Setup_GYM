<main>
    <h1>✅ 200 OK</h1>
    <p>Si ves esto, el servidor de Slim y el motor de plantillas estan funcionando correctamente.</p>
</main>

<main>
    <h1>Listado de Entidad</h1>

    <?php
    if (empty($productos)) {
        echo "<p>No hay productos para mostrar.</p>";
    } else {
        echo "<ul>";
        foreach ($productos as $producto) {
            $id = ($producto['id']);
            $name = ($producto['name']);
            $price = ($producto['price']);

            echo "<li>
                    <b>$name</b> — \$$price
                  </li>";
        }
        echo "</ul>";
    }
    ?>
</main>
