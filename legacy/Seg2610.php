<?php
   // Registro de incidentes ferroviarios
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CConsultasXls.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2610', 'Mnu0000.php?Id=6000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Mostrar'] == 'Mostrar') {
      fxMostrar();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=6001');
   } else if (@$_POST['Boton1'] == 'Generar') {
      fxGenerar();
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxScreen();
   } else {
      $_SESSION['pdFecFin'] = date('Y-m-d');
      $_SESSION['pdFecIni'] = fxAddDate($_SESSION['pdFecFin'], -30);
      fxInit();
      fxScreen();
   }   

   // Funciones auxiliares
   function fxGenerar() {
      global $loSmarty;
      $_SESSION['pcCodPer'] = $_REQUEST['pcCodPer'];
      $_SESSION['pcCodCur'] = $_REQUEST['pcCodCur'];
      $_SESSION['pcCodNor'] = $_REQUEST['pcCodNor'];
      $_SESSION['pdFecIni'] = $_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin'] = $_REQUEST['pdFecFin'];
      $_SESSION['pcTipCur'] = $_REQUEST['pcTipCur'];
      $lo = new CConsultasXls();
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $lo->pcCodCur = $_SESSION['pcCodCur'];
      $lo->pcCodNor = $_SESSION['pcCodNor'];
      $lo->pdFecIni = $_SESSION['pdFecIni'];
      $lo->pdFecFin = $_SESSION['pdFecFin'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->pcTipCur = $_SESSION['pcTipCur'];
      $llOk = $lo->omGenerarCursos();
      if (!$llOk) {
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('saData', $lo->paData);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg2610.tpl');
      }
   }
   
   function fxInit() {
      $lo = new CConsultasXls();
      $llOk = $lo->omInitCursos();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         fxHeader('Mnu0000.php?Id=6000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paCodNor'] = $lo->paCodNor;
         $_SESSION['paCodCur'] = $lo->paCodCur;
         $_SESSION['paTipCur'] = $lo->paTipCur;
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saCodNor', $_SESSION['paCodNor']);
      $loSmarty->assign('saCodCur', $_SESSION['paCodCur']);
      $loSmarty->assign('saTipCur', $_SESSION['paTipCur']);
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('scCodNor', $_SESSION['pcCodNor']);
      $loSmarty->assign('scCodCur', $_SESSION['pcCodCur']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2610.tpl');
   }
?>