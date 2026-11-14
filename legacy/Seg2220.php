<?php
   // Datos adicionales de Incidentes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2220', 'Mnu0000.php?Id=4001'))
      return;
   if (!empty($_GET['pcNroInc'])) {
      $_SESSION['pcNroInc']=$_GET['pcNroInc'];
	  fxInit();
      fxAplicar();
      return;
   }
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2220.php';
      fxHeader('Seg9008.php');
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=4001');
   } else if (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if (@$_POST['Boton2'] == 'Nuevo') {
      fxNuevo();
   } else if (@$_POST['Boton2'] == 'Eliminar') {
      fxEliminar();
   } else if (@$_POST['Boton3'] == 'Grabar') {
      fxGrabar();
   } else if (@$_POST['Boton3'] == 'Cancelar') {
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2220.tpl');
   } else {
      fxInit();
	  ScreenI();
   }

   function fxAplicar() {
      global $loSmarty;      
      $_SESSION['plNuevo'] = false;
      $lo = new CIncidentes();
      if (@$_GET['Id'] == 'Aceptar' or !empty($_GET['pcNroInc'])) 
         $lo->pcNroInc = $_SESSION['pcNroInc'];
      else  
         $lo->pcNroInc = $_REQUEST['pcNroInc'];
      $llOk = $lo->omAplicar1();
      if (!$llOk) {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2220.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['pmDatos']  = $lo->pmDatos;
         $_SESSION['pcNroInc'] = $lo->pcNroInc;
         $_SESSION['pcConsec'] = $lo->pcConsec;
         $_SESSION['pcPotenc'] = $lo->pcPotenc;
         $_SESSION['pcReqInv'] = $lo->pcReqInv;
         $_SESSION['pcCodPer'] = $lo->pcCodPer;
         $_SESSION['pmAnaPre'] = $lo->pmAnaPre;
         $_SESSION['paData']   = $lo->paData;
         $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
         $loSmarty->assign('scNroInc', $lo->pcNroInc);
         $loSmarty->assign('scConsec', $lo->pcConsec);
         $loSmarty->assign('scPotenc', $lo->pcPotenc);
         $loSmarty->assign('scReqInv', $lo->pcReqInv);
         $loSmarty->assign('scCodPer', $lo->pcCodPer);
         $loSmarty->assign('smAnaPre', $lo->pmAnaPre);
         $loSmarty->assign('saData',   $lo->paData);
         fxDumpArray();
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg2220.tpl');
      }
   }
   
   function fxNuevo() {
      global $loSmarty;
      fxLoadVar();
      $laData = null;
      if (isset($_REQUEST['paData'])) {
         $laData = $_REQUEST['paData'];
      }
      $j = fxCount($laData) + 1;
      $laData[] = array($j, '00','','000000','','','0');
      fxDumpArray();
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('smAnaPre', $_SESSION['pmAnaPre']);
      $loSmarty->assign('scConsec', $_SESSION['pcConsec']);
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('scPotenc', $_SESSION['pcPotenc']);
      $loSmarty->assign('scCondic', $_SESSION['pcCondic']);
      $loSmarty->assign('scReqInv', $_SESSION['pcReqInv']);
      $loSmarty->assign('saData', $laData);
      $loSmarty->display('Plantillas/Seg2220.tpl');
   }

   function fxEliminar() {
      global $loSmarty;
      $laData = $_REQUEST['paData'];
      if (isset($_REQUEST['paCheck']))
         $laData = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
      fxDumpArray();
      $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('smAnaPre', $_SESSION['pmAnaPre']);
      $loSmarty->assign('scConsec', $_SESSION['pcConsec']);
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('scPotenc', $_SESSION['pcPotenc']);
      $loSmarty->assign('scCondic', $_SESSION['pcCondic']);
      $loSmarty->assign('scReqInv', $_SESSION['pcReqInv']);
      $loSmarty->assign('saAccTom', $_SESSION['paAccTom']);
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saCondic', $_SESSION['paCondic']);
      $loSmarty->assign('saData', $laData);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg2220.tpl');
   }

   function fxGrabar() {
      global $loSmarty;
      fxLoadVar();
      $lo->pcReqInv = $_SESSION['pcReqInv'];
      $lo = new CIncidentes();
      $lo->paData   = $_REQUEST['paData'];
      $lo->pcNroInc = $_SESSION['pcNroInc'];
      $lo->pcConsec = $_SESSION['pcConsec'];
      $lo->pcPotenc = $_SESSION['pcPotenc'];
      $lo->pcReqInv = $_SESSION['pcReqInv'];
      $lo->pmAnaPre = $_SESSION['pmAnaPre'];
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
	  $llOk = $lo->omGrabarDetalleIncidente();
	  if (!$llOk) { // Error
         fxDumpArray();
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
         $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
         $loSmarty->assign('smAnaPre', $_SESSION['pmAnaPre']);
         $loSmarty->assign('scConsec', $_SESSION['pcConsec']);
         $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
         $loSmarty->assign('scPotenc', $_SESSION['pcPotenc']);
         $loSmarty->assign('scCondic', $_SESSION['pcCondic']);
         $loSmarty->assign('scReqInv', $_SESSION['pcReqInv']);
         $loSmarty->assign('saData',   $_SESSION['paData']);
         $loSmarty->display('Plantillas/Seg2220.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         fxHeader('Seg2250.php?pcNroInc='.$lo->pcNroInc);
         return;
      }
   }

   function fxInit() {
      $lo = new CIncidentes();
      $llOk = $lo->omInitIncidentes();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=4001', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paClaInc'] = $lo->paClaInc;
         $_SESSION['paTipInc'] = $lo->paTipInc;
         $_SESSION['paLugar']  = $lo->paLugar;
         $_SESSION['paAreas']  = $lo->paAreas;
         $_SESSION['paCausa']  = $lo->paCausa;
         $_SESSION['paCodPry'] = $lo->paCodPry;
         $_SESSION['paCodEmp'] = $lo->paCodEmp;
         $_SESSION['paConsec'] = $lo->paConsec;
         $_SESSION['paPotenc'] = $lo->paPotenc;
         $_SESSION['paAccTom'] = $lo->paAccTom;
         $_SESSION['paCondic'] = $lo->paCondic;
         $_SESSION['paPerCod'] = $lo->paPerCod;
      }
   }
    function ScreenI() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2220.tpl');
   }
      
   function fxLoadVar() {
      $_SESSION['pcNroInc'] = $_REQUEST['pcNroInc'];
      $_SESSION['paData']   = $_REQUEST['paData'];
      $_SESSION['pmAnaPre'] = $_REQUEST['pmAnaPre'];
      $_SESSION['pcFecInc'] = $_REQUEST['pcFecInc'];
      $_SESSION['pcHorInc'] = $_REQUEST['pcHorInc'];
      $_SESSION['pcCodPe1'] = $_REQUEST['pcCodPe1'];
      $_SESSION['pcCodPe2'] = $_REQUEST['pcCodPe1'];
      $_SESSION['pcCodEmp'] = $_REQUEST['pcCodEmp'];
      $_SESSION['pcCodPer'] = $_REQUEST['pcCodPer'];
      $_SESSION['pcConsec'] = $_REQUEST['pcConsec'];
      $_SESSION['pcPotenc'] = $_REQUEST['pcPotenc'];
      $_SESSION['pcCondic'] = $_REQUEST['pcCondic'];
      $_SESSION['pcAccTom'] = $_REQUEST['pcAccTom'];
      $_SESSION['pcCodPry'] = $_REQUEST['pcCodPry'];
      $_SESSION['pcArea1']  = $_REQUEST['pcArea1'];
      $_SESSION['pcArea2']  = $_REQUEST['pcArea1'];
      $_SESSION['pcCausa']  = $_REQUEST['pcCausa'];
      $_SESSION['pcTipInc'] = $_REQUEST['pcTipInc'];
      $_SESSION['pcClaInc'] = $_REQUEST['pcClaInc'];
      $_SESSION['pcLugar']  = $_REQUEST['pcLugar'];
      $_SESSION['pmDescri'] = $_REQUEST['pmDescri'];
      $_SESSION['pmSugere'] = $_REQUEST['pmSugere'];
      $lcReqInv = $_REQUEST['pcReqInv'];
      if ($lcReqInv != 'S')
         $lcReqInv = 'N';
      $_SESSION['pcReqInv'] = $lcReqInv;
   }
   
   function fxDumpArray() {
      global $loSmarty;
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
      $loSmarty->assign('smAnaPre', $_SESSION['pmAnaPre']);
      $loSmarty->assign('saConsec', $_SESSION['paConsec']);
      $loSmarty->assign('saPotenc', $_SESSION['paPotenc']);
      $loSmarty->assign('saCondic', $_SESSION['paCondic']);
      $loSmarty->assign('saReqInv', $_SESSION['paReqInv']);
      $loSmarty->assign('saAccTom', $_SESSION['paAccTom']);
      $loSmarty->assign('saCodPry', $_SESSION['paCodPry']);
      $loSmarty->assign('saAreas',  $_SESSION['paAreas']);
      $loSmarty->assign('saCausa',  $_SESSION['paCausa']);
      $loSmarty->assign('saTipInc', $_SESSION['paTipInc']);
      $loSmarty->assign('saClaInc', $_SESSION['paClaInc']);
      $loSmarty->assign('saLugar',  $_SESSION['paLugar']);
      $loSmarty->assign('saPerCod', $_SESSION['paPerCod']);
   }
?>