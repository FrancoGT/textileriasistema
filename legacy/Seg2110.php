<?php
   // Registro de pruebas de eficiencia en terreno
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CEvaluacion.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2110', 'Mnu0000.php?Id=3000'))
      return;
   $lcEvaTip = 'E';
   $c=0;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESION');
   } else if (@$_POST['Boton1'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2110.php';
      $_SESSION['pcEvaTip'] = 'E';
      fxHeader('Seg9006.php');
   } else if (@$_POST['Evaluar'] == 'Evaluar') {
      fxEvaluar();
   } else if (@$_POST['Boton1'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      $_SESSION['plNuevo'] = false;
      $lo = new CEvaluacion();
      if (@$_GET['Id'] == 'Aceptar') 
         $lo->pcNroEva = $_SESSION['pcNroEva'];
      else  
         $lo->pcNroEva = $lcEvaTip.$_REQUEST['pcNroEva'];
      $llOk = $lo->omAplicarEficienciaTerreno();
      if (!$llOk) {
         fxInit();
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $_SESSION['paData'] = $lo->paData;
         $_SESSION['scNroEva'] = $lo->pcNroEva;
         $_SESSION['scFecEva'] = $lo->pcFecEva;
         $_SESSION['scTren'] = $lo->pcTren;
         $_SESSION['scHorEva'] = $lo->pcHorEva;
         $_SESSION['scKilome'] = $lo->pcKilome;
         $_SESSION['saDataP'] = $lo->paDataP;
         $_SESSION['scCompos'] = $lo->pcCompos;
         $_SESSION['scSubDiv'] = $lo->pcSubDiv;
         $_SESSION['scEstaci'] = $lo->pcEstaci;
         $_SESSION['scResult'] = $lo->pcResult;
         $_SESSION['scTipVag'] = $lo->pcTipVag;
         $_SESSION['scSuperv'] = $lo->pcSuperv;
         $_SESSION['scCodUn3'] = $lo->pcCodUn3;
         $_SESSION['scCodUn1'] = $lo->pcCodUn1;
         $_SESSION['scCodUn2'] = $lo->pcCodUn2;
         $_SESSION['scCodEmp'] = $lo->pcCodEmp;
         $_SESSION['scCodPry'] = $lo->pcCodPry;
         $_SESSION['smObserv'] = $lo->pmObserv;
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scNroEva', $_SESSION['scNroEva']);
         $loSmarty->assign('saDataP',  $_SESSION['saDataP']);
         $loSmarty->assign('saData',   $_SESSION['paData']);
         $loSmarty->assign('scFecEva', $_SESSION['scFecEva']);
         $loSmarty->assign('scHorEva', $_SESSION['scHorEva']);
         $loSmarty->assign('scKilome',  $_SESSION['scKilome']);
         $loSmarty->assign('scTren',   $_SESSION['scTren']);
         $loSmarty->assign('scCompos', $_SESSION['scCompos']);
         $loSmarty->assign('scSubDiv', $_SESSION['scSubDiv']);
         $loSmarty->assign('scEstaci', $_SESSION['scEstaci']);
         $loSmarty->assign('scResult', $_SESSION['scResult']);
         $loSmarty->assign('scTipVag', $_SESSION['scTipVag']);
         $loSmarty->assign('scSuperv', $_SESSION['scSuperv']);
         $loSmarty->assign('scCodUn3',  $_SESSION['scCodUn3']);
         $loSmarty->assign('scCodUn1', $_SESSION['scCodUn1']);
         $loSmarty->assign('scCodUn2', $_SESSION['scCodUn2']);
         $loSmarty->assign('scCodEmp', $_SESSION['scCodEmp']);
         $loSmarty->assign('scCodPry', $_SESSION['scCodPry']);
         $loSmarty->assign('smObserv',$_SESSION['smObserv']);
         fxDumpArray();
         $loSmarty->display('Plantillas/Seg2110.tpl');
      }
   } else if (@$_POST['Boton1'] == 'Nuevo') {
      $_SESSION['plNuevo'] = true;
      $_SESSION['pcNroEva'] = 'NUEVO';
      fxDumpArray();
      fxScreen1();
   } else if (@$_POST['Boton3'] == 'Nueva Norma') {
      $lcCodPer = $_REQUEST['pcCodPer'];
      $lcCodNor = $_REQUEST['pcCodNor'];
      foreach($_SESSION['paCodNor'] as $laFila) {
         if ($lcCodNor == $laFila[0]) {
            $lcDesNor = $laFila[1];
            break;
         }
      }
      $laData = $_REQUEST['paData'];
      $i = fxCount($laData) + 1;
      $laData[$i] = array($i, $lcCodNor, $lcDesNor, 'B');
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
      $loSmarty->assign('scCodPer', $lcCodPer);
      $loSmarty->assign('saResult', $_SESSION['paResult']);
      $loSmarty->assign('saCodNor', $_SESSION['paCodNor']);
      $loSmarty->assign('saData', $laData);
      $loSmarty->display('Plantillas/Seg2110.tpl');
   } else if (@$_POST['Boton3'] == 'Eliminar') {
      if (isset($_REQUEST['paCheck'])) {
         $laData = fxEliminarFila($_REQUEST['paData'], $_REQUEST['paCheck']);
      }
      $lcCodPer = $_REQUEST['pcCodPer'];
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
      $loSmarty->assign('scCodPer', $lcCodPer);
      $loSmarty->assign('saResult', $_SESSION['paResult']);
      $loSmarty->assign('saCodNor', $_SESSION['paCodNor']);
      $loSmarty->assign('saData', $laData);
      $loSmarty->display('Plantillas/Seg2110.tpl');
   } else if (@$_POST['Boton3'] == 'Guardar') {
      $laTmp = fxJoinArray($_REQUEST['paData'], $_SESSION['paData'], $_REQUEST['pcCodPer']);
      $_SESSION['paData'] = $laTmp;
      fxDumpArray();
      fxScreen1();
   } else if (@$_POST['Boton2'] == 'Grabar') {
      fxGrabar();
   } else if (@$_POST['Boton2'] == 'Cancelar') {
      fxInit();
   } else if (@$_POST['Boton1'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=3000');
   } else {
      // Inicializa personal evaluado
      $laDataP[] = array(1, '00000', 'M');
      $laDataP[] = array(2, '00000', 'J');
      $laDataP[] = array(3, '00000', 'B');
      unset($_SESSION['paDataP']);
      $_SESSION['paDataP'] = $laDataP;
      // Inicializa datos de la clase
      $lo = new CEvaluacion();
      $llOk = $lo->omCargaPersonal();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=3000', fxXmlError($lo->pcError));
      } else {
         $lo->paCodPer[] = array('999999', '* N/C');
         $_SESSION['paCodPer'] = $lo->paCodPer;
         $llOk = $lo->omInitPruebas();
         if (!$llOk) { // Error
            fxInit();
            fxAlert(fxXmlError($lo->pcError));
         } else {
            unset($_SESSION['paData']);
            $_SESSION['paSubDiv'] = $lo->paSubDiv;
            $_SESSION['paEstaci'] = $lo->paEstaci;
            $_SESSION['paResult'] = $lo->paResult;
            $_SESSION['paTipVag'] = $lo->paTipVag;
            $_SESSION['paSuperv'] = $lo->paSuperv;
            $_SESSION['paCodUni'] = $lo->paCodUni;
            $_SESSION['paPuesto'] = $lo->paPuesto;
            $_SESSION['paCodNor'] = $lo->paCodNor;
            $_SESSION['paCodEmp'] = $lo->paCodEmp;
            $_SESSION['paCodPry'] = $lo->paCodPry;
            $_SESSION['paPreBas'] = $lo->paPreBas;
            fxInit();
         }
      }
   }

   function fxInit() {
      global $lcEvaTip, $loSmarty;
      unset($_SESSION['paData']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('scEvaTip', $lcEvaTip);
      $loSmarty->display('Plantillas/Seg2110.tpl');
   }

   function fxScreen1() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scNroEva', $_SESSION['pcNroEva']);
      $loSmarty->assign('scFecEva', $_SESSION['pcFecEva']);
      $loSmarty->assign('scHorEva', $_SESSION['pcHorEva']);
      $loSmarty->assign('scKilome', $_SESSION['pcKilome']);
      $loSmarty->assign('scTren',   $_SESSION['pcTren']);
      $loSmarty->assign('smObserv', $_SESSION['pmObserv']);
      $loSmarty->assign('scCompos', $_SESSION['pcCompos']);
      $loSmarty->assign('scSubDiv', $_SESSION['pcSubDiv']);
      $loSmarty->assign('scEstaci', $_SESSION['pcEstaci']);
      $loSmarty->assign('scResult', $_SESSION['pcResult']);
      $loSmarty->assign('scTipVag', $_SESSION['pcTipVag']);
      $loSmarty->assign('scSuperv', $_SESSION['pcSuperv']);
      $loSmarty->assign('scCodUn1', $_SESSION['pcCodUn1']);
      $loSmarty->assign('scCodUn2', $_SESSION['pcCodUn2']);
      $loSmarty->assign('scCodUn3', $_SESSION['pcCodUn3']);
      $loSmarty->assign('scCodEmp', $_SESSION['pcCodEmp']);
      $loSmarty->assign('scCodPry', $_SESSION['pcCodPry']);
      $loSmarty->assign('saDataP', $_SESSION['paDataP']);
      $loSmarty->display('Plantillas/Seg2110.tpl');
   }
   
   function fxLoadVar() {
      $_SESSION['pcNroEva'] = $_REQUEST['pcNroEva'];
      $_SESSION['pcFecEva'] = $_REQUEST['pcFecEva'];
      $_SESSION['pcHorEva'] = $_REQUEST['pcHorEva'];
      $_SESSION['pcKilome'] = $_REQUEST['pcKilome'];
      $_SESSION['pcTren']   = $_REQUEST['pcTren'];
      $_SESSION['pcCompos'] = $_REQUEST['pcCompos'];
      $_SESSION['pcSubDiv'] = $_REQUEST['pcSubDiv'];
      $_SESSION['pcEstaci'] = $_REQUEST['pcEstaci'];
      $_SESSION['pcResult'] = $_REQUEST['pcResult'];
      $_SESSION['pcTipVag'] = $_REQUEST['pcTipVag'];
      $_SESSION['pcSuperv']  = $_REQUEST['pcSuperv'];
      $_SESSION['pcCodUn1'] = $_REQUEST['pcCodUn1'];
      $_SESSION['pcCodUn2'] = $_REQUEST['pcCodUn2'];
      $_SESSION['pcCodUn3'] = $_REQUEST['pcCodUn3'];
      $_SESSION['pcCodEmp'] = $_REQUEST['pcCodEmp'];
      $_SESSION['pcCodPry'] = $_REQUEST['pcCodPry'];
      $_SESSION['pmObserv'] = $_REQUEST['pmObserv'];
      $_SESSION['paDataP']  = $_REQUEST['paDataP'];
   }
   
   function fxDumpArray() {
      global $loSmarty;
      $loSmarty->assign('saSubDiv', $_SESSION['paSubDiv']);
      $loSmarty->assign('saEstaci', $_SESSION['paEstaci']);
      $loSmarty->assign('saResult', $_SESSION['paResult']);
      $loSmarty->assign('saTipVag', $_SESSION['paTipVag']);
      $loSmarty->assign('saSuperv', $_SESSION['paSuperv']);
      $loSmarty->assign('saCodUni', $_SESSION['paCodUni']);
      $loSmarty->assign('saPuesto', $_SESSION['paPuesto']);
      $loSmarty->assign('saCodPer', $_SESSION['paCodPer']);
      $loSmarty->assign('saCodNor', $_SESSION['paCodNor']);
      $loSmarty->assign('saCodEmp', $_SESSION['paCodEmp']);
      $loSmarty->assign('saCodPry', $_SESSION['paCodPry']);
   }

   function fxNormasEmpleados($p_aData, $p_cCodPer) {
	  $laData = null;
      if (!isset($p_aData)) {
         $j = 0;
         foreach ($_SESSION['paPreBas'] as $i) {
            $j++;
            $laData[$j] = array($j, $i[0], $i[1], 'B');
         }
         return $laData;
      }
      $j = 0;
	  foreach($p_aData as $i) {
	     if ($i[0] == $p_cCodPer) {
            $j++;
            $laData[$j] = array($j, $i[2], $i[3], $i[4]);
         }
      }
      if ($j == 0) {
         $lcCodPer = $p_aData[0][0];
         foreach($p_aData as $i) {
            if ($i[0] == $lcCodPer) {
               $j++;
               $laData[$j] = array($j, $i[2], $i[3], 'B');
            }
         }
      }
      return $laData;
   };

   function fxNombreEmpleado($p_aDataP, $p_cCodPer) {
      $lcNombre = null;
      if (!isset($p_aDataP))
         return $lcNombre;
      foreach($p_aDataP as $i) {
         if ($i[0] == $p_cCodPer) {
            $lcNombre = $i[1];
            break;
           }
      }
      return $lcNombre;
   };

   function fxJoinArray($p_aDataN, $p_aData, $p_cCodPer) {
      $lcPuesto = $_SESSION['pcPuesto'];
      $laTmp = null;
      if (isset($p_aData)) {
         foreach($p_aData as $i) {
            if ($i[0] != $p_cCodPer)
               $laTmp[] = array($i[0], $i[1], $i[2], $i[3], $i[4]);
         }
      }
      if (isset($p_aDataN)) {
         foreach($p_aDataN as $i) {
            $laTmp[] = array($p_cCodPer, $lcPuesto, $i[1], $i[2], $i[3]);
         }
      }
      return $laTmp;
   };

   // Evaluar normas por personal
   function fxEvaluar() {
      global $loSmarty;
      fxLoadVar();
	  // Verifica que no haya personal evaluado repetido
	  $llOk = fxValRepeated($_REQUEST['paDataP'], 1);
	  if ($llOk) {
         fxDumpArray();
         fxScreen();
		 fxAlert('PERSONAL EVALUADO SE REPITE');
		 return;
	  }
      if (!isset($_REQUEST['pcPerCod'])) {
         fxDumpArray();
         fxScreen();
		 return;
      }
      // Busca personal a editar
      $i = $_REQUEST['pcPerCod'];
      foreach ($_REQUEST['paDataP'] as $j) {
         if ($i == $j[0]) {
		    $lcCodPer = $j[1];
            $lcPuesto = $j[2];
            break;
         }
      }
      if (empty($lcCodPer) or $lcCodPer == '999999') {
         fxDumpArray();
         fxScreen();
         fxAlert('NO PUEDE SER EVALUADO UN TERCERO');
         return;
      }
      $laData = fxNormasEmpleados($_SESSION['paData'], $lcCodPer);
      $lcNombre = fxNombreEmpleado($_SESSION['paCodPer'], $lcCodPer);
      $_SESSION['pcNombre'] = $lcNombre;
      $_SESSION['pcPuesto'] = $lcPuesto;
      $loSmarty->assign('scNombre', $lcNombre);
      $loSmarty->assign('scCodPer', $lcCodPer);
      $loSmarty->assign('saResult', $_SESSION['paResult']);
      $loSmarty->assign('saCodNor', $_SESSION['paCodNor']);
      $loSmarty->assign('saData', $laData);
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->display('Plantillas/Seg2110.tpl');
   }

   function fxGrabar() {
      global $loSmarty, $lcEvaTip;
      $lo = new CEvaluacion();
      $lo->plNuevo  = $_SESSION['plNuevo'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->paPuesto = $_SESSION['paPuesto'];
      $lo->paData   = $_SESSION['paData'];
      $lo->paDataP  = $_REQUEST['paDataP'];
      $lo->pcNroEva = $_REQUEST['pcNroEva'];
      $lo->pcFecEva = $_REQUEST['pcFecEva'];
      $lo->pcHorEva = $_REQUEST['pcHorEva'];
      $lo->pcKilome = $_REQUEST['pcKilome'];
      $lo->pcTren   = $_REQUEST['pcTren'];
      $lo->pcCompos = $_REQUEST['pcCompos'];
      $lo->pcSubDiv = $_REQUEST['pcSubDiv'];
      $lo->pcEstaci = $_REQUEST['pcEstaci'];
      $lo->pcTipVag = $_REQUEST['pcTipVag'];
      $lo->pcSuperv = $_REQUEST['pcSuperv'];
      $lo->pcCodUn1 = $_REQUEST['pcCodUn1'];
      $lo->pcCodUn2 = $_REQUEST['pcCodUn2'];
      $lo->pcCodUn3 = $_REQUEST['pcCodUn3'];
      $lo->pcCodEmp = $_REQUEST['pcCodEmp'];
      $lo->pcCodPry = $_REQUEST['pcCodPry'];
      $lo->pmObserv = $_REQUEST['pmObserv'];
      $lo->paData = $_SESSION['paData'];
      $llOk = $lo->omGrabarPruebaEficiencia();
      if (!$llOk) { // Error
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->assign('scNroEva', $lo->pcNroEva);
         $loSmarty->assign('scFecEva', $lo->pcFecEva);
         $loSmarty->assign('scHorEva', $lo->pcHorEva);
         $loSmarty->assign('scKilome', $lo->pcKilome);
         $loSmarty->assign('scTren',   $lo->pcTren);
         $loSmarty->assign('scCompos', $lo->pcCompos);
         $loSmarty->assign('scSubDiv', $lo->pcSubDiv);
         $loSmarty->assign('scEstaci', $lo->pcEstaci);
         $loSmarty->assign('scTipVag', $lo->pcTipVag);
         $loSmarty->assign('scSuperv', $lo->pcSuperv);
         $loSmarty->assign('scCodUn1', $lo->pcCodUn1);
         $loSmarty->assign('scCodUn2', $lo->pcCodUn2);
         $loSmarty->assign('scCodUn3', $lo->pcCodUn3);
         $loSmarty->assign('scCodEmp', $lo->pcCodEmp);
         $loSmarty->assign('scCodPry', $lo->pcCodPry);
         $loSmarty->assign('smObserv', $lo->pmObserv);
         $loSmarty->assign('saDataP',  $lo->paDataP);
         fxDumpArray();
         $loSmarty->display('Plantillas/Seg2110.tpl');
         fxAlert(fxXmlError($lo->pcError));
      } else {
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->assign('scEvaTip', $lcEvaTip);
         $loSmarty->display('Plantillas/Seg2110.tpl');
         fxAlert('GRABACIÓN CONFORME');
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scNroEva', $_SESSION['pcNroEva']);
      $loSmarty->assign('saDataP',  $_SESSION['paDataP']);
      $loSmarty->assign('saData',   $_SESSION['paData']);
      $loSmarty->assign('scFecEva', $_SESSION['pcFecEva']);
      $loSmarty->assign('scHorEva', $_SESSION['pcHorEva']);
      $loSmarty->assign('scKilome', $_SESSION['pcKilome']);
      $loSmarty->assign('scTren',   $_REQUEST['pcTren']);
      $loSmarty->assign('scCompos', $_SESSION['pcCompos']);
      $loSmarty->assign('scSubDiv', $_SESSION['pcSubDiv']);
      $loSmarty->assign('scEstaci', $_SESSION['pcEstaci']);
      $loSmarty->assign('scResult', $_SESSION['pcResult']);
      $loSmarty->assign('scTipVag', $_SESSION['pcTipVag']);
      $loSmarty->assign('scSuperv', $_SESSION['pcSuperv']);
      $loSmarty->assign('scCodUn3', $_SESSION['pcCodUn3']);
      $loSmarty->assign('scCodUn1', $_SESSION['pcCodUn1']);
      $loSmarty->assign('scCodUn2', $_SESSION['pcCodUn2']);
      $loSmarty->assign('scCodEmp', $_REQUEST['pcCodEmp']);
      $loSmarty->assign('scCodPry', $_REQUEST['pcCodPry']);
      $loSmarty->assign('smObserv', $_REQUEST['pmObserv']);
      $loSmarty->display('Plantillas/Seg2110.tpl');
   }
?>