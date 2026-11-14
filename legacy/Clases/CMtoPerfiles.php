<?php
require_once 'Clases/CBase.php';

// Mantenimiento de perfiles de usuario
class CMtoPerfiles extends CBase {
   public $pcNomPer, $pcCodPer, $paCodPer, $pcNroDni, $pcEmail, $paEstado, $paPerfil, $paCodOpc;

   public function __construct() {
      parent::__construct();
      $this->paCodPer = null;
      $this->paCodOpc = null;
      $this->paEstado = null;
      $this->paPerfil = null;
   }

   function omInitPerfiles() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }    
      // Carga Estado
	  $lcSql="SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '001'";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DEFINIDOS. SUBTABLA [001]</ERROR></DATA>';
         return false;
      }      
       // Carga perfiles
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '049'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paPerfil[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY PERFILES DEFINIDOS. SUBTABLA [049]</ERROR></DATA>';
         return false;
      }
      return true;
   }

   function omInitOpcion() {
      if (empty($this->pcPerfil)) {
         $this->pcError = '<DATA><ERROR>PERFIL NO DEFINIDO</ERROR></DATA>';
         return false;
      }      
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Valida perfil
      $RS = $loSql->omExec("SELECT cDescri FROM V_E01TTAB WHERE cCodTab = '049' AND cCodigo = '$this->pcPerfil'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = '<DATA><ERROR>PERFIL NO SE ENCUENTRA EN TABLA [049]</ERROR></DATA>';
         return false;
      }
      $this->pcDescri = $laFila[0];
      // Carga Opciones     
      $RS = $loSql->omExec("SELECT cCodOpc, cDescri FROM E01TOPC WHERE NOT cCodOpc IN 
              (SELECT cCodOpc FROM E01PPRF where cPerfil = '$this->pcPerfil') AND
              cEstado = 'A' ORDER BY cCodOpc");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodOpc[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY OPCIONES LIBRES PARA PERFIL</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   function omBuscarPorPerfil() {
      if (empty($this->pcPerfil)) {
         $this->pcError = '<DATA><ERROR>PERFIL NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Valida perfil
      $RS = $loSql->omExec("SELECT cDescri FROM V_E01TTAB WHERE cCodTab = '049' AND cCodigo = '$this->pcPerfil'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = '<DATA><ERROR>PERFIL NO SE ENCUENTRA EN TABLA [049]</ERROR></DATA>';
         return false;
      }
      $this->pcDescri = $laFila[0];
      // Carga Perfil
      $RS = $loSql->omExec("SELECT A.cCodOpc, A.cDescri, C.cDescri FROM E01TOPC A 
              INNER JOIN E01PPRF B ON A.cCodOpc = B.cCodOpc 
              LEFT OUTER JOIN V_E01TTAB C ON A.cEstado = C.cCodigo AND C.cCodTab = '001'
              WHERE B.cPerfil = '$this->pcPerfil'");
      while ($laFila = $loSql->fetch($RS)) {
         $this->paPerfil[] = array($laFila[0], $laFila[1], $laFila[2]);
      }
      $loSql->omDisconnect();
      return true;
   }
   
   function omGrabarPerfil() {
      if(empty($this->pcCodOpc)) {
         $this->pcError = '<DATA><ERROR>OPCION NO DEFINDA</ERROR></DATA>';
         return false;
      } else if(empty($this->pcEstado)) {
         $this->pcError = '<DATA><ERROR>ESTADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if(empty($this->pcPerfil)) {
         $this->pcError = '<DATA><ERROR>PERFIL NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if(empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $RS = $loSql->omExec("SELECT MAX(NSERIAL) FROM E01PPRF");
	  $laFila = $loSql->fetch($RS);
	  $lcNserial=(int)$laFila[0]+1;
	  $lcserial=(string)$lcNserial;
      // Graba opcion en perfil            
      $llOk = $loSql->omExec("INSERT INTO E01PPRF (nSerial,cCodOpc, cPerfil, cEstado, cCodUsu) VALUES 
              ('$lcserial','$this->pcCodOpc', '$this->pcPerfil', '$this->pcEstado', '$this->pcCodUsu')");
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR EN GRABACION</ERROR></DATA>';
         return false;
      }     
      $loSql->omDisconnect();
      return true;
   }
   
   function omEliminarPerfil() {
      if(empty($this->pcCodOpc)) {
         $this->pcError = '<DATA><ERROR>OPCION NO SELECCIONADA</ERROR></DATA>';
         return false;
      } else if(empty($this->pcPerfil)) {
         $this->pcError = '<DATA><ERROR>PERFIL NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }      
      // Elimina opcion en perfil
      $llOk = $loSql->omExec("DELETE FROM E01PPRF WHERE cCodOpc = '$this->pcCodOpc' AND cPerfil = '$this->pcPerfil'");
      $loSql->omDisconnect();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR EN ELIMINACION DE OPCION EN PERFIL</ERROR></DATA>';
         return false;
      }     
      return true;
   }
  
   // Validar perfil por opcion y usuario
   function omValidarPerfil($p_cCodUsu, $p_cCodOpc) {
      if (empty($p_cCodOpc)) {
         $this->pcError = '<DATA><ERROR>OPCION NO DEFINIDA</ERROR></DATA>';
         return false;
      } else if(empty($p_cCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }      
      // Graba opcion en perfil            
	  $lcSql ="SELECT cCodOpc FROM E01PPRF WHERE cCodOpc = '$p_cCodOpc' AND cEstado = 'A' AND
                            cPerfil IN (SELECT cPerfil FROM E01TUSU WHERE cCodUsu = '$p_cCodUsu')";
      $RS = $loSql->omExec($lcSql);							
      $laLinea = $loSql->fetch($RS);
      $loSql->omDisconnect();
      if (empty($laLinea[0])) {
         $this->pcError = '<DATA><ERROR>USUARIO NO TIENE PERMISO PARA ESTA OPCION</ERROR></DATA>';
         return false;
      }
      return true;
   } 
} 

function fxValidarPermiso($p_cCodOpc, $p_cMenu) {
   $lo = new CMtoPerfiles();
   $llOk = $lo->omValidarPerfil($_SESSION['gcCodUsu'], $p_cCodOpc);
   if (!$llOk) {
      fxHeader($p_cMenu, fxXmlError($lo->pcError));
	  return false;
   }
   return true;
}
?>