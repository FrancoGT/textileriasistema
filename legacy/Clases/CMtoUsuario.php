<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Mantenimiento de Usuarios
//-------------------------------------
class CMtoUsuario extends CBase {
   public $paEstado, $pcEstado, $pcCodUsu, $pcCodPer, $paCodPer, $pdFecIni, $pdFecFin, $pdFecha, $pcNombre, $paData,
          $paNombre, $paCodEmp, $plNuevo, $paCodUsu, $paPerfil, $pcPerfil;
   
   public function __construct() {
      parent::__construct();
      $this->paCodPer = null;
      $this->paEstado = null;
      $this->paData   = null;
      $this->paCodEmp = null;
      $this->paCodUsu = null;
      $this->paPerfil = null;
   }
   
   // Inicializa mantenimiento de usuarios
   function omInitUsuarios() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga estado
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '001'");
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
      // Carga perfil
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '049'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paPerfil[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERFILES DEFINIDOS. SUBTABLA [049]</ERROR></DATA>';
         return false;
      }
      // Carga usuario
	  $lcSql="SELECT cCodUsu, cNombre, cDesPer FROM V_E01TUSU WHERE cCodUsu!='9999' ORDER BY cNombre";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodUsu[] = array($laFila[0], $laFila[1], $laFila[2]);
      }
      $loSql->omDisconnect();
      return true;
   }
   
   // Personal para nuevo usuario
   function omNuevoUsuario() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga usuarios      
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM E01MPER WHERE NOT cCodPer IN (SELECT cCodPer FROM E01TUSU) AND cEstado = 'A' ORDER BY cNombre");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL ACTIVO DISPONIBLE PARA SER USUARIO</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   // Recupera datos para editar usuario
   function omEditar() {
      if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CÓDIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }              
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }      
      // Carga personal      
      $RS = $loSql->omExec("SELECT cNombre, cEstado, dFecFin, cCodPer, cPerfil FROM V_E01TUSU WHERE cCodUsu = '$this->pcCodUsu'");
      $loSql->omDisconnect();
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = '<DATA><ERROR>CÓDIGO DE USUARIO NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcNombre = $laFila[0];
      $this->pcEstado = $laFila[1];
      $this->pdFecFin = $laFila[2];
      $this->pcCodPer = $laFila[3];
      $this->pcPerfil = $laFila[4];
      return true;
   }
   
   // Inicializa historia de personal ???
   public function omInitHistoria() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga puesto
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '022'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paPuesto[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PUESTOS DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Carga sector
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '037'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paSector[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY SECTORES DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Cargos
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 3), cDescri FROM V_E01TTAB WHERE cCodTab = '015'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCargos[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY CARGOS DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Empresas
      $RS = $loSql->omExec("SELECT cCodEmp, cDescri FROM E01MEMP ORDER BY nOrden, cDescri");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodEmp[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY EMPRESAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      // Carga Empleados
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S03MPER WHERE cEstado = 'A' AND cCodPer != '999999' ORDER BY CNOMBRE");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY EMPLEADOS ACTIVOS</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   // Actualizar
   public function omActualizar () {
      if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DEL USUARIO A ACTUALIZAR VACIO</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecFin)) {
         $this->pcError = '<DATA><ERROR>FECHA INVALIDA DE EXPIRACION DE CLAVE</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcUsuCod)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }         
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $llOk = $this->mxValDatos1($loSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
	  $lcSql="UPDATE E01TUSU SET cEstado = '$this->pcEstado', dFecFin = '$this->pdFecFin',
                 cPerfil = '$this->pcPerfil', cUsuCod = '$this->pcUsuCod', tModifi = SYSDATE() 
                 WHERE cCodUsu = '$this->pcCodUsu'";
      $llOk = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR EN GRABACIÓN DE USUARIO</ERROR></DATA>';
      }
      return $llOk;
   }

   // Valida estado y perfil
   protected function mxValDatos1($p_oSql) {
      $RS = $p_oSql->omExec("SELECT cCodigo FROM V_E01TTAB WHERE cCodTab = '001' AND cCodigo = '$this->pcEstado'");
      $laFila = $p_oSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = '<DATA><ERROR>ESTADO ['.$this->pcEstado.'] NO DEFINIDO. SUBTABLA [001]</ERROR></DATA>';
         return false;
      }
      $RS = $p_oSql->omExec("SELECT cCodigo FROM V_E01TTAB WHERE cCodTab = '049' AND cCodigo = '$this->pcPerfil'");
      $laFila = $p_oSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = '<DATA><ERROR>PERFIL ['.$this->pcPerfil.'] NO DEFINIDO. SUBTABLA [049]</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   // Graba nuevo usuario
   public function omGrabar() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcUsuCod)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }         
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Valida datos
      $llOk = $this->mxValDatos1($loSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
      // Validar codigo de personal
      $lcSql = "SELECT cNroDni FROM E01MPER WHERE cCodPer = '$this->pcCodPer'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL NO EXISTE</ERROR></DATA>';
         return false;
      }
      $lcNroDni = $laFila[0];
      // Nuevo usuario
	  $lcSql="SELECT MAX(cCodUsu) FROM E01TUSU WHERE cCodUsu != '9999'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $lcCodUsu = $laFila[0];
      if (empty($lcCodUsu)) {
         $lcCodUsu = '0000';
      }
      $i = (int)$lcCodUsu + 1;
      $lcCodUsu = (string)$i;
      $lcCodUsu = right('00000'.trim($lcCodUsu), 4);
      $lcSql = "INSERT INTO E01TUSU (cCodUsu, cCodPer, cEstado, cClave, dFecFin, cPerfil, cUsuCod, tModifi) VALUES 
                   ('$lcCodUsu', '$this->pcCodPer', 'A', ('$lcNroDni'),
                   SYSDATE(), '$this->pcPerfil', '$this->pcUsuCod', SYSDATE())";
      $llOk = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR EN GRABACIÓN DE NUEVO USUARIO</ERROR></DATA>';
      }
      return $llOk;
   }
}
?>