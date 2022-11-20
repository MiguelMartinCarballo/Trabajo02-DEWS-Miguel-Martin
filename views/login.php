<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Ejercicio 18 - Miguel Martín</title>
</head>

<body>
  <!-- Formulario para iniciar sesión -->
  <h1>Formulario de login</h1>

  <form action="?method=auth" method="post">
    <label for="">Nombre </label>
    <input type="text" name="usuario">
    <br>
    <label for="">Contraseña </label>
    <input type="text" name="pw">
    <br>
    <input type="submit" name="envio" id="envio" value="Entrar">
  </form>
</body>

</html>