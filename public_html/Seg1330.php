<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoEmpresas.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1330', 'Mnu0000.php?Id=1000'))
      return;
   $laCodEmp = null;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if ((@$_POST['Aplicar'] == 'Aplicar') or (@$_POST['Aceptar'] == 'Aceptar')) {
      $lo = new CMtoEmpresas();
      $lo->pcCodEmp = $_REQUEST['pcCodEmp'];
      $llOk = $lo->omInitMtoAreas();
      if (!$llOk) { // Error
         $loSmarty->assign('scAviso', $lo->pcError);
         $loSmarty->assign('scVolver', 'Seg1330.php');
         $loSmarty->display('Plantillas/SIS1000.tpl');
      } else {
        $_SESSION['pcCodEmp'] = $lo->pcCodEmp;
         $_SESSION['pcDescri'] = $lo->pcDescri;
        unset($_SESSION['paDataB']);
        $_SESSION['paDataB'] = $lo->paData;
         $loSmarty->assign('scBehavior', '1');
       $loSmarty->assign('saData', $lo->paData);
       $loSmarty->assign('saEstado', $_SESSION['paEstado']);
       $loSmarty->assign('scCodEmp', $lo->pcCodEmp);
       $loSmarty->assign('scDescri', $lo->pcDescri);
         $loSmarty->display('Plantillas/Seg1330.tpl');
      }
   } else if (@$_POST['Nuevo'] == 'Nuevo') {
      $laData = null;
      if (isset($_REQUEST['paData'])) {
         $laData = $_REQUEST['paData'];
      }
      $i = count($laData) + 1;
      $laData[$i] = array($i, '', '', '', 1);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scCodEmp', $_SESSION['pcCodEmp']);
      $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('saData', $laData);
      $loSmarty->display('Plantillas/Seg1330.tpl');
   } else if (@$_POST['Grabar'] == 'Grabar') {
      $lo = new CMtoEmpresas();
      $lo->paData = $_REQUEST['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->pcCodEmp = $_SESSION['pcCodEmp'];
      $llOk = $lo->omGrabarAreas();
      if (!$llOk) { // Error
         fxAlert(fxXmlError($lo->pcError));
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scCodEmp', $_SESSION['pcCodEmp']);
         $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
         $loSmarty->assign('saData', $lo->paData);
         $loSmarty->display('Plantillas/Seg1330.tpl');
      } else {
         fxAlert('GRABACIÓN CONFORME');
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
         $loSmarty->display('Plantillas/Seg1330.tpl');
      }
      $loSmarty->assign('scVolver', 'Seg1330.php');
   } else if (@$_POST['Eliminar'] == 'Eliminar') {
     $llOk = fxValDelete($_REQUEST['paCheck'], $_SESSION['paDataB']);
     $laData = $_REQUEST['paData'];
      if (!$llOk) {   
        fxAlert('NO SE PUEDE ELIMINAR FILA ANTIGUA');
     } else if (isset($_REQUEST['paCheck']) and $llOk) {
         $laData = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
     }
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scCodEmp', $_SESSION['pcCodEmp']);
      $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
      $loSmarty->assign('saData', $laData);
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->display('Plantillas/Seg1330.tpl');
   } else {
      $lo = new CMtoEmpresas();
      $llOk = $lo->omInitEmpresas();
      if (!$llOk) { // Error
         $loSmarty->assign('scAviso', $lo->pcError);
         $loSmarty->assign('scVolver', 'Seg1330.php');
         $loSmarty->display('Plantillas/SIS1000.tpl');
      } else {
         unset($_SESSION['paCodEmp']);
         $_SESSION['paCodEmp'] = $lo->paCodEmp;
         unset($_SESSION['paEstado']);
         $_SESSION['paEstado'] = $lo->paEstado;
         $loSmarty->assign('saCodEmp', $lo->paCodEmp);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1330.tpl');
      }
   }
?>