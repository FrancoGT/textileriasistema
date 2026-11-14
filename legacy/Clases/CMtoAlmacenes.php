<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Mantenimiento de ALmacenes
//-------------------------------------
class CMtoAlmacenes extends CBase {
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
   public function omInitAsignar() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Almacenes para la grid
      $RS = $loSql->omExec("SELECT cCodAlm,cNomAlm,cDirec FROM E01MALM WHERE CESTADO='A' AND CTIPO='I'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paDataA[] = array($laFila[0], $laFila[1], $laFila[2]);
         $i++;
      }	  
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ALMACENES DEFINIDOS.</ERROR></DATA>';
         return false;
      }
	  // Carga Almacenes Y UN PROVEEDOR para el combo
	  $this->paCodAlm[] = array('9999', 'PROVEEDOR');
      $RS = $loSql->omExec("SELECT cCodAlm,cNomAlm FROM E01MALM WHERE CESTADO='A' AND CTIPO='I'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodAlm[] = array($laFila[0], $laFila[1]);
         $i++;
      }	  
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ALMACENES DEFINIDOS.</ERROR></DATA>';
         return false;
      }
      //Carga Insumos
	  $RS = $loSql->omExec("SELECT CCODINS,CDESCRI FROM E01MINS");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodIns[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY INSUMOS DEFINIDOS. TABLA [001]</ERROR></DATA>';
         return false;
      }

	  // Carga tipos de Almacen
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
	  $this->pcNomAlm=strtoupper($this->pcNomAlm);
	  $this->pcDirec=strtoupper($this->pcDirec);
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
   public function omGrabarTabla() {
      if (empty($this->pcDestino)) {
         $this->pcError = '<DATA><ERROR>NO HAY DESTINO ASIGNADO</ERROR></DATA>';
         return false;
      }elseif (empty($this->pcOrigen)) {
         $this->pcError = '<DATA><ERROR>NO HAY ORIGEN ASIGNADO</ERROR></DATA>';
         return false;
      }elseif (empty($this->paDataI) or fxCount($this->paDataI) == 0) {
         $this->pcError = '<DATA><ERROR>DETALLE DE INSUMOS NO DEFINIDA</ERROR></DATA>';
         return false;
      }elseif ($this->pcOrigen==$this->pcDestino) {
         $this->pcError = '<DATA><ERROR>NO SE PUEDE MOVER INSUMOS AL MISMO ALMACEN</ERROR></DATA>';
         return false;
      }elseif ($this->pcDestino == '9999') {
         $this->pcError = '<DATA><ERROR>NO SE PUEDE MOVER INSUMOS AL PROOVEDOR</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
	  //PROVEEDOR A UN ALMACEN -> VA A LOGISTICA Y LOGISTICA ARA LA FACTURA PARA PASAR A CUENTAS X COBRAR
	  if($this->pcOrigen=='9999'){
	  //CREA EL PUENTE DE INSUMOS PENDIENTES 
	  
	  
	  
	     foreach ($this->paDataI as $i) {
		     $lcSql="SELECT * FROM E01PALM WHERE CCODALM='$this->pcDestino' AND CCODINS ='$i[0]'";
		     $RS = $loSql->omExec($lcSql);
             $laFila = $loSql->fetch($RS);
			 //no existe ese insumo en ese almacen
	         if(empty($laFila[0])){
			    $lcSql = "INSERT INTO E01PALM (cCodAlm, cCodIns,nCanT,cCodUsu, tModifi) VALUES 
                 ('$this->pcDestino', '$i[0]','$i[4]', '$this->pcCodUsu', SYSDATE())";
                $llOk = $loSql->omExec($lcSql);
				if (!$llOk) {
	    	       $loSql->omDisconnect();
                   $this->pcError = '<DATA><ERROR>ERROR EN GRABACION 1</ERROR></DATA>';
			       return false;
                }
			 }else{
			   $RS = $loSql->omExec("SELECT NCANT FROM E01PALM WHERE CCODALM='$this->pcDestino' AND CCODINS='$i[0]'");
               $laFila = $loSql->fetch($RS);
	           $this->pnCantidad=$laFila[0]+$i[4];
			   $lcSql = "UPDATE E01PALM SET NCANT ='$this->pnCantidad', cCodUsu='$this->pcCodUsu', tmodifi=SYSDATE() WHERE CCODALM='$this->pcDestino' AND CCODINS='$i[0]'";
               $llOk = $loSql->omExec($lcSql);
			   if (!$llOk) {
	    	       $loSql->omDisconnect();
                   $this->pcError = '<DATA><ERROR>ERROR EN GRABACION 2</ERROR></DATA>';
			       return false;
               }
			 }
	       }
	  }else{
	    
	    //resta insumos al origen y agrega al destino
		foreach ($this->paDataI as $i) {
		     $lcSql="SELECT NCANT FROM E01PALM WHERE CCODALM='$this->pcOrigen' AND CCODINS ='$i[0]'";
		     $RS = $loSql->omExec($lcSql);
             $laFila = $loSql->fetch($RS);
			 $lnCant = $laFila[0];
			 if(empty($laFila[0])){
			       $loSql->omDisconnect();
                   $this->pcError = '<DATA><ERROR>ALMACEN ORIGEN SIN INSUMOS SUFICIENTES</ERROR></DATA>';
			       return false;                
			 }elseif($lnCant < $i[4]){
			       $loSql->omDisconnect();
                   $this->pcError = '<DATA><ERROR>ALMACEN ORIGEN SIN INSUMOS SUFICIENTES (1)</ERROR></DATA>';
			       return false;                
			 }
			 $lnCantRes=$lnCant-$i[4];
			 //actualiza el origen
     	     $lcSql = "UPDATE E01PALM SET NCANT ='$lnCantRes', cCodUsu='$this->pcCodUsu', tmodifi=SYSDATE() WHERE CCODALM='$this->pcOrigen' AND CCODINS='$i[0]'";
               $llOk = $loSql->omExec($lcSql);
			   if (!$llOk) {
	    	       $loSql->omDisconnect();
                   $this->pcError = '<DATA><ERROR>ERROR EN GRABACION 2</ERROR></DATA>';
			       return false;
               }
			 //suma al destino
			 $lcSql="SELECT * FROM E01PALM WHERE CCODALM='$this->pcDestino' AND CCODINS ='$i[0]'";
		     $RS = $loSql->omExec($lcSql);
             $laFila = $loSql->fetch($RS);
			 //no existe ese insumo en ese almacen
	         if(empty($laFila[0])){
			    $lcSql = "INSERT INTO E01PALM (cCodAlm, cCodIns,nCanT,cCodUsu, tModifi) VALUES 
                 ('$this->pcDestino', '$i[0]', '$i[4]', '$this->pcCodUsu', SYSDATE())";
                $llOk = $loSql->omExec($lcSql);
				if (!$llOk) {
	    	       $loSql->omDisconnect();
                   $this->pcError = '<DATA><ERROR>ERROR EN GRABACION 1</ERROR></DATA>';
			       return false;
                }
			 }else{
			   $RS = $loSql->omExec("SELECT NCANT FROM E01PALM WHERE CCODALM='$this->pcDestino' AND CCODINS='$i[0]'");
               $laFila = $loSql->fetch($RS);
	           $this->pnCantidad=$laFila[0]+$i[4];
			   $lcSql = "UPDATE E01PALM SET NCANT ='$this->pnCantidad', cCodUsu='$this->pcCodUsu', tmodifi=SYSDATE() WHERE CCODALM='$this->pcDestino' AND CCODINS='$i[0]'";
               $llOk = $loSql->omExec($lcSql);
			   if (!$llOk) {
	    	       $loSql->omDisconnect();
                   $this->pcError = '<DATA><ERROR>ERROR EN GRABACION 2</ERROR></DATA>';
			       return false;
               }
			 }
         }
	  }
      $loSql->omDisconnect();
      return true;
   }
  
}
?>