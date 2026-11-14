<?php
   // Seguimiento de factores criticos de riesgo
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CIncidentes.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2240', 'Mnu0000.php?Id=4000'))
      return;
   if (!empty($_GET['pcNroInc'])) {
      $_SESSION['pcNroInc']=$_GET['pcNroInc'];
      fxAplicar();
      return;
   }
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Boton9'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2240.php';
      fxHeader('Seg9008.php?Id=Req');
   } else if (@$_POST['Boton9'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      fxAplicar();
   } else if (@$_POST['Boton9'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=4002');
   } else if (@$_POST['Nuevo'] == 'Nuevo') {
      fxNuevo();
   } else if (@$_POST['Eliminar'] == 'Eliminar') {
      fxEliminar();
   } else if (@$_POST['Grabar'] == 'Grabar') {
      fxGrabar();
   } else if (@$_POST['Boton1'] == 'Factor Crítico') {
      fxFactorCritico();
   } else if (@$_POST['Boton1'] == 'Siguiente') {
      fxHeader('Seg2270.php?pcNroInc='.$_SESSION['pcNroInc']);
      return;
   } else if (@$_POST['Boton1'] == 'Causas Inmediatas') {
      fxCausasImediatas();
   } else if (@$_POST['Boton1'] == 'Causas Básicas') {
      fxCausasBasicas();
   } else if (@$_POST['Boton1'] == 'Control Gestión') {
      fxControlGestion();
   } else if (@$_POST['Boton1'] == 'Conclusiones') {
      fxConclusiones();
   } else if (@$_POST['Boton2'] == 'Nuevo') {
      fxNuevo1();
   } else if (@$_POST['Nuevo2'] == 'Nuevo') {
      fxNuevo2();
   } else if (@$_POST['Boton3'] == 'Nuevo') {
      fxNuevo3();
   } else if (@$_POST['Boton4'] == 'Nuevo') {
      fxNuevo4();
   } else if (@$_POST['Nuevo5'] == 'Nuevo') {
      fxNuevo5();
   } else if (@$_POST['Boton2'] == 'Eliminar') {
      fxEliminar1();
   } else if (@$_POST['Boton4'] == 'Eliminar') {
      fxEliminar4();
   } else if (@$_POST['Eliminar2'] == 'Eliminar') {
      fxEliminar2();
   } else if (@$_POST['Boton3'] == 'Eliminar') {
      fxEliminar3();
   } else if (@$_POST['Eliminar5'] == 'Eliminar') {
      fxEliminar5();
   } else if (@$_POST['Boton1'] == 'Cancelar') {
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2240.tpl');
   } else if (@$_POST['Cancelar2'] == 'Cancelar' or @$_POST['Cancelar3'] == 'Cancelar' or @$_POST['Cancelar1'] == 'Cancelar' or @$_POST['Cancelar4'] == 'Cancelar' or @$_POST['Boton6'] == 'Cancelar') {
      fxScreen();
   } else if (@$_POST['Grabar1'] == 'Grabar') {
      fxGrabar1();
   } else if (@$_POST['Grabar2'] == 'Grabar') {
      fxGrabar2();
   } else if (@$_POST['Grabar3'] == 'Grabar') {
      fxGrabar3();
   } else if (@$_POST['Boton6'] == 'Grabar') {
      fxGrabar6();
   } else if (@$_POST['Grabar4'] == 'Grabar') {
      fxGrabar4();
   } else {
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg2240.tpl');
   }

   function fxGrabar() {
      global $loSmarty;
      fxLoadVar();
      $lo = new CIncidentes();
      $lo->pmCausa1 = $_SESSION['pmCausa1'];
      $lo->pmCausa2 = $_SESSION['pmCausa2'];
      $lo->pmCausa3 = $_SESSION['pmCausa3'];
      $lo->pmCausa4 = $_SESSION['pmCausa4'];
      $lo->pmCausa5 = $_SESSION['pmCausa5'];
      $lo->pmComen1 = $_SESSION['pmComen1'];
      $lo->pmComen2 = $_SESSION['pmComen2'];
      $lo->pmComen3 = $_SESSION['pmComen3'];
      $lo->pmComen4 = $_SESSION['pmComen4'];
      $lo->pmComen5 = $_SESSION['pmComen5'];
      $lo->pmEveCro = $_SESSION['pmEveCro'];
      $lo->pmFacCri = $_SESSION['pmFacCri'];
      $lo->pmConclu = $_SESSION['pmConclu'];
      $lo->pcNroInc = $_SESSION['pcNroInc'];
      $lo->paDataF  = $_SESSION['paDataF'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarCausas();
      if (!$llOk) { // Error
         fxScreen();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2240.tpl');
         fxAlert('GRABACIÓN CONFORME');
      }
   }

   function fxGrabar1() {
      global $loSmarty;
      $_SESSION['paData1']  = $_REQUEST['paData1'];
      $_SESSION['paData2']  = $_REQUEST['paData2'];
      $_SESSION['pmComSbe'] = $_REQUEST['pmComSbe'];
      $lo = new CIncidentes();
      $lo->paData1 = $_SESSION['paData1'];
      $lo->paData2 = $_SESSION['paData2'];
      $lo->pmComSbe = $_SESSION['pmComSbe'];
      $lo->pcNroInc = $_SESSION['pcNroInc'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarCausasInmediatas();
      if (!$llOk) { // Error
         fxCausasImediatas();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         fxScreen();
      }
   }

   function fxGrabar2() {
      global $loSmarty;
      $_SESSION['paData3'] = $_REQUEST['paData3'];
      $_SESSION['paData4'] = $_REQUEST['paData4'];
      $_SESSION['pmComFac'] = $_REQUEST['pmComFac'];
      $lo = new CIncidentes();
      $lo->paData3 = $_SESSION['paData3'];
      $lo->paData4 = $_SESSION['paData4'];
      $lo->pmComFac = $_SESSION['pmComFac'];
      $lo->pcNroInc = $_SESSION['pcNroInc'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarCausasBasicas();
      if (!$llOk) { // Error
         fxCausasBasicas();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         fxScreen();
      }
   }

   function fxGrabar3() {
      global $loSmarty;
      $_SESSION['pmFacCri'] = $_REQUEST['pmFacCri'];
      $_SESSION['pmConclu'] = $_REQUEST['pmConclu'];
      $lo = new CIncidentes();
      $lo->pmFacCri = $_SESSION['pmFacCri'];
      $lo->pmConclu = $_SESSION['pmConclu'];
      $lo->pcNroInc = $_SESSION['pcNroInc'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarFactorCritico();
      if (!$llOk) {
         fxFactorCritico();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         fxScreen();
      }
   }
   
   function fxGrabar6() {
      global $loSmarty;
      $_SESSION['pmConclu'] = $_REQUEST['pmConclu'];
      $lo = new CIncidentes();
      $lo->pmConclu = $_SESSION['pmConclu'];
      $lo->pcNroInc = $_SESSION['pcNroInc'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarConclusion();
      if (!$llOk) {
         fxConclusiones();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         fxScreen();
      }
   }
   
   function fxGrabar4() {
      global $loSmarty;
      $_SESSION['paData5'] = $_REQUEST['paData5'];
      $_SESSION['pmComCge'] = $_REQUEST['pmComCge'];
      $lo = new CIncidentes();
      $lo->paData5 = $_SESSION['paData5'];
      $lo->pcNroInc = $_SESSION['pcNroInc'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->pmComCge = $_SESSION['pmComCge'];
      $llOk = $lo->omGrabarControlGestion();
      if (!$llOk) { // Error
         fxControlGestion();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         fxScreen();
      }
   }
   
   function fxCausasImediatas() {
      global $loSmarty;
      $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('smComSbe', $_SESSION['pmComSbe']);
      $loSmarty->assign('saData1',  $_SESSION['paData1']);
      $loSmarty->assign('saData2',  $_SESSION['paData2']);
      $loSmarty->assign('saFacCr1', $_SESSION['paFacCr1']);
      $loSmarty->assign('saFacCr2', $_SESSION['paFacCr2']);
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->display('Plantillas/Seg2240.tpl');
   }
   
   function fxControlGestion() {
      global $loSmarty;
      $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('saData5',  $_SESSION['paData5']);
      $loSmarty->assign('saFacCr5', $_SESSION['paFacCr5']);
      $loSmarty->assign('smComCge', $_SESSION['pmComCge']);
      $loSmarty->assign('scBehavior', '5');
      $loSmarty->display('Plantillas/Seg2240.tpl');
   }
   
   function fxCausasBasicas() {
      global $loSmarty;
      $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('smComFac', $_SESSION['pmComFac']);
      $loSmarty->assign('saData3',  $_SESSION['paData3']);
      $loSmarty->assign('saData4',  $_SESSION['paData4']);
      $loSmarty->assign('saFacCr3', $_SESSION['paFacCr3']);
      $loSmarty->assign('saFacCr4', $_SESSION['paFacCr4']);
      $loSmarty->assign('scBehavior', '3');
      $loSmarty->display('Plantillas/Seg2240.tpl');
   }
   
   function fxEliminar1() {
      global $loSmarty;
      $_SESSION['paData2']=$_REQUEST['paData2'];
	  $laTmp = $_REQUEST['paData1'];
	  if (isset($_REQUEST['paData1']) and isset($_REQUEST['paCheck1']))
         $laTmp = fxEliminarFila($_REQUEST['paData1'], $_REQUEST['paCheck1']);
      $_SESSION['paData1'] = $laTmp;
      fxCausasImediatas();
   }

   function fxEliminar2() {
      global $loSmarty;
	  $_SESSION['paData1'] = $_REQUEST['paData1'];
      $laTmp = $_REQUEST['paData2'];
      if (isset($_REQUEST['paData2']) and isset($_REQUEST['paCheck2']))
         $laTmp = fxEliminarFila($_REQUEST['paData2'], $_REQUEST['paCheck2']);
      $_SESSION['paData2'] = $laTmp;
      fxCausasImediatas();
   }
   
   function fxEliminar3() {
      global $loSmarty;
      $_SESSION['paData4'] = $_REQUEST['paData4'];
	  $laTmp = $_REQUEST['paData3'];
	  if (isset($_REQUEST['paData3']) and isset($_REQUEST['paCheck3']))
         $laTmp = fxEliminarFila($_REQUEST['paData3'], $_REQUEST['paCheck3']);
	  $_SESSION['paData3'] = $laTmp;
      fxCausasBasicas();
   }
   
   function fxEliminar4() {
      global $loSmarty;
      $_SESSION['paData3'] = $_REQUEST['paData3'];
	  $laTmp = $_REQUEST['paData4'];
      if (isset($_REQUEST['paData4']) and isset($_REQUEST['paCheck4']))
         $laTmp = fxEliminarFila($_REQUEST['paData4'], $_REQUEST['paCheck4']);
      $_SESSION['paData4'] = $laTmp;
      fxCausasBasicas();
   }
   
   function fxEliminar5() {
      global $loSmarty;
      $laTmp = $_REQUEST['paData5'];
      if (isset($_REQUEST['paData5']) and isset($_REQUEST['paCheck5']))
         $laTmp = fxEliminarFila($_REQUEST['paData5'], $_REQUEST['paCheck5']);
      $_SESSION['paData5'] = $laTmp;
      fxControlGestion();
   }
   
   function fxNuevo() {
      global $loSmarty;
      $laTmp = $_REQUEST['paDataF'];
      $i = count($laTmp) + 1;
      $laTmp[$i] = array($i, '');
      $_SESSION['paDataF'] = $laTmp;
      fxScreen();
   }

   function fxNuevo1() {
      global $loSmarty;
      $_SESSION['paData2'] = $_REQUEST['paData2'];
      $laTmp = $_REQUEST['paData1'];
      $i = count($laTmp) + 1;
      $laTmp[$i] = array($i, '', '');
      $_SESSION['paData1'] = $laTmp;
      fxCausasImediatas();
   }

   function fxNuevo2() {
      global $loSmarty;
      $_SESSION['paData1'] = $_REQUEST['paData1'];
      $laTmp = $_REQUEST['paData2'];
      $i = count($laTmp) + 1;
      $laTmp[$i] = array($i, '', '');
      $_SESSION['paData2'] = $laTmp;
      fxCausasImediatas();
   }
   
   function fxNuevo3() {
      global $loSmarty;
      $_SESSION['paData4'] = $_REQUEST['paData4'];
      $laTmp = $_REQUEST['paData3'];
      $i = count($laTmp) + 1;
      $laTmp[$i] = array($i, '', '');
      $_SESSION['paData3'] = $laTmp;
      fxCausasBasicas();
   }
   
   function fxNuevo4() {
      global $loSmarty;
      $_SESSION['paData3'] = $_REQUEST['paData3'];
      $laTmp = $_REQUEST['paData4'];
      $i = count($laTmp) + 1;
      $laTmp[$i] = array($i, '', '');
      $_SESSION['paData4'] = $laTmp;
      fxCausasBasicas();
   }

   function fxNuevo5() {
      global $loSmarty;
      $laTmp = $_REQUEST['paData5'];
      $i = count($laTmp) + 1;
      $laTmp[$i] = array($i, '', '');
      $_SESSION['paData5'] = $laTmp;
      fxControlGestion();
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('saDataF',  $_SESSION['paDataF']);
      $loSmarty->assign('smCausa1', $_SESSION['pmCausa1']);
      $loSmarty->assign('smCausa2', $_SESSION['pmCausa2']);
      $loSmarty->assign('smCausa3', $_SESSION['pmCausa3']);
      $loSmarty->assign('smCausa4', $_SESSION['pmCausa4']);
      $loSmarty->assign('smCausa5', $_SESSION['pmCausa5']);
      $loSmarty->assign('smComen1', $_SESSION['pmComen1']);
      $loSmarty->assign('smComen2', $_SESSION['pmComen2']);
      $loSmarty->assign('smComen3', $_SESSION['pmComen3']);
      $loSmarty->assign('smComen4', $_SESSION['pmComen4']);
      $loSmarty->assign('smComen5', $_SESSION['pmComen5']);
      $loSmarty->assign('smEveCro', $_SESSION['pmEveCro']);
      $loSmarty->assign('smFacCri', $_SESSION['pmFacCri']);
      $loSmarty->assign('smConclu', $_SESSION['pmConclu']);
      $loSmarty->assign('smConGes', $_SESSION['pmConGes']);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg2240.tpl');
   }
   
   function fxConclusiones(){
      global $loSmarty;
      $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('smConclu', $_SESSION['pmConclu']);
      $loSmarty->assign('scBehavior', '6');
      $loSmarty->display('Plantillas/Seg2240.tpl');
   }
   
   function fxFactorCritico() {
      global $loSmarty;
      $loSmarty->assign('scNroInc', $_SESSION['pcNroInc']);
      $loSmarty->assign('smDatos',  $_SESSION['pmDatos']);
      $loSmarty->assign('smFacCri', $_SESSION['pmFacCri']);
      $loSmarty->assign('scBehavior', '4');
      $loSmarty->display('Plantillas/Seg2240.tpl');
   }
   
   function fxAplicar() {
      global $loSmarty;
      $_SESSION['plNuevo'] = false;
      $lo = new CIncidentes();
      if (@$_GET['Id'] == 'Aceptar') 
         $lo->pcNroInc = $_SESSION['pcNroInc'];
      else  
         $lo->pcNroInc = $_REQUEST['pcNroInc'];
      $llOk = $lo->omAplicar3();
	  if (!$llOk) {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2240.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['pmDatos']  = $lo->pmDatos;
         $_SESSION['paDataF']  = $lo->paDataF;
         $_SESSION['pcNroInc'] = $lo->pcNroInc;
         $_SESSION['pmCausa1'] = $lo->pmCausa1;
         $_SESSION['pmCausa2'] = $lo->pmCausa2;
         $_SESSION['pmCausa3'] = $lo->pmCausa3;
         $_SESSION['pmCausa4'] = $lo->pmCausa4;
         $_SESSION['pmCausa5'] = $lo->pmCausa5;
         $_SESSION['pmComen1'] = $lo->pmComen1;
         $_SESSION['pmComen2'] = $lo->pmComen2;
         $_SESSION['pmComen3'] = $lo->pmComen3;
         $_SESSION['pmComen4'] = $lo->pmComen4;
         $_SESSION['pmComen5'] = $lo->pmComen5;
         $_SESSION['pmEveCro'] = $lo->pmEveCro;
         $_SESSION['pmFacCri'] = $lo->pmFacCri;
         $_SESSION['pmConclu'] = $lo->pmConclu;
         $_SESSION['pmComSbe'] = $lo->pmComSbe;
         $_SESSION['pmComFac'] = $lo->pmComFac;
         $_SESSION['pmComCge'] = $lo->pmComCge;
         $_SESSION['paData1']  = $lo->paData1;
         $_SESSION['paData2']  = $lo->paData2;
         $_SESSION['paData3']  = $lo->paData3;
         $_SESSION['paData4']  = $lo->paData4;
         $_SESSION['paData5']  = $lo->paData5;
         $_SESSION['paFacCr1'] = $lo->paFacCr1;
         $_SESSION['paFacCr2'] = $lo->paFacCr2;
         $_SESSION['paFacCr3'] = $lo->paFacCr3;
         $_SESSION['paFacCr4'] = $lo->paFacCr4;
         $_SESSION['paFacCr5'] = $lo->paFacCr5;
         fxScreen();
      }
   }
   
   function fxLoadVar() {
      $_SESSION['pcNroInc'] = $_REQUEST['pcNroInc'];
      $_SESSION['pmCausa1'] = $_REQUEST['pmCausa1'];
      $_SESSION['pmCausa2'] = $_REQUEST['pmCausa2'];
      $_SESSION['pmCausa3'] = $_REQUEST['pmCausa3'];
      $_SESSION['pmCausa4'] = $_REQUEST['pmCausa4'];
      $_SESSION['pmCausa5'] = $_REQUEST['pmCausa5'];
      $_SESSION['pmComen1'] = $_REQUEST['pmComen1'];
      $_SESSION['pmComen2'] = $_REQUEST['pmComen2'];
      $_SESSION['pmComen3'] = $_REQUEST['pmComen3'];
      $_SESSION['pmComen4'] = $_REQUEST['pmComen4'];
      $_SESSION['pmComen5'] = $_REQUEST['pmComen5'];
      $_SESSION['pmEveCro'] = $_REQUEST['pmEveCro'];
      $_SESSION['pmFacCri'] = $_REQUEST['pmFacCri'];
      $_SESSION['pmConclu'] = $_REQUEST['pmConclu'];
      $_SESSION['paDataF']  = $_REQUEST['paDataF'];
   }
?>