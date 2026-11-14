<?php
   // Registro y mantenimiento de empresas
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoEmpresas.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1220', 'Mnu0000.php?Id=1000'))
      return;
   // Verifica inicio de sesion
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Nuevo'] == 'Nuevo') {
      $_SESSION['pcCodCur'] = '';
      $_SESSION['plNuevo'] = true;
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->assign('scEstado', '');
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->display('Plantillas/Seg1220.tpl');
   } else if (@$_POST['Aplicar'] == 'Aplicar') {
      $lo = new CMtoEmpresas();
      $lo->pcCodEmp = @$_REQUEST['paCheck'][0];
      $llOk = $lo->omAplicar1();
      if (!$llOk) { // Error
         $loSmarty->assign('scAviso', $lo->pcError);
         $loSmarty->assign('scVolver', 'Seg1220.php');
         $loSmarty->display('Plantillas/Sis1000.tpl');
      } else {
         // Guarda codigo de la empresa
         $_SESSION['pcCodEmp'] = $lo->pcCodEmp;
         $_SESSION['plNuevo'] = false;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scCodEmp', $lo->pcCodEmp);
         $loSmarty->assign('scDescri', $lo->pcDescri);
         $loSmarty->assign('scEstado', $lo->pcEstado);
         $loSmarty->assign('smDatos', $lo->pmDatos);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->display('Plantillas/Seg1220.tpl');
      }
   } else if (@$_POST['Grabar'] == 'Grabar') {
      $lo = new CMtoEmpresas();
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcCodEmp = $_REQUEST['pcCodEmp'];
      $lo->pcDescri = $_REQUEST['pcDescri'];
      $lo->pcEstado = $_REQUEST['pcEstado'];
      $lo->pmDatos  = $_REQUEST['pmDatos'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarEmpresa();
      if (!$llOk) { // Error
         $lcMensaje = fxXmlError($lo->pcError);
         if ($lo->plNuevo)
            $loSmarty->assign('scBehavior', '2');
         else
            $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scCodEmp', $lo->pcCodEmp);
         $loSmarty->assign('scDescri', $lo->pcDescri);
         $loSmarty->assign('scEstado', $lo->pcEstado);
         $loSmarty->assign('smDatos', $lo->pmDatos);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->display('Plantillas/Seg1220.tpl');
         fxAlert($lcMensaje);
      } else {
         fxInit();
         fxAlert('GRABACION CONFORME');
      }
   } else {
      fxInit();
   }

   function fxInit() {
      global $loSmarty;
      $lo = new CMtoEmpresas();
      $llOk = $lo->omCargarMtoEmpresas();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=4000', fxXmlError($lo->pcError));
      } else { 
         $_SESSION['paData']   = $lo->paData;
         $_SESSION['paEstado'] = $lo->paEstado;
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->assign('saData', $_SESSION['paData']);
         $loSmarty->display('Plantillas/Seg1220.tpl');
      }
   }
?>