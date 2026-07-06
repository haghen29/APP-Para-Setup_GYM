<link href="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/css/bootstrap.min.css" rel="stylesheet" id="bootstrap-css">
<script src="//maxcdn.bootstrapcdn.com/bootstrap/4.1.1/js/bootstrap.min.js"></script>
<script src="//cdnjs.cloudflare.com/ajax/libs/jquery/3.2.1/jquery.min.js"></script>
<!DOCTYPE html>
<html>
<head>
	<title>Login Page</title>
	<link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.1.3/css/bootstrap.min.css" integrity="sha384-MCw98/SFnGE8fJT3GXwEOngsV7Zt27NXFoaoApmYm81iuXoPkFOJwJ8ERdknLPMO" crossorigin="anonymous">
	<link rel="stylesheet" href="https://use.fontawesome.com/releases/v5.3.1/css/all.css" integrity="sha384-mzrmE5qonljUremFsqc01SB46JvROS7bZs3IO2EmfFsd15uHvIt+Y8vEf7N7fWAU" crossorigin="anonymous">
	<link rel="stylesheet" type="text/css" href="styles.css">
<style>
@import url('https://fonts.googleapis.com/css?family=Numans');

html,body{
background-image: url('/img/fondologin1.png');
background-size: cover;
background-repeat: no-repeat;
height: 100%;
font-family: 'Numans', sans-serif;

}

.container{
height: 85%;
align-content: center;
}

.card{
height: 370px;
margin-top: auto;
margin-bottom: auto;
width: 400px;
background-color: rgba(0,0,0,0.5) !important;
}

.social_icon span{
font-size: 60px;
margin-left: 10px;
color: #FFC312;
}

.social_icon span:hover{
color: white;
cursor: pointer;
}

.card-header h3{
color: white;
}

.social_icon{
position: absolute;
right: 20px;
top: -45px;
}

.input-group-prepend span{
width: 50px;
background-color: #7d7d73;
color: black;
border:0 !important;
}

input:focus{
outline: 0 0 0 0  !important;
box-shadow: 0 0 0 0 !important;

}

.remember{
color: white;
}

.remember input
{
width: 20px;
height: 20px;
margin-left: 15px;
margin-right: 5px;
}

.login_btn{
color: black;
background-color: #7d7d73;
width: 100px;
}

.login_btn:hover{
color: black;
background-color: white;
}

.links{
color: white;
}

.links a{
margin-left: 4px;
}
</style>
</head>
<body>
 <nav class="navbar navbar-expand-lg bg-body-tertiary" data-bs-theme="dark">
  <div class="container-fluid">   
    <a class="navbar-brand" href="/index">
    <img src="/img/ots.png" alt="Bootstrap" width="80" height="80">

  </a>
    <button class="navbar-toggler" type="button" data-bs-toggle="collapse" data-bs-target="#navbarNavAltMarkup" aria-controls="navbarNavAltMarkup" aria-expanded="false" aria-label="Toggle navigation">
      <span class="navbar-toggler-icon"></span>
    </button>
    <div class="collapse navbar-collapse" id="navbarNavAltMarkup">
      <div class="navbar-nav">
        <a class="nav-link active" aria-current="page" href="#seccion1">Servicio</a>
        <a class="nav-link active" aria-current="page" href="#seccion2">Nosotros</a>
        <a class="nav-link active" aria-current="page" href="#seccion3">Clientes</a>
        <a class="nav-link active" aria-current="page" href="#seccion4">Blog</a>
        <a class="nav-link active" aria-current="page" href="#seccion5">Contacto</a>
      </div>
    </div>
  </div>
</nav>


<div class="container">
    <div class="d-flex justify-content-center h-100">
        <div class="card">
            <div class="card-header">
                <h3>Registro</h3>
            </div>

            <div class="card-body">
                <form action="/entidad" method="POST">
                    <div class="input-group form-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-envelope"></i>
                            </span>
                        </div>
                        <input type="email" name="correo" class="form-control" placeholder="Correo electrónico">
                    </div>

            
                    <div class="input-group form-group">
                        <div class="input-group-prepend">
                            <span class="input-group-text">
                                <i class="fas fa-key"></i>
                            </span>
                        </div>
                        <input type="password" name="password" class="form-control" placeholder="Contraseña">
                    </div>

                    <div class="form-group">
						<input type="submit" value="Crear" class="btn float-right login_btn">
					</div>

                </form>
            </div>

            <div class="card-footer">
                <div class="d-flex justify-content-center links">
                    ¿Ya tienes una cuenta?
                    <a href="/create/login">Iniciar sesión</a>
                </div>
            </div>
        </div>
    </div>
</div>
<footer class="bg-dark text-white mt-auto py-3">
    <div class="container-fluid">
        <div class="row text-center align-items-center">

            <div class="col-lg-3">
                <img src="/img/ots.png" alt="Logo" width="80">
            </div>

            <div class="col-lg-3">
                Contacto
            </div>

            <div class="col-lg-2">
                Redes Sociales
            </div>

            <div class="col-lg-2">
                Nosotros
            </div>

            <div class="col-lg-2">
                Datos legales
            </div>

        </div>
    </div>
</footer>
</body>
</html>