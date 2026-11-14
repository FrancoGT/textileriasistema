<?php
require_once 'Clases/CBase.php';

// Clase de mantenimiento de normas
class CMtoNormas extends CBase {
   public $paEstado, $pcCodUsu, $pcDescri, $pcEstado, $pcCodNor, $paTipo, $pcTipo, $paNorma, $paCodNor;

   public function __construct() {
      parent::__construct();
      $this->paTipo   = null;
      $this->paEstado = null;
      $this->paCodNor = null;
   }

   // Inicia mantenimiento de normas
   public function omInit() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      //Cargar tipos de norma
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR WHERE cTipo = '0' AND SUBSTR(cCodNor, 1, 1) != '0' ORDER BY cCodNor");
      while ($laFila = $loSql->fetch($RS)) {
         $this->paTipo[] = array($laFila[0], $laFila[1]);
      }
      // Carga Estados
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '001'");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DEFINIDOS. SUBTABLA [001]</ERROR></DATA>';
         return false;
      }
      return true;
   }

   public function omCargarNormasPorTipo() {
      if (empty($this->pcTipo)) {
         $this->pcError = '<DATA><ERROR>TIPO DE NORMA NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      //Traer descripcion de cabecera de normas
      $RS = $loSql->omExec("SELECT cDescri FROM S02MNOR WHERE cCodNor = '$this->pcTipo'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE CABECERA DE NORMA NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcDescri = $laFila[0];
      $lcTipo = trim($this->pcTipo);
      $RS = $loSql->omExec("SELECT cCodNor, cDescri, cEstado, cPreBas FROM S02MNOR WHERE cCodNor LIKE '$lcTipo%' AND cTipo = '1' ORDER BY cCodNor");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodNor[] = array($i, $laFila[0], $laFila[1], $laFila[2], $laFila[3], '0');
      }
      return true;
   }

    // Grabar normas
   public function omGrabarNormas() {
      if (empty($this->pcCodNor)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE NORMA NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->paCodNor) or fxCount($this->paCodNor) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DETALLE DE NORMAS</ERROR></DATA>';
         return false;
      }
	  // Valida detalle
      $lcTipo = trim($this->pcCodNor);
      $j = strlen($lcTipo);
      foreach($this->paCodNor as $i) {
         if (empty($i[1])) {
            $this->pcError = '<DATA><ERROR>CODIGO DE NORMA VACIO</ERROR></DATA>';
            return false;
         } else if (substr($i[1], 0, $j) != $lcTipo) {
            $this->pcError = '<DATA><ERROR>CODIGO DE NORMA NO CORRESPONDE A CABECERA</ERROR></DATA>';
            return false;
         } else if (trim($i[1]) === $lcTipo) {
            $this->pcError = '<DATA><ERROR>CODIGO DE NORMA ES IGUAL A CABECERA</ERROR></DATA>';
            return false;
         } if (empty($i[2])) {
            $this->pcError = '<DATA><ERROR>DESCRIPCION DE NORMA VACIA</ERROR></DATA>';
            return false;
         } if (empty($i[3])) {
            $this->pcError = '<DATA><ERROR>ESTADO DE NORMA NO DEFINIDA</ERROR></DATA>';
            return false;
         }
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      foreach($this->paCodNor as $i) {
	     $lcPreBas = (isset($i[4])) ? 'S' : 'N';
         if ($i[5] == '0') {
            $lcSql = "UPDATE S02MNOR SET cDescri = UPPER('$i[2]'), cEstado = '$i[3]', cPreBas = '$lcPreBas', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cCodNor = '$i[1]'";
         } else {
            $lcSql = "INSERT INTO S02MNOR (cCodNor, cDescri, cEstado, cTipo, cPreBas, cCodUsu) VALUES ('$i[1]', '$i[2]', '$i[3]', '1', '$lcPreBas', '$this->pcCodUsu')";
         }
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $this->pcError = '<DATA><ERROR>ERROR AL GRABAR NORMAS</ERROR></DATA>';
            break;
         }
      }
      $loSql->omDisconnect();
      return $llOk;
   }
   
   // Grabar cabecera normas
   public function omGrabarCabecera() {
      if (empty($this->pcCodNor)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE NORMA NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcDescri)) {
          $this->pcError = '<DATA><ERROR>DESCRIPCION DE LA NORMA ESTA VACIA</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcCodNor = strtoupper(trim($this->pcCodNor));
      if ($this->plNuevo) {
         $RS = $loSql->omExec("SELECT cCodNor FROM S02MNOR WHERE cCodNor = '$lcCodNor'");
         $laFila = $loSql->fetch($RS);
         if (!empty($laFila[0])) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE NORMA YA EXISTE</ERROR></DATA>';
            return false;
         }
         $lcSql = "INSERT INTO S02MNOR (cCodNor, cTipo, cDescri, cEstado, cCodUsu) VALUES (UPPER('$lcCodNor'), '0', UPPER('$this->pcDescri'), 'A', '$this->pcCodUsu')";
      } else {
         $RS = $loSql->omExec("SELECT cCodNor FROM S03MNOR WHERE cCodNor = '$lcCodNor'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE NORMA NO DEFINIDO</ERROR></DATA>';
            return false;
         }
         $lcSql = "UPDATE S02MNOR SET cDescri = '$this->pcDescri', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cCodNor = '$lcCodNor'";
      }
      $llOk = $loSql->omExec($lcSql);
      if (!$llOk) {
         $loSql->rollback();
         $this->pcError = '<DATA><ERROR>ERROR AL GRABAR CABECERA DE NORMAS</ERROR></DATA>';
         $loSql->omDisconnect();
         return false;
      }
      $loSql->omDisconnect();
      return $llOk;
   }
}
?>
