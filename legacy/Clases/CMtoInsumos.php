<?php
require_once 'Clases/CBase.php';
//------------------------------------------
// Mantenimiento y procesos de Compras
//------------------------------------------
class CMtoInsumos extends CBase {   
   public function __construct() {
      parent::__construct();      
   }
 
   // Inicializa proveedores
   function omInitProveedores() {//D.C.R.       
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }                
       // Carga Sub Tipo de Productos
      $RS = $loSql->omExec("SELECT cCodigo, cDescri FROM V_E01TTAB WHERE cCodTab = '011'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paSubTip[] = array(trim($laFila[0]), $laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = 'NO HAY SUBTIPOS DEFINIDOS';
         return false;
      }
      // Carga Unidad de Medida
      $RS = $loSql->omExec("SELECT cCodigo, cDescri FROM V_E01TTAB WHERE cCodTab = '007'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paUniMed[] = array(trim($laFila[0]), $laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = 'NO HAY TIPOS DE UNIDAD DEFINIDAS';
         return false;
      }
      
      //Data Productos
      $RS = $loSql->omExec("SELECT A.CCODINS, A.CDESCRI, B.CDESCRI, A.NCANGRA, A.NPRECIO FROM E01MINS A INNER JOIN V_E01TTAB B ON B.CCODIGO = A.CUNIMED WHERE B.CCODTAB = '007' ORDER BY A.CDESCRI");
      while ($laFila = $loSql->fetch($RS)) {                  
         $tipo = substr($laFila[0],0,2);          
         $RS1 = $loSql->omExec("SELECT CDESCRI FROM V_E01TTAB WHERE CCODTAB = '011' AND CCODIGO = '$tipo'");                
         $lFila = $loSql->fetch($RS1); 
         $this->paData[] = array($laFila[0], $laFila[1], $lFila[0], $laFila[2], $laFila[3],$laFila[4],$laFila[5]);
      }
        //Carga Tipos
      $RS = $loSql->omExec("SELECT CCODIGO, CDESCRI FROM V_E01TTAB WHERE CCODTAB = '009'");
      $i = 0;
      while($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipArs[] = array(trim($laFila[0]), $laFila[1]);
      }
      $loSql->omDisconnect();
      return true;
   }
   
   function omBuscarTipo(){
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }         
      //Carga Tipos
      $RS = $loSql->omExec("SELECT CCODIGO, CDESCRI FROM V_E01TTAB WHERE CCODTAB = '024'");
      $i = 0;
      while($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[] = array($laFila[0], $laFila[1], $laFila[2]);
      }
      $loSql->omDisconnect();
      return true;
   }
   
   function omInitProveedor() {//D.C.R.
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }         
      //Carga Proveedores
      $RS = $loSql->omExec("SELECT A.CDESCRI, A.CNRORUC, B.CDESCRI FROM E01MPRV A INNER JOIN E01TTAB B ON B.CCODIGO = A.CESTADO WHERE B.CCODTAB='001' AND B.CTIPREG='1' ORDER BY CCODPRV");
      $i = 0;
      while($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[] = array($laFila[0], $laFila[1], $laFila[2]);
      }
      $loSql->omDisconnect();
      return true;
   }
   
   function omGrabarArticulo(){
      if (empty($this->pcDescri)) {
         $this->pcError = 'DESCRIPCIÓN NO DEFINIDA';
         return false;
      } else if (empty($this->pcUniMed)) {
         $this->pcError = 'UNIDAD DE MEDIDA NO DEFINIDA';
         return false;
      } else if (empty($this->pnPrecio)) {
         $this->pcError = 'PRECI0 NO DEFINIDO';
         return false;
      } else if (empty($this->pnCanGra)) {
         $this->pcError = 'PARTE GRANULADA NO DEFINIDA';
         return false;
      } 
	  $this->pcDescri=strtoupper($this->pcDescri);
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }           
      if($this->nuevo == 0){
         $pcSubTip= trim($this->pcSubTip);
         $RS = $loSql->omExec("SELECT COUNT(*) FROM E01MINS WHERE CCODINS LIKE '$pcSubTip%'");
         $laFila = $loSql->fetch($RS);
         $lcCodigo = $laFila[0];         
         $i = (int)$lcCodigo + 1;
         $lcCodigo = (string)$i;
         $this->pcCodigo = right('000'.trim($lcCodigo), 4);
         $this->pcCodigo = trim($this->pcSubTip).trim($this->pcCodigo);         
         $this->pcUniMed = trim($this->pcUniMed);    
         $llOk= $loSql->omExec("INSERT INTO E01MINS (CCODINS, CDESCRI, CUNIMED, NPRECIO, NCANGRA , CCODUSU) VALUES('$this->pcCodigo', '$this->pcDescri', '$this->pcUniMed', $this->pnPrecio,$this->pnCanGra,'$this->cCoduUsu')");
      }else if($this->nuevo == 1){
          $llOk= $loSql->omExec("UPDATE E01MINS SET CUNIMED = '$this->pcUniMed', NCANGRA = '$this->pnCanGra', NPRECIO = '$this->pnPrecio' WHERE CCODARS = '$this->pcCodArs'");
      }
      if (!$llOk) {
          $this->pcError = 'ERROR EN LA INSERCIÓN';
          return false;
      }      
      $loSql->omDisconnect();
      return true;       
   }
   
   function omInitEditar(){
      if(empty($this->pcCodArs)){
         $this->pcError = 'ARTICULO NO SELECCIONADO';         
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;         
         return false;
      }         
      $RS = $loSql->omExec("SELECT CDESCRI, CUNIMED, NPRECIO,NCANGRA FROM E01MINS WHERE CCODINS = '$this->pcCodArs'");      
      $laFila = $loSql->fetch($RS);
      $this->pcDescri = $laFila[0];
      $this->pcUniMed = $laFila[1];
      $this->pnPrec   = $laFila[2];
      $this->pnCanGra = $laFila[3];
      $loSql->omDisconnect();
      return true;
   }
   
   // Inicializa Articulos
   function omInitArtiProveedores(){
      if(empty($this->pcCodPrv)){
         $this->pcError = 'PROVEEDOR NO SELECCIONADO';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }         
      $RS = $loSql->omExec("SELECT CNOMPRV FROM E01MPRV WHERE CCODPRV = '$this->pcCodPrv'");      
      $laFila = $loSql->fetch($RS); 
      $this->pcNomPrv = $laFila[0];
      $RS = $loSql->omExec("SELECT A.CCODARS, A.CDESCRI, B.CDESCRI, A.NPRECIO,A.NCANGRA FROM E01MARS A INNER JOIN V_E01TTAB B ON B.CCODIGO = A.CUNIMED  WHERE B.CCODTAB = '011'");
      while ($laFila = $loSql->fetch($RS)) {                  
         $this->paData[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3],$laFila[4]);
      }                  
      $loSql->omDisconnect();      
      return true;
   }
   
   // Inicializa Articulos
   function omInitArticulos(){
       if(empty($this->pcCodPrv)){
           $this->pcError = 'PROVEEDOR NO SELECCIONADO';
           return false;
       }
       $loSql = new CSql();
       $llOk = $loSql->omConnect();
       if (!$llOk) {
           $this->pcError = $loSql->pcError;
           return false;
       }         
       $RS = $loSql->omExec("SELECT CDESCRI FROM E01MPRV WHERE CCODPRV = '$this->pcCodPrv'");                     
       $laFila = $loSql->fetch($RS);              
       $this->pcDescri = $laFila[0];
       $RS = $loSql->omExec("SELECT A.CCODARS, A.CDESCRI, B.CDESCRI, A.NPREC, C.CDESCRI FROM E01MARS A INNER JOIN E01TTAB B ON A.CUNIMED = B.CCODIGO 
                INNER JOIN E01TTAB C ON C.CCODIGO = A.CMONEDA WHERE A.CCODPRV = '$this->pcCodPrv' AND B.CCODTAB = '011' AND B.CTIPREG = '1' AND C.CCODTAB = '002' AND C.CTIPREG = '1'");
       $i = 0;
       while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3],$laFila[4]);
      }
      if ($i == 0) {
         $this->pcError = 'NO HAY ARTICULOS DEFINIDOS';
         return false;
      }      
      $loSql->omDisconnect();      
      return true;
   }

   //Añade Articulos Seleccionados
   function omAñadeArticulo(){       
      if (empty($this->pcNroCom)) {
         $this->pcError = 'TIPO DE COMPRA NO SELECCIONADO';            
         return false;         
      } else if (empty($this->pcCodCom)) {
         $this->pcError = 'CODIGO SELECCIONADO';                  
         return false;         
      } else if (empty($this->pnCantidad)) {
         $this->pcError = 'ARTICULO NO SELECCIONADO';                  
         return false;         
      } else if (empty($this->pcCodArS)) {
         $this->pcError = 'CANTIDAD INGRESADA';                  
         return false;         
      } else if (empty($this->pcCodPrv)) {
         $this->pcError = 'TOTAL A PAGAR NO INGRESADO';                  
         return false;         
      }      
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }   
      $a=0;
      foreach($this->pnCantidad as $j) {                  
         $RS = $loSql->omExec("SELECT COUNT(*) FROM E01DCOM");
         $laFila = $loSql->fetch($RS);
         $lcCodigo = $laFila[0];
         
         $i = (int)$lcCodigo + 1;
         $lcCodigo = (string)$i;
         $this->pcCodigo = right('00000'.trim($lcCodigo), 6);                       
        if(!empty($this->pnCantidad[$a])){          
            $pcCodArS = $this->pcCodArS[$a];          
            $pnCantidad = $this->pnCantidad[$a];          
            $llOk= $loSql->omExec("INSERT INTO E01DCOM VALUES('$this->pcCodCom','$this->pcCodigo','$pcCodArS',$pnCantidad,'A')");            
            if (!$llOk) {
                $loSql->omDisconnect();        
                $this->pcError = '<DATA><ERROR>ERROR EN GRABACIÓN</ERROR></DATA>';
                return false;
            }
        }
        $a++;
      }       
      $loSql->omDisconnect();
      return true;       
   }
   
   function omEliminarProducto(){
       if (empty($this->pcCodArs)) {
           $this->pcError = 'ARTICULO NO SELECCIONADO';            
           return false;         
        } 
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }           
        $llOk= $loSql->omExec("DELETE FROM E01MARS WHERE CCODARS = '$this->pcCodArs'");
        if (!$llOk) {
            $loSql->omDisconnect();        
            $this->pcError = '<DATA><ERROR>ERROR EN ELIMINACIÓN</ERROR></DATA>';
            return false;
        } 
        $loSql->omDisconnect();
        return true;       
   }
   
   //Eliminar Articulo del Detalle del Comprobante
   function omEliminarDetalleComprobante(){
      if (empty($this->pcNroCom)) {
         $this->pcError = 'COMPROBANTE NO SELECCIONADO';            
         return false;         
      } 
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }           
      $llOk= $loSql->omExec("DELETE FROM E01DCOM WHERE CNROCOM = '$this->pcNroCom'");
      if (!$llOk) {
        $loSql->omDisconnect();        
        $this->pcError = '<DATA><ERROR>ERROR EN ELIMINACIÓN</ERROR></DATA>';
        return false;
      } 
      $loSql->omDisconnect();
      return true;       
   }
   
   function omInitDatos(){
       $loSql = new CSql();
       $llOk = $loSql->omConnect();
       if (!$llOk) {
           $this->pcError = $loSql->pcError;
           return false;
       }                          
       $this->pcDescri = $laFila[0];
       $RS = $loSql->omExec("SELECT B.CDESCRI, A.NMONTO, A.NMONTO, C.CDESCRI FROM E01MCOM A INNER JOIN E01MARS B ON A.CDESCRI = B.CCODARS INNER JOIN E01TTAB C ON C.CCODIGO=A.CMONEDA WHERE CCODTAB='002' AND CTIPREG = '1'");
       $i = 0;
       while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3]);
      }
      if ($i == 0) {
         $this->pcError = 'NO HAY ARTICULOS DEFINIDOS';
         return false;
      }
        $loSql->omDisconnect();
      return true;
   }
   
}
?>