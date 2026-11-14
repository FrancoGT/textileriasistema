<?php
   // Registro y mantenimiento de personal operativo
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoEmpleados.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP1000', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Nuevo') {
      $_SESSION['plNuevo'] = true;
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('saSexo', $_SESSION['paSexo']);
      $loSmarty->assign('saEstCiv', $_SESSION['paEstCiv']);
      $loSmarty->display('Plantillas/ERP1000.tpl');
   } else if (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      $lo = new CMtoEmpleados();
      $lo->pcCodPer = @$_REQUEST['pcCodPer'];
      if (@$_GET['Id'] == 'Aceptar')
         $lo->pcCodPer = $_SESSION['pcCodPer'];
      $llOk = $lo->omAplicar();
      if (!$llOk) { // Error
         fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
         $_SESSION['pcCodPer'] = $lo->pcCodPer;
         $_SESSION['plNuevo'] = false;
         $_SESSION['pcPuesto'] = $lo->paData['CDESPUE'];
         $_SESSION['pcCargo'] =  $lo->paData['CDESCAR'];
         $_SESSION['pcCarDis'] = $lo->paData['CDESDIS'];
         $_SESSION['pcSector'] = $lo->paData['CDESSEC'];
         $_SESSION['pcDesPry'] = $lo->paData['CDESPRY'];
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scCodPer', $lo->pcCodPer);
         $loSmarty->assign('scNombre', $lo->paData['CNOMBRE']);
         $loSmarty->assign('scDirecc', $lo->paData['CDIRECC']);
         $loSmarty->assign('scEmail',  $lo->paData['CEMAIL']);
         $loSmarty->assign('scNroDni', $lo->paData['CNRODNI']);
         $loSmarty->assign('scSexo',   $lo->paData['CSEXO']);
         $loSmarty->assign('saSexo',   $_SESSION['paSexo']);
         $loSmarty->assign('scEstCiv', $lo->paData['CESTCIV']);
         $loSmarty->assign('saEstCiv', $_SESSION['paEstCiv']);
         $loSmarty->assign('scEstado', $lo->paData['CESTADO']);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->assign('scCodEmp', $lo->paData['CDESCRI']);
         $loSmarty->assign('scPuesto', $lo->paData['CDESPUE']);
         $loSmarty->assign('scCargo',  $lo->paData['CDESCAR']);
         $loSmarty->assign('scCarDis', $lo->paData['CDESDIS']);
         $loSmarty->assign('scSector', $lo->paData['CDESSEC']);
         $loSmarty->assign('scDesPry', $lo->paData['CDESPRY']);
         $loSmarty->display('Plantillas/ERP1000.tpl');
      }
   } else if (@$_POST['Boton2'] == 'Grabar') {
      fxGrabar();
   } else if (@$_POST['Boton1'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'ERP1000';
      fxHeader('Seg9005.php');
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxScreen();
   } else {
      $lo = new CMtoEmpleados();
      $llOk = $lo->omCargarMtoEmpleados();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else { 
         $_SESSION['paEstCiv'] = $lo->paEstCiv;
         $_SESSION['paSexo'] = $lo->paSexo;
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paCodPer'] = $lo->paCodPer;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->display('Plantillas/ERP1000.tpl');
   }
   
   function fxGrabar() {
      global $loSmarty;      
      $lo = new CMtoEmpleados();
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcCodPer = $_REQUEST['pcCodPer'];
      $lo->pcNombre = $_REQUEST['pcNombre'];
      $lo->pcDirecc = $_REQUEST['pcDirecc'];
      $lo->pcEmail =  $_REQUEST['pcEmail'];
      $lo->pcNroDni = $_REQUEST['pcNroDni'];
      $lo->pcEstado = $_REQUEST['pcEstado'];
      $lo->pcSexo   = $_REQUEST['pcSexo'];
      $lo->pcEstCiv = $_REQUEST['pcEstCiv'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarEmpleado();
      if (!$llOk) {
         $lcMensaje = fxXmlError($lo->pcError);
         $loSmarty->assign('saSexo',   $_SESSION['paSexo']);
         $loSmarty->assign('saEstCiv', $_SESSION['paEstCiv']);
         $loSmarty->assign('saEstado', $_SESSION['paEstado']);
         $loSmarty->assign('scCodPer', $lo->pcCodPer);
         $loSmarty->assign('scNombre', $lo->pcNombre);
         $loSmarty->assign('scDirecc', $lo->pcDirecc);
         $loSmarty->assign('scEmail',  $lo->pcEmail);
         $loSmarty->assign('scNroDni', $lo->pcNroDni);
         $loSmarty->assign('scSexo',   $lo->pcSexo);
         $loSmarty->assign('scEstCiv', $lo->pcEstCiv);
         $loSmarty->assign('scEstado', $lo->pcEstado);
         $loSmarty->assign('scCodEmp', $lo->paData['CDESCRI']);
         $loSmarty->assign('scPuesto', $_SESSION['pcPuesto']);
         $loSmarty->assign('scCargo',  $_SESSION['pcCargo']);
         $loSmarty->assign('scCarDis', $_SESSION['pcCarDis']);
         $loSmarty->assign('scSector', $_SESSION['pcSector']);
         $loSmarty->assign('scDesPry', $_SESSION['pcDesPry']);
         if ($_SESSION['plNuevo'])
            $loSmarty->assign('scBehavior', '2');
         else
            $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/ERP1000.tpl');
         fxAlert($lcMensaje);
      } else {
         if ($lo->plNuevo) {
            $_SESSION['paCodPer'] = $lo->paCodPer;
         }
          $lo = new CMtoEmpleados();
          $lo->omCargarMtoEmpleados();
          $_SESSION['paCodPer'] = $lo->paCodPer;
         fxScreen();
         fxAlert('GRABACION CONFORME');
      }
   }
?>