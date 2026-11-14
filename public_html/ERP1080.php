<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoTablas.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP1080', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   }  elseif (@$_POST['Nuevo'] == 'Nuevo') {
      $laData = null;

      if (isset($_REQUEST['paData'])) {
         $laData = $_REQUEST['paData'];
      }
      $i = count($laData) + 1;
      $laData[$i] = array($i, '0', '', '', 1);
      $loSmarty->assign('flagn', '1'); 
      $loSmarty->assign('scBehavior', '1'); 
      $loSmarty->assign('scCodTab', $_SESSION['pcCodTab']); 
      $loSmarty->assign('scDescri', $_SESSION['pcDescri']); 
      $loSmarty->assign('saData',   $laData);
      $loSmarty->display('Plantillas/ERP1080.tpl');

   } elseif (@$_POST['Eliminar'] == 'Eliminar') {
      // No procede eliminar si es antiguo
      $llOk = true;
      if (isset($_REQUEST['paCheck'])) {
         foreach ($_REQUEST['paCheck'] as $i) {
            foreach ($_SESSION['paDataB'] as $j) {
               if ($i == $j[0]) {
                  $llOk = false;
                  break;
               }
            }
            if (!$llOk)
               break;
         }
      }
      $laData = $_REQUEST['paData'];
      if (!$llOk) {   
         fxAlert('NO SE PUEDE ELIMINAR FILA ANTIGUA');
      } elseif (isset($_REQUEST['paCheck']) and $llOk) {
         $laData = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
      }
      $loSmarty->assign('scBehavior', '1'); 
      $loSmarty->assign('scCodTab', $_SESSION['pcCodTab']); 
      $loSmarty->assign('scDescri', $_SESSION['pcDescri']); 
      $loSmarty->assign('saData',   $laData);
      $loSmarty->display('Plantillas/ERP1080.tpl');
   } elseif (@$_POST['Boton1'] == 'Editar') {
      if (!isset($_REQUEST['paCheck'])) {
         $loSmarty->assign('saCodTab', $_SESSION['paCodTab']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/ERP1080.tpl');
      } else {
         $lcCodTab = $_REQUEST['paCheck'][0];
         $lo = new CMtoTablas();
         $lo->pcCodTab = $lcCodTab;
         $llOk = $lo->omEditarTabla();
         if (!$llOk) { // Error
            $loSmarty->assign('saCodTab', $_SESSION['paCodTab']);
            $loSmarty->assign('scBehavior', '0');
            $loSmarty->display('Plantillas/ERP1080.tpl');
            fxAlert(fxXmlError($lo->pcError));
         } else {
            $_SESSION['paDataB'] = $lo->paData;
            $_SESSION['pcCodTab'] = $lo->pcCodTab; 
            $_SESSION['pcDescri'] = $lo->pcDescri; 
            $loSmarty->assign('scBehavior', '1'); 
            $loSmarty->assign('scCodTab', $lo->pcCodTab); 
            $loSmarty->assign('scDescri', $lo->pcDescri); 
            $loSmarty->assign('saData', $lo->paData);
            $loSmarty->display('Plantillas/ERP1080.tpl');
         }
      }
   } elseif (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } elseif (@$_POST['Grabar'] == 'Grabar') {
      $_SESSION['paData']= $_REQUEST['paData'];
      $lo = new CMtoTablas();
      $lo->pcCodTab = $_SESSION['pcCodTab'];
      $lo->paData   = $_REQUEST['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarTabla();
      if (!$llOk) { // Error
         $loSmarty->assign('scBehavior', '1'); 
         $loSmarty->assign('scCodTab', $_SESSION['pcCodTab']); 
         $loSmarty->assign('scDescri', $_SESSION['pcDescri']); 
         $loSmarty->assign('saData',   $_SESSION['paData']);
         $loSmarty->display('Plantillas/ERP1080.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('saCodTab', $_SESSION['paCodTab']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/ERP1080.tpl');
         fxAlert('GRABACION CONFORME');
      }
   } else {
      $lo = new CMtoTablas();
      $llOk = $lo->omInitTablas();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodTab'] = $lo->paCodTab;
       $loSmarty->assign('flag_cancelar', '1');
         $loSmarty->assign('saCodTab', $_SESSION['paCodTab']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/ERP1080.tpl');
      }
   }
?>