<?php
   // Registro de Incidentes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2280', 'Mnu0000.php?Id=4002'))
      return;
   if (!empty($_GET['pcNroInc'])) {
      $_SESSION['pcNroInc']=$_GET['pcNroInc'];
      fxAplicar();
      return;
   }
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Buscar'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2280';
      fxHeader('Seg9008.php?Id=Req');
   } else if (@$_POST['Nuevo'] == 'Nuevo') {
      fxNuevo();
   } else if (@$_POST['Aplicar'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if (@$_POST['Eliminar'] == 'Eliminar') {
      fxEliminar();
   } else if (@$_POST['Grabar'] == 'Grabar') {
      fxGrabar();
   } else {
      fxInit();
   }

   function fxGrabar() {
      global $loSmarty;
      fxLoadVar();
      $lo = new CIncidentes();
      $lo->pmEveCro = $_SESSION['pmEveCro'];
      $lo->pmDesDet = $_SESSION['pmDesDet'];
      $lo->pcNroInc = $_SESSION['pcNroInc'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarAnalisisDetalladoIncidente();
      if (!$llOk) { // Error
         $lcMessage = fxXmlError($lo->pcError);
         fxScreen();
         fxAlert($lcMessage);
      } else {
         fxHeader('Seg2240.php?pcNroInc='.$lo->pcNroInc);
         return;
      }
   }
      
   function fxInit() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2280.tpl');
   }
   
   function fxNuevo() {
      global $loSmarty;
      $laTmp = $_REQUEST['paDataF'];
      $i = count($laTmp) + 1;
      $laTmp[$i] = array($i, '');
      $_SESSION['paDataF'] = $laTmp;
      fxScreen();
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('smEveCro', $_SESSION['pmEveCro']);
      $loSmarty->assign('smDesDet', $_SESSION['pmDesDet']);
      $loSmarty->display('Plantillas/Seg2280.tpl');
   }
   
   function fxAplicar() {
      global $loSmarty;
      $_SESSION['plNuevo'] = false;
      $lo = new CIncidentes();
      if (@$_GET['Id'] == 'Aceptar') 
         $lo->pcNroInc = $_SESSION['pcNroInc'];
      else  
         $lo->pcNroInc = $_REQUEST['pcNroInc'];
      $llOk = $lo->omAplicar7();
      if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2280.tpl');
         fxAlert($lcMessage);
      } else {
         $_SESSION['pmDatos']  = $lo->pmDatos;
         $_SESSION['pcNroInc'] = $lo->pcNroInc;
         $_SESSION['pmEveCro'] = $lo->pmEveCro;
         $_SESSION['pmDesDet'] = $lo->pmDesDet;
         fxScreen();
      }
   }
   
   function fxLoadVar() {
      $_SESSION['pcNroInc'] = $_REQUEST['pcNroInc'];
      $_SESSION['pmEveCro'] = $_REQUEST['pmEveCro'];
      $_SESSION['pmDesDet'] = $_REQUEST['pmDesDet'];
   }
?>