<?php
   // Registro y mantenimiento de usuarios
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoUsuario.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1390', 'Mnu0000.php?Id=1000'))
      return;
   // Verifica inicio de sesion
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESION');
   } else if (@$_POST['Boton1'] == 'Editar') {
      fxEditar();
   } else if (@$_POST['Boton1'] == 'Nuevo') {
      fxNuevo();
   } else if (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') { 
      fxAplicar();
   } else if (@$_POST['Boton1'] == 'Salir') {  
      fxHeader('Mnu0000.php?Id=1000');
   } else if (@$_POST['Boton2'] == 'Actualizar') {
      fxActualizar();
   } else if (@$_POST['Boton3'] == 'Grabar') {
      fxGrabar();
   } else {          
      fxInit();
   }
   
   // Funciones auxiliares
   function fxInit() {
      global $loSmarty;
      $lo = new CMtoUsuario();
      $llOk = $lo->omInitUsuarios();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else { 
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paCodUsu'] = $lo->paCodUsu;
         $_SESSION['paPerfil'] = $lo->paPerfil;
         fxScreen();
      }
   }
   
   function fxEditar() {
      global $loSmarty;
      if (empty($_REQUEST['pcCodUsu'])) {
         fxScreen();
         return;
      }
      $lo = new CMtoUsuario();
      $lo->pcCodUsu = @$_REQUEST['pcCodUsu'];
      $llOk = $lo->omEditar();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {                  
         $_SESSION['plNuevo'] = false;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scCodUsu', $lo->pcCodUsu);
         $loSmarty->assign('scNombre', $lo->pcNombre);
         $loSmarty->assign('scEstado', $lo->pcEstado);
         $loSmarty->assign('sdFecFin', $lo->pdFecFin);
         $loSmarty->assign('scPerfil', $lo->pcPerfil);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->assign('saPerfil', $_SESSION['paPerfil']);
         $loSmarty->display('Plantillas/Seg1390.tpl');
      }
   }

   function fxNuevo() {
      global $loSmarty;
      $_SESSION['plNuevo'] = true;
      $lo = new CMtoUsuario();
      $llOk = $lo->omNuevoUsuario();
      if (!$llOk) { // Error         
         fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $loSmarty->assign('scBehavior', '2');
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->assign('saPerfil', $_SESSION['paPerfil']);
         $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
         $loSmarty->display('Plantillas/Seg1390.tpl');
      }
   }

   function fxActualizar() {
      global $loSmarty;
      $lo = new CMtoUsuario();
      $lo->pcCodUsu = $_REQUEST['pcCodUsu'];
      $lo->pcEstado = $_REQUEST['pcEstado'];
      $lo->pcPerfil = $_REQUEST['pcPerfil'];
      $lo->pdFecFin = $_REQUEST['pdFecFin'];
      $lo->pcUsuCod = $_SESSION['gcCodUsu'];
      $llOk = $lo->omActualizar();
      if (!$llOk) { // Error         
         fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         fxInit();
         fxAlert('GRABACION CONFORME');
      }
   }

   function fxGrabar() {
      global $loSmarty;
      $lo = new CMtoUsuario();
      $lo->pcCodPer = $_REQUEST['pcCodPer'];
      $lo->pcEstado = 'A';
      $lo->pcPerfil = $_REQUEST['pcPerfil'];
      $lo->pcUsuCod = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabar();
      if (!$llOk) { // Error         
         fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         fxInit();
         fxAlert('GRABACION CONFORME');
      }
   }

   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saCodUsu', $_SESSION['paCodUsu']);
      $loSmarty->display('Plantillas/Seg1390.tpl');
   }
?>
