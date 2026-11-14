<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CConsultasXls.php';
   require_once 'Clases/CCharlas.rep.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG7020', 'Mnu0000.php?Id=6000'))
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
      $loSmarty->assign('saTipo', $_SESSION['paTipo']);
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg7020.tpl');
   }
   function fxReporte(){
      global $loSmarty;
      $lo = new CConsultasXls();
      $lo->pcTipo = @$_REQUEST['pcTipo'];
      $lo->gcCodUsu = $_SESSION['gcCodUsu'];
      $_SESSION['pdFecIni']=$_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin']=$_REQUEST['pdFecFin'];
      $lo->pdFecIni=$_SESSION['pdFecIni'];
      $lo->pdFecFin=$_SESSION['pdFecFin'];
      $llOk=$lo->omCharlasTipo();
      if(!$llOk){
         fxHeader('Mnu0000.php?Id=6000',fxXmlError($lo->pcError));
      } else {
        $loSmarty->assign('saData', $lo->paData);
        $loSmarty->assign('scBehavior', '1');
        $loSmarty->display('Plantillas/Seg7020.tpl');
      }
   }
   function fxInit(){
      global $loSmarty;
      $lo=new CCharlasRep();
      $llOk=$lo->omInit();
      if(!$llOk){
         fxHeader('Mnu0000.php?Id=6000',fxXmlError($lo->pcError));
      } else {
         $_SESSION['paTipo']=$lo->paTipo;
         $loSmarty->assign('saTipo', $_SESSION['paTipo']);
         $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
         $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg7020.tpl');
      }
   }
   
   
?>
