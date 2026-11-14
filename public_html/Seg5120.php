<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CCursos.rep.php';
   require_once 'Clases/CMtoPerfiles.php';
   require_once 'Clases/CConsultasXls.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG5120', 'Mnu0000.php?Id=6002'))
      return;
   if (!fxInitSession()) { 
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Salir') {      
      fxHeader('Mnu0000.php?Id=6002');
   } else if (@$_POST['Boton1'] == 'Reporte') {
      fxReporte();
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxScreen();
   } else if (@$_POST['Boton1'] == 'Excel') {
      fxExcel();
   } else if (@$_POST['Boton3'] == 'Cancelar') {
      fxScreen();
   } else {
      $_SESSION['pdFecFin'] = date('Y-m-d');
      $_SESSION['pdFecIni'] = fxAddDate($_SESSION['pdFecFin'], -30);
      fxInit();
   }   
   
   // Funciones auxiliares
   function fxScreen(){
      global $loSmarty;
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg5120.tpl');
   }
   
   function fxReporte(){
     global $loSmarty;
     $lo = new CCursosRep();
     $lo->pcCodPer = @$_REQUEST['pcCodPer'];
     $lo->gcCodUsu = $_SESSION['gcCodUsu'];
     $_SESSION['pdFecIni'] = $_REQUEST['pdFecIni'];
     $_SESSION['pdFecFin'] = $_REQUEST['pdFecFin'];
     $lo->pdFecIni = $_SESSION['pdFecIni'];
     $lo->pdFecFin = $_SESSION['pdFecFin'];
     $llOk = $lo->omReporteCurso();
     if (!$llOk) {
        fxInit();
        fxAlert(fxXmlError($lo->pcError));
     } else {
        $loSmarty->assign('scFilRet', $lo->pcFilRet);
        $loSmarty->assign('scBehavior', '1');
        $loSmarty->display('Plantillas/Seg5120.tpl');
     }
   }
   
   function fxExcel() {
      global $loSmarty;
      $lo = new CConsultasXls();
      $lo->pcCodPer = @$_REQUEST['pcCodPer'];
      $lo->gcCodUsu = $_SESSION['gcCodUsu'];
      $_SESSION['pdFecIni'] = $_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin'] = $_REQUEST['pdFecFin'];
      $lo->pdFecIni = $_SESSION['pdFecIni'];
      $lo->pdFecFin = $_SESSION['pdFecFin'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omRepCap();
      if (!$llOk) {
            fxInit();
            fxAlert(fxXmlError($lo->pcError));
      }
      $loSmarty->assign("saData", $lo->paData);
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->display('Plantillas/Seg5120.tpl');
   }

   
   function fxInit(){
      global $loSmarty;
      $lo = new CCursosRep();
      $llOk = $lo->omInit();
      if(!$llOk){
         fxHeader('Mnu0000.php?Id=6002', fxXmlError($lo->pcError));
      }else{
       $_SESSION['paCodPer'] = $lo->paCodPer;
       $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
       $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
       $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
       $loSmarty->assign('scBehavior', '0');
       $loSmarty->display('Plantillas/Seg5120.tpl');
      }
   }
?>
