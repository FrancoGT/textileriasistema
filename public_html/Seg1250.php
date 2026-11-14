<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoCursos.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1250', 'Mnu0000.php?Id=1000'))
      return;
   $laCodCur = null;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if ((@$_POST['Aplicar'] == 'Aplicar') or (@$_POST['Aceptar'] == 'Aceptar')) {
      $lo = new CMtoCursos();
      $lo->pcCodCur = $_REQUEST['pcCodCur'];
      $llOk = $lo->omMtoPuenteCursos();
      if (!$llOk) { // Error
         $loSmarty->assign('scAviso', $lo->pcError);
         $loSmarty->assign('scVolver', 'Seg1250.php');
         $loSmarty->display('Plantillas/SIS1000.tpl');
      } else {
         unset($_SESSION['paCodNor']);
         foreach($lo->paCodNor as $i) {
            $_SESSION['paCodNor'][] = array($i[0], $i[1]);
         }
         $_SESSION['pcCodCur'] = $lo->pcCodCur;
         $_SESSION['pcDescri'] = $lo->pcDescri;
         $loSmarty->assign('scBehavior', '1'); 
         $loSmarty->assign('scCodCur', $lo->pcCodCur);
         $loSmarty->assign('scDescri', $lo->pcDescri);
         $loSmarty->assign('saCodNor', $lo->paCodNor);
         $loSmarty->assign('sIndex', $lo->paIndex);
         $loSmarty->display('Plantillas/Seg1250.tpl');
      }
   } else if (@$_POST['Nuevo'] == 'Nuevo') {
      $laIndex = null;
      $j = 0;
      if (isset($_REQUEST['paIndex'])) {
         foreach($_REQUEST['paIndex'] as $i) {
            $laIndex[] = array($i, $_REQUEST['paCodNor'][$j], $_REQUEST['paDescri'][$j]);
            $j++;
         }
      }
      $laIndex[] = array($j + 1, '000', '');
      // Recupera codigo de normas   OJOFPM
      $laCodNor = $_SESSION['paCodNor'];
      $loSmarty->assign('scBehavior', '1');      
      $loSmarty->assign('scCodCur', $_SESSION['pcCodCur']);
      $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
      $loSmarty->assign('saCodNor', $laCodNor);
      $loSmarty->assign('saIndex', $laIndex);
      $loSmarty->display('Plantillas/Seg1250.tpl');
   } else if (@$_POST['Grabar'] == 'Grabar') {
      $lo = new CMtoCursos();
      $laCodCur= $lo->pcCodCur;
      $lo->paCurNor = null;
      $j = 0; 
      foreach($_REQUEST['paCodNor'] as $i) {
         $lcDescri = $_REQUEST['paDescri'][$j];
         $lo->paCurNor[] = array($i, $lcDescri);
         $j++;
      }
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->pcCodCur = $_SESSION['pcCodCur'];
      $llOk = $lo->omGrabarPuenteCursos();
      if (!$llOk) { // Error
         fxAlert(fxXmlError($lo->pcError));
         $laCurNor = null;
         $j = 0;
         foreach($lo->paCurNor as $i) {
            $laCurNor[] = array($j,$i[0], $i[1]);
            $j++;
         }
         $laCodNor = null;
         foreach($_SESSION['paCodNor'] as $i) {
            @$laCodNor[] = array($i[0], $i[1]);
         }
         $loSmarty->assign('saCodNor', $laCodNor);
         $loSmarty->assign('scBehavior', '1');      
         $loSmarty->assign('scCodCur', $_SESSION['pcCodCur']);
         $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
         $loSmarty->assign('saIndex', $laCurNor);
         $loSmarty->display('Plantillas/Seg1250.tpl');
      } else {
         echo '<script>alert(\'GRABACION CONFORME.\');</script>';
         $laCodCur = null;
         foreach($_SESSION['paCodCur'] as $i) {
            @$laCodCur[] = array($i[0], $i[1]);
         }
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->assign('saCodCur', $laCodCur);
         $loSmarty->display('Plantillas/Seg1250.tpl');
      }
      $loSmarty->assign('scVolver', 'Seg1250.php');
   } else if (@$_POST['Eliminar'] == 'Eliminar') {
      $laCodNor = $_SESSION['paCodNor'];
      $k = 0;
      $lnIndex = 0;
      $laCurNor = null;
      foreach($_REQUEST['paIndex'] as $i) {
         $llOk = true;
         foreach($_REQUEST['paCheck'] as $j) {
            if ($i == $j) {
               $llOk = false;
               break;
            }
         }
         if ($llOk) {
            $lnIndex++;
            $laCurNor[] = array($lnIndex, $_REQUEST['paCodNor'][$k], $_REQUEST['paDescri'][$k]);
         }
         $k++;
      }
      $loSmarty->assign('scBehavior', '1'); 
      $loSmarty->assign('scCodCur', $_SESSION['pcCodCur']);
      $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
      $loSmarty->assign('saIndex', $laCurNor);
      $loSmarty->assign('saCodNor', $laCodNor);
      $loSmarty->display('Plantillas/Seg1250.tpl');
   } else {
      $lo= new CMtoCursos();
      $llOk = $lo->initDetalleCursos();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else {
         unset($_SESSION['paCodCur']);
         $_SESSION['paCodCur'] = $lo->paCodCur;
         $loSmarty->assign('saCodCur',$lo->paCodCur);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1250.tpl');
      }
   }
?>