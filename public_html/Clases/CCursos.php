<?php
require_once "Clases/CBase.php";
//-------------------------------------
// Mantenimiento de alumnos
//-------------------------------------
class CCursos extends CBase {
   public $pcCodCur = null;
   public $pcNomCur = null;
   public $pcLabTeo = null;
   public $pcCurCor = null;
   public $paLabTeo = null;
   public $paEstado = null;

   public function __construct() {
      parent::__construct();
   }

   public function omIniciar() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Tipo de curso
      $i = 0;
      $RS = $loSql->omExec("SELECT cDescri, SUBSTRING(cCodigo, 1, 1)::CHARACTER(1) FROM V_S01TTAB WHERE cCodTab = '010'");
      while ($laFila = pg_fetch_row($RS)) {
         $i++;
         $this->paLabTeo[] = array($laFila[0], $laFila[1], false);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = 'TABLA DE TIPOS DE CURSO [010] NO ESTA DEFINIDA';
         return false;
      }
      // Estado del curso
      $RS = $loSql->omExec("SELECT cDescri, SUBSTRING(cCodigo, 1, 1)::CHARACTER(1) FROM V_S01TTAB WHERE cCodTab = '001'");
      $i = 0;
      while ($laFila = pg_fetch_row($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1], false);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = 'TABLA DE ESTADOS [001] NO ESTA DEFINIDA';
         return false;
      }
      return true;
   }

   public function omAplicar() {
      // Trae tabla de T/L
      $llOk = $this->omIniciar();
      if (!$llOk) {
         return false;
      }
      // Conecta con DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Verifica si codigo de curso existe
      $RS = $loSql->omExec("SELECT cCodCur, cNomCur, cCurCor, cLabTeo, cEstado FROM L01MCUR WHERE cCodCur = '$this->pcCodCur'");
      $laFila = pg_fetch_row($RS);
      $loSql->omDisconnect();
      if ($laFila[0] == '') {
         $this->pcError = "CODIGO DE CURSO NO EXISTE";
         return false;
      }
      $this->pcCodCur = $laFila[0];
      $this->pcNomCur = $laFila[1];
      $this->pcCurCor = $laFila[2];
      $this->pcLabTeo = $laFila[3];
      $this->pcEstado = $laFila[4];
      // Marca laboratorio/teoria
      for ($i = 0; $i < count($this->paLabTeo); $i++) {
         if ($this->paLabTeo[$i][1] == $this->pcLabTeo) {
            $this->paLabTeo[$i][2] = true;
         }
      }
      // Marca Estado
      for ($i = 0; $i < count($this->paEstado); $i++) {
         if ($this->paEstado[$i][1] == $this->pcEstado) {
            $this->paEstado[$i][2] = true;
         }
      }
      return true;
   }

   public function omGrabar() {
      if (empty($this->pcCodCur)) {
         $this->pcError = 'CODIGO DE CURSO VACIO';
         return false;
      } else if (empty($this->pcNomCur)) {
         $this->pcError = 'DESCRIPCION/NOMBRE DEL CURSO VACIO';
         return false;
      } else if (empty($this->pcCurCor)) {
         $this->pcError = 'DESCRIPCION CORTA VACIA';
         return false;
      } else if (empty($this->pcLabTeo)) {
         $this->pcError = 'INDICADOR DE TEORIA/LABORATORIO NO DEFINIDA';
         return false;
      } else if (empty($this->pcEstado)) {
         $this->pcError = 'ESTADO NO DEFINIDO';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = 'CODIGO DE USUARIO VACIO';
         return false;
      }
      
      //echo $this->pcCodCur;
      //echo $this->pcLabTeo;
      
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      if ($this->plNuevo) {
         // Insertar
         $lcSql = "SELECT * FROM f_GrabarCurso(1, '$this->pcCodCur', '$this->pcNomCur', '$this->pcCurCor', '$this->pcLabTeo', '$this->pcEstado', '$this->pcCodUsu')";
      } else {
         // Actualizar
         $lcSql = "SELECT * FROM f_GrabarCurso(2, '$this->pcCodCur', '$this->pcNomCur', '$this->pcCurCor', '$this->pcLabTeo', '$this->pcEstado', '$this->pcCodUsu')";
      }
      //die($lcSql);
      $RS = $loSql->omExec($lcSql);
      $laFila = pg_fetch_row($RS);
      $loSql->omDisconnect();
      // Busca resultado
      $llOk = $this->mxError($laFila[0]);
      return $llOk;
   }
   
}
?>
