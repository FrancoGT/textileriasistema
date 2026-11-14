<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoCursos.php';
   session_start();
   $loSmarty = new Smarty;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if ((@$_POST['Boton2'] == 'Aceptar')) {
      $lcCodCur = $_REQUEST['pcCodCur'];
      if (empty($lcCodCur)) {
         $loSmarty->assign('scBehavior', '1'); 
         $loSmarty->assign('saCodCur', $_SESSION['paCodCur']);
         $loSmarty->display('Plantillas/Seg9003.tpl');
      } else {
         $_SESSION['pcCodCur'] = $lcCodCur;
         fxHeader($_SESSION['pcVolver'].'?Id=Aceptar');
      }
   } else if ((@$_POST['Boton2'] == 'Cancelar')) {
      fxScreen();
   } else if ((@$_POST['Boton1'] == 'Salir')) {
      fxHeader($_SESSION['pcVolver']);
   } else if ((@$_POST['Boton1'] == 'Aplicar')) {
      $lo = new CMtoCursos();
      $lo->pdFecIni = $_REQUEST['pdFecIni'];
      $lo->pdFecFin = $_REQUEST['pdFecFin'];
	  $lo->pcTipo = $_REQUEST['pcTipo'];
      $_SESSION['pdFecIni'] = $_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin'] = $_REQUEST['pdFecFin'];
      $llOk = $lo->omBuscarCursos();
      if (!$llOk) { // Error
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodCur'] = $lo->paCodCur;
         $loSmarty->assign('scBehavior', '1'); 
         $loSmarty->assign('saCodCur', $_SESSION['paCodCur']);
         $loSmarty->display('Plantillas/Seg9003.tpl');
      }
   } else {
      fxInit();
   }
   
   function fxInit() {
      $lo = new CMtoCursos();
      $llOk = $lo->omCargarMtoCursos();
      if (!$llOk) {
	     fxHeader($_SESSION['pcVolver'], fxXmlError($lo->pcError));
      } else {
         $_SESSION['paTipo'] = $lo->paTipo;
      }
	  $_SESSION['pdFecFin'] = date('Y-m-d');
      $_SESSION['pdFecIni'] = fxAddDate($_SESSION['pdFecFin'], -30);
      fxScreen();
   }
   
   function fxScreen() {
      global $loSmarty;
	  $loSmarty->assign('saTipo', $_SESSION['paTipo']);
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg9003.tpl');
   }
?>
