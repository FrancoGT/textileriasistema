<?php
require_once "Clases/CBase.php";
//-------------------------------------
// Inicio de sesion 
//-------------------------------------
class CLogin extends CBase {
   public $pcCodigo, $pcClave, $pcClave1, $pcClave2, $pcNombre, $pcCodPer, $pcTipo, $pcCadena, $pnCambio;

   public function __construct() {
      parent::__construct();
   }

   public function omCambiarClave() {
      // Condiciones de excepcion
      if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO ESTA DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcClave)) {
         $this->pcError = '<DATA><ERROR>CLAVE NO DEFINIDA</ERROR></DATA>';
         return false;
      } else if (empty($this->pcClave1)) {
         $this->pcError = '<DATA><ERROR>NUEVA CLAVE NO DEFINIDA</ERROR></DATA>';
         return false;
      } else if (empty($this->pcClave2)) {
         $this->pcError = '<DATA><ERROR>REINGRESO DE NUEVA CLAVE NO DEFINIDA</ERROR></DATA>';
         return false;
      } else if ($this->pcClave1 != $this->pcClave2) {
         $this->pcError = '<DATA><ERROR>REINGRESO DE NUEVA CLAVE NO DEFINIDA</ERROR></DATA>';
         return false;
      }
      // Conectar DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Verificar la clave antigua
      $lcSql = "SELECT cCodUsu, cClave FROM E01TUSU WHERE cCodUsu = '$this->pcCodUsu'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>USUARIO NO EXISTE</ERROR></DATA>';
         return false;
      }
      if ($this->pcClave != $laFila[1]) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CLAVE INCORRECTA</ERROR></DATA>';
         return false;
      }
      // Guardamos nueva clave
      $lcClave = $this->pcClave1;//ojo EMM
      $lcSql = "UPDATE E01TUSU SET cClave = '$lcClave', dFecFin = SYSDATE() + 45 WHERE cCodUsu = '$this->pcCodUsu'";
      $llOk = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR LA CLAVE</ERROR></DATA>';
      }
      return $llOk;
   }

   public function omLogin() {
      if (empty($this->pcCodigo)) {         
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO ESTA DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcClave)) {
         $this->pcError = '<DATA><ERROR>CLAVE NO DEFINIDA</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }      
      //$lcSql = "SELECT E01TUSU('$this->pcCodigo', '$this->pcClave') FROM DUAL";      
      $lcSql = "SELECT CCODUSU,DFECFIN,CCODPER FROM E01TUSU WHERE CCODUSU = '$this->pcCodigo' AND CCLAVE = '$this->pcClave'";
      $RS = $loSql->omExec($lcSql);      
      $laFila = $loSql->fetch($RS);
      $lcCambio=$laFila[1];
      if(empty($laFila[0])){
         $this->pcError = '<DATA><ERROR>CLAVE O USUARIO NO VALIDO</ERROR></DATA>'; 
         $loSql->omDisconnect();
         return false;
      }
      $lcSql = "SELECT CNOMBRE,CCODPER FROM E01MPER WHERE CCODPER = '$laFila[2]'";
      $RS = $loSql->omExec($lcSql);      
      $laFila = $loSql->fetch($RS);

      $loSql->omDisconnect();
      $this->pcNombre = $laFila[0];
      $this->pcCodigo = $this->pcCodigo;
      if($lcCambio == NULL)     
          $this->pnCambio = 1;
      else
          $this->pnCambio = 0;
      return true;
   }
}
?>