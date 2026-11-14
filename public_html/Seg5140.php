<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CCursos.rep.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG5140', 'Mnu0000.php?Id=6002'))
      return;
   if (!fxInitSession()) { 
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Salir') {      
      fxHeader('Mnu0000.php?Id=6002');
   } else if (@$_POST['Boton1'] == 'Reporte') {
      fxReporte();
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxScreen();
   } else {
      unset($_SESSION['pcTipo']);
      $_SESSION['pdFecFin'] = date('Y-m-d');
      $_SESSION['pdFecIni'] = fxAddDate($_SESSION['pdFecFin'], -30);      
      fxInit();
   }   

   function fxReporte(){
      global $loSmarty;
      $_SESSION['pcTipo'] = $_REQUEST['pcTipo'];
      $_SESSION['pdFecIni'] = $_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin'] = $_REQUEST['pdFecFin'];
      $lo = new CCursosRep();
      $lo->pcTipo = $_SESSION['pcTipo'];
      $lo->pdFecIni = $_SESSION['pdFecIni'];
      $lo->pdFecFin = $_SESSION['pdFecFin'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omRepPersonalTipo();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         $loSmarty->assign('scFilRep', $lo->pcFilRep);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg5140.tpl');
      }
   }
   
   function fxInit(){
      $lo = new CCursosRep();
      $llOk = $lo->omInit();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=6002', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paTipo'] = $lo->paTipo;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('saTipo', $_SESSION['paTipo']);
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg5140.tpl');
   }
?>
