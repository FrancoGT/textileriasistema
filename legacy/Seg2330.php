<?php
   // Registro de incidentes ferroviarios
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CConsultasXls.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2330', 'Mnu0000.php?Id=6000'))
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
      $_SESSION['pcTipInc'] = $_REQUEST['pcTipInc'];
      $_SESSION['pcArea']   = $_REQUEST['pcArea'];
      $_SESSION['pcSubDiv'] = $_REQUEST['pcSubDiv'];
      $_SESSION['pdFecIni'] = $_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin'] = $_REQUEST['pdFecFin'];
      $_SESSION['pcLugar']  = $_REQUEST['pcLugar'];
      $_SESSION['pcEstaci'] = $_REQUEST['pcEstaci'];
      $_SESSION['pcEstacf'] = $_REQUEST['pcEstacf'];
      $_SESSION['pcCodFcr'] = $_REQUEST['pcCodFcr'];
      $_SESSION['pnCosto']  = $_REQUEST['pnCosto'];
      $_SESSION['pcHeridos'] = $_REQUEST['pcHeridos'];
      $_SESSION['pcKilome'] = ($_SESSION['pcEstaci'] == '99') ? $_REQUEST['pcKilome'] : $_SESSION['pcEstaci'];
      $_SESSION['pcKilomf'] = ($_SESSION['pcEstacf'] == '99') ? $_REQUEST['pcKilomf'] : $_SESSION['pcEstacf'];
      $lo = new CConsultasXls();
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $lo->pcCodEmp = $_SESSION['pcCodEmp'];
      $lo->pcCodPry = $_SESSION['pcCodPry'];
      $lo->pcTipInc = $_SESSION['pcTipInc'];
      $lo->pcArea   = $_SESSION['pcArea'];
      $lo->pcCodFcr   = $_SESSION['pcCodFcr'];
      $lo->pnCosto   = $_SESSION['pnCosto'];
      $lo->pcSubDiv = $_SESSION['pcSubDiv'];
      $lo->paCodUni = $_REQUEST['paCodUni'];
      $lo->pdFecIni = $_SESSION['pdFecIni'];
      $lo->pdFecFin = $_SESSION['pdFecFin'];
      $lo->pcLugar = $_SESSION['pcLugar'];
      $lo->pcHeridos = $_SESSION['pcHeridos'];
      $lo->pcKilome = $_SESSION['pcKilome'];
      $lo->pcKilomf = $_SESSION['pcKilomf'];
      $llOk = $lo->omIncidentesFerroviarios();
      if (!$llOk) {
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('saData', $lo->paData);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg2330.tpl');
      }
   }
   
   function fxInit() {
      $lo = new CConsultasXls();
      $llOk = $lo->omInitIncidentesFerroviarios();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         fxHeader('Mnu0000.php?Id=6000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paCodUni'] = $lo->paCodUni;
         $_SESSION['paCodEmp'] = $lo->paCodEmp;
         $_SESSION['paCodPry'] = $lo->paCodPry;
         $_SESSION['paTipInc'] = $lo->paTipInc;
         $_SESSION['paArea']   = $lo->paArea;
         $_SESSION['paSubDiv'] = $lo->paSubDiv;
         $_SESSION['paCodFcr'] = $lo->paCodFcr;
         $_SESSION['paLugar']  = $lo->paLugar;
         $_SESSION['paEstaci'] = $lo->paEstaci;
         $_SESSION['paHeridos'] = $lo->paHeridos;
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('scCodPer', '*');
      $loSmarty->assign('saCodUni', $_SESSION['paCodUni']);
      $loSmarty->assign('scCodUn1', '*');
      $loSmarty->assign('scCodUn2', '*');
      $loSmarty->assign('scCodUn3', '*');
      $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
      $loSmarty->assign('scCodEmp', '*');
      $loSmarty->assign('saCodPry', $_SESSION['paCodPry']);
      $loSmarty->assign('scCodPry', '*');
      $loSmarty->assign('saTipInc', $_SESSION['paTipInc']);
      $loSmarty->assign('scTipInc', '*');
      $loSmarty->assign('saArea',   $_SESSION['paArea']);
      $loSmarty->assign('scArea', '*');
      $loSmarty->assign('saSubDiv', $_SESSION['paSubDiv']);
      $loSmarty->assign('scSubDiv', '*');
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
      $loSmarty->assign('saEstaci', $_SESSION['paEstaci']);
      $loSmarty->assign('scEstacf', '99');      
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
      $loSmarty->assign('snCosto', '0');
      $loSmarty->assign('saCodFcr', $_SESSION['paCodFcr']);
      $loSmarty->assign('scCodFcr', '*');
      $loSmarty->assign('saLugar', $_SESSION['paLugar']);
      $loSmarty->assign('scLugar', '*');
      $loSmarty->assign('saHeridos', $_SESSION['paHeridos']);
      $loSmarty->assign('scHeridos', '*');
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2330.tpl');
   }
?>