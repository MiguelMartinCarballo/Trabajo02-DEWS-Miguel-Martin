<?php

class App
{

  public $dsn = 'mysql:dbname=agenda;host=localhost';
  public $usuario = "dbuser";
  public $password = "secret";
  public $db;

  /**
   * Constructor
   * inicia la clase iniciando una sesión
   */
  public function __construct()
  {
    session_start();

    try{
      $this->db = new PDO($this->dsn, $this->usuario, $this->password);
    } catch (PDOException $e){
      echo "Error producido al conectar: " . $e->getMessage();
    }
  }

  /**
   * run
   * Si recibe un método cuando lo llaman ejecutará ese método
   * si no lo recibe ejecuta el método 'login'
   */
  public function run()
  {
    if (isset($_GET['method'])) {
      $method = $_GET['method'];
    } else {
      $method = 'login';
    }

    $this->$method();
  }

  /**
   * login
   * Si la cookie 'nombre' esta establecida redirige al método 'home'
   * si no, incluye la vista 'login.php'
   */
  public function login()
  {
    if (isset($_COOKIE['name'])) {
      header('Location: ?method=home');
    } else {
      include('views/login.php');
    }
  }


  public function comprobarcredenciales($nombreusu, $clave)
  {
    $sql = $this->db->prepare("SELECT usuario, password FROM credenciales");
    $sql->execute();
    $resultado = $sql->fetchAll();
    foreach ($resultado as $credencialesdb) {
      echo "$credencialesdb[0] --- $credencialesdb[1]<br>";
      if ($nombreusu === $credencialesdb[0] && $clave === $credencialesdb[1]) {
        $credenciales["nombreusu"] = $nombreusu;
        return $credenciales;
      }
    }
    return false;
  }

  /**
   * auth
   * Será llamado desde la vista login
   * Si ha recibido 'name', establecerá una cookie para el nombre y otra para la contraseña
   * y redirigirá al metodo home
   * si no, redirige de nuevo al método login
   */
  public function auth()
  {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      if (isset($_POST["envio"])) {
        $credentials = $this->comprobarcredenciales($_POST["usuario"], $_POST["pw"]);
        if ($credentials === false) {
          $error = 1;
          header("Location: ?method=login");
        } else {
          $_SESSION["usuariook"] = $credentials;
          header("Location: ?method=home");
          exit();
        }
      }
    }
  }

  /**
   * home
   * Es llamado desde el método 'auth'
   * si la cookie 'deseos' esta establecida la deserializa para obetener la información de los bytes y la guarda
   * si no, crea un array.
   * Siempre que la cookie 'name' esté establecida incluirá la vista 'home.php'
   * donde se mostrará la infomación de la lista de deseos
   */
  public function home()
  {
    if (!isset($_SESSION["usuariook"])) {
      header('Location: ?method=login');
    } else {
      if (isset($_SESSION['deseos'])) {
        $deseos = unserialize($_COOKIE['deseos']);
      } else {
        $deseos = array();
      }
      include('views/home.php');
    }
  }

  /**
   * new
   * Deserializa los datos de la cookie 'deseos' en caso de estar establecida y los guarda en un array
   * o crea un array nuevo en caso distinto
   * Añade el nuevo deseo recibido por formulario al array y lo vuelve a poner como cookie
   * Finalmente, redirecciona al metodo 'home' de nuevo
   */
  public function new()
  {
    if (!isset($_POST['new'])) {
      header('Location: index.php?method=home');
    } else {
      if (isset($_COOKIE['deseos'])) {
        $deseos = unserialize($_COOKIE['deseos']);
      } else {
        $deseos = [];
      }
      $deseos[] = $_POST['new'];
      setcookie('deseos', serialize($deseos), strtotime("60 minutes"));
      header('Location: index.php?method=home');
    }
  }

  /**
   * delete
   * Deserializa los datos de la cookie 'deseos' en caso de estar establecida y los guarda en un array
   * o crea un array nuevo en caso distinto
   * Elimina el deseo con el id recibido por formulario del array y lo vuelve a poner como cookie
   * Finalmente, redirecciona al metodo 'home' de nuevo
   */
  public function delete()
  {
    if (isset($_COOKIE['deseos'])) {
      $deseos = unserialize($_COOKIE['deseos']);
    } else {
      $deseos = [];
    }
    $id = $_GET['id'];
    unset($deseos[$id]);
    setcookie('deseos', serialize($deseos), strtotime("60 minutes"));
    header('Location: index.php?method=home');
  }

  /**
   * empty
   * vacía la cookie 'deseos' y vuelve al método 'home'
   */
  public function empty()
  {
    setcookie('deseos', '', time() - 7000);
    header('Location: index.php?method=home');
  }

  /**
   * close
   * Elimina todas las cookies
   * y redirige al método 'login'
   */
  public function close()
  {
    setcookie('deseos', '',  time() - 7000);
    setcookie('name', '',  time() - 7000);
    setcookie('password', '',  time() - 7000);
    header('Location: index.php?method=login');
  }


  public function subirfichero()
  {
    if (isset($_POST["envio"])) {
      $tipo = $_FILES["myfile"]["type"];
      if ($tipo != 'image/png' & $tipo != 'image/jpeg' & $tipo != 'application/pdf') {
        header('Location: ?method=home&estado=errorformato');
        exit();
      } elseif ($_FILES["myfile"]["size"] > 5242880) {
        header('Location: ?method=home&estado=errorsize');
        exit();
      } else {
        $destino = "uploads/" . $_FILES["myfile"]["name"];
        move_uploaded_file($_FILES["myfile"]["tmp_name"], $destino);
        header('Location: ?method=home&estado=correcto');
        exit();
      }
    }
    header('Location: ?method=home');
  }
}
