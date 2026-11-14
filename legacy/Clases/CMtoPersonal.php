<?php
include "Clases/CBase.php";

class CMtoPersonal extends CBase {
   public $pcNomPer = null;
   public $pcCodPer = null;
   public $paCodPer = null;
   public $pcNroDni = null;
   public $pcEmail  = null;

   function omBuscar(){
      if (empty($this->pcNomPer)) {
         $this->pcError = "NOMBRE DE PERSONAL VACIO";
         return false;
      }
      $lcNomPer = trim($this->pcNomPer).'%';
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $i = 0;
      $lcSql = "SELECT cNombre, cCodPer FROM L01MPER WHERE cNombre LIKE '$lcNomPer'";
      $RS =  $loSql->omExec($lcSql);
      while ($laFila = pg_fetch_row($RS)) {
         $i++;
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
      }    
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = "NO HAY PERSONAL QUE CUMPLA CRITERIO";
         return false;
      }
      return true;
   }

   function omEditar(){
      if (empty($this->pcCodPer)) {
         $this->pcError = "CODIGO DE PERSONAL VACIO";
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cNombre, cNroDni, cEmail FROM L01MPER WHERE cCodPer = '$this->pcCodPer'";
      $RS =  $loSql->omExec($lcSql);
      $laFila = pg_fetch_row($RS);
      $loSql->omDisconnect();
      $this->pcNomPer = $laFila[0];
      $this->pcNroDni = $laFila[1];
      $this->pcEmail  = $laFila[2];
      if (empty($this->pcNomPer)) {
         $this->pcError = "CODIGO DE PERSONAL NO DEFINIDO";
         return false;
      }
      return true;
   }

   function omGrabar(){
      if (empty($this->pcCodPer)) {
         $this->pcError = "CODIGO DE PERSONAL VACIO";
         return false;
      } else if (empty($this->pcNomPer)) {
         $this->pcError = "NOMBRE DE PERSONAL VACIO";
         return false;
      } else if (empty($this->pcNroDni)) {
         $this->pcError = "NUMERO DE DNI VACIO";
         return false;
      } else if (empty($this->pcEmail)) {
         $this->pcError = "EMAIL VACIO";
         return false;
      }
      $lcCodPer = trim($this->pcCodPer);
      $lcNombre = trim($this->pcNomPer);
      $lcNroDni = trim($this->pcNroDni);
      $lcEmail = trim($this->pcEmail);
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "UPDATE L01MPER SET cNombre = '$lcNombre', cNroDni = '$lcNroDni', cEmail = '$lcEmail' WHERE cCodPer = '$lcCodPer'";
      $llOk = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      if (!$llOk) {
         $this->pcError = "ERROR EN GRABACION. REVISE";
      }
      return $llOk;
   }
   
   
}
?>
