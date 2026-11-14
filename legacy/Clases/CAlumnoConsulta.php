<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Matricula alumno en curso
//-------------------------------------
class CAlumnoConsulta extends CBase {
   public $pcCodAlu = null;
   public $pdFecha1 = null;
   public $pdFecha2 = null;
   public $pcNomAlu = null;
   public $paLogAsi = null;
   public $paFechas = null;

   public function __construct() {
      parent::__construct();
   }

   public function omConsultar() {
      if (empty($this->pcCodAlu)) {
         $this->pcError = 'CODIGO DE ALUMNO VACIO';
         return false;
      } else if (empty($this->pdFecha1)) {
         $this->pcError = 'FECHA INICIAL VACIA';
         return false;
      } else if (empty($this->pdFecha2)) {
         $this->pcError = 'FECHA FINAL VACIA';
         return false;
      } else if ($this->mxValDate($this->pdFecha1)) {
         $this->pcError = 'ERROR EN FECHA INICIAL';
         return false;
      } else if ($this->mxValDate($this->pdFecha2)) {
         $this->pcError = 'ERROR EN FECHA FINAL';
         return false;
      }
      $lcParam = '<DATA><CCODALU>'.$this->pcCodAlu.'</CCODALU><DFECHA1>'.$this->pdFecha1.'</DFECHA1><DFECHA2>'.$this->pdFecha2.'</DFECHA2></DATA>';
      $lcParam = "python LogAsistencia.py '$lcParam'";
      $xml = exec($lcParam);
      $xml = new SimpleXMLElement($xml);
      $xml->asXML();
      $lcError = $xml->ERROR;
      if (!empty($lcError)) {
         $this->pcError = $lcError;
         return false;
      }
      $this->pcNomAlu = $xml->CNOMALU;
      $this->paLogAsi = array();
      foreach($xml->D as $i){
         $this->paFechas[] = $i;
      }
      foreach($xml->CNOMALU->CNOMCUR as $i){         
         $laAsiste = array();
         foreach($i->A as $j){
            $laAsiste[] = $j;
         }
         $this->paLogAsi[] = array($i, $laAsiste);
      }
      return true;
   }
}
