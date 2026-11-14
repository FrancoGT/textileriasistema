<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Mantenimiento de Maquinas
//-------------------------------------
class CMtoMaquinas extends CBase {
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
      $RS = $loSql->omExec("SELECT cCodMaq,cDescri FROM E01MMAQ");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodMaq[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->paCodMaq[] = array('9999', 'CREAR NUEVA MAQUINA');
      }
      // Carga Estados
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '001'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paEstado[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DEFINIDOS. TABLA [001]</ERROR></DATA>';
         return false;
      }
	  // Carga tipos de Maquina
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '005'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paTipo[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE MQUINA DEFINIDOS . TABLA [005]</ERROR></DATA>';
         return false;
      }
      $loSql->omDisconnect();
      return true;
   }
   public function omGrabar(){
      if (empty($this->pcNomMaq)) {
         $this->pcError = '<DATA><ERROR>NOMBRE DE MaqACEN VACIO</ERROR></DATA>';
         return false;
      }
	  $this->pcNomMaq=strtoupper($this->pcNomMaq);
	  $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
	  if ($this->plNuevo) {
         $RS = $loSql->omExec('SELECT MAX(cCodMaq) FROM E01MMAQ');
         $laFila = $loSql->fetch($RS);
         if ($laFila[0] == null) {
            $lcCodMaq = '000001';
         } else {
            $lcCodMaq = $laFila[0];
            $lnCodigo = (int)$lcCodMaq + 1;
            $lcCodMaq = (string)$lnCodigo;
            $lcCodMaq = right('00000'.trim($lcCodMaq), 6);
         }
         $lcSql = "INSERT INTO E01MMAQ (cCodMaq, cDescri, cEstado,cTipo,cFlag, cCodUsu, tmodifi) 
		 VALUES ('$lcCodMaq', '$this->pcNomMaq','$this->pcEstado','$this->pcTipo','L','$this->pcCodUsu',SYSDATE())";//L Libre
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL GRABAR MAQUINA</ERROR></DATA>';
            return false;
         }
      } else {
         $lcCodMaq = $this->pcCodMaq;
         $lcSql = "UPDATE E01MMAQ SET cDescri='$this->pcNomMaq',cEstado='$this->pcEstado',cTipo='$this->pcTipo', cCodUsu='$this->pcCodUsu',tmodifi= SYSDATE() WHERE CCODMaq='$this->pcCodMaq'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR MAQUINA</ERROR></DATA>';
            return false;
         }
      }
	  $loSql->omDisconnect();
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