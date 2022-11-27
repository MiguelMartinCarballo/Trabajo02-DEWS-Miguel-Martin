<?php

class App
{

  /**
   * Se guardan las variables con los datos para conectar con la base de datos,
   * el dsn, el usuario y la contraseña.
   */
  public $dsn = 'mysql:dbname=agenda;host=localhost';
  public $usuario = "dbuser";
  public $password = "secret";
  public $db;

  /**
   * Constructor
   * inicia la clase iniciando una sesión,
   * conecta con la base de datos mediante 'PDO'
   */
  public function __construct()
  {
    session_start();

    try {
      $this->db = new PDO($this->dsn, $this->usuario, $this->password);
    } catch (PDOException $e) {
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
   * Si la sesión 'usuariook' esta establecida redirige al método 'home', porque ya se ha iniciado sesión en la sesión actual
   * si no, incluye la vista 'login.php', donde se podrá iniciar sesión
   */
  public function login()
  {
    if (isset($_SESSION['usuariook'])) {
      header('Location: ?method=home');
    } else {
      include('views/login.php');
    }
  }


  /**
   * cargarxml
   * obtiene los datos del archivo xml, se codifica en utf-8, se carga en una variable como array asociativo,
   * luego se recorre este array, sacando el atributo y el resto de valores e introduciendolos en la base de datos
   * mediante una sentencia 'insert'. Dependiendo del atributo, persona o empresa, se insertará e apellidos o email.
   */
  public function cargarxml()
  {
    $agenda = utf8_encode(file_get_contents('agenda.xml'));
    $datos = simplexml_load_string($agenda);

    foreach ($datos as $dato) {
      $i = 1;

      $atributos = $dato->attributes();

      foreach ($atributos as $atributo) {
        if ($atributo == 'empresa') {
          $sql = $this->db->prepare("INSERT IGNORE INTO contactos (tipo, nombre, direccion, telefono, email) VALUES ('empresa', ?, ?, ?, ?)");
        } else {
          $sql = $this->db->prepare("INSERT IGNORE INTO contactos (tipo, nombre, apellidos, direccion, telefono) VALUES ('persona', ?, ?, ?, ?)");
        }
      }

      // Se recorren los elementos del xml y se asignan por orden en los parámetros de la sentencia
      foreach ($dato as $valor) {
        $sql->bindValue($i, "$valor");
        $i++;
      }

      $sql->execute();
    }
  }


  /**
   * comprobarcredenciales
   * Parámetros: $nombreusu, $clave
   * Devuelve: $creedenciales o $false
   * Se pasa por parámetro el nombre de usuario y la clave,
   *  se realiza una consulta de los usuarios y contraseñas almacenadas en la base de datos en la tabla de credenciales,
   *  se recorre el resultado de la consulta y se comprueba si los parámetros pasados coinciden, en tal caso,
   *  se crea un array con el nombre de usuario para guardarlo posteriormente en una sesión
   *  y se devuelve este array.
   *  En caso de no coincidir el nombre y contraseña o no realizarse la consulta, devuelve un booleano falso.
   */
  public function comprobarcredenciales($nombreusu, $clave)
  {
    $sql = $this->db->prepare("SELECT usuario, password FROM credenciales");
    $sql->execute();
    $resultado = $sql->fetchAll();
    foreach ($resultado as $credencialesdb) {
      if ($nombreusu === $credencialesdb[0] && password_verify($clave, $credencialesdb[1])) {
        $credenciales["nombreusu"] = $nombreusu;
        return $credenciales;
      }
    }
    return false;
  }

  /**
   * auth
   * Será llamado desde la vista login
   * Comprueba si las credenciales recibidas por formulario con método 'post' son válidas
   *  mediante el método 'comprobarcredenciales' y pásandole por parámetro lo recibido,
   *  en caso de ser falso, lo enviará a la vista 'login' de nuevo,
   *  en caso de no ser falso, introduce el array de credenciales en una sesión,
   *  carga los datos del xml en la base de datos mediante el método 'cargarxml' y redirige al método 'home',
   *  para ir a la pantalla principal.
   */
  public function auth()
  {
    if ($_SERVER["REQUEST_METHOD"] === "POST") {
      if (isset($_POST["envio"])) {
        $credentials = $this->comprobarcredenciales($_POST["usuario"], $_POST["pw"]);
        if ($credentials === false) {
          header("Location: ?method=login");
        } else {
          $_SESSION["usuariook"] = $credentials;
          $this->cargarxml();
          header("Location: ?method=home");
          exit();
        }
      }
    }
  }

  /**
   * home
   * Es llamado desde el método 'auth'
   * si la sesion 'usuariook' no está establecida redirige al método 'login' de vuelta.
   * si no, 
   * guarda en todos todos lo contactos de la base de datos mediante le método 'todosContactos',
   * si se recibe por 'post' que se ha pulsado el botón de buscar contacto o el de modificar,
   * se guarda en una variable distinta el array con los datos del contacto recibidos mediante el método 'buscarContacto'
   * y se incluye la vista 'home'.
   */
  public function home()
  {
    if (!isset($_SESSION["usuariook"])) {
      header('Location: ?method=login');
    } else {
      $contactos = $this->todosContactos();
      if (isset($_POST['envioBusqueda'])) {
        $busqueda = $this->buscarContacto();
      }
      if (isset($_POST['envioModificar'])) {
        $modificar = $this->buscarContacto();
      }
      include('views/home.php');
    }
  }


  /**
   * todosContactos
   * Devuelve: $contactos, array asociativo
   * hace una consulta seleccionando todos los datos de la tabla 'contactos' de la base de datos,
   * lo guarda como un array asociativo y lo devuelve.
   */
  public function todosContactos()
  {
    $sql = $this->db->prepare("SELECT * FROM contactos");
    $sql->execute();
    $contactos = $sql->fetchAll(PDO::FETCH_ASSOC);

    return $contactos;
  }


  /**
   * buscarContacto
   * Si se recibe un nombre para buscar, realiza una consulta en la base de datos que contenga ese nombre.
   * Si se recibe un número para buscar, realiza una consulta en la base de datos que tenga ese número como teléfono.
   * Devuelve: El resultado de esa consulta como array asociativo.
   */
  public function buscarContacto()
  {
    if(isset($_POST['nombreBusqueda'])){
      $nombreBusqueda = $_POST['nombreBusqueda'];
      $sql = $this->db->prepare("SELECT * FROM contactos WHERE nombre LIKE '%$nombreBusqueda%'");
    }
    if(isset($_POST['numero'])){
      $numeroBusqueda = $_POST['numero'];
      $sql = $this->db->prepare("SELECT * FROM contactos WHERE telefono = '$numeroBusqueda'");
    }
    $sql->execute();
    $contacto = $sql->fetchAll(PDO::FETCH_ASSOC);

    return $contacto;
  }


  /**
   * crearContacto
   * Recibe los datos de un formulario,
   * realiza una inserción en la bbdd en la tabla contactos con los datos recibidos,
   * según si es de tipo perdona o contacto, se introduce apellidos o email.
   * Redirige al método 'home'.
   */
  public function crearContacto()
  {
    if (isset($_POST["envioContacto"])) {
      $tipo = $_POST['tipo'];
      $nombre = $_POST['nombre'];
      $direccion = $_POST['direccion'];
      $telefono = $_POST['telefono'];

      switch ($tipo) {
        case 'persona':
          $apellidos = $_POST['apellidos'];
          $sql = $this->db->prepare("INSERT IGNORE INTO contactos (tipo, nombre, direccion, telefono, apellidos) VALUES ('$tipo', '$nombre', '$direccion', '$telefono', '$apellidos')");
          $sql->execute();
          break;
        case 'empresa':
          $email = $_POST['email'];
          $sql = $this->db->prepare("INSERT IGNORE INTO contactos (tipo, nombre, direccion, telefono, email) VALUES ($tipo, $nombre, $direccion, $telefono, $email)");
          $sql->execute();
          break;
      }
    }
    header('Location: ?method=home');
  }


  /**
   * borrarContacto
   * Borra de la tabla contactos de la bbdd la fila con el dato telefono igual al número recibido por 'post'
   * Redirige al método 'home'
   */
  public function borrarContacto()
  {
    if (isset($_POST['envioEliminar'])) {
      $numero = $_POST['numero'];
      $sql = $this->db->prepare("DELETE FROM contactos WHERE telefono = '$numero'");
      $sql->execute();
    }
    header('Location: ?method=home');
  }


  /**
   * modificarContacto
   * Recibe los datos de un formulario,
   * realiza una actualización en la bbdd en la tabla contactos con los datos recibidos,
   * según si es de tipo perdona o contacto, se actualiza apellidos o email.
   * Redirige al método 'home'.
   */
  public function modificarContacto()
  {
    if (isset($_POST["envioActualizar"])) {
      $numero = $_POST['numeroAnterior'];
      $tipo = $_POST['tipo'];
      $nombre = $_POST['nombre'];
      $direccion = $_POST['direccion'];
      $telefono = $_POST['telefono'];
      switch ($tipo) {
        case 'persona':
          $apellidos = $_POST['apellidos'];
          $sql = $this->db->prepare("UPDATE contactos SET nombre = '$nombre', direccion = '$direccion', telefono = '$telefono', apellidos = '$apellidos' WHERE telefono = '$numero'");
          $sql->execute();
          break;
        case 'empresa':
          $email = $_POST['email'];
          $sql = $this->db->prepare("UPDATE contactos SET nombre = '$nombre', direccion = '$direccion', telefono = '$telefono', email = '$email' WHERE telefono = '$numero'");
          $sql->execute();
          break;
      }
    }
    header('Location: ?method=home');
  }


  /**
   * subirFichero
   * Recibe un fichero de un formulario
   * comprueba que el fichero cumpla con el formato png, jpg o pdf y que tenga un tamaño menor a 5 MB,
   * si cumple sube el archivo a la carpeta 'uploads' en le directorio raiz y se envía un parámetro para luego indicarlo en la vista,
   * si no cumple, envía un parámetro con el tipo de error, para luego mostrarlo en la vista.
   * Redirige al método 'home'.
   */
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


  /**
   * Si hay sesión cierra y borra la sesión y redirige al método 'login'
   */
  public function cerrar()
  {
    if (isset($_SESSION["usuariook"])) {
      $_SESSION = array();
      session_destroy();
      setcookie(session_name(), '', time() - 7200, '/');
    }
    header("Location: ?method=login");
  }
}
