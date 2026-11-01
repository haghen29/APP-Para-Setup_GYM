<div class="container">
    <div class="d-flex justify-content-center h-100">
        <div class="card">
            <div class="card-header">
                <h3>Registro</h3>
            </div>

            <div class="card-body">
                                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2"><?= html($error) ?></div>
                <?php endif; ?>
                <form action="/auth/register" method="POST">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="nombre" class="form-control" placeholder="Nombre" value="<?= html($old["nombre"] ?? "") ?>" required>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-user"></i></span>
                        <input type="text" name="apellido" class="form-control" placeholder="Apellido" value="<?= html($old["apellido"] ?? "") ?>" required>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="Correo electrónico" value="<?= html($old["email"] ?? "") ?>" required>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" name="contrasena" class="form-control" placeholder="Contraseña (mín. 6 caracteres)" required minlength="6">
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" name="confirmar" class="form-control" placeholder="Repetir contraseña" required>
                    </div>
                    <div class="mb-3">
                        <input type="submit" value="Crear" class="btn float-end login_btn">
                    </div>
                </form>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-center links">
                    ¿Ya tienes una cuenta?
                    <a href="/auth/login">Iniciar sesión</a>
                </div>
            </div>
        </div>
    </div>
</div>
