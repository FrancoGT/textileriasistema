<?php
   // Registro y mantenimiento de perfiles de usuario
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG6210', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESION');
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } else if (@$_POST['Boton1'] == 'Aplicar') {
      fxAplicar();
   } else if (@$_POST['Boton2'] == 'Nuevo') {
      fxNuevo();
   } else if (@$_POST['Boton2'] == 'Eliminar') {
      fxEliminar();
   } else if (@$_POST['Boton2'] == 'Cancelar' or @$_POST['Boton3'] == 'Cancelar') {
      fxScreen();
   } else if (@$_POST['Boton3'] == 'Grabar') {
      fxGrabar();
   } else {
      fxInit();
   }
   
   // Funciones auxiliares
   function fxInit() {
      global $loSmarty;
      $lo = new CMtoPerfiles();
      $llOk = $lo->omInitPerfiles();
      if (!$llOk) {// Error
         fxHeader('Mnu0000.php?Id=1000',fxXmlError($lo->pcError));
      } else {
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paPerfil'] = $lo->paPerfil;
         fxScreen();
      }
   }
   
    function fxAplicar(){
      global $loSmarty;
      $lo = new CMtoPerfiles();
      if ($_REQUEST['pcPerfil']) {
         $lo->pcPerfil = $_REQUEST['pcPerfil'];
      } else {
         $lo->pcPerfil = $_SESSION['pcPerfil'];
      }
      $_SESSION['pcPerfil'] = $lo->pcPerfil;
      $llOk = $lo->omBuscarPorPerfil();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('saPerfil', $lo->paPerfil);
         $loSmarty->assign('pcDescri', $lo->pcDescri);
         $loSmarty->display('Plantillas/Seg6210.tpl');
      }
   }
         
   function fxEliminar() {
      global $loSmarty;
      $lo = new CMtoPerfiles();
      $lo->pcPerfil = $_SESSION['pcPerfil'];
      $lo->pcCodOpc = $_REQUEST['pcCodOpc'];
      $lo->paPerfil = $_REQUEST['saPerfil'];
      $llOk = $lo->omEliminarPerfil();
      if (!$llOk)
         $lcMessage = $lo->pcError;
      else
         $lcMessage = 'ELIMINACION REALIZADA';
      fxAplicar();
      fxAlert($lcMessage);
   }
   
   function fxGrabar(){
      global $loSmarty;
      $lo = new CMtoPerfiles();
      $lo->pcCodOpc = $_REQUEST['pcCodOpc'];
      $lo->pcEstado = $_REQUEST['pcEstado'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->pcPerfil = $_SESSION['pcPerfil'];
      $llOk = $lo->omGrabarPerfil();
      if (!$llOk)
         $lcMessage = fxXmlError ($lo->pcError);
      else
         $lcMessage = 'GRABACION CORRECTA';
      fxAplicar();
      fxAlert($lcMessage);
   }
          
   function fxNuevo() {
      global $loSmarty;
      $lo = new CMtoPerfiles();
      $lo->pcPerfil = $_SESSION['pcPerfil'];
      $llOk = $lo->omInitOpcion();
      if (!$llOk){
         fxAplicar();
         fxAlert(fxXmlError($lo->pcError));
      } else{
         $loSmarty->assign('scBehavior', '2');
         $loSmarty->assign('saCodOpc', $lo->paCodOpc);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->display('Plantillas/Seg6210.tpl');
      }
   }
  
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saPerfil', $_SESSION['paPerfil']);
      $loSmarty->display('Plantillas/Seg6210.tpl');
   }
?>