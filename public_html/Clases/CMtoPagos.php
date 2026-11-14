<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Mantenimiento de ALmacenes
//-------------------------------------
class CMtoPagos extends CBase {
   public $paEstado, $pcEstado, $pcCodUsu, $pcCodPer, $pcCodSan, $pcPerEmp, $paCodPer, $paCodSan, $paCodNor, $paPerEmp,
          $pdFecIni, $pmObserv, $pdFecha, $pdCese, $pcNombre, $pcDirecc, $pcSexo, $paSexo, $paTipo, $pcTipo, $pmDatos,
          $paIndex, $paData, $paNombre, $paCodEmp, $plNuevo, $paEstCiv, $paCargo, $paCargos, $pcNroDni, $pcSector,
          $paSector, $pcDesSec, $paCarDis, $paAreas, $paCodPry, $pcNomAlm,$pcDirec;
   
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
   
   // Inicializa GRILLA DE FACTURAS NO PAGADAS
   public function omInitCobrar() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
	  $lcSql="SELECT A.cCodCom,A.cCodPrv,B.cNombre,A.dFecLis FROM E01CCOM A INNER JOIN E01MPRV B ON A.CCODPRV = B.CCODPRV AND A.CESTADO='A'";
	  $RS = $loSql->omExec($lcSql);
	  $i = 0;
	  while ($laFila = $loSql->fetch($RS)) {
	     $lnMonto=0;
	     $lcSql="SELECT A.CCODINS,A.NCANT, B.NPRECIO FROM E02DCOM A INNER JOIN E01MINS B ON A.CCODINS=B.CCODINS";
	     $RS1 = $loSql->omExec($lcSql);
         while ($laFila1 = $loSql->fetch($RS1)) {
		       $lnMonto=$lnMonto+($laFila1[1]*$laFila1[2]);
         }
		 $this->paDataFac[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3], $lnMonto);
		 $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY FACTURAS QUE PAGAR</ERROR></DATA>';
         return false;
      }
	  $loSql->omDisconnect();
	  return true;
   }
   public function omInitCobrar1() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
	  $lcSql="SELECT A.cCodCom,A.cCodPrv,B.cNombre,A.dFecLis FROM E01CCOM A INNER JOIN E01MPRV B ON A.CCODPRV = B.CCODPRV AND A.CESTADO='I'";
	  $RS = $loSql->omExec($lcSql);
	  $i = 0;
	  while ($laFila = $loSql->fetch($RS)) {
	     $lnMonto=0;
	     $lcSql="SELECT A.CCODINS,A.NCANT, B.NPRECIO FROM E02DCOM A INNER JOIN E01MINS B ON A.CCODINS=B.CCODINS";
	     $RS1 = $loSql->omExec($lcSql);
         while ($laFila1 = $loSql->fetch($RS1)) {
		       $lnMonto=$lnMonto+($laFila1[1]*$laFila1[2]);
         }
		 $this->paDataFac[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3], $lnMonto);
		 $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY FACTURAS QUE PAGAR</ERROR></DATA>';
         return false;
      }
	  $loSql->omDisconnect();
	  return true;
   }
   
  public function omDetalleFactura() {
      if(empty($this->pcCodFac)){
	     $this->pcError = '<DATA><ERROR>NO SELECIONO FACTURA</ERROR></DATA>';
         return false;
	  }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
	  
	  $lcSql="SELECT cCodPrv FROM E01CCOM WHERE CCODCOM='$this->pcCodFac'";
	  $RS = $loSql->omExec($lcSql);
	  $laFila = $loSql->fetch($RS);
	  $lcCodPrv=$laFila[0];
	  $lcSql="SELECT A.cNombre,A.cNroDni,A.cTipPer,B.cDescri AS CTIPO,A.cNroRUC,A.cDirecc FROM E01MPRV A LEFT OUTER JOIN V_E01TTAB B ON B.CCODIGO=A.CTIPPER AND B.CCODTAB='098' WHERE A.CCODPRV='$lcCodPrv'";
	  $RS = $loSql->omExec($lcSql);
	  $laFila = $loSql->fetch($RS);
	  $this->pcNombre=$laFila[0];
	  $this->pcTipo=$laFila[2];
	  $this->pcTipPer=$laFila[3];
	  $this->pcNroDNI=$laFila[1];
	  $this->pcNroRUC=$laFila[4];
	  $this->pcDirecc=$laFila[5];
	  
	  $lcSql="SELECT A.CCODINS, B.CDESCRI, B.CUNIMED, C.CDESCRI AS CDESUNI, B.NPRECIO, A.NCANT
FROM E02DCOM A
INNER JOIN E01MINS B ON A.CCODINS = B.CCODINS
LEFT OUTER JOIN V_E01TTAB C ON C.CCODIGO = B.CUNIMED AND C.CCODTAB='007' WHERE A.CCODCOM='$this->pcCodFac'";
	  $RS = $loSql->omExec($lcSql);
      $total=0;
	  while ($laFila = $loSql->fetch($RS)) {
	        $this->paData[]=array($laFila[0],$laFila[1],$laFila[3],$laFila[4],$laFila[5],$laFila[4]*$laFila[5]);
			$total=$total+$laFila[4]*$laFila[5];
	  }
	  $_SESSION['pcVV']=round($lnTotal/(1.18),2);
	  $_SESSION['pcIGV']=round(($lnTotal-$_SESSION['pcVV']),2);	  
	  $this->Total=$total;
	  $this->VV=round($total/(1.18),2);
	  $this->IGV=round($total-($this->VV),2);
	  $loSql->omDisconnect();
	  return true;
   }
   
     public function omDetalleFactura1() {
      if(empty($this->pcCodFac)){
	     $this->pcError = '<DATA><ERROR>NO SELECIONO FACTURA</ERROR></DATA>';
         return false;
	  }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
	  
	  $lcSql="SELECT cCodPrv,dFecLis FROM E01CCOM WHERE CCODCOM='$this->pcCodFac'";
	  $RS = $loSql->omExec($lcSql);
	  $laFila = $loSql->fetch($RS);
	  $lcCodPrv=$laFila[0];
	  $this->pdFecLis=$laFila[1];
	  $lcSql="SELECT A.cNombre,A.cNroDni,A.cTipPer,B.cDescri AS CTIPO,A.cNroRUC,A.cDirecc FROM E01MPRV A LEFT OUTER JOIN V_E01TTAB B ON B.CCODIGO=A.CTIPPER AND B.CCODTAB='098' WHERE A.CCODPRV='$lcCodPrv'";
	  $RS = $loSql->omExec($lcSql);
	  $laFila = $loSql->fetch($RS);
	  $this->pcNombre=$laFila[0];
	  $this->pcTipo=$laFila[2];
	  $this->pcTipPer=$laFila[3];
	  $this->pcNroDNI=$laFila[1];
	  $this->pcNroRUC=$laFila[4];
	  $this->pcDirecc=$laFila[5];
	  
	  $lcSql="SELECT A.CCODINS, B.CDESCRI, B.CUNIMED, C.CDESCRI AS CDESUNI, B.NPRECIO, A.NCANT
FROM E02DCOM A
INNER JOIN E01MINS B ON A.CCODINS = B.CCODINS
LEFT OUTER JOIN V_E01TTAB C ON C.CCODIGO = B.CUNIMED AND C.CCODTAB='007' WHERE A.CCODCOM='$this->pcCodFac'";
	  $RS = $loSql->omExec($lcSql);
      $total=0;
	  while ($laFila = $loSql->fetch($RS)) {
	        $this->paData[]=array($laFila[0],$laFila[1],$laFila[3],$laFila[4],$laFila[5],$laFila[4]*$laFila[5]);
			$total=$total+$laFila[4]*$laFila[5];
	  }
	  $_SESSION['pcVV']=round($lnTotal/(1.18),2);
	  $_SESSION['pcIGV']=round(($lnTotal-$_SESSION['pcVV']),2);	  
	  $this->Total=$total;
	  $this->VV=round($total/(1.18),2);
	  $this->IGV=round($total-($this->VV),2);
	  $loSql->omDisconnect();
	  return true;
   }
   
   public function omInitCuentas() {
      if(empty($this->pcCodFac)){
	     $this->pcError = '<DATA><ERROR>NO SELECIONO FACTURA</ERROR></DATA>';
         return false;
	  }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
	  $lcSql="SELECT cCodPrv FROM E01CCOM WHERE CCODCOM='$this->pcCodFac'";
	  $RS = $loSql->omExec($lcSql);
	  $laFila = $loSql->fetch($RS);
	  $lcCodPrv=$laFila[0];
	  $lcSql="SELECT cNombre FROM E01MPRV WHERE CCODPRV='$lcCodPrv'";
	  $RS = $loSql->omExec($lcSql);
	  $laFila = $loSql->fetch($RS);
	  $this->pcNombre=$laFila[0];
// cargar cuentas de provedor	  
      $lcSql="SELECT CCODPRV,NCUENTA FROM E01PCTA WHERE CCODPRV='$lcCodPrv'";
 	  $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCtaPrv[] = array($laFila[0],$laFila[1]);
         $i++;
      }
	  if($i==0){
	     $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>EL PROVEEDOR NO TIENE CUENTAS DESIGNADAS</ERROR></DATA>';
         return false;
	 }
	 // carga bancos
	 $lcSql="SELECT CCODBAN,CNOMBAN FROM E01MBAN";
	  $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodBan[] = array($laFila[0],$laFila[1]);
         $i++;
      }
	  if($i==0){
	     $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY BANCOS REGISTRADOS</ERROR></DATA>';
         return false;
	  }
	  $this->paCtaBan[] = array('999999','**************');
	  $loSql->omDisconnect();
	  return true;
   }
   
   
   public function omCargarCtaBan() {
      if(empty($this->pcCodBan)){
	     $this->pcError = '<DATA><ERROR>BANCO NO SELECCIONADO</ERROR></DATA>';
         return false;
	  }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
 	  $RS = $loSql->omExec("SELECT CCODBAN,NCUENTA FROM E01PCTA WHERE CCODBAN='$this->pcCodBan'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCtaBan[] = array($laFila[1],$laFila[1]);
         $i++;
      }
	  if($i==0){
	     $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>EL BANCO NO TIENE CUENTAS DESIGNADAS</ERROR></DATA>';
         return false;
	 }
	  $loSql->omDisconnect();
	  return true;
   }
   
   public function omGrabarPago() {
      if(empty($this->pcCodBan)){
	     $this->pcError = '<DATA><ERROR>BANCO NO SELECCIONADO</ERROR></DATA>';
         return false;
	  }elseif(empty($this->pcCodFac)){
	     $this->pcError = '<DATA><ERROR>FACTURA NO SELECCIONADO</ERROR></DATA>';
         return false;
	  }elseif(empty($this->pcCtaBan)){
	     $this->pcError = '<DATA><ERROR>CUENTA DE BANCO NO SELECCIONADO</ERROR></DATA>';
         return false;
	  }elseif(($this->pcCtaBan)=='999999'){
	     $this->pcError = '<DATA><ERROR>CUENTA DE BANCO NO SELECCIONADO</ERROR></DATA>';
         return false;
	  }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
 	  $llOk = $loSql->omExec("UPDATE E01CCOM SET CESTADO='I',cCTAPAG='$this->pcCtaBan',CCODBAN='$this->pcCodBan',dFecLis=SYSDATE() WHERE CCODCOM = '$this->pcCodFac'");
      if(!$llOk){
	     $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR EN GRABACION</ERROR></DATA>';
         return false;
	 }
	  $loSql->omDisconnect();
	  return true;
   }

}
?>