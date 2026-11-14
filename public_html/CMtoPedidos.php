<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Mantenimiento de ALmacenes
//-------------------------------------
class CMtoPedidos extends CBase {
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
   
   // Inicializa historia de personal
   public function omInit() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Almacenes
      $RS = $loSql->omExec("SELECT cCodAlm,cNomAlm FROM E01MALM");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodAlm[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->paCodAlm[] = array('9999', 'CREAR NUEVO ALMACEN');
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
	  // Carga tipos d eAlmacen
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '003'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paTipo[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE ALMACEN DEFINIDOS . TABLA [003]</ERROR></DATA>';
         return false;
      }

      return true;
   }

   // Inicializa historia de personal
   public function omInitPedidos() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga todos los tipos
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '098'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paTipo[] = array($laFila[0], $laFila[1]);
         $i++;
      }	  
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TIPO DE PERSONA TABLA[098]</ERROR></DATA>';
         return false;
      }
	  //carga NroDNI
      $RS = $loSql->omExec("SELECT cCodCli,cNRoDni FROM E01MCLI WHERE cTipo='A'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paNroDNI[] = array($laFila[0], $laFila[1]);
         $i++;
      }	  
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAS NATURALES EN CLIENTES</ERROR></DATA>';
         return false;
      }
      //carga NroRUC
      $RS = $loSql->omExec("SELECT cCodCli,cNRoDni FROM E01MCLI WHERE cTipo!='A'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paNroRUC[] = array($laFila[0], $laFila[1]);
         $i++;
      }	  
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAS JURIDICAS EN CLIENTES</ERROR></DATA>';
         return false;
      }
      //cargar prioridades
	  $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '002'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paPriori[] = array($laFila[0], $laFila[1]);
         $i++;
      }	  
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PRIORIDADES EN TABLA[002]</ERROR></DATA>';
         return false;
      }
	  //cargar productos
      $RS = $loSql->omExec("SELECT CCODPROD,CDESCRI  FROM E01MPROD WHERE cTipo = 'A' ");//EEMM
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodProd[] = array($laFila[0], $laFila[1]);
         $i++;
      }	  
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>PRODUCTOS REGISTRADOS</ERROR></DATA>';
         return false;
      }	  
      $loSql->omDisconnect();
      return true;
   }

   
   public function omGrabar(){
      if (empty($this->pcNomAlm)) {
         $this->pcError = '<DATA><ERROR>NOMBRE DE ALMACEN VACIO</ERROR></DATA>';
         return false;
      }elseif(empty($this->pcDirec)){
	     $this->pcError = '<DATA><ERROR>DIRECCION DE ALMACEN VACIO</ERROR></DATA>';
         return false;
      }
	  $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
	  if ($this->plNuevo) {
         $RS = $loSql->omExec('SELECT MAX(cCodAlm) FROM E01MALM');
         $laFila = $loSql->fetch($RS);
         if ($laFila[0] == null) {
            $lcCodAlm = '000001';
         } else {
            $lcCodAlm = $laFila[0];
            $lnCodigo = (int)$lcCodAlm + 1;
            $lcCodAlm = (string)$lnCodigo;
            $lcCodAlm = right('00000'.trim($lcCodAlm), 6);
         }
         $lcSql = "INSERT INTO E01MALM (cCodAlm, cNomAlm, cDirec, cEstado,cTipo, cCodUsu, tmodifi) 
		 VALUES ('$lcCodAlm', '$this->pcNomAlm', '$this->pcDirec','$this->pcEstado','$this->pcTipo','$this->pcCodUsu',SYSDATE())";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL GRABAR ALMACEN</ERROR></DATA>';
            return false;
         }
      } else {
         $lcCodAlm = $this->pcCodAlm;
         $lcSql = "UPDATE E01MALM SET cNomAlm='$this->pcNomAlm',cDirec='$this->pcDirec',cEstado='$this->pcEstado',cTipo='$this->pcTipo', cCodUsu='$this->pcCodUsu',tmodifi= SYSDATE() WHERE CCODALM='$this->pcCodAlm'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR ALMACEN</ERROR></DATA>';
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
      // Carga Almacenes
      $RS = $loSql->omExec("SELECT cCodAlm,cNomAlm,cDirec,cEstado,cTipo FROM E01MALM WHERE CCODALM ='$this->pcCodAlm'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>NO HAY ALMACENES REGISTRADOS</ERROR></DATA>';
            return false;
      }else{
	  $this->pcNomAlm=$laFila[1];
	  $this->pcDirec=$laFila[2];
	  $this->pcEstado=$laFila[3];
	  $this->pcTipo=$laFila[4];
	  }
	  $loSql->omDisconnect();
      return true;
   }
   
   public function omAplicar(){
      if (empty($this->pcCodAlm)) {
         $this->pcError = '<DATA><ERROR>NO SELECCIONO ALMACEN</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Almacenes 
      $RS = $loSql->omExec("SELECT cCodAlm,cNomAlm,cDirec,cEstado,cTipo FROM E01MALM WHERE CCODALM ='$this->pcCodAlm'");
      $laFila = $loSql->fetch($RS);
	  $this->pcNomAlm=$laFila[1];
	  //Carga Insumos del Almacen
	  $RS = $loSql->omExec("SELECT CCODINS,CDESCRI,CDESUNI,NPRECIO,NCANT FROM V_E01PALM WHERE CCODALM ='$this->pcCodAlm'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paDataID[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3], $laFila[4]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY INSUMOS EN ALMACEN</ERROR></DATA>';
         return false;
      }

	  $loSql->omDisconnect();
      return true;
   }
   
   public function omProducto(){
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }

     //cargar producto con su total
	  $RS = $loSql->omExec("SELECT CCODPROD,CDESPROD,NPRECIO FROM V_E01PPROD WHERE cCodProd='$this->pcCodProd'");
      $i = 0;
	  $lnCostoTotal=0;
	  $this->paDataP=null;
      while ($laFila = $loSql->fetch($RS)) {
         $lnCostoTotal = $lnCostoTotal+$laFila[2];
		 $lcCodProd = $laFila[0];
		 $lcDesProd = $laFila[1];
         $i++;
      }
	  $this->paDataP[]=array($lcCodProd,$lcDesProd,$lnCostoTotal);
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY INSUMOS EN EL PRODUCTO</ERROR></DATA>';
         return false;
      }
	  $loSql->omDisconnect();
	  return true;
   }
   
   public function omCargaCliente(){
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }

     //cargar producto con su total
	  $RS = $loSql->omExec("SELECT CNOMBRE FROM E01MCLI WHERE cCodCli='$this->pcCodCli'");
      $laFila = $loSql->fetch($RS);
	  $this->pcNombre=$laFila[0];
	  $loSql->omDisconnect();
	  return true;
   }
   
   public function omGrabarTabla() {
      if (empty($this->paDataF) or count($this->paDataF) == 0) {
         $this->pcError = '<DATA><ERROR>DETALLE DE PRODUCTOS NO DEFINIDA</ERROR></DATA>';
         return false;
      }elseif (empty($this->pcCodCli)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE CLIENTE VACIO</ERROR></DATA>';
         return false;
      }elseif (empty($this->pcTotal)) {
         $this->pcError = '<DATA><ERROR>ERROR EN GRABACION</ERROR></DATA>';
         return false;
      }elseif (empty($this->pdFecLis)) {
         $this->pcError = '<DATA><ERROR>FECHA VACIA</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
	  //GRABA CABECERA
	  $RS = $loSql->omExec("SELECT MAX(cCodPed) FROM E01CPED WHERE SUBSTR(cCodPed, 1, 1) != '9'");
      $laFila = $loSql->fetch($RS);
      $lcCodPed = '000000';
      if (!empty($laFila[0]))
          $lcCodPed = $laFila[0];
      $lnCodPed = (int)$lcCodPed + 1;
      $lcCodPed = (string)$lnCodPed;
      $lcCodPed = right('00000'.trim($lcCodPed), 6);
	  $lcSql = "INSERT INTO E01CPED (cCodPed, cCodCli,nTotal,dFecLis,cPriori,cEstadoPL,cEstadoPR,cCodUsu, tModifi) VALUES 
                 ('$lcCodPed', '$this->pcCodCli','$this->pcTotal','$this->pdFecLis','$this->pcPriori','I','P','$this->pcCodUsu', SYSDATE())";
      $llOk = $loSql->omExec($lcSql);
	  if (!$llOk) {
	    	       $loSql->omDisconnect();
                   $this->pcError = '<DATA><ERROR>ERROR EN GRABACION 0</ERROR></DATA>';
			       return false;
                }
	  //GRABA DETALLE
    foreach ($this->paDataF as $i) {
	  $lcSql = "INSERT INTO E01DPED (cCodPed, cCodProd,nCant,cEstadoPL,cEstadoPR,cPROCESO,cCodUsu, tModifi) VALUES 
                 ('$lcCodPed', '$i[0]','$i[3]','I','P','I','$this->pcCodUsu', SYSDATE())";
      $llOk = $loSql->omExec($lcSql);
	  if (!$llOk) {
	    	       $loSql->omDisconnect();
                   $this->pcError = '<DATA><ERROR>ERROR EN GRABACION 1</ERROR></DATA>';
			       return false;
                }
	       
	  }
      $loSql->omDisconnect();
      return true;
   }
   
   public function omInitPedidosRep(){
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Pedidos
	  $lcSql="SELECT cCodPed,cCodCli,cNombre,cDesPL,cDESPRIO,DFECLIS FROM V_E01CPED";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paDataPed[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3], $laFila[4],$laFila[5]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PEDIDOS</ERROR></DATA>';
         return false;
      }
	  $loSql->omDisconnect();
	  return true;
   }

   public function omInitPedidosRepProd(){
      if (empty($this->pcCodPed)) {
         $this->pcError = '<DATA><ERROR>NO SELECCIONO PEDIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga detalle de la cabecera
	  $lcSql="SELECT cCodPed,cCodProd,cDescri,nCant,cDesPL FROM V_E01DPED WHERE CCODPED='$this->pcCodPed'";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paDataProd[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3], $laFila[4]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PRODUCTOS EN EL PEDIDO</ERROR></DATA>';
         return false;
      }
	  $loSql->omDisconnect();
	  return true;
   }  
   
   public function omInitPedidosAsignar(){
      if (empty($this->pcCodPed)) {
         $this->pcError = '<DATA><ERROR>NO SELECCIONO PEDIDO</ERROR></DATA>';
         return false;
      }elseif (empty($this->pcCodProd)) {
         $this->pcError = '<DATA><ERROR>NO SELECCIONO PRODUCTO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
	  /*
       $loSmarty->assign('scDescri', $lo->pcDescri);
       $loSmarty->assign('snCant', $lo->pnCant);
       $loSmarty->assign('saDataM', $lo->paDataM);
       $loSmarty->assign('saDataP', $lo->paDataP);

	  */
	  //Carga datos del producto 
	  $RS = $loSql->omExec("SELECT cDescri,nCant FROM V_E01DPED WHERE CCODPED='$this->pcCodPed' AND CCODPROD='$this->pcCodProd'");
      $laFila = $loSql->fetch($RS);
	  $this->pcDescri=$laFila[0];
	  $this->pnCant=$laFila[1];
      // Carga MAQUINAS libres
	  $lcSql = "SELECT CCODMAQ,CDESCRI FROM E01MMAQ WHERE CESTADOOP='L'";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodMaq[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY MAQUINAS LIBRES</ERROR></DATA>';
         return false;
      }
	  // Carga PERSONAL libres
	  $lcSql = "SELECT CCODPER,CNOMBRE FROM E01MPER WHERE CESTADOOP='L' AND CCODPER!='999999'";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL LIBRES</ERROR></DATA>';
         return false;
      }
	  $loSql->omDisconnect();
	  return true;
   }
    public function omAgregarM() {
      if (empty($this->pcCodMaq)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE MAQUINA VACIO</ERROR></DATA>';
          return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Maquinas
	  $lcSql="SELECT A.cCodMaq,A.cDescri,B.CDESCRI AS CTIPO FROM E01MMAQ A LEFT OUTER JOIN V_E01TTAB B ON B.CCODIGO=A.CTIPO AND CCODTAB='005' WHERE cCodMaq = '$this->pcCodMaq'";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paDataMaq[] = array($laFila[0], $laFila[1], $laFila[2]);
      }
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY MAQUINA DEFINIDOS</ERROR></DATA>';
         return false;
      }
	  $loSql->omDisconnect();
      return true;
   }

   public function omAgregarP() {
      if (empty($this->pcCodPer)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL VACIO</ERROR></DATA>';
          return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga PErsonal
	  $lcSql="SELECT cCodPer,cNombre FROM E01MPER WHERE cCodPer = '$this->pcCodPer'";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paDataPer[] = array($laFila[0], $laFila[1]);
      }
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL DEFINIDOS</ERROR></DATA>';
         return false;
      }
	  $loSql->omDisconnect();
      return true;
   }
  public function omGrabarAsignado(){
      if (empty($this->pcCodPed)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL VACIO</ERROR></DATA>';
          return false;
      }elseif (empty($this->pcCodProd)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL VACIO</ERROR></DATA>';
          return false;
      }elseif (empty($this->paDataM) or count($this->paDataM) == 0) {
         $this->pcError = '<DATA><ERROR>DETALLE DE MAQUINARIA NO DEFINIDA</ERROR></DATA>';
         return false;
      }elseif (empty($this->paDataP) or count($this->paDataP) == 0) {
         $this->pcError = '<DATA><ERROR>DETALLE DE PERSONAL NO DEFINIDA</ERROR></DATA>';
         return false;
      }
	  $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
	  //datos q son guardados en esta tabla son para produccion y los maestros se actualizan
	  
	  foreach ($this->paDataM as $i) {
         $lcSql = "INSERT INTO E01MAQ (cCodMaq, cCodPed,cCodProd,cEstadoOp,cCodUsu, tModifi) VALUES 
                 ('$i[0]', '$this->pcCodPed', '$this->pcCodProd','L', '$this->pcCodUsu', SYSDATE())";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
     		$loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL GRABAR EN E01MAQ</ERROR></DATA>';
			return false;
         }
		 //actualiza maestro a ocupado libre ocupado
		 $lcSql = "UPDATE E01MMAQ SET CESTADOOP='O' WHERE CCODMAQ='$i[0]'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
     		$loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL CAMBIAR ESTADO EN E01MMAQ</ERROR></DATA>';
			return false;
         }
      }
	  foreach ($this->paDataP as $j) {
         $lcSql = "INSERT INTO E01PER (cCodPer, cCodPed,cCodProd,cEstadoOp,cCodUsu, tModifi) VALUES 
                 ('$j[0]', '$this->pcCodPed', '$this->pcCodProd','L', '$this->pcCodUsu', SYSDATE())";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
     		$loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL GRABAR EN E01PER</ERROR></DATA>';
			return false;
         }
		 //actualiza maestro a ocupado libre ocupado
		 $lcSql = "UPDATE E01MPER SET CESTADOOP='O' WHERE CCODPER='$j[0]'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
     		$loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL CAMBIAR ESTADO EN E01MPER</ERROR></DATA>';
			return false;
         }
      }
	  //COMO ya se asignaron trabajadores para ese producto el estado del E01DPED se actualiza para ese producto
	  //actualiza maestro a ocupado libre ocupado
		 $lcSql = "UPDATE E01DPED SET CESTADOPL='C' WHERE CCODPED='$this->pcCodPed' AND CCODPROD='$this->pcCodProd'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
     		$loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL CAMBIAR ESTADO EN E01DPED</ERROR></DATA>';
			return false;
         }
	  $loSql->omDisconnect();
	  return true;
  }
}
?>