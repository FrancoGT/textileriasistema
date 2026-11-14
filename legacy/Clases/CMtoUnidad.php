<?php
//-------------------------------------
// Mantenimiento de Unidades
//-------------------------------------
require_once 'Clases/CBase.php';

class CMtoUnidad extends CBase {
   public $paEstado, $pcEstado, $pcCodUsu, $pcCodPer, $paCodPer, $pdFecIni, $pdFecFin, $pdFecha,
          $pcNombre, $paData, $paNombre, $paCodEmp, $plNuevo, $paCodUni;
   
   public function __construct() {
      parent::__construct();
      $this->paEstado = $this->paData = $this->paCodEmp = $this->paCodUni = null;
   }

   // Inicia mantenimiento de unidades
   function omInitUnidad() {
      // Conecta DB 
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga estados
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '001'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ESTADOS [001] DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Carga unidades
      $RS = $loSql->omExec("SELECT A.cCodUni, A.cDescri, B.cDescri AS cDesEst FROM S02MUNI A 
                            LEFT OUTER JOIN V_S02TTAB B ON A.cEstado = B.cCodigo AND B.cCodTab = '001'
                            WHERE A.cCodUni != '99999' ORDER BY A.cCodUni");
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodUni[] = array($laFila[0], $laFila[1], $laFila[2]);
      }
      $loSql->omDisconnect();
      return true;
   }
   
   function omAplicar() {
      if (empty($this->pcCodUni)) {
         $this->pcError = '<DATA><ERROR>CÓDIGO DE UNIDAD NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga unidades
      $RS = $loSql->omExec("SELECT cDescri, cEstado FROM S02MUNI WHERE cCodUni = '$this->pcCodUni'");
      $loSql->omDisconnect();
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = '<DATA><ERROR>CÓDIGO DE UNIDAD NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcDescri = $laFila[0];
      $this->pcEstado = $laFila[1];
      return true;
   }
   
   // Grabar Unidad
   public function omGrabarUnidad () {
      if (empty($this->pcCodUni)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD VACIO ESTA VACIO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcDescri)) {
         $this->pcError = '<DATA><ERROR>DESCRIPCION DE LA UNIDAD ESTA VACIA</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcEstado)) {
         $this->pcError = '<DATA><ERROR>ESTADO DE LA UNIDAD ESTA VACIO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      if ($this->plNuevo) {
         $RS = $loSql->omExec("SELECT cCodUni FROM S02MUNI WHERE cCodUni = '$this->pcCodUni'");
         $laFila = $loSql->fetch($RS);
         if (!empty($laFila[0])) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD EXISTE</ERROR></DATA>';
            return false;
         }
         $lcSql = "INSERT INTO S02MUNI (cCodUni, cDescri, cEstado, cCodUsu) VALUES 
                   ('$this->pcCodUni', '$this->pcDescri', '$this->pcEstado', '$this->pcCodUsu')";
      } else {
         $RS = $loSql->omExec("SELECT cCodUni FROM S02MUNI WHERE cCodUni = '$this->pcCodUni'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD NO EXISTE</ERROR></DATA>';
            return false;
         }
         $lcSql = "UPDATE S02MUNI SET cEstado = '$this->pcEstado', cDescri = '$this->pcDescri', cCodUsu = '$this->pcCodUsu', 
                   tModifi = SYSDATE WHERE cCodUni = '$this->pcCodUni'";
      }
      $llOk = $loSql->omExec($lcSql);
      if (!$llOk) {
         $loSql->rollback();
         $this->pcError = '<DATA><ERROR>ERROR EN GRABACIÓN DE UNIDAD</ERROR></DATA>';
      }
      $loSql->omDisconnect();
      return $llOk;
   }
}
?>