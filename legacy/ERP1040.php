<?php
   // Registro y mantenimiento de personal operativo
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoMaquinas.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP1040', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Nuevo') {
      $_SESSION['plNuevo'] = true;
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
	  $loSmarty->assign('saTipo', $_SESSION['paTipo']);
      $loSmarty->display('Plantillas/ERP1040.tpl');
   } else if (@$_POST['Boton1'] == 'Aplicar') {
      $lo = new CMtoMaquinas();
      $lo->pcCodMaq = @$_REQUEST['pcCodMaq'];
      $llOk = $lo->omEditar();
      if (!$llOk) { // Error
         fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         $_SESSION['pcCodMaq'] = $lo->pcCodMaq;
         $_SESSION['plNuevo'] = false;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scCodMaq', $lo->pcCodMaq);
         $loSmarty->assign('scNomMaq', $lo->pcNomMaq);
         $loSmarty->assign('scEstado', $lo->pcEstado);
		 $loSmarty->assign('scTipo', $lo->pcTipo);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
		 $loSmarty->assign('saTipo', $_SESSION['paTipo']);
         $loSmarty->display('Plantillas/ERP1040.tpl');
      }
   } else if (@$_POST['Boton2'] == 'Grabar') {
      fxGrabar();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxScreen();
   } else {
      $lo = new CMtoMaquinas();
      $llOk = $lo->omInit();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else { 
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paCodMaq'] = $lo->paCodMaq;
		 $_SESSION['paTipo'] = $lo->paTipo;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saCodMaq', $_SESSION['paCodMaq']);
      $loSmarty->display('Plantillas/ERP1040.tpl');
   }
   
   function fxGrabar() {
      global $loSmarty;      
      $lo = new CMtoMaquinas();
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcCodMaq = $_REQUEST['pcCodMaq'];	  
      $lo->pcNomMaq = strtoupper($_REQUEST['pcNomMaq']);
      $lo->pcEstado = $_REQUEST['pcEstado'];
	  $lo->pcTipo = $_REQUEST['pcTipo'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabar();
      if (!$llOk) {
         $lcMensaje = fxXmlError($lo->pcError);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->assign('scCodMaq', $lo->pcCodMaq);
         $loSmarty->assign('scNomMaq', $lo->pcNomMaq);
		 $loSmarty->assign('scTipo', $lo->pcTipo);
         $loSmarty->assign('scEstado', $lo->pcEstado);
         if ($_SESSION['plNuevo'])
            $loSmarty->assign('scBehavior', '2');
         else
            $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/ERP1040.tpl');
         fxAlert($lcMensaje);
      } else {
		    $lo->omInit();
            $_SESSION['paCodMaq'] = $lo->paCodMaq;
         fxScreen();
         fxAlert('GRABACION CONFORME');
      }
   }
?>
