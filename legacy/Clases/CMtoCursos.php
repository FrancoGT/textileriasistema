<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Registro y mantenimiento de cursos
//-------------------------------------
class CMtoCursos extends CBase {
   public $paEstado, $pcCodUsu, $pcDescri, $pcEstado, $paCodNor, $paNorCod, $paCurNor, $paDescri, $paIndex,
          $pcNomDoc, $pdFecha, $pcTiempo, $pcCodCur, $paCodPer, $pnNota, $paData, $pcCodNor, $paSubNor,$paTipo;
   
   public function __construct() {
      parent::__construct();
      $this->paEstado = $this->paData = $this->paCodNor = $this->paCodPer = null;
   }
  
   // Inicia mantenimiento de Cursos
   public function omCargarMtoCursos() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Estados
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '001'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DEFINIDOS. TABLA [001]</ERROR></DATA>';
         return false;
      }
      // Carga normas
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR WHERE cEstado = 'A' AND cTipo = '1' ORDER BY cCodNor, cDescri");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodNor[] = array($laFila[0], trim($laFila[0]).' - '.$laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY NORMAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      // Carga tipo
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE cCodTab = '055'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipo[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY TIPO DE CURSO DEFINIDOS. TABLA [055]</ERROR></DATA>';
         return false;
      }
      return true;
   }

   // Aplica codigo de Cursos
   public function omAplicarCursos() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Curso
      $RS = $loSql->omExec("SELECT cDescri, cEstado, cNomDoc, TO_CHAR(dFecha,'yyyy-mm-dd'), cTiempo, mDatos, cTipo FROM S02MCUR WHERE cCodCur = '$this->pcCodCur'");
      $laFila = $loSql->fetch($RS);
      $this->pcDescri = $laFila[0];
      if (empty($this->pcDescri)) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE CURSO NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcEstado = $laFila[1];
      $this->pcNomDoc = $laFila[2];
      $this->pdFecha  = $laFila[3];
      $this->pcTiempo = $laFila[4];
      $this->pmDatos  = $laFila[5]->load();
      $this->pcTipo   = $laFila['CTIPO'];
      // Carga normas
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR WHERE cEstado = 'A' AND cTipo = '1' ORDER BY cDescri");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodNor[] = array(trim($laFila[0]), $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY NORMAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      // Trae normas del curso
      $RS = $loSql->omExec("SELECT cCodNor, cDescri, mDatos FROM V_S02PCUR WHERE cCodCur = '$this->pcCodCur' ORDER BY cCodNor");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $lmDatos = (empty($laFila[2])) ? '' : $laFila[2]->load();
         $i++;
         $this->paData[$i] = array($i, $laFila[0], trim($laFila[0]).'-'.$laFila[1], $lmDatos);
      }
      return $llOk;
   }
   
   // Inicializa mantenimiento de detalle (normas) de cursos
   public function initDetalleCursos() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Curso
      $lcSql = "SELECT cCodCur, cDescri FROM S02MCUR WHERE cEstado = 'A' ORDER BY cDescri";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodCur[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY CURSOS ACTIVOS</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   public function omTablasMtoLicencias() {
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
   
   // Mantenimientode puente cursos - normas
   public function omMtoPuenteCursos() {
      if (empty($this->pcCodCur)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE CURSO VACIO</ERROR></DATA>';
         return false;
      }
      $this->paData = $this->paCodNor = null;
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
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR WHERE cEstado = 'A' AND CTIPO = 0 ORDER BY cDescri");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodNor[] = array(trim($laFila[0]), $laFila[1]);
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
         $this->paData[$i] = array($i, $laFila[0], $laFila[1]->load());
      }
      return $llOk;
   }

   // Mantenimiento de subnormas
   public function omMtoSubNormas() {
      $this->paSubNor = null;
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      // Carga normas
      $lcPrefijo = strpos($this->pcCodNor,'-') ? explode('-', $this->pcCodNor) : $this->pcCodNor;
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR WHERE cCodNor LIKE '$lcPrefijo%' AND cEstado = 'A' AND CTIPO = 1 ORDER BY cDescri");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paSubNor[] = array(trim($laFila[0]), $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY SUBNORMAS DEFINIDAS</ERROR></DATA>';
         $llOk = false;
      }
      return $llOk;
   }

   // Graba puente cursos - normas
   public function omGrabarPuenteCursos() {
      if (empty($this->pcCodCur)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE CURSO VACIO</ERROR></DATA>';
         return false;
      } elseif (empty($this->paData)) {
         $this->pcError = '<DATA><ERROR>ERROR: NO HA DEFINIDO NORMAS DEL CURSO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Valida que no se repitan normas
      $i = 0;
      foreach($this->paData as $laFila) {
         $i++;
         for ($j = $i + 1; $j <= fxCount($this->paData); $j++) {
             if ($laFila[1] == $this->paData[$j][1]) {
                $this->pcError = '<DATA><ERROR>LA NORMA ['.$laFila[1].'] ESTA REPETIDA</ERROR></DATA>';
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
      // Valida Curso
      $RS = $loSql->omExec("SELECT cDescri FROM S02MCUR WHERE cCodCur = '$this->pcCodCur'");
      $laFila = $loSql->fetch($RS);
      if ($laFila[0] == null) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE CURSO NO EXISTE</ERROR></DATA>';
         return false;
      }
      $llOk = $loSql->omExec("DELETE FROM S02PCUR WHERE cCodCur = '$this->pcCodCur'");
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR NORMAS ANTERIORES</ERROR></DATA>';
         return false;
      }
      foreach($this->paData as $i) {
         $lcSql = "INSERT INTO S02PCUR (cCodCur, cCodNor, mDatos, cCodUsu) VALUES ('$this->pcCodCur', '$i[1]', '$i[2]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk)
            break;
      }
      $loSql->omDisconnect();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR AL GRABAR NORMAS. REINTENTE</ERROR></DATA>';
      }
      return $llOk;
   }
   
   // Grabar curso y detalle
   public function omGrabarCursos() {
      $laHora = explode(":", $this->pcTiempo);
      if (empty($this->pcDescri)) {
         $this->pcError = '<DATA><ERROR>DESCRIPCION DEL CURSO ESTA VACIA</ERROR></DATA>';
         return false;
      }  elseif (empty($this->pcNomDoc)) {
         $this->pcError = '<DATA><ERROR>NOMBRE DEL EXPOSITOR VACIO</ERROR></DATA>';
         return false;
      } elseif (!$this->mxValDate($this->pdFecha)) {
         $this->pcError = '<DATA><ERROR>FECHA INVALIDA</ERROR></DATA>';
         return false;
      }  elseif (empty($this->pmDatos)) {
         $this->pcError = '<DATA><ERROR>DATOS VARIOS NO DEFINIDO</ERROR></DATA>';
         return false;
      }  elseif ($laHora[0] > '99' or $laHora[0] < '00' or $laHora[1] > 59 or $laHora[1] < '00') {
         $this->pcError = '<DATA><ERROR>DURACION DEL CURSO INVALIDO</ERROR></DATA>';
         return false;
      } elseif (fxCount($this->paData) == 0) {
         $this->pcError ='<DATA><ERROR>NO HAY NORMAS EN EL DETALLE DEL CURSO</ERROR></DATA>';
         return false;
      }  elseif (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }  elseif (empty($this->pcTipo)) {
         $this->pcError = '<DATA><ERROR>TIPO DE CAPACITACION VACIA</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodCur) AND !$this->plNuevo) {
         $this->pcError = '<DATA><ERROR>CODIGO DE CURSO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Valida que no se repitan normas
      $i = 0;
      foreach ($this->paData as $laFila) {
         $i++;
         if (substr($laFila[1], 0, 1) == 'Z')
             continue;
         for ($j = $i + 1; $j <= fxCount($this->paData); $j++) {
             if ($laFila[1] == $this->paData[$j][1]) {
                $this->pcError = '<DATA><ERROR>LA NORMA ['.$laFila[1].'] ESTA REPETIDA</ERROR></DATA>';
                return false;
             }
         }
      }
      // Datos vacios de normas
      foreach ($this->paData as $i) {
         if (empty($i[2])) {
            $this->pcError = '<DATA><ERROR>DATOS DE LA NORMA NO DEFINIDO</ERROR></DATA>';
            return false;
         }
      }
      $this->pcDescri = strtoupper(trim($this->pcDescri));
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      if ($this->plNuevo) {
         $RS = $loSql->omExec('SELECT MAX(cCodCur) FROM S02MCUR');
         $laFila = $loSql->fetch($RS);
         if ($laFila[0] == null)
            $lcCodCur = '000000';
         else
            $lcCodCur = $laFila[0] ;
         $lnCodCur = (int)$lcCodCur + 1;
         $lcCodCur = (string)$lnCodCur;
         $this->pcCodCur = right('00000'.trim($lcCodCur), 6);
         $lcSql = "INSERT INTO S02MCUR (cCodCur, cDescri, cEstado, cNomDoc, dFecha, cTiempo, mDatos, cTipo, cCodUsu) VALUES 
                   ('$this->pcCodCur', '$this->pcDescri', '$this->pcEstado','$this->pcNomDoc', TO_DATE('$this->pdFecha','yyyy-mm-dd'), '$this->pcTiempo', '$this->pmDatos', '$this->pcTipo', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR CURSO</ERROR></DATA>';
            return false;
         }
      } else {
         $RS = $loSql->omExec("SELECT cCodCur FROM S02MCUR WHERE cCodCur = '$this->pcCodCur'");
         $laFila = $loSql->fetch($RS);
         if ($laFila[0] == null) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE CURSO NO EXISTE</ERROR></DATA>';
            return false;
         }
         $lcSql = "UPDATE S02MCUR SET cDescri = '$this->pcDescri', cEstado = '$this->pcEstado', cNomDoc = '$this->pcNomDoc', dFecha = TO_DATE('$this->pdFecha', 'yyyy-mm-dd'), cTiempo = '$this->pcTiempo', mDatos = '$this->pmDatos', cTipo = '$this->pcTipo', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cCodCur = '$this->pcCodCur'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR CURSO</ERROR></DATA>';
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM S02PCUR WHERE cCodCur = '$this->pcCodCur'");
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR NORMAS DEL CURSO</ERROR></DATA>';
            return false;
         }
      }
      foreach($this->paData as $i) {
         $lcSql = "INSERT INTO S02PCUR (cCodCur, cCodNor, mDatos, cCodUsu) VALUES 
                   ('$this->pcCodCur', '$i[1]', '$i[3]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            break;
         }
      }
      $loSql->omDisconnect();
      if (!$llOk) 
         $this->pcError = '<DATA><ERROR>ERROR AL GRABAR NORMAS DEL CURSO</ERROR></DATA>';
      return $llOk;
   }

   // Buscar cursos
   public function omBuscarCursos() {
      // Condiciones de excepcion
      if (!fxValDate($this->pdFecIni) ){
         $this->pcError = '<DATA><ERROR>FECHA INICIAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecFin) ){
         $this->pcError = '<DATA><ERROR>FECHA FINAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif ($this->pdFecFin < $this->pdFecIni){
         $this->pcError = '<DATA><ERROR>FECHA FINAL ES MAYOR QUE FECHA INICIAL</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcTipo)){
         $this->pcError = '<DATA><ERROR>TIPO DE CAPACITACION ESTA VACIA</ERROR></DATA>';
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
      $lcSql = "SELECT cCodCur, cDescri, TO_CHAR(dFecha, 'yyyy-mm-dd') FROM S02MCUR WHERE cTipo='$this->pcTipo' AND dFecha BETWEEN TO_DATE('$this->pdFecIni','yyyy-mm-dd') AND TO_DATE('$this->pdFecFin','yyyy-mm-dd') ORDER BY dFecha DESC";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodCur[] = array($laFila[0], $laFila[1], $laFila[2]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY CURSOS QUE CUMPLAN CONDICION</ERROR></DATA>';
         return false;
      }
      return true;
   }

   public function omMtoCursosNotas() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cDescri, TO_CHAR(dFecha, 'yyyy-mm-dd') FROM S02MCUR WHERE cCodCur = '$this->pcCodCur'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->pcDescri = $laFila[0];
      if (empty($this->pcDescri)) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE CURSO NO EXISTE</ERROR></DATA>';
         $llOk = false;
      }
      $this->pdFecha = $laFila[1];
      $lcSql = "SELECT cCodPer, cNombre FROM S02MPER WHERE cEstado = 'A' ORDER BY cNombre";
      $RS = $loSql->omExec($lcSql);
      $i = 1;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Trae empleados en el curso
      $lcSql = "SELECT cCodPer, nNota FROM S02DCAP WHERE cCodCur = '$this->pcCodCur' ORDER BY cCodPer";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $laFila[1] = ($laFila[1] == -1)? '': $laFila[1];
         $i++;
         $this->paData[$i] = array($i, $laFila[0], $laFila[1]);
      }
      return $llOk;
   }

   public function omGrabarCursosNotas() {
      if (empty($this->pcCodCur)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE CURSO VACIO</ERROR></DATA>';
         return false;
      } elseif (empty($this->paData)) {
         $this->pcError = '<DATA><ERROR>NO HA DEFINIDO PERSONAL</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Valida que no se repitan personal
      $i = 0;
      foreach ($this->paData as $laFila) {
         $i++;
         for ($j = $i + 1; $j <= fxCount($this->paData); $j++) {
             if ($laFila[1] == $this->paData[$j][1]) {
                $this->pcError = '<DATA><ERROR>EL EMPLEADO ['.$laFila[1].'] ESTA REPETIDO</ERROR></DATA>';
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
      // Valida Curso
      $RS = $loSql->omExec("SELECT cDescri FROM S02MCUR WHERE cCodCur = '$this->pcCodCur'");
      $laFila = $loSql->fetch($RS);
      if ($laFila[0] == null) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE CURSO NO EXISTE</ERROR></DATA>';
         return false;
      }
      $lcSql = "DELETE FROM S02DCAP WHERE cCodCur = '$this->pcCodCur'";
      $llOk = $loSql->omExec($lcSql);
      if (!$llOk) {
         $loSql->rollback();
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO SE PUDO ELIMINAR DETALLE ANTERIOR</ERROR></DATA>';
         return false;
      }
      foreach ($this->paData as $laFila) {
         $lnNota = (empty($laFila[2]))? -1: $laFila[2];
         $lcSql = "INSERT INTO S02DCAP (cCodPer, cCodCur, nNota, cCodUsu) VALUES ('$laFila[1]', '$this->pcCodCur', $lnNota, '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $this->pcError = '<DATA><ERROR>ERROR AL INGRESAR PERSONAL DEL CURSO</ERROR></DATA>';
            break;
         }
      }
      $loSql->omDisconnect();
      return $llOk;
   }
}
?>