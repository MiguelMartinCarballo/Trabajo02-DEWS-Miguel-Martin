<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trabajo 02 - Miguel Martín</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
</head>

<body style="background-color: #fff6dd;">

<!-- Formulario para iniciar sesión -->
    <form action="?method=auth" method="post">
      <div class="row my-5 justify-content-md-center bg-warning bg-gradient">
        <div class="col-12 text-center">
          <h1>Formulario de login</h1>
        </div>
      </div>
      <div class="row justify-content-md-center">
        <div class="col-5 fw-bold text-end">
          <label for="">Nombre </label>
        </div>
        <div class="col-6">
          <input type="text" name="usuario">
        </div>
      </div>
      <br>
      <div class="row justify-content-md-center">
        <div class="col-5 fw-bold text-end">
          <label for="">Contraseña </label>
        </div>
        <div class="col-6">
          <input type="text" name="pw">
        </div>
      </div>
      <br>
      <div class="row justify-content-md-center">
        <div class="col-12 mt-4 text-center">
          <input class="btn btn-primary" type="submit" name="envio" id="envio" value="Entrar">
        </div>
      </div>
    </form>

  </div>
</body>

</html>