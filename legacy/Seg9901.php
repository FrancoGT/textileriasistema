<?php
   // Registro de heridos en incidentes incidentes
   require_once "Libs/Smarty.class.php";
   require_once "Clases/CIncidentes.php";
   session_start();
   $lcVolver = $_SESSION['pcVolver'].'.php';
   $loSmarty = new Smarty;
   if (!fxInitSession()) {
       fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Nuevo'] == "Nuevo") {
      $lcCodInc = $_REQUEST['pcCodInc'];
      $laData   = $_REQUEST['paData'];
      $i = fxCount($laData) + 1;
      $laData[$i] = array($i, '000000', '', '');
      $loSmarty->assign("scBehavior", '0');
      $loSmarty->assign("saData", $laData);
      $loSmarty->assign("saCodPer", $_SESSION['paCodPer']);
      $loSmarty->assign("scCodInc", $lcCodInc);
      $loSmarty->display("Plantillas/Seg9901.tpl");
   } else if (@$_POST['Eliminar'] == 'Eliminar') {
      if (isset($_REQUEST['paCheck'])) {
         $laData = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
      }
       $loSmarty->assign("scBehavior", '0');
       $loSmarty->assign("saData", $laData);
       $loSmarty->assign("saCodPer", $_SESSION['paCodPer']);
       $loSmarty->assign("scCodInc", $lcCodInc);
       $loSmarty->display("Plantillas/Seg9901.tpl");
   } else if (@$_POST['Grabar'] == "Grabar") {
      fxLoadVar();
      $lo = new CEvaluacion();
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->paData   = $laData;
      $lo->paDataP  = $laDataP;
      $lo->pcNroEva = $lcNroEva;
      $lo->pcFecEva = $lcFecEva;
      $lo->pcHorSer = $lcHorSer;
      $lo->pcHorEva = $lcHorEva;
      $lo->pcKilome = $lcKilome;
      $lo->pcTren   = $lcTren;
      $lo->pcSubDiv = $lcSubDiv;
      $lo->pcEstaci = $lcEstaci;
      $lo->pcEstacf = $lcEstacf;
      $lo->pcResult = $lcResult;
      $lo->pcTipVag = $lcTipVag;
      $lo->pcSuperv = $lcSuperv;
      $lo->pcSuper1 = $lcSuper1;
      $lo->pcCodUni = $lcCodUni;
      $lo->pcCompos = $lcCompos;
      $lo->pcCondic = $lcCondic;
      $lo->pcConCli = $lcConCli;
      $lo->pcVelAut = $lcVelAut;
      $lo->pcVelReg = $lcVelReg;
      $lo->pcRestri = $lcRestri;
      $llOk = $lo->omGrabarPruebaRadar();
      if (!$llOk) { // Error
         $lcMessage = fxXmlError($lo->pcError);
         fxAlert($lcMessage);
         $loSmarty->assign("scBehavior", '1');
         $loSmarty->assign('scNroEva', $lcNroEva);
         $loSmarty->assign('scFecEva', $lcFecEva);
         $loSmarty->assign("scHorEva", $lcHorEva);
         $loSmarty->assign("scHorSer", $lcHorSer);
         $loSmarty->assign("scKilome", $lcKilome);
         $loSmarty->assign("scKilomf", $lcKilomf);
         $loSmarty->assign("scTren",   $lcTren);
         $loSmarty->assign("scCompos", $lcCompos);
         $loSmarty->assign("scSubDiv", $lcSubDiv);
         $loSmarty->assign("scEstaci", $lcEstaci);
         $loSmarty->assign("scEstacf", $lcEstacf);
         $loSmarty->assign("scResult", $lcResult);
         $loSmarty->assign("scTipVag", $lcTipVag);
         $loSmarty->assign("scSuperv", $lcSuperv);
         $loSmarty->assign("scSuper1", $lcSuper1);
         $loSmarty->assign("scCodUni", $lcCodUni);
         $loSmarty->assign("scPuesto", $lcPuesto);
         $loSmarty->assign("scCodPer", $lcCodPer);
         $loSmarty->assign("scCodNor", $lcCodNor);
         $loSmarty->assign("scCondic", $lcCondic);
         $loSmarty->assign("scConCli", $lcConCli);
         $loSmarty->assign("scVelAut", $lcVelAut);
         $loSmarty->assign("scVelReg", $lcVelReg);
         fxDumpArray();
         $loSmarty->assign("saDataP", $laDataP);
         $loSmarty->assign("saData", $laData);
         $loSmarty->display("Plantillas/Seg2130.tpl");
      } else {
         $loSmarty->assign("scAviso", "GRABACIÓN CONFORME");
         $loSmarty->assign("scVolver", "Mnu0000.php");
         $loSmarty->display("Plantillas/SIS1000.tpl");
      }
   } else {
      $lo = new CIncidentes();
      $lo->pcCodInc = @$_GET['Id'];
      $llOk = $lo->omCargarHeridos();
      if (!$llOk) {
         $loSmarty->assign("scAviso", $lo->pcError);
         $loSmarty->assign("scVolver", $lcVolver);
         $loSmarty->display("Plantillas/SIS1000.tpl");
      } else {
         unset($_SESSION['paData']);
         $_SESSION['paData'] = $lo->paData;
         unset($_SESSION['paTipHer']);
         $_SESSION['paTipHer'] = $lo->paTipHer;
         $loSmarty->assign("scBehavior", '0');
         $loSmarty->assign("saData", $lo->paData);
         $loSmarty->assign("saCodPer", $lo->paCodPer);
         $loSmarty->assign("scCodInc", $lo->pcCodInc);
         $loSmarty->display("Plantillas/Seg9901.tpl");
      }
   }
   
   function fxLoadVar() {
      global $lcNroEva, $lcFecEva, $lcHorEva, $lcKilome, $lcTren, $lcCompos, $lcSubDiv, $lcEstaci, $lcEstacf, $lcResult, $lcTipVag, $lcSuperv, $lcCodUni, $laDataP, $laData, $lcKilomf, $lcHorSer, $lcCondic, $lcConCli, $lcSuper1, $lcVelAut, $lcVelReg, $lcRestri;
      $lcNroEva = $_REQUEST['pcNroEva'];
      $lcFecEva = $_REQUEST['pcFecEva'];
      $lcHorEva = $_REQUEST['pcHorEva'];
      $lcHorSer = $_REQUEST['pcHorSer'];
      $lcKilome = $_REQUEST['pcKilome'];
      $lcKilomf = $_REQUEST['pcKilomf'];
      $lcTren   = $_REQUEST['pcTren'];
      $lcCompos = $_REQUEST['pcCompos'];
      $lcSubDiv = $_REQUEST['pcSubDiv'];
      $lcEstaci = $_REQUEST['pcEstaci'];
      $lcEstacf = $_REQUEST['pcEstacf'];
      $lcResult = $_REQUEST['pcResult'];
      $lcTipVag = $_REQUEST['pcTipVag'];
      $lcSuperv = $_REQUEST['pcSuperv'];
      $lcCodUni = $_REQUEST['pcCodUni'];
      $lcCondic = $_REQUEST['pcCondic'];
      $lcConCli = $_REQUEST['pcConCli'];
      $lcVelAut = $_REQUEST['pcVelAut'];
      $lcVelReg = $_REQUEST['pcVelReg'];
      $lcSuper1 = $_REQUEST['pcSuper1'];
      $lcRestri = 'S';
      if (empty($_REQUEST['pcRestri']))
         $lcRestri = 'N';
      $laDataP  = $_REQUEST['paDataP'];
      $laData   = $_REQUEST['paData'];
   }
   
   function fxDumpArray() {
      global $loSmarty;
      $loSmarty->assign("saSubDiv", $_SESSION['paSubDiv']);
      $loSmarty->assign("saEstaci", $_SESSION['paEstaci']);
      $loSmarty->assign("saEstacf", $_SESSION['paEstaci']);
      $loSmarty->assign("saResult", $_SESSION['paResult']);
      $loSmarty->assign("saTipVag", $_SESSION['paTipVag']);
      $loSmarty->assign("saSuperv", $_SESSION['paSuperv']);
      $loSmarty->assign("saCodUni", $_SESSION['paCodUni']);
      $loSmarty->assign("saPuesto", $_SESSION['paPuesto']);
      $loSmarty->assign("saCodPer", $_SESSION['paCodPer']);
      $loSmarty->assign("saCodNor", $_SESSION['paCodNor']);
      $loSmarty->assign("saCondic", $_SESSION['paCondic']);
      $loSmarty->assign("saConCli", $_SESSION['paConCli']);
   }
   
?>