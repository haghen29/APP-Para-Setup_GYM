#  Setup_Gym

**Descripción:**
Aplicación web orientada a gimnasios que permite gestionar la compra de maquinaria, controlar el stock de productos para la venta y optimizar la administración del negocio de forma eficiente.

Integrantes:

- Borio Perez Santino | [@haghen29](https://github.com/haghen29)
- Burgos Leandro | [@burgosleandro](https://github.com/burgosleandro)
- Estigarribia Adrian | [@adrianestigarribia](https://github.com/adrianestigarribia)
- Ibarra Román | [@username](https://github.com/username)

Proyecto institucional **E.E.S.T Nº4 de Berazategui**.

## Elevator's Pitch

- Para dueños y administradores de gimnasios
- Quienes necesitan mejorar la gestión de compras, stock y crecimiento del negocio
- El Setup_Gym es una aplicación web de gestión empresarial.
- Que te permite organizar compras de maquinaria, controlar productos y facilitar el negocio.
- Diferente a páginas o aplicaciones genéricas que no brindan un beneficio a tanto detalle.
- Nuestro proyecto ofrece una solución específica para gimnasios, fácil de usar y adaptada a necesidades reales

## Requisitos

- PHP 8.1 o superior.
- Extensión PDO habilitada.
- MySQL o PostgreSQL según la configuración en el archivo `.env`.

## Arquitectura en capas

```
public/index.php -> src/bootstrap.php (arma la app)
  src/routes/web.php       Rutas: qué dirección va a qué controlador
  src/middlewares/         Porteros: sesión, admin y log
  src/controllers/         Controladores: validan y deciden
  src/models/              Modelos: todas las consultas SQL
  src/views/               Vistas: el HTML que ve el usuario
```

## Migraciones

Colocá tus archivos `.sql` en `src/database/migrations/` y ejecuta:

```bash
composer migrate
```

## Primer administrador

El registro público siempre crea usuarios con rol `cliente`. Para tener un admin (acceso a `/admin`), registrate desde `/auth/register` y después, en MySQL:

```sql
UPDATE USUARIO SET rol = 'admin' WHERE email = 'tu@email.com';
```

Cerrá sesión y volvé a entrar para que el cambio de rol tome efecto.

## Licencia

Este proyecto está licenciado bajo la [Licencia MIT](./LICENSE).

---

Generado por la [plantilla](https://github.com/PDI-EEST4/slim-template-2026), a discreción de los integrantes del proyecto.
