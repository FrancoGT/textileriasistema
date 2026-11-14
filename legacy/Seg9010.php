<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoUnidad.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif ((@$_POST['Boton1'] == 'Aceptar')) {
      $lcCodUni = $_REQUEST['pcCodUni'];
      if (empty($lcCodUni)) {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->assign('saCodUni', $_SESSION['paCodUni']);      
         $loSmarty->display('Plantillas/Seg9010.tpl');
      } else {
         $_SESSION['pcCodUni'] = $lcCodUni;
         fxHeader($_SESSION['pcVolver'].'?Id=Aceptar');
      }
   } elseif ((@$_POST['Boton1'] == 'Salir')) {
      fxHeader($_SESSION['pcVolver']);
   } else {
      $lo = new CMtoUnidad();
	  $llOk = $lo->omInitUnidades();
      if (!$llOk) {
         fxHeader($_SESSION['pcVolver'], fxXmlError($lo->pcError));
         return;
      }
	  $_SESSION['paCodUni']=$lo->paCodUni;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saCodUni', $lo->paCodUni);      
      $loSmarty->display('Plantillas/Seg9010.tpl');
   }
?>