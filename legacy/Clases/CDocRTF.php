<?php
require_once 'Clases/CBase.php';

// Reportes de incidentes
class CDocRTF extends CBase {
   public $pcNroInc, $pdFecInc, $pmDescri, $pcFilRet,$pcCodUsu;
   protected $lnError;

   public function __construct() {
      parent::__construct();
   }
   // Reporte: informe de investigacion de incidentes111
    public function omPedido() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
	  $lcSql="SELECT MAX(cCodPed) FROM E01CPED";
	  $RS = $loSql->omExec($lcSql);
	  $laFila = $loSql->fetch($RS);
      $this->pcCodPed=$laFila[0];
      if (empty($this->pcCodPed)) {
	     $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NUMERO DE PEDIDO NO EXISTE</ERROR></DATA>';
         return false;
      }
	  $lcSql="SELECT A.CCODPED,A.NTOTAL,A.CCODCLI,A.DFECLIS,B.CNOMBRE,B.CNRODNI,B.CNRORUC,B.CDIREC,B.CTELEF FROM E01CPED A INNER JOIN E01MCLI B ON A.CCODCLI=B.CCODCLI WHERE A.CCODPED='$this->pcCodPed'";
	  $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
	  $lcNombre = $laFila[4];
	  $lcDirec = $laFila[7];
	  $lcTelef = $laFila[8];
	  if($laFila[5]='99999999')
	     $lcNumero = $laFila[6];
      elseif($laFila[6]='99999999999')
	     $lcNumero = $laFila[5];
	  $lcSql="SELECT A.cCodProd,A.nCant,B.cDescri FROM E01DPED A INNER JOIN E01MPROD B ON A.CCODPROD=B.CCODPROD WHERE A.CCODPED='$this->pcCodPed'";
	  $RS = $loSql->omExec($lcSql);
	  $laProd = array(array('\b DESCRIPCION DE PRODUCTO'), array('\b PRECIO C/U'),array('\b CANTIDAD'),array('\b TOTAL'));
	  $Total=0;
	  while ($laFila = $loSql->fetch($RS)) {
	     array_push($laProd[0],'\\qc '.$laFila[2]);
		 $RS1 = $loSql->omExec("SELECT CCODPROD,CDESPROD,NPRECIO FROM V_E01PPROD WHERE cCodProd='$laFila[0]'");
         $lnCostoTotal=0;
		 while ($laFila1 = $loSql->fetch($RS1)) {
		       $lnCostoTotal = $lnCostoTotal+$laFila1[2];
		 }
		 array_push($laProd[1],'\\qc '.$lnCostoTotal);//**
		 array_push($laProd[2],'\\qc '.$laFila[1]);
		 array_push($laProd[3],'\\qc '.$lnCostoTotal*$laFila[1]);
		 $Total=$Total+$lnCostoTotal*$laFila[1];
	  }
	  $VV=round($Total/(1.18),2);
	  $IGV=round(($Total-$VV),2);
      $loSql->omDisconnect();
     // Construir Informe en RTF
      $laArray = array('#*CODPED*#'=>$this->pcCodPed,'#*CNOMBRE*#'=>$lcNombre, '#*CDIREC*#'=>$lcDirec, '#*CNUMERO*#'=>$lcNumero,
                      '#*CTELEF*#'=>$lcTelef, '#*VV*#'=>$VV, '#*IGV*#'=>$IGV,
                      '#*TOTAL*#'=>$Total);
      $laDim=array(3500,1900,1500,1500);
      $laAArray = array('#*TABLA*#'=>array(fxCount($laProd), fxCount($laProd[0]), $laProd,$laDim),
                        );
      $lo = new CRtf();
      $lo->pcFile = 'PEDIDO';
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omInicializar();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR 1</ERROR></DATA>';
         return false;
      }
      $lo->paArray = $laArray;
      $llOk = $lo->omGenerar();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR 2</ERROR></DATA>';
         return false;
      }
      $lo->paAArray = $laAArray;
      $llOk = $lo->omGenerarArray(true);
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR 3</ERROR></DATA>';
         return false;
      }
      $this->pcFilRet = $lo->pcFilRet;
	  //echo "<a href = '$lo->pcFilRet'>Archivo de Salida</a>";
      return true;
   }
   
   
      // Reporte: informe de investigacion de incidentes111
    public function omPedido1() {
	   if (empty($this->pcCodPed)) {
	     $this->pcError = '<DATA><ERROR>NUMERO DE PEDIDO NO EXISTE</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }	  
	  $lcSql="SELECT A.CCODPED,A.NTOTAL,A.CCODCLI,A.DFECLIS,B.CNOMBRE,B.CNRODNI,B.CNRORUC,B.CDIREC,B.CTELEF FROM E01CPED A INNER JOIN E01MCLI B ON A.CCODCLI=B.CCODCLI WHERE A.CCODPED='$this->pcCodPed'";
	  $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
	  $lcNombre = $laFila[4];
	  $lcDirec = $laFila[7];
	  $lcTelef = $laFila[8];
	  if($laFila[5]='99999999')
	     $lcNumero = $laFila[6];
      elseif($laFila[6]='99999999999')
	     $lcNumero = $laFila[5];
	  $lcSql="SELECT A.cCodProd,A.nCant,B.cDescri FROM E01DPED A INNER JOIN E01MPROD B ON A.CCODPROD=B.CCODPROD WHERE A.CCODPED='$this->pcCodPed'";
	  $RS = $loSql->omExec($lcSql);
	  $laProd = array(array('\b DESCRIPCION DE PRODUCTO'), array('\b PRECIO C/U'),array('\b CANTIDAD'),array('\b TOTAL'));
	  $Total=0;
	  while ($laFila = $loSql->fetch($RS)) {
	     array_push($laProd[0],'\\qc '.$laFila[2]);
		 $RS1 = $loSql->omExec("SELECT CCODPROD,CDESPROD,NPRECIO FROM V_E01PPROD WHERE cCodProd='$laFila[0]'");
         $lnCostoTotal=0;
		 while ($laFila1 = $loSql->fetch($RS1)) {
		       $lnCostoTotal = $lnCostoTotal+$laFila1[2];
		 }
		 array_push($laProd[1],'\\qc '.$lnCostoTotal);//**
		 array_push($laProd[2],'\\qc '.$laFila[1]);
		 array_push($laProd[3],'\\qc '.$lnCostoTotal*$laFila[1]);
		 $Total=$Total+$lnCostoTotal*$laFila[1];
	  }
	  $VV=round($Total/(1.18),2);
	  $IGV=round(($Total-$VV),2);
      $loSql->omDisconnect();
     // Construir Informe en RTF
      $laArray = array('#*CODPED*#'=>$this->pcCodPed,'#*CNOMBRE*#'=>$lcNombre, '#*CDIREC*#'=>$lcDirec, '#*CNUMERO*#'=>$lcNumero,
                      '#*CTELEF*#'=>$lcTelef, '#*VV*#'=>$VV, '#*IGV*#'=>$IGV,
                      '#*TOTAL*#'=>$Total);
      $laDim=array(3500,1900,1500,1500);
      $laAArray = array('#*TABLA*#'=>array(fxCount($laProd), fxCount($laProd[0]), $laProd,$laDim),
                        );
      $lo = new CRtf();
      $lo->pcFile = 'PEDIDO';
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omInicializar();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR 1</ERROR></DATA>';
         return false;
      }
      $lo->paArray = $laArray;
      $llOk = $lo->omGenerar();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR 2</ERROR></DATA>';
         return false;
      }
      $lo->paAArray = $laAArray;
      $llOk = $lo->omGenerarArray(true);
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR 3</ERROR></DATA>';
         return false;
      }
      $this->pcFilRet = $lo->pcFilRet;
	  //echo "<a href = '$lo->pcFilRet'>Archivo de Salida</a>";
      return true;
   }
}
?>