<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CLicencias.rep.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG5170', 'Mnu0000.php?Id=6002'))
      return;
   if (!fxInitSession()) { 
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxHeader('Mnu0000.php?Id=6002');
   } else {
      fxInit();
   }   

   function fxReporte(){
      global $loSmarty;
      $lo = new CLicenciasRep();
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omLicenciasPersonalVigente();
      if (!$llOk) {
         fxScreen();
		 fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('scFilRep', $lo->pcFilRep);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg5170.tpl');
      }
   }
   
   function fxInit(){
      $lo = new CLicenciasRep();
      $llOk = $lo->omInit();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=6002', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg5170.tpl');
   }
?>
