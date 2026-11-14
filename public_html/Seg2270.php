<?php
   // Levantar archivos anexos al incidentes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2270', 'Mnu0000.php?Id=4002'))
      return;
   if (!empty($_GET['pcNroInc'])) {
      $_SESSION['pcNroInc']=$_GET['pcNroInc'];
      fxAplicar();
      return;
   }
   if (!fxInitSession()) { 
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Buscar'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2270.php';
      fxHeader('Seg9008.php?Id=Req');
   } else if (@$_POST['Aplicar'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if (@$_POST['Nuevo'] == 'Nuevo' or @$_GET['Id'] == 'Aceptar') {
      $lo = new CIncidentes();
      if (@$_GET['Id'] == 'Aceptar') 
         $lo->pcNroInc = $_SESSION['pcNroInc'];
      else  
         $lo->pcNroInc = $_REQUEST['pcNroInc'];
      $llOk = $lo->omAplicar5();
      if (!$llOk) {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2270.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['pcNroInc'] = $lo->pcNroInc;
         $_SESSION['pmDatos']  = $lo->pmDatos;
         $_SESSION['paData']   = $lo->paData;
         $loSmarty->assign('scNroInc',  $_SESSION['pcNroInc']);
         $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
         $loSmarty->assign('saData', $_SESSION['paData']);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg2270.tpl');
      }
   } else if (@$_POST['Boton2'] == 'Siguiente') {
      fxHeader('Seg2310.php?pcNroInc='.$_SESSION['pcNroInc']);
      return;
   } else if(@$_POST['Subir'] == 'Subir') {
      $lo = new CIncidentes();
      $lo->pcNroInc = $_SESSION['pcNroInc'];
      $lo->paFiles  = $_FILES;
      $llOk = $lo->omSubir();
      if (!$llOk)
         $lcMessage = $lo->pcError;
      else
         $lcMessage = 'ARCHIVO CARGADO CORRECTAMENTE';
      fxAplicar();
      fxAlert($lcMessage);
   } else {
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2270.tpl');
   }
   
   function fxAplicar() {
      global $loSmarty;
      $lo = new CIncidentes();
      if (@$_GET['Id'] == 'Aceptar' or !empty($_GET['pcNroInc'])) 
         $lo->pcNroInc = $_SESSION['pcNroInc'];
      else  
         $lo->pcNroInc = $_REQUEST['pcNroInc'];
      $llOk = $lo->omAplicar5();
      if (!$llOk) {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2270.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['pcNroInc'] = $lo->pcNroInc;
         $_SESSION['pmDatos']  = $lo->pmDatos;
         $_SESSION['paData']   = $lo->paData;
         $loSmarty->assign('scNroInc',  $_SESSION['pcNroInc']);
         $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
         $loSmarty->assign('saData', $_SESSION['paData']);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg2270.tpl');
      }
   }
?>
