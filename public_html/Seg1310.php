<?php
   // Mantenimiento de licencias
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoLicencias.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1310', 'Mnu0000.php?Id=1000'))
      return;
   $laCodCur = null;
   if (!fxInitSession()) {
       fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Nuevo'] == 'Nuevo') {
      $laData = null;
      if (isset($_REQUEST['paData'])) {
         $laData = $_REQUEST['paData'];
      }
      $j = count($laData);
      $laData[] = array($j + 1, '', '', 'A');
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('saData', $laData);
      $loSmarty->display('Plantillas/Seg1310.tpl');
   } else if (@$_POST['Grabar'] == 'Grabar') {
      $lo = new CMtoLicencias();
      $lo->paData = $_REQUEST['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarTipoLicencia();
      if (!$llOk) { // Error
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->assign('saData', $_REQUEST['paData']);
         $loSmarty->display('Plantillas/Seg1310.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('scAviso', 'GRABACIÓN CONFORME');
         $loSmarty->assign('scVolver', 'Mnu0000.php');
         $loSmarty->display('Plantillas/SIS1000.tpl');
      }
   } else if (@$_POST['Eliminar'] == 'Eliminar') {
      // No procede eliminar si es antiguo
      $llOk = true;
      if (isset($_REQUEST['paCheck'])) {
         foreach($_REQUEST['paCheck'] as $i) {
            foreach($_SESSION['paDataB'] as $j) {
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
        fxAlert('NO SE PUEDE ELIMINAR TIPO DE LICENCIA ANTIGUO');
     } else {
         if (isset($_REQUEST['paCheck']) and $llOk)
            $laData = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
     }
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('saData', $laData);
      $loSmarty->display('Plantillas/Seg1310.tpl');
   } else {
      $lo = new CMtoLicencias();
      $llOk = $lo->omInitMtoLicencias();
      if (!$llOk) { // Error
         $loSmarty->assign('scAviso', $lo->pcError);
         $loSmarty->assign('scVolver', 'Seg1310.php');
         $loSmarty->display('Plantillas/SIS1000.tpl');
      } else {
        unset($_SESSION['paEstado']);
        $_SESSION['paEstado'] = $lo->paEstado;
        unset($_SESSION['paDataB']);
        $_SESSION['paDataB'] = $lo->paData;
         $loSmarty->assign('saData', $lo->paData);
       $loSmarty->assign('saEstado', $lo->paEstado);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1310.tpl');
      }
   }
?>