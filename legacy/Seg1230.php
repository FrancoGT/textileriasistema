<?php
   // Registro y mantenimiento de cursos
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoCursos.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1230', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif (@$_POST['Boton1'] == 'Nuevo') {
      $_SESSION['pcCodCur'] = '';
      $_SESSION['plNuevo'] = true;
      fxArray();
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg1230.tpl');
   } elseif (@$_POST['Boton2'] == 'Nuevo') {
      $lcDescri = '*';
      $lcCodNor = $_REQUEST['pcCodNor'];
      foreach ($_SESSION['paCodNor'] as $laFila) {
         if ($laFila[0] == $lcCodNor) {
            $lcDescri = $laFila[1];
            break;
         }
      }
      $laData = null;
      if (isset($_REQUEST['paData'])) {
         $laData = $_REQUEST['paData'];
      }
      $j = fxCount($laData);
      $laData[] = array($j + 1, $lcCodNor, $lcDescri, '');
      $_SESSION['paData'] = $laData;
      fxArray();
      $loSmarty->assign('scDescri', $_REQUEST['pcDescri']);
      $loSmarty->assign('scEstado', $_REQUEST['pcEstado']);
      $loSmarty->assign('scTipo',   $_REQUEST['pcTipo']);
      $loSmarty->assign('scNomDoc', $_REQUEST['pcNomDoc']);
      $loSmarty->assign('sdFecha',  $_REQUEST['pdFecha']);
      $loSmarty->assign('scTiempo', $_REQUEST['pcTiempo']);
      $loSmarty->assign('smDatos',  $_REQUEST['pmDatos']);
      $loSmarty->assign('saData',   $_SESSION['paData']);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg1230.tpl');
   } elseif (@$_POST['Boton2'] == 'Eliminar') {
      $laData = $_REQUEST['paData'];
      if (isset($_REQUEST['paCheck']))
         $laData = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
      $_SESSION['paData'] = $laData;
      fxArray();
      $loSmarty->assign('scEstado', '');
      $loSmarty->assign('scDescri', $_REQUEST['pcDescri']);
      $loSmarty->assign('scEstado', $_REQUEST['pcEstado']);
      $loSmarty->assign('scTipo', $_REQUEST['pcTipo']);
      $loSmarty->assign('scNomDoc', $_REQUEST['pcNomDoc']);
      $loSmarty->assign('sdFecha',  $_REQUEST['pdFecha']);
      $loSmarty->assign('scTiempo', $_REQUEST['pcTiempo']);
      $loSmarty->assign('smDatos',  $_REQUEST['pmDatos']);
      $loSmarty->assign('saData',   $_SESSION['paData']);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg1230.tpl');
   } elseif (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      $_SESSION['plNuevo'] = false;
      $lo = new CMtoCursos();
      $lo->pcCodCur = @$_REQUEST['pcCodCur'];
      if (@$_GET['Id'] == 'Aceptar')
         $lo->pcCodCur = $_SESSION['pcCodCur'];
      $llOk = $lo->omAplicarCursos();
      if (!$llOk) { // Error
         fxAlert(fxXmlError($lo->pcError));
         fxInit();
      } else {
         $_SESSION['pcCodCur'] = $lo->pcCodCur;
         fxArray();
         $loSmarty->assign('scCodCur', $lo->pcCodCur);
         $loSmarty->assign('scDescri', $lo->pcDescri);
         $loSmarty->assign('scEstado', $lo->pcEstado);
         $loSmarty->assign('scTipo',   $lo->pcTipo);
         $loSmarty->assign('scNomDoc', $lo->pcNomDoc);
         $loSmarty->assign('sdFecha',  $lo->pdFecha);
         $loSmarty->assign('scTiempo', $lo->pcTiempo);
         $loSmarty->assign('smDatos',  $lo->pmDatos);
         $loSmarty->assign('saData',   $lo->paData);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg1230.tpl');
      }
   } elseif (@$_POST['Boton3'] == 'Grabar') {
      $lo = new CMtoCursos();
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcDescri = $_REQUEST['pcDescri'];
      $lo->pcEstado = $_REQUEST['pcEstado'];
      $lo->pcTipo   = $_REQUEST['pcTipo'];
      $lo->pcNomDoc = $_REQUEST['pcNomDoc'];
      $lo->pdFecha  = $_REQUEST['pdFecha'];
      $lo->pcTiempo = $_REQUEST['pcTiempo'];
      $lo->pmDatos  = $_REQUEST['pmDatos'];
      $lo->paData   = $_REQUEST['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->pcCodCur = $_SESSION['pcCodCur'];   // ???OJOFPM
      $llOk = $lo->omGrabarCursos();
      if (!$llOk) { // Error
         $lcMessage = fxXmlError($lo->pcError);
         fxArray();
         $loSmarty->assign('scDescri', $lo->pcDescri);
         $loSmarty->assign('scEstado', $lo->pcEstado);
         $loSmarty->assign('scTipo', $lo->pcTipo);
         $loSmarty->assign('scNomDoc', $lo->pcNomDoc);
         $loSmarty->assign('sdFecha',  $lo->pdFecha);
         $loSmarty->assign('scTiempo', $lo->pcTiempo);
         $loSmarty->assign('smDatos',  $lo->pmDatos);
         $loSmarty->assign('scCodCur', $_SESSION['pcCodCur']);
         $loSmarty->assign('saData',   $lo->paData);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg1230.tpl');
         fxAlert($lcMessage);
      } else {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1230.tpl');
         fxAlert('GRABACION CONFORME');
      }
   } elseif (@$_POST['Boton1'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg1230.php';
      fxHeader('Seg9003.php');
   } elseif (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } elseif (@$_POST['Boton3'] == 'Cancelar') {
      fxInit();
   } else {
      $lo = new CMtoCursos();
      $llOk = $lo->omCargarMtoCursos();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paTipo'] = $lo->paTipo;
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paCodNor'] = $lo->paCodNor;
         fxInit();
      }
   }
   
   function fxInit() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('scCodCur', '');
      $loSmarty->display('Plantillas/Seg1230.tpl');
   }
   
   function fxArray() {
      global $loSmarty;
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('saTipo',   $_SESSION['paTipo']);
      $loSmarty->assign('saCodNor', $_SESSION['paCodNor']);
   }
?>