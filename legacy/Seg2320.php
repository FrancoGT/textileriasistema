<?php
   // Seguimiento ACR
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2320', 'Mnu0000.php?Id=4100'))
      return;
   if (!empty($_GET['pcNroInc'])) {
      $_SESSION['pcNroInc']=$_GET['pcNroInc'];
      fxInit();
      fxAplicar();
      return;
   }
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Buscar'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2320.php';
      fxHeader('Seg9008.php?Id=Req');
   } else if (@$_POST['Aplicar'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if (@$_POST['Editar'] == 'Editar') {
      fxEditar();
   } else if (@$_POST['Grabar1'] == 'Grabar') {
      fxGrabarSeguimiento();
      fxAplicar();
   } else if (@$_POST['Cancelar1'] == 'Cancelar') {
      fxScreen();
   } else if (@$_POST['Grabar'] == 'Grabar') {
      fxGrabar();
   } else {
      fxInit();
	  $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2320.tpl');
   }
   function fxInit(){
      $lo = new CIncidentes();
      $llOk = $lo->omInitIncidentes();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=4100', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paAccTom'] = $lo->paAccTom;
         $_SESSION['paCondic'] = $lo->paCondic;
	  }
   }
   // Funciones auxiliares
   function fxGrabar(){
      global $loSmarty;
      $lcEficaz = (isset($_REQUEST['pcEficaz'])) ? 'S' : 'N';
      $lo = new CIncidentes();
      $lo->pcNroInc = $_REQUEST['pcNroInc'];
      $lo->pmComent = $_REQUEST['pmComent'];
      $lo->pcEficaz = $lcEficaz;
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabar1();
      if (!$llOk) { // Error
        fxScreen();
		fxAlert(fxXmlError($lo->pcError));
      } else {
        fxHeader('Seg2350.php?pcNroInc='.$_SESSION['pcNroInc']);
        return;
	  }
   }
   
   function fxEditar() {
      global $loSmarty;
      if (!isset($_REQUEST['paCheck'])) {
         fxScreen();
      } else {
         // Ubica elemento a editar
         $lnSerial = 0;
         foreach($_SESSION['paData'] as $i) {
            if ($_REQUEST['paCheck'][0] == $i[0]) {
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
            fxScreen();
            fxAlert(fxXmlError($lo->pcError));
         } else {
            $_SESSION['pcNombre'] = $lo->pcNombre;
            $_SESSION['pcFecha1'] = $lo->pcFecha1;
            $_SESSION['pcDescri'] = $lo->pcDescri;
            $_SESSION['pmCronic'] = $lo->pmComent;
            $_SESSION['pcCondic'] = $lo->pcCondic;
            $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
            $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
            $loSmarty->assign('scFecha1', $_SESSION['pcFecha1']);
            $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
            $loSmarty->assign('smCronic', $_SESSION['pmCronic']);
            $loSmarty->assign('scCondic', $_SESSION['pcCondic']);
            $loSmarty->assign('saCondic', $_SESSION['paCondic']);
            $loSmarty->assign('scBehavior', '2');
            $loSmarty->display('Plantillas/Seg2320.tpl');
         }
      }
   }
   
   function fxAplicar() {
      global $loSmarty;
      $lo = new CIncidentes();
      if (@$_GET['Id'] == 'Aceptar' or !empty($_GET['pcNroInc'])) 
         $lo->pcNroInc = $_SESSION['pcNroInc'];
      else  
         $lo->pcNroInc = $_REQUEST['pcNroInc'];
      $llOk = $lo->omAplicar6();
      if (!$llOk) {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2320.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['pcNroInc'] = $lo->pcNroInc;
         $_SESSION['pcEficaz'] = $lo->pcEficaz;
         $_SESSION['pmComent'] = $lo->pmComent;
         $_SESSION['pmDatos']  = $lo->pmDatos;
         $_SESSION['paData']   = $lo->paData;
         fxScreen();
      }
   }

   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scEficaz', $_SESSION['pcEficaz']);
      $loSmarty->assign('smComent', $_SESSION['pmComent']);
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saAccTom', $_SESSION['paAccTom']);
      $loSmarty->assign('saCondic', $_SESSION['paCondic']);
      $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('saData',   $_SESSION['paData']);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg2320.tpl');
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
      } else {
         $lcMessage = 'GRABACION CONFORME';
      }
      fxAplicar();
      fxAlert($lcMessage);
   }
?>