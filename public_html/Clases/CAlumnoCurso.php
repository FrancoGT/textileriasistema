<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Matricula alumno en curso
//-------------------------------------
class CAlumnoCurso extends CBase {
   public $paCodCur = null;
   public $paCurHor = null;
   public $pcNomAlu = null;
   public $pcCodPer = null;
   public $pcTipo   = null;
   public $pcCodUsu = null;

   public function __construct() {
      parent::__construct();
   }

   //--- Carga arreglo de cursos
   public function omIniciar() {
      // Condiciones de excepcion
      if (empty($this->pcCodPer)) {
         $this->pcError = 'CODIGO DE USUARIO VACIO';
         return false;
      } else if (empty($this->pcTipo)) {
         $this->pcError = 'TIPO DE USUARIO VACIO';
         return false;
      }
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
      // Carga cursos
      $lcSql = "SELECT TRIM(B.cNomCur) || ' (' || A.cLabTeo || ')', A.cCodCur FROM V_Clases A INNER JOIN L01MCUR B ON A.cCodCur = B.cCodCur WHERE A.cCodDoc = (SELECT cCodDoc FROM V_Docentes WHERE cCodPer = '$this->pcCodPer') ORDER BY cCodCur, cSecGru";
      if ($this->pcTipo == 'A')
         $lcSql = "SELECT TRIM(cNomCur) || ' (' || cLabTeo || ')', cCodCur FROM L01MCUR ORDER BY cCodCur";
      else if (substr($lcPeriod, -1) == '1')
         $lcSql = "SELECT TRIM(C.cNomCur) || ' (' || C.cLabTeo || ')', C.cCodCur FROM L01PCUR A 
                          INNER JOIN L01MDOC B ON A.cCodDoc = B.cCodDoc
                          INNER JOIN L01MCUR C ON A.cCodCur = C.cCodCur
                          WHERE A.cCodDoc = (SELECT cCodDoc FROM V_Docentes WHERE cCodPer = '$this->pcCodPer') AND 
                          SUBSTRING(A.cCodCur, 3, 2) IN ('01', '03', '05', '07', '09') ORDER BY cCodCur";
      else if (substr($lcPeriod, -1) == '2')
         $lcSql = "SELECT TRIM(C.cNomCur) || ' (' || C.cLabTeo || ')', C.cCodCur FROM L01PCUR A 
                          INNER JOIN L01MDOC B ON A.cCodDoc = B.cCodDoc
                          INNER JOIN L01MCUR C ON A.cCodCur = C.cCodCur
                          WHERE A.cCodDoc = (SELECT cCodDoc FROM V_Docentes WHERE cCodPer = '$this->pcCodPer') AND 
                          SUBSTRING(A.cCodCur, 3, 2) IN ('02', '04', '06', '08', '10') ORDER BY cCodCur";
      $RS = $loSql->omExec($lcSql);
      while ($laFila = pg_fetch_row($RS)) {
         $this->paCodCur[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      return $llOk;
   }

   //--- Carga cursos y horarios
   public function omAplicar() {
      // Condiciones de excepcion
      if (empty($this->pcCodAlu)) {
         $this->pcError = 'CODIGO DE ALUMNO VACIO';
         return false;
      } else if (empty($this->pcCodCur)) {
         $this->pcError = 'CODIGO DE CURSO VACIO';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $this->paNomAlu = '';
      $lcSql = "SELECT cNombre FROM V_Alumnos WHERE cCodAlu = '$this->pcCodAlu'";
      $RS = $loSql->omExec($lcSql);
      $laFila = pg_fetch_row($RS);
      $this->pcNomAlu = $laFila[0];
      if (empty($this->pcNomAlu)) {
         $loSql->omDisconnect();
         $this->pcError = 'CODIGO DE ALUMNO NO EXISTE';
         return false;
      }
      $lcSql = "SELECT cCurHor, cGlosa FROM V_Clases WHERE cCodCur = '$this->pcCodCur' ORDER BY cGlosa";
//      die($lcSql);
      $RS = $loSql->omExec($lcSql);
      while ($laFila = pg_fetch_row($RS)) {
         $this->paCurHor[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      return $llOk;
   }

   public function omGrabar() {
      // Condiciones de excepcion
      if (empty($this->pcCodAlu)) {
         $this->pcError = 'CODIGO DE ALUMNO VACIO';
         return false;
      } else if (empty($this->pcCurHor)) {
         $this->pcError = 'CODIGO DE CURSO-HORARIO VACIO';
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
      $lcSql = "SELECT * FROM f_InsertL01PALU('$this->pcCodAlu', '$this->pcCurHor', '$this->pcCodUsu')";
      $RS = $loSql->omExec($lcSql);
      $laFila = pg_fetch_row($RS);
      $loSql->omDisconnect();
      // Ver resultado
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
      $RS = $loSql->omExec("SELECT * FROM VHorarios");
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      while ($laFila = pg_fetch_row($RS)) {
         $this->paCodHor[] = array($laFila[0], $laFila[1]);         
      }
      // Carga laboratorios
      $RS = $loSql->omExec("SELECT cDesLab, cCodLab FROM L01MLAB ORDER BY cCodLab");
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
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cLabHor FROM L01PHOR WHERE cCodLab = '$this->pcCodLab' AND cCodHor = '$this->pcCodHor'";
      $RS = $loSql->omExec($lcSql);
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
      $lcSql = "INSERT INTO L01PHOR (cLabHor, cCodLab, cCodHor, cCodUsu) VALUES ('$lcLabHor', '$this->pcCodLab', '$this->pcCodHor', '99999')";
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
   public $paCodPer = null;
   public $pcCodLab = null;
   public $pcCodHor = null;
   public $pcCodCur = null;
   public $pcCodPer = null;
   public $pcNombre = null;
   public $pcDesLab = null;

   public function __construct() {
      parent::__construct();
   }

   //--- Carga arreglo de personal, docente, horarios y laboratorios
   public function omIniciar() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga laboratorios
      $RS = $loSql->omExec("SELECT cDesLab, cCodLab FROM L01MLAB ORDER BY cCodLab");
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
      $RS = $loSql->omExec("SELECT cNombre, cCodPer FROM L01MPER WHERE cTipo = 'D' ORDER BY cNombre");
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      $i = 0;
      while ($laFila = pg_fetch_row($RS)) {
         $i++;
         $this->paCodPer[] = array($laFila[0], $laFila[1]);         
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = 'NO HAY DOCENTES DEFINIDOS';
         return false;
      }
      return $llOk;
   }

   public function omAplicar() {
      // Condiciones de excepcion
      if (empty($this->pcCodLab)) {
         $this->pcError = 'LABORATORIO NO DEFINIDO';
         return false;
      } else if (empty($this->pcCodPer)) {
         $this->pcError = 'DOCENTE NO DEFINIDO';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Nombre de docente
      $RS = $loSql->omExec("SELECT cNombre FROM L01MPER WHERE cCodPer = '$this->pcCodPer'");
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      while ($laFila = pg_fetch_row($RS)) {
         $this->pcNombre = $laFila[0];
      }
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
      $lcSql = "SELECT * FROM VHorarios A INNER JOIN L01PHOR B ON A.cCodHor = B.cCodHor WHERE B.cCodLab = '$this->pcCodLab'";
      $RS = $loSql->omExec($lcSql);
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
      $RS = $loSql->omExec("SELECT A.cNomCur, A.cCodCur FROM L01MCUR A INNER JOIN L01PCUR B ON A.cCodCur = B.cCodCur WHERE B.cCodPer = '$this->pcCodPer' ORDER BY A.cCodCur");
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
      } else if (empty($this->pcCodPer)) {
         $this->pcError = 'DOCENTE NO DEFINIDO';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT GrabarL01PCHO AS cLinea FROM GrabarL01PCHO('$this->pcCodPer', '$this->pcCodCur', '$this->pcCodLab', '$this->pcCodHor', '99999')"; 
      $RS = $loSql->omExec($lcSql);
      while ($laFila = pg_fetch_row($RS)) {
         $lcLinea = $laFila[0];
      }
      $loSql->omDisconnect();
      if (substr($lcLinea, 0, 7) == '<ERROR>') {
         $this->pcError = $lcLinea;
         return false;
      }
      return true;
   }
}

?>
