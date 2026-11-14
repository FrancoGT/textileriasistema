<?php
   // Excel de licencias
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CConsultasXls.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2630', 'Mnu0000.php?Id=6000'))
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
      $_SESSION['pdVigFin'] = date('Y-m-d');
      $_SESSION['pdVigIni'] = fxAddDate($_SESSION['pdVigFin'], -30);
      $_SESSION['pdVenFin'] = date('Y-m-d');
      $_SESSION['pdVenIni'] = fxAddDate($_SESSION['pdVigFin'], -30);
      fxInit();
      fxScreen();
   }   
   
   // Funciones auxiliares
   function fxGenerar() {
      global $loSmarty;
      $_SESSION['pcCodPer'] = $_REQUEST['pcCodPer'];
      $_SESSION['pcCodEmp'] = $_REQUEST['pcCodEmp'];
      $_SESSION['pcTipLic'] = $_REQUEST['pcTipLic'];
      $_SESSION['pcEstado'] = trim($_REQUEST['pcEstado']);
      $_SESSION['pcRestri'] = $_REQUEST['pcRestri'];
      $_SESSION['pdVigIni'] = $_REQUEST['pdVigIni'];
      $_SESSION['pdVigFin'] = $_REQUEST['pdVigFin'];
      $_SESSION['pdVenIni'] = $_REQUEST['pdVenIni'];
      $_SESSION['pdVenFin'] = $_REQUEST['pdVenFin'];
      $_SESSION['pcSector'] = $_REQUEST['pcSector'];
      $_SESSION['pcSecAut'] = $_REQUEST['pcSecAut'];
      $lo = new CConsultasXls();
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $lo->pcCodEmp = $_SESSION['pcCodEmp'];
      $lo->pcTipLic = $_SESSION['pcTipLic'];
      $lo->pcEstado = $_SESSION['pcEstado'];
      $lo->pcRestri = $_SESSION['pcRestri'];
      $lo->pdVigIni = $_SESSION['pdVigIni'];
      $lo->pdVigFin = $_SESSION['pdVigFin'];
      $lo->pdVenIni = $_SESSION['pdVenIni'];
      $lo->pdVenFin = $_SESSION['pdVenFin'];
      $lo->pcSector = $_SESSION['pcSector'];
      $lo->pcSecAut = $_SESSION['pcSecAut'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omLicenciasExcel();
      if (!$llOk) {
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('saData', $lo->paData);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg2630.tpl');
      }
   }
   
   function fxInit() {
      $lo = new CConsultasXls();
      $llOk = $lo->omInitLicencias();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         fxHeader('Mnu0000.php?Id=6000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paSector'] = $lo->paSector;
         $_SESSION['paCodEmp'] = $lo->paCodEmp;
         $_SESSION['paTipLic'] = $lo->paTipLic;
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paRestri'] = $lo->paRestri;
         $_SESSION['paSecAut'] = $lo->paSecAut;
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saSector', $_SESSION['paSector']);
      $loSmarty->assign('saSecAut', $_SESSION['paSecAut']);
      $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
      $loSmarty->assign('saTipLic', $_SESSION['paTipLic']);
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('saRestri', $_SESSION['paRestri']);
      $loSmarty->assign('sdVigIni', $_SESSION['pdVigIni']);
      $loSmarty->assign('sdVigFin', $_SESSION['pdVigFin']);
      $loSmarty->assign('sdVenIni', $_SESSION['pdVenIni']);
      $loSmarty->assign('sdVenFin', $_SESSION['pdVenFin']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2630.tpl');
   }
?>