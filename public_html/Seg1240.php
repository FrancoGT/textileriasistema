<?php
   // Registro y mantenimiento de normas
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoNormas.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1240', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Nuevo') { // Nueva cabecera de norma
      $_SESSION['plNuevo'] = true;
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->assign('scCodNor', '');
      $loSmarty->assign('scDescri', '');
      $loSmarty->display('Plantillas/Seg1240.tpl');
   } else if (@$_POST['Boton1'] == 'Aplicar') {
      $lo = new CMtoNormas();
      $lo->pcTipo = @$_REQUEST['pcTipo'];
      $_SESSION['pcTipo'] = $lo->pcTipo;
      $llOk = $lo->omCargarNormasPorTipo();
      if (!$llOk) { // Error
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['pcDescri'] = $lo->pcDescri;
		 $loSmarty->assign('scTipo', $_SESSION['pcTipo']);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scDescri', $lo->pcDescri);
         $loSmarty->assign('saData', $lo->paCodNor);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->display('Plantillas/Seg1240.tpl');
      }
   } else if (@$_POST['Boton1'] == 'Editar') {   // Editar cabecera de norma
      if ($_REQUEST['pcTipo']) {
         $lcDescri = '*** ERROR ***';   // Ubica descripcion
         $lcCodNor = trim($_REQUEST['pcTipo']);
         foreach($_SESSION['paTipo'] as $i) {
            if (trim($i[0]) == $lcCodNor) {
               $lcDescri = $i[1];
               break;
            }
         }
         $_SESSION['plNuevo'] = false;
         $loSmarty->assign('scBehavior', '2');
         $loSmarty->assign('scCodNor', $lcCodNor);
         $loSmarty->assign('scDescri', $lcDescri);
         $loSmarty->display('Plantillas/Seg1240.tpl');
      } else {
         fxScreen();
      }
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } else if (@$_POST['Boton2'] == 'Grabar') { // graba detalle de normas
      fxGrabarNormas();
   } else if (@$_POST['Boton2'] == 'Nuevo') { // Nueva norma
      $laTmp = $_REQUEST['paData'];
      $i = count($laTmp) + 1;
      $laTmp[] = array($i, '', '', 'A', 'N', '1');
      $_SESSION['paData'] = $laTmp;
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
      $loSmarty->assign('saData', $_SESSION['paData']);
      $loSmarty->display('Plantillas/Seg1240.tpl');
   } else if (@$_POST['Boton2'] == 'Eliminar') { // Elimina fila
      fxEliminar();
   } else if (@$_POST['Boton2'] == 'Cancelar' or @$_POST['Boton3'] == 'Cancelar') {
      fxScreen();
   } else if (@$_POST['Boton3'] == 'Grabar') {
      $lo = new CMtoNormas();
      $lo->plNuevo = $_SESSION['plNuevo'];
      $lo->pcCodNor = $_REQUEST['pcCodNor'];
      $lo->pcDescri = $_REQUEST['pcDescri'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarCabecera();
      if (!$llOk) { // Error
         $lcMessage = fxXmlError($lo->pcError);
         fxAlert($lcMessage);
      } else {
         fxAlert('GRABACION CONFORME');
      }
      fxInit();
   } else {
      fxInit();
   }

   // Funciones auxiliares
   function fxInit() {
      global $loSmarty;
      $lo = new CMtoNormas();
      $llOk = $lo->omInit();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else {
         // Guarda estados de proyectos
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paTipo']   = $lo->paTipo;
         fxScreen();
      }
   }
   
   function fxGrabarNormas() {
      global $loSmarty;
      $lo = new CMtoNormas();
      $lo->pcCodNor = $_SESSION['pcTipo'];
      $lo->paCodNor = $_REQUEST['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarNormas();
      if (!$llOk) { // Error
         $lcMensaje = fxXmlError($lo->pcError);
      } else {
         $lcMensaje = 'GRABACION CONFORME';
      }
      fxScreen();
      fxAlert($lcMensaje);
   }

   function fxEliminar() {
      global $loSmarty;
      $laTmp = $_REQUEST['paData'];
      if (isset($_REQUEST['paCheck'])) {
         $laTmp = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
      }
      $_SESSION['paData'] = $laTmp;
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
      $loSmarty->assign('saData', $_SESSION['paData']);
      $loSmarty->display('Plantillas/Seg1240.tpl');
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('saTipo', $_SESSION['paTipo']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg1240.tpl');
   }
?>