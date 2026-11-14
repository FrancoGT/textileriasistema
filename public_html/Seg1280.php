<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoCursos.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1280', 'Mnu0000.php?Id=1000'))
      return;
   $laCodPer = null;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if ((@$_POST['Aplicar'] == 'Aplicar') or (@$_POST['Aceptar'] == 'Aceptar')) {
      $lo = new CMtoCursos();
      $lo->pcCodCur = $_REQUEST['pcCodCur'];
      $llOk = $lo->omMtoCursosNotas();
      if (!$llOk) { // Error
         $loSmarty->assign('saCodCur', $_SESSION['paCodCur']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1280.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['pcCodCur'] = $lo->pcCodCur;
         $_SESSION['pcDescri'] = $lo->pcDescri;
         $_SESSION['pdFecha'] = $lo->pdFecha;
         $loSmarty->assign('scBehavior', '1'); 
         $loSmarty->assign('scCodCur', $lo->pcCodCur);
         $loSmarty->assign('scDescri', $lo->pcDescri);
         $loSmarty->assign('sdFecha', $lo->pdFecha);
         $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
         $loSmarty->assign('saData', $lo->paData);
         $loSmarty->display('Plantillas/Seg1280.tpl');
      }
   } else if (@$_POST['Nuevo'] == 'Nuevo') {
      $laData = null;
      if (isset($_REQUEST['paData'])) {
         $laData = $_REQUEST['paData'];
      }
      $j = count($laData);
      $laData[] = array($j + 1, '000000','');
      $laCodPer = $_SESSION['paCodPer'];
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scCodCur', $_SESSION['pcCodCur']);
      $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
      $loSmarty->assign('sdFecha', $_SESSION['pdFecha']);
      $loSmarty->assign('saCodPer', $laCodPer);
      $loSmarty->assign('saData', $laData);
      $loSmarty->display('Plantillas/Seg1280.tpl');
   } else if (@$_POST['Eliminar'] == 'Eliminar') {
      $laData = $_REQUEST['paData'];
      if (isset($_REQUEST['paCheck']))
         $laData = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
      $laCodPer = $_SESSION['paCodPer'];
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scCodCur', $_SESSION['pcCodCur']);
      $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
      $loSmarty->assign('sdFecha', $_SESSION['pdFecha']);
      $loSmarty->assign('saData', $laData);
      $loSmarty->assign('saCodPer', $laCodPer);
      $loSmarty->display('Plantillas/Seg1280.tpl');
   } else if (@$_POST['Cancelar'] == 'Cancelar') {
      $loSmarty->assign('saCodCur', $_SESSION['paCodCur']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg1280.tpl');
   } else if (@$_POST['Grabar'] == 'Grabar') {
      $lo = new CMtoCursos();
      $lo->paData = $_REQUEST['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->pcCodCur = $_SESSION['pcCodCur'];
      $llOk = $lo->omGrabarCursosNotas();
      if (!$llOk) { // Error
         $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
         $loSmarty->assign('scCodCur', $_SESSION['pcCodCur']);
         $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
         $loSmarty->assign('sdFecha', $_SESSION['pdFecha']);
         $loSmarty->assign('saData', $lo->paData);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg1280.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('saCodCur', $_SESSION['paCodCur']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1280.tpl');
         fxAlert('GRABACION CONFORME');
      }
   } else {
      $lo = new CMtoCursos();
      $llOk = $lo->initDetalleCursos();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodCur'] = $lo->paCodCur;
         $loSmarty->assign('saCodCur', $_SESSION['paCodCur']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1280.tpl');
      }
   }
?>