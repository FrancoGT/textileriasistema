<?php
   // Registro de Incidentes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2260', 'Mnu0000.php?Id=4002'))
      return;
   if (!empty($_GET['pcNroInc'])) {
      $_SESSION['pcNroInc']=$_GET['pcNroInc'];
	  fxInit();
      fxAplicar();
      return;
   }
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2260.php';
      fxHeader('Seg9008.php?Id=Req');
   } else if (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=4002');
   }  else if (@$_POST['Grabar'] == 'Grabar') {
      fxGrabarInvestigacion();
   } else {
      fxInit();
	  fxScreenI();
   }
   function fxInit(){
      $lo = new CIncidentes();
      $llOk = $lo->omInitIncidentes();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=4002', fxXmlError($lo->pcError));
      }	  
   }
   function fxScreenI(){
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2260.tpl');
   }
   // Funciones auxiliares
   function fxDumpArray() {
      global $loSmarty;
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saAccTom', $_SESSION['paAccTom']);
      $loSmarty->assign('saCondic', $_SESSION['paCondic']);
   }

   function fxAplicar(){
      global $loSmarty;
      $lo = new CIncidentes();
      if (@$_GET['Id'] == 'Aceptar' or !empty($_GET['pcNroInc'])) 
      $lo->pcNroInc = $_SESSION['pcNroInc'];
      else  
      $lo->pcNroInc = $_REQUEST['pcNroInc'];
      $llOk = $lo->omAplicar4();
      if (!$llOk) {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2260.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['pcNroInc'] = $lo->pcNroInc;
         $_SESSION['pmDatos']  = $lo->pmDatos;
         $_SESSION['pcDamnif'] = $lo->pcDamnif;
         $_SESSION['pcDamPro1'] = $lo->pcDamPro1;
         $_SESSION['pcDamPro2'] = $lo->pcDamPro2;
         $_SESSION['pcDamPro3'] = $lo->pcDamPro3;
         $_SESSION['pcDamPro4'] = $lo->pcDamPro4;
         $_SESSION['pmDamPro'] = $lo->pmDamPro;
         $_SESSION['pcLesPer'] = $lo->pcLesPer;
         $_SESSION['pmLesPer'] = $lo->pmLesPer;
         $_SESSION['pcPerPro'] = $lo->pcPerPro;
         $_SESSION['pcTiePer'] = $lo->pcTiePer;
         $_SESSION['pcDamAmb'] = $lo->pcDamAmb;
         $_SESSION['pmDamAmb'] = $lo->pmDamAmb;
         $_SESSION['pnCosto'] = $lo->pnCosto;
         $loSmarty->assign('scBehavior', '1');
         fxAssign();
         fxDumpArray();
         $loSmarty->display('Plantillas/Seg2260.tpl');
      }
   }
   
   function fxGrabarInvestigacion() {
      global $loSmarty;
      $_SESSION['pcDamnif'] = isset($_REQUEST['pcDamnif']) ? 'S': 'N';
      $lcDamPro = isset($_REQUEST['pcDamPro1']) ? 'S': 'N';
      $lcDamPro = $lcDamPro.(isset($_REQUEST['pcDamPro2']) ? 'S': 'N');
      $lcDamPro = $lcDamPro.(isset($_REQUEST['pcDamPro3']) ? 'S': 'N');
      $_SESSION['pcDamPro'] = $lcDamPro.(isset($_REQUEST['pcDamPro4']) ? 'S': 'N');
      $_SESSION['pmDamPro'] = $_REQUEST['pmDamPro'];      
      $_SESSION['pcLesPer'] = isset($_REQUEST['pcLesPer']) ? 'S': 'N';
      $_SESSION['pmLesPer'] = $_REQUEST['pmLesPer'];
      $_SESSION['pcPerPro'] = isset($_REQUEST['pcPerPro']) ? 'S': 'N';
      $_SESSION['pcTiePer'] = $_REQUEST['pcTiePer'];
      $lcDamAmb = (isset($_REQUEST['pcDamAmb']) ? 'S': 'N');
      $lcDamAmb = $lcDamAmb.(isset($_REQUEST['pcDamAmb1']) ? 'S': 'N');
      $lcDamAmb = $lcDamAmb.(isset($_REQUEST['pcDamAmb2']) ? 'S': 'N');
      $_SESSION['pcDamAmb'] = $lcDamAmb.(isset($_REQUEST['pcDamAmb3']) ? 'S': 'N');
      $_SESSION['pmDamAmb'] = $_REQUEST['pmDamAmb'];
	  $_SESSION['pnCosto'] = $_REQUEST['pnCosto'];
      $lo = new CIncidentes();
      $lo->pcNroInc = $_SESSION['pcNroInc'];
      $lo->pcDamnif = $_SESSION['pcDamnif'];
      $lo->pcDamPro = $_SESSION['pcDamPro'];
      $lo->pmDamPro = $_SESSION['pmDamPro'];
      $lo->pcLesPer = $_SESSION['pcLesPer'];
      $lo->pmLesPer = $_SESSION['pmLesPer'];
      $lo->pcPerPro = $_SESSION['pcPerPro'];
      $lo->pcTiePer = $_SESSION['pcTiePer'];
      $lo->pcDamAmb = $_SESSION['pcDamAmb'];
      //$lo->pcDamAmb0 = $_SESSION['pcDamAmb0'];
      $lo->pmDamAmb = $_SESSION['pmDamAmb'];
      $lo->pnCosto  = $_SESSION['pnCosto'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarInvestigacion();
      if (!$llOk) { // Error  959950446 
         fxAlert(fxXmlError($lo->pcError));
         fxAssign();
         fxDumpArray();
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg2260.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         fxHeader('Seg2280.php?pcNroInc='.$lo->pcNroInc);
         return;
       }
   }

   function fxAssign() {
      global $loSmarty;
      $loSmarty->assign('scNroInc',  $_SESSION['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('scDamnif', $_SESSION['pcDamnif']);
      $loSmarty->assign('scDamPro1', substr($_SESSION['pcDamPro'], 0, 1));
      $loSmarty->assign('scDamPro2', substr($_SESSION['pcDamPro'], 1, 1));
      $loSmarty->assign('scDamPro3', substr($_SESSION['pcDamPro'], 2, 1));
      $loSmarty->assign('scDamPro4', substr($_SESSION['pcDamPro'], 3, 1));
      $loSmarty->assign('smDamPro', $_SESSION['pmDamPro']);
      $loSmarty->assign('scLesPer', $_SESSION['pcLesPer']);
      $loSmarty->assign('smLesPer', $_SESSION['pmLesPer']);
      $loSmarty->assign('scPerPro', $_SESSION['pcPerPro']);
      $loSmarty->assign('scTiePer', $_SESSION['pcTiePer']);
     $loSmarty->assign('scDamAmb', substr($_SESSION['pcDamAmb'], 0, 1));
      $loSmarty->assign('scDamAmb1', substr($_SESSION['pcDamAmb'], 1, 1));
      $loSmarty->assign('scDamAmb2', substr($_SESSION['pcDamAmb'], 2, 1));
      $loSmarty->assign('scDamAmb3', substr($_SESSION['pcDamAmb'], 3, 1));
      $loSmarty->assign('smDamAmb', $_SESSION['pmDamAmb']);
      $loSmarty->assign('snCosto', $_SESSION['pnCosto']);
   }
?>