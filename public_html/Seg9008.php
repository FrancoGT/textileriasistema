<?php
   // Consulta de incidentes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.php';
   session_cache_limiter();
   session_start();
   if (@$_GET['Id'] == 'Req') {
      $_SESSION['pcReqInv'] = 'S';
   } elseif (!isset($_SESSION['pcReqInv'])) {
      $_SESSION['pcReqInv'] = 'N';
   }
   if (@$_GET['Beh'] == '2') {
      $_SESSION['pcBehavior'] = '2';
   } elseif (!isset($_SESSION['pcBehavior'])) {
      $_SESSION['pcBehavior'] = '1';
   }
   $loSmarty = new Smarty;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if ((@$_POST['Boton2'] == 'Aceptar')) {
      $lcNroInc = $_REQUEST['pcNroInc'];
      $laIncNro = @$_REQUEST['paIncNro'];
      if (empty($lcNroInc)) {
         if (empty($laIncNro)) {
            fxScreenD();
            fxAlert('NO HA ELEGIDO INCIDENTE(S)');
            return;
         }
      }
      $_SESSION['paIncNro'] = @$_REQUEST['paIncNro'];
      $_SESSION['pcNroInc'] = @$_REQUEST['pcNroInc'];
      unset($_SESSION['pcReqInv']);
      unset($_SESSION['pcBehavior']);
      fxHeader($_SESSION['pcVolver'].'?Id=Aceptar');
   } else if ((@$_POST['Boton1'] == 'Salir') OR (@$_POST['Boton2'] == 'Salir')) {
      unset($_SESSION['pcReqInv']);
      unset($_SESSION['pcBehavior']);
      fxHeader($_SESSION['pcVolver']);
   } else if ((@$_POST['Boton1'] == 'Aplicar')) {
      fxAplicar();
   } else if ((@$_POST['Boton2'] == 'Filtrar')) {
      fxInit();
   } else {
      $_SESSION['pcEstado_1']='P';
      $_SESSION['pdFecFin_1'] = date('Y-m-d');
      $_SESSION['pdFecIni_1'] = fxAddDate($_SESSION['pdFecFin_1'], -30);
//      fxInit();
      fxAplicar1();
   }

   // Funciones auxiliares
  function fxInit() {
      $lo = new CIncidentes();
      $llOk = $lo->omInitConsultas();
      if (!$llOk) {
         fxHeader($_SESSION['pcVolver'], fxXmlError($lo->pcError));
      } else {
         $_SESSION['paEstado_1'] = $lo->paEstado;
         fxScreen();
     }
  }
     
  function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni_1']);
      $loSmarty->assign('sdFecFin', $_SESSION['pdFecFin_1']);
      $loSmarty->assign('saEstado', $_SESSION['paEstado_1']);
      $loSmarty->assign('scEstado', $_SESSION['pcEstado_1']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg9008.tpl');
   }
   
   // Funciones auxiliares
   function fxAplicar() {
      $_SESSION['pdFecIni_1'] = $_REQUEST['pdFecIni'];
      $_SESSION['pdFecFin_1'] = $_REQUEST['pdFecFin'];
      $_SESSION['pcEstado_1'] = $_REQUEST['pcEstado'];
      fxAplicar1();
   }
   
   // Funciones auxiliares
   function fxAplicar1() {
      $lo = new CIncidentes();
      $lo->pdFecIni = $_SESSION['pdFecIni_1'];
      $lo->pdFecFin = $_SESSION['pdFecFin_1'];
      $lo->pcEstado = $_SESSION['pcEstado_1'];
	  $llOk = $lo->omConsultar($_SESSION['pcReqInv']);
      if (!$llOk) {
         fxInit();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['paNroInc_1'] = $lo->paNroInc;
         fxScreenD();
     }
   }

   function fxScreenD() {
      global $loSmarty;
      $loSmarty->assign('saNroInc', $_SESSION['paNroInc_1']);
      $loSmarty->assign('scBehavior', $_SESSION['pcBehavior']);
      $loSmarty->display('Plantillas/Seg9008.tpl');
   }
?>