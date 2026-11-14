<?php
   // Registro y mantenimiento de proyectos
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoProyectos.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1210', 'Mnu0000.php?Id=1000'))
      return;
   // Verifica inicio de sesion
   if (!fxInitSession()) {
       fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Nuevo'] == 'Nuevo') {
      $_SESSION['pcCodPry'] = '';
      $_SESSION['plNuevo'] = true;
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('scEstado', '');
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->display('Plantillas/Seg1210.tpl');
   } else if (@$_POST['Aplicar'] == 'Aplicar') {
      $lo = new CMtoProyectos();
      $lo->pcCodPry = @$_REQUEST['paCheck'][0];
      $llOk = $lo->omAplicarProyecto();
      if (!$llOk) { // Error
         $lcMensaje = fxXmlError($lo->pcError);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->assign('saData', $_SESSION['paData']);
         $loSmarty->display('Plantillas/Seg1210.tpl');
         fxAlert($lcMensaje);
      } else {
         // Guarda codigo de proyecto
         $_SESSION['pcCodPry'] = $lo->pcCodPry;
         $_SESSION['plNuevo'] = false;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scCodPry', $lo->pcCodPry);
         $loSmarty->assign('scDescri', $lo->pcDescri);
         $loSmarty->assign('sdFecIni', $lo->pdFecIni);
         $loSmarty->assign('scEstado', $lo->pcEstado);
         $loSmarty->assign('scCodPer', $lo->pcCodPer);
         $loSmarty->assign('smDatos',  $lo->pmDatos);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
         $loSmarty->display('Plantillas/Seg1210.tpl');
      }
   } else if (@$_POST['Grabar'] == 'Grabar') {
      $_SESSION['pcDescri'] =  $_REQUEST['pcDescri'];
      $_SESSION['pdFecIni'] =  $_REQUEST['pdFecIni'];
      $_SESSION['pcEstado'] =  $_REQUEST['pcEstado'];
      $_SESSION['pcCodPer'] =  $_REQUEST['pcCodPer'];
      $_SESSION['pmDatos']  =  $_REQUEST['pmDatos'];
      $lo = new CMtoProyectos();
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcCodPry = $_REQUEST['pcCodPry'];
      $lo->pdFecIni = $_SESSION['pdFecIni'];
      $lo->pcDescri = $_SESSION['pcDescri'];
      $lo->pcEstado = $_SESSION['pcEstado'];
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $lo->pmDatos  = $_SESSION['pmDatos'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarProyecto();
      if (!$llOk) { 
         $lcMensaje = fxXmlError($lo->pcError);
         if ($_SESSION['plNuevo']){
            $loSmarty->assign('scBehavior', '2');
         } else {
            $loSmarty->assign('scBehavior', '1');
            $loSmarty->assign('scCodPry', $_SESSION['pcCodPry']);
         }
         $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
         $loSmarty->assign('sdFecIni', $_SESSION['pdFecIni']);
         $loSmarty->assign('scEstado', $_SESSION['pcEstado']);
         $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
         $loSmarty->display('Plantillas/Seg1210.tpl');
         fxAlert($lcMensaje);
      } else {
         fxInit();
      }
   } else {
      fxInit();
   }
   
   function fxInit() {
      global $loSmarty;
      $lo = new CMtoProyectos();
      $llOk = $lo->omCargarMtoProyectos();
      if (!$llOk) { // Error
         $loSmarty->assign('scAviso', $lo->pcError);
         $loSmarty->assign('scVolver', 'Seg1210.php');
         $loSmarty->display('Plantillas/Sis1000.tpl');
      } else { 
         // Guarda estados de proyectos
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paData'] = $lo->paData;
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->assign('saData', $_SESSION['paData']);
         $loSmarty->display('Plantillas/Seg1210.tpl');
      }
   }
?>