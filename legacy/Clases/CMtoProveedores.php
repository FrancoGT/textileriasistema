<?php
require_once 'Clases/CBase.php';
//------------------------------------------
// Mantenimiento de Proveedores 
//------------------------------------------
class CMtoProveedores extends CBase {   
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
      //Carga Proveedores
      $RS = $loSql->omExec("SELECT A.CCODPRV, A.CNRORUC, A.CNOMBRE, B.CDESCRI FROM E01MPRV A INNER JOIN V_E01TTAB B ON A.CESTADO=B.CCODIGO WHERE B.CCODTAB='001' ORDER BY A.CCODPRV");
      while($laFila = $loSql->fetch($RS)) {         
         $this->paData[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3]);
      }
      //Carga Sexo
      $RS = $loSql->omExec("SELECT CCODIGO, CDESCRI FROM V_E01TTAB WHERE CCODTAB='003' ORDER BY CDESCRI");
      while($laFila = $loSql->fetch($RS)) {
         $this->paSexo[] = array(trim($laFila[0]), $laFila[1]);
      }
       //Carga Tipo de Artículos
      $RS = $loSql->omExec("SELECT CCODIGO, CDESCRI FROM V_E01TTAB WHERE CCODTAB = '011' ORDER BY CDESCRI");
      $i = 0;
      while($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipos[] = array(trim($laFila[0]), $laFila[1]);
      }
       //Carga Tipo de Persona
      $RS = $loSql->omExec("SELECT CCODIGO, CDESCRI FROM V_E01TTAB WHERE CCODTAB = '098' ORDER BY CDESCRI");
      $i = 0;
      while($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipPer[] = array(trim($laFila[0]), $laFila[1]);
      }
      // Carga estado de comprobante
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), CDESCRI FROM V_E01TTAB WHERE CCODTAB = '001' ORDER BY CDESCRI");    
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = 'NO HAY ESTADOS DEFINIDOS';
         return false;
      }
      //Código para Proveedor
      $RS = $loSql->omExec("SELECT COUNT(*) FROM E01MPRV"); 
      $laFila = $loSql->fetch($RS);
      $lcCodigo = $laFila[0];
      $i = (int)$lcCodigo + 1;      
      $lcCodigo = (string)$i;
      $this->pcCodPrv = right('00000'.trim($lcCodigo), 6);      
      $loSql->omDisconnect();
      return true;
   }
   
   function omGrabarProveedor() {
      if (empty($this->pcNombre)){
          $this->pcError = 'NOMBRE NO DEFINIDO(1)';          
          return false;      
      } elseif (empty($this->pcDirecc)){
          $this->pcError = 'DIRECCION NO DEFINIDA';
          return false;      
      }
	  $this->pcNombre=strtoupper($this->pcNombre);
	  $this->pcDirecc=strtoupper($this->pcDirecc);
      
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      
      //Graba Proveedores
      if ($this->nuevo == 0){                            
          //Código para Proveedor
        $RS = $loSql->omExec("SELECT COUNT(*) FROM E01MPRV"); 
        $laFila = $loSql->fetch($RS);
        $lcCodigo = $laFila[0];
        $i = (int)$lcCodigo + 1;      
        $lcCodigo = (string)$i;
        $this->pcCodPrv = right('00000'.trim($lcCodigo), 6);      
		if($this->pcTipPer=='a')
          $lcSql="INSERT INTO E01MPRV (CCODPRV,CNOMBRE,CNRODNI,CTIPPER,CNRORUC,CDIRECC,DFECNAC,CESTADO,MDATOS,CCODUSU) VALUES('$this->pcCodPrv','$this->pcNombre','$this->pcNroDni','$this->pcTipPer','99999999999','$this->pcDirecc','$this->pdFecNac','A','$this->pcTexCon','$this->pcCodUsu')";
		else
		  $lcSql="INSERT INTO E01MPRV (CCODPRV,CNOMBRE,CNRODNI,CTIPPER,CNRORUC,CDIRECC,DFECNAC,CESTADO,MDATOS,CCODUSU) VALUES('$this->pcCodPrv','$this->pcNombre','99999999','$this->pcTipPer','$this->pcNroRuc','$this->pcDirecc','$this->pdFecNac','A','$this->pcTexCon','$this->pcCodUsu')";
		$llOk = $loSql->omExec($lcSql);
         if(!$llOk){
		 $loSql->omDisconnect();
             $this->pcError = 'ERROR EN LA GRABACIÓN';
             return false;
         }
      } else if ($this->nuevo == 1){
         
          //ACTUALIZA ESTADO
         $llOk = $loSql->omExec("UPDATE E01MPRV SET CDIRECC = '$this->pcDirecc', MDATOS = '$this->pcTexCon', CCODUSU = '$this->pcCodUsu' WHERE CCODPRV = '$this->pcCodPrv'");         
         if(!$llOk){
		 $loSql->omDisconnect();
             $this->pcError = 'ERROR EN LA EDICIÓN DEL ESTADO';
             return false;
         }
      }
        //ACTUALIZA LINEA DE PRODUCCIÓN      
        $loSql->omExec("DELETE FROM E01DPRV WHERE CCODPRV = '$this->pcCodPrv'");
        $a=0;
        while($this->paTipArs[$a]) {
           $pcTipArs = trim($this->paTipArs[$a]);            
           $llOk = $loSql->omExec("INSERT INTO E01DPRV (CCODPRV,CTIPARS,CCODUSU) VALUES('$this->pcCodPrv','$pcTipArs','$this->pcCodUsu')");
          
           if(!$llOk){
		   $loSql->omDisconnect();
               $this->pcError = 'ERROR EN LA GRABACIÓN DEL DETALLE DEL PROVEEDOR';
               return false;
           }        
           $a++;         
        }      
      $loSql->omDisconnect();
      return true;
   }
   
   function omGrabarPersonalProveedor(){
      if (empty($this->pcNombre)){
          $this->pcError = 'NOMBRE NO DEFINIDO';          
          return false;      
      } else if (empty($this->pcDirecc)){
          $this->pcError = 'DIRECCION NO DEFINIDA';
          return false;      
      }
	  $this->pcNombre=strtoupper($this->pcNombre);
	  $this->pcDirecc=strtoupper($this->pcDirecc);
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }            
      //Graba Proveedores
      if ($this->nuevo == 0){
         $RS = $loSql->omExec("SELECT COUNT(*) FROM S01MPER"); 
         $laFila = $loSql->fetch($RS);
         $lcCodigo = $laFila[0];
         $i = (int)$lcCodigo;      
         $lcCodigo = (string)$i;
         $this->pcCodPer = right('00000'.trim($lcCodigo), 6);      
         $this->pcTipPer = trim($this->pcTipPer);
         if(empty($this->pcNroDni)){//INSERTA EMPRESA             
             $llOk = $loSql->omExec("INSERT INTO S01MPER (CCODPER, CNOMBRE, CNRODNI, CSEXO, CTIPPER, CNRORUC, CDIRECC, CCODUBI, DFECNAC, CLUGNAC, CCODUSU)
                 VALUES('$this->pcCodPer','$this->pcNombre','XXXXXXXX','$this->pcSexo','X','$this->pcNroRuc','$this->pcDirecc','010101','$this->pdFecNac','010101','$this->pcCodUsu')");             
         }else if ($this->pcTipPer != '3'){//INSERTA PERSONA NATURAL, NO PROVEEDOR
             if ($this->pcTipPer == '1'){//PERSONA NATURAL SIN RUC                 
                 $llOk = $loSql->omExec("INSERT INTO S01MPER (CCODPER, CNOMBRE, CNRODNI, CSEXO, CTIPPER, CNRORUC, CDIRECC, CCODUBI, DFECNAC, CLUGNAC, CCODUSU)
                 VALUES('$this->pcCodPer','$this->pcNombre','$this->pcNroDni','$this->pcSexo','$this->pcTipPer','XXXXXXXXXXX','$this->pcDirecc','010101','$this->pdFecNac','010101','$this->pcCodUsu')");
             }else if ($this->pcTipPer == '2'){//PERSONA NATURAL CON RUC                 
                 $llOk = $loSql->omExec("INSERT INTO S01MPER (CCODPER, CNOMBRE, CNRODNI, CSEXO, CTIPPER, CNRORUC, CDIRECC, CCODUBI, DFECNAC, CLUGNAC, CCODUSU)
                 VALUES('$this->pcCodPer','$this->pcNombre','$this->pcNroDni','$this->pcSexo','$this->pcTipPer','$this->pcNroRuc','$this->pcDirecc','010101','$this->pdFecNac','010101','$this->pcCodUsu')");
             }             
             if(!$llOk){
			     $loSql->omDisconnect();
                 $this->pcError = 'ERROR EN LA GRABACIÓN DE PROVEEDOR';
                 return false;
             }             
             $loSql->omDisconnect();
             return true;
         }else{//INSERTA PERSONAL PROVEEDOR
             $llOk = $loSql->omExec("INSERT INTO S01MPER (CCODPER, CNOMBRE, CNRODNI, CSEXO, CTIPPER, CNRORUC, CDIRECC, CCODUBI, DFECNAC, CLUGNAC, CCODUSU)
                 VALUES('$this->pcCodPer','$this->pcNombre','$this->pcNroDni','$this->pcSexo','$this->pcTipPer','$this->pcNroRuc','$this->pcDirecc','010101','$this->pdFecNac','010101','$this->pcCodUsu')");
         }
         if(!$llOk){
		     $loSql->omDisconnect();
             $this->pcError = 'ERROR EN LA GRABACIÓN DE PERSONAL';
             return false;
         }         
         $llOk = $loSql->omExec("INSERT INTO E01MPRV (CCODPRV,CESTADO,NPORDET,NPORPER,MDATOS,CCODUSU) VALUES('$this->pcCodPer','A','0','0','$this->pcTexCon','$this->pcCodUsu')");
         if(!$llOk){
		     $loSql->omDisconnect();
             $this->pcError = 'ERROR EN LA GRABACIÓN DE PROVEEDOR';
             return false;
         }
         $a=0;
         while($this->paTipArs[$a]) {
            $pcTipArs = trim($this->paTipArs[$a]);
            $llOk = $loSql->omExec("INSERT INTO E01DPRV (CCODPRV,CTIPARS,CCODUSU) VALUES('$this->pcCodPer','$pcTipArs','$this->pcCodUsu')");
            if(!$llOk){
			    $loSql->omDisconnect();
                $this->pcError = 'ERROR EN LA GRABACIÓN DEL DETALLE DEL PROVEEDOR';
                return false;
            }        
            $a++;         
         }
      } else if ($this->nuevo == 1){
         $llOk = $loSql->omExec("UPDATE E01MPRV SET CESTADO = '$this->pcEstado' WHERE CCODPRV = '$this->pcCodPrv'");         
         if(!$llOk){
		     $loSql->omDisconnect();
             $this->pcError = 'ERROR EN LA EDICIÓN';
             return false;
         }
      }
      $loSql->omDisconnect();
      return true;
   }
   
   function omEliminarProveedor(){
      if (empty($this->pcCodPrv)) {
         $this->pcError = 'PROVEEDOR NO SELECCIONADO';            
         return false;         
      } 
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }           
      $llOk= $loSql->omExec("DELETE FROM E01MPRV WHERE CCODPRV = '$this->pcCodPrv'");
      if (!$llOk) {
        $loSql->omDisconnect();        
        $this->pcError = '<DATA><ERROR>ERROR EN ELIMINACIÓN</ERROR></DATA>';
        return false;
      } 
      $loSql->omDisconnect();
      return true;              
   }
   
   function omEditarProveedor(){
       if (empty($this->pcCodPrv)){
           $this->pcError = 'PROVEEDOR NO SELECCIONADO';
           return false;
       }       
       $loSql = new CSql();
       $llOk = $loSql->omConnect();
       if (!$llOk) {
          $this->pcError = $loSql->pcError;
          return false;
       }
       //Datos del proveedor para editar
       $RS = $loSql->omExec("SELECT CNOMBRE, CESTADO, MDATOS, CNRODNI, CNRORUC, DFECNAC, CDIRECC FROM E01MPRV WHERE CCODPRV = '$this->pcCodPrv'");
       $laFila = $loSql->fetch($RS);
       $this->pcNombre = $laFila[0];
       $this->pcEstado = $laFila[1];       
       $this->pcTexCon = $laFila[2];       
       $this->pcNroDni = $laFila[3];
       $this->pcNroRuc = $laFila[4];
       $this->pdFecNac = $laFila[5];
       $this->pcDirecc = $laFila[6];
       //TIPOS DE ARTÍCULO DE ESTE PROVEEDOR
       
       $RS = $loSql->omExec("SELECT CTIPARS FROM E01DPRV WHERE CCODPRV = '$this->pcCodPrv'");
       while($laFila = $loSql->fetch($RS)) {       
         $this->paTipArs[] = array($laFila[0]);
       }     
       $loSql->omDisconnect();
       return true;
   }   
   
   function omInitPersonal(){
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;         
      } 
      //Carga Personal
      $RS = $loSql->omExec("SELECT CCODPER, CNOMBRE FROM S01MPER WHERE CCODPER != '999999' AND CCODPER NOT IN (SELECT CCODPRV FROM E01MPRV)");      
      $i = 0;
      while($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
      }     
      $loSql->omDisconnect();
      return true;
   }
   
   function omGrabarComprobante(){
       if (empty($this->pcNroCom)) {
         $this->pcError = 'NÚMERO DE COMPROBANTE NO DEFINIDO';
         return false;
      } else if (empty($this->pcCodCom)) {
         $this->pcError = 'CÓDIGO DE COMPROBANTE NO DEFINIDO';
         return false;
      } else if (empty($this->pcCodPrv)) {
         $this->pcError = 'PROVEEDOR NO DEFINIDO';
         return false;
      } else if (empty($this->pdFecEmi)) {
         $this->pcError = 'FECHA NO DEFINIDA';
         return false;
      } else if (empty($this->pcDesCom)) {
         $this->pcError = 'DESCRIPCION NO DEFINIDA';
         return false;
      } else if (empty($this->pnTotPag)) {
         $this->pcError = 'TOTAL A PAGAR NO DEFINIDO';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }           
      $RS = $loSql->omExec("SELECT CNROCOM FROM E01DCOM WHERE CCODCOM = '$this->pcCodCom'");
      $i = 0;      
      while ($laFila = $loSql->fetch($RS)) {
         $i++;         
         echo $i;
         $llOk= $loSql->omExec("INSERT INTO E01MCOM VALUES('$this->pcCodCom','$laFila[0]','$this->pcDesCom','$this->pdFecEmi','$this->pcCodPrv','P',$this->pnTotPag)");                          
         if (!$llOk) {
            $this->pcError = 'ERROR EN LA INSERCIÓN';
            return false;
         }        
      }       
      if ($i=0) {
        $loSql->omDisconnect();        
        $this->pcError = '<DATA><ERROR>NO SE PRODUJO NINGUNA GRABACIÓN EN GRABACIÓN</ERROR></DATA>';
        return false;
      }       
      $loSql->omExec("UPDATE E01DCOM SET CESTADO = 'I'");               
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
       $RS = $loSql->omExec("SELECT CDESCRI FROM E01MPRV WHERE CCODPRV = '$this->pcCodPrv'");                     
       $laFila = $loSql->fetch($RS);              
       $this->pcDescri = $laFila[0];
       $RS = $loSql->omExec("SELECT A.CNROCOM, B.CDESCRI, C.CDESCRI, B.NPREC, A.CCANTIDAD,(B.NPREC*A.CCANTIDAD) AS NTOTPAG from E01DCOM A INNER JOIN E01MARS B ON A.CCODARS = B.CCODARS INNER JOIN V_S01TTAB C ON B.CUNIMED=C.CCODIGO WHERE C.CCODTAB ='011' AND A.CESTADO = 'A'");              
       $i = 0;
       $this->nTotPag = 0;
       while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->nTotPag += $laFila[5];
         $this->paData[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3],$laFila[4],$laFila[5]);
      }      
      //Código para Árticulo
      $RS = $loSql->omExec("SELECT COUNT(*) FROM E01MCOM");          
      $laFila = $loSql->fetch($RS);
      $lcCodigo = $laFila[0];
      $i = (int)$lcCodigo + 1;      
      $lcCodigo = (string)$i;
      $this->pcCodCom = right('P-000'.trim($lcCodigo), 6);                
      //Código para comprobante
      $RS = $loSql->omExec("SELECT COUNT(*) FROM E01DCOM");          
      $laFila = $loSql->fetch($RS);
      $lcCodigo = $laFila[0];
      $i = (int)$lcCodigo + 1;      
      $lcCodigo = (string)$i;
      $this->pcCodigo = right('00000'.trim($lcCodigo), 6);                
      $this->dFecha = date('Y-m-d');
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
       $RS = $loSql->omExec("SELECT A.CCODARS, A.CDESCRI, B.CDESCRI, A.NPREC, C.CDESCRI FROM E01MARS A INNER JOIN S01TTAB B ON A.CUNIMED = B.CCODIGO 
                INNER JOIN S01TTAB C ON C.CCODIGO = A.CMONEDA WHERE A.CCODPRV = '$this->pcCodPrv' AND B.CCODTAB = '011' AND B.CTIPREG = '1' AND C.CCODTAB = '002' AND C.CTIPREG = '1'");
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
       $RS = $loSql->omExec("SELECT B.CDESCRI, A.NMONTO, A.NMONTO, C.CDESCRI FROM E01MCOM A INNER JOIN E01MARS B ON A.CDESCRI = B.CCODARS INNER JOIN S01TTAB C ON C.CCODIGO=A.CMONEDA WHERE CCODTAB='002' AND CTIPREG = '1'");
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