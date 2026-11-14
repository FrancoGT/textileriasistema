<?php
require_once 'Clases/CBase.php';

//------------------------------------------------------------------------------------------
// Registro de procesos de proyectos
//------------------------------------------------------------------------------------------
class CProcesos extends CBase {
   public $paCodPry, $paEstado, $pcDesPry, $paData;
   protected $lcEmail;

   public function __construct() {
      parent::__construct();
      $this->paCodPry = $this->paEstado = $this->paData = null;
   }

   public function omInitProcesos() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga maestro de proyectos
      $RS = $loSql->omExec("SELECT cCodPry, cDescri FROM S02MPRY WHERE cEstado = 'A' ORDER BY cDescri");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPry[$i] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PROYECTOS ACTIVOS DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Carga Estados
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '001'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY CARGOS [015] DEFINIDOS</ERROR></DATA>';
         return false;
      }
      return true;
   }

   public function omAplicar() {
      if (empty($this->pcCodPry)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PROYECTO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Descripcion del proyecto
      $RS = $loSql->omExec("SELECT cDescri FROM S02MPRY WHERE cCodPry = '$this->pcCodPry'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE PROYECTO NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcDesPry = $laFila[0];
      // Procesos del proyecto cCodPry, cCodPrc, cDescri
      $RS = $loSql->omExec("SELECT cCodPrc, cDescri FROM S02PPRC WHERE cCodPry = '$this->pcCodPry'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[] = array($i, $laFila[0], $laFila[1], 0);
      }
      $loSql->omDisconnect();
      return true;
   }
   
   public function omGrabar() {
      if (empty($this->pcCodPry)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PROYECTO NO DEFINIDO</ERROR></DATA>';
         return false;
      } if (empty($this->paData) or count($this->paData) == 0){
         $this->pcError = '<DATA><ERROR>NO HAY DATOS DE PROCESOS PARA PROYECTO</ERROR></DATA>';
         return false;
      }
      // Valida descripcion
      foreach($this->paData as $i) {
         if (empty($i[2])) {
            $this->pcError = '<DATA><ERROR>DESCRIPCION DE PROCESO VACIO</ERROR></DATA>';
            return false;   
         }
      }
      // Conecta con DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Hallar maximo correlativo de ccodprc
      $RS = $loSql->omExec('SELECT MAX(cCodPrc) FROM S02PPRC');
      $laFila = $loSql->fetch($RS);
      $lcCodPrc = (empty($laFila[0])) ? '00000' : $laFila[0];
      // Graba procesos de proyectos
      foreach($this->paData as $i) {
         if ($i[3] == 1) {
            // Siguiente correlativo de ccodprc
            $lnTmp = (int)$lcCodPrc + 1;
            $lcCodPrc = (string)$lnTmp;
            $lcCodPrc = right("0000".trim($lcCodPrc), 5);
            $lcSql = "INSERT INTO S02PPRC (cCodPry, cCodPrc, cDescri, cEstado, cCodUsu) VALUES 
                      ('$this->pcCodPry', '$lcCodPrc', UPPER('$i[2]'), 'A', '$this->pcCodUsu')";
         } else {
            $lcSql = "UPDATE S02PPRC SET cDescri = UPPER('$i[2]'), cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cCodPrc = '$i[1]'";
         }
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            break;
         }
      }
      $loSql->omDisconnect();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR AL GRABAR PROCESOS DE PROYECTO</ERROR></DATA>';
         return false;
      }
      return true;
   }
}
?>