<?php
require_once "Clases/CBase.php";
//-----------------------------------------
// Consulta asistencia alumnos por docente
//-----------------------------------------
class CConsultas extends CBase {
   public $paCodNor, $paCodCur, $pcDescri, $pcNomDoc, $pdFecha, $pcTiempo, $paData, $paTipEva, $pcTipEva, $paNroEva,
          $pcHorEva, $pcKilome, $pcTren, $pdFecEva, $pcSubDiv, $pcDesEsi, $pcDesEsf, $pcNomSp1, $pcNomSp2, $pcDesPry,
          $pcResult, $pcNroEva;

   public function __construct() {
      parent::__construct();
      $this->paCodNor = null;
      $this->paCodCur = null;
      $this->paData   = null;
      $this->paTipEva = null;
      $this->paNroEva = null;
   }

   public function omInitCursos() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR WHERE cTipo = '1' ORDER BY cCodNor");
      $loSql->omDisconnect();
      if (!$RS) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodNor[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY NORMAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      return true;
   }

   public function omAplicarCursos() {
      if (empty($this->pcCodNor)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE NORMA NO DEFINIDA</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
//      $RS = $loSql->omExec("SELECT cCodCur, cDescri, cNomDoc, dFecha, cTiempo, cCodNor, cDesNor FROM V_S02MNOR WHERE cCodNor = '$this->pcCodNor'");
      $RS = $loSql->omExec("SELECT cCodCur, cDescri FROM V_S02MCUR WHERE cCodNor = '$this->pcCodNor' ORDER BY cCodCur");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodCur[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY CURSOS PARA NORMAS DEFINIDA</ERROR></DATA>';
         return false;
      }
      return true;
   }

   public function omConsultarCursos() {
      if (empty($this->pcCodCur)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE CURSO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $RS = $loSql->omExec("SELECT cDescri, cNomDoc, TO_CHAR(dFecha, 'yyyy-mm-dd'), cTiempo, cCodNor, cDesNor FROM V_S02MCUR WHERE cCodCur = '$this->pcCodCur' ORDER BY cCodNor");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         if ($i == 1) {
            $this->pcDescri = $laFila[0];
            $this->pcNomDoc = $laFila[1];
            $this->pdFecha  = $laFila[2];
            $this->pcTiempo = $laFila[3];
         }
         $this->paData[] = array($laFila[4], $laFila[5]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DETALLE DE CURSO</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   public function omInitEvaluaciones() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '044'");
      $loSql->omDisconnect();
      if (!$RS) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipEva[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE EVALUACION DEFINIDOS [044]</ERROR></DATA>';
         return false;
      }
      return true;
   }

   public function omAplicarEvaluaciones() {
      if (empty($this->pcTipEva)) {
         $this->pcError = '<DATA><ERROR>TIPO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $RS = $loSql->omExec("SELECT cNroEva, cTren, TO_CHAR(dFecEva, 'yyyy-mm-dd') FROM S02MEVA WHERE SUBSTR(cNroEva, 1, 1) = '$this->pcTipEva' ORDER BY dFecEva DESC");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paNroEva[] = array($laFila[0], $laFila[1], $laFila[2]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY EVALUACIONES PARA TIPO DEFINIDO</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   public function omConsultarEvaluaciones() {
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
      // Resultados
      $laTmp = null;
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '027'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $laTmp[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>TABLA [027] DE RESULTADOS NO ESTA DEFINIDA</ERROR></DATA>';
         return false;
      }
      // Evaluacion
      $RS = $loSql->omExec("SELECT TO_CHAR(dFecEva, 'yyyy-mm-dd'), cHorEva, cKilome, cTren, cDesSub, cDesEsi, cDesEsf, cNomSp1, cNomSp2, cDescri, cDesPry, cResult FROM V_S02MEVA WHERE cNroEva = '$this->pcNroEva'");
      $loSql->omDisconnect();
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pdFecEva = $laFila[0];
      $this->pcHorEva = $laFila[1];
      $this->pcKilome = $laFila[2];
      $this->pcTren   = $laFila[3];
      $this->pcDesSub = $laFila[4];
      $this->pcDesEsi = $laFila[5];
      $this->pcDesEsf = $laFila[6];
      $this->pcNomSp1 = $laFila[7];
      $this->pcNomSp2 = $laFila[8];
      $this->pcDescri = $laFila[9];
      $this->pcDesPry = $laFila[10];
      $lcResult = $laFila[11];
      // Descripcion del resultado
      $this->pcResult = 'ERROR';
      //print_r($laTmp);
      for ($i = 0; $i < fxCount($laTmp); $i++) {
          if ($lcResult == $laTmp[$i][0]) {
             $this->pcResult = $laTmp[$i][1];
        //     echo '***'.$laTmp[$i][1].'***'.$laTmp[$i][0].'***';
             break;
          }
      }
      return true;
   }
   
/////////////////////////////////   
   public function omAplicar() {
      // Condiciones de excepcion
      if (empty($this->pcCodDoc)) {
         $this->pcError = 'CODIGO DE DOCENTE VACIO';
         return false;
      }
      // Secciones y grupos
      $this->mxArraySecciones();
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cNombre FROM V_Docentes WHERE cCodDoc = '$this->pcCodDoc'";
      $RS = $loSql->omExec($lcSql);
      $laFila = pg_fetch_row($RS);
      $this->pcNombre = $laFila[0];
      if (empty($this->pcNombre) || ($this->pcNombre == null)) {
         $loSql->omDisconnect();
         $this->pcError = 'CODIGO DE DOCENTE NO EXISTE';
         return false;
      }
      // Busca cursos para docente OJOFPM
      //$lcSql = "SELECT TRIM(cNomCur) || ' (' || cLabTeo || ')', cCodCur FROM L01MCUR WHERE SUBSTRING(cCodCur, 3, 2) IN ('02', '04', '06', '08', '10') AND cCodCur IN (SELECT DISTINCT cCodCur FROM V_Clases WHERE cCodDoc = '$this->pcCodDoc') ORDER BY cCodCur";
      $lcSql = "SELECT TRIM(cNomCur) || ' (' || cLabTeo || ')', cCodCur FROM L01MCUR WHERE cCodCur IN (SELECT DISTINCT cCodCur FROM V_Clases WHERE cCodDoc = '$this->pcCodDoc') ORDER BY cCodCur";
      $RS = $loSql->omExec($lcSql);
      while ($laFila = pg_fetch_row($RS)) {
         $this->paCodCur[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      return true;
   }

   public function omConsultar() {
      // Condiciones de excepcion
      if (empty($this->pcCodDoc)) {
         $this->pcError = 'CODIGO DE DOCENTE VACIO';
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
      // Busca cursos para docente
      $lcSql = "SELECT cNomCur FROM L01MCUR WHERE cCodCur = '$this->pcCodCur'";
      $RS = $loSql->omExec($lcSql);
      $laFila = pg_fetch_row($RS);
      $this->pcNomCur = $laFila[0];
      if (empty($this->pcNomCur)) {
         $loSql->omDisconnect();
         $this->pcError = 'CODIGO DE CURSO NO EXISTE';
         return false;
      }
      $lcSql = "SELECT cCodAlu, cNomAlu, cSecGru, cAsiste, cFechas, cPuntos FROM f_Asistencia('$this->pcCodDoc', '$this->pcCodCur', '$this->pcSecGru', '$this->pdFecIni', '$this->pdFecFin') ORDER BY cNomAlu";
      $RS = $loSql->omExec($lcSql);
      $laFila = pg_fetch_row($RS);
      $laFecNum = explode(" ", $laFila[4]);
      for ($i = 0; $i < fxCount($laFecNum); $i++) {
         $laMesDia = explode("-", $laFecNum[$i]);
         @$this->paFechas[] = fxNombreMes($laMesDia[0]) . " " . $laMesDia[1];
      }
      do {
         $laPuntos = explode(" ", trim($laFila[5]));
         $lnTotPun = 0;
         for ($i = 0; $i < fxCount($laPuntos); $i++)
            $lnTotPun += $laPuntos[$i];
         $this->paCodAlu[] = array($laFila[0], $laFila[1], $laFila[2], explode(" ", trim($laFila[3])), $laPuntos, $lnTotPun);
      } while ($laFila = pg_fetch_row($RS));
      $loSql->omDisconnect();
      return true;
   }
}
?>