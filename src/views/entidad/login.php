<div class="container">
	<div class="d-flex justify-content-center h-100">
		<div class="card">
			<div class="card-header">
				<h3>Inicio de sesión</h3>
			</div>
			<div class="card-body">
				                <?php if (!empty($error)): ?>
                    <div class="alert alert-danger py-2"><?= html($error) ?></div>
                <?php endif; ?>
                <?php if (!empty($registrado)): ?>
                    <div class="alert alert-success py-2">Cuenta creada. Ya podés iniciar sesión.</div>
                <?php endif; ?>
                <?php if (!empty($aviso) && empty($error)): ?>
                    <div class="alert alert-info py-2">Iniciá sesión para continuar.</div>
                <?php endif; ?>
                <form action="/auth/login" method="POST">
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-envelope"></i></span>
                        <input type="email" name="email" class="form-control" placeholder="Correo electrónico" value="<?= html($old["email"] ?? "") ?>" required>
                    </div>
                    <div class="input-group mb-3">
                        <span class="input-group-text"><i class="fas fa-key"></i></span>
                        <input type="password" name="contrasena" class="form-control" placeholder="Contraseña" required>
                    </div>
                    <div class="mb-3">
                        <input type="submit" value="Acceder" class="btn float-end login_btn">
                    </div>
                </form>
			</div>
			<div class="card-footer">
				<div class="d-flex justify-content-center links">
					¿Aun no tienes una cuenta?<a href="/auth/register">Crear cuenta</a>
				</div>
			</div>
		</div>
	</div>
</div>
