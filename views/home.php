<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trabajo 02 - Miguel Martín</title>
</head>

<body style="margin: 5vh 10vh;">

  <?php
    if(!isset($_SESSION['usuariook'])){
      header("Location: ?method=login");
    }
  ?>

  <h1>Bienvenido <?= $_SESSION['usuariook']['nombreusu']?></h1>



  <h2>Sube una foto</h2>
  <form action="?method=subirfichero" method="post" enctype="multipart/form-data">
    <p>
      <label for="mifich">Selecciona un fichero</label>
      <input type="file" name="myfile" id="mifich">
      <br><br>
      <input type="submit" name="envio" value="Subir fichero">
    </p>
  </form>

  <?php
  if (isset($_GET["estado"])) {
    switch($_GET["estado"]) {
      case "errorformato":
        echo "<p style='color: red'>Formato del archivo no permitido</p>";
        break;
      case "errorsize":
        echo "<p style='color: red'>Tamaño del archivo demasiado grande</p>";
        break;
      case "correcto":
        echo "<p style='color: green'>Archivo subido correctamente al servidor</p>";
        break;
    }
  }
  ?>

</body>

</html>