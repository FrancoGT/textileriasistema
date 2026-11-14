<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CLicencias.rep.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG5180', 'Mnu0000.php?Id=6002'))
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

   function fxReporte(){
      global $loSmarty;
      $lo = new CLicenciasRep();
      $_SESSION['pdFecIni'] = $_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin'] = $_REQUEST['pdFecFin'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->pdFecIni = $_SESSION['pdFecIni'];
      $lo->pdFecFin = $_SESSION['pdFecFin'];
      $lo->pcTipo   = $_REQUEST['pcTipo'];
      if( $lo->pcTipo == 'C'){
         $llOk = $lo->omReportePruebas();
      }if( $lo->pcTipo == 'E'){
          $llOk = $lo->omReportePruebas1();
      }if( $lo->pcTipo == 'R'){
          $llOk = $lo->omReportePruebas2();
      }if( $lo->pcTipo == 'T'){
          $llOk = $lo->omReportePruebas3();
      }
     if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         $loSmarty->assign('scFilRep', $lo->pcFilRep);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg5180.tpl');
      }
   }
   
   function fxInit(){
      global $loSmarty;
      $lo=new CLicenciasRep();
      $llOk=$lo->omInit();
      if(!$llOk){
         fxHeader('Mnu0000.php?Id=6002',fxXmlError($lo->pcError));
      }else{
         $_SESSION['paTipo'] = $lo->paTipo;
         $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
         $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
         $loSmarty->assign('saTipo',$_SESSION['paTipo']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg5180.tpl');
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
      $loSmarty->assign('saTipo',$_SESSION['paTipo']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg5180.tpl');
   }
?>
