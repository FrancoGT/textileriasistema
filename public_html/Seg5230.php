<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.rep.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG5230', 'Mnu0000.php?Id=6002'))
      return;
   if (!fxInitSession()) { 
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxScreen();
   } else if (@$_POST['Boton1'] == 'Reporte') {
      $_SESSION['pcVolver'] = 'Seg5230.php';
      fxHeader('Seg9011.php?Id=Req');
   } else if (@$_POST['Boton1'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg5230.php';
      fxHeader('Seg9008.php?Beh=2');
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=6002');
   } else if (@$_GET['Id'] == 'Aceptar') {
      fxReporte();
   } else {
      fxScreen();
   }   

   // Funciones para Seleccionar
   function fxReporte(){
      global $loSmarty;
	  $lo = new CIncidentesRep();
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
	  $llOk = $lo->omConsultarIncidentes($_SESSION['paIncNro']);
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         $loSmarty->assign('scFilRep', $lo->pcFilRep);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg5230.tpl');
      }
   }
   
   function fxInit() {
//      $_SESSION['pcVolver'] = 'Seg5230.php';
//      fxHeader('Seg9008.php?Beh=2');
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg5230.tpl');
   }
?>