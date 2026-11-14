<?php
require_once 'Clases/CBase.php';

// Clase de mantenimiento de normas
class CEnvioEmails extends CBase {
   public $paData, $pcCodUsu, $pcCodPer, $pcNroInc;
   protected $lcEmail;

   public function __construct() {
      parent::__construct();
      $this->paData = null;
   }

   // Inicia mantenimiento de normas
   public function omEnviar1() {
      if (empty($this->paData) or fxCount($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DETALLE DE INCIDENTES</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE VACIO</ERROR></DATA>';
         return false;
      }
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Emails de areas y descripcion de incidente
      $RS = $loSql->omExec("SELECT cArea1, cArea2, mDescri, cCodPer FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO EXISTE</ERROR></DATA>';
         return false;
      }
      $lcCodPer = $laFila['CCODPER'];
      $lcDescri = trim($laFila['MDESCRI']->load());
      $lcArea1  = $laFila['CAREA1'];
      $lcArea2  = $laFila['CAREA2'];
      // Email del responsable
      $RS = $loSql->omExec("SELECT cEmail FROM S02MPER WHERE cCodPer  = '$lcCodPer'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL RESPONSABLE NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->lcEmail = '';
      if (fxValEmail($laFila[0])) {
         $this->lcEmail .= ','.trim($laFila[0]);
      }
      // Emails de areas
      $llOk = $this->mxCargarEmails($loSql, $lcArea1, $lcArea2);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
      // Email de personal responsables de acciones y detalle de accion
      $lcTexto1 = '';
      foreach ($this->paData as $i) {
         $RS = $loSql->omExec("SELECT cEmail, cNombre FROM S02MPER WHERE cCodPer = '$i[3]'");
         $laFila = $loSql->fetch($RS);
         if (fxValEmail($laFila[0])) {
            $this->lcEmail .= ','.trim($laFila[0]);
         }
         $lcTexto1 .= substr($laFila[1], 0, 20).': '.$i[2].'. FECHA: '.$i[4].chr(13);
      }
      // Elimina email repetidos
      $this->mxEliminarEmailRepetidos();
      // Fecha y hora
      $RS = $loSql->omExec("SELECT TO_CHAR(SYSDATE, 'yyyy-mm-dd hh24:ss') FROM DUAL");
      $laFila = $loSql->fetch($RS);
      $lcFecha = $laFila[0];
      $loSql->omDisconnect();
      // Envio de emails
      $lcTexto = "Reporte: $this->pcNroInc".chr(13);
      $lcTexto .= "Fecha y hora: $lcFecha".chr(13);
      $lcTexto .= "Descripcion: $lcDescri".chr(13);
      $lcTexto .= $lcTexto1;
      $lo = new CSendEmail();
      $lo->pcForWho = $this->lcEmail;
      $lo->pcFrom    = 'webmaster@perurail.com';
      $lo->pcSubject = 'Evaluación del Reporte';
      $lo->pcMessage = $lcTexto;
      $llOk = $lo->omSend();
      if (!$llOk) {
         $this->pcError = $lo->pcError;
         return false;
      }
      return $llOk;
   }

   // Carga emails de responsable superior y responsables de areas
   protected function mxCargarEmails($p_oSql, $p_cArea1, $p_cArea2) {
      // Email de personal maximo responsable
      $RS = $p_oSql->omExec("SELECT mTexto, cCodTab FROM S02TTAB WHERE cCodTab = '030' AND cTipReg = '1' AND cCodigo = '99'");
      $laFila = $p_oSql->fetch($RS);
      if (empty($laFila[1])) {
         $this->lcEmail = 'webmaster@perurail.com';
      } else{
         $this->lcEmail = trim($laFila[0]->load());
      }
      // Email de personal responsable de areas
      if ($p_cArea1 == $p_cArea2 and $p_cArea1 != '99') {
         $RS = $p_oSql->omExec("SELECT mTexto, cCodTab FROM S02TTAB WHERE cCodTab = '030' AND cTipReg = '1' AND cCodigo = '$p_cArea1'");
         $laFila = $p_oSql->fetch($RS);
         if (empty($laFila[1])){
            $lcTmp = 'webmaster@perurail.com';
         } else {
            $lcTmp = $laFila[0]->load();
         }
         $this->lcEmail .= ','.$lcTmp;
      } else {
         if ($p_cArea1 != '99') {
            $RS = $p_oSql->omExec("SELECT mTexto, cCodTab FROM S02TTAB WHERE cCodTab = '030' AND cTipReg = '1' AND cCodigo = '$p_cArea1'");
            $laFila = $p_oSql->fetch($RS);
            if (empty($laFila[1])){
               $lcTmp = 'webmaster@perurail.com';
            } else {
               $lcTmp = $laFila[0]->load();
            }
            $this->lcEmail .= ','.$lcTmp;
         }
         if ($p_cArea2 != '99') {
            $RS = $p_oSql->omExec("SELECT mTexto, cCodTab FROM S02TTAB WHERE cCodTab = '030' AND cTipReg = '1' AND cCodigo = '$p_cArea2'");
            $laFila = $p_oSql->fetch($RS);
            if (empty($laFila[1])){
               $lcTmp = 'webmaster@perurail.com';
            } else {
               $lcTmp = $laFila[0]->load();
            }
            $this->lcEmail .= ','.$lcTmp;
         }
      }
      return true;
   }
   
   // Elimina emails repetidos
   protected function mxEliminarEmailRepetidos() {
      $laTmp = explode(',', $this->lcEmail);
      // Elimina repetidos
      $laEmail = null;
      for ($i = 0;$i < fxCount($laTmp);$i++) {
          $llFlag = true;
          for ($j = 0;$j < fxCount($laEmail);$j++) {
              if ($laTmp[$i] == $laEmail[$j]) {
                 $llFlag = false;
                 break;
              }
          }
          if ($llFlag and fxValEmail($laTmp[$i])) {
             $laEmail[] = array($laTmp[$i]);
          }
      }
      $this->lcEmail = '';
      foreach($laEmail as $i) {
          $this->lcEmail .= $i[0].',';
      }
   }
   
   // Envio de email a responsables de plan de accion de de ACR
   public function omEnviar2() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->paData) or fxCount($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DETALLE DE INCIDENTES</ERROR></DATA>';
         return false;
      }
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Descripcion del incidente
      $RS = $loSql->omExec("SELECT mDescri FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = "<DATA><ERROR>NUMERO DE COMPROBANTE [$this->pcNroInc] NO EXISTE</ERROR></DATA>";
         $loSql->omDisconnect();
         return false;
      }
      $lmDescri = $laFila[0]->load();
      // Fecha y hora
      $RS = $loSql->omExec("SELECT TO_CHAR(SYSDATE, 'yyyy-mm-dd hh24:ss') FROM DUAL");
      $laFila = $loSql->fetch($RS);
      $lcFecha = $laFila[0];
      // Envio de emails a responsables
      $lo = new CSendEmail();
      $lcTexto = "Incidente: $this->pcNroInc".chr(13);
      $lcTexto .= "Fecha y hora: $lcFecha".chr(13);
      foreach($this->paData as $i) {
         $lcTexto1 = '';
         $RS = $loSql->omExec("SELECT cNombre, cEmail FROM S02MPER WHERE cCodPer = '$i[3]'");
         $laFila = $loSql->fetch($RS);
         $lcNombre = $laFila[0];
         $lcEmail = $laFila[1];
         if (fxValEmail($lcEmail)) {
            // Email de responsables del area
            $RS = $loSql->omExec("SELECT cArea FROM TABLE (F_S02MPER_1('$i[3]'))");
            $laFila = $loSql->fetch($RS);
            $lcArea = $laFila[0];
            if (!empty($lcArea)) {
               $RS = $loSql->omExec("SELECT mTexto FROM S02TTAB WHERE ccodTab = '030' AND cCodigo = '$lcArea' AND cTipReg = '1'");
               $laFila = $loSql->fetch($RS);
               $lcEmails = $laFila[0]->load();
               if (!empty($lcEmails)) {
                  $laEmails = explode(',', $lcEmails);
                  for ($j = 0;$j < fxCount($laEmails);$j++) {
                      if (fxValEmail($laEmails[$j])) {
                         $lcEmail .= ','.$laEmails[$j];
                      }
                  }
               }
            }
            $lcTexto1 = $lcTexto."Para : $lcNombre".chr(13);
            $lcTexto1 .= "Descripcion: $lmDescri".chr(13);
            $lo->pcForWho  = $lcEmail;
            $lo->pcFrom    = 'webmaster@perurail.com';
            $lo->pcSubject = 'SEGUIMIENTO ACR';
            $lo->pcMessage = $lcTexto1;
            $llOk = $lo->omSend();
            if (!$llOk) {
               $loSql->omDisconnect();
               $this->pcError = $lo->pcError;
               return false;
            }
         }
      }
      $loSql->omDisconnect();
      return true;
   }
}
?>
