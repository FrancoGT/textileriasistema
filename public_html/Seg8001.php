<?php
   // Registro y mantenimiento de empleados
   require_once "Libs/Smarty.class.php";
   require_once "Clases/CMtoUnidad.php";
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   // Verifica inicio de sesion
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Nuevo'] == "Nuevo") {
      fxNuevo();      
   } else if (@$_POST['Aplicar'] == "Aplicar" or @$_GET['Id'] == 'Aceptar') { 
      fxAplicar();      
   } else if (@$_POST['Grabar'] == 'Grabar') {
      fxGrabar();         
   } else if (@$_POST['Buscar'] == 'Buscar') {  
      fxBuscar();      
   } else {          
      fxInit();      
   }
   
   function fxAplicar(){
      global $loSmarty;
      $lo = new CMtoUnidad();
      $lo->pcCodUni = @$_REQUEST["pcCodUni"];            
      if (@$_GET['Id'] == 'Aceptar')
         $lo->pcCodUni = $_SESSION['pcCodPer'];            
      $llOk = $lo->omBuscarUnidad();
      if (!$llOk) { // Error                  
         $loSmarty->assign("scBehavior", '0');
         $loSmarty->display("Plantillas/Seg8001.tpl");         
         fxAlert($lo->pcError);         
      } else {                  
         $_SESSION["pcCodPer"] = $lo->pcCodPer;
         $_SESSION["plNuevo"] = false;
         $loSmarty->assign("scBehavior", '1');
         $loSmarty->assign("scCodUni", $lo->pcCodUni);
         $loSmarty->assign("scDescri", $lo->pcDescri);                  
         $loSmarty->assign("scEstado", $lo->pcEstado);         
         $loSmarty->assign("saEstado", $_SESSION['paEstado']);                  
         $loSmarty->display("Plantillas/Seg8001.tpl");
      }
   }
   function fxInit() {
      global $loSmarty;
      $lo = new CMtoUnidad();      
      $llOk = $lo->omInitUnidad();      
      if (!$llOk) { // Error
         $loSmarty->assign("scAviso", $lo->pcError);
         $loSmarty->assign("scVolver", "Seg8001.php");
         $loSmarty->display("Plantillas/Sis1000.tpl");
      } else { 
         // Guarda estados del personal         
         unset($_SESSION['paEstado']);                  
         $_SESSION['paEstado'] = $lo->paEstado;                  
         $_SESSION['paCodUni'] = $lo->paCodUni; 
         $loSmarty->assign("scBehavior", '0');
         //$loSmarty->assign("scCodPer", '');
         $loSmarty->display("Plantillas/Seg8001.tpl");
      }
   }
   
   function fxNuevo() {
      global $loSmarty;
      $_SESSION["plNuevo"] = true;
      $loSmarty->assign("scBehavior", '1');
      $loSmarty->assign("saEstado", $_SESSION['paEstado']);      
      $loSmarty->assign("scCodUni", $_SESSION['paCodUni']);      
      $loSmarty->display("Plantillas/Seg8001.tpl");      
   }
   
   function fxBuscar() {
      global $loSmarty;
      $lo = new CMtoUnidad();
      $lo->omInitUsuarios();
      $_SESSION['pcVolver'] = 'Seg8001';
      $loSmarty->assign("scBehavior", '1');
      $loSmarty->assign("saCodPer", $lo->paCodUni);      
      $loSmarty->display("Plantillas/Seg9005.tpl");            
   }
   
   function fxGrabar() {
      global $loSmarty;
      $lo = new CMtoUnidad();
      $lo->plNuevo  = $_SESSION['plNuevo'];      
      $lo->pcCodUni = $_REQUEST['pcCodUni'];      
      $lo->pcDescri = $_REQUEST['pcDescri'];              
      if($_REQUEST['pcEstado']){
         $lo->pcEstado = ($_REQUEST['pcEstado']);             
      }        
      $lo->gcCodUsu = $_SESSION['gcCodUsu'];             
      $llOk = $lo->omGrabarUnidad();
      if (!$llOk) { // Error         
         $lcMessage = fxXmlError($lo->pcError);
         fxAlert($lcMessage);         
         fxInit();         
      } else {
         fxInit();
         fxAlert('GRABACION CONFORME');
      }
   }
?>
