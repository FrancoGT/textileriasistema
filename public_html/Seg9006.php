<?php
   // Consulta de evaluaciones
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CEvaluacion.php';
   session_start();
   $loSmarty = new Smarty;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESION');
   } else if ((@$_POST['Boton2'] == 'Aceptar')) {
      $lcNroEva = $_REQUEST['pcNroEva'];
      if (empty($lcNroEva)) {
         $loSmarty->assign('saData', $_SESSION['paData_1']);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg9006.tpl');
         fxAlert('NO HA ELEGIDO NUMERO DE EVALUACION');
      } else {
         $_SESSION['pcNroEva'] = $lcNroEva;
         fxHeader($_SESSION['pcVolver'].'?Id=Aceptar');
      }
   } else if ((@$_POST['Boton2'] == 'Cancelar')) {
      fxScreen();   
   } else if ((@$_POST['Boton1'] == 'Aplicar')) {
      $_SESSION['pdFecIni_1'] = $_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin_1'] = $_REQUEST['pdFecFin'];
      $lo = new CEvaluacion();
      $lo->pcEvaTip = $_SESSION['pcEvaTip'];
      $lo->pdFecIni = $_SESSION['pdFecIni_1'];
      $lo->pdFecFin = $_SESSION['pdFecFin_1'];
      $llOk = $lo->omBuscarEvaluacion();
      if (!$llOk) {
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['paData_1'] = $lo->paData;
         $loSmarty->assign('saData', $_SESSION['paData_1']);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg9006.tpl');
      }
   } else if ((@$_POST['Boton1'] == 'Salir')) {
      fxHeader($_SESSION['pcVolver']);
   } else {
      $_SESSION['pdFecFin_1'] = date('Y-m-d');
      $_SESSION['pdFecIni_1'] = fxAddDate($_SESSION['pdFecFin_1'], -30);
      fxScreen();
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni_1']);
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin_1']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg9006.tpl');
   }
?>