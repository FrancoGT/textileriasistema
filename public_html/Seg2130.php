<?php
   // Registro de evaluaciones de velocidad por radar
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CEvaluacion.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2130', 'Mnu0000.php?Id=3000'))
      return;
   $lcEvaTip = 'R';
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif (@$_POST['Boton1'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2130';
      $_SESSION['pcEvaTip'] = $lcEvaTip;
      fxHeader('Seg9006.php');
   } elseif (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      $_SESSION['plNuevo'] = false;
      $lo = new CEvaluacion();
      if (@$_GET['Id'] == 'Aceptar') 
         $lo->pcNroEva = $_SESSION['pcNroEva'];
      else  
         $lo->pcNroEva = $lcEvaTip.$_REQUEST['pcNroEva'];
      $llOk = $lo->omAplicarRadar();
      if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
         fxInit();
         fxAlert($lcMessage);
      } else {
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scNroEva', $lo->pcNroEva);
         $loSmarty->assign('saDataP',  $lo->paDataP);
         $loSmarty->assign('scFecEva', $lo->pcFecEva);
         $loSmarty->assign('scHorEva', $lo->pcHorEva);
         $loSmarty->assign('scHorSer', $lo->pcHorSer);
         $loSmarty->assign('scKilome', $lo->pcKilome);
         $loSmarty->assign('scKilomf', $lo->pcKilomf);
         $loSmarty->assign('scTren',   $lo->pcTren);
         $loSmarty->assign('scEstaci', $lo->pcEstaci);
         $loSmarty->assign('scEstacf', $lo->pcEstacf);
         $loSmarty->assign('scSubDiv', $lo->pcSubDiv);
         $loSmarty->assign('scResult', $lo->pcResult);
         $loSmarty->assign('scTipVag', $lo->pcTipVag);
         $loSmarty->assign('scSuperv', $lo->pcSuperv);
         $loSmarty->assign('scSuper1', $lo->pcSuper1);
         $loSmarty->assign('scCodUn1', $lo->pcCodUn1);
         $loSmarty->assign('scCodUn2', $lo->pcCodUn2);
         $loSmarty->assign('scCodUn3', $lo->pcCodUn3);
         $loSmarty->assign('scCompos', $lo->pcCompos);
         $loSmarty->assign('scVelAut', $lo->pcVelAut);
         $loSmarty->assign('scVelReg', $lo->pcVelReg);
         $loSmarty->assign('scRestri', $lo->pcRestri);
         $loSmarty->assign('scConCli', $lo->pcConCli);
         $loSmarty->assign('scResult', $lo->pcResult);
         $loSmarty->assign('scCodEmp', $lo->pcCodEmp);
         $loSmarty->assign('scCodPry', $lo->pcCodPry);
         $loSmarty->assign('smObserv', $lo->pmObserv);
         fxDumpArray();
         $loSmarty->display('Plantillas/Seg2130.tpl');
      }
   } elseif (@$_POST['Boton1'] == 'Nuevo') {
      $laData = null;
      $laDataP[] = array(1, '00000', 'M');
      $laDataP[] = array(2, '00000', 'J');
      $laDataP[] = array(3, '00000', 'B');
      $_SESSION['pcNroEva'] = 'NUEVO';
      $_SESSION['paDataP']  = $laDataP;
      $_SESSION['paData']   = $laData;
      $_SESSION['plNuevo']  = true;
      $_SESSION['pcSuper1'] = '999999';
      fxScreen1();
   } elseif (@$_POST['Boton2'] == 'Grabar') {
      fxGrabar();
   } elseif (@$_POST['Boton2'] == 'Cancelar') {
      fxInit();
   } elseif (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=3000');
   } else {
      fxInit1();
   }

   // Funciones auxiliares
   function fxInit1() {
      global $lcEvaTip, $loSmarty;
      $lo = new CEvaluacion();
      $llOk = $lo->omInitEvalRadar();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=3000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paSubDiv'] = $lo->paSubDiv;
         $_SESSION['paResult'] = $lo->paResult;
         $_SESSION['paTipVag'] = $lo->paTipVag;
         $_SESSION['paSuperv'] = $lo->paSuperv;
         $_SESSION['paCodUni'] = $lo->paCodUni;
         $_SESSION['paPuesto'] = $lo->paPuesto;
         $_SESSION['paCodNor'] = $lo->paCodNor;
         $_SESSION['paCondic'] = $lo->paCondic;
         $_SESSION['paConCli'] = $lo->paConCli;
         $_SESSION['paCodEmp'] = $lo->paCodEmp;
         $_SESSION['paCodPry'] = $lo->paCodPry;
		 $_SESSION['paEstaci'] = $lo->paEstaci;
         $loSmarty->assign('scBehavior', '0');
		 $loSmarty->assign('scEvaTip', $lcEvaTip);
         $loSmarty->display('Plantillas/Seg2130.tpl');
      }
   }

   function fxGrabar() {
      global $loSmarty, $lcEvaTip;
      fxLoadVar();
      $_SESSION['paDataP'] = $_REQUEST['paDataP'];
      $lo = new CEvaluacion();
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->paDataP  = $_SESSION['paDataP'];
      $lo->pcNroEva = $_SESSION['pcNroEva'];
      $lo->pcFecEva = $_SESSION['pcFecEva'];
      $lo->pcHorSer = $_SESSION['pcHorSer'];
      $lo->pcHorEva = $_SESSION['pcHorEva'];
      $lo->pcKilome = $_SESSION['pcKilome'];
      $lo->pcTren   = $_SESSION['pcTren'];
      $lo->pcSubDiv = $_SESSION['pcSubDiv'];
      $lo->pcEstaci = $_SESSION['pcEstaci'];
      $lo->pcEstacf = $_SESSION['pcEstacf'];
      $lo->pcResult = $_SESSION['pcResult'];
      $lo->pcTipVag = $_SESSION['pcTipVag'];
      $lo->pcSuperv = $_SESSION['pcSuperv'];
      $lo->pcSuper1 = $_SESSION['pcSuper1'];
      $lo->pcCodUn1 = $_SESSION['pcCodUn1'];
      $lo->pcCodUn2 = $_SESSION['pcCodUn2'];
      $lo->pcCodUn3 = $_SESSION['pcCodUn3'];
      $lo->pcCompos = $_SESSION['pcCompos'];
      $lo->pcCondic = $_SESSION['pcCondic'];
      $lo->pcConCli = $_SESSION['pcConCli'];
      $lo->pcVelAut = $_SESSION['pcVelAut'];
      $lo->pcVelReg = $_SESSION['pcVelReg'];
      $lo->pcRestri = $_SESSION['pcRestri'];
      $lo->pcCodEmp = $_SESSION['pcCodEmp'];
      $lo->pcCodPry = $_SESSION['pcCodPry'];
      $lo->pmObserv = $_SESSION['pmObserv'];
      $llOk = $lo->omGrabarPruebaRadar();
      if (!$llOk) { // Error
         fxScreen1();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->assign('scEvaTip', $lcEvaTip);
         $loSmarty->display('Plantillas/Seg2130.tpl');
         fxAlert('GRABACIÓN CONFORME');
      }
   }

   function fxScreen1() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scNroEva', $_SESSION['pcNroEva']);
      $loSmarty->assign('scFecEva', $_SESSION['pcFecEva']);
      $loSmarty->assign('scHorEva', $_SESSION['pcHorEva']);
      $loSmarty->assign('scHorSer', $_SESSION['pcHorSer']);
      $loSmarty->assign('scKilome', $_SESSION['pcKilome']);
      $loSmarty->assign('scKilomf', $_SESSION['pcKilomf']);
      $loSmarty->assign('scTren',   $_SESSION['pcTren']);
      $loSmarty->assign('scCompos', $_SESSION['pcCompos']);
      $loSmarty->assign('scSubDiv', $_SESSION['pcSubDiv']);
      $loSmarty->assign('scEstaci', $_SESSION['pcEstaci']);
      $loSmarty->assign('scEstacf', $_SESSION['pcEstacf']);
      $loSmarty->assign('scResult', $_SESSION['pcResult']);
      $loSmarty->assign('scTipVag', $_SESSION['pcTipVag']);
      $loSmarty->assign('scSuperv', $_SESSION['pcSuperv']);
      $loSmarty->assign('scSuper1', $_SESSION['pcSuper1']);
      $loSmarty->assign('scCodUn1', $_SESSION['pcCodUn1']);
      $loSmarty->assign('scCodUn2', $_SESSION['pcCodUn2']);
      $loSmarty->assign('scCodUn3', $_SESSION['pcCodUn3']);
      $loSmarty->assign('scPuesto', $_SESSION['pcPuesto']);
      $loSmarty->assign('scCodNor', $_SESSION['pcCodNor']);
      $loSmarty->assign('scCondic', $_SESSION['pcCondic']);
      $loSmarty->assign('scConCli', $_SESSION['pcConCli']);
      $loSmarty->assign('scVelAut', $_SESSION['pcVelAut']);
      $loSmarty->assign('scVelReg', $_SESSION['pcVelReg']);
      $loSmarty->assign('scCodEmp', $_SESSION['pcCodEmp']);
      $loSmarty->assign('scCodPry', $_SESSION['pcCodPry']);
      $loSmarty->assign('smObserv', $_SESSION['pmObserv']);
      fxDumpArray();
      $loSmarty->assign('saDataP', $_SESSION['paDataP']);
      $loSmarty->display('Plantillas/Seg2130.tpl');
   }
   
   function fxLoadVar() {
      $_SESSION['pcNroEva'] = $_REQUEST['pcNroEva'];
      $_SESSION['pcFecEva'] = $_REQUEST['pcFecEva'];
      $_SESSION['pcHorEva'] = $_REQUEST['pcHorEva'];
      $_SESSION['pcHorSer'] = $_REQUEST['pcHorSer'];
      $_SESSION['pcKilome'] = $_REQUEST['pcKilome'];
      $_SESSION['pcKilomf'] = $_REQUEST['pcKilomf'];
      $_SESSION['pcTren']   = $_REQUEST['pcTren'];
      $_SESSION['pcCompos'] = $_REQUEST['pcCompos'];
      $_SESSION['pcSubDiv'] = $_REQUEST['pcSubDiv'];
      $_SESSION['pcEstaci'] = $_REQUEST['pcEstaci'];
      $_SESSION['pcEstacf'] = $_REQUEST['pcEstacf'];
      $_SESSION['pcResult'] = $_REQUEST['pcResult'];
      $_SESSION['pcTipVag'] = $_REQUEST['pcTipVag'];
      $_SESSION['pcSuperv'] = $_REQUEST['pcSuperv'];
      $_SESSION['pcCodUn1'] = $_REQUEST['pcCodUn1'];
      $_SESSION['pcCodUn2'] = $_REQUEST['pcCodUn2'];
      $_SESSION['pcCodUn3'] = $_REQUEST['pcCodUn3'];
      $_SESSION['pcCondic'] = $_REQUEST['pcCondic'];
      $_SESSION['pcConCli'] = $_REQUEST['pcConCli'];
      $_SESSION['pcVelAut'] = $_REQUEST['pcVelAut'];
      $_SESSION['pcVelReg'] = $_REQUEST['pcVelReg'];
      $_SESSION['pcSuper1'] = $_REQUEST['pcSuper1'];
      $_SESSION['pcCodEmp'] = $_REQUEST['pcCodEmp'];
      $_SESSION['pcCodPry'] = $_REQUEST['pcCodPry'];
      $_SESSION['pmObserv'] = $_REQUEST['pmObserv'];
      $_SESSION['pcRestri'] = 'S';
      if (empty($_REQUEST['pcRestri']))
         $lcRestri = 'N';
      $laDataP  = $_REQUEST['paDataP'];
      $laData   = $_REQUEST['paData'];
   }
   
   function fxDumpArray() {
      global $loSmarty;
      $loSmarty->assign('saSubDiv', $_SESSION['paSubDiv']);
      $loSmarty->assign('saEstaci', $_SESSION['paEstaci']);
      $loSmarty->assign('saEstacf', $_SESSION['paEstaci']);
      $loSmarty->assign('saResult', $_SESSION['paResult']);
      $loSmarty->assign('saTipVag', $_SESSION['paTipVag']);
      $loSmarty->assign('saSuperv', $_SESSION['paSuperv']);
      $loSmarty->assign('saCodUni', $_SESSION['paCodUni']);
      $loSmarty->assign('saPuesto', $_SESSION['paPuesto']);
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saCodNor', $_SESSION['paCodNor']);
      $loSmarty->assign('saCondic', $_SESSION['paCondic']);
      $loSmarty->assign('saConCli', $_SESSION['paConCli']);
      $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
      $loSmarty->assign('saCodPry', $_SESSION['paCodPry']);
   }
   
   function fxInit() {
      global $lcEvaTip, $loSmarty;
      unset($_SESSION['paData']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('scEvaTip', $lcEvaTip);
      $loSmarty->display('Plantillas/Seg2130.tpl');
   }
?>