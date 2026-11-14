<?php
   // Registro y mantenimiento de unidades
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoUnidad.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1430', 'Mnu0000.php?Id=1000'))
      return;
   // Verifica inicio de sesion
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif (@$_POST['Boton1'] == 'Nuevo') {
      fxNuevo();
   } elseif (@$_POST['Boton1'] == 'Aplicar') { 
      fxAplicar();
   } elseif (@$_POST['Boton1'] == 'Buscar') {  
      fxBuscar();
   } elseif (@$_POST['Boton1'] == 'Salir') {  
      fxHeader('Mnu0000.php?Id=1000');
   } elseif (@$_POST['Boton2'] == 'Grabar') {
      fxGrabar();
   } elseif (@$_POST['Boton2'] == 'Cancelar') {
      fxInit();
   } else {      
      fxInit();
   }
   
   // Funciones auxiliares
   function fxAplicar(){
      $lo = new CMtoUnidad();
      $lo->pcCodUni = @$_REQUEST['pcCodUni'];
      $llOk = $lo->omAplicar();
      if (!$llOk) { // Error                  
         fxScreenD();
         fxAlert(fxXmlError($lo->pcError));
      } else {                  
         $_SESSION['pcCodUni'] = $lo->pcCodUni;
         $_SESSION['pcDescri'] = $lo->pcDescri;
         $_SESSION['pcEstado'] = $lo->pcEstado;
         $_SESSION['plNuevo'] = false;
         fxScreen();
      }
   }
   
   function fxInit() {
      global $loSmarty;
      $lo = new CMtoUnidad();
      $llOk = $lo->omInitUnidad();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else { 
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paCodUni'] = $lo->paCodUni;
         fxScreenD();
      }
   }
   
   function fxNuevo() {
      global $loSmarty;
      $_SESSION['plNuevo'] = true;
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('scCodUni', '');
      $loSmarty->display('Plantillas/Seg1430.tpl');
   }
   
   function fxBuscar() {
      $_SESSION['pcVolver'] = 'Seg1430.php';
      fxHeader('Seg9010.php');
   }
   
   function fxGrabar() {
      global $loSmarty;
      $_SESSION['pcCodUni'] = strtoupper($_REQUEST['pcCodUni']);
      $_SESSION['pcDescri'] = strtoupper($_REQUEST['pcDescri']);
      $_SESSION['pcEstado'] = $_REQUEST['pcEstado'];
      $lo = new CMtoUnidad();
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcCodUni = $_SESSION['pcCodUni'];
      $lo->pcDescri = $_SESSION['pcDescri'];
      $lo->pcEstado = $_SESSION['pcEstado'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarUnidad();
      if (!$llOk) {
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         fxInit();
         fxAlert('GRABACION CONFORME');
      }
   }

   function fxScreenD() {
      global $loSmarty;
      $loSmarty->assign('saCodUni', $_SESSION['paCodUni']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg1430.tpl');
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scCodUni', $_SESSION['pcCodUni']);
      $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
      $loSmarty->assign('scEstado', $_SESSION['pcEstado']);
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg1430.tpl');
   }
?>
