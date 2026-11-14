<?php
   // Registro de Incidentes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2250', 'Mnu0000.php?Id=4001'))
      return;
   if (!empty($_GET['pcNroInc'])) {
      $_SESSION['pcNroInc']=$_GET['pcNroInc'];
	  fxInit();
      fxAplicar();
      return;
   }
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2250.php';
      fxHeader('Seg9008.php');
   } else if (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=4001');
   } else if (@$_POST['Boton2'] == 'Editar') {
      fxEditar();
   } else if (@$_POST['Boton3'] == 'Grabar') {
      fxGrabarSeguimiento();
      fxAplicar();
   } else if (@$_POST['Boton2'] == 'Siguiente') {
      fxHeader('Seg2540.php?pcNroInc='.$_SESSION['pcNroInc']);
      return;
   } else if (@$_POST['Boton3'] == 'Cancelar') {
      fxDumpArray();
      $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('saData', $_SESSION['paData']);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg2250.tpl');
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2250.tpl');
   } else {
     fxInit();
	 fxScreenI();
   }
   
   function fxInit(){
      $lo = new CIncidentes();
      $llOk = $lo->omInitIncidentes();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=4001');
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paAccTom'] = $lo->paAccTom;
         $_SESSION['paCondic'] = $lo->paCondic;
      }
   }
   function fxScreenI(){
      $loSmarty = new Smarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2250.tpl');
   }

   function fxEditar() {
      global $loSmarty;
      if (!isset($_REQUEST['paCheck'])) {
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scNroInc',  $_SESSION['pcNroInc']);
         $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
         $loSmarty->assign('saData', $_SESSION['paData']);
         fxDumpArray();
         $loSmarty->display('Plantillas/Seg2250.tpl');
      } else {
         // Ubica elemento a editar
         $lnSerial = 0;
         $lcId = $_REQUEST['paCheck'][0];
         foreach($_SESSION['paData'] as $i) {
            if ($lcId == $i[0]) {
               $lnSerial = $i[7];
               break;
            }
         }
         $_SESSION['pnSerial'] = $lnSerial;
         $lo = new CIncidentes();
         $lo->pcNroInc = $_SESSION['pcNroInc'];
         $lo->pnSerial = $_SESSION['pnSerial'];
         $llOk = $lo->omEditarSeguimiento();
         if (!$llOk) {
            $lcMessage = fxXmlError($lo->pcError);
            $loSmarty->assign('scBehavior', '1');
            $loSmarty->assign('scNroInc',  $_SESSION['pcNroInc']);
            $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
            $loSmarty->assign('saData', $_SESSION['paData']);
            fxDumpArray();
            $loSmarty->display('Plantillas/Seg2250.tpl');
            fxAlert($lcMessage);
         } else {
            $loSmarty->assign('scBehavior', '2');
            $_SESSION['pcNombre'] = $lo->pcNombre;
            $_SESSION['pcFecha1'] = $lo->pcFecha1;
            $_SESSION['pcDescri'] = $lo->pcDescri;
            $_SESSION['pmComent'] = $lo->pmComent;
            $_SESSION['pcCondic'] = $lo->pcCondic;
            $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
            $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
            $loSmarty->assign('scFecha1', $_SESSION['pcFecha1']);
            $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
            $loSmarty->assign('smCronic', $_SESSION['pmComent']);
            $loSmarty->assign('scCondic', $_SESSION['pcCondic']);
            $loSmarty->assign('saCondic', $_SESSION['paCondic']);
            $loSmarty->display('Plantillas/Seg2250.tpl');
         }
      }
   }
   
   function fxAplicar() {
      global $loSmarty;
      $lo = new CIncidentes();
      if (@$_GET['Id'] == 'Aceptar') 
         $lo->pcNroInc = $_SESSION['pcNroInc'];
      else  
         $lo->pcNroInc = $_REQUEST['pcNroInc'];
      $llOk = $lo->omAplicar7();
      if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2250.tpl');
         fxAlert($lcMessage);
      } else {
         $_SESSION['pcNroInc'] = $lo->pcNroInc;
         $_SESSION['pmDatos']  = $lo->pmDatos;
         $_SESSION['paData']   = $lo->paData;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scNroInc',  $_SESSION['pcNroInc']);
         $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
         $loSmarty->assign('saData', $_SESSION['paData']);
         fxDumpArray();
         $loSmarty->display('Plantillas/Seg2250.tpl');
      }
   }

   function fxDumpArray() {
      global $loSmarty;
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saAccTom', $_SESSION['paAccTom']);
      $loSmarty->assign('saCondic', $_SESSION['paCondic']);
   }

   function fxGrabarSeguimiento() {
      global $loSmarty;
      $lo = new CIncidentes();
      $lo->pnSerial = $_SESSION['pnSerial'];
      $lo->pmComent = $_REQUEST['pmComent'];
      $lo->pcCondic = $_REQUEST['pcCondic'];
      $llOk = $lo->omGrabarSeguimiento();
      if (!$llOk) { // Error
         $lcMessage = fxXmlError($lo->pcError);
		 fxAlert($lcMessage);
	  }
   }
?>