<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoEmpleados.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter('public');
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1290', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if ((@$_POST['Boton1'] == 'Aplicar') or (@$_GET['Id'] == 'Aceptar')) {
      fxAplicar();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=2000');
   } else if (@$_POST['Boton2'] == 'Nuevo') {
      fxNuevaSancion();
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxInit();
   } else if (@$_POST['Boton3'] == 'Eliminar') {
      $laData = $_REQUEST['paData'];
      if (isset($_REQUEST['paData']) and isset($_REQUEST['paCheck']))
         $laData = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
      $loSmarty->assign('scNroDni', $_SESSION['pcNroDni']);
      $loSmarty->assign('smObserv', $_SESSION['pmObserv']);
      $loSmarty->assign('saData',   $laData);
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('scEstado', $_REQUEST['pcEstado']);
      $loSmarty->assign('saCodSan', $_SESSION['paCodSan']);
      $loSmarty->assign('scCodSan', $_SESSION['pcCodSan']);
      $loSmarty->assign('sdFecha',  $_SESSION['pdFecha']);
      $loSmarty->assign('saCodNor', $_SESSION['paCodNor']);
      $loSmarty->assign('scBehavior', '2'); 
      $loSmarty->display('Plantillas/Seg1290.tpl');
   } else if (@$_POST['Boton3'] == 'Nuevo') {
      fxNuevo();
   } else if (@$_POST['Boton2'] == 'Cancelar' or @$_POST['Boton4'] == 'Cancelar') {
      fxInit();
   } else if (@$_POST['Boton2'] == 'Editar') {
      fxEditar();
   } else if (@$_POST['Boton4'] == 'Grabar') {
      fxGrabar();
   } else {
      $lo= new CMtoEmpleados();
      $llOk = $lo->omInitPersonalSancion();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=2000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paCodNor'] = $lo->paCodNor;
         $_SESSION['paCodSan'] = $lo->paCodSan;
         fxInit();
      }
   }

   // Funciones auxiliares
   function fxInit() {
      global $loSmarty;
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saCodSan', $_SESSION['paCodSan']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg1290.tpl');
   }
   
   function fxAplicar($p_cCodPer = null) {
      global $loSmarty;      
      $lo = new CMtoEmpleados();
      $lo->pcCodPer = $_REQUEST['pcCodPer'];
      if (isset($p_cCodPer)) {
         $lo->pcCodPer = $p_cCodPer;
      }
      $llOk = $lo->omMtoPersonalSancion();
      if (!$llOk) { // Error
         fxInit();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['pcCodPer'] = $lo->pcCodPer;
         $_SESSION['pcNombre'] = trim($lo->pcNombre);
         $_SESSION['pcNroDni'] = $lo->pcNroDni;
         $_SESSION['pmObserv'] = $lo->pmObserv;
         $_SESSION['paData1'] = $lo->paData;
         $loSmarty->assign('scBehavior', '1'); 
         $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
         $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
         $loSmarty->assign('smObserv', $_SESSION['pmObserv']);
         $loSmarty->assign('scNroDni', $_SESSION['pcNroDni']);
         $loSmarty->assign('scCodSan', $lo->pcCodSan);
         $loSmarty->assign('saData', $_SESSION['paData1']);
         $loSmarty->display('Plantillas/Seg1290.tpl');
      }
   }

   function fxNuevaSancion() {
      global $loSmarty;   
      $laData = null;
      $_SESSION['plNuevo'] = true;
      $loSmarty->assign('scBehavior', '2');      
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
      $loSmarty->assign('scNroDni', $_SESSION['pcNroDni']);
      $loSmarty->assign('smObserv', $_SESSION['pmObserv']);
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('scEstado', $_REQUEST['pcEstado']);
      $loSmarty->assign('saCodNor', $_SESSION['paCodNor']);
      $loSmarty->assign('sdFecha',  $_REQUEST['pdFecha']);
      $loSmarty->assign('saCodSan', $_SESSION['paCodSan']);
      $loSmarty->assign('scCodSan', $_REQUEST['pcCodSan']);
      $loSmarty->assign('saData',   $laData);
      $loSmarty->display('Plantillas/Seg1290.tpl');
   }
   
   function fxNuevo() {
      global $loSmarty;
      $lcCodNor = $_REQUEST['pcCodNor'];
      $lcDescri = '*** ERROR ***';
      foreach($_SESSION['paCodNor'] as $laFila) {
         if ($lcCodNor == $laFila[0]) {
            $lcDescri = $laFila[1];
            break;
         }
      }
      $laData = null;
      if (isset($_REQUEST['paData'])) {
         $laData = $_REQUEST['paData'];
      }
      $i = count($laData) + 1;
      $laData[$i] = array($i, $lcCodNor, $lcDescri);
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
      $loSmarty->assign('scNroDni', $_SESSION['pcNroDni']);
      $loSmarty->assign('smObserv', $_SESSION['pmObserv']);
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('scEstado', $_REQUEST['pcEstado']);
      $loSmarty->assign('saCodNor', $_SESSION['paCodNor']);
      $loSmarty->assign('sdFecha',  $_REQUEST['pdFecha']);
      $loSmarty->assign('saCodSan', $_SESSION['paCodSan']);
      $loSmarty->assign('scCodSan', $_REQUEST['pcCodSan']);
      $loSmarty->assign('saData',   $laData);
      $loSmarty->assign('scBehavior', '2');      
      $loSmarty->display('Plantillas/Seg1290.tpl');
   }
   
   function fxGrabar() {
      global $loSmarty;
      $_SESSION['paData']= $_REQUEST['paData'];
      $lo = new CMtoEmpleados();
      $lo->pcCodigo = $_SESSION['pcCodigo'];
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $lo->pcCodSan = $_REQUEST['pcCodSan'];
      $lo->pdFecha  = $_REQUEST['pdFecha'];
      $lo->pmObserv = $_REQUEST['pmObserv'];
      $lo->pcEstado = $_REQUEST['pcEstado'];
      $lo->paData   = $_REQUEST['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarPersonalSancion();
      if (!$llOk) { // Error
         $loSmarty->assign('smObserv', $_SESSION['pmObserv']);
         $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
         $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
         $loSmarty->assign('scNroDni', $_SESSION['pcNroDni']);
         $loSmarty->assign('saCodSan', $_SESSION['paCodSan']);
         $loSmarty->assign('saCodNor', $_SESSION['paCodNor']);
         $loSmarty->assign('scCodSan', $_SESSION['pcCodSan']);
         $loSmarty->assign('scEstado', $_SESSION['pcEstado']);
         $loSmarty->assign('sdFecha',  $_REQUEST['pdFecha']);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->assign('saData',   $_SESSION['paData']);
         $loSmarty->assign('scBehavior', '2');
         $loSmarty->display('Plantillas/Seg1290.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         fxAlert('GRABACION CONFORME');
         fxAplicar($_SESSION['pcCodPer']);
      }
   }
   
   function fxEditar() {
      global $loSmarty;
      if (!isset($_REQUEST['paCheck'])) {
         $loSmarty->assign('scBehavior', '1'); 
         $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
         $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
         $loSmarty->assign('scNroDni', $_SESSION['pcNroDni']);
         $loSmarty->assign('smObserv', $_SESSION['pmObserv']);
         $loSmarty->assign('saData', $_REQUEST['paData']);
         $loSmarty->display('Plantillas/Seg1290.tpl');
     } else {
         $lcCodigo = fxBuscarFila($_REQUEST['paData'], $_REQUEST['paCheck'], 1);
         $lo = new CMtoEmpleados();
         $lo->pcCodigo = $lcCodigo;
         $llOk = $lo->omEditarSancion();
         if (!$llOk) {
            fxInit();
            fxAlert(fxXmlError($lo->pcError));
         } else {
            $_SESSION['plNuevo']  = false;
            $_SESSION['pcCodigo'] = $lcCodigo;
            $_SESSION['pcCodSan'] = $lo->pcCodSan;
            $_SESSION['pcEstado'] = $lo->pcEstado;
            $_SESSION['pdFecha']  = $lo->pdFecha;
            $_SESSION['pmObserv'] = $lo->pmObserv;
            $_SESSION['paData']   = $lo->paData;
            $loSmarty->assign('scBehavior', '2');
            $loSmarty->assign('smObserv', $_SESSION['pmObserv']);
            $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
            $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
            $loSmarty->assign('scNroDni', $_SESSION['pcNroDni']);
            $loSmarty->assign('saCodSan', $_SESSION['paCodSan']);
            $loSmarty->assign('scCodSan', $_SESSION['pcCodSan']);
            $loSmarty->assign('saCodNor', $_SESSION['paCodNor']);
            $loSmarty->assign('scEstado', $_SESSION['pcEstado']);
            $loSmarty->assign('saEstado', $_SESSION['paEstado']);
            $loSmarty->assign('saData',   $_SESSION['paData']);
            $loSmarty->assign('sdFecha',  $_SESSION['pdFecha']);
            $loSmarty->display('Plantillas/Seg1290.tpl');
         }
      }
   }
?>