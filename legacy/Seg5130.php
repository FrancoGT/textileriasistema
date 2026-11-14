<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CCursos.rep.php';
   require_once 'Clases/CMtoPerfiles.php';
   require_once 'Clases/CConsultasXls.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG5130', 'Mnu0000.php?Id=6002'))
      return;
   if (!fxInitSession()) { 
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Salir') {      
      fxHeader('Mnu0000.php?Id=6002');
   } else if (@$_POST['Boton1'] == 'Reporte') {
      fxReporte();
   } else if (@$_POST['Boton1'] == 'Excel') {
      fxExcel();
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxScreen();
   } else if (@$_POST['Boton3'] == 'Cancelar') {
      fxScreen();
   } else {
      unset($_SESSION['pcCodPer']);
      unset($_SESSION['pcCodNor']);
      fxInit();
   }   

   function fxReporte(){
      global $loSmarty;
      $_SESSION['pcCodPer'] = $_REQUEST['pcCodPer'];
      $_SESSION['pcCodNor'] = $_REQUEST['pcCodNor'];
      $lo = new CCursosRep();
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $lo->pcCodNor = $_SESSION['pcCodNor'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omRepPersonalNormal();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         $loSmarty->assign('scFilRep', $lo->pcFilRep);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg5130.tpl');
      }
   }
   
   function fxExcel(){
      global $loSmarty;
      $_SESSION['pcCodPer'] = $_REQUEST['pcCodPer'];
      $_SESSION['pcCodNor'] = $_REQUEST['pcCodNor'];
      $lo = new CConsultasXls();
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $lo->pcCodNor = $_SESSION['pcCodNor'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omRepCapPer();
      if (!$llOk) {
            fxInit();
            fxAlert(fxXmlError($lo->pcError));
      }
      $loSmarty->assign("saData", $lo->paData);
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->display('Plantillas/Seg5130.tpl');
   }
   
   function fxInit(){
      $lo = new CCursosRep();
      $llOk = $lo->omInit1();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=6002', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paCodNor'] = $lo->paCodNor;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('scCodNor', $_SESSION['pcCodNor']);
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saCodNor', $_SESSION['paCodNor']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg5130.tpl');
   }
?>
