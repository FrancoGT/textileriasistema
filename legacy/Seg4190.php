<?php
   // Reporte de incidentes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG4190', 'Mnu0000.php?Id=6000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Buscar'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg4190';
      fxHeader('Seg9008.php');
   } else if (@$_POST['Aplicar'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      $_SESSION['plNuevo'] = false;
      $lo = new CIncidentes();
      if (@$_GET['Id'] == 'Aceptar') {
         $lo->pcNroInc = $_SESSION['pcNroInc'];
      } else {
         $lo->pcNroInc = $_REQUEST['pcNroInc'];
      }
      $llOk = $lo->omAplicar2();
      if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
         fxAlert($lcMessage);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg4190.tpl');
      } else {
         $_SESSION['pmDatos'] = $lo->pmDatos;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('smDatos',  $lo->pmDatos);
         $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
         $_SESSION['paData'] = $lo->paCodPin;
         //print_r($_SESSION['paData']); die();
         $loSmarty->assign('saCodPin', $lo->paCodPin);
         $loSmarty->assign('saCodUin', $lo->paCodUin);
         $loSmarty->assign('saCodPer', $lo->paCodPer);
         $loSmarty->assign('saCodUni', $lo->paCodUni);
         fxDumpArray();
         $loSmarty->display('Plantillas/Seg4190.tpl');
      }
   } else if(@$_POST['Nuevo'] == 'Nuevo'){
      fxLoadVar();
      $lo = new CIncidentes();      
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $llOk = $lo->omAgregarPersonalInvolucrado();
      if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
      } else {
         $i = $lo->paData;
         $laData = $_SESSION['paData'];
         $i = fxCount($laData) + 1;
         $laData[$i]= array($i, $lo->paData[0][0] , $lo->paData[0][1] , $lo->paData[0][2] , $lo->paData[0][3], $lo->paData[0][4] , $lo->paData[0][5], '', '', '', '');
         $_SESSION['paData'] = $laData;
      }
      fxDumpArray();
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scNroInc', $_REQUEST['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('scCodPer',  $_SESSION['pcCodPer']);
      $loSmarty->assign('scPuesto',  $_SESSION['pcPuesto']);
      $loSmarty->assign('saCodPin', $_SESSION['paData']);
      $loSmarty->assign('saCodUin', $_SESSION['paData1']);
      $loSmarty->display('Plantillas/Seg4190.tpl');
      fxAlert($lcMessage);
   } else if (@$_POST['Eliminar'] == 'Eliminar') {
       $laCodUin = null;
       if (isset($_REQUEST['paData1'])) {
           $laCodUin = $_REQUEST['paData1'];
       }
       $laCodPin = $_REQUEST['paData'];
       if (isset($_REQUEST['paCheck']))
           $laCodPin = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
       fxDumpArray();
       $loSmarty->assign('scBehavior', '1');
       $loSmarty->assign('scNroInc', $_REQUEST['pcNroInc']);
       $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
       $loSmarty->assign('saCodPin', $laCodPin);
       $loSmarty->assign('saCodUin', $laCodUin);
       $loSmarty->display('Plantillas/Seg4190.tpl');
   } else if (@$_POST['NuevoN'] == 'Nuevo') {
      fxLoadVar();
      $laCodPin = null;
      if (isset($_REQUEST['paData'])) {
         $laCodPin = $_REQUEST['paData'];
      }
      $laCodUin = null;
      if (isset($_REQUEST['paData1'])) {
         $laCodUin = $_REQUEST['paData1'];
      }
      $j = fxCount($laCodUin);
      $laCodUin[] = array($j + 1, '00000','','');
      fxDumpArray();
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scNroInc', $_REQUEST['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('scCodPer',  $_SESSION['pcCodPer']);
      $loSmarty->assign('saCodUin', $laCodUin);
      $loSmarty->assign('saCodPin', $laCodPin);
      $loSmarty->display('Plantillas/Seg4190.tpl');
   } else if (@$_POST['EliminarN'] == 'Eliminar') {
      $laCodPin = null;
      if (isset($_REQUEST['paData'])) {
         $laCodPin = $_REQUEST['paData'];
      }
      $laCodUin = $_REQUEST['paData1'];
      if (isset($_REQUEST['paCheck1']))
         $laCodUin = fxEliminarFila($_REQUEST['paData1'], $_REQUEST['paCheck1']);
      fxDumpArray();
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scNroInc', $_REQUEST['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('saCodPin', $laCodPin);
      $loSmarty->assign('saCodUin', $laCodUin);
      $loSmarty->display('Plantillas/Seg4190.tpl');
   } else if (@$_POST['Grabar'] == 'Grabar') {
      fxLoadVar();
      $lo = new CIncidentes();
      $lo->paCodPin   = $_REQUEST['paData'];
      $lo->paCodUin   = $_REQUEST['paData1'];
      $_SESSION['paData']= $lo->paCodPin;
      $_SESSION['paData1']= $lo->paCodUin;
      $lo->pcNroInc = $_REQUEST['pcNroInc'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarPUInvInc();
      if (!$llOk) { // Error
         fxDumpArray();
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scNroInc', $_REQUEST['pcNroInc']);
         $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
         $loSmarty->assign('saCodPin', $_SESSION['paData']);
         $loSmarty->assign('saCodUin', $_SESSION['paData1']);
         $loSmarty->display('Plantillas/Seg4190.tpl');
         $lcMessage = fxXmlError($lo->pcError);
         fxAlert($lcMessage);
      } else {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg4190.tpl');
         fxAlert('GRABACIÓN CONFORME');
      }
   } else {
      $lo = new CIncidentes();
      $llOk = $lo->omInitIncidentes();
      if (!$llOk) {
         $loSmarty->assign('scAviso', $lo->pcError);
         $loSmarty->assign('scVolver', 'Seg4190.php');
         $loSmarty->display('Plantillas/SIS1000.tpl');
      } else {
         $lo->paCodPer[] = array('999999', '*');
         unset($_SESSION['paCodPer']);
         $_SESSION['paCodPer'] = $lo->paCodPer;
         unset($_SESSION['paClaInc']);
         $_SESSION['paClaInc'] = $lo->paClaInc;
         unset($_SESSION['paTipInc']);
         $_SESSION['paTipInc'] = $lo->paTipInc;
         unset($_SESSION['paLugar']);
         $_SESSION['paLugar'] = $lo->paLugar;
         unset($_SESSION['paAreas']);
         $_SESSION['paAreas'] = $lo->paAreas;
         unset($_SESSION['paCausa']);
         $_SESSION['paCausa'] = $lo->paCausa;
         unset($_SESSION['paCodPry']);
         $_SESSION['paCodPry'] = $lo->paCodPry;
         unset($_SESSION['paCodEmp']);
         $_SESSION['paCodEmp'] = $lo->paCodEmp;
         unset($_SESSION['paConsec']);
         $_SESSION['paConsec'] = $lo->paConsec;
         unset($_SESSION['paPotenc']);
         $_SESSION['paPotenc'] = $lo->paPotenc;
         unset($_SESSION['paAccTom']);
         $_SESSION['paAccTom'] = $lo->paAccTom;
         unset($_SESSION['paCondic']);
         $_SESSION['paCondic'] = $lo->paCondic;
         unset($_SESSION['paPuesto']);
         $_SESSION['paPuesto'] = $lo->paPuesto;
         unset($_SESSION['paCodUni']);
         $_SESSION['paCodUni'] = $lo->paCodUni;
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg4190.tpl');
      }
   }
   
   function fxLoadVar() {
      $_SESSION['pcNroInc'] = $_REQUEST['pcNroInc'];
      //$_SESSION['pmDatos']  = $_REQUEST['pmDatos'];
      $_SESSION['paData']   = $_REQUEST['paData'];
      $_SESSION['paData1']  = $_REQUEST['paData1'];
      $_SESSION['pmAnaPre'] = $_REQUEST['pmAnaPre'];
      $_SESSION['pcFecInc'] = $_REQUEST['pcFecInc'];
      $_SESSION['pcHorInc'] = $_REQUEST['pcHorInc'];
      $_SESSION['pcCodPe1'] = $_REQUEST['pcCodPe1'];
      $_SESSION['pcCodPe2'] = $_REQUEST['pcCodPe1'];
      $_SESSION['pcCodEmp'] = $_REQUEST['pcCodEmp'];
      $_SESSION['pcCodPer'] = $_REQUEST['pcCodPer'];
      $_SESSION['pcConsec'] = $_REQUEST['pcConsec'];
      $_SESSION['pcPotenc'] = $_REQUEST['pcPotenc'];
      $_SESSION['pcCondic'] = $_REQUEST['pcCondic'];
      $_SESSION['pcAccTom'] = $_REQUEST['pcAccTom'];
      $_SESSION['pcCodPry'] = $_REQUEST['pcCodPry'];
      $_SESSION['pcArea1']  = $_REQUEST['pcArea1'];
      $_SESSION['pcArea2']  = $_REQUEST['pcArea1'];
      $_SESSION['pcCausa']  = $_REQUEST['pcCausa'];
      $_SESSION['pcTipInc'] = $_REQUEST['pcTipInc'];
      $_SESSION['pcClaInc'] = $_REQUEST['pcClaInc'];
      $_SESSION['pcLugar']  = $_REQUEST['pcLugar'];
      $_SESSION['pmDescri'] = $_REQUEST['pmDescri'];
      $_SESSION['pmSugere'] = $_REQUEST['pmSugere'];
      $lcReqInv = $_REQUEST['pcReqInv'];
      if ($lcReqInv != 'S')
         $lcReqInv = 'N';
       $_SESSION['pcReqInv'] = $lcReqInv;
   }
   
   function fxDumpArray() {
      global $loSmarty;
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
      $loSmarty->assign('smAnaPre', $_SESSION['pmAnaPre']);
      $loSmarty->assign('saConsec', $_SESSION['paConsec']);
      $loSmarty->assign('saPotenc', $_SESSION['paPotenc']);
      $loSmarty->assign('saCondic', $_SESSION['paCondic']);
      $loSmarty->assign('saReqInv', $_SESSION['paReqInv']);
      $loSmarty->assign('saAccTom', $_SESSION['paAccTom']);
      $loSmarty->assign('saCodPry', $_SESSION['paCodPry']);
      $loSmarty->assign('saAreas',  $_SESSION['paAreas']);
      $loSmarty->assign('saCausa',  $_SESSION['paCausa']);
      $loSmarty->assign('saTipInc', $_SESSION['paTipInc']);
      $loSmarty->assign('saClaInc', $_SESSION['paClaInc']);
      $loSmarty->assign('saLugar',  $_SESSION['paLugar']);
      $loSmarty->assign('saPuesto', $_SESSION['paPuesto']);
      $loSmarty->assign('saCodUni', $_SESSION['paCodUni']);
   }
?>