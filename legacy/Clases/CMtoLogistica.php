<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Mantenimiento de Maquinas
//-------------------------------------
class CMtoLogistica extends CBase {
   public $paEstado, $pcEstado, $pcCodUsu, $pcCodPer, $pcCodSan, $pcPerEmp, $paCodPer, $paCodSan, $paCodNor, $paPerEmp,
          $pdFecIni, $pmObserv, $pdFecha, $pdCese, $pcNombre, $pcDirecc, $pcSexo, $paSexo, $paTipo, $pcTipo, $pmDatos,
          $paIndex, $paData, $paNombre, $paCodEmp, $plNuevo, $paEstCiv, $paCargo, $paCargos, $pcNroDni, $pcSector,
          $paSector, $pcDesSec, $paCarDis, $paAreas, $paCodPry, $pcNomMaq,$pcDirec;
   
   public function __construct() {
      parent::__construct();
      $this->paEstado = null;
      $this->paSexo   = null;
      $this->paTipo   = null;
      $this->paEstCiv = null;
      $this->paCargo  = null;
      $this->paCargos = null;
      $this->paData   = null;
      $this->paCodEmp = null;
      $this->paCodPer = null;
      $this->paSector = null;
      $this->paCarDis = null;
      $this->paAreas  = null;
      $this->paCodPry = null;
   }
   
   // Inicializa historia de personal
   public function omInit() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Maquina
      $RS = $loSql->omExec("SELECT cCodPrv,cNombre FROM E01MPRV");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPrv[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DEFINIDOS. TABLA [001]</ERROR></DATA>';
         return false;
      }
      // Carga insumos
      $RS = $loSql->omExec("SELECT cCodIns,cDescri FROM E01MINS");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodIns[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DEFINIDOS. TABLA [001]</ERROR></DATA>';
         return false;
      }
      $loSql->omDisconnect();
      return true;
   }
   
   public function omGrabar(){
   
	  return true;

   }
   
   public function omEditar(){
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Maquinas
      $RS = $loSql->omExec("SELECT cCodMaq,cDescri,cEstado,cTipo FROM E01MMAQ WHERE CCODMAQ ='$this->pcCodMaq'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>NO HAY MAQUINA REGISTRADOS</ERROR></DATA>';
            return false;
      }else{
	  $this->pcNomMaq=$laFila[1];
	  $this->pcEstado=$laFila[2];
	  $this->pcTipo=$laFila[3];
	  }
	  $loSql->omDisconnect();
      return true;
   }
  
}
?>