<?php

require_once "Clases/CBase.php";

//-------------------------------------
// Mantenimiento alumnos
//-------------------------------------
class CMtoHorarios extends CBase {

   public $pcCurHor = null;

   public function __construct() {
      parent::__construct();
   }

   //--- Elimina Horario
   public function omEliminar() {
      // Condiciones de excepcion
      if (empty($this->pcCurHor)) {
         $this->pcError = 'CODIGO DE HORARIO VACIO';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT CALUHOR FROM L01DASI WHERE CALUHOR IN(SELECT CALUHOR FROM L01PALU WHERE CCURHOR = '$this->pcCurHor') LIMIT 1";
      echo $lcSql."<br>";
      $RS = $loSql->omExec($lcSql);
      $laFila = pg_fetch_row($RS);
      if (!empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = 'ERROR: HORARIO TIENE ALUMNOS CON ASISTENCIA';
         return false;
      }
      $lcSql = "DELETE FROM L01PALU WHERE CCURHOR = '$this->pcCurHor'";echo $lcSql."<br>";
      $llOk = $loSql->omExec($lcSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = 'ERROR EN ELIMINACION DE ALUMNOS. AVISE A COORDINACION';
         return false;
      }
      $lcSql = "DELETE FROM L01PCHO WHERE CCURHOR = '$this->pcCurHor'";echo $lcSql."<br>";
      $llOk = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      if (!$llOk) {
         $this->pcError = 'ERROR EN ELIMINACION DE HORARIO. AVISE A COORDINACION';
         return false;
      }
      return true;
   }

}

?>