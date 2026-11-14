<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Matricula alumno en curso
//-------------------------------------
class CMtoProyectos extends CBase {
   public $paData, $paEstado, $pdFecIni, $pcDescri, $pcEstado, $pcCodUsu, $pcCodPry, $pmDatos , $paCodPry, $pcCodPer,
          $paCodPer;
   
   public function __construct() {
      parent::__construct();
      $this->paEstado = null;
      $this->paCodPer = null;
      $this->paData = null;
   }

   // Inicia mantenimiento de proyectos
   public function omCargarMtoProyectos() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga personal
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE cEstado = 'A' ORDER BY cNombre");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL DEFINIDO </ERROR></DATA>';
         return false;
      }
      // Carga proyectos
      $RS = $loSql->omExec("SELECT cCodPry, cDescri FROM S02MPRY ORDER BY cDescri");
      while ($laFila = $loSql->fetch($RS)) {
         $this->paData[] = array($laFila[0], $laFila[1]);
      }
      // Carga Estados
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '001'");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DEFINIDOS. TABLA [001]</ERROR></DATA>';
         return false;
      }
      return true;
   }

   // Aplica codigo de proyecto
   public function omAplicarProyecto() {
      if (empty($this->pcCodPry)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PROYECTO VACIO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga proyecto
      $lcSql = "SELECT cDescri, TO_CHAR(dFecIni, 'yyyy-mm-dd'), cEstado, cCodPer, mDatos FROM S02MPRY WHERE cCodPry = '$this->pcCodPry'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->pcDescri = $laFila[0];
      if (empty($this->pcDescri)) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE PROYECTO NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pdFecIni = $laFila[1];
      $this->pcEstado = $laFila[2];
      $this->pcCodPer = $laFila[3];
      $this->pmDatos  = $laFila[4]->load();
      $loSql->omDisconnect();
      return $llOk;
   }

   // Grabar proyecto
   public function omGrabarProyecto() {
      if (empty($this->pcCodPry)) {
            $this->pcError = '<DATA><ERROR>CODIGO DE PROYECTO NO DEFINIDO</ERROR></DATA>';
            return false;
      } else if (empty($this->pcDescri)) {
         $this->pcError = '<DATA><ERROR>DESCRIPCION DEL PROYECTO ESTA VACIA</ERROR></DATA>';
         return false;
      } else if (empty($this->pdFecIni)) {
         $this->pcError = '<DATA><ERROR>FECHA DE INICIO VACIA</ERROR></DATA>';
         return false;
      } else if (!$this->mxValDate($this->pdFecIni)) {
         $this->pcError = '<DATA><ERROR>FECHA DE INICIO INVALIDA</ERROR></DATA>';
         return false;
      } else if (empty($this->pcEstado)) {
         $this->pcError = '<DATA><ERROR>ESTADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pmDatos)) {
         $this->pcError = '<DATA><ERROR>DATOS VARIOS NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodPry) AND !$this->plNuevo) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PROYECTO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $this->pcDescri = strtoupper($this->pcDescri);
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      if ($this->plNuevo) {
         $RS = $loSql->omExec("SELECT cCodPry FROM S02MPRY WHERE cCodPry = '$this->pcCodPry'");
         $laFila = $loSql->fetch($RS);
            if (!empty($laFila[0])) {
                $this->pcError = '<DATA><ERROR>CODIGO DE PROYECTO REPETIDO</ERROR></DATA>';
                return false;
            }
            $lcSql = "INSERT INTO S02MPRY (cCodPry, cDescri, dFecIni, cEstado, cCodPer, mDatos, cCodUsu) VALUES ('$this->pcCodPry','$this->pcDescri', TO_DATE('$this->pdFecIni','yyyy-mm-dd'), '$this->pcEstado', '$this->pcCodPer', '$this->pmDatos', '$this->pcCodUsu')";
      } else {
         $RS = $loSql->omExec("SELECT cCodPry FROM S02MPRY WHERE cCodPry = '$this->pcCodPry'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE PROYECTO NO DEFINIDO</ERROR></DATA>';
            return false;
         }
         $lcSql = "UPDATE S02MPRY SET cDescri = '$this->pcDescri', dFecIni = TO_DATE('$this->pdFecIni','yyyy-mm-dd'), cEstado = '$this->pcEstado', mDatos = '$this->pmDatos', cCodPer = '$this->pcCodPer', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cCodPry = '$this->pcCodPry'";
      }
      $llOk = $loSql->omExec($lcSql);
	  
      $loSql->omDisconnect();
      return true;
   }
   
   // Buscar proyectos
   public function omBuscarProyectos() {
      // Condiciones de excepcion
      if (empty($this->pcDescri)) {
         $this->pcError = '<DATA><ERROR>DESCRIPCION INVALIDA</ERROR></DATA>';
         return false;
      }
      $this->paCodPry = null;
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcDescri = trim($this->pcDescri).'%';
      $lcSql = "SELECT cCodPry, cDescri FROM S02MPRY WHERE cDescri LIKE '$lcDescri' ORDER BY cDescri";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPry[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY PROYECTOS QUE CUMPLAN CONDICION</ERROR></DATA>';
         $llOk = false;
      }
      return $llOk;
   }   



//////////////////////////////////////////////////////////////


}

?>
