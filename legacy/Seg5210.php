<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CPruebas.rep.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG5210', 'Mnu0000.php?Id=6000'))
      return;
   if (!fxInitSession()) { 
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxScreen();
   } else if (@$_POST['Boton1'] == 'Reporte') {
      fxReporte();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=6000');
   } else {
      $_SESSION['pdFecFin'] = date('Y-m-d');
      $_SESSION['pdFecIni'] = fxAddDate($_SESSION['pdFecFin'], -30);
      fxInit();
   }   

   // Funciones auxiliares
   function fxReporte(){
      global $loSmarty;
      $_SESSION['pcTipo']   = $_REQUEST['pcTipo'];
      $_SESSION['pdFecIni'] = $_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin'] = $_REQUEST['pdFecFin'];
      $lo = new CPruebasRep();
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->pcTipo   = $_SESSION['pcTipo'];
      $lo->pdFecIni = $_SESSION['pdFecIni'];
      $lo->pdFecFin = $_SESSION['pdFecFin'];
      $llOk = $lo->omPruebasCabecera();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         $loSmarty->assign('scFilRep', $lo->pcFilRep);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg5210.tpl');
      }
   }
   
   function fxInit(){
      $lo = new CPruebasRep();
      $llOk = $lo->omInit1();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=6000', fxXmlError($lo->pcError));
         return;
      }
      $_SESSION['paTipo'] = $lo->paTipo;
      fxScreen();
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('saTipo', $_SESSION['paTipo']);
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg5210.tpl');
   }

?>
