<?php
   // Reporte Incidentes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.rep.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2540', 'Mnu0000.php?Id=4001'))
      return;
   if (!empty($_GET['pcNroInc'])) {
      $_SESSION['pcNroInc']=$_GET['pcNroInc'];
      fxAplicar();
      return;
   }
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2540.php';
      fxHeader('Seg9008.php');
      fxReporte();   // OJOEMM
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=4001');
   } else if (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if (@$_POST['Boton2'] == 'Reporte') {
      fxReporte();
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxInit();
   } else if (@$_POST['Boton2'] == 'Siguiente') {
      fxHeader('Seg2230.php?pcNroInc='.$_SESSION['pcNroInc']);
      return;
   } else if (@$_POST['Boton3'] == 'Cancelar') {
      fxInit();
   } else {
      fxInit();
   }

   function fxAplicar() {
      global $loSmarty;      
      $lo = new CIncidentesRep();
      if (@$_GET['Id'] == 'Aceptar') 
         $lo->pcNroInc = $_SESSION['pcNroInc'];
      else  
         $lo->pcNroInc = $_REQUEST['pcNroInc'];
      $llOk = $lo->omAplicar();
      if (!$llOk) {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2540.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['pmDescri'] = $lo->pmDescri;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('smDescri', $_SESSION['pmDescri']);
         $loSmarty->assign('scNroInc', $lo->pcNroInc);
         $loSmarty->display('Plantillas/Seg2540.tpl');
      }
   }
   
   function fxReporte() {
      global $loSmarty;
      $lo = new CIncidentesRep();
      $lo->pcNroInc = $_REQUEST['pcNroInc'];
      //$lo->pcNroInc = '000012';
      $llOk = $lo->omInformeInvestigacion1();
	  if (!$llOk) {
         fxInit();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         //fxInit();
		 $loSmarty->assign('scFilRet', $lo->pcFilRet);
         $loSmarty->assign('scBehavior', '2');
         $loSmarty->display('Plantillas/Seg2540.tpl');
      }
   }

   function fxInit() {
      global $loSmarty;
	  $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2540.tpl');
   }
?>