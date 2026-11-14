<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Matricula alumno en curso
//-------------------------------------
class CAlumnoHorario extends CBase {
   public $pcCurHor = null;
   public $pcGlosa = null;
   public $pcNomAlu = null;
   public $pcCodAlu = null;
   public $paCurHor = null;

   public function __construct() {
      parent::__construct();
   }

   //--- Carga cursos y horarios
   public function omAplicar() {
      // Condiciones de excepcion
      if (empty($this->pcCodAlu)) {
         $this->pcError = 'CODIGO DE ALUMNO VACIO';
         return false;
      } 
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cNombre FROM V_Alumnos WHERE cCodAlu = '$this->pcCodAlu'";
      $RS = $loSql->omExec($lcSql);
      $laFila = pg_fetch_row($RS);
      $this->pcNomAlu = $laFila[0];
      if (empty($this->pcNomAlu)) {
         $loSql->omDisconnect();
         $this->pcError = 'CODIGO DE ALUMNO NO EXISTE';
         return false;
      } 
      $lcSql = "SELECT cGlosa, cCurHor FROM V_ClasesA WHERE cCodAlu = '$this->pcCodAlu' ORDER BY cNroDia, cHorIni";
      $RS = $loSql->omExec($lcSql);
      if (!$RS) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }      
      while ($laFila = pg_fetch_row($RS)) {
         $this->paCurHor[] = array($laFila[0], $laFila[1]);         
      }      
      $loSql->omDisconnect();
      return $llOk;
   }
   
   public function omEliminar() {
      // Condiciones de excepcion
      if (empty($this->pcCodAlu)) {
         $this->pcError = 'CODIGO DE ALUMNO VACIO';
         return false;
      } else if (empty($this->pcCurHor)) {
         $this->pcError = 'CODIGO DE HORARIO VACIO';
         return false;
      } 
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = " SELECT * FROM f_eliminaralumnohorario('$this->pcCodAlu', '$this->pcCurHor')";
      $RS = $loSql->omExec($lcSql);
      $laFila = pg_fetch_row($RS);
      $llOk = strpos($laFila[0], "ERROR");
      if (!$llOk) {
         $this->pcError = $laFila[0];
      }
      $loSql->omDisconnect(); 
      return $llOk;
   }   
}
?>
