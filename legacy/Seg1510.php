<?php
   // Registro de Incidentes ferroviarios
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentesFerroviarios.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG1510', 'Mnu0000.php?Id=9000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg1510.php';
      fxHeader('Seg9009.php');
   } else if (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if (@$_POST['Boton1'] == 'Nuevo') {
      fxNuevo();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=9000');
   } else if (@$_POST['Boton2'] == 'Nuevo') {
      fxNuevoReg();
   } else if (@$_POST['Boton2'] == 'Eliminar') {
      fxEliminar();
   } else if (@$_POST['Boton3'] == 'Grabar') {
      fxGrabar();
   } else if (@$_POST['Boton3'] == 'Cancelar') {
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg1510.tpl');
   } else {
      fxInit();
   }

   function fxEliminar() {
      global $loSmarty;
      fxCargarRequest();
      $laData = $_REQUEST['paData'];
      $laCheck = $_REQUEST['paCheck'];
      $laData = fxEliminarFila($laData, $laCheck);
      $_SESSION['paData'] = $laData;
      fxDumpArray();
      fxDevolverRequest();
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg1510.tpl');
   }
   
   function fxNuevoReg() {
      global $loSmarty;
      fxCargarRequest();
      $lcCodUni = $_REQUEST['pcCodUni'];
      if (empty($lcCodUni)) {
         fxDumpArray();
         fxDevolverRequest();
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg1510.tpl');
         return;
      }
      foreach($_SESSION['paCodUni'] as $laFila) {
         if ($laFila[0] == $lcCodUni) {
            $lcDescri = $laFila[1];
            break;
         }
      }
      $laTmp = $_REQUEST['paData'];
      $i = fxCount($laTmp) + 1;
      $laTmp[] = array($i, $lcCodUni, $lcDescri);
      $_SESSION['paData'] = $laTmp;
      fxDumpArray();
      fxDevolverRequest();
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg1510.tpl');
   }

   function fxInit() {
      global $loSmarty;
      $lo = new CIncidentesFerroviarios();
      $llOk = $lo->omInitIncidentes();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=9000', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paCodEmp'] = $lo->paCodEmp;
         $_SESSION['paCodPry'] = $lo->paCodPry;
         $_SESSION['paCodFcr'] = $lo->paCodFcr;
         $_SESSION['paIncFer'] = $lo->paIncFer;
         $_SESSION['paLugAcc'] = $lo->paLugAcc;
         $_SESSION['paAreas']  = $lo->paAreas;
         $_SESSION['paSubDiv'] = $lo->paSubDiv;
         $_SESSION['paEstaci'] = $lo->paEstaci;
         $_SESSION['paCodUni'] = $lo->paCodUni;
		 $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1510.tpl');
      }
   }

   function fxDumpArray() {
      global $loSmarty;
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
      $loSmarty->assign('saCodPry', $_SESSION['paCodPry']);
      $loSmarty->assign('saAreas',  $_SESSION['paAreas']);
      $loSmarty->assign('saCodFcr', $_SESSION['paCodFcr']);
      $loSmarty->assign('saIncFer', $_SESSION['paIncFer']);
      $loSmarty->assign('saLugAcc', $_SESSION['paLugAcc']);
      $loSmarty->assign('saSubDiv', $_SESSION['paSubDiv']);
      $loSmarty->assign('saEstaci', $_SESSION['paEstaci']);
      $loSmarty->assign('saCodUni', $_SESSION['paCodUni']);
      $loSmarty->assign('saData', $_SESSION['paData']);
   }
   
   function fxNuevo() {
      global $loSmarty;
      $_SESSION['plNuevo'] = true;
      $laData = array();
      $_SESSION['paData'] = $laData;
      fxDumpArray();
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scHerido', 'S');
      $loSmarty->display('Plantillas/Seg1510.tpl');
   }

   function fxGrabar() {
      global $loSmarty;
      fxCargarRequest();
      $lo = new CIncidentesFerroviarios();
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcNumero = $_SESSION['pcNumero'];
      $lo->pdFecha  = $_SESSION['pdFecha'];
      $lo->pcHora   = $_SESSION['pcHora'];
      $lo->pcIncFer = $_SESSION['pcIncFer'];
      $lo->pcTren   = $_SESSION['pcTren'];
      $lo->pcCodEmp = $_SESSION['pcCodEmp'];
      $lo->pcCodPry = $_SESSION['pcCodPry'];
      $lo->pcSubDiv = $_SESSION['pcSubDiv'];
      $lo->pcKilome = $_SESSION['pcKilome'];
      $lo->pcLugar  = $_SESSION['pcLugar'];
      $lo->pcCodMaq = $_SESSION['pcCodMaq'];
      $lo->pcCodJef = $_SESSION['pcCodJef'];
      $lo->pcCodBrq = $_SESSION['pcCodBrq'];
      $lo->pcHerido = $_SESSION['pcHerido'];
      $lo->pcCodFcr = $_SESSION['pcCodFcr'];
      $lo->pcArea   = $_SESSION['pcArea'];
      $lo->pnCosto  = $_SESSION['pnCosto'];
      $lo->pcDemora = $_SESSION['pcDemora'];
      $lo->pmEquInv = $_SESSION['pmEquInv'];
      $lo->pmDescri = $_SESSION['pmDescri'];
      $lo->pmDetHer = $_SESSION['pmDetHer'];
      $lo->pmMedAdo = $_SESSION['pmMedAdo'];
      $lo->pmCausas = $_SESSION['pmCausas'];
      $lo->pcEstaci = $_SESSION['pcEstaci'];
      $lo->paData   = $_SESSION['paData'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabar();
      if (!$llOk) { 
         $lcMessage = fxXmlError($lo->pcError);
         fxDumpArray();
         fxDevolverRequest();
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg1510.tpl');
         fxAlert($lcMessage);
      } else {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1510.tpl');
         fxAlert('GRABACION CONFORME');
      }
   }
   
   function fxAplicar() {
      global $loSmarty;
      $_SESSION['plNuevo'] = false;
      $lo = new CIncidentesFerroviarios();
      if (@$_GET['Id'] == 'Aceptar') 
         $lo->pcNumero = $_SESSION['pcNumero'];
      else  
         $lo->pcNumero = $_REQUEST['pcNumero'];
      $llOk = $lo->omAplicar();
      if (!$llOk) {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg1510.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['paData'] = $lo->paData;
         fxDumpArray();
         $loSmarty->assign('scNumero', $lo->pcNumero);
         $loSmarty->assign('sdFecha',  $lo->pcFecha);
         $loSmarty->assign('scHora',   $lo->pcHora);
         $loSmarty->assign('scIncFer', $lo->pcIncFer);
         $loSmarty->assign('scTren',   $lo->pcTren);
         $loSmarty->assign('scCodEmp', $lo->pcCodEmp);
         $loSmarty->assign('scCodPry', $lo->pcCodPry);
         $loSmarty->assign('scSubDiv', $lo->pcSubDiv);
         $loSmarty->assign('scKilome', $lo->pcKilome);
         $loSmarty->assign('scLugar',  $lo->pcLugar);
         $loSmarty->assign('scCodMaq', $lo->pcCodMaq);
         $loSmarty->assign('scCodJef', $lo->pcCodJef);
         $loSmarty->assign('scCodBrq', $lo->pcCodBrq);
         $loSmarty->assign('scHerido', $lo->pcHerido);
         $loSmarty->assign('scCodFcr', $lo->pcCodFcr);
         $loSmarty->assign('scArea',   $lo->pcArea);
         $loSmarty->assign('snCosto',  number_format($lo->pnCosto, 2, '.', ''));
         $loSmarty->assign('scDemora', $lo->pcDemora);
         $loSmarty->assign('scEstaci', $lo->pcEstaci);
         $loSmarty->assign('smEquInv', $lo->pmEquInv);
         $loSmarty->assign('smDescri', $lo->pmDescri);
         $loSmarty->assign('smDetHer', $lo->pmDetHer);
         $loSmarty->assign('smMedAdo', $lo->pmMedAdo);
         $loSmarty->assign('smCausas', $lo->pmCausas);
         $loSmarty->assign('scEstaci', $lo->pcEstaci);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg1510.tpl');
      }
   }

   function fxCargarRequest() {
      $_SESSION['pcNumero'] = $_REQUEST['pcNumero'];
      $_SESSION['pdFecha']  = $_REQUEST['pdFecha'];
      $_SESSION['pcHora']   = $_REQUEST['pcHora'];
      $_SESSION['pcIncFer'] = $_REQUEST['pcIncFer'];
      $_SESSION['pcTren']   = $_REQUEST['pcTren'];
      $_SESSION['pcCodEmp'] = $_REQUEST['pcCodEmp'];
      $_SESSION['pcCodPry'] = $_REQUEST['pcCodPry'];
      $_SESSION['pcSubDiv'] = $_REQUEST['pcSubDiv'];
      $_SESSION['pcKilome'] = $_REQUEST['pcKilome'];
      $_SESSION['pcLugar']  = $_REQUEST['pcLugar'];
      $_SESSION['pcCodMaq'] = $_REQUEST['pcCodMaq'];
      $_SESSION['pcCodJef'] = $_REQUEST['pcCodJef'];
      $_SESSION['pcCodBrq'] = $_REQUEST['pcCodBrq'];
      $_SESSION['pcHerido'] = ($_REQUEST['pcHerido'] == 'S') ? 'S' : 'N';
      $_SESSION['pcCodFcr'] = $_REQUEST['pcCodFcr'];
      $_SESSION['pcArea']   = $_REQUEST['pcArea'];
      $_SESSION['pnCosto']  = $_REQUEST['pnCosto'];
      $_SESSION['pcDemora'] = $_REQUEST['pcDemora'];
      $_SESSION['pmEquInv'] = $_REQUEST['pmEquInv'];
      $_SESSION['pmDescri'] = $_REQUEST['pmDescri'];
      $_SESSION['pmDetHer'] = $_REQUEST['pmDetHer'];
      $_SESSION['pmMedAdo'] = $_REQUEST['pmMedAdo'];
      $_SESSION['pmCausas'] = $_REQUEST['pmCausas'];
      $_SESSION['pcEstaci'] = $_REQUEST['pcEstaci'];
      $_SESSION['paData']   = $_REQUEST['paData'];
   }

   function fxDevolverRequest() {
      global $loSmarty;
      $loSmarty->assign('scNumero', $_SESSION['pcNumero']);
      $loSmarty->assign('sdFecha',  $_SESSION['pdFecha']);
      $loSmarty->assign('scHora',   $_SESSION['pcHora']);
      $loSmarty->assign('scIncFer', $_SESSION['pcIncFer']);
      $loSmarty->assign('scTren',   $_SESSION['pcTren']);
      $loSmarty->assign('scCodEmp', $_SESSION['pcCodEmp']);
      $loSmarty->assign('scCodPry', $_SESSION['pcCodPry']);
      $loSmarty->assign('scSubDiv', $_SESSION['pcSubDiv']);
      $loSmarty->assign('scKilome', $_SESSION['pcKilome']);
      $loSmarty->assign('scLugar',  $_SESSION['pcLugar']);
      $loSmarty->assign('scCodMaq', $_SESSION['pcCodMaq']);
      $loSmarty->assign('scCodJef', $_SESSION['pcCodJef']);
      $loSmarty->assign('scCodBrq', $_SESSION['pcCodBrq']);
      $loSmarty->assign('scHerido', $_SESSION['pcHerido']);
      $loSmarty->assign('scCodFcr', $_SESSION['pcCodFcr']);
      $loSmarty->assign('scArea',   $_SESSION['pcArea']);
      $loSmarty->assign('snCosto',  $_SESSION['pnCosto']);
      $loSmarty->assign('scDemora', $_SESSION['pcDemora']);
      $loSmarty->assign('smEquInv', $_SESSION['pmEquInv']);
      $loSmarty->assign('smDescri', $_SESSION['pmDescri']);
      $loSmarty->assign('smDetHer', $_SESSION['pmDetHer']);
      $loSmarty->assign('smMedAdo', $_SESSION['pmMedAdo']);
      $loSmarty->assign('smCausas', $_SESSION['pmCausas']);
      $loSmarty->assign('scEstaci', $_SESSION['pcEstaci']);
      $loSmarty->assign('saData',   $_SESSION['paData']);
   }
?>