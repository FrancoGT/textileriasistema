<?php
   // Registro de Incidentes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2210', 'Mnu0000.php?Id=4001'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton1'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2210.php';
      fxHeader('Seg9008.php');
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=4001');
   } else if (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      $_SESSION['plNuevo'] = false;
      $lo = new CIncidentes();
      if (@$_GET['Id'] == 'Aceptar') 
         $lo->pcNroInc = $_SESSION['pcNroInc'];
      else  
         $lo->pcNroInc = $_REQUEST['pcNroInc'];
      $llOk = $lo->omAplicar();
      if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2210.tpl');
         fxAlert($lcMessage);
      } else {
         // Procesos de proyecto vigente
         $laTmp = null;
         foreach($_SESSION['paCodPrc'] as $laFila) {
            if ($laFila[0] == $lo->pcCodPry) {
               $laTmp[] = array($laFila[1], $laFila[2]);
            }
         }
         $_SESSION['paPrcCod'] = $laTmp;
         $loSmarty->assign('scNroInc', $lo->pcNroInc);
         $loSmarty->assign('scFecInc', $lo->pcFecInc);
         $loSmarty->assign('scHorInc', $lo->pcHorInc);
         $loSmarty->assign('scCodEmp', $lo->pcCodEmp);
         $loSmarty->assign('scCodPry', $lo->pcCodPry);
         $loSmarty->assign('scLugar',  $lo->pcLugar);
         $loSmarty->assign('scClaInc', $lo->pcClaInc);
         $loSmarty->assign('scTipInc', $lo->pcTipInc);
         $loSmarty->assign('scCodPe1', $lo->pcCodPe1);
         $loSmarty->assign('scArea1',  $lo->pcArea1);
         $loSmarty->assign('scCodPe2', $lo->pcCodPe2);
         $loSmarty->assign('scArea2',  $lo->pcArea2);
         $loSmarty->assign('smDescri', $lo->pmDescri);
         $loSmarty->assign('smSugere', $lo->pmSugere);
         $loSmarty->assign('scKilome', $lo->pcKilome);
         $loSmarty->assign('scCodPrc', $lo->pcCodPrc);
         $loSmarty->assign('scSubDiv', $lo->pcSubDiv);
         $loSmarty->assign('scRepFis', $lo->pcRepFis);
         $loSmarty->assign('scEstaci', $lo->pcEstaci);
         fxDumpArray();
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg2210.tpl');
      }
   } else if (@$_POST['Boton1'] == 'Nuevo') {
      $_SESSION['plNuevo'] = true;
      fxDumpArray();
      // Procesos de primer proyecto
      $laTmp = null;
      $lcCodPry = $_SESSION['paCodPry'][0][0];
      foreach($_SESSION['paCodPrc'] as $laFila) {
         if ($laFila[0] == $lcCodPry) {
            $laTmp[] = array($laFila[1], $laFila[2]);
         }
      }
      $_SESSION['paPrcCod'] = $laTmp;
      $loSmarty->assign('saCodPrc', $laTmp);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg2210.tpl');
   } else if (@$_POST['Grabar'] == 'Grabar') {
      fxLoadVar();
      $lo = new CIncidentes();
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcNroInc = $_SESSION['pcNroInc'];
      $lo->pcFecInc = $_SESSION['pcFecInc'];
      $lo->pcHorInc = $_SESSION['pcHorInc'];
      $lo->pcCodPe1 = $_SESSION['pcCodPe1'];
      $lo->pcCodPe2 = $_SESSION['pcCodPe2'];
      $lo->pcCodEmp = $_SESSION['pcCodEmp'];
      $lo->pcCodPry = $_SESSION['pcCodPry'];
      $lo->pcArea1  = $_SESSION['pcArea1'];
      $lo->pcArea2  = $_SESSION['pcArea2'];
      $lo->pcCausa  = $_SESSION['pcCausa'];
      $lo->pcTipInc = $_SESSION['pcTipInc'];
      $lo->pcClaInc = $_SESSION['pcClaInc'];
      $lo->pcLugar  = $_SESSION['pcLugar'];
      $lo->pcSubDiv = $_SESSION['pcSubDiv'];
      $lo->pcKilome = $_SESSION['pcKilome'];
      $lo->pcEstaci = $_SESSION['pcEstaci'];
      $lo->pmDescri = $_SESSION['pmDescri'];
      $lo->pmSugere = $_SESSION['pmSugere'];
      $lo->pcCodPrc = $_SESSION['pcCodPrc'];
      $lo->pcRepFis = $_SESSION['pcRepFis'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabar();
      if (!$llOk) { // Error
         fxDumpArray();
         $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
         $loSmarty->assign('scFecInc', $_SESSION['pcFecInc']);
         $loSmarty->assign('scHorInc', $_SESSION['pcHorInc']);
         $loSmarty->assign('scCodPe1', $_SESSION['pcCodPe1']);
         $loSmarty->assign('scCodPe2', $_SESSION['pcCodPe2']);
         $loSmarty->assign('scCodEmp', $_SESSION['pcCodEmp']);
         $loSmarty->assign('scCodPry', $_SESSION['pcCodPry']);
         $loSmarty->assign('scArea1',  $_SESSION['pcArea1']);
         $loSmarty->assign('scArea2',  $_SESSION['pcArea2']);
         $loSmarty->assign('scCausa',  $_SESSION['pcCausa']);
         $loSmarty->assign('scTipInc', $_SESSION['pcTipInc']);
         $loSmarty->assign('scClaInc', $_SESSION['pcClaInc']);
         $loSmarty->assign('scKilome', $_SESSION['pcKilome']);
         $loSmarty->assign('scLugar',  $_SESSION['pcLugar']);
         $loSmarty->assign('smDescri', $_SESSION['pmDescri']);
         $loSmarty->assign('smSugere', $_SESSION['pmSugere']);
         $loSmarty->assign('scCodPrc', $_SESSION['pcCodPrc']);
         $loSmarty->assign('scRepFis', $_SESSION['pcRepFis']);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg2210.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         fxHeader('Seg2220.php?pcNroInc='.$lo->pcNroInc);
         return;
      }
   } else {
      $lo = new CIncidentes();
      $llOk = $lo->omInitIncidentes();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=4001', fxXmlError($lo->pcError));
      } else {
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $_SESSION['paPerTer'] = $lo->paPerTer;
         $_SESSION['paClaInc'] = $lo->paClaInc;
         $_SESSION['paTipInc'] = $lo->paTipInc;
         $_SESSION['paLugar']  = $lo->paLugar;
         $_SESSION['paAreas']  = $lo->paAreas;
         $_SESSION['paCausa']  = $lo->paCausa;
         $_SESSION['paCodPry'] = $lo->paCodPry;
         $_SESSION['paCodEmp'] = $lo->paCodEmp;
         $_SESSION['paSubDiv'] = $lo->paSubDiv;
         $_SESSION['paEstaci'] = $lo->paEstaci;
         $_SESSION['paCodPrc'] = $lo->paCodPrc;
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2210.tpl');
      }
   }
   
   function fxLoadVar() {
      $_SESSION['pcNroInc'] = $_REQUEST['pcNroInc'];
      $_SESSION['pcFecInc'] = $_REQUEST['pcFecInc'];
      $_SESSION['pcHorInc'] = $_REQUEST['pcHorInc'];
      $_SESSION['pcCodPe1'] = $_REQUEST['pcCodPe1'];
      $_SESSION['pcCodPe2'] = $_REQUEST['pcCodPe2'];
      $_SESSION['pcCodEmp'] = $_REQUEST['pcCodEmp'];
      $_SESSION['pcCodPry'] = $_REQUEST['pcCodPry'];
      $_SESSION['pcArea1']  = $_REQUEST['pcArea1'];
      $_SESSION['pcArea2']  = $_REQUEST['pcArea2'];
      $_SESSION['pcCausa']  = $_REQUEST['pcCausa'];
      $_SESSION['pcTipInc'] = $_REQUEST['pcTipInc'];
      $_SESSION['pcClaInc'] = $_REQUEST['pcClaInc'];
      $_SESSION['pcLugar']  = $_REQUEST['pcLugar'];
      $_SESSION['pcSubDiv'] = $_REQUEST['pcSubDiv'];
      $_SESSION['pcKilome'] = $_REQUEST['pcKilome'];
      $_SESSION['pcEstaci'] = $_REQUEST['pcEstaci'];
      $_SESSION['pmDescri'] = $_REQUEST['pmDescri'];
      $_SESSION['pmSugere'] = $_REQUEST['pmSugere'];
      $_SESSION['pcCodPrc'] = $_REQUEST['pcCodPrc'];
      $_SESSION['pcRepFis'] = $_REQUEST['pcRepFis'];
      // Sub-arreglo de codigo de procesos
      $laTmp = null;
      foreach($_SESSION['paCodPrc'] as $laFila) {
         if ($laFila[0] == $_REQUEST['pcCodPry'])
            $laTmp[] = array($laFila[1], $laFila[2]);
      }
      $_SESSION['paPrcCod'] = $laTmp;
   }
   
   function fxDumpArray() {
      global $loSmarty;
      $laPerTer = $_SESSION['paCodPer'];
      foreach($_SESSION['paPerTer'] as $laFila) {
         $laPerTer[] = array($laFila[0], $laFila[1]);
      }
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saPerCod', $laPerTer);
      $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
      $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
      $loSmarty->assign('saCodPry', $_SESSION['paCodPry']);
      $loSmarty->assign('saAreas',  $_SESSION['paAreas']);
      $loSmarty->assign('saCausa',  $_SESSION['paCausa']);
      $loSmarty->assign('saTipInc', $_SESSION['paTipInc']);
      $loSmarty->assign('saClaInc', $_SESSION['paClaInc']);
      $loSmarty->assign('saLugar',  $_SESSION['paLugar']);
      $loSmarty->assign('saSubDiv', $_SESSION['paSubDiv']);
      $loSmarty->assign('saEstaci', $_SESSION['paEstaci']);
      $loSmarty->assign('saCodPrc', $_SESSION['paPrcCod']);
   }
?>