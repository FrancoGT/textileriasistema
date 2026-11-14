<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoEmpleados.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1270', 'Mnu0000.php?Id=1000'))
      return;
   $laCodPer = null;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if ((@$_POST['Aplicar'] == 'Aplicar') or (@$_POST['Aceptar'] == 'Aceptar')) {
      $lo = new CMtoEmpleados();
      $lo->pcCodPer = $_REQUEST['pcCodPer'];
      $llOk = $lo->omMtoPuentePersonal();
      if (!$llOk) { // Error
         $loSmarty->assign('scAviso', $lo->pcError);
         $loSmarty->assign('scVolver', 'Seg1270.php');
         $loSmarty->display('Plantillas/SIS1000.tpl');
      } else {
         unset($_SESSION['paCodEmp']);
         foreach($lo->paCodEmp as $i) {
            $_SESSION['paCodEmp'][] = array($i[0], $i[1]);
         }
         $_SESSION['pcCodPer'] = $lo -> pcCodPer;
         $_SESSION['pcNombre'] = $lo -> pcNombre;
         $loSmarty->assign('scBehavior', '1'); 
         $loSmarty->assign('scCodPer', $lo->pcCodPer);
         $loSmarty->assign('scNombre', $lo->pcNombre);
         $loSmarty->assign('saCodEmp', $lo->paCodEmp);
         $loSmarty->assign('saData', $lo->paData);
         $loSmarty->display('Plantillas/Seg1270.tpl');
      }
   } else if (@$_POST['Nuevo'] == 'Nuevo') {
      $laData = null;
      if (isset($_REQUEST['paData'])) {
         $laData = $_REQUEST['paData'];
      }
      $j = fxCount($laData);
      $laData[] = array($j + 1, '000', '','','');
      $laCodEmp = null;
      foreach($_SESSION['paCodEmp'] as $i) {
            @$laCodEmp[] = array($i[0], $i[1]);
        }
      $loSmarty->assign('scBehavior', '1');      
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
      $loSmarty->assign('saCodEmp', $laCodEmp);
      $loSmarty->assign('saData', $laData);
      $loSmarty->display('Plantillas/Seg1270.tpl');
   } else if (@$_POST['Grabar'] == 'Grabar') {
      $lo = new CMtoEmpleados();
      $laCodPer= $lo->pcCodPer;
      $lo->paData = $_REQUEST['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $llOk = $lo->omGrabarPuentePersonal();
      if (!$llOk) { // Error
         fxAlert(fxXmlError($lo->pcError));
         $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
         $loSmarty->assign('scBehavior', '1');   
         $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
         $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
         $loSmarty->assign('saData', $lo->paData);
         $loSmarty->display('Plantillas/Seg1270.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $laCodPer = $_SESSION['paCodPer'];
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->assign('saCodPer', $laCodPer);
         $loSmarty->display('Plantillas/Seg1270.tpl');
         fxAlert(fxXmlError('GRABACION CONFORME'));
      }
      $loSmarty->assign('scVolver', 'Seg1270.php');
   } else if (@$_POST['Eliminar'] == 'Eliminar') {
      $laData = $_REQUEST['paData'];
      if (isset($_REQUEST['paCheck']))
         $laData = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
      $loSmarty->assign('scBehavior', '1'); 
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
      $loSmarty->assign('saData', $laData);
      $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
      $loSmarty->display('Plantillas/Seg1270.tpl');
   } else {
      $lo= new CMtoEmpleados();
      $llOk = $lo->initDetallePersonal();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
         return;
      }
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg1270.tpl');
   }
?>