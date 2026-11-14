<?php
   // Registro de seguimiento de FCR
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2310', 'Mnu0000.php?Id=4100'))
      return;
   if (!empty($_GET['pcNroInc'])) {
      $_SESSION['pcNroInc']=$_GET['pcNroInc'];
      fxInit();
      fxAplicar();
      return;
   }
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Buscar'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2310.php';
      fxHeader('Seg9008.php?Id=Req');
   } else if (@$_POST['Aplicar'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if (@$_POST['Nuevo'] == 'Nuevo') {
      $laTmp = $_REQUEST['paData'];
      $i = fxCount($laTmp) + 1;
      $laTmp[$i] = array($i, '00', '', '000000', '', '', '0');
      $_SESSION['paData'] = $laTmp; 
      fxScreen();
   } else if (@$_POST['Eliminar'] == 'Eliminar') {
      $laTmp = $_REQUEST['paData'];
      if (isset($_REQUEST['paCheck']))
         $laTmp = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
      $_SESSION['paData'] = $laTmp;
      fxScreen();
   } else if (@$_POST['Grabar'] == 'Grabar') {
      fxGrabar();
   } else {      
      fxInit();
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2310.tpl');
   }
   
   function fxInit(){
      $lo = new CIncidentes();
      $llOk = $lo->omInitIncidentes();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=4100', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paAccTom'] = $lo->paAccTom;
         $_SESSION['paCondic'] = $lo->paCondic;
      }
   }
   // Funciones auxiliares
   function fxGrabar() {      
      global $loSmarty;
      $_SESSION['paData']   = $_REQUEST['paData'];
      $_SESSION['pcNroInc'] = $_REQUEST['pcNroInc'];
      $lo = new CIncidentes();
      $lo->paData =   $_SESSION['paData'];
      $lo->pcNroInc = $_SESSION['pcNroInc'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarCausaRaiz();
      if (!$llOk){
          $lcMessage = fxXmlError($lo->pcError);
		  fxScreen();
          fxAlert($lcMessage);
      } else{
           fxHeader('Seg2320.php?pcNroInc='.$_SESSION['pcNroInc']);
           return;
	  }
   }
   
   function fxAplicar() {
      global $loSmarty;
      $lo = new CIncidentes();
      if (@$_GET['Id'] == 'Aceptar' or !empty($_GET['pcNroInc'])) 
         $lo->pcNroInc = $_SESSION['pcNroInc'];
      else  
         $lo->pcNroInc = $_REQUEST['pcNroInc'];
      $llOk = $lo->omAplicar6();
      if (!$llOk) {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2310.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['pmDatos']  = $lo->pmDatos;
         $_SESSION['pcNroInc'] = $lo->pcNroInc;
         $_SESSION['paData']   = $lo->paData;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
      $loSmarty->assign('saData',   $_SESSION['paData']);
      $loSmarty->assign('saAccTom', $_SESSION['paAccTom']);
      $loSmarty->assign('saCondic', $_SESSION['paCondic']);
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg2310.tpl');
   }
?>