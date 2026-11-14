<?php
   // Reporte de Evaluaciones
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CPruebas.rep.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2530', 'Mnu0000.php?Id=6002'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Buscar') {
      fxBuscar();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=6002');
   } else if (@$_POST['Boton1'] == 'Generar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if (@$_POST['Boton2'] == 'Reporte') {
      fxReporte();
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxInit();
   } else {
      $_SESSION['pdFecFin'] = date('Y-m-d');
      $_SESSION['pdFecIni'] = fxAddDate($_SESSION['pdFecFin'], -30);
      fxInit();
   }

   function fxBuscar() {
      global $loSmarty;
      $_SESSION['pdFecIni'] = $_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin'] = $_REQUEST['pdFecFin'];
      $lo = new CPruebasRep();
      $lo->pcTipo = $_REQUEST['pcTipo'];
      $lo->pcCodPer = $_REQUEST['pcCodPer'];
      $lo->pcSuperv = $_REQUEST['pcSuperv'];
      $lo->pdFecIni = $_SESSION['pdFecIni'];
      $lo->pdFecFin = $_SESSION['pdFecFin'];
      $llOk = $lo->omBuscar();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=6002', fxXmlError($lo->pcError));
      } else {
         $_SESSION["paData"] = $lo->paData;
         $loSmarty->assign("saData", $_SESSION["paData"]);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg2530.tpl');
      }   
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
         $loSmarty->display('Plantillas/Seg2530.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['pmDescri'] = $lo->pmDescri;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('smDescri', $_SESSION['pmDescri']);
         $loSmarty->assign('scNroInc', $lo->pcNroInc);
         $loSmarty->display('Plantillas/Seg2530.tpl');
      }
   }
   
   function fxReporte() {
      global $loSmarty;
      $lo = new CPruebasRep();
      $lo->pcNroEva = $_REQUEST['pcNroEva'];
      if (substr($lo->pcNroEva, 0, 1) == 'E') {   //eficiencia terreno
         $llOk = $lo->omEvaluacion2();
      } elseif (substr($lo->pcNroEva, 0, 1) == 'C') {   //conduccion de trenes
         $llOk = $lo->omEvaluacion3();
      } elseif (substr($lo->pcNroEva, 0, 1) == 'R') {   //velocidad por radar
         $llOk = $lo->omEvaluacion1();
      } elseif (substr($lo->pcNroEva, 0, 1) == 'T') {   //conduccion de tractos
         $llOk = $lo->omEvaluacion4();
      }
      if (!$llOk) {
         fxInit();
         fxAlert(fxXmlError($lo->pcError));
      } else {
	     $loSmarty->assign('scFilRet', $lo->pcFilRet);
         $loSmarty->assign('scBehavior', '2');
         $loSmarty->display('Plantillas/Seg2530.tpl');
      }
   }

   function fxInit() {
      global $loSmarty;
      $lo = new CPruebasRep();
      $llOk = $lo->omInit();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=6002', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paTipo'] = $lo->paTipo;
         $_SESSION['paSubDiv'] = $lo->paSubDiv;
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paSuperv'] = $lo->paSuperv;
         $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
         $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->assign('saTipo', $_SESSION['paTipo']);
         $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
         $loSmarty->assign('saSuperv', $_SESSION['paSuperv']);
         $loSmarty->display('Plantillas/Seg2530.tpl');
      }
   }
?>