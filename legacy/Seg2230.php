<?php
   // Seguimiento de acciones a tomar de incidentes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2230', 'Mnu0000.php?Id=4002'))
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
      $_SESSION['pcVolver'] = 'Seg2230.php';
      fxHeader('Seg9008.php?Id=Req');
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=4002');
   } else if (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if(@$_POST['Boton2'] == 'Nuevo') {
      fxLoadVar();
      $lo = new CIncidentes();      
      $lo->pcCodPer = $_SESSION['pcCodPer'];
      $llOk = $lo->omAgregarPersonalInvolucrado();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $i = $lo->paData;
         $laData = $_SESSION['paData'];
         $i = fxCount($laData) + 1;
         $laData[$i]= array($i, $lo->paData[0][0] , $lo->paData[0][1] , $lo->paData[0][2] , $lo->paData[0][3], $lo->paData[0][4] , $lo->paData[0][5], '', '', '', '', '');
         $_SESSION['paData'] = $laData;
      }
      fxDumpArray();
      $loSmarty->assign('scNroInc', $_REQUEST['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('scCodPer', $_SESSION['pcCodPer']);
      $loSmarty->assign('scPuesto', $_SESSION['pcPuesto']);
      $loSmarty->assign('saCodPin', $_SESSION['paData']);
      $loSmarty->assign('saCodUin', $_SESSION['paData1']);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg2230.tpl');
   } else if (@$_POST['Boton2'] == 'Eliminar') {
       $laCodUin = null;
       if (isset($_REQUEST['paData1'])) {
          $laCodUin = $_REQUEST['paData1'];
       }
       $laCodPin = $_REQUEST['paData'];
       if (isset($_REQUEST['paCheck']))
          $laCodPin = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
       fxDumpArray();
       $loSmarty->assign('scBehavior', '1');
       $loSmarty->assign('scNroInc', $_REQUEST['pcNroInc']);
       $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
       $loSmarty->assign('saCodPin', $laCodPin);
       $loSmarty->assign('saCodUin', $laCodUin);
       $loSmarty->display('Plantillas/Seg2230.tpl');
   } else if (@$_POST['Boton3'] == 'Nuevo') {
      fxNuevo();
   } else if (@$_POST['Boton3'] == 'Eliminar') {
      $laCodPin = null;
      if (isset($_REQUEST['paData'])) {
         $laCodPin = $_REQUEST['paData'];
      }
      $laCodUin = $_REQUEST['paData1'];
      if (isset($_REQUEST['paCheck1']))
         $laCodUin = fxEliminarFila($_REQUEST['paData1'], $_REQUEST['paCheck1']);
      fxDumpArray();
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scNroInc', $_REQUEST['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('saCodPin', $laCodPin);
      $loSmarty->assign('saCodUin', $laCodUin);
      $loSmarty->display('Plantillas/Seg2230.tpl');
   } else if (@$_POST['Boton4'] == 'Grabar') {
      fxLoadVar();
      $lo = new CIncidentes();
      $lo->paCodPin = $_REQUEST['paData'];
      $lo->paCodUin = $_REQUEST['paData1'];
      $_SESSION['paData']= $lo->paCodPin;
      $_SESSION['paData1']= $lo->paCodUin;
      $lo->pcNroInc = $_REQUEST['pcNroInc'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarPUInvInc();
      if (!$llOk) { // Error
         fxDumpArray();
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scNroInc', $_REQUEST['pcNroInc']);
         $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
         $loSmarty->assign('saCodPin', $_SESSION['paData']);
         $loSmarty->assign('saCodUin', $_SESSION['paData1']);
         $loSmarty->display('Plantillas/Seg2230.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         fxHeader('Seg2260.php?pcNroInc='.$lo->pcNroInc);
         return;
        }
   } else if (@$_POST['Boton4'] == 'Cancelar') {
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2230.tpl');
   } else {
     fxInit();
	 fxScreenI();
   }
   
   function fxInit(){
      $lo = new CIncidentes();
      $llOk = $lo->omInitIncidentes();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=4002', fxXmlError($lo->pcError));
      } else {
         $lo->paCodPer[] = array('999999', '*');
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
         $_SESSION['paPuesto'] = $lo->paPuesto;
         $_SESSION['paCarDis'] = $lo->paCarDis;
         $_SESSION['paCodUni'] = $lo->paCodUni;
         $_SESSION['paPerCod'] = $lo->paPerCod;
      }
   }
   
   function fxScreenI(){
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2230.tpl');
   }
   
   function fxAplicar(){
      global $loSmarty;      
      //$_SESSION['plNuevo'] = false;
      $lo = new CIncidentes();
      if (@$_GET['Id'] == 'Aceptar' or !empty($_GET['pcNroInc'])) {
         $lo->pcNroInc = $_SESSION['pcNroInc'];
      } else {
         $lo->pcNroInc = $_REQUEST['pcNroInc'];
      }
      $llOk = $lo->omAplicar2();
      if (!$llOk) {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2230.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['pmDatos'] = $lo->pmDatos;
         $_SESSION['paData'] = $lo->paCodPin;
         $loSmarty->assign('smDatos',  $lo->pmDatos);
         $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
         $loSmarty->assign('saCodPin', $lo->paCodPin);
         $loSmarty->assign('saCodUin', $lo->paCodUin);
         $loSmarty->assign('saCodPer', $lo->paCodPer);
         $loSmarty->assign('saCodUni', $lo->paCodUni);
         fxDumpArray();
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg2230.tpl');
      }
   }
   // Funciones auxiliares
   function fxLoadVar() {
      $_SESSION['pcNroInc'] = $_REQUEST['pcNroInc'];
      $_SESSION['paData']   = $_REQUEST['paData'];
      $_SESSION['paData1']  = $_REQUEST['paData1'];
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
      $lcReqInv = ($lcReqInv != 'S') ? 'N': $lcReqInv;
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
      $loSmarty->assign('saPuesto', $_SESSION['paPuesto']);
      $loSmarty->assign('saCodUni', $_SESSION['paCodUni']);
      $loSmarty->assign('saCarDis', $_SESSION['paCarDis']);
      $loSmarty->assign('saPerCod', $_SESSION['paPerCod']);
   }
   
   function fxNuevo() {
      global $loSmarty;      
      fxLoadVar();
	  $lcDescri = $laCodUin = null;      
      // Codigo y descripcion de la nueva unidad
      $lcCodUni = $_REQUEST['pcCodUni'];
      foreach($_SESSION['paCodUni'] as $i) {
         if ($i[0] == $lcCodUni) {
            $lcDescri = $i[1];
            break;
         }
      }
      // Unidades registradas
      if (isset($_REQUEST['paData1'])) {
         $laCodUin = $_REQUEST['paData1'];
      }
      // Nueva unidad
	  $j = fxCount($laCodUin);
	  $laCodUin[] = array($j + 1, $lcCodUni, $lcDescri, '');
	  fxDumpArray();
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scNroInc', $_REQUEST['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('scCodPer',  $_SESSION['pcCodPer']);
      $loSmarty->assign('saCodUin', $laCodUin);
      $loSmarty->assign('saCodPin', $_SESSION['paData']);
      $loSmarty->display('Plantillas/Seg2230.tpl');
   }
?>