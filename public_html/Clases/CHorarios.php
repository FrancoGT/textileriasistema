<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Mantenimiento de Horarios
//-------------------------------------
class CHorarios extends CBase {
   public $paNroDia = null;
   public $pcNroDia = null;
   public $pcHorIni = null;
   public $pcHorFin = null;

   public function __construct() {
      parent::__construct();
   }

   //--- Carga arreglo de dias
   public function omAplicar() {
      $this->paNroDia[0][0] = 'LUNES';
      $this->paNroDia[0][1] = '1';
      $this->paNroDia[1][0] = 'MARTES';
      $this->paNroDia[1][1] = '2';
      $this->paNroDia[2][0] = 'MIERCOLES';
      $this->paNroDia[2][1] = '3';
      $this->paNroDia[3][0] = 'JUEVES';
      $this->paNroDia[3][1] = '4';
      $this->paNroDia[4][0] = 'VIERNES';
      $this->paNroDia[4][1] = '5';
      $this->paNroDia[5][0] = 'SABADO';
      $this->paNroDia[5][1] = '6';
      $this->paNroDia[6][0] = 'DOMINGO';
      $this->paNroDia[6][1] = '7';
      return true;
   }

   public function omGrabar() {
      // Condiciones de excepcion
      $llOk = $this->mxValidar();
      if (!$llOk) {
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lCSql = "SELECT cCodHor FROM L01MHOR WHERE cNroDia = '$this->pcNroDia' AND cHorIni = '$this->pcHorIni' AND cHorFin = '$this->pcHorFin'";
      $RS = $loSql->omExec($lCSql);
      while ($laFila = pg_fetch_row($RS)) {
         if (!empty($laFila[0])) {
            $llOk = false;
         }
      }
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = 'HORARIO YA EXISTE';
         return false;
      }
      $RS = $loSql->omExec("SELECT MAX(cCodHor) FROM L01MHOR");
      while ($laFila = pg_fetch_row($RS)) {
         $lcCodHor = $laFila[0];
      }
      if (empty($lcCodHor)) {
         $lcCodHor = "00000";
      }
      $lnCodHor = (int)$lcCodHor + 1;
      $lcCodHor = (string)$lnCodHor;
      $lcCodHor = right("0000".trim($lcCodHor), 5);
      $lCSql = "INSERT INTO L01MHOR (cCodHor, cHorIni, cHorFin, cNroDia, cCodUsu) VALUES ('$lcCodHor', '$this->pcHorIni', '$this->pcHorFin', '$this->pcNroDia', '99999')";
      $llOk = $loSql->omExec($lCSql);
      $loSql->omDisconnect();
      if (!$llOk) {
         $this->pcError = "ERROR EN GRABACION. REINTENTE";
      }
      return $llOk;
   }
   
   protected function mxValidar() {
      // Carga arreglo de dias
      $this->omAplicar();
      // Carga otros arreglos
      $laArray1 = array('0', '1', '2');
      $laArray2 = array('0', '1', '2', '3', '4', '5', '6', '7', '8', '9');
      $laArray3 = array('0', '1', '2', '3', '4', '5');
      // Condiciones de excepcion
      if (empty($this->pcHorIni)) {
         $this->pcError = 'HORA DE INICIO NO DEFINIDA';
         return false;
      } else if (empty($this->pcHorFin)) {
         $this->pcError = 'HORA DE FIN NO DEFINIDA';
         return false;
      } else if (empty($this->pcNroDia)) {
         $this->pcError = 'DIA NO DEFINIDO';
         return false;
      } else if (in_array($this->pcNroDia, $this->paNroDia)) {
         $this->pcError = 'VALOR DE DIA INCORRECTO';
         return false;
      } else if (substr($this->pcHorIni, 2, 1) != ':') {
         $this->pcError = 'ERROR EN FORMATO DE HORA DE INICIO';
         return false;
      } else if (!in_array(substr($this->pcHorIni, 0, 1), $laArray1)) {
         $this->pcError = 'ERROR EN HORA DE INICIO (1)';
         return false;
      } else if (!in_array(substr($this->pcHorIni, 1, 1), $laArray2)) {
         $this->pcError = 'ERROR EN HORA DE INICIO (2)';
         return false;
      } else if (!in_array(substr($this->pcHorIni, 3, 1), $laArray3)) {
         $this->pcError = 'ERROR EN HORA DE INICIO (3)';
         return false;
      } else if (!in_array(substr($this->pcHorIni, 4, 1), $laArray2)) {
         $this->pcError = 'ERROR EN HORA DE INICIO (4)';
         return false;
      } else if (substr($this->pcHorFin, 2, 1) != ':') {
         $this->pcError = 'ERROR EN FORMATO DE HORA DE FIN';
         return false;
      } else if (!in_array(substr($this->pcHorFin, 0, 1), $laArray1)) {
         $this->pcError = 'ERROR EN HORA DE FIN (1)';
         return false;
      } else if (!in_array(substr($this->pcHorFin, 1, 1), $laArray2)) {
         $this->pcError = 'ERROR EN HORA DE FIN (2)';
         return false;
      } else if (!in_array(substr($this->pcHorFin, 3, 1), $laArray3)) {
         $this->pcError = 'ERROR EN HORA DE FIN (3)';
         return false;
      } else if (!in_array(substr($this->pcHorFin, 4, 1), $laArray2)) {
         $this->pcError = 'ERROR EN HORA DE FIN (4)';
         return false;
      } else if ($this->pcHorIni >= $this->pcHorFin) {
         $this->pcError = 'HORA DE INICIO ES MAYOR QUE HORA DE SALIDA';
         return false;
      } else if ($this->pcHorIni < "07:00") {
         $this->pcError = 'HORA DE INICIO NO PUEDE SER MENOR QUE 7:00am';
         return false;
      } else if ($this->pcHorFin > "21:30") {
         $this->pcError = 'HORA DE INICIO NO PUEDE SER MAYOR QUE 09:30pm';
         return false;
      }
      return true;
   }
}

//------------------------------------------
// Mantenimiento de Horarios y laboratorios
//------------------------------------------
class CHorariosLab extends CBase {
   public $paCodLab = null;
   public $paCodHor = null;
   public $pcCodLab = null;
   public $pcCodHor = null;

   public function __construct() {
      parent::__construct();
   }

   //--- Carga arreglo de horarios y laboratorios
   public function omAplicar() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga horarios
      $RS = $loSql->omExec("SELECT * FROM V_Horarios");
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      while ($laFila = pg_fetch_row($RS)) {
         $this->paCodHor[] = array($laFila[0], $laFila[1]);         
      }
      // Carga laboratorios
      $RS = $loSql->omExec("SELECT cDesLab, cCodLab FROM L01MLAB ORDER BY cDesLab");
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      while ($laFila = pg_fetch_row($RS)) {
         $this->paCodLab[] = array($laFila[0], $laFila[1]);         
      }
      $loSql->omDisconnect();
      return $llOk;
   }

   public function omGrabar() {
      // Condiciones de excepcion
      if (empty($this->pcCodLab)) {
         $this->pcError = 'LABORATORIO NO DEFINIDO';
         return false;
      } else if (empty($this->pcCodHor)) {
         $this->pcError = 'HORARIO NO DEFINIDO';
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
      $lCSql = "SELECT cLabHor FROM L01PHOR WHERE cCodLab = '$this->pcCodLab' AND cCodHor = '$this->pcCodHor'";
      $RS = $loSql->omExec($lCSql);
      while ($laFila = pg_fetch_row($RS)) {
         if (!empty($laFila[0])) {
            $llOk = false;
         }
      }
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = 'HORARIO - LABORATORIO YA EXISTE';
         return false;
      }
      $RS = $loSql->omExec("SELECT MAX(cLabHor) FROM L01PHOR");
      while ($laFila = pg_fetch_row($RS)) {
         $lcLabHor = $laFila[0];
      }
      if (empty($lcLabHor)) {
         $lcLabHor = "000000";
      }
      $lnLabHor = (int)$lcLabHor + 1;
      $lcLabHor = (string)$lnLabHor;
      $lcLabHor = right("00000".trim($lcLabHor), 6);
      $lcSql = "INSERT INTO L01PHOR (cLabHor, cCodLab, cCodHor, cCodUsu) VALUES ('$lcLabHor', '$this->pcCodLab', '$this->pcCodHor', '$this->pcCodUsu')";
      $llOk = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      if (!$llOk) {
         $this->pcError = "ERROR EN GRABACION. REINTENTE";
      }
      return $llOk;
   }
}

//--------------------------------------------------------------
// Mantenimiento de Docentes, Cursos, Horarios y laboratorios
//--------------------------------------------------------------
class CPuente extends CBase {
   public $paCodLab = null;
   public $paCodHor = null;
   public $paCodCur = null;
   public $paCodDoc = null;
   public $pcCodLab = null;
   public $pcCodHor = null;
   public $pcCodCur = null;
   public $pcCodDoc = null;
   public $pcNombre = null;
   public $pcDesLab = null;
   public $paSecGru = null;
   public $pcSecGru = null;
   public $pcCodPer = null;

   public function __construct() {
      parent::__construct();
   }

   // Carga arreglo de personal, docente, horarios y laboratorios
   public function omIniciar() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga laboratorios
      $RS = $loSql->omExec("SELECT cDesLab, cCodLab FROM L01MLAB ORDER BY cDesLab");
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      $i = 0;
      while ($laFila = pg_fetch_row($RS)) {
         $i++;
         $this->paCodLab[] = array($laFila[0], $laFila[1]);         
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = 'NO HAY LABORATORIOS DEFINIDOS';
         return false;
      }
      // Carga docentes
      $RS = $loSql->omExec("SELECT cNombre, cCodDoc FROM V_Docentes WHERE cTipo = 'D' ORDER BY cNombre");
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      $i = 0;
      while ($laFila = pg_fetch_row($RS)) {
         $i++;
         $this->paCodDoc[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = 'NO HAY DOCENTES DEFINIDOS';
         return false;
      }
      // Codigo docente
      $RS = $loSql->omExec("SELECT cCodDoc FROM V_Docentes WHERE cCodPer = '$this->pcCodPer'");
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      $laFila = pg_fetch_row($RS);
      $this->pcCodDoc = $laFila[0];
      $loSql->omDisconnect();
      return $llOk;
   }

   public function omAplicar() {
      // Condiciones de excepcion
      if (empty($this->pcCodLab)) {
         $this->pcError = 'LABORATORIO NO DEFINIDO';
         return false;
      } else if (empty($this->pcCodDoc)) {
         $this->pcError = 'DOCENTE NO DEFINIDO';
         return false;
      }
      // Arreglo de secciones y grupos
      $this->mxArraySecciones();
      // Conecta con DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Periodo academico
      $RS = $loSql->omExec("SELECT TRIM(cDesCor) FROM V_S01TTAB WHERE cCodTab = '999' AND cCodigo = 'GCPERIOD'");
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      $laFila = pg_fetch_row($RS);
      $lcPeriod = $laFila[0];
      // Nombre de docente
      $RS = $loSql->omExec("SELECT cNombre FROM V_Docentes WHERE cCodDoc = '$this->pcCodDoc'");
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      $laFila = pg_fetch_row($RS);
      $this->pcNombre = $laFila[0];
      // Descripcion de laboratorio
      $RS = $loSql->omExec("SELECT cDesLab FROM L01MLAB WHERE cCodLab = '$this->pcCodLab'");
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      while ($laFila = pg_fetch_row($RS)) {
         $this->pcDesLab = $laFila[0];
      }
      // Carga horarios
      $lCSql = "SELECT * FROM V_Horarios A INNER JOIN L01PHOR B ON A.cCodHor = B.cCodHor WHERE B.cCodLab = '$this->pcCodLab'";
      $RS = $loSql->omExec($lCSql);
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      $i = 0;
      while ($laFila = pg_fetch_row($RS)) {
         $i++;
         $this->paCodHor[] = array($laFila[0], $laFila[1]);         
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = 'NO HAY HORARIOS DEFINIDOS PARA AULA/LABORATORIO';
         return false;
      }
      // Carga cursos
      $lcSql = "SELECT TRIM(A.cNomCur) || ' - ' || A.cLabTeo, A.cCodCur FROM L01MCUR A INNER JOIN L01PCUR B ON A.cCodCur = B.cCodCur WHERE B.cCodDoc = '$this->pcCodDoc' ORDER BY A.cCodCur";
      if (substr($lcPeriod, -1) == '1')
         $lcSql = "SELECT A.cNomCur || ' - ' || A.cLabTeo, A.cCodCur FROM L01MCUR A INNER JOIN L01PCUR B ON A.cCodCur = B.cCodCur WHERE B.cCodDoc = '$this->pcCodDoc' AND SUBSTRING(A.cCodCur, 3, 2) IN ('01', '03', '05', '07', '09') ORDER BY A.cCodCur";
      else  if (substr($lcPeriod, -1) == '2')
         $lcSql = "SELECT A.cNomCur || ' - ' || A.cLabTeo, A.cCodCur FROM L01MCUR A INNER JOIN L01PCUR B ON A.cCodCur = B.cCodCur WHERE B.cCodDoc = '$this->pcCodDoc' AND SUBSTRING(A.cCodCur, 3, 2) IN ('02', '04', '06', '08', '10') ORDER BY A.cCodCur";
      $RS = $loSql->omExec($lcSql);
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      $i = 0;
      while ($laFila = pg_fetch_row($RS)) {
         $i++;
         $this->paCodCur[] = array($laFila[0], $laFila[1]);         
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = 'NO HAY CURSOS DEFINIDOS PARA DOCENTE';
         return false;
      }
      return $llOk;
   }
   
   public function omGrabar() {
      // Condiciones de excepcion
      if (empty($this->pcCodLab)) {
         $this->pcError = 'LABORATORIO NO DEFINIDO';
         return false;
      } else if (empty($this->pcCodHor)) {
         $this->pcError = 'HORARIO NO DEFINIDO';
         return false;
      } else if (empty($this->pcCodCur)) {
         $this->pcError = 'CURSO NO DEFINIDO';
         return false;
      } else if (empty($this->pcCodDoc)) {
         $this->pcError = 'DOCENTE NO DEFINIDO';
         return false;
      } else if (empty($this->pcSecGru)) {
         $this->pcError = 'SECCION O GRUPO NO DEFINIDO';
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
      $lcSql = "SELECT * FROM f_InsertL01PCHO('$this->pcCodDoc', '$this->pcCodCur', '$this->pcCodLab', '$this->pcCodHor', '$this->pcSecGru', '$this->pcCodUsu')"; 
      $RS = $loSql->omExec($lcSql);
      $laFila = pg_fetch_row($RS);
      $loSql->omDisconnect();
      // Verifica resultado
      $loXml = new CXml();
      $loXml->pcData = $laFila[0];
      $loXml->pcLabel = "ERROR";
      $llOk = $loXml->omBuscar(); 
      if ($llOk) {
         $this->pcError = $loXml->pcValor;
         return false;
      }
      return true;
   }
}

?>
