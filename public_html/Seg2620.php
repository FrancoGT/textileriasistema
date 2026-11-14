<?php
   // Excel de evaluaciones
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CConsultasXls.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2620', 'Mnu0000.php?Id=6000'))
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
   
   function fxGenerar() {
      global $loSmarty;
      $_SESSION['pcCodPer'] = $_REQUEST['pcCodPer'];
      $_SESSION['pcCodEmp'] = $_REQUEST['pcCodEmp'];
      $_SESSION['pcCodPry'] = $_REQUEST['pcCodPry'];
      $_SESSION['pcTipEva'] = trim($_REQUEST['pcTipEva']);
      $_SESSION['pcSubDiv'] = $_REQUEST['pcSubDiv'];
      $_SESSION['pdFecIni'] = $_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin'] = $_REQUEST['pdFecFin'];
      $_SESSION['pcTipVag'] = $_REQUEST['pcTipVag'];
      $_SESSION['pcEstaci'] = $_REQUEST['pcEstaci'];
      $_SESSION['pcEstacf'] = $_REQUEST['pcEstacf'];
      $_SESSION['pcSuperv'] = $_REQUEST['pcSuperv'];
      $_SESSION['pnCosto']  = $_REQUEST['pnCosto'];
      $_SESSION['pcCodNor'] = $_REQUEST['pcCodNor'];
      $_SESSION['pcKilome'] = ($_SESSION['pcEstaci'] == '99') ? $_REQUEST['pcKilome'] : $_SESSION['pcEstaci'];
      $_SESSION['pcKilomf'] = ($_SESSION['pcEstacf'] == '99') ? $_REQUEST['pcKilomf'] : $_SESSION['pcEstacf'];
      $lo = new CConsultasXls();
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $lo->pcCodEmp = $_SESSION['pcCodEmp'];
      $lo->pcCodPry = $_SESSION['pcCodPry'];
      $lo->pcTipEva = $_SESSION['pcTipEva'];
      $lo->pcSuperv = $_SESSION['pcSuperv'];
      $lo->pcSubDiv = $_SESSION['pcSubDiv'];
      $lo->paCodUni = $_REQUEST['paCodUni'];
      $lo->pdFecIni = $_SESSION['pdFecIni'];
      $lo->pdFecFin = $_SESSION['pdFecFin'];
      $lo->pcTipVag = $_SESSION['pcTipVag'];
      $lo->pcCodNor = $_SESSION['pcCodNor'];
      $lo->pcKilome = $_SESSION['pcKilome'];
      $lo->pcKilomf = $_SESSION['pcKilomf'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omEvaluacionesExcel();
      if (!$llOk) {
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('saData', $lo->paData);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg2620.tpl');
      }
   }
   
   function fxInit() {
      $lo = new CConsultasXls();
      $llOk = $lo->omInitEvaluaciones();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         fxHeader('Mnu0000.php?Id=6000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paCodUni'] = $lo->paCodUni;
         $_SESSION['paCodEmp'] = $lo->paCodEmp;
         $_SESSION['paCodPry'] = $lo->paCodPry;
         $_SESSION['paTipEva'] = $lo->paTipEva;
         $_SESSION['paSubDiv'] = $lo->paSubDiv;
         $_SESSION['paSuperv'] = $lo->paSuperv;
         $_SESSION['paTipVag'] = $lo->paTipVag;
         $_SESSION['paEstaci'] = $lo->paEstaci;
         $_SESSION['paCodNor'] = $lo->paCodNor;
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saCodUni', $_SESSION['paCodUni']);
      $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
      $loSmarty->assign('saCodPry', $_SESSION['paCodPry']);
      $loSmarty->assign('saTipEva', $_SESSION['paTipEva']);
      $loSmarty->assign('saSubDiv', $_SESSION['paSubDiv']);
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
      $loSmarty->assign('saEstaci', $_SESSION['paEstaci']);
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
      $loSmarty->assign('saSuperv', $_SESSION['paSuperv']);
      $loSmarty->assign('scSuper2', '*');
      $loSmarty->assign('saTipVag', $_SESSION['paTipVag']);
      $loSmarty->assign('saCodNor', $_SESSION['paCodNor']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2620.tpl');
   }
?>