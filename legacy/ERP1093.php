<?php
   // Registro y mantenimiento BANCOS
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoBancos.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP1093', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Nuevo') {
      $_SESSION['plNuevo'] = true;
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
	  $loSmarty->assign('saCiudad', $_SESSION['paCiudad']);
      $loSmarty->display('Plantillas/ERP1093.tpl');
   } else if (@$_POST['Boton1'] == 'Aplicar') {
      $lo = new CMtoBancos();
      $lo->pcCodBan = @$_REQUEST['pcCodBan'];
      $llOk = $lo->omEditar();
      if (!$llOk) { // Error
         fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         $_SESSION['pcCodBan'] = $lo->pcCodBan;
         $_SESSION['plNuevo'] = false;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scCodBan', $lo->pcCodBan);
		 $loSmarty->assign('scNomBan', $lo->pcNomBan);
         $loSmarty->assign('scEstado', $lo->pcEstado);
		 $loSmarty->assign('scCiudad', $lo->pcCiudad);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
		 $loSmarty->assign('saCiudad', $_SESSION['paCiudad']);
         $loSmarty->display('Plantillas/ERP1093.tpl');
      }
   } else if (@$_POST['Boton2'] == 'Grabar') {
      fxGrabar();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxScreen();
   } else {
      $lo = new CMtoBancos();
      $llOk = $lo->omInit();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else { 
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paCodBan'] = $lo->paCodBan;
		 $_SESSION['paCiudad'] = $lo->paCiudad;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saCodBan', $_SESSION['paCodBan']);
      $loSmarty->display('Plantillas/ERP1093.tpl');
   }
   
   function fxGrabar() {
      global $loSmarty;      
      $lo = new CMtoBancos();
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcCodBan = $_REQUEST['pcCodBan'];	  
      $lo->pcNomBan = $_REQUEST['pcNomBan'];
      $lo->pcEstado = $_REQUEST['pcEstado'];
	  $lo->pcCiudad = $_REQUEST['pcCiudad'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabar();
      if (!$llOk) {
         $lcMensaje = fxXmlError($lo->pcError);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
		 $loSmarty->assign('saCiudad', $_SESSION['paCiudad']);
         $loSmarty->assign('scCodBan', $lo->pcCodBan);
         $loSmarty->assign('scNomBan', $lo->pcNomBan);
		 $loSmarty->assign('scCiudad', $lo->pcCiudad);
         $loSmarty->assign('scEstado', $lo->pcEstado);
         if ($_SESSION['plNuevo'])
            $loSmarty->assign('scBehavior', '2');
         else
            $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/ERP1093.tpl');
         fxAlert($lcMensaje);
      } else {
		    $lo->omInit();
            $_SESSION['paCodBan'] = $lo->paCodBan;
         fxScreen();
         fxAlert('GRABACION CONFORME');
      }
   }
?>
