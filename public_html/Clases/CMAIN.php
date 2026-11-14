<?php
require_once "Clases/CBase.php";

//------------------------------------------------------------------------------------------
// Registro de Pruebas de eficiencia en terreno, conduccion de trenes y velocidad por radar
//------------------------------------------------------------------------------------------
class CMAIN extends CBase {
   public $paSubDiv = null;
   public $paEstaci = null;
   public $paTipVag = null;
   public $paResult = null;
   public $paSuperv = null;
   public $paCodUni = null;   
   public $paPuesto = null;
   public $paCodNor = null;
   public $paDataP  = null;
   public $pcFecEva = null;
   public $pcHorEva = null;
   public $pcKilome = null;
   public $pcTren   = null;
   public $pcCompos = null;
   public $pcSubDiv = null;
   public $pcEstaci = null;
   public $pcResult = null;
   public $pcTipVag = null;
   public $pcSuperv = null;
   public $pcCodUni = null;
   public $pcCodAin = null;
   public $pcCodUsu = null;
   public $paNroEva = null;
   public $pcEvaTip = null;

//
   public $paEstado      = null;
   public $pcEstado      = null;
   public $pcCodPer      = null;
   public $pcCodSan      = null;
   public $pcPerEmp      = null;
   public $paCodPer      = null;
   public $paCodSan      = null;
   public $paPerEmp      = null;
   public $pdFecIni      = null;
   public $pdFecha       = null;
   public $pdCese        = null;
   public $pcNombre      = null;
   public $pcDireccion   = null;
   public $pcSexo        = null;
   public $paSexo        = null;
   public $paTipo        = null;
   public $pcTipo        = null;
   public $pmDatos       = null;
   public $paIndex       = null;
   public $paData        = null;
   public $paNombre      = null;
   public $paCodEmp      = null;
   public $plNuevo       = false;
   public $paEstCiv      = null;
   public $paCargo       = null;
   
   public function __construct() {
      parent::__construct();
      $this->paSubDiv = null;
      $this->paEstaci = null;
      $this->paTipVag = null;
      $this->paResult = null;
      $this->paSuperv = null;
      $this->paCodUni = null;
      $this->paCodNor = null;
      $this->paDataP  = null;
      $this->pcCodAin = '';
      $this->pcFecEva = null;
      $this->pcHorEva = null;
      $this->pcKilome = null;
      $this->pcTren   = null;
      $this->pcCompos = null;
      $this->pcSubDiv = null;
      $this->pcEstaci = null;
      $this->pcResult = null;
      $this->pcTipVag = null;
      $this->pcSuperv = null;
      $this->pcCodUni = null;
      $this->pcCodUsu = null;
      $this->paNroEva = null;
   }

   // Inicia mantenimiento de empresas
   public function omInitPruebas($p_nTipo = 1) {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga CUASA
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE cCodTab = '009'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
        $i++;
         $this->paCausa[] = array($laFila[0], $laFila[1]);
      }
     if ($i == 0) {
        $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY SUBDIVISIONES DEFININDAS. TABLA [020]</ERROR></DATA>';
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
         $this->pcError = '<DATA><ERROR>NO HAY SUBDIVISIONES DEFININDAS. TABLA [020]</ERROR></DATA>';
       return false;
     }
     // Carga tipo de accidente
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE cCodTab = '006'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
        $i++;
         $this->paTipAcc[] = array($laFila[0], $laFila[1]);
      }
     if ($i == 0) {
        $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE ACCIDENTES DEFININDOS. TABLA [006]</ERROR></DATA>';
       return false;
     }
     // Carga Estaciones
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE cCodTab = '021'");
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
     // Carga Lugares
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '008'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
        $i++;
         $this->paLugar[] = array($laFila[0], $laFila[1]);
      }
     if ($i == 0) {
        $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY LUGARES DEFINIDOS. TABLA [008]</ERROR></DATA>';
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
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '003'");
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
        $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '023'");
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
           $i++;
           $this->paPuesto[] = array($laFila[0], $laFila[1]);
        }
	 }
     if ($i == 0) {
        $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PUESTOS DEFINIDOS. TABLA [022]</ERROR></DATA>';
       return false;
     }
     // Carga empresas
      $RS = $loSql->omExec("SELECT cCodEmp, cDescri FROM S02MEMP WHERE cEstado = 'A' ORDER BY cDescri");
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
     // Carga areas
      $RS = $loSql->omExec("SELECT cArea, cDescri FROM S02TARE WHERE cEstado = 'A' ORDER BY cDescri");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
        $i++;
         $this->paArea[] = array($laFila[0], $laFila[1]);
      }
     if ($i == 0) {
        $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY EMPRESAS DEFINIDAS</ERROR></DATA>';
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
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR WHERE cEstado = 'A' ORDER BY cDescri");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
        $i++;
         $this->paCodNor[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY UNIDADES DEFINIDAS</ERROR></DATA>';
         return false;
      }
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
     // Carga personal
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER ORDER BY cNombre");
      $i = 0;
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
      if (empty($this->paDataP) or count($this->paDataP) == 0) {
         $this->pcError = '<DATA><ERROR>DATOS DE PERSONAL EVALUADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->paData) or count($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>DATOS DE NORMAS INCUMPLIDAS NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (!fxValDate($this->pcFecEva)) {
         $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE EVALUACION ['.$this->pcFecEva.']</ERROR></DATA>';
         return false;
      } else if (!fxValTime($this->pcHorEva)) {
         $this->pcError = '<DATA><ERROR>ERROR EN HORA DE PRUEBA-EVALUACION ['.$this->pcHorEva.']</ERROR></DATA>';
         return false;
      } else if (empty($this->pcKilome)) {
          $this->pcError = '<DATA><ERROR>KILOMETRAJE NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcTren)) {
          $this->pcError = '<DATA><ERROR>TREN NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCompos)) {
          $this->pcError = '<DATA><ERROR>COMPOSICION NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (empty($this->pcSubDiv)) {
          $this->pcError = '<DATA><ERROR>SUBDIVISION NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcEstaci)) {
          $this->pcError = '<DATA><ERROR>ESTACION NO DEFINIDA</ERROR></DATA>';
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
      } else if (empty($this->pcCodUni)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD NO DEFINIDO</ERROR></DATA>';
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
      if (!$this->plNuevo) {
         $RS = $loSql->omExec("SELECT cNroEva FROM S02MEVA WHERE cNroEva = '$this->pcNroEva'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO EXISTE</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $lcSql = "UPDATE S02MEVA SET dFecEva = TO_DATE('$this->pcFecEva', 'yyyy-mm-dd'), cKilome = '$this->pcKilome', cTren = '$this->pcTren', cCompos = '$this->pcCompos', cSubDiv = '$this->pcSubDiv', cEstaci = '$this->pcEstaci', cResult = '$this->pcResult', cTipVag = '$this->pcTipVag', cSuperv = '$this->pcSuperv', cCodUni = '$this->pcCodUni', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cNroEva = '$this->pcNroEva'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR EVALUACION</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM S02DNOR WHERE cNroEva = '$this->pcNroEva'");
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR NORMAS INCUMPLIDAS ANTIGUAS</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM S02DPER WHERE cNroEva = '$this->pcNroEva'");
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR PERSONAL EVALUADO ANTIGUO</ERROR></DATA>';
            $loSql->omDisconnect();
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
         $lcSql = "INSERT INTO S02MEVA (cNroEva, dFecEva, cHorEva, cKilome, cTren, cCompos, cSubDiv, cEstaci, cResult, cTipVag, cSuperv, cCodUni, cCodUsu) VALUES ('$this->pcNroEva', TO_DATE('$this->pcFecEva', 'YYYY-MM-DD'), '$this->pcHorEva', '$this->pcKilome', '$this->pcTren', '$this->pcCompos', '$this->pcSubDiv', '$this->pcEstaci', '$this->pcResult', '$this->pcTipVag', '$this->pcSuperv', '$this->pcCodUni', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INGRESAR PRUEBA-EVALUACION</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      }
      foreach($this->paDataP as $i) {
         $lcSql = "INSERT INTO S02DPER (cNroEva, cCodPer, cPuesto, cCodUsu) VALUES ('$this->pcNroEva', '$i[1]', '$i[2]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR PERSONAL EVALUADO</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      }
      $loSql->omDisconnect();
      return true;
   }

   // Aplica numero de evaluacion
   public function omAplicar() {
      // Condiciones de excepcion
      if (empty($this->pcCodAin)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Evaluacion
      $RS = $loSql->omExec("SELECT TO_CHAR(dFecEva, 'yyyy-mm-dd'), cHorEva, cKilome, cTren, cCompos, cSubDiv, cEstaci, cResult, cTipVag, cCodEmp, cCodUni, cKilomf, cEstacf, cHorSer FROM S02MEVA WHERE cCodAin = '$this->pcCodAin'");
      $laFila = $loSql->fetch($RS);
      $this->pcFecEva = $laFila[0];
      if (empty($this->pcFecEva)) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcHorEva = $laFila[1];
      $this->pcKilome = $laFila[2];
      $this->pcTren   = $laFila[3];
      $this->pcCompos = $laFila[4];
      $this->pcSubDiv = $laFila[5];
      $this->pcEstaci = $laFila[6];
      $this->pcResult = $laFila[7];
      $this->pcTipVag = $laFila[8];
      $this->pcSuperv = $laFila[9];
      $this->pcCodUni = $laFila[10];
      $this->pcKilomf = $laFila[11];
      $this->pcEstacf = $laFila[12];
      $this->pcHorSer = $laFila[13];
      // Carga normas incumplidas
      $RS = $loSql->omExec("SELECT cCodNor FROM S02DNOR WHERE cNroEva = '$this->pcNroEva'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($i, $laFila[0]);
      }
      // Carga personal evaluado
      $RS = $loSql->omExec("SELECT cCodPer, cPuesto FROM S02DPER WHERE cNroEva = '$this->pcNroEva'");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paDataP[$i] = array($i, $laFila[0], $laFila[1]);
      }
      return true;
   }

   // Graba prueba de eficiencia de conduccion
   public function omGrabarPruebaConduccion() {
      if (empty($this->paDataP) or count($this->paDataP) == 0) {
         $this->pcError = '<DATA><ERROR>DATOS DE PERSONAL EVALUADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->paData) or count($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>DATOS DE NORMAS INCUMPLIDAS NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (!fxValDate($this->pcFecEva)) {
         $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE EVALUACION ['.$this->pcFecEva.']</ERROR></DATA>';
         return false;
      } else if (!fxValTime($this->pcHorEva)) {
         $this->pcError = '<DATA><ERROR>ERROR EN HORA DE PRUEBA-EVALUACION ['.$this->pcHorEva.']</ERROR></DATA>';
         return false;
      } else if (empty($this->pcKilome)) {
          $this->pcError = '<DATA><ERROR>KILOMETRAJE (PK) INICIAL NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcKilomf)) {
          $this->pcError = '<DATA><ERROR>KILOMETRAJE (PK) FINAL NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if ($this->pcKilome == $this->pcKilomf) {
          $this->pcError = '<DATA><ERROR>ERROR: KILOMETRAJE (PK) INICIAL ES IGUAL A KILOMETRAJE FINAL</ERROR></DATA>';
          return false;
      } else if (empty($this->pcTren)) {
          $this->pcError = '<DATA><ERROR>TREN NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcEstaci)) {
          $this->pcError = '<DATA><ERROR>ESTACION INICIAL NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (empty($this->pcEstacf)) {
          $this->pcError = '<DATA><ERROR>ESTACION FINAL NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if ($this->pcEstaci == $this->pcEstacf) {
          $this->pcError = '<DATA><ERROR>ERROR: ESTACION INICIAL ES IGUAL ESTACION FINAL</ERROR></DATA>';
          return false;
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
      } else if (empty($this->pcCodUni)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodUsu)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (!$this->plNuevo and empty($this->pcNroEva)) {
          $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
          return false;
      }
	  // Valida repeticiones en arreglos
      $llOk = fxValRepeated($this->paData, 1);
	  if ($llOk) {
          $this->pcError = '<DATA><ERROR>ERROR: HAY NORMAS REPETIDAS</ERROR></DATA>';
          return false;
	  }
      $llOk = fxValRepeated($this->paDataP, 2);
	  if ($llOk) {
          $this->pcError = '<DATA><ERROR>ERROR: PUESTO DE PERSONAL REPETIDO</ERROR></DATA>';
          return false;
	  }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      if (!$this->plNuevo) {
         $RS = $loSql->omExec("SELECT cNroEva FROM S02MEVA WHERE cNroEva = '$this->pcNroEva'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO EXISTE</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $lcSql = "UPDATE S02MEVA SET dFecEva = TO_DATE('$this->pcFecEva', 'yyyy-mm-dd'), cHorEva = '$this->pcHorEva', cHorSer = '$this->pcHorSer', cKilome = '$this->pcKilome', cKilomf = '$this->pcKilomf', cTren = UPPER('$this->pcTren'), cEstaci = '$this->pcEstaci', cEstacf = '$this->pcEstacf', cResult = '$this->pcResult', cTipVag = '$this->pcTipVag', cSuperv = '$this->pcSuperv', cCodUni = '$this->pcCodUni', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cNroEva = '$this->pcNroEva'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR EVALUACION</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM S02DNOR WHERE cNroEva = '$this->pcNroEva'");
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR NORMAS INCUMPLIDAS ANTIGUAS</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM S02DPER WHERE cNroEva = '$this->pcNroEva'");
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR PERSONAL EVALUADO ANTIGUO</ERROR></DATA>';
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
         $lcSql = "INSERT INTO S02MEVA (cNroEva, dFecEva, cHorEva, cHorSer, cKilome, cKilomf, cTren, cCompos, cSubDiv, cEstaci, cEstacf, cResult, cTipVag, cSuperv, cCodUni, cCodUsu) VALUES ('$this->pcNroEva', TO_DATE('$this->pcFecEva', 'yyyy-mm-dd'), '$this->pcHorEva', '$this->pcHorSer', '$this->pcKilome', '$this->pcKilomf', UPPER('$this->pcTren'), ' ', ' ', '$this->pcEstaci', '$this->pcEstacf', '$this->pcResult', '$this->pcTipVag', '$this->pcSuperv', '$this->pcCodUni', '$this->pcCodUsu')";
//		 die($lcSql);
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INGRESAR PRUEBA-EVALUACION</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      }
      foreach($this->paData as $i) {
         $lcSql = "INSERT INTO S02DNOR (cNroEva, cCodNor, cCodUsu) VALUES ('$this->pcNroEva', '$i[1]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR NORMAS INCUMPLIDAS</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      }
      foreach($this->paDataP as $i) {
         $lcSql = "INSERT INTO S02DPER (cNroEva, cCodPer, cPuesto, cCodUsu) VALUES ('$this->pcNroEva', '$i[1]', '$i[2]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR PERSONAL EVALUADO</ERROR></DATA>';
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
	  }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
	  $lcEvaTip = trim($this->pcEvaTip).'%';
      $lcSql = "SELECT cNroEva, TO_CHAR(dFecEva, 'yyyy-mm-dd'), cHorEva, cTren FROM S02MEVA WHERE cNroEva LIKE '$lcEvaTip' ORDER BY dFecEva, cHorEva DESC";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paNroEva[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY EVALUACIONES REGISTRADAS</ERROR></DATA>';
         $llOk = false;
      }
      return $llOk;
   }
   
//////////   
   // ?????????????
   public function initDetallePersonal(){
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Empleado
      $lcSql = "SELECT CCODPER, CNOMBRE FROM S02MPER WHERE cEstado = 'A' ORDER BY CNOMBRE";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i=0;
      
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i==0) {
         $this->pcError = 'NO HAY EMPLEADOS ACTIVOS';
         return false;
      }
      return true;
   }
   
   // Mantenimiento de puente cursos - normas
   public function omMtoPuentePersonal() {
      $this->paIndex = null;
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Curso
      $lcSql = "SELECT cNombre FROM S02MPER WHERE cCodPer = '$this->pcCodPer'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->pcNombre = $laFila[0];
      if ($this->pcNombre == null) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE EMPLEADO NO EXISTE</ERROR></DATA>';
         return false;
      }
      // Trae empresas en las que trabajan los empleados
      $lcSql = "SELECT cCodEmp, to_char(dFecIni,'yyyy-mm-dd'), dCese, mDatos FROM S02PPER  WHERE cCodPer = '$this->pcCodPer' ORDER BY cCodEmp";
      $RS = $loSql->omExec($lcSql);
      $i = 1;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paIndex[] = array($i, $laFila[0], $laFila[1], $laFila[2], $laFila[3]);
         $i++;
      }
      $this->paCodEmp = null;
      $lcSql = "SELECT cCodEmp, cDescri FROM S02MEMP WHERE cEstado = 'A' ORDER BY cDescri";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodEmp[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY EMPRESAS DEFINIDAS</ERROR></DATA>';
         $llOk = false;
      }
      return $llOk;
   }
   
   // Graba puente personal - empresa
   public function omGrabarPuentePersonal() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE EMPLEADO ESTA VACIO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (!isset($this->paPerEmp)) {
          $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA GRABAR</ERROR></DATA>';
          return false;
      }
      foreach($this->paPerEmp as $i) {
          if (empty($i[2])) {
             $this->pcError = '<DATA><ERROR>FECHA DE INICIO NO DEFINIDO</ERROR></DATA>';
             return false;
          } else if (!$this->mxValDate($i[2])) {
             $this->pcError = '<DATA><ERROR>FECHA DE INICIO INVALIDA</ERROR></DATA>';
             return false;
          } else if (empty($i[4])) {
             $this->pcError = '<DATA><ERROR>DESCRIPCION VACIA</ERROR></DATA>';
             return false;
          }
      }
      $finCese=count($this->paPerEmp)-1;
      if($finCese>=1){
          if (!empty($this->paPerEmp[$finCese][3])){
             $this->pcError = '<DATA><ERROR>ERROR </ERROR></DATA>';
             return false;
          }
      }
      if($finCese<0){
          $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA GRABAR</ERROR></DATA>';
          return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "DELETE FROM S02PPER WHERE cCodPer = '$this->pcCodPer'";
      //echo $lcSql;
      $loSql->omExec($lcSql);
      foreach($this->paPerEmp as $i) {
         if(empty($i[3])){
            $lcSql = "INSERT INTO S02PPER (cCodPer, cCodEmp, dFecIni, dCese, cEstado, mDatos, cCodUsu) VALUES ('$this->pcCodPer', '$i[1]', TO_DATE('$i[2]','yyyy/mm/dd'), NULL, 'A', '$i[4]','$this->pcCodUsu')";
            $loSql->omExec($lcSql);
         }
         else
         {
            $lcSql = "INSERT INTO S02PPER (cCodPer, cCodEmp, dFecIni, dCese, cEstado, mDatos, cCodUsu) VALUES ('$this->pcCodPer', '$i[1]', TO_DATE('$i[2]','yyyy/mm/dd'), TO_DATE('$i[3]','yyyy/mm/dd') , 'I', '$i[4]','$this->pcCodUsu')";
            $loSql->omExec($lcSql);   
         }
      }
      $loSql->omDisconnect();
      return true;
   }
   
   // Grabar Empleado
   public function omGrabarEmpleado() {
      if (empty($this->pcNombre)) {
         $this->pcError = '<DATA><ERROR>NOMBRE DEL EMPLEADO ESTA VACIO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcDireccion)) {
         $this->pcError = '<DATA><ERROR>DIRECCION DEL EMPLEADO NO DEFINIDA</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodPer) AND !$this->plNuevo) {
         $this->pcError = '<DATA><ERROR>CODIGO DE EMPLEADO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $this->pcNombre = strtoupper($this->pcNombre);
     $this->pcDireccion = strtoupper($this->pcDireccion);
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      if ($this->plNuevo) {
         $RS = $loSql->omExec('SELECT MAX(cCodPer) FROM S02MPER');
         $laFila = $loSql->fetch($RS);
         if ($laFila[0] == null)
            $lcCodPer = '000000';
         else
            $lcCodPer = $laFila[0] ;
         $lnCodPer = (int)$lcCodPer + 1;
         $lcCodPer = (string)$lnCodPer;
         $lcCodPer = right('00000'.trim($lcCodPer), 6);
         $lcSql = "INSERT INTO S02MPER (cCodPer, cNombre, cDirecc, cCargo, cSexo, cEstCiv, cEstado, cCodUsu) VALUES ('$lcCodPer', '$this->pcNombre','$this->pcDireccion', '$this->pcCargo', '$this->pcSexo', '$this->pcEstCiv' ,'$this->pcEstado', '$this->pcCodUsu')";
      } else {
         $RS = $loSql->omExec("SELECT cCodPer FROM S02MPER WHERE cCodPer = '$this->pcCodPer'");
         $laFila = $loSql->fetch($RS);
         if ($laFila[0] == null) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE EMPLEADO NO DEFINIDO</ERROR></DATA>';
            return false;
         }
         $lcSql = "UPDATE S02MPER SET cNombre = '$this->pcNombre', cDirecc = '$this->pcDireccion', cCargo = '$this->pcCargo', cSexo = '$this->pcSexo', cEstCiv = '$this->pcEstCiv', cEstado = '$this->pcEstado', cCodUsu = '$this->pcCodUsu', tModifi =(SELECT SYSDATE FROM DUAL)) WHERE cCodPer = '$this->pcCodPer'";
      }
      $llOk = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR EN GRABACIÓN DE PERSONAL</ERROR></DATA>';
      }
      return $llOk;
   }

   // Buscar empleados por nombre
   public function omBuscarEmpleados() {
      // Condiciones de excepcion
      if (empty($this->pcNombre)) {
         $this->pcError = '<DATA><ERROR>NOMBRE VACIO</ERROR></DATA>';
         return false;
      }
      $this->paCodPer = null;
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcNombre = trim($this->pcNombre).'%';
      $lcSql = "SELECT cCodPer, cNombre FROM S02MPER WHERE cNombre LIKE '$lcNombre' ORDER BY cNombre";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY EMPLEADOS QUE CUMPLAN CONDICION</ERROR></DATA>';
         $llOk = false;
      }
      return $llOk;
   } 
   
   public function omInitPersonalSancion(){
      $llOk = $this->omCargarMtoEmpleados();
      if (!$llOk) {
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Empleado
      $lcSql = "SELECT cCodPer, cNombre FROM S02MPER WHERE cEstado = 'A' ORDER BY CNOMBRE";
      $RS = $loSql->omExec($lcSql);
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
   
   // Busca persona para registrar sancion(es)
   public function omAplicarPersonalSancion() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE EMPLEADO VACIO</ERROR></DATA>';
         return false;
      }
      $llOk = $this->omCargarMtoEmpleados();
      if (!$llOk) {
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
     // Traer sanciones
      $this->paCodSan = null;
      $RS = $loSql->omExec("SELECT cCodSan, cDescri FROM S02TSAN ORDER BY cCodSan");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodSan[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY SANCIONES DEFINIDAS</ERROR></DATA>';
         return false;
      }
     // Normas
      $this->paCodNor = null;
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR WHERE cEstado = 'A' ORDER BY cDescri");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodNor[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY NORMAS DEFINIDAS</ERROR></DATA>';
         $llOk = false;
      }
      return $llOk;
   }   

   // Editar sancion de persona
   public function omEditarSancion() {
      if (empty($this->pcCodigo)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE SANCION VACIO</ERROR></DATA>';
         return false;
      }
      $llOk = $this->omCargarMtoEmpleados();
      if (!$llOk) {
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Normas
      $this->paData = null;
      $RS = $loSql->omExec("SELECT cCodNor FROM S02DSAN WHERE cCodigo = '$this->pcCodigo'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($i, $laFila[0]);
      }
      $RS = $loSql->omExec("SELECT cCodSan, cTipSan, cEstado, dFecha FROM S02PSAN WHERE cCodigo = '$this->pcCodigo'");
      $loSql->omDisconnect();
      $laFila = $loSql->fetch($RS);
     $this->pcCodSan = $laFila[0];
     $this->pcTipo   = $laFila[1];
     $this->pcEstado = $laFila[2];
     $this->pdFecha  = $laFila[3]; 
      return $llOk;
   }   
   
   public function omMtoPersonalSancion() {
      $llOk = $this->omAplicar();
      if (!$llOk) {
         return false;
      }
      $this->paData = null;
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Trae sanciones del empleado
     $lcSql = "SELECT cCodigo, cDescri, cTipSan, dFecha FROM V_S02PSAN WHERE cCodPer = '$this->pcCodPer' ORDER BY dFecha";
      $RS = $loSql->omExec($lcSql);
     $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
        $i++;
         $this->paData[$i] = array($i, $laFila[0], $laFila[1], $laFila[2], $laFila[3]);
      }
      return $llOk;
   }
   
   public function omGrabarPersonalSancion(){
      if (!$this->mxValDate($this->pdFecha)) {
         $this->pcError = '<DATA><ERROR>FECHA ESTA VACIA</ERROR></DATA>';
         return false;
     } else if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL VACIO</ERROR></DATA>';
         return false;
     } else if (empty($this->pcCodSan)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE SANCION VACIO</ERROR></DATA>';
         return false;
     }else if (empty($this->pcTipo)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE TIPO VACIO</ERROR></DATA>';
         return false;
     }else if (empty($this->pcEstado)) {
         $this->pcError = '<DATA><ERROR>ESTADO VACIO</ERROR></DATA>';
         return false;
     } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>USUARIO VACIO</ERROR></DATA>';
         return false;
     } else if (count($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY NORMAS PARA GRABAR</ERROR></DATA>';
         return false;
     } else if ($this->plNuevo and empty($this->pcCodigo)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE SANCION NO ESTA DEFINIDO</ERROR></DATA>';
         return false;
     }
     for ($i = 1; $i <= count($this->paData); $i++) {
          for ($j = $i + 1; $j <= count($this->paData); $j++) {
            if ($this->paData[$i][1] == $this->paData[$j][1]) {
                 $this->pcError = '<DATA><ERROR>NORMA ['.$this->paData[$j][1].'] ESTA REPETIDA</ERROR></DATA>';
                 return false;
           }
            }
     }
     
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
     if ($this->plNuevo) {
         $RS = $loSql->omExec('SELECT MAX(cCodigo) FROM S02PSAN');
         $laFila = $loSql->fetch($RS);
         if ($laFila[0] == null)
            $lcCodigo = '000001';
         else
            $lcCodigo = $laFila[0];
         $lnCodigo = (int)$lcCodigo + 1;
         $lcCodigo = (string)$lnCodigo;
         $lcCodigo = right('00000'.trim($lcCodigo), 6);
         $lcSql = "INSERT INTO S02PSAN (cCodigo, cCodPer, cCodSan, cTipSan, dFecha, cEstado, cCodUsu) VALUES ('$lcCodigo', '$this->pcCodPer', '$this->pcCodSan', '$this->pcTipo', TO_DATE('$this->pdFecha','yyyy/mm/dd'), '$this->pcEstado', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL GRABAR CABECERA DE SANCIONES [S02PSAN]</ERROR></DATA>';
            return false;
         }
     } else {
        $lcCodigo = $this->pcCodigo;
         $RS = $loSql->omExec("SELECT cCodigo FROM S02PSAN WHERE cCodigo = '$lcCodigo'");
         $laFila = $loSql->fetch($RS);
         if ($laFila[0] == null) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>NO EXISTE CODIGO DE SANCION ['.$lcCodigo.'] EN [S02PSAN]</ERROR></DATA>';
            return false;
         }
         $lcSql = "UPDATE S02PSAN SET cCodSan = '$this->pcCodSan', cTipSan = '$this->pcTipo', dFecha = '$this->pdFecha', cEstado = '$this->pcEstado' WHERE cCodigo = '$lcCodigo'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL DEPURAR SANCIONES ANTERIORES [S02DSAN]</ERROR></DATA>';
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM S02DSAN WHERE cCodigo = '$lcCodigo'");
         if (!$llOk) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL DEPURAR SANCIONES ANTERIORES [S02DSAN]</ERROR></DATA>';
            return false;
         }
     }
      foreach ($this->paData as $i) {
         $lcSql = "INSERT INTO S02DSAN (cCodigo, cCodNor, cCodUsu) VALUES ('$lcCodigo', '$i[1]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL GRABAR DETALLE DE NORMAS DE SANCION [S02DSAN]</ERROR></DATA>';
            break;
         }
      }
      $loSql->omDisconnect();
      return $llOk;
   }
   
//////////////////////////
   
   

   // Buscar activos fijos
   public function xomBuscarActivoFijo() {
      // Condiciones de excepcion
//      if (empty($this->pcCodAfj)) {
//         $this->pcError = 'CODIGO DE ACTIVO FIJO VACIO';
//         return false;
//      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cDescri, dFecUso FROM ADM.E03MAFJ WHERE cCodAfj = '$this->pcCodAfj' ORDER BY cDescri";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      //if OJOFPM
      $laFila = $loSql->fetch($RS);
      $this->pcDescri = $laFila[0];
      if (empty($this->pcDescri)) {
         $this->pcError = 'CODIGO DE ACTIVO FIJO NO EXISTE';
         $llOk = false;
      }
      $this->pdFecUso = $laFila[1];
      return $llOk;
   }
   
   public function omMtoActivFijo() {
      // Condiciones de excepcion
      if (empty($this->pcCodAfj)) {
         $this->pcError = 'CODIGO DE ACTIVO FIJO VACIO';
         return false;
      }
      $llOk = $this->omAplicarActivoFijo();
      if (!$llOk)
         return false;
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Trae tipo de relacion
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 3), cDescri FROM V_S01TTAB WHERE cCodTab = '805'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipMto[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = 'NO HAY TIPOS DE RELACION DEFINIDOS [337]';
         return false;
      }
      // Trae derecho habientes 
      $lcSql = "SELECT cconcep, dprogra FROM ADM.E03DMTO WHERE cCodAfj = '$this->pcCodAfj'";
      $RS = $loSql->omExec($lcSql);
      while ($laFila = $loSql->fetch($RS)) {
         $this->paDatMto[] = array($laFila[0], $laFila[1]);;
      }
      $loSql->omDisconnect();
      return $llOk;
   }
   
   // Grabar fecha de uso del activo fijo
   public function omGrabarFechaUso() {
      // Condiciones de excepcion
      if (empty($this->pcCodAfj)) {
         $this->pcError = 'CODIGO DE ACTIVO FIJO VACIO';
         return false;
      } else if (empty($this->pcFecUso)) {
         $this->pcError = 'FECHA DE USO VACIA';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "UPDATE ADM.E03MAFJ SET dFecUso = '$this->pcFecUso' WHERE cCodAfj = '$this->pcCodAfj'";
      $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      return true;
   }

   
   public function omBuscarArea() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Trae tipo de relacion
      $RS = $loSql->omExec("SELECT cArea, cDescri FROM S01TARE WHERE LENGTH(TRIM(cArea)) = 6");
      while ($laFila = $loSql->fetch($RS)) {
         $this->paArea[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      return $llOk;
   }
   
   public function omGrabarMantenimiento() {
      // Condiciones de excepcion
      if (empty($this->paDatMto)) {
         $this->pcError = 'FECHA DE MANTENIMIENTO VACIA';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = 'CODIGO DE USUARIO VACIO';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcCodAfj = $this->paDatMto[0][0];
      $RS = $loSql->omExec("SELECT cCodAfj FROM ADM.E03MAFJ WHERE cCodAfj = '$lcCodAfj'");
      $laFila = $loSql->fetch($RS);
      IF ($laFila[0] == null) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE ACTIVO FIJO NO EXISTE</ERROR></DATA>';
         return false;
      }
      $loSql->omExec("DELETE FROM ADM.E03DMTO WHERE cCodAfj = '$lcCodAfj'");
      foreach($this->paDatMto as $i) {
         $lcSql = "INSERT INTO ADM.E03DMTO (ccodafj, cconcep, dprogra, cglosa, ccodusu) VALUES 
                  ('$i[0]', '$i[1]', '$i[2]', '', '$this->pcCodUsu')";
         $loSql->omExec($lcSql);
      }
      $loSql->omDisconnect();
      return true;
   }

   
}

?>
