<?php
   // Consulta de incidentes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.php';
   session_cache_limiter();
   session_start();
   $_SESSION['pcReqInv'] = 'N';
   if (@$_GET['Id'] == 'Req') {
      $_SESSION['pcReqInv'] = 'S';
   }
   $loSmarty = new Smarty;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if ((@$_POST['Boton2'] == 'Aceptar')) {
      $lcNroInc = $_REQUEST['pcNroInc'];
      $laIncNro = @$_REQUEST['paIncNro'];
      if (empty($lcNroInc)) {
	     if (empty($laIncNro)) {
            fxScreen();
            fxAlert('NO HA ELEGIDO INCIDENTE(S)');
            return;
         }
      }
      $_SESSION['paIncNro'] = @$_REQUEST['paIncNro'];
      $_SESSION['pcNroInc'] = @$_REQUEST['pcNroInc'];
      fxHeader($_SESSION['pcVolver'].'?Id=Aceptar');
   } else if ((@$_POST['Boton1'] == 'Salir')) {
      fxHeader($_SESSION['pcVolver']);
   } else if ((@$_POST['Boton1'] == 'Aplicar')) {
      fxAplicar();
   } else if ((@$_POST['Boton2'] == 'Cancelar')) {
      fxInit();
   } else {
      $_SESSION['pdFecFin'] = date('Y-m-d');
      $_SESSION['pdFecIni'] = fxAddDate($_SESSION['pdFecFin'], -30);
      fxInit();
   }

   // Funciones auxiliares
  function fxInit() {
      global $loSmarty;
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
	  $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg9011.tpl');
   }
   
   // Funciones auxiliares
   function fxAplicar() {
      global $loSmarty;
      $_SESSION['pdFecIni'] = $_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin'] = $_REQUEST['pdFecFin'];
      $lo = new CIncidentes();
      $lo->pdFecIni = $_SESSION['pdFecIni'];
      $lo->pdFecFin = $_SESSION['pdFecFin'];
      $llOk = $lo->omConsultar($_SESSION['pcReqInv']);
      if (!$llOk) {
         fxInit();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['paNroInc'] = $lo->paNroInc;
         fxScreen();
     }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('saNroInc', $_SESSION['paNroInc']);
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->display('Plantillas/Seg9011.tpl');
   }
?>