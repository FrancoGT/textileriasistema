<?php
require_once 'Clases/CBase.php';

//------------------------------------------------------------------------------------------
// Registro de Pruebas de eficiencia en terreno, conduccion de trenes y velocidad por radar
//------------------------------------------------------------------------------------------
class CEvaluacion extends CBase {
   public $paSubDiv, $paEstaci, $paTipVag, $paResult, $paSuperv, $paCodUni, $paPuesto, $paCodNor, $paDataP, $paData,
          $pcFecEva, $pcHorEva, $pcKilome, $pcTren, $pcCompos, $pcSubDiv, $pcEstaci, $pcResult, $pcTipVag, $pcSuperv,
          $pcCodUn3, $pcCodUn1, $pcCodUn2, $pcNroEva, $pcCodUsu, $paNroEva, $paCondic, $paConCli, $pcConCli, $paCodEmp,
          $paCodOpe, $pcCodOpe, $paCodPry, $paEvaCon, $pcCodEmp, $pcCodPry, $pcEvaTip, $pmObserv, $paResul1, $paPreBas;

   public function __construct() {
      parent::__construct();
      $this->paSubDiv = $this->paEstaci = $this->paTipVag = $this->paResult = $this->paSuperv = $this->paCodUni = 
      $this->paCodNor = $this->paDataP = $this->paData = $this->paNroEva = $this->paCondic = $this->paConCli = 
      $this->paCodEmp = $this->paCodPry = $this->paEvaCon = $this->paResul1 = $this->paCodOpe =  $this->paPreBas = null;
   }

   // Inicia datos de evaluaciones-pruebas
   public function omInitPruebas($p_nTipo = 1) {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga items de evaluacion para evaluacion de conduccion de trenes
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE cCodTab = '026' AND SUBSTR(cCodigo, 1, 2) != '99'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEvaCon[$laFila[0]] = array($laFila[0], $laFila[1], 'S');
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ITEMS PARA EVALUAR LA CONDUCCION DE TRENES. TABLA [026]</ERROR></DATA>';
         return false;
      }
      // Resultados de evaluacion de conduccion de trenes
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '027'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paResul1[] = array($laFila[0], $laFila[1], 'S');
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY RESULTADOS PARA EVALUAR LA CONDUCCION DE TRENES. TABLA [027]</ERROR></DATA>';
         return false;
      }
      // Carga subdivisiones
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE cCodTab = '020'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paSubDiv[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY SUBDIVISIONES DEFINIDAS. TABLA [020]</ERROR></DATA>';
         return false;
      }
      // Carga Estaciones
      $RS = $loSql->omExec("SELECT TRIM(cCodigo), cDescri FROM V_S02TTAB WHERE cCodTab = '021'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstaci[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ESTACIONES DEFINIDAS. TABLA [021]</ERROR></DATA>';
         return false;
      }
      // Carga tipo de vagon
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '002'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipVag[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE VAGON DEFINIDOS. TABLA [002]</ERROR></DATA>';
         return false;
      }
      // Carga resultado de prueba
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), SUBSTR(cDescri, 1, 20) FROM V_S02TTAB WHERE cCodTab = '003'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paResult[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>TABLA DE RESULTADOS NO DEFINIDOS. TABLA [003]</ERROR></DATA>';
         return false;
      }
      // Carga condiciones de Via
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '004'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCondic[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>TABLA DE CONDICIONES DE VIA NO DEFINIDA. TABLA [004]</ERROR></DATA>';
         return false;
      }
     // Carga condiciones climaticas
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '005'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paConCli[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>TABLA DE CONDICIONES CLIMATICAS NO DEFINIDA. TABLA [005]</ERROR></DATA>';
         return false;
      }
      if ($p_nTipo == 1) {
         // Carga puesto de prueba para eficiencia en terreno
         $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '022'");
         $i = 0;
         while ($laFila = $loSql->fetch($RS)) {
            $i++;
            $this->paPuesto[] = array($laFila[0], $laFila[1]);
         }
      } else if ($p_nTipo == 2) {
           // Carga puesto de prueba para conduccion de trenes
          $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '022'");
          $i = 0;
          while ($laFila = $loSql->fetch($RS)) {
             $i++;
             $this->paPuesto[] = array($laFila[0], $laFila[1]);
          }
      } else if ($p_nTipo == 3) {
         // Carga items de evaluacion para evaluacion de conduccion de trenes
         $this->paEvaCon = null;
         $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE cCodTab = '036' AND SUBSTR(cCodigo, 1, 2) != '99'");
         $i = 0;
         while ($laFila = $loSql->fetch($RS)) {
            $i++;
            $this->paEvaCon[] = array($i, $laFila[0], $laFila[1], 'S');
         }
         if ($i == 0) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>NO HAY ITEMS PARA EVALUAR LA CONDUCCION DE VEHICULOS TERRESTRES. TABLA [036]</ERROR></DATA>';
            return false;
         }
         // Carga puesto de prueba para conduccion de tractos
         $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '023'");
         $i = 0;
         while ($laFila = $loSql->fetch($RS)) {
            $i++;
            $this->paPuesto[] = array($laFila[0], $laFila[1]);
         }
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PUESTOS DEFINIDOS PARA CONDUCCION DE VEHICULOS TERRESTRES. TABLA [022]</ERROR></DATA>';
         return false;
      }
      // Carga supervisores
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE cEstado = 'A' ORDER BY cNombre");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $RS1 = $loSql->omExec("SELECT cCargo FROM TABLE (F_S02MPER_1('$laFila[0]'))");
         $lcCargo = $loSql->fetch($RS1);
         if ($lcCargo[0] == '001' or $lcCargo[0] == '002' or $lcCargo[0] == '003') {
            $i++;
            $this->paSuperv[] = array($laFila[0], $laFila[1]);
         }
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY SUPERVISORES DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Carga unidades
      $RS = $loSql->omExec("SELECT cCodUni, cDescri FROM S02MUNI WHERE cEstado = 'A' ORDER BY cDescri");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodUni[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY UNIDADES DEFINIDAS</ERROR></DATA>';
         return false;
      }
      // Carga normas
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR WHERE cTipo = '1' AND cEstado = 'A' ORDER BY cCodNor");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodNor[] = array($laFila[0], trim($laFila[0]).'-'.$laFila[1]);
      }
      // Carga normas de cuestionario base
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR WHERE cTipo = '1' AND cEstado = 'A' AND cPreBas = 'S' ORDER BY cCodNor");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paPreBas[] = array($laFila[0], trim($laFila[0]).'-'.$laFila[1]);
      }
      // Carga empresas
      $RS = $loSql->omExec("SELECT cCodEmp, cDescri FROM S02MEMP WHERE cEstado = 'A' ORDER BY nOrden, cDescri");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodEmp[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY EMPRESAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      // Carga proyectos
      $RS = $loSql->omExec("SELECT cCodPry, cDescri FROM S02MPRY WHERE cEstado = 'A' ORDER BY cDescri");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPry[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY PROYECTOS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      return true;
   }

   public function omInitEvalRadar() {
      $llOk = $this->omCargaPersonal();
      if (!$llOk) {
         return false;
      }
      $llOk = $this->omInitPruebas(2);
      if (!$llOk) {
         return false;
      }
      // Supervisor cuando no corresponda
      $this->paSuperv[] = array('999999', '* N/C');
      // Personal cuando no corresponda
      $this->paCodPer[] = array('999999', '* N/C');
      return true;
   }
   
   public function omInitEvalTerrestres() {
      $llOk = $this->omCargaPersonal();
      if (!$llOk) {
         return false;
      }
      $llOk = $this->omInitPruebas(3);
      if (!$llOk) {
         return false;
      }
      // Supervisor cuando no corresponda
      $this->paSuperv[] = array('999999', '* N/C');
      // Personal cuando no corresponda
      $this->paCodPer[] = array('999999', '* N/C');
      return true;
   }

   // Carga personal para evaluacion
   public function omCargaPersonal() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga operadores
      $i = 0;
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE cEstado = 'A' ORDER BY cNombre");
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodOpe[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Carga personal
      $i = 0;
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE cEstado = 'A' ORDER BY cNombre");
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL DEFINIDO</ERROR></DATA>';
         return false;
      }
      return true;
   }

   // Graba prueba de eficiencia en terreno
   public function omGrabarPruebaEficiencia() {
      $this->pcKilome = trim($this->pcKilome);
      $this->pcCompos = trim($this->pcCompos);
      if (empty($this->paDataP) or count($this->paDataP) == 0) {
         $this->pcError = '<DATA><ERROR>DATOS DE PERSONAL EVALUADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (!fxValDate($this->pcFecEva)) {
         $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE EVALUACION ['.$this->pcFecEva.']</ERROR></DATA>';
         return false;
      } else if (!fxValTime($this->pcHorEva)) {
         $this->pcError = '<DATA><ERROR>ERROR EN HORA DE PRUEBA-EVALUACION ['.$this->pcHorEva.']</ERROR></DATA>';
         return false;
      } else if ($this->pcEstaci == '99' and !is_numeric(trim($this->pcKilome))) {
          $this->pcError = '<DATA><ERROR>KILOMETRAJE NO ES NUMERICO</ERROR></DATA>';
          return false;
      } else if ($this->pcEstaci == '99' and (!(floatval($this->pcKilome) >= 0 and floatval($this->pcKilome) <= 999999.999))) {
          $this->pcError = '<DATA><ERROR>KILOMETRAJE NO ESTA EN RANGO PERMITIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcTren)) {
          $this->pcError = '<DATA><ERROR>TREN NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCompos)) {
          $this->pcError = '<DATA><ERROR>COMPOSICION NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (!$this->mxValCompos()) {
          $this->pcError = '<DATA><ERROR>ESTRUCTURA DE COMPOSICION ERRADA. REVISE</ERROR></DATA>';
          return false;
      } else if (empty($this->pcSubDiv)) {
          $this->pcError = '<DATA><ERROR>SUBDIVISION NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcEstaci)) {
          $this->pcError = '<DATA><ERROR>ESTACION NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (empty($this->pcTipVag)) {
          $this->pcError = '<DATA><ERROR>TIPO DE VAGON NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcSuperv)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE SUPERVISOR NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodUn1)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD 1 NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodUn2)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD 2 NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUn3)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD 3 NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodEmp)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE EMPRESA NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodPry)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE PROYECTO NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pmObserv)) {
          $this->pcError = '<DATA><ERROR>CAMPO DE OBSERVACIONES ESTA VACIO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (!$this->plNuevo and empty($this->pcNroEva)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Elimina no personal
      $laDataP = null;
      $j = 0;
      foreach($this->paDataP as $i) {
         if ($i[1] != '999999') {
            $j++;
            $laDataP[$j] = array($i[0], $i[1], $i[2]);
         }
      }
      if (count($laDataP) == 0) {
         $this->pcError = '<DATA><ERROR>DATOS DE PERSONAL EVALUADO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Evalua personal repetido
      $llOk = fxValRepeated($laDataP, 2);
      if ($llOk) {
         $this->pcError = '<DATA><ERROR>PUESTO DEL PERSONAL EVALUADO REPETIDO. REVISE</ERROR></DATA>';
         return false;
      }
      // Actualiza arreglo laData
      $laData = null;
      foreach ($laDataP as $i){
         $k = 0;
         foreach ($this->paData as $j){
            if ($i[1] == $j[0]){
               $k++;
               $laData[] = array($j[0], $i[2], $j[2], $j[4]);
            }
         }
         if ($k == 0) {
            $this->pcError = '<DATA><ERROR>HAY PERSONAL SIN NORMAS DEFINIDAS</ERROR></DATA>';
            return false;
         }
      }
      // Empleado con norma repetida
      foreach($laData as $i){
         foreach($laData as $j) {
            $k = 0;
            if ($i[0].$i[2] == $j[0].$j[2]) {
               $k++;
            }
         }
         if ($k > 1) {
            $this->pcError = '<DATA><ERROR>ERROR: EMPLEADO TIENE REPETIDA NORMA EVALUADA. REVISE</ERROR></DATA>';
            return false;
         }
      }
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Valida PK de estacion
      if ($this->pcEstaci != '99') {
         $this->pcKilome = $this->pcEstaci;
      }
      // Valida puesto de personal evaluado y verifica que supervisor no sea personal evaluado
      foreach($laDataP as $i) {
         if ($i[1] == $this->pcSuperv) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR: SUPERVISOR ESTA SIENDO EVALUADO. REVISE</ERROR></DATA>';
            return false;
         }
         $llFlag = false;
         foreach($this->paPuesto as $j) {
            if ($j[0] == $i[2]) {
               $llFlag = true;
               break;
            }
         }
         if (!$llFlag) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ALGUN(OS) PUESTO(S) NO ESTA(N) DEFINIDO(S) REVISE</ERROR></DATA>';
            return false;
         }
      }
      if (!$this->plNuevo) {
         $RS = $loSql->omExec("SELECT cNroEva FROM S02MEVA WHERE cNroEva = '$this->pcNroEva'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO EXISTE</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $lcSql = "UPDATE S02MEVA SET dFecEva = TO_DATE('$this->pcFecEva', 'yyyy-mm-dd'), cHorEva = '$this->pcHorEva', cKilome = '$this->pcKilome', cTren = '$this->pcTren', cCompos = '$this->pcCompos', cSubDiv = '$this->pcSubDiv', cEstaci = '$this->pcEstaci', cTipVag = '$this->pcTipVag', cSuperv = '$this->pcSuperv', cCodUn1 = '$this->pcCodUn1', cCodUn2 = '$this->pcCodUn2', cCodUn3 = '$this->pcCodUn3', cCodEmp = '$this->pcCodEmp', cCodPry = '$this->pcCodPry', cCodUsu = '$this->pcCodUsu', mObserv = '$this->pmObserv', tModifi = SYSDATE WHERE cNroEva = '$this->pcNroEva'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR EVALUACION</ERROR></DATA>';
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM S02DEVA WHERE cNroEva = '$this->pcNroEva'");
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR DETALLE DEL PERSONAL Y NORMAS EVALUADAS (ANTIGUAS)</ERROR></DATA>';
            return false;
         }
      } else {
         $RS = $loSql->omExec("SELECT MAX(cNroEva) FROM S02MEVA WHERE cNroEva LIKE 'E%'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $lcNroEva = '00000';
         } else {
            $lcNroEva = substr($laFila[0], 1);
         }
         $lnNroEva = (int)$lcNroEva + 1;
         $lcNroEva = (string)$lnNroEva;
         $this->pcNroEva = 'E'.right('0000'.trim($lcNroEva), 5);
         $lcSql = "INSERT INTO S02MEVA (cNroEva, dFecEva, cHorEva, cKilome, cTren, cCompos, cSubDiv, cEstaci, cTipVag, cSuperv, cCodUn1, cCodUn2, cCodUn3, cCodEmp, cCodPry, mObserv, cSuper2, cCodUsu) VALUES ('$this->pcNroEva', TO_DATE('$this->pcFecEva', 'YYYY-MM-DD'), '$this->pcHorEva', '$this->pcKilome', '$this->pcTren', '$this->pcCompos', '$this->pcSubDiv', '$this->pcEstaci', '$this->pcTipVag', '$this->pcSuperv', '$this->pcCodUn1', '$this->pcCodUn2', '$this->pcCodUn3', '$this->pcCodEmp', '$this->pcCodPry', '$this->pmObserv', '999999', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL INGRESAR PRUEBA-EVALUACION11111</ERROR></DATA>';
            return false;
         }
      }
      foreach($laData as $i) {
         $lcSql = "INSERT INTO S02DEVA (cNroEva, cCodPer, cPuesto, cCodNor, cResult, cCodUsu) VALUES ('$this->pcNroEva', '$i[0]', '$i[1]', '$i[2]', '$i[3]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR NORMAS</ERROR></DATA>';
            return false;
         }
      }      
      $loSql->omDisconnect();
      return true;
   }

   // Aplica numero de evaluacion
   public function omAplicarEficienciaTerreno() {
      if (empty($this->pcNroEva)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Datos de cabecera
      $llOk = $this->mxAplicar($loSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
      // Carga normas evaluadas
      $llOk = $this->mxNormasEvaluadas($loSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
      // Carga personal evaluado
      $llOk = $this->mxPersonalEvaluado($loSql);
      $loSql->omDisconnect();
      return $llOk;
   }

   protected function mxPersonalEvaluado($p_oSql) {
      // Carga personal evaluado
      $RS = $p_oSql->omExec("SELECT DISTINCT cCodPer, cPuesto FROM S02DEVA WHERE cNroEva = '$this->pcNroEva' ORDER BY cPuesto DESC");
     // Ordena por puesto Maq/JT/Brq
      $laTmp = null;
      while ($laFila = $p_oSql->fetch($RS)) {
         if ($laFila[1] == 'M')  {
            $laTmp[] = array($laFila[0], $laFila[1]);
            $this->pcCodPer = $laFila[0];
         } elseif ($laFila[1] == 'J') {
            $laTmp[] = array($laFila[0], $laFila[1]);
         } else {
            $laTmp[] = array($laFila[0], $laFila[1]);
         }
      }
      $i = 0;
      foreach($laTmp as $laFila) {
         $i++;
         $this->paDataP[$i] = array($i, $laFila[0], $laFila[1]);
      }
      for ($j = $i + 1; $j <= 3; $j++) {
          $this->paDataP[$j] = array($j, '999999', '');
      }
      return true;
   }

   protected function mxNormasEvaluadas($p_oSql) {
      $RS = $p_oSql->omExec("SELECT cCodPer, cPuesto, cCodNor, cResult, cDescri FROM V_S02DEVA_3 
                             WHERE cNroEva = '$this->pcNroEva' ORDER BY cCodPer, cCodNor, cEvaCon");
      $i = 0;
      while ($laFila = $p_oSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($laFila[0], $laFila[1], $laFila[2], trim($laFila['CCODNOR']).'-'.$laFila['CDESCRI'], $laFila[3]);
      }
      return true;
   }

   // Aplica numero de evaluacion
   public function omAplicarConduccionTrenes() {
      if (empty($this->pcNroEva)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Datos de cabecera
      $llOk = $this->mxAplicar($loSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
      // Carga items evaluados
      $llOk = $this->mxItemsEvaluados($loSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
      // Carga personal evaluado
      $llOk = $this->mxPersonalEvaluado($loSql);
      $loSql->omDisconnect();
      return $llOk;
   }
   
   protected function mxItemsEvaluados($p_oSql) {
      $RS = $p_oSql->omExec("SELECT A.cEvaCon, B.cDescri, A.cResult FROM S02DEVA A LEFT OUTER JOIN V_S02TTAB B
                             ON A.cEvaCon = B.cCodigo AND B.cCodTab = '026' WHERE cNroEva = '$this->pcNroEva' ORDER BY cEvaCon"); 
      $i = 0;
      while ($laFila = $p_oSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($i, $laFila[0], $laFila[1], $laFila[2]);
      }
      return true;
   }

   protected function mxAplicar($p_oSql) {
      // Carga Evaluacion
      $lcSql="SELECT TO_CHAR(dFecEva, 'yyyy-mm-dd'), cHorEva, cKilome, cTren, cCompos, cSubDiv, TRIM(cEstaci) AS CESTACI, cTipVag, cSuperv, cCodUn1, cCodUn2, cCodUn3, cKilomf, TRIM(cEstacf), cHorSer, cVelAut, cVelReg, cRestri, cSuper2, cCodEmp, cCodPry, cConCli, cResult, mObserv FROM S02MEVA WHERE cNroEva = '$this->pcNroEva'";
	  $RS = $p_oSql->omExec($lcSql);
      
	  $laFila = $p_oSql->fetch($RS);
      $this->pcFecEva = $laFila[0];
      if (empty($this->pcFecEva)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcHorEva = $laFila[1];
      $this->pcKilome = $laFila[2];
      $this->pcTren   = $laFila[3];
      $this->pcCompos = $laFila[4];
      $this->pcSubDiv = $laFila[5];
      $this->pcEstaci = $laFila[6];
      $this->pcTipVag = $laFila[7];
      $this->pcSuperv = $laFila[8];
      $this->pcCodUn1 = $laFila[9];
      $this->pcCodUn2 = $laFila[10];
      $this->pcCodUn3 = $laFila[11];
      $this->pcKilomf = $laFila[12];
      $this->pcEstacf = $laFila[13];
      $this->pcHorSer = $laFila[14];
      $this->pcVelAut = $laFila[15];
      $this->pcVelReg = $laFila[16];
      $this->pcRestri = $laFila[17];
      $this->pcSuper1 = $laFila[18];
      $this->pcCodEmp = $laFila[19];
      $this->pcCodPry = $laFila[20];
      $this->pcConCli = $laFila[21];
      $this->pcResult = $laFila[22];
      $this->pmObserv = $laFila[23]->load();
      return true;
   }
 
   public function omAplicarRadar() {
      if (empty($this->pcNroEva)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Datos de cabecera
      $llOk = $this->mxAplicar($loSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
      // Carga items evaluados
      $llOk = $this->mxNormasEvaluadas($loSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
      // Carga personal evaluado
      $llOk = $this->mxPersonalEvaluado($loSql);
      $loSql->omDisconnect();
      return $llOk;
   }
 
   public function omAplicarConduccionTractos() {
      if (empty($this->pcNroEva)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Datos de cabecera
      $llOk = $this->mxAplicar($loSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
      // Carga items evaluados
      $llOk = $this->mxItemsEvaluadosTerrestres($loSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
      // Carga personal evaluado
      $llOk = $this->mxPersonalEvaluado($loSql);
      $loSql->omDisconnect();
      return $llOk;
   }

   protected function mxItemsEvaluadosTerrestres($p_oSql) {
      $RS = $p_oSql->omExec("SELECT A.cEvaCon, B.cDescri, A.cResult FROM S02DEVA A LEFT OUTER JOIN V_S02TTAB B
                             ON A.cEvaCon = B.cCodigo AND B.cCodTab = '036' WHERE cNroEva = '$this->pcNroEva' ORDER BY cEvaCon"); 
      $i = 0;
      while ($laFila = $p_oSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($i, $laFila[0], $laFila[1], $laFila[2]);
      }
      return true;
   }
   
   // Graba prueba de eficiencia de conduccion
   public function omGrabarPruebaConduccion() {
      $this->pcKilome = trim($this->pcKilome);
      $this->pcKilomf = trim($this->pcKilomf);
      $this->pcCompos = trim($this->pcCompos);
      if (empty($this->paData) or count($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>DATOS DE ITEMS DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (!fxValDate($this->pcFecEva)) {
         $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE EVALUACION ['.$this->pcFecEva.']</ERROR></DATA>';
         return false;
      } else if (!fxValTime($this->pcHorEva)) {
         $this->pcError = '<DATA><ERROR>ERROR EN HORA DE PRUEBA-EVALUACION ['.$this->pcHorEva.']</ERROR></DATA>';
         return false;
      } else if ($this->pcEstaci == '99' and (!is_numeric(trim($this->pcKilome)))) {
         $this->pcError = '<DATA><ERROR>KILOMETRAJE NO ES NUMERICO</ERROR></DATA>';
         return false;
      } else if ($this->pcEstaci == '99' and (!(floatval($this->pcKilome) >= 0 and floatval($this->pcKilome) <= 999999.999))) {
         $this->pcError = '<DATA><ERROR>KILOMETRAJE NO ESTA EN RANGO PERMITIDO</ERROR></DATA>';
         return false;
      } else if ($this->pcEstacf == '99' and (empty($this->pcKilomf))) {
         $this->pcError = '<DATA><ERROR>KILOMETRAJE FINAL NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if ($this->pcEstacf == '99' and (!is_numeric(trim($this->pcKilomf)))) {
          $this->pcError = '<DATA><ERROR>KILOMETRAJE FINAL NO ES NUMERICO</ERROR></DATA>';
          return false;
      } else if ($this->pcEstacf == '99' and (!(floatval($this->pcKilomf) >= 0 and floatval($this->pcKilomf) <= 999999.999))) {
          $this->pcError = '<DATA><ERROR>KILOMETRAJE FINAL NO ESTA EN RANGO PERMITIDO</ERROR></DATA>';
          return false;
//      } else if ($this->pcKilome == $this->pcKilomf) {
//          $this->pcError = '<DATA><ERROR>ERROR: KILOMETRAJE (PK) INICIAL ES IGUAL A KILOMETRAJE FINAL</ERROR></DATA>';
//          return false;
      } else if (empty($this->pcTren)) {
          $this->pcError = '<DATA><ERROR>TREN NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcEstaci)) {
          $this->pcError = '<DATA><ERROR>ESTACION INICIAL NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (empty($this->pcEstacf)) {
          $this->pcError = '<DATA><ERROR>ESTACION FINAL NO DEFINIDA</ERROR></DATA>';
          return false;
//      } else if ($this->pcEstaci == $this->pcEstacf) {
//          $this->pcError = '<DATA><ERROR>ERROR: ESTACION INICIAL ES IGUAL ESTACION FINAL</ERROR></DATA>';
//          return false;
      } else if (!fxValTime($this->pcHorSer)) {
          $this->pcError = '<DATA><ERROR>HORAS DE SERVICIOS INVALIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcResult)) {
          $this->pcError = '<DATA><ERROR>RESULTADO NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcTipVag)) {
          $this->pcError = '<DATA><ERROR>TIPO DE VAGON NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcSuperv)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE SUPERVISOR NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodUn1)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD 1 NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUn2)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD 2 NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUn3)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD 3 NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL EVALUADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pmObserv)) {
          $this->pcError = '<DATA><ERROR>NO HA DEFINIDO OBSERVACIONES</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCompos)) {
         $this->pcError = '<DATA><ERROR>COMPOSICION NO DEFINIDA</ERROR></DATA>';
         return false;
      } else if (!$this->mxValCompos()) {
         $this->pcError = '<DATA><ERROR>ESTRUCTURA DE COMPOSICION ERRADA. REVISE</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (!$this->plNuevo and empty($this->pcNroEva)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
     // Valida PK de estacion
      if ($this->pcEstaci != '99') {
         $this->pcKilome = $this->pcEstaci;
      }
      if ($this->pcEstacf != '99') {
         $this->pcKilomf = $this->pcEstacf;
      }
      if (!$this->plNuevo) {
         $RS = $loSql->omExec("SELECT cNroEva FROM S02MEVA WHERE cNroEva = '$this->pcNroEva'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO EXISTE</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $lcSql = "UPDATE S02MEVA SET dFecEva = TO_DATE('$this->pcFecEva', 'yyyy-mm-dd'), cHorEva = '$this->pcHorEva', cHorSer = '$this->pcHorSer', cKilome = '$this->pcKilome', cKilomf = '$this->pcKilomf', cTren = UPPER('$this->pcTren'), cEstaci = '$this->pcEstaci', cEstacf = '$this->pcEstacf', cResult = '$this->pcResult', cTipVag = '$this->pcTipVag', cSuperv = '$this->pcSuperv', cCodUn1 = '$this->pcCodUn1', cCodUn2 = '$this->pcCodUn2', cCodUn3 = '$this->pcCodUn3', mObserv = '$this->pmObserv', cCompos = '$this->pcCompos', cCodEmp = '$this->pcCodEmp', cSubDiv = '$this->pcSubDiv', cCodPry = '$this->pcCodPry', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cNroEva = '$this->pcNroEva'"; 
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR EVALUACION</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM S02DEVA WHERE cNroEva = '$this->pcNroEva'");
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR NORMAS INCUMPLIDAS ANTIGUAS</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      } else {
         $RS = $loSql->omExec("SELECT MAX(cNroEva) FROM S02MEVA WHERE cNroEva LIKE 'C%'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $lcNroEva = '00000';
         } else {
            $lcNroEva = substr($laFila[0], 1);
         }
         $lnNroEva = (int)$lcNroEva + 1;
         $lcNroEva = (string)$lnNroEva;
         $this->pcNroEva = 'C'.right('0000'.trim($lcNroEva), 5);
         $lcSql = "INSERT INTO S02MEVA (cNroEva, dFecEva, cHorEva, cHorSer, cKilome, cKilomf, cTren, cCompos, cSubDiv, cEstaci, cEstacf, cResult, cTipVag, cSuperv, cCodUn1, cCodUn2, cCodUn3, cCodEmp, cCodPry, mObserv, cSuper2, cCodUsu) VALUES ('$this->pcNroEva', TO_DATE('$this->pcFecEva', 'yyyy-mm-dd'), '$this->pcHorEva', '$this->pcHorSer', '$this->pcKilome', '$this->pcKilomf', UPPER('$this->pcTren'), '$this->pcCompos', '$this->pcSubDiv', '$this->pcEstaci', '$this->pcEstacf', '$this->pcResult', '$this->pcTipVag', '$this->pcSuperv', '$this->pcCodUn1', '$this->pcCodUn2', '$this->pcCodUn3', '$this->pcCodEmp', '$this->pcCodPry', '$this->pmObserv', '999999', '$this->pcCodUsu')";
		 $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INGRESAR PRUEBA-EVALUACION</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      }
      foreach($this->paData as $i) {
         $lcSql = "INSERT INTO S02DEVA (cNroEva, cCodPer, cPuesto, cCodNor, cEvaCon, cResult, cCodUsu) VALUES  
                 ('$this->pcNroEva', '$this->pcCodPer', 'M', '000000000000', '$i[1]', '$i[3]', '$this->pcCodUsu')";
		 $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR ITEMS EVALUADOS</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      }
      $loSql->omDisconnect();
      return true;
   }

   // Graba prueba de eficiencia de conduccion de tractos
   public function omGrabarPruebaConduccionTractos() {
      $this->pcKilome = trim($this->pcKilome);
      $this->pcKilomf = trim($this->pcKilomf);
      $this->pcCompos = trim($this->pcCompos);
      if (empty($this->paData) or count($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>DATOS DE ITEMS DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (!fxValDate($this->pcFecEva)) {
         $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE EVALUACION ['.$this->pcFecEva.']</ERROR></DATA>';
         return false;
      } else if (!fxValTime($this->pcHorEva)) {
         $this->pcError = '<DATA><ERROR>ERROR EN HORA DE PRUEBA-EVALUACION ['.$this->pcHorEva.']</ERROR></DATA>';
         return false;
      } else if ($this->pcEstaci == '99' and (empty($this->pcKilome))) {
         $this->pcError = '<DATA><ERROR>KILOMETRAJE NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if ($this->pcEstaci == '99' and (!is_numeric(trim($this->pcKilome)))) {
         $this->pcError = '<DATA><ERROR>KILOMETRAJE NO ES NUMERICO</ERROR></DATA>';
         return false;
      } else if ($this->pcEstaci == '99' and (!(floatval($this->pcKilome) >= 0 and floatval($this->pcKilome) <= 999999.999))) {
         $this->pcError = '<DATA><ERROR>KILOMETRAJE NO ESTA EN RANGO PERMITIDO</ERROR></DATA>';
         return false;
      } else if ($this->pcEstacf == '99' and (empty($this->pcKilomf))) {
         $this->pcError = '<DATA><ERROR>KILOMETRAJE FINAL NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if ($this->pcEstacf == '99' and (!is_numeric(trim($this->pcKilomf)))) {
         $this->pcError = '<DATA><ERROR>KILOMETRAJE FINAL NO ES NUMERICO</ERROR></DATA>';
         return false;
      } else if ($this->pcEstacf == '99' and (!(floatval($this->pcKilomf) >= 0 and floatval($this->pcKilomf) <= 999999.999))) {
         $this->pcError = '<DATA><ERROR>KILOMETRAJE FINAL NO ESTA EN RANGO PERMITIDO</ERROR></DATA>';
         return false;
      //} else if ($this->pcKilome == $this->pcKilomf) {
       //  $this->pcError = '<DATA><ERROR>ERROR: KILOMETRAJE (PK) INICIAL ES IGUAL A KILOMETRAJE FINAL</ERROR></DATA>';
        // return false;
      } else if (empty($this->pcTren)) {
         $this->pcError = '<DATA><ERROR>TREN NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcEstaci)) {
         $this->pcError = '<DATA><ERROR>ESTACION INICIAL NO DEFINIDA</ERROR></DATA>';
         return false;
      } else if (empty($this->pcEstacf)) {
         $this->pcError = '<DATA><ERROR>ESTACION FINAL NO DEFINIDA</ERROR></DATA>';
         return false;
      //} else if ($this->pcEstaci == $this->pcEstacf) {
       //  $this->pcError = '<DATA><ERROR>ERROR: ESTACION INICIAL ES IGUAL ESTACION FINAL</ERROR></DATA>';
       //  return false;
      } else if (!fxValTime($this->pcHorSer)) {
         $this->pcError = '<DATA><ERROR>HORAS DE SERVICIOS INVALIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcResult)) {
         $this->pcError = '<DATA><ERROR>RESULTADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcTipVag)) {
         $this->pcError = '<DATA><ERROR>TIPO DE VAGON NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcSuperv)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE SUPERVISOR NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUn1)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD 1 NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUn2)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD 2 NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUn3)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD 3 NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodOpe)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL EVALUADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pmObserv)) {
         $this->pcError = '<DATA><ERROR>NO HA DEFINIDO OBSERVACIONES</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCompos)) {
         $this->pcError = '<DATA><ERROR>COMPOSICION NO DEFINIDA</ERROR></DATA>';
         return false;
      } else if (!$this->mxValCompos()) {
         $this->pcError = '<DATA><ERROR>ESTRUCTURA DE COMPOSICION ERRADA. REVISE</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if ($this->pcSuperv == $this->pcCodOpe) {
         $this->pcError = '<DATA><ERROR>NO SE PUEDE REPETIR SUPERVISOR CON OPERADOR </ERROR></DATA>';
         return false;
      } else if (!$this->plNuevo and empty($this->pcNroEva)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Valida PK de estacion
      if ($this->pcEstaci != '99') {
         $this->pcKilome = $this->pcEstaci;
      }
      if ($this->pcEstacf != '99') {
         $this->pcKilomf = $this->pcEstacf;
      }
      if (!$this->plNuevo) {
         $RS = $loSql->omExec("SELECT cNroEva FROM S02MEVA WHERE cNroEva = '$this->pcNroEva'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO EXISTE</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $lcSql = "UPDATE S02MEVA SET dFecEva = TO_DATE('$this->pcFecEva', 'yyyy-mm-dd'), cHorEva = '$this->pcHorEva', cHorSer = '$this->pcHorSer', cKilome = '$this->pcKilome', cKilomf = '$this->pcKilomf', cTren = UPPER('$this->pcTren'), cEstaci = '$this->pcEstaci', cEstacf = '$this->pcEstacf', cResult = '$this->pcResult', cTipVag = '$this->pcTipVag', cSuperv = '$this->pcSuperv', cSuper2 = '999999', cCodUn1 = '$this->pcCodUn1', cCodUn2 = '$this->pcCodUn2', cCodUn3 = '$this->pcCodUn3', mObserv = '$this->pmObserv', cCompos = '$this->pcCompos', cCodEmp = '$this->pcCodEmp', cCodPry = '$this->pcCodPry',cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cNroEva = '$this->pcNroEva'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR EVALUACION</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM S02DEVA WHERE cNroEva = '$this->pcNroEva'");
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR NORMAS INCUMPLIDAS ANTIGUAS</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      } else {
         $RS = $loSql->omExec("SELECT MAX(cNroEva) FROM S02MEVA WHERE cNroEva LIKE 'T%'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $lcNroEva = '00000';
         } else {
            $lcNroEva = substr($laFila[0], 1);
         }
         $lnNroEva = (int)$lcNroEva + 1;
         $lcNroEva = (string)$lnNroEva;
         $this->pcNroEva = 'T'.right('0000'.trim($lcNroEva), 5);
         $lcSql = "INSERT INTO S02MEVA (cNroEva, dFecEva, cHorEva, cHorSer, cKilome, cKilomf, cTren, cCompos, cSubDiv, cEstaci, cEstacf, cResult, cTipVag, cSuperv, cSuper2, cCodUn1, cCodUn2, cCodUn3, cCodEmp, cCodPry, mObserv, cCodUsu) VALUES ('$this->pcNroEva', TO_DATE('$this->pcFecEva', 'yyyy-mm-dd'), '$this->pcHorEva', '$this->pcHorSer', '$this->pcKilome', '$this->pcKilomf', UPPER('$this->pcTren'), '$this->pcCompos', '$this->pcSubDiv', '$this->pcEstaci', '$this->pcEstacf', '$this->pcResult', '$this->pcTipVag', '$this->pcSuperv', '999999', '$this->pcCodUn1', '$this->pcCodUn2', '$this->pcCodUn3', '$this->pcCodEmp', '$this->pcCodPry', '$this->pmObserv', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INGRESAR PRUEBA-EVALUACION</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      }
      foreach($this->paData as $i) {
         $lcSql = "INSERT INTO S02DEVA (cNroEva, cCodPer, cPuesto, cCodNor, cEvaCon, cResult, cCodUsu) VALUES 
                   ('$this->pcNroEva', '$this->pcCodOpe', 'M', '000000000000', '$i[1]', '$i[3]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR ITEMS EVALUADOS</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      }
      $loSql->omDisconnect();
      return true;
   }

   // Graba prueba de velocidad por radar
   public function omGrabarPruebaRadar() {
      $this->pcKilome = trim($this->pcKilome);
      if (empty($this->paDataP) or count($this->paDataP) == 0) {
         $this->pcError = '<DATA><ERROR>DATOS DE PERSONAL EVALUADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (!fxValDate($this->pcFecEva)) {
         $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE EVALUACION ['.$this->pcFecEva.']</ERROR></DATA>';
         return false;
      } else if (!fxValTime($this->pcHorEva)) {
         $this->pcError = '<DATA><ERROR>ERROR EN HORA DE PRUEBA-EVALUACION ['.$this->pcHorEva.']</ERROR></DATA>';
         return false;
      }else if (empty($this->pcCodEmp)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE EMPRESA NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodPry)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE PROYECTO NO DEFINIDO</ERROR></DATA>';
          return false;
      }  else if ($this->pcEstacf == '99' and (!is_numeric($this->pcKilome))) {
         $this->pcError = '<DATA><ERROR>KILOMETRAJE (PK) NO ES NUMERICO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcTren)) {
         $this->pcError = '<DATA><ERROR>TREN NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcResult)) {
         $this->pcError = '<DATA><ERROR>RESULTADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcTipVag)) {
         $this->pcError = '<DATA><ERROR>TIPO DE VAGON NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcSuperv)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PRIMER SUPERVISOR NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if ($this->pcSuperv == '999999') {
         $this->pcError = '<DATA><ERROR>PRIMER SUPERVISOR DEBE SER VALIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcSuper1)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE SEGUNDO SUPERVISOR NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if ($this->pcSuperv == $this->pcSuper1) {
         $this->pcError = '<DATA><ERROR>SUPERVISORES NO PUEDEN REPETIRSE</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUn1)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PRIMERA UNIDAD NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUn2)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE SEGUNDA UNIDAD NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUn3)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE TERCERA UNIDAD NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCompos)) {
          $this->pcError = '<DATA><ERROR>COMPOSICION NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (!$this->mxValCompos()) {
         $this->pcError = '<DATA><ERROR>ESTRUCTURA DE COMPOSICION INVALIDA</ERROR></DATA>';
         return false;
      } else if (empty($this->pcSubDiv)) {
         $this->pcError = '<DATA><ERROR>SUBDIVISION NO DEFINIDA</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCondic)) {
         $this->pcError = '<DATA><ERROR>CONDICI?N DE V??A NO DEFINIDAS</ERROR></DATA>';
         return false;
      } else if (empty($this->pcConCli)) {
         $this->pcError = '<DATA><ERROR>CONDICI?N CLIM??TICA NO DEFINIDA</ERROR></DATA>';
         return false;
      } else if (empty($this->pcVelAut)) {
         $this->pcError = '<DATA><ERROR>VELOCIDAD M??XIMA AUTORIZADA NO DEFINIDA</ERROR></DATA>';
         return false;
      } else if (empty($this->pcVelReg)) {
         $this->pcError = '<DATA><ERROR>VELOCIDAD REGISTRADA NO DEFINIDA</ERROR></DATA>';
         return false;
      } else if (!($this->pcRestri == 'S' OR $this->pcRestri == 'N')) {
         $this->pcError = '<DATA><ERROR>ERROR: RESTRICCION DEBE SER [S] O [N]</ERROR></DATA>';
         return false;
      } else if (!$this->mxValVelocidad($this->pcVelAut, 1)) {
         return false;
      } else if (!$this->mxValVelocidad($this->pcVelReg, 2)) {
          return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pmObserv)) {
         $this->pcError = '<DATA><ERROR>CAMPO DE OBSERVACIONES NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (!$this->plNuevo and empty($this->pcNroEva)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $lnKilome = (float)$this->pcKilome;
      if ($lnKilome < 0 or $lnKilome > 999999.999) {
         $this->pcError = '<DATA><ERROR>KILOMETRAJE (PK) NO ESTA EN RANGO PERMITIDO [0, 999999.999]</ERROR></DATA>';
         return false;
      }
      // Elimina no personal
      $laDataP = null;
      $j = 0;
      foreach($this->paDataP as $i) {
         if ($i[1] != '999999') {
            $j++;
            $laDataP[$j] = array($i[0], $i[1], $i[2]);
         }
      }
      if (count($laDataP) == 0) {
         $this->pcError = '<DATA><ERROR>DATOS DE PERSONAL EVALUADO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
     // Evalua personal repetido
      $llOk = fxValRepeated($laDataP, 2);
      if ($llOk) {
         $this->pcError = '<DATA><ERROR>PUESTO DEL PERSONAL EVALUADO REPETIDO. REVISE</ERROR></DATA>';
         return false;
      }
      // Verifica que supervisor no sea personal evaluado
      foreach($laDataP as $i) {
         if ($i[1] == $this->pcSuperv) {
            $this->pcError = '<DATA><ERROR>ERROR: SUPERVISOR ESTA SIENDO EVALUADO. REVISE</ERROR></DATA>';
            return false;
         }
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Valida PK de estacion
      if ($this->pcEstaci != '99') {
         $this->pcKilome = $this->pcEstaci;
      }
      if (!$this->plNuevo) {
         $RS = $loSql->omExec("SELECT cNroEva FROM S02MEVA WHERE cNroEva = '$this->pcNroEva'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO EXISTE</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $lcSql = "UPDATE S02MEVA SET dFecEva = TO_DATE('$this->pcFecEva', 'yyyy-mm-dd'), cHorEva = '$this->pcHorEva', cKilome = '$this->pcKilome', cTren = UPPER('$this->pcTren'), cResult = '$this->pcResult', cTipVag = '$this->pcTipVag', cSuperv = '$this->pcSuperv', cSuper2 = '$this->pcSuper1', cCodUn1 = '$this->pcCodUn1', cCodUn2 = '$this->pcCodUn2', cCodUn3 = '$this->pcCodUn3', cCondic = '$this->pcCondic', cConCli = '$this->pcConCli', cVelAut = '$this->pcVelAut', cVelReg = '$this->pcVelReg', cRestri = '$this->pcRestri', cCodEmp = '$this->pcCodEmp', cCodPry = '$this->pcCodPry', mObserv = '$this->pmObserv', cSubDiv = '$this->pcSubDiv', cEstaci = '$this->pcEstaci', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cNroEva = '$this->pcNroEva'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR EVALUACION</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM S02DEVA WHERE cNroEva = '$this->pcNroEva'");
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR NORMAS INCUMPLIDAS ANTIGUAS</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      } else {
         $RS = $loSql->omExec("SELECT MAX(cNroEva) FROM S02MEVA WHERE cNroEva LIKE 'R%'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $lcNroEva = '00000';
         } else {
            $lcNroEva = substr($laFila[0], 1);
         }
         $lnNroEva = (int)$lcNroEva + 1;
         $lcNroEva = (string)$lnNroEva;
         $this->pcNroEva = 'R'.right('0000'.trim($lcNroEva), 5);
         $lcSql = "INSERT INTO S02MEVA (cNroEva, dFecEva, cHorEva, cHorSer, cKilome, cTren, cCompos, cSubDiv, cResult, cTipVag, cSuperv, cSuper2, cCodUn1, cCodUn2, cCodUn3, cCondic, cConCli, cVelAut, cVelReg, cRestri, cCodEmp, cCodPry, mObserv,cEstaci, cCodUsu) VALUES ('$this->pcNroEva', TO_DATE('$this->pcFecEva', 'yyyy-mm-dd'), '$this->pcHorEva', '*', '$this->pcKilome', UPPER('$this->pcTren'), '$this->pcCompos', '$this->pcSubDiv', '$this->pcResult', '$this->pcTipVag', '$this->pcSuperv', '$this->pcSuper1', '$this->pcCodUn1', '$this->pcCodUn2', '$this->pcCodUn3', '$this->pcCondic', '$this->pcConCli', '$this->pcVelAut', '$this->pcVelReg', '$this->pcRestri', '$this->pcCodEmp', '$this->pcCodPry', '$this->pmObserv', '$this->pcEstaci', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INGRESAR PRUEBA-EVALUACION DE CONTROL DE VELOCIDAD POR RADAR</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      }
      foreach($laDataP as $i) {
         $lcSql = "INSERT INTO S02DEVA (cNroEva, cCodPer, cPuesto, cCodNor, cEvaCon, cResult, cCodUsu) VALUES ('$this->pcNroEva', '$i[1]', '$i[2]', '000000000000', '**', '$this->pcResult','$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR NORMAS INCUMPLIDAS</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      } 
      $loSql->omDisconnect();
      return true;
   }

   // Buscar evaluaciones
   public function omBuscarEvaluacion() {
      if (empty($this->pcEvaTip)) {
         $this->pcError = '<DATA><ERROR>TIPO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecIni)) {
         $this->pcError = '<DATA><ERROR>FECHA DE INICIO INVALIDA</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecFin)) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif ($this->pdFecIni > $this->pdFecFin) {
         $this->pcError = '<DATA><ERROR>FECHA INICIAL MAYOR QUE FECHA FINAL</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcEvaTip = trim($this->pcEvaTip).'%';
      $lcSql = "SELECT cNroEva, TO_CHAR(dFecEva, 'yyyy-mm-dd'), cHorEva FROM S02MEVA 
                WHERE dFecEva BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd') AND
                cNroEva LIKE '$lcEvaTip' ORDER BY dFecEva, cHorEva DESC";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $RS1 = $loSql->omExec("SELECT A.cPuesto, B.cNombre FROM S02DEVA A INNER JOIN S02MPER B ON A.cCodPer = B.cCodPer WHERE A.cNroEva = '$laFila[0]' AND SUBSTR(A.cCodPer, 1, 1) != '9'");
         $lcNombre = '*** ERROR ***';
         while ($j = $loSql->fetch($RS1)) {
            if ($j[0] == 'M') {
               $lcNombre = $j[1];
               break;
            } else {
               $lcNombre = $j[1];
            }
         }
         $this->paData[] = array($laFila[0], $laFila[1], $laFila[2], $lcNombre);
         $i++;
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY EVALUACIONES REGISTRADAS</ERROR></DATA>';
         $llOk = false;
      }
      return $llOk;
   }

   // Valida la estructua de la composicion 99.99.999
   protected function mxValCompos() {
      $lcTmp = substr($this->pcCompos, 0, 2);
      if (!($lcTmp >= '00' and $lcTmp <= '99')) {
         return false;
      }
      $lcTmp = substr($this->pcCompos, 2, 1);
      if (!($lcTmp == '.')) {
         return false;
      }
      $lcTmp = substr($this->pcCompos, 3, 2);
      if (!($lcTmp >= '00' and $lcTmp <= '99')) {
         return false;
      }
      $lcTmp = substr($this->pcCompos, 5, 1);
      if (!($lcTmp == '.')) {
         return false;
      }
      $lcTmp = substr($this->pcCompos, 6, 3);
      if (!($lcTmp >= '0000' and $lcTmp <= '9999')) {
         return false;
      }
      return true;
   }

   protected function mxValVelocidad($p_cVeloci, $p_nTipo) {
      if (empty($p_cVeloci)) {
         if ($p_nTipo == 1)
            $this->pcError = '<DATA><ERROR>VELOCIDAD AUTORIZADA NO DEFINIDA</ERROR></DATA>';
         else
            $this->pcError = '<DATA><ERROR>VELOCIDAD REGISTRADA NO DEFINIDA</ERROR></DATA>';
         return false;
      } if (!is_numeric(trim($p_cVeloci))) {
         if ($p_nTipo == 1)
            $this->pcError = '<DATA><ERROR>VELOCIDAD AUTORIZADA NO ES NUMERICO</ERROR></DATA>';
         else
            $this->pcError = '<DATA><ERROR>VELOCIDAD REGISTRADA NO ES NUMERICO</ERROR></DATA>';
         return false;
      } else if (!(floatval($p_cVeloci) >= 0 and floatval($p_cVeloci) <= 999.99)) {
         if ($p_nTipo == 1)
            $this->pcError = '<DATA><ERROR>VELOCIDAD AUTORIZADA NO ESTA EN RANGO PERMITIDO</ERROR></DATA>';
         else
            $this->pcError = '<DATA><ERROR>VELOCIDAD REGISTRADA NO ESTA EN RANGO PERMITIDO</ERROR></DATA>';
         return false;
       }
       return true;
   }
}
?>
