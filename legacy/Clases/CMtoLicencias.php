<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Matricula alumno en curso
//-------------------------------------
class CMtoLicencias extends CBase {
   public $paEstado, $pcCodUsu, $pcDescri, $pcEstado, $paCodNor, $paCurNor, $paDescri, $paIndex, $pcNomDoc, $pdFecha,
          $pcTiempo, $pcCodCur, $paCodPer, $pnNota, $paData, $paTipLic, $paCargo, $paSecAut, $paSector, $paRestri,
          $pcNroDni, $paCodEmp, $pcCarDis, $pcDesSec;
   
   public function __construct() {
      parent::__construct();
      $this->paData = $this->paEstado = $this->paTipLic = $this->paCargo = $this->paSector = $this->paSecAut =
      $this->paRestri = $this->paCodEmp = null;
   }

   // Carga tablas para mantenimiento de licencias
   public function omInitLicencias() {
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Trae estados
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '001'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>TABLA DE ESTADOS [001] NO ESTA DEFINIDA</ERROR></DATA>';
         return false;
      }
      // Trae tipos de licencia
      $lcSql = "SELECT cTipLic, cDescri, cEstado, cEmail FROM S02MLIC ORDER BY cTipLic";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[] = array($i, $laFila[0], $laFila[1], $laFila[2], $laFila[3], 1);
      }
      $loSql->omDisconnect();
      return true;
   }
   
   // Carga tablas para mantenimiento de licencias
   public function omTablasMtoLicencias_old() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cDescri FROM S02MLIC ORDER BY cDescri";
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '001'");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>TABLA [001] NO ESTA DEFINIDA EN [S01TTAB]</ERROR></DATA>';
         return false;
      }
      return true;
   }

   // Inicializa mantenimiento de licencias
   public function omInitMtoLicencias() {
      $llOk = $this->omTablasMtoLicencias();
      if (!$llOk) {
         return false;
      }
     $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $RS = $loSql->omExec('SELECT cTipLic, cDescri, cEstado FROM S02MLIC ORDER BY cTipLic');
      $loSql->omDisconnect();
     $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
        $i++;
         $this->paData[$i] = array($i, $laFila[0], $laFila[1], $laFila[2]);
      }
      return true;
   }
   
   // Graba tipos de licencia
   public function omGrabarTipoLicencia() {
      if (empty($this->paData)) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA GRABAR</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Valida que no se repitan tipos y datos de licencia no este vacios
      $i = 0;
      foreach ($this->paData as $laFila) {
         if (!in_array(substr($laFila[1], 0, 1), array('V', 'F', 'T'))) {
            $this->pcError = '<DATA><ERROR>PREFIJO DE CODIGO ERRADO ['.$laFila[1].']</ERROR></DATA>';
            return false;
         }
         $lcNumero = trim(substr($laFila[1], 1));
         if (strlen($lcNumero) != 2 or !preg_match("/^[0-9]+$/", $lcNumero)) {
            $this->pcError = '<DATA><ERROR>NÚMERO DE TIPO DE LICENCIA ERRADO ['.$laFila[1].']. DEBE ESTAR ENTRE [00-99]</ERROR></DATA>';
            return false;
         }
         if (empty($laFila[1]) or empty($laFila[2])) {
            $this->pcError = '<DATA><ERROR>ERROR HAY DATOS VACIOS</ERROR></DATA>';
            return false;
         }
         $laEmail = explode(",", $laFila[4]);
         foreach ($laEmail as $j) {
            $llOk = fxValEmail($j);
            if (!$llOk) {
                $this->pcError = '<DATA><ERROR>LA LICENCIA ['.$laFila[1].'] TIENE EMAIL ERRADOS</ERROR></DATA>';
                return false;
             }
         }
            
         $i++;
         for ($j = $i + 1; $j <= fxCount($this->paData); $j++) {
             if ($laFila[1] == $this->paData[$j][1]) {
                $this->pcError = '<DATA><ERROR>LA LICENCIA ['.$laFila[1].'] ESTA REPETIDA</ERROR></DATA>';
                return false;
             }
         }
      }
      // Conecta con DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      foreach ($this->paData as $laFila) {
         $lcTipLic = $laFila[1];
         $RS = $loSql->omExec("SELECT cTipLic FROM S02MLIC WHERE cTipLic = '$lcTipLic'");
         $i = $loSql->fetch($RS);
         if (empty($i)) {
            $lcSql = "INSERT INTO S02MLIC (cTipLic, cDescri, cEstado, cEmail, cCodUsu) VALUES 
                      ('$laFila[1]', '$laFila[2]', '$laFila[3]', '$laFila[4]', '$this->pcCodUsu')";
         } else {
            $lcSql = "UPDATE S02MLIC SET cDescri = '$laFila[2]', cEstado = '$laFila[3]', cEmail = '$laFila[4]', cCodUsu = '$this->pcCodUsu' WHERE cTipLic = '$laFila[1]'";
         }
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL GRABAR TIPOS DE LICENCIAS</ERROR></DATA>';
            return false;
         }
      }
      $loSql->omDisconnect();
      return $llOk;
   }

   // Aplicar codigo de personal para licencias
   public function omAplicar() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL VACIO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga nombre
      $RS = $loSql->omExec("SELECT cNombre, cNroDni, cCarDis FROM  TABLE (F_S02MPER_1('$this->pcCodPer'))");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcNombre = $laFila[0];
      $this->pcNroDni = $laFila[1];
      $this->pcCarDis = $laFila[2];
	  $this->pcDesSec = $this->mxSector($this->pcCodPer, $loSql);
      // Carga registro de licencias
      $RS = $loSql->omExec("SELECT cTipLic, TO_CHAR(dFecVig, 'YYYY-MM-DD'), TO_CHAR(dFecFin, 'YYYY-MM-DD'), cNroLic, nNota, cEstado, cCargo, cCodEmp FROM S02DLIC WHERE cCodPer = '$this->pcCodPer' AND SUBSTR(cTipLic, 1, 1) = 'F' ORDER BY dFecVig");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($i, $laFila[0], $laFila[1], $laFila[2], $laFila[3], $laFila[4], $laFila[5], $laFila[6], $laFila[7]);
      }
      return true;
   }

   public function omAplicarVehiculosFerroviarios() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL VACIO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga nombre
      $lcSql = "SELECT cNombre, cNroDni FROM S02MPER WHERE cCodPer = '$this->pcCodPer'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcNombre = $laFila[0];
      $this->pcNroDni = $laFila[1];
	  //Carga Sectores
	  $this->pcDesSec = $this->mxSector($this->pcCodPer, $loSql);
      // Carga registro de licencias
      $lcSql = "SELECT cTipLic, TO_CHAR(dFecVig, 'YYYY-MM-DD'), TO_CHAR(dFecFin, 'YYYY-MM-DD'), cNroLic, cSecAut, cAutori, cRestri, SUBSTR(cSector, 1, 1), cEstado, cCodEmp FROM S02DLIC WHERE cCodPer = '$this->pcCodPer' AND SUBSTR(cTipLic, 1, 1) = 'V' ORDER BY dFecVig";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($i, $laFila[0], $laFila[1], $laFila[2], $laFila[3], $laFila[4], $laFila[5], $laFila[6], $laFila[7], $laFila[8], $laFila[9]);
      }
      return true;
   }
   
   protected function mxSector($p_cCodPer, $p_oSql){
      $lcDesSec = '*** ERROR ***';
      $RS = $p_oSql->omExec("SELECT cDesSEc FROM TABLE (F_S02MPER_1('$p_cCodPer'))");
      $laFila = $p_oSql->fetch($RS);
	  if (!empty($laFila[0]))
	     $lcDesSec = $laFila[0];
      return $lcDesSec;
   }
   
   public function omAplicarVehiculosTerrestres() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL VACIO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga nombre
      $RS = $loSql->omExec("SELECT cNombre, cNroDni FROM S02MPER WHERE cCodPer = '$this->pcCodPer'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcNombre = $laFila[0];
      $this->pcNroDni = $laFila[1];
	  $this->pcDesSec = $this->mxSector($this->pcCodPer, $loSql);
      // Carga registro de licencias
      $RS = $loSql->omExec("SELECT cTipLic, TO_CHAR(dFecVig, 'YYYY-MM-DD'), TO_CHAR(dFecFin, 'YYYY-MM-DD'), cNroLic, cRestri, cEstado FROM S02DLIC WHERE cCodPer = '$this->pcCodPer' AND SUBSTR(cTipLic, 1, 1) = 'T' ORDER BY dFecVig");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($i, $laFila[0], $laFila[1], $laFila[2], $laFila[3], $laFila[4], $laFila[5]);
      }
      return true;
   }
   
   // Inicializa registro
   public function omInitRegistro($p_cTipo) {
      if (empty($p_cTipo)) {
         $this->pcError = '<DATA><ERROR>NO HA DEFINIDO TIPO DE LICENCIA</ERROR></DATA>';
         return false;
     }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga empresas
      $RS = $loSql->omExec("SELECT cCodEmp, cDescri FROM S02MEMP WHERE cEstado = 'A' ORDER BY nOrden, cDescri");
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
      // Carga estado de licencias
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '035'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paEstado[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ESTADO DE LICENCIA DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Carga cargos disgregados
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 3), cDescri FROM V_S02TTAB WHERE cCodTab = '045'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCargo[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TABLA DE CARGOS DISGREGADOS [035]</ERROR></DATA>';
         return false;
      }
      // Carga restricciones
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '047'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paRestri[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TABLA DE RESTRICCIONES [047]</ERROR></DATA>';
         return false;
      }
      // Carga sectores
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '037'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paSector[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TABLA DE SECTORES [037]</ERROR></DATA>';
         return false;
      }
      // Carga sector autorizado de conduccion
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '046'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paSecAut[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TABLA DE SECTORES DE CONDUCCION [046]</ERROR></DATA>';
         return false;
      }
      // Carga sector 
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '046'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paSector[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TABLA DE SECTORES DE CONDUCCION [046]</ERROR></DATA>';
         return false;
      }
      // Carga tipo de licencias
      $RS = $loSql->omExec("SELECT cTipLic, cDescri FROM S02MLIC WHERE CESTADO ='A' ORDER BY cTipLic");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
        if (substr($laFila[0], 0, 1) == $p_cTipo) {
            $this->paTipLic[] = array($laFila[0], $laFila[1]);
            $i++;
         }
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE LICENCIA DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Carga Empleados
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE cEstado = 'A' AND cCodPer != '999999' ORDER BY cNombre");
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

   // Graba registro de licencia
   public function omGrabarRegistro() {
      if (empty($this->paData) or fxCount($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA GRABAR</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Valida fecha 
      foreach($this->paData as $laFila) {
         if (!fxValDate($laFila[2])) {
            $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE VIGENCIA DE LICENCIA</ERROR></DATA>';
            return false;
         } else if (!fxValDate($laFila[3])) {
            $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE CADUCIDAD DE LICENCIA</ERROR></DATA>';
            return false;
         } else if ($laFila[2] >= $laFila[3]) {
            $this->pcError = '<DATA><ERROR>FECHA DE VIGENCIA ES MAYOR O IGUAL QUE FECHA DE VENCIMIENTO</ERROR></DATA>';
            return false;
         } else if (empty($laFila[4])) {
            $this->pcError = '<DATA><ERROR>NÚMERO DE LICENCIA NO DEFINIDO</ERROR></DATA>';
            return false;
         }
      }
      // Conecta con DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $llOk = $loSql->omExec("DELETE FROM S02DLIC WHERE cCodPer = '$this->pcCodPer' AND SUBSTR(cTipLic, 1, 1) = 'F'");
      if (!$llOk) {
        $loSql->rollback();
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR LICENCIAS ANTERIORES</ERROR></DATA>';
         return false;
      }
      // Verifica que no hayan numeros de licencia repetidos
/*      foreach($this->paData as $i) {
         $RS = $loSql->omExec("SELECT cCodPer FROM S02DLIC WHERE cNroLic = '$i[4]'");
         $laFila = $loSql->fetch($RS);
         if (!empty($laFila[0])) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>PERSONAL [$laFila[0]] TIENE REPETIDO NUMERO DE LICENCIA [$i[4]]</ERROR></DATA>';
            return false;
         }
      }*/
      // Graba licencias de empleado
      $ldFecSis = date('Y-m-d');
      foreach($this->paData as $i) {
/*         $lcEstado = 'A';
         if ($ldFecSis > $i[3])
            $lcEstado = 'V';*/
         $llOk = $loSql->omExec("INSERT INTO S02DLIC (cCodPer, cTipLic, dFecVig, dFecFin, cNroLic, nNota, cEstado, cCargo, cCodEmp, cCodUsu) VALUES ('$this->pcCodPer', '$i[1]', TO_DATE('$i[2]', 'YYYY-MM-DD'), TO_DATE('$i[3]', 'YYYY-MM-DD'), '$i[4]', '$i[5]', '$i[6]', '$i[7]', '$i[8]', '$this->pcCodUsu')");
         if (!$llOk) {
              $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL GRABAR LICENCIAS</ERROR></DATA>';
            return false;
         }
      }
      $loSql->omDisconnect();
      return $llOk;
   }
   
   public function omGrabarVehiculosFerroviarios() {
      if (empty($this->paData) or fxCount($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA GRABAR</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      foreach($this->paData as $laFila) {
         if (!fxValDate($laFila[2])) {
            $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE VIGENCIA DE LICENCIA</ERROR></DATA>';
            return false;
         } else if (!fxValDate($laFila[3])) {
            $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE CADUCIDAD DE LICENCIA</ERROR></DATA>';
            return false;
         } else if ($laFila[2] >= $laFila[3]) {
            $this->pcError = '<DATA><ERROR>FECHA DE VIGENCIA ES MAYOR O IGUAL QUE FECHA DE VENCIMIENTO</ERROR></DATA>';
            return false;
         } else if (empty($laFila[4])) {
            $this->pcError = '<DATA><ERROR>NÚMERO DE LICENCIA NO DEFINIDO</ERROR></DATA>';
            return false;
         } else if (empty($laFila[6])) {
            $this->pcError = '<DATA><ERROR>VEHICULOS AUTORIZADOS NO DEFINIDO</ERROR></DATA>';
            return false;
         }
      }
      // Conecta con DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $llOk = $loSql->omExec("DELETE FROM S02DLIC WHERE cCodPer = '$this->pcCodPer' AND SUBSTR(cTipLic, 1, 1) = 'V'");
      if (!$llOk) {
         $loSql->rollback();
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR LICENCIAS ANTERIORES</ERROR></DATA>';
         return false;
      }
      // Graba licencias de empleado
      foreach($this->paData as $i) {
         $lcSql = "INSERT INTO S02DLIC (cCodPer, cTipLic, dFecVig, dFecFin, cNroLic, cSecAut, cAutori, cRestri, cSector, cEstado, cCodEmp, cCodUsu) VALUES ('$this->pcCodPer', '$i[1]', TO_DATE('$i[2]', 'YYYY-MM-DD'), TO_DATE('$i[3]', 'YYYY-MM-DD'), '$i[4]', '$i[5]', '$i[6]', '$i[7]', '$i[8]', '$i[9]', '$i[10]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL GRABAR LICENCIAS DE CONDUCCION DE VEHICULOS FERROVIARIOS</ERROR></DATA>';
            return false;
         }
      }
      $loSql->omDisconnect();
      return $llOk;
   }

   public function omGrabarVehiculosTerrestres() {
      if (empty($this->paData) or fxCount($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA GRABAR</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      foreach($this->paData as $laFila) {
         if (!fxValDate($laFila[2])) {
            $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE VIGENCIA DE LICENCIA</ERROR></DATA>';
            return false;
         } else if (!fxValDate($laFila[3])) {
            $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE CADUCIDAD DE LICENCIA</ERROR></DATA>';
            return false;
         } else if ($laFila[2] >= $laFila[3]) {
            $this->pcError = '<DATA><ERROR>FECHA DE VIGENCIA ES MAYOR O IGUAL QUE FECHA DE VENCIMIENTO</ERROR></DATA>';
            return false;
         } else if (empty($laFila[4])) {
            $this->pcError = '<DATA><ERROR>NÚMERO DE LICENCIA NO DEFINIDO</ERROR></DATA>';
            return false;
         }
      }
      // Conecta con DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $llOk = $loSql->omExec("DELETE FROM S02DLIC WHERE cCodPer = '$this->pcCodPer' AND SUBSTR(cTipLic, 1, 1) = 'T'");
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR LICENCIAS ANTERIORES</ERROR></DATA>';
         return false;
      }
      // Graba licencias de empleado
      foreach($this->paData as $i) {
         $llOk = $loSql->omExec("INSERT INTO S02DLIC (cCodPer, cTipLic, dFecVig, dFecFin, cNroLic, cRestri, cEstado, cCodUsu) VALUES ('$this->pcCodPer', '$i[1]', TO_DATE('$i[2]', 'YYYY-MM-DD'), TO_DATE('$i[3]', 'YYYY-MM-DD'), '$i[4]', '$i[5]', '$i[6]', '$this->pcCodUsu')");
//cTipLic, dFecVig, dFecFin, cNroLic, cRestri, cEstado 
         if (!$llOk) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL GRABAR LICENCIAS DE CONDUCCION DE VEHICULOS FERROVIARIOS</ERROR></DATA>';
            return false;
         }
      }
      $loSql->omDisconnect();
      return $llOk;
   }
   
   //////////////////////////////////////////////////////
   // Aplica codigo de Cursos
   public function omAplicarCursos() {
      // Condiciones de excepcion
      if (empty($this->pcCodCur)) {
         $this->pcError = 'CODIGO DE CURSO VACIO';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Curso
      $lcSql = "SELECT cDescri, cEstado, cNomDoc, dFecha, cTiempo, mDatos FROM S02MCUR WHERE cCodCur = '$this->pcCodCur'";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $laFila = $loSql->fetch($RS);
      $this->pcDescri = $laFila[0];
      if (empty($this->pcDescri)) {
         $this->pcError = 'CODIGO DE CURSO NO EXISTE';
         $llOk = false;
      }
      $this->pcEstado = $laFila[1];
      $this->pcNomDoc = $laFila[2];
      $this->pdFecha  = $laFila[3];
      $this->pcTiempo = $laFila[4];
      $this->pmDatos  = $laFila[5];
      return $llOk;
   }

   
   // Mantenimientode puente cursos - normas
   public function omMtoPuenteCursos() {
      if (empty($this->pcCodCur)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE CURSO VACIO</ERROR></DATA>';
         return false;
      }
      $this->paData = null;
      $this->paCodNor = null;
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Curso
      $lcSql = "SELECT cDescri FROM S02MCUR WHERE cCodCur = '$this->pcCodCur'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->pcDescri = $laFila[0];
      if ($this->pcDescri == null) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE CURSO NO EXISTE</ERROR></DATA>';
         return false;
      }
      // Carga normas
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR WHERE cEstado = 'A' ORDER BY cDescri");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodNor[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY NORMAS DEFINIDAS</ERROR></DATA>';
         $llOk = false;
      }
      // Trae normas del curso
      $lcSql = "SELECT cCodNor, mDatos FROM S02PCUR WHERE cCodCur = '$this->pcCodCur' ORDER BY cCodNor";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($i, $laFila[0], $laFila[1]);
      }
      return $llOk;
   }

   
   //////////////////////////////////////////
   // Grabar fecha de uso del activo fijo
   public function omGrabarCursos() {
      $Hora = explode(":", $this->pcTiempo);
      if (empty($this->pcDescri)) {
         $this->pcError = 'DESCRIPCION DEL PROYECTO ESTA VACIA';
         return false;
      }  else if (empty($this->pcNomDoc)) {
         $this->pcError = 'NOMBRE DEL DOCENTE NO DEFINIDO';
         return false;
      }  else if (empty($this->pdFecha)) {
         $this->pcError = 'FECHA NO DEFINIDO';
         return false;
      } else if (!$this->mxValDate($this->pdFecha)) {
         $this->pcError = 'FECHA INVALIDA';
         return false;
      }  else if (empty($this->pmDatos)) {
         $this->pcError = 'DATOS VARIOS NO DEFINIDO';
         return false;
      }  else if ($Hora[0] > 24 || $Hora[0] < 0 && $Hora[1] > 59) {
         $this->pcError = 'HORA INVALIDA';
         return false;
      }  else if (empty($this->pcTiempo)) {
         $this->pcError = 'HORA NO DEFINIDA';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = 'CODIGO DE USUARIO NO DEFINIDO';
         return false;
      } else if (empty($this->pcCodCur) AND !$this->plNuevo) {
         $this->pcError = 'CODIGO DE CURSO NO DEFINIDO';
         return false;
      }
      $this->pcDescri = strtoupper($this->pcDescri);
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      if ($this->plNuevo) {
         $RS = $loSql->omExec('SELECT MAX(cCodcur) FROM S02MCUR');
         $laFila = $loSql->fetch($RS);
         if ($laFila[0] == null)
            $lcCodCur = '0000';
         else
            $lcCodCur = $laFila[0] ;
         $lnCodCur = (int)$lcCodCur + 1;
         $lcCodCur = (string)$lnCodCur;
         $lcCodCur = right('00'.trim($lcCodCur), 3);
         $lcSql = "INSERT INTO S02MCUR (cCodCur, cDescri, cEstado, cnomdoc, dfecha, cTiempo, mDatos, cCodUsu) VALUES ('$lcCodCur', '$this->pcDescri', '$this->pcEstado','$this->pcNomDoc', '$this->pdFecha','$this->pcTiempo', '$this->pmDatos', '$this->pcCodUsu')";
      } else {
         $RS = $loSql->omExec("SELECT cCodCur FROM S02MCUR WHERE cCodCur = '$this->pcCodCur'");
         $laFila = $loSql->fetch($RS);
         if ($laFila[0] == null) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE CURSO NO DEFINIDO</ERROR></DATA>';
            return false;
         }
         $lcSql = "UPDATE S02MCUR SET cDescri = '$this->pcDescri', cEstado = '$this->pcEstado', cNomDoc ='$this->pcNomDoc', dfecha = '$this->pdFecha', cTiempo = '$this->pcTiempo', mDatos = '$this->pmDatos', cCodUsu = '$this->pcCodUsu', tModifi = NOW() WHERE cCodCur = '$this->pcCodCur'";
      }
      $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      return true;
   }

   // Buscar empresas
   public function omBuscarCursos() {
      // Condiciones de excepcion
      if (empty($this->pcDescri)) {
         $this->pcError = '<DATA><ERROR>DESCRIPCION INVALIDA</ERROR></DATA>';
         return false;
      }
      $this->paCodCur = null;
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcDescri = trim($this->pcDescri).'%';
      $lcSql = "SELECT cCodCur, cDescri FROM S02MCUR WHERE cDescri LIKE '$lcDescri' ORDER BY cDescri";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodCur[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY CURSOS QUE CUMPLAN CONDICION</ERROR></DATA>';
         $llOk = false;
      }
      return $llOk;
   } 
  
   public function omMtoCursosNotas() {
  $loSql = new CSql();
  $llOk = $loSql->omConnect();
  if (!$llOk) {
     $this->pcError = $loSql->pcError;
     return false;
  }
  // Carga activo fijo
  $lcSql = "SELECT cDescri, dFecha FROM S02MCUR WHERE cCodCur = '$this->pcCodCur'";
  $RS = $loSql->omExec($lcSql);
  $laFila = $loSql->fetch($RS);
  $this->pcDescri = $laFila[0];
  if (empty($this->pcDescri)) {
     $loSql->omDisconnect();
     $this->pcError = 'CODIGO DE CURSO NO EXISTE';
     $llOk = false;
  }
  $this->pdFecha = $laFila[1];
  $this->paCodPer = null;
  
  $lcSql = "SELECT cCodPer, cNombre FROM S02MPER WHERE cEstado = 'A' ORDER BY cNombre";
  $RS = $loSql->omExec($lcSql);
  $loSql->omDisconnect();
  $i = 1;
  while ($laFila = $loSql->fetch($RS)) {
     $this->paCodPer[] = array($laFila[0], $laFila[1]);
     $i++;
  }
  if ($i == 0) {
     $this->pcError = '<DATA><ERROR>NO HAY EMPLEADOS DEFINIDOS</ERROR></DATA>';
     $llOk = false;
  }
  return $llOk;
}
    
   public function omGrabarCursosNotas(){
      if (empty($this->pnNota)) {
         $this->pcError = 'NOTA DEL EMPLEADO ESTA VACIA';
         return false;
      }  else if (($this->pnNota < 0)||($this->pnNota > 100)) {
         $this->pcError = 'NOTA INVALIDA';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = 'CODIGO DE USUARIO NO DEFINIDO';
         return false; 
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "DELETE FROM s02dCap WHERE cCodPer = '$this->paCodPer' AND cCodCur = '$this->pcCodCur'";
      $loSql->omExec($lcSql);
      $lcSql = "INSERT INTO s02dCap (cCodPer, cCodCur, nNota, cCodUsu) VALUES ('$this->paCodPer', '$this->pcCodCur', '$this->pnNota','$this->pcCodUsu')";
      $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      return true;
   }
}
?>