<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CPruebas.rep.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG5220', 'Mnu0000.php?Id=6002'))
      return;
   if (!fxInitSession()) { 
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxScreen();
   } else if (@$_POST['Boton1'] == 'Reporte') {
      fxReporte();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=6002');
   } else {
      $_SESSION['pdFecFin'] = date('Y-m-d');
      $_SESSION['pdFecIni'] = fxAddDate($_SESSION['pdFecFin'], -30);
      fxInit();
   }   

   // Funciones auxiliares
   function fxReporte(){
      global $loSmarty;
      $_SESSION['pcTipo']   = $_REQUEST['pcTipo'];
      $_SESSION['pcCodPer'] = $_REQUEST['pcCodPer'];
      $_SESSION['pdFecIni'] = $_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin'] = $_REQUEST['pdFecFin'];
      $lo = new CPruebasRep();
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->pcTipo   = $_SESSION['pcTipo'];
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $lo->pdFecIni = $_SESSION['pdFecIni'];
      $lo->pdFecFin = $_SESSION['pdFecFin'];
      $llOk = $lo->omPruebasPersonal();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         $loSmarty->assign('scFilRep', $lo->pcFilRep);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg5220.tpl');
      }
   }
   
   function fxInit(){
      $lo = new CPruebasRep();
      $llOk = $lo->omInit2();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=6002', fxXmlError($lo->pcError));
         return;
      }
      $_SESSION['paTipo']   = $lo->paTipo;
      $_SESSION['paCodPer'] = $lo->paCodPer;
      fxScreen();
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('saTipo',   $_SESSION['paTipo']);
      $loSmarty->assign('scTipo',   $_SESSION['pcTipo']);
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg5220.tpl');
   }
?>