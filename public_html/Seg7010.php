<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CConsultasXls.php';
   require_once 'Clases/CCharlas.rep.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG7010', 'Mnu0000.php?Id=6000'))
      return;
   if (!fxInitSession()) { 
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Salir') {      
      fxHeader('Mnu0000.php?Id=6000');
   } else if (@$_POST['Boton1'] == 'Reporte') {
      fxReporte();
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxScreen();
   } else {
      $_SESSION['pdFecFin'] = date('Y-m-d');
      $_SESSION['pdFecIni'] = fxAddDate($_SESSION['pdFecFin'], -30);
      fxInit();
   }   
   
   function fxScreen(){
      global $loSmarty;
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg7010.tpl');
   }
   
   function fxReporte(){
      global $loSmarty;
      $lo = new CConsultasXls();
     $lo->pcCodPer = @$_REQUEST['pcCodPer'];
      $lo->gcCodUsu = $_SESSION['gcCodUsu'];
     $_SESSION['pdFecIni']=$_REQUEST['pdFecIni'];
     $_SESSION['pdFecFin']=$_REQUEST['pdFecFin'];
     $lo->pdFecIni=$_SESSION['pdFecIni'];
      $lo->pdFecFin=$_SESSION['pdFecFin'];
     $lo->omCharlas();
      $loSmarty->assign('saData', $lo->paData);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg7010.tpl');
   }
   
   function fxInit(){
      global $loSmarty;
      $lo=new CCharlasRep();
      $llOk=$lo->omInit();
      if(!$llOk){
         fxHeader('Mnu0000.php?Id=6000',fxXmlError($lo->pcError));
      }else{
        $_SESSION['paCodPer']=$lo->paCodPer;
       $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
         $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
         $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg7010.tpl');
      }
   }
?>
