<?php
require_once "Clases/CBase.php";

// Reportes de incidentes
class CCharlasRep extends CBase {
   public $paCodPer,$pcCodPer;

   public function __construct() {
      parent::__construct();
	  $this->paCodPer = null;
   }
   
   public function omInit(){
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
	  //Carga Empleados
      $lcSql = "SELECT cCodPer, cNombre FROM S03MPER WHERE cEstado = 'A' AND cCodPer != '999999'  ORDER BY CNOMBRE ";
	  $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY EMPLEADOS ACTIVOS</ERROR></DATA>';
         return false;
      }
	  //CargaTipo
	  $lcSql = "SELECT cCodigo, cDescri FROM V_S02TTAB WHERE cCodTab='055'";
	  $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paTipo[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY EMPLEADOS ACTIVOS</ERROR></DATA>';
         return false;
      }
	  //Carga Normas
	  /*
	  $loSql="SELECT cCodNor, cDescri FROM S02MNOR WHERE cTipo = '1' ORDER BY cCodNor";
      $RS = $loSql->omExec($lcSql);
	  $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodNor[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
	     $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY NORMAS DEFINIDAS</ERROR></DATA>';
         return false;
      }*/
      $loSql->omDisconnect();
      return true;
   }
}
?>