<?php
require_once 'Clases/CBase.php';

//------------------------------------------------------------
// Envio de emails:
// 1) Vencimiento de licencias. Solo los viernes
// 2) Recordatorio de trabajos realizados. Todos los vencidos
//------------------------------------------------------------
class CServicios extends CBase {
   public $paEstado;
   protected $ldFecSis, $laAreas, $lcEmailG, $lcEmailV;
   
   public function __construct() {
      parent::__construct();
      $this->paEstado = $this->laAreas = null;
   }

   // Envia emails recordatorios
   public function omEmailRecodatorio() {
      // Conecta DB      
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Fecha del sistema
      $llOk = $this->mxFechaSistema($loSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
      // Email por areas
      $llOk = $this->mxEmailAreas($loSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
      // Licencias
      $llOk = $this->mxLicencias($loSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
      // Seguimiento de tareas de incidentes
      $llOk = $this->mxTareasIncidentes($loSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
      // Seguimiento de tareas de ACR
      $llOk = $this->mxTareasACR($loSql);
      $loSql->omDisconnect();
      return $llOk;
   }

   // Arreglo de emails
   protected function mxEmailAreas($p_oSql) {
      $i = 0;      
      $RS = $p_oSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), mTexto, cTipReg FROM S02TTAB WHERE cCodTab = '030'");
      while ($laFila = $p_oSql->fetch($RS)) {
         $lcEmails = trim($laFila[1]->load());
         $llOk = $this->mxValEmails($lcEmails);
         if (!$llOk) {
            if ($laFila[2] == '0') {
               $this->pcError = '<DATA><ERROR>EMAIL DE GERENCIA DE SEGURIDAD NO ES VALIDO</ERROR></DATA>';
               return false;
            } elseif ($laFila[1] == '99') {
               $this->pcError = '<DATA><ERROR>EMAIL DE CONTROLADOR DE LICENCIAS NO ES VALIDO</ERROR></DATA>';
               return false;
            }
            continue;
         }
         $i++;
         if ($laFila[2] == '0') {
            $this->lcEmailG = $lcEmails;
         } elseif ($laFila[0] == '99') {
            $this->lcEmailV = $lcEmails;
         }
         $this->laAreas[] = array($laFila[0], $lcEmails);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY AREAS DEFINIDAS CON EMAILS VALIDOS</ERROR></DATA>';
         return false;
      }
	  return true;
   }
   
   // Seguimiento de tareas de incidentes
   protected function mxTareasIncidentes($p_oSql) {
      // Email de prueba
      $this->mxSendEmail('fparedesmarchena@gmail.com', 'VERIFICACION DE ENVIO DE EMAILS', 'VERIFICACION DE ENVIO DE EMAILS');
      // Arreglo temporal
      $laData = null;
      // Trae todas las tareas vencidas a la fecha de proceso
      $ldFecSis = substr($this->ldFecSis, 0, 10);
      $RS = $p_oSql->omExec("SELECT cNombre, cEmail, cDescri, dFecha1, cArea FROM V_S02DINC_1 WHERE dFecha < TO_DATE($ldFecSis, 'yyyy-mm-dd') AND cCondic = 'P' AND cTipo = 'I'");
      while ($laFila = $p_oSql->fetch($RS)) {
         $lcEmail = $this->lcEmailG;
         if (!empty($laFila[1]) and fxValEmail($laFila[1])) {
            $lcEmail .= ','.$laFila[1];
         }
         // Ubica area a quien enviar email
         foreach($this->laAreas as $i) {
            if ($i[0] == $laFila[4]) {
               $lcEmail .= ','.$i[1];
               break;
            }
         }
         $laData[] = array($laFila[0], $lcEmail, $laFila[2], $laFila[3]);
      }
      // Envia email
      foreach($laData as $laFila) {
         $lcTexto = "TAREAS DE INCIDENTES PENDIENTES VENCIDAS
                    '$laFila[0]' - '$laFila[2]' - '$laFila[3]'";
         $this->mxSendEmail($lcEmail, 'TAREAS INCIDENTES PENDIENTES', $lcTexto);
      }
      return true;
   }
   
   // Seguimiento de tareas de ACR
   protected function mxTareasACR($p_oSql) {
      // Arreglo temporal
      $laData = null;
      // Trae todas las tareas vencidas a la fecha de proceso
      $ldFecSis = substr($this->ldFecSis, 0, 10);
      $RS = $p_oSql->omExec("SELECT cNombre, cEmail, cDescri, dFecha1, cArea FROM V_S02DINC_1 WHERE dFecha < TO_DATE($ldFecSis, 'yyyy-mm-dd') AND cCondic = 'P' AND cTipo = 'C'");
      while ($laFila = $p_oSql->fetch($RS)) {
         $lcEmail = $this->lcEmailG;
         if (!empty($laFila[1]) and fxValEmail($laFila[1])) {
            $lcEmail .= ','.$laFila[1];
         }
         // Ubica area a quien enviar email
         foreach($this->laAreas as $i) {
            if ($i[0] == $laFila[4]) {
               $lcEmail .= ','.$i[1];
               break;
            }
         }
         $laData[] = array($laFila[0], $lcEmail, $laFila[2], $laFila[3]);
      }
      // Envia email
      foreach($laData as $laFila) {
         $lcTexto = "TAREAS DE ACR PENDIENTES VENCIDAS
                    '$laFila[0]' - '$laFila[2]' - '$laFila[3]'";
         $this->mxSendEmail($lcEmail, 'TAREAS INCIDENTES PENDIENTES', $lcTexto);
      }
      return true;
   }
      
   // Envia emails de expiracion de licencias. Solo los dia viernes
   protected function mxLicencias($p_oSql) {
      // Fecha del servidor
      $ldFecha = date('Y-m-d');
      $lnDia = date('N');
      // Dia viernes
      if ($lnDia != 5) {
         return true;
      }
      // Suma 96 dias
      $ldFecha1 = fxAddDate($ldFecha, 96);
      // Trae licencias que vencen antes de esa fecha
      $lcTexto = 'LICENCIAS POR VENCER'.chr(10);
      $RS = $p_oSql->omExec("SELECT cNombre, cTipLic, TO_CHAR(dFecFin, 'yyyy-mm-dd') FROM V_S02DLIC WHERE dFecFin <= TO_DATE('$ldFecha1', 'yyyy-mm-dd') AND cEstado IN ('A', 'D') ORDER BY dFecFin");
      $i = 0;
      while ($laFila = $p_oSql->fetch($RS)) {
         $lcTexto .= $laFila[0].chr(10);
         $lcTexto .= $laFila[1].chr(10);
         $lcTexto .= $laFila[2].chr(10).chr(10);
         $i++;
      }
      if ($i == 0) {
         return true;
      }
      $this->mxSendEmail($this->lcEmailV, 'VENCIMIENTO DE LICENCIAS',  $lcTexto);
      return true;
   }
   
   protected function mxFechaSistema($p_oSql) {
      $RS = $p_oSql->omExec("SELECT TO_CHAR(SYSDATE, 'yyyy-mm-dd hh24:MM') FROM DUAL");
      $laFila = $p_oSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = '<DATA><ERROR>NO SE PUDO OBTENER LA FECHA Y HORA DEL SISTEMA</ERROR></DATA>';
         return false;
      }
      $this->ldFecSis = $laFila[0];
      return true;
   }

   protected function mxValEmails($p_cEmails) {
      $laEmails = explode(',', $p_cEmails);
      $llOk = false;
      foreach($laEmails as $i) {
          $llFlag = fxValEmail($i);
          if ($llFlag) {
              return true;
          }
      }
      return false;
   }
   
   protected function mxSendEmail($p_cEmail, $p_cSubject, $p_cTexto) {
      $lo = new CSendEmail();
      $lo->pcForWho = $p_cEmail;
      $lo->pcFrom =   'webmaster@perurail.com';
      $lo->pcSubject = $p_cSubject;
      $lo->pcMessage = $p_cTexto;
      $llOk = $lo->omSend();
      if (!$llOk) {
         echo '<br>ERROR';
         $this->pcError = $lo->pcError;
         return false;
      }
      return true;
   }
}
?>