<?php
   // Excel de sanciones
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CConsultasXls.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2640', 'Mnu0000.php?Id=6000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif (@$_POST['Mostrar'] == 'Mostrar') {
      fxMostrar();
   } elseif (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=6001');
   } elseif (@$_POST['Boton1'] == 'Generar') {
      fxGenerar();
   } elseif (@$_POST['Boton2'] == 'Cancelar') {
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
      $_SESSION['pcCodSan'] = $_REQUEST['pcCodSan'];
      $_SESSION['pcCodNor'] = $_REQUEST['pcCodNor'];
      $_SESSION['pdFecIni'] = $_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin'] = $_REQUEST['pdFecFin'];
      $lo = new CConsultasXls();
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $lo->pcCodSan = $_SESSION['pcCodSan'];
      $lo->pcCodNor = $_SESSION['pcCodNor'];
      $lo->pdFecIni = $_SESSION['pdFecIni'];
      $lo->pdFecFin = $_SESSION['pdFecFin'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omSancionesExcel();
      if (!$llOk) {
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('saData', $lo->paData);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg2640.tpl');
      }
   }
   
   function fxInit() {
      $lo = new CConsultasXls();
      $llOk = $lo->omInitSanciones();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         fxHeader('Mnu0000.php?Id=6000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paCodNor'] = $lo->paCodNor;
         $_SESSION['paCodSan'] = $lo->paCodSan;
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saCodSan', $_SESSION['paCodSan']);
      $loSmarty->assign('saCodNor', $_SESSION['paCodNor']);
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2640.tpl');
   }
?>