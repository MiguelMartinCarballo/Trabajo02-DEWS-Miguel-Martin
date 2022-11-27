<!DOCTYPE html>
<html lang="en">

<head>
  <meta charset="UTF-8">
  <meta name="viewport" content="width=device-width, initial-scale=1.0">
  <title>Trabajo 02 - Miguel Martín</title>
  <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.0.2/dist/css/bootstrap.min.css" rel="stylesheet" integrity="sha384-EVSTQN3/azprG1Anm3QDgpJLIm9Nao0Yz1ztcQTwFspd3yD65VohhpuuCOmLASjC" crossorigin="anonymous">
  <style>
    html {
      width: 1920px;
    }

    div>div {
      outline: 1px solid rgb(0, 0, 0);
      padding-top: 10px;
    }

    tr {
      height: 40px;
    }

    input[type="submit"]{
      background-color: #ffc107;
      border: solid 2px #FF8B0F;
      border-radius: 15px;
    }

    h5 {
      width: 100%;
      text-align: center;
    }
  </style>
</head>

<!-- Se usa bootstrap con columnas para colocar cada operación -->

<body style="margin: 5vh 10vh; background-color: #fff6dd;">
    <!-- Comprueba primero si la sesión está iniciada, si no está vulve al login -->
  <?php
  if (!isset($_SESSION['usuariook'])) {
    header("Location: ?method=login");
  }
  ?>

  <!-- Título con saludo al usuario de la sesión -->
  <div class="row my-5 justify-content-md-center bg-warning bg-gradient">
    <div class="col-12 text-center">
      <h1>Bienvenido <?= $_SESSION['usuariook']['nombreusu'] ?></h1>
    </div>
    <h5><a href="?method=cerrar">Cerrar sesión</a></h5>
  </div>

  <!--  Tabla con la lista de los contactos obtenidos del método 'todosContratos' 
        Añade botones en cada contacto para eliminarlo de la bbdd y para modificarlo 
        en ambos se envía el telefono para usarlo como identificador
  -->
  <div class="row">
    <div class="col-2">
      <h2>Lista de contactos</h2>
      <table>
        <?php
        foreach ($contactos as $dato) {
          echo "<tr><td colspan=2> ----------------------------------------- </td></tr>";
          foreach ($dato as $clave => $valor) {
            if ($valor != null) {
              echo "
          <tr>
            <td>$clave </td>
            <td>$valor</td>
          </tr>
        ";
            }
            if ($clave == 'telefono') {
              $numero = $valor;
            }
          }
        ?>
          <tr>
            <td>
              <form action="?method=borrarContacto" method="post">
                <input type="hidden" name="numero" value=<?= $numero ?>>
                <input type="submit" name="envioEliminar" value="Eliminar">
              </form>
            </td>
            <td>
              <form action="?method=home" method="post">
                <input type="hidden" name="numero" value=<?= $numero ?>>
                <input type="submit" name="envioModificar" value="Modificar">
              </form>
            </td>
          </tr>
        <?php
        }
        ?>

      </table>
    </div>


    <!--  Dos tablas para añadir contactos a la bbdd,
          una para añadir tipo persona y otra para empresa,
          con formulario para enviar los datos al método que los inserta -->
    <div class="col-2 mx-4">
      <h2>Añadir contacto</h2>
      <br>
      <h3>Persona</h3>
      <form action="?method=crearContacto" method="post">
        <table>
          <input type="hidden" name="tipo" value="persona">
          <tr>
            <td>
              <label for="telefono">Teléfono: </label>
            </td>
            <td>
              <input type="text" name="telefono" required>
            </td>
          </tr>
          <tr>
            <td>
              <label for="nombre">Nombre: </label>
            </td>
            <td>
              <input type="text" name="nombre" required>
            </td>
          </tr>
          <tr>
            <td>
              <label for="apellidos">Apellidos: </label>
            </td>
            <td>
              <input type="text" name="apellidos" required>
            </td>
          </tr>
          <tr>
            <td>
              <label for="direccion">Dirección: </label>
            </td>
            <td>
              <input type="text" name="direccion" required>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <br>
              <input type="submit" name="envioContacto" value="Añadir persona">
            </td>
          </tr>
        </table>
        <br><br>
      </form>

      <h3>Empresa</h3>
      <form action="?method=crearContacto" method="post">
        <table>
          <input type="hidden" name="tipo" value="empresa">
          <tr>
            <td>
              <label for="telefono">Teléfono: </label>
            </td>
            <td>
              <input type="text" name="telefono" required>
            </td>
          </tr>
          <tr>
            <td>
              <label for="nombre">Nombre: </label>
            </td>
            <td>
              <input type="text" name="nombre" required>
            </td>
          </tr>
          <tr>
            <td>
              <label for="apellidos">Apellidos: </label>
            </td>
            <td>
              <input type="text" name="apellidos" required>
            </td>
          </tr>
          <tr>
            <td>
              <label for="email">Email: </label>
            </td>
            <td>
              <input type="text" name="email" required>
            </td>
          </tr>
          <tr>
            <td colspan="2">
              <br>
              <input type="submit" name="envioContacto" value="Añadir empresa">
            </td>
          </tr>
        </table>
      </form>
    </div>

    <!-- Formulario para buscar contacto, envía el texto para buscar un contacto con el texto en el nombre -->
    <div class="col-2">
      <h2>Buscar contacto</h2>
      <br>
      <form action="?method=home" method="post">
        <label for="nombre">Nombre: </label>
        <input type="text" name="nombreBusqueda" required>
        <br><br>
        <input type="submit" name="envioBusqueda" value="Buscar contacto">
      </form>

      <!--  Mediante los datos recibidos de la consulta de buscar contacto mediante el formulario anterior,
            si existe lo recorre y lo muestra en una tabla, añadiendo los botones de eliminar y modificar -->
      <?php if (isset($busqueda)) { ?>
        <table>
          <?php
          foreach ($busqueda as $dato) {
            echo "<tr><td colspan=2> ------------------------ </td></tr>";
            foreach ($dato as $clave => $valor) {
              if ($valor != null) {
                echo "
          <tr>
            <td>$clave </td>
            <td>$valor</td>
          </tr>
        ";
              }
              if ($clave == 'telefono') {
                $numero = $valor;
              }
            }
          ?>
            <tr>
              <td>
                <form action="?method=borrarContacto" method="post">
                  <input type="hidden" name="numero" value=<?= $numero ?>>
                  <input type="submit" name="envioEliminar" value="Eliminar">
                </form>
              </td>
              <td>
                <form action="?method=home" method="post">
                  <input type="hidden" name="numero" value=<?= $numero ?>>
                  <input type="submit" name="envioModificar" value="Modificar">
                </form>
              </td>
            </tr>
          <?php
          }
          ?>
        </table>
      <?php } ?>
    </div>


    <!-- Formulario para introducir y enviar un archivo al método 'subirFichero' -->
    <div class="col-3 mx-4">
      <h2>Sube una foto</h2>
      <br>
      <form action="?method=subirfichero" method="post" enctype="multipart/form-data">
        <label for="mifich">Selecciona un fichero</label>
        <input type="file" name="myfile" id="mifich">
        <br><br>
        <input type="submit" name="envio" value="Subir fichero">
      </form>

      <!-- Avisa del resultado de la subida del fichero según los parámetros devueltos -->
      <?php
      if (isset($_GET["estado"])) {
        switch ($_GET["estado"]) {
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
    </div>


    <!--  Mediante los datos recibidos de la consulta de buscar contacto mediante el formulario del botón modificar,
          si existe lo recorre y lo muestra en una tabla, dentro de un formulario, en inputs, para poder ser modificados
          y con un botón que envía los datos al método 'modificarContacto' que lo actualiza en la base de datos, enviando 
          el teléfono antes de modificarlo para usarlo como identificador -->
    <div class="col">
        <h2>Modificar contacto</h2>
        <?php if (isset($modificar)) { ?>
        <table>
          <tr>
            <td colspan=2> ------------------------ </td>
          </tr>
          <form action="?method=modificarContacto" method="post">
            <?php
            foreach ($modificar[0] as $clave => $valor) {
              if ($valor != null) {
            ?>

                <tr>
                  <?php
                  if ($clave == 'tipo') {
                  ?>
                    <td><label><?= $clave ?></label> </td>
                    <td><label><?= $valor ?><label></td>
                    <input type="hidden" name="tipo" value="<?= $valor ?>">
                  <?php
                  } else {
                  ?>
                    <td><label for="<?= $clave ?>"><?= $clave ?></label> </td>
                    <td><input type="text" name="<?= $clave ?>" value="<?= $valor ?>" required> </td>
                  <?php
                  }
                  ?>
                </tr>

            <?php
              }
              if ($clave == 'telefono') {
                $numero = $valor;
              }
            }
            ?>
            <input type="hidden" name="numeroAnterior" value=<?= $numero ?>>
            <tr>
              <td>
                <input type="submit" name="envioActualizar" value="Actualizar">
              </td>
            </tr>
          </form>
        </table>
      <?php } ?>
    </div>


  </div>
</body>

</html>