<?php
   // Mantenimiento de procesos de proyectos
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CProcesos.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG6220', 'Mnu0000.php?Id=1000'))
      return;
   // Verifica inicio de sesion
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Aplicar') {
      fxAplicar();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } else if (@$_POST['Boton2'] == 'Nuevo') { 
      fxNuevo();
   } else if (@$_POST['Boton2'] == 'Grabar') { 
      fxGrabar();
   } else if (@$_POST['Boton2'] == 'Eliminar') { 
      fxEliminar();
   } else if (@$_POST['Boton2'] == 'Cancelar') { 
      fxScreen();
   } else {          
      fxInit();
   }
   
   // Funciones Auxiliares
   function fxInit() {
      $lo = new CProcesos();
      $llOk = $lo->omInitProcesos();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.tpl?Id=1000', fxXmlError($lo->pcError));
      } else { 
         $_SESSION['paCodPry'] = $lo->paCodPry;
         fxScreen();
      }
   }

   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('saCodPry', $_SESSION['paCodPry']);
      $loSmarty->assign('scCodPry', $_SESSION['pcCodPry']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg6220.tpl');
   }
   
   function fxAplicar(){
      global $loSmarty;
      $lo = new CProcesos();
      $lo->pcCodPry = @$_REQUEST['pcCodPry'];
      $llOk = $lo->omAplicar();
      if (!$llOk) { // Error
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {                  
         $_SESSION['pcCodPry'] = $lo->pcCodPry;
         $_SESSION['pcDesPry'] = $lo->pcDesPry;
         $_SESSION['paData']   = $lo->paData;
         fxScreenD();
      }
   }

   function fxScreenD() {
      global $loSmarty;
      $loSmarty->assign('scCodPry', $_SESSION['pcCodPry']);
      $loSmarty->assign('scDesPry', $_SESSION['pcDesPry']);
      $loSmarty->assign('saData',   $_SESSION['paData']);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg6220.tpl');
   }
   
   function fxNuevo() {
      $laTmp = $_REQUEST['paData'];
      $i = fxCount($laTmp) + 1;
      $laTmp[] = array($i, '*', '', 1);
      $_SESSION['paData'] = $laTmp;
      fxScreenD();
   }

   function fxEliminar() {
      $laData = $_REQUEST['paData'];
      if (isset($_REQUEST['paCheck']))
         $laData = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
      $_SESSION['paData'] = $laData;
      fxScreenD();
   }

   function fxGrabar() {
      global $loSmarty;
      $_SESSION['paData'] = $_REQUEST['paData'];
      $lo = new CProcesos();
      $lo->pcCodPry = $_SESSION['pcCodPry'];
      $lo->paData   = $_SESSION['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabar();
      if (!$llOk) {
         fxScreenD();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         fxScreen();
         fxAlert('GRABACION CONFORME');
      }
   }
?>