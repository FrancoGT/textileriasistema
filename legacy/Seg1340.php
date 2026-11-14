<?php
   // Registro y mantenimiento de factores criticos de riesgo
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoFactores.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1340', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Nuevo1'] == 'Nuevo') {
      $laTmp = $_REQUEST['paData'];
      $i = fxCount($laTmp) + 1;
      $laTmp[] = array($i, '', '', 'A', '1');
      $_SESSION['paData'] = $laTmp;
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('scDescri', $_SESSION['pcDescri']);
      $loSmarty->assign('saData', $_SESSION['paData']);
      $loSmarty->display('Plantillas/Seg1340.tpl');
   } else if (@$_POST['Grabar1'] == 'Grabar') {
      // graba detalle de Factores
      fxGrabarFactores();
   } else if (@$_POST['Eliminar'] == 'Eliminar') {
      // Elimina fila
      fxEliminar();
   } else if (@$_POST['Nuevo'] == 'Nuevo') {
      // Nueva cabecera de factores
      $_SESSION['plNuevo'] = true;
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->assign('scCodFcr', '');
      $loSmarty->assign('scDescri', '');
      $loSmarty->display('Plantillas/Seg1340.tpl');
   } else if (@$_POST['Editar'] == 'Editar') {
      // Editar cabecera de norma
      if ($_REQUEST['pcTipo']) {
         // Ubica descripcion
         $lcDescri = '*** ERROR ***';
         $lcCodFcr = trim($_REQUEST['pcTipo']);
         foreach($_SESSION['paTipo'] as $i) {
            if (trim($i[0]) == $lcCodFcr) {
               $lcDescri = $i[1];
               break;
            }
         }
         $_SESSION['plNuevo'] = false;
         $loSmarty->assign('scBehavior', '2');
         $loSmarty->assign('scCodFcr', $lcCodFcr);
         $loSmarty->assign('scDescri', $lcDescri);
         $loSmarty->display('Plantillas/Seg1340.tpl');
      } else {
         fxInit();
      }
  } else if (@$_POST['Aplicar'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') { // NO ACTIVO
      $lo = new CMtoFactores();
      $lo->pcCodFcr = @$_REQUEST['pcCodFcr'];
      if (@$_GET['Id'] == 'Aceptar')
         $lo->pcCodFcr = $_SESSION['pcCodFcr'];
      $llOk = $lo->omAplicarFactores();
      if (!$llOk) { // Error
         $loSmarty->assign('scAviso', $lo->pcError);
         $loSmarty->assign('scVolver', 'Seg1340.php');
         $loSmarty->display('Plantillas/Sis1000.tpl');
      } else {
         $_SESSION['pcCodFcr'] = $lo->pcCodFcr;
         $_SESSION['plNuevo'] = false;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scCodFcr', $lo->pcCodFcr);
         $loSmarty->assign('scDescri', $lo->pcDescri);
         $loSmarty->assign('scEstado', $lo->pcEstado);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->display('Plantillas/Seg1340.tpl');
      }
   } else if (@$_POST['Aplicar1'] == 'Aplicar') {
      $lo = new CMtoFactores();
      $lo->pcTipo = @$_REQUEST['pcTipo'];
      $_SESSION['pcTipo'] = $lo->pcTipo;
      $llOk = $lo->omCargarFactorespPorTipo();
      if (!$llOk) { // Error
         $loSmarty->assign('scAviso', $lo->pcError);
         $loSmarty->assign('scVolver', 'Seg1340.php');
         $loSmarty->display('Plantillas/Sis1000.tpl');
      } else {
         $_SESSION['pcDescri'] = $lo->pcDescri;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scDescri', $lo->pcDescri);
         $loSmarty->assign('saData', $lo->paCodFcr);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->display('Plantillas/Seg1340.tpl');
      }
   } else if (@$_POST['Grabar'] == 'Grabar') {
      $lo = new CMtoFactores();
      $lo->plNuevo = $_SESSION['plNuevo'];
      $lo->pcCodFcr = $_REQUEST['pcCodFcr'];
      $lo->pcDescri = $_REQUEST['pcDescri'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarCabecera();
      if (!$llOk) { // Error
         $lcMessage = fxXmlError($lo->pcError);
      } else {
         $lcMessage = 'GRABACION CONFORME';
      }
      fxInit();
      fxAlert($lcMessage);
   } else if (@$_POST['Grabar1'] == 'Grabar1') {
      $llNuevo = $_SESSION['plNuevo'];
      $lo = new CMtoFactores();
      $lo->plNuevo = $llNuevo;
      $lo->pcCodFcr = $_REQUEST['pcCodFcr'];
      $lo->pcDescri = $_REQUEST['pcDescri'];
      $lo->pcEstado = $_REQUEST['pcEstado'];
      $lo->pcTipo = $_SESSION['pcTipo'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarFactores();
      if (!$llOk) { // Error
         $loSmarty->assign('scAviso', $lo->pcError);
      } else {
         $loSmarty->assign('scAviso', 'GRABACION CONFORME');
      }
      $loSmarty->assign('scVolver', 'Seg1340.php');
      $loSmarty->display('Plantillas/Sis1000.tpl');
   } else if (@$_POST['Buscar'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg1340';
      fxHeader('Seg9004.php');
   } else {
      fxInit();
   }

   // Funciones auxiliares 
   function fxInit() {
      global $loSmarty;
      $lo = new CMtoFactores();
      $llOk = $lo->omInit();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else {
         // Guarda estados de proyectos
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paTipo'] = $lo->paTipo;
         $loSmarty->assign('saTipo', $_SESSION['paTipo']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1340.tpl');
      }
   }
   
   function fxGrabarFactores() {
      global $loSmarty;
      $lo = new CMtoFactores();
      $lo->pcCodFcr = $_SESSION['pcTipo'];
      $lo->paCodFcr = $_REQUEST['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarFactores();
      if (!$llOk) { // Error
         $lcMessage = fxXmlError($lo->pcError);
      } else {
         $lcMessage = 'GRABACION CONFORME';
      }
      $loSmarty->assign('saTipo', $_SESSION['paTipo']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg1340.tpl');
      fxAlert($lcMessage);
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
      $loSmarty->display('Plantillas/Seg1340.tpl');
   }
?>