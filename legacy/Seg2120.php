<?php
   // Registro de evaluaciones de conduccion de trenes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CEvaluacion.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2120', 'Mnu0000.php?Id=3000'))
      return;
   $lcEvaTip = 'C';
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESION');
   } else if (@$_POST['Boton1'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2120';
      $_SESSION['pcEvaTip'] = 'C';
      fxHeader('Seg9006.php');
   } else if (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if (@$_POST['Boton1'] == 'Nuevo') {
      $_SESSION['plNuevo'] = true;
      $laData = $_SESSION['paEvaCon'];
      $laTmp = null;
      $j = 0;
      foreach($laData as $i) {
         $j++;
         $laTmp[$i[0]] = array($j, $i[0], $i[1], 'S');
      }
      $_SESSION['paData'] = $laTmp;
      $_SESSION['pcNroEva'] = 'NUEVO';
      $_SESSION['pmObserv'] = 'SIN OBSERVACIONES';
      fxScreen1();
   } else if (@$_POST['Boton2'] == 'Grabar') {
      fxLoadVar();
      fxFixData();
      $lo = new CEvaluacion();
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->paData   = $_SESSION['paData'];
      $lo->pcNroEva = $_SESSION['pcNroEva'];
      $lo->pcFecEva = $_SESSION['pcFecEva'];
      $lo->pcHorSer = $_SESSION['pcHorSer'];
      $lo->pcHorEva = $_SESSION['pcHorEva'];
      $lo->pcKilome = $_SESSION['pcKilome'];
      $lo->pcKilomf = $_SESSION['pcKilomf'];
      $lo->pcTren   = $_SESSION['pcTren'];
      $lo->pcCompos = $_SESSION['pcCompos'];
      $lo->pcSubDiv = $_SESSION['pcSubDiv'];
      $lo->pcEstaci = $_SESSION['pcEstaci'];
      $lo->pcEstacf = $_SESSION['pcEstacf'];
      $lo->pcResult = $_SESSION['pcResult'];
      $lo->pcTipVag = $_SESSION['pcTipVag'];
      $lo->pcSuperv = $_SESSION['pcSuperv'];
      $lo->pcCodUn1 = $_SESSION['pcCodUn1'];
      $lo->pcCodUn2 = $_SESSION['pcCodUn2'];
      $lo->pcCodUn3 = $_SESSION['pcCodUn3'];
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $lo->pmObserv = $_SESSION['pmObserv'];
      $lo->pcCodEmp = $_SESSION['pcCodEmp'];
      $lo->pcCodPry = $_SESSION['pcCodPry'];
      $llOk = $lo->omGrabarPruebaConduccion();
      if (!$llOk) { // Error
         fxScreen1();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->assign('scEvaTip', $lcEvaTip);
         $loSmarty->display('Plantillas/Seg2120.tpl');
         fxAlert('GRABACION CONFORME');
      }
   } else if (@$_POST['Boton1'] == 'Cancelar') {
      fxInit();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=3000');
   } else {
      $lo = new CEvaluacion();
      $llOk = $lo->omCargaPersonal();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=3000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $llOk = $lo->omInitPruebas(2);
         if (!$llOk) { // Error
            fxInit();
            fxAlert(fxXmlError($lo->pcError));
         } else {
            $_SESSION['paSubDiv'] = $lo->paSubDiv;
            $_SESSION['paEstaci'] = $lo->paEstaci;
            $_SESSION['paResult'] = $lo->paResul1;
            $_SESSION['paTipVag'] = $lo->paTipVag;
            $_SESSION['paSuperv'] = $lo->paSuperv;
            $_SESSION['paCodUni'] = $lo->paCodUni;
            $_SESSION['paCodNor'] = $lo->paCodNor;
            $_SESSION['paEvaCon'] = $lo->paEvaCon;
            $_SESSION['paCodEmp'] = $lo->paCodEmp;
            $_SESSION['paCodPry'] = $lo->paCodPry;
            $loSmarty->assign('scBehavior', '0');
            $loSmarty->assign('scEvaTip', $lcEvaTip);
            $loSmarty->display('Plantillas/Seg2120.tpl');
         }
      }
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
      $_SESSION['pcResult'] = $_REQUEST['pcResult'];
      $_SESSION['pcCodPer'] = $_REQUEST['pcCodPer'];
      $_SESSION['pmObserv'] = $_REQUEST['pmObserv'];
      $_SESSION['pcCodEmp'] = $_REQUEST['pcCodEmp'];
      $_SESSION['pcCodPry'] = $_REQUEST['pcCodPry'];
      $_SESSION['paData']   = $_REQUEST['paData'];
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
      $loSmarty->assign('scCodUn1', $_SESSION['pcCodUn1']);
      $loSmarty->assign('scCodUn2', $_SESSION['pcCodUn2']);
      $loSmarty->assign('scCodUn3', $_SESSION['pcCodUn3']);
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('scCodEmp', $_SESSION['pcCodEmp']);
      $loSmarty->assign('scCodPry', $_SESSION['pcCodPry']);
      $loSmarty->assign('smObserv', $_SESSION['pmObserv']);
      fxDumpArray();
      $loSmarty->assign('saData', $_SESSION['paData']);
      $loSmarty->display('Plantillas/Seg2120.tpl');
   }

   function fxAplicar() {
      global $loSmarty, $lcEvaTip;
      $_SESSION['plNuevo'] = false;
      $lo = new CEvaluacion();
      if (@$_GET['Id'] == 'Aceptar') 
         $lo->pcNroEva = $_SESSION['pcNroEva'];
      else  
         $lo->pcNroEva = $lcEvaTip.$_REQUEST['pcNroEva'];
	  $llOk = $lo->omAplicarConduccionTrenes();
      if (!$llOk) {
         fxInit();
         fxAlert(fxXmlError($lo->pcError));
      } else {
	     $_SESSION['paData'] = $lo->paData;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scNroEva', $lo->pcNroEva);
         $loSmarty->assign('saData',   $_SESSION['paData']);
         $loSmarty->assign('scFecEva', $lo->pcFecEva);
         $loSmarty->assign('scHorEva', $lo->pcHorEva);
         $loSmarty->assign('scHorSer', $lo->pcHorSer);
         $loSmarty->assign('scKilome', $lo->pcKilome);
         $loSmarty->assign('scKilomf', $lo->pcKilomf);
         $loSmarty->assign('scTren',   $lo->pcTren);
         $loSmarty->assign('scCompos', $lo->pcCompos);
         $loSmarty->assign('scEstaci', $lo->pcEstaci);
         $loSmarty->assign('scEstacf', $lo->pcEstacf);
         $loSmarty->assign('scSubDiv', $lo->pcSubDiv);
         $loSmarty->assign('scResult', $lo->pcResult);
         $loSmarty->assign('scTipVag', $lo->pcTipVag);
         $loSmarty->assign('scSuperv', $lo->pcSuperv);
         $loSmarty->assign('scCodUn1', $lo->pcCodUn1);
         $loSmarty->assign('scCodUn2', $lo->pcCodUn2);
         $loSmarty->assign('scCodUn3', $lo->pcCodUn3);
         $loSmarty->assign('scCodEmp', $lo->pcCodEmp);
         $loSmarty->assign('scCodPer', $lo->pcCodPer);
         $loSmarty->assign('scCodPry', $lo->pcCodPry);
         $loSmarty->assign('smObserv', $lo->pmObserv);
         fxDumpArray();
         $loSmarty->display('Plantillas/Seg2120.tpl');
      }
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
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
      $loSmarty->assign('saCodPry', $_SESSION['paCodPry']);
   }
   
   function fxInit() {
      global $lcEvaTip, $loSmarty;
      unset($_SESSION['paData']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('scEvaTip', $lcEvaTip);
      $loSmarty->display('Plantillas/Seg2120.tpl');
   }
   
   function fxFixData() {
      $laCheck = $_REQUEST['paCheck'];
      $laData = $_SESSION['paData'];
      $laTmp = null;
      foreach($laData as $i) {
         $lcFlag = 'N';
         if (isset($laCheck)) {
            foreach($laCheck as $j) {
               if ($i[0] == $j) {
                  $lcFlag = 'S';
                  break;
               }
            }
         }
         $laTmp[$i[0]] = array($i[0], $i[1], $i[2], $lcFlag);
      }
      $_SESSION['paData'] = $laTmp;
      return;
   }
?>