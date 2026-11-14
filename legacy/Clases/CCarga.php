<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Consulta carga de docentes
//-------------------------------------
class CCarga extends CBase {
   public $pcCodDoc = null;
   public $pcCarga  = null;

   public function __construct() {
      parent::__construct();
   }

   public function omAplicar() {
      if (empty($this->pcCodDoc)) {
         $this->pcError = 'CODIGO DE DOCENTE VACIO';
         return false;
      }
      $lcParam = '<DATA><CCODDOC>'.$this->pcCodDoc.'</CCODDOC><CPERIOD>20122</CPERIOD></DATA>';
      $lcParam = "python CargaDocente.py '$lcParam'";
      $xml = exec($lcParam);
      $this->pcCarga = $xml;
      //echo $xml;
      $xml = new SimpleXMLElement($xml);
      $xml->asXML();
      $lcError = $xml->ERROR;
      if (!empty($lcError)) {
         $this->pcError = $lcError;
         return false;
      }
      $this->pcCarga = str_replace("{br}", "<b199r>", $this->pcCarga);
      $this->pcCarga = str_replace("{/br}", "</br>", $this->pcCarga);
      return true;
   }
}
