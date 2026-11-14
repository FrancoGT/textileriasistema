<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Mantenimiento de Clientes
//-------------------------------------
class CMtoClientes extends CBase {
   public $paEstado, $pcDescri, $pcEstado, $pcCodUsu, $pcCodEmp, $pmDatos, $paCodEmp, $paData;

   public function __construct() {
      parent::__construct();
      $this->paEstado = null;
      $this->pcDescri = null;
      $this->paData = null;
   }

  
   //INICIALIZA DATOS PARA CLIENTES
   public function omInitClientes(){   
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
       // Carga Estado
       $lcSql = "SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '001'";
       $RS = $loSql->omExec($lcSql);
       $i = 0;
       while ($laFila = $loSql->fetch($RS)) {
           $i++;
           $this->paEstado[] = array($laFila[0], $laFila[1]);
       }
       if ($i == 0) {
	       $loSql->omDisconnect();
           $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DEFINIDOS</ERROR></DATA>';
           return false;
       }
       // Carga Prioridades
       $lcSql = "SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '002'";
       $RS = $loSql->omExec($lcSql);
       $i = 0;
       while ($laFila = $loSql->fetch($RS)) {
           $i++;
           $this->paPriori[] = array($laFila[0], $laFila[1]);
       }
       if ($i == 0) {
	       $loSql->omDisconnect();
           $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DEFINIDOS</ERROR></DATA>';
           return false;
       }

       // Carga CIIU
       $lcSql = "SELECT SUBSTR(cCodigo, 1, 4), cDescri FROM V_E01TTAB WHERE cCodTab = '099'";
       $RS = $loSql->omExec($lcSql);
       $i = 0;
       while ($laFila = $loSql->fetch($RS)) {
           $i++;
           $this->paCIIU[] = array($laFila[0], $laFila[1]);
       }
       if ($i == 0) {
	       $loSql->omDisconnect();
           $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DEFINIDOS</ERROR></DATA>';
           return false;
       }
	   // Carga Tipo de Cliente
       $lcSql = "SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '098'";
       $RS = $loSql->omExec($lcSql);
       $i = 0;
       while ($laFila = $loSql->fetch($RS)) {
           $i++;
           $this->paTipCli[] = array($laFila[0], $laFila[1]);
       }
       if ($i == 0) {
	       $loSql->omDisconnect();
           $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DEFINIDOS</ERROR></DATA>';
           return false;
       }
	   // Carga PERSONAS TIPO A
      $RS = $loSql->omExec("SELECT cCodCli,cNombre FROM E01MCLI WHERE CTIPO = 'A'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodA[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $this->paCodA[] = array('9999', 'CREAR NUEVO CLIENTE TIPO A');
      }
	  	   // Carga PERSONAS TIPO B
      $RS = $loSql->omExec("SELECT cCodCli,cNombre FROM E01MCLI WHERE CTIPO = 'B'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodB[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $this->paCodB[] = array('9999', 'CREAR NUEVO CLIENTE TIPO B');
      }

	  	   // Carga PERSONAS TIPO C
      $RS = $loSql->omExec("SELECT cCodCli,cNombre FROM E01MCLI WHERE CTIPO = 'C'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodC[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $this->paCodC[] = array('9999', 'CREAR NUEVO CLIENTE TIPO C');
      }

	   $loSql->omDisconnect();
       return true;
    }
	
    //APLICAR CLIENTE
	public function omAplicarCliente(){
	    if(empty($this->pcCodCli)){
		   $this->pcError = '<DATA><ERROR>ERROR AL CARGAR CODIGO DE CLIENTE</ERROR></DATA>';
		   return false;
		}
	    $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;           
        }
		$lcSql="SELECT cNombre,cNroDNI,CNRORUC,CDIREC,CTELEF,CTIPO,CESTADO,CCODCIIU,CSIGLAS,CPRIORI,cCodCli FROM E01MCLI WHERE cCodCli = '$this->pcCodCli'";
        $RS = $loSql->omExec($lcSql);
        $laFila = $loSql->fetch($RS);
        if (empty($laFila[0])) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE CLIENTE NO EXISTE</ERROR></DATA>';
            return false;
        }
		$this->pcNombre =$laFila[0];
		$this->pcNroDNI =$laFila[1];
		$this->pcNroRUC =$laFila[2];
		$this->pcDirec =$laFila[3];
		$this->pcTelef =$laFila[4];
		$this->pcTipo =$laFila[5];
		$this->pcEstado =$laFila[6];
		$this->pcCIIU =$laFila[7];
		$this->pcSiglas =$laFila[8];
		$this->pcPriori =$laFila[9];
		$this->pcCodCli =$laFila[10];
		$loSql->omDisconnect();
		return true;    
	}
	
    public function omGrabarCliente(){
		if(empty($this->pcNombre)){
		   $this->pcError = '<DATA><ERROR>NOMBRE DE CLIENTE VACIO</ERROR></DATA>';
		   return false;
		}elseif(empty($this->pcNroDNI)){
		   $this->pcError = '<DATA><ERROR>DNI DE CLIENTE VACIO</ERROR></DATA>';
		   return false;
		}elseif(strlen($this->pcNroDNI)!=8){
		   $this->pcError = '<DATA><ERROR>LONGITUD DNI DE CLIENTE INCORRECTO</ERROR></DATA>';
		   return false;
		}elseif(strlen($this->pcNroRUC)!=11){
		   $this->pcError = '<DATA><ERROR>LONGITUD RUC DE CLIENTE INCORRECTO</ERROR></DATA>';
		   return false;
		}elseif(empty($this->pcNroRUC)){
		   $this->pcError = '<DATA><ERROR>RUC DE CLIENTE VACIO</ERROR></DATA>';
		   return false;
		}elseif(empty($this->pcDirec)){
		   $this->pcError = '<DATA><ERROR>DIRECCION DE CLIENTE VACIO</ERROR></DATA>';
		   return false;
		}elseif(empty($this->pcTelef)){
		   $this->pcError = '<DATA><ERROR>TELEFONO DE CLIENTE VACIO</ERROR></DATA>';
		   return false;
		}elseif(empty($this->pcCIIU)){
		   $this->pcError = '<DATA><ERROR>CIIU DE CLIENTE VACIO</ERROR></DATA>';
		   return false;
		}elseif(empty($this->pcSiglas)){
		   $this->pcError = '<DATA><ERROR>SIGLAS DE CLIENTE VACIO</ERROR></DATA>';
		   return false;
		}elseif(empty($this->pcPriori)){
		   $this->pcError = '<DATA><ERROR>PRIORIDAD DE CLIENTE VACIO</ERROR></DATA>';
		   return false;
		}
		$this->pcNombre=strtoupper($this->pcNombre);
		$this->pcDirec=strtoupper($this->pcDirec);
		$this->pcSiglas=strtoupper($this->pcSiglas);
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;           
        }        
        
        if ($this->plNuevo) {
         $RS = $loSql->omExec("SELECT COUNT(CCODCLI) FROM E01MCLI");
         $laFila = $loSql->fetch($RS);
         $lcCodCli = '000000';
         if (!empty($laFila[0]))
            $lcCodCli = $laFila[0];
         $lnCodCli = (int)$lcCodCli + 1;
         $lcCodCli = (string)$lnCodCli;
         $lcCodCli = right('00000'.trim($lcCodCli), 6);
         $lcSql = "INSERT INTO E01MCLI (CCODCLI, CNOMBRE, CNRODNI, CNRORUC, CDIREC, CTELEF, CTIPO, CESTADO, CCODCIIU, CSIGLAS,CPRIORI, CCODUSU, TMODIFI) VALUES 
		 ('$lcCodCli', '$this->pcNombre','$this->pcNroDNI', '$this->pcNroRUC', '$this->pcDirec', '$this->pcTelef', '$this->pcTipo', '$this->pcEstado', '$this->pcCIIU','$this->pcSiglas','$this->pcPriori', '$this->pcCodUsu',SYSDATE())";
		 } else {
            $RS = $loSql->omExec("SELECT cCodCli FROM E01MCLI WHERE cCodCli = '$this->pcCodCli'");
            $laFila = $loSql->fetch($RS);
            if (empty($laFila[0])) {
               $loSql->omDisconnect();
               $this->pcError = '<DATA><ERROR>CODIGO DE CLIENTE NO EXISTE</ERROR></DATA>';
               return false;
            }
            $lcSql = "UPDATE E01MCLI SET cNombre = '$this->pcNombre',cNroDNI='$this->pcNroDNI',cNroRUC='$this->pcNroRUC', cDirec = '$this->pcDirec',cTelef='$this->pcTelef',cTipo='$this->pcTipo',cEstado='$this->pcEstado',cCodCIIU='$this->pcCIIU',cSiglas='$this->pcSiglas',cPriori='$this->pcPriori',cCodUsu='$this->pcCodUsu',TMODIFI=SYSDATE() WHERE cCodCLi='$this->pcCodCli'";
         }
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR EN GRABACIÓN DE PERSONAL</ERROR></DATA>';
            return false;
         }
         if ($this->plNuevo) {
            // Carga Empleados nuevamente
            $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM E01MPER WHERE SUBSTR(cCodPer, 1, 1) != '9' ORDER BY CNOMBRE");
            while ($laFila = $loSql->fetch($RS)) {
               $this->paCodPer[] = array($laFila[0], $laFila[1]);
            }
         }
        $loSql->omDisconnect();
        return true;
    }

    

}


//////////////////////////