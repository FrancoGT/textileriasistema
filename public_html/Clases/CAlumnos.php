<?php
require_once "Clases/CBase.php";
//-------------------------------------
// Mantenimiento de alumnos
//-------------------------------------
class CAlumnos extends CBase {
   public $pcCodAlu = null;
   public $pcNomAlu = null;
   public $pcEmail  = null;
   public $pcNroDni = null;
   public $pcClave  = null;
   public $pcNacion = null;
   public $pcEstado = null;
   public $pcCodUsu = null;
   public $plNuevo  = null;
   public $paEstado = null;
   public $paNacion = null;

   public function __construct() {
      parent::__construct();
   }

   public function omIniciar() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Estado de alumnos
      $RS = $loSql->omExec("SELECT cDescri, SUBSTRING(cCodigo, 1, 1)::CHARACTER(1) FROM V_S01TTAB WHERE cCodTab = '007'");
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = 'TABLA DE ESTADO DE ALUMNOS [007] NO ESTA DEFINIDA';
         return false;
      }
      while ($laFila = pg_fetch_row($RS)) {
         $this->paEstado[] = array($laFila[0], $laFila[1], False);
      }
      $this->paEstado[0][2] = True;
      // Estado de alumnos
      $RS = $loSql->omExec("SELECT cDescri, SUBSTRING(cCodigo, 1, 3)::CHARACTER(3) FROM V_S01TTAB WHERE cCodTab = '008'");
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = 'TABLA DE NACIONALIDADES [008] NO ESTA DEFINIDA';
         return false;
      }
      while ($laFila = pg_fetch_row($RS)) {
         $this->paNacion[] = array($laFila[0], $laFila[1], 0);
      }
      $loSql->omDisconnect();
      return $llOk;
   }

   public function omAplicar() {
      // Trae tablas de nacionalidad y estado
      $llOk = $this->omIniciar();
      if (!$llOk) {
         return false;
      }
      // Conecta con DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Verifica si codigo de alumno existe
      $RS = $loSql->omExec("SELECT cNombre, cEmail, cNroDni, cNacion, cEstado FROM V_Alumnos WHERE cCodAlu = '$this->pcCodAlu'");
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      $this->pcError = "ALUMNO NO EXISTE";
      $llOk = false;
      while ($laFila = pg_fetch_row($RS)) {
         $this->pcNomAlu = $laFila[0];
         $this->pcEmail  = $laFila[1];
         $this->pcNroDni = $laFila[2];
         $this->pcNacion = $laFila[3];
         $this->pcEstado = $laFila[4];
         $llOk = true;
      }
      $loSql->omDisconnect();
      // Marca nacionalidad y estado elegidos
      for ($i = 0; $i < count($this->paNacion); $i++) {
         if ($this->paNacion[$i][1] == $this->pcNacion) {
            $this->paNacion[$i][2] = True;
         }
      }
      for ($i = 0; $i < count($this->paEstado); $i++) {
         if ($this->paEstado[$i][1] == $this->pcEstado) {
            $this->paEstado[$i][2] = True;
         }
      }
      return $llOk;
   }

   public function omGrabar() {
      // Condiciones de excepcion
      $lcNroDni = trim($this->pcNroDni);
      if (empty($this->pcCodAlu)) {
         $this->pcError = 'CODIGO DE ALUMNO VACIO';
         return false;
      } else if (empty($this->pcNomAlu)) {
         $this->pcError = 'NOMBRE DE ALUMNO VACIO';
         return false;
      } else if (empty($this->pcEmail)) {
         $this->pcError = 'EMAIL VACIO';
         return false;
      } else if (empty($this->pcNroDni)) {
         $this->pcError = 'DNI VACIO';
         return false;
      } else if (empty($this->pcNacion)) {
         $this->pcError = 'NACIONALIDAD NO DEFINIDA';
         return false;
      } else if (($this->plNuevo) && empty($this->pcClave)) {
         $this->pcError = 'CLAVE NO DEFINIDA';
         return false;
      } else if (($this->plNuevo) && ($this->pcClave != $this->pcClave1)) {
         $this->pcError = 'CLAVES NO SON IGUALES';
         return false;
      } else if (empty($this->pcEstado)) {
         $this->pcError = 'ESTADO NO DEFINIDO';
         return false;
      } else if (strlen($lcNroDni) != 8) {
         $this->pcError = 'LONGITUD DE DNI ERRADO';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = 'CODIGO DE USUARIO VACIO';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      if ($this->plNuevo) {
         $lcSql = "SELECT * FROM f_InsertAlumno('$this->pcCodAlu', '$this->pcNomAlu', '$this->pcEmail', '$this->pcNroDni', '$this->pcNacion', '$this->pcClave', '$this->pcEstado', '$this->pcCodUsu')";
         $RS = $loSql->omExec($lcSql);
         $laFila = pg_fetch_row($RS);
      } else {
         $lcSql = "SELECT * FROM f_UpdateAlumno('$this->pcCodAlu', '$this->pcNomAlu', '$this->pcEmail', '$this->pcNroDni', '$this->pcNacion', '$this->pcEstado', '$this->pcCodUsu')";
         $RS = $loSql->omExec($lcSql);
         $laFila = pg_fetch_row($RS);
      }
      $loSql->omDisconnect();
      // Busca resultado
      $loXml = new CXml();
      $loXml->pcData = $laFila[0];
      $loXml->pcLabel = "ERROR";
      $llOk = $loXml->omBuscar(); 
      if ($llOk) {
         $this->pcError = $loXml->pcValor;
         $llOk = false;
      }
      return true;
   }
}
?>
