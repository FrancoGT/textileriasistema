<?php
require_once 'Clases/CBase.php';

//-------------------------------------
// Mantenimiento de tabla de tablas
//-------------------------------------
class CMtoTablas extends CBase {
   public $pcCodUsu, $paData, $paCodTab, $pcDescri;
   
   function __construct() {
      parent::__construct();
      $this->paData = $this->paCodTab = null;
   }

   // Inicia mantenimiento de tablas
   public function omInitTablas() {
      // Conectar DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga tablas
      $RS = $loSql->omExec("SELECT cCodTab, cDescri FROM E01TTAB WHERE cTipReg = '0' ORDER BY cCodTab");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodTab[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY TABLAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   public function omEditarTabla() {
      if (empty($this->pcCodTab)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE TABLA NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Conectar DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Descripcion de tabla
      $RS = $loSql->omExec("SELECT cDescri FROM E01TTAB WHERE cCodTab = '$this->pcCodTab' AND cTipReg = '0'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CABECERA DE TABLA ['.$this->pcCodTab.'] NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcDescri = $laFila[0];
      // Carga detalle de tabla
      $RS = $loSql->omExec("SELECT nOrden, cCodigo, cDescri FROM E01TTAB WHERE cCodTab = '$this->pcCodTab' AND cTipReg = '1' ORDER BY cCodigo");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[] = array($i, $laFila[0], trim($laFila[1]), $laFila[2], 0);
      }
      return true;
   }
   
   public function omGrabarTabla() {
      if (empty($this->pcCodTab)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE TABLA NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->paData) or count($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>DETALLE DE TABLA NO DEFINIDA</ERROR></DATA>';
         return false;
      }
      $lnTamano = count($this->paData);
      // Verifica que nOrden sea entero y datos vacios
      for ($i = 1; $i <= $lnTamano; $i++) {
          if (empty($this->paData[$i][3]) or empty($this->paData[$i][2])) {
             $this->pcError = '<DATA><ERROR>HAY DATOS VACIOS</ERROR></DATA>';
             return false;
          }
      }
      // Verifica que no haya dos codigos iguales, datos vacios y pone NORDEN en 0 si esta vacio
      for ($i = 1; $i <= $lnTamano; $i++) {
          for ($j = $i + 1; $j <= $lnTamano; $j++) {
              $lcCodig1 = trim((string)$this->paData[$i][2]);
              $lcCodig2 = trim((string)$this->paData[$j][2]);
              if ((string)$lcCodig1 === (string)$lcCodig2) {
                 $this->pcError = '<DATA><ERROR>CODIGO ['.$lcCodig1.'] Y ['.$lcCodig2.'] ESTA REPETIDO</ERROR></DATA>';
                 return false;
              }
          }
      }
      // Conecta con BD
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Verifica codigo de tabla
      $RS = $loSql->omExec("SELECT cDescri, cCodigo FROM E01TTAB WHERE cCodTab = '$this->pcCodTab' AND cTipReg = '0'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CABECERA DE TABLA ['.$this->pcCodTab.'] NO EXISTE</ERROR></DATA>';
         return false;
      }
      if (!empty($laFila[1])) {
         // Valida formato de tabla para codigos
         $lcFormat = trim($laFila[1]);
         $lnLen = strlen($lcFormat);
         for ($i = 1; $i <= $lnTamano; $i++) {
             $lcCodigo = trim($this->paData[$i][2]);
             if ($this->pcCodTab != '021') {
                if (strlen(trim($lcCodigo)) != $lnLen) {
                   $loSql->omDisconnect();
                   $this->pcError = '<DATA><ERROR>LONGITUD DE CODIGO ERRADO ['.$lcCodigo.']</ERROR></DATA>';
                   return false;
                } 
             } else {
                $lcCadena = "/^[0-9.]+$/";
                if (strlen(trim($lcCodigo)) > $lnLen) {
                   $loSql->omDisconnect();
                   $this->pcError = '<DATA><ERROR>LONGITUD DE CODIGO ERRADO ['.$lcCodigo.']</ERROR></DATA>';
                   return false;
                } elseif (!preg_match($lcCadena, $lcCodigo)) {
                   $loSql->omDisconnect();
                   $this->pcError = '<DATA><ERROR>FORMATO DE PK INVALIDO ['.$lcCodigo.']</ERROR></DATA>';
                   return false;
                }
             }
         }
      }
      // Borra anterior data
      $llOk = $loSql->omExec("DELETE FROM E01TTAB WHERE cCodTab = '$this->pcCodTab' AND cTipReg != '0'");
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR DETALLE DE TABLA ['.$this->pcCodTab.']</ERROR></DATA>';
         return false;
      }
      foreach ($this->paData as $i) {
         $lcCodigo = strtoupper(trim($i[2]));
         $lcSql = "INSERT INTO E01TTAB (cCodTab, nOrden, cTipReg, cCodigo, cDescri, cCodUsu, tModifi) VALUES 
                 ('$this->pcCodTab', '$i[1]', '1', '$lcCodigo', '$i[3]', '$this->pcCodUsu', SYSDATE())";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
		    $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL GRABAR DETALLE DE TABLA ['.$this->pcCodTab.']</ERROR></DATA>';
            break;
         }
      }
      $loSql->omDisconnect();
      return $llOk;
   }
   
   // Editar emails de areas
   public function omEditarEmailAreas() {
      // Conectar DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Detalle de tabla
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri, mTexto, cTipReg FROM E01TTAB WHERE cCodTab = '030' ORDER BY cCodigo");
      $loSql->omDisconnect();
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $lcDescri = $laFila[1];
         if ($laFila[3] == '0') {
            $lcDescri = 'GERENCIA';
         } elseif ($laFila[0] == '99') {
            $lcDescri = 'CONTROL LICENCIAS';
         }
         $this->paData[] = array($i, $laFila[0], $lcDescri, $laFila[2]->load(), $laFila[3]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA EDITAR</ERROR></DATA>';
         return false;
      }
      return true;
   }

   // Grabar emails de areas
   public function omGrabarEmailAreas() {
      if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->paData) or count($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>DETALLE DE TABLA NO DEFINIDA</ERROR></DATA>';
         return false;
      }
      foreach ($this->paData as $i) {
         $laEmails = explode(',', $i[3]);
         foreach ($laEmails as $j) {
            $llOk = fxValEmail($j);
            if (!$llOk) {
               $this->pcError = '<DATA><ERROR>ERROR EN EMAILS DE CODIGO ['.$i[1].']</ERROR></DATA>';
               return false;
            }
         }
      }
      // Conectar DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      foreach ($this->paData as $i) {
         $llOk = $loSql->omExec("UPDATE E01TTAB SET mTexto = '$i[3]' WHERE cCodTab = '030' AND cCodigo = '$i[1]' AND cTipReg = '$i[4]'");
         if (!$llOk) {
		    $loSql->omDisconnect(); 
            $this->pcError = "<DATA><ERROR>ERROR AL GRABAR EMAILS DE AREAS. REVISE</ERROR></DATA>";
            break;
         }
      }
      $loSql->omDisconnect();
      return $llOk;
   }
}
?>