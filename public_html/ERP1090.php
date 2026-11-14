<?php
   // Registro y mantenimiento de personal operativo
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoAlmacenes.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP1090', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Nuevo') {
      $_SESSION['plNuevo'] = true;
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
	  $loSmarty->assign('saTipo', $_SESSION['paTipo']);
      $loSmarty->display('Plantillas/ERP1090.tpl');
   } else if (@$_POST['Boton1'] == 'Aplicar') {
      $lo = new CMtoAlmacenes();
      $lo->pcCodAlm = @$_REQUEST['pcCodAlm'];
      $llOk = $lo->omEditar();
      if (!$llOk) { // Error
         fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         $_SESSION['pcCodAlm'] = $lo->pcCodAlm;
         $_SESSION['plNuevo'] = false;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scCodAlm', $lo->pcCodAlm);
         $loSmarty->assign('scNomAlm', $lo->pcNomAlm);
         $loSmarty->assign('scDirec', $lo->pcDirec);
         $loSmarty->assign('scEstado', $lo->pcEstado);
		 $loSmarty->assign('scTipo', $lo->pcTipo);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
		 $loSmarty->assign('saTipo', $_SESSION['paTipo']);
         $loSmarty->display('Plantillas/ERP1090.tpl');
      }
   } else if (@$_POST['Boton2'] == 'Grabar') {
      fxGrabar();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxScreen();
   } else {
      $lo = new CMtoAlmacenes();
      $llOk = $lo->omInit();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else { 
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paCodAlm'] = $lo->paCodAlm;
		 $_SESSION['paTipo'] = $lo->paTipo;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saCodAlm', $_SESSION['paCodAlm']);
      $loSmarty->display('Plantillas/ERP1090.tpl');
   }
   
   function fxGrabar() {
      global $loSmarty;      
      $lo = new CMtoAlmacenes();
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcCodAlm = $_REQUEST['pcCodAlm'];	  
      $lo->pcNomAlm = $_REQUEST['pcNomAlm'];
      $lo->pcDirec = $_REQUEST['pcDirec'];
      $lo->pcEstado = $_REQUEST['pcEstado'];
	  $lo->pcTipo = $_REQUEST['pcTipo'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabar();
      if (!$llOk) {
         $lcMensaje = fxXmlError($lo->pcError);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->assign('scCodAlm', $lo->pcCodAlm);
         $loSmarty->assign('scNomAlm', $lo->pcNomAlm);
         $loSmarty->assign('scDirec', $lo->pcDirec);
		 $loSmarty->assign('scTipo', $lo->pcTipo);
         $loSmarty->assign('scEstado', $lo->pcEstado);
         if ($_SESSION['plNuevo'])
            $loSmarty->assign('scBehavior', '2');
         else
            $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/ERP1090.tpl');
         fxAlert($lcMensaje);
      } else {
		    $lo->omInit();
            $_SESSION['paCodAlm'] = $lo->paCodAlm;
         fxScreen();
         fxAlert('GRABACION CONFORME');
      }
   }
?>
