<?php
   // Seguimiento de acciones a tomar de incidentes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2350', 'Mnu0000.php?Id=4000'))
      return;
   if (!empty($_GET['pcNroInc'])) {
      $_SESSION['pcNroInc'] = $_GET['pcNroInc'];
      fxInit();
      fxAplicar();
      return;
   }
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2350.php';
      fxHeader('Seg9008.php?Id=Req');
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=4002');
   } else if (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if(@$_POST['Boton2'] == 'Nuevo') {
      fxNuevo();
   } else if (@$_POST['Boton2'] == 'Eliminar') {
      fxEliminar();
   } else if (@$_POST['Boton4'] == 'Grabar') {
      fxGrabar();
   } else if (@$_POST['Boton4'] == 'Cancelar') {
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2350.tpl');
   } else {
      unset($_SESSION['paData']);
      fxInit();
      fxScreenI();
   }
   
   function fxAplicar() {
      $lo = new CIncidentes();
      if (@$_GET['Id'] == 'Aceptar' or !empty($_GET['pcNroInc'])) {
         $lo->pcNroInc = $_SESSION['pcNroInc'];
      } else {
         $lo->pcNroInc = $_REQUEST['pcNroInc'];
      }
      $llOk = $lo->omAplicar8();
      if (!$llOk) {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2350.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['pmDatos'] = $lo->pmDatos;
         $_SESSION['paData'] = $lo->paData;
         fxScreen();
      }
   }
   
   function fxNuevo() {
      $lcCodPer = $_REQUEST['pcCodPer'];
      $lcNombre = '*** ERROR ***';
      foreach($_SESSION['paCodPer'] as $laFila) {
         if ($laFila[0] == $lcCodPer) {
            $lcNombre = $laFila[1];
            break;
         }
      }
      $laData = $_REQUEST['paData'];
      $i = fxCount($laData) + 1;
	 // ID, CCODPER, CNOMBRE. CLIDER, CESTADO
      $laData[$i]= array($i, $lcCodPer, $lcNombre , 'N', 'A');
      $_SESSION['paData'] = $laData;
	 fxScreen();
   }
   
   function fxEliminar() {
      $laData = $_REQUEST['paData'];
	   if (isset($_REQUEST['paCheck'])) {
	      $laData = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
          $_SESSION['paData'] = $laData;
      }
      fxScreen();
   }
   
   function fxGrabar() {
      global $loSmarty;
      $_SESSION['paData'] = $_REQUEST['paData'];
      $lo = new CIncidentes();
      $lo->paData   = $_SESSION['paData'];
      $lo->pcNroInc = $_SESSION['pcNroInc'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarEquipo();
      if (!$llOk) { // Error
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {
        fxHeader('Seg2510.php?pcNroInc='.$_SESSION['pcNroInc']);
        return;
      }
   }
   
   function fxInit() {
      $lo = new CIncidentes();
      $llOk = $lo->omInitInvestigacion();
	  if (!$llOk) {
         fxHeader('Mnu0000.php?Id=4000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paEstado1'] = $lo->paEstado;//OJO VER
      }
   }
   
   function fxScreenI() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2350.tpl');
   }

   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('saCodPer',  $_SESSION['paCodPer']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
      $loSmarty->assign('saData',   $_SESSION['paData']);
      $loSmarty->assign('saEstado', $_SESSION['paEstado1']);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg2350.tpl');
   }
?>