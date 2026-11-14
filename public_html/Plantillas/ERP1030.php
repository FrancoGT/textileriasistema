<?php
   // Registro y mantenimiento de clientes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoClientes.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP1030', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   }
   if (@$_POST['Boton'] == 'Nuevo') {
      if($_SESSION['lcTipo']=='A'){
		 $loSmarty->assign('saEstado', $_SESSION['paEstado']);
		 $loSmarty->assign('scBehavior', '1'); 
      }else{
	     $loSmarty->assign('saEstado', $_SESSION['paEstado']);
		 $loSmarty->assign('saCIIU', $_SESSION['paCIIU']);
		 $loSmarty->assign('scBehavior', '2'); 
      }
      $_SESSION['pcCodCur'] = '';
      $_SESSION['plNuevo'] = true;
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->assign('scEstado', '');
	  $loSmarty->assign('saPriori', $_SESSION['paPriori']);
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('scTipo', $_SESSION['lcTipo']);  
      $loSmarty->display('Plantillas/ERP1030.tpl');
   } else if (@$_POST['Cancelar'] == 'Cancelar') {
        $lcTipo=$_SESSION['lcTipo'];
        if($lcTipo=='A'){
	       $loSmarty->assign('scBehavior', '3'); 
		 }elseif($lcTipo=='B'){
		   $loSmarty->assign('scBehavior', '4'); 
		 }elseif($lcTipo=='C'){
		   $loSmarty->assign('scBehavior', '5'); 
		 }
		 $loSmarty->assign('scTipo', $lcTipo); 
		 $loSmarty->assign('saCodA', $_SESSION['paCodA']);
		 $loSmarty->assign('saCodB', $_SESSION['paCodB']);
		 $loSmarty->assign('saCodC', $_SESSION['paCodC']);
		 $loSmarty->assign('scEstado', $lo->pcEstado);
		 $loSmarty->display('Plantillas/ERP1030.tpl');
   } else if (@$_POST['Aplicar'] == 'Aplicar') {
   echo '*****';
      $lo = new CMtoClientes();
      $lo->pcCodCli = @$_REQUEST['pcCodCli'];
	  echo':::'.$lo->pcCodCli;
	  echo '0000';
      $llOk = $lo->omAplicarCliente();
	  echo '1111';
      if (!$llOk) { // Error
	     fxAlert(fxXmlError($lo->pcError));
         $loSmarty->assign('scAviso', $lo->pcError);
		 $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/ERP1030.tpl');
	  } else {	  
         // aginamos datos a la plantilla
		 $loSmarty->assign('scBehavior', '6');
         $loSmarty->assign('scTipo', $lo->pcTipo);
		 $loSmarty->assign('scCodCli', $lo->pcCodCli);
		 $loSmarty->assign('saPriori', $_SESSION['paPriori']);
		 $loSmarty->assign('saEstado', $_SESSION['paEstado']);
		 $loSmarty->assign('saCIIU', $_SESSION['paCIIU']);
         $loSmarty->assign('scNombre', $lo->pcNombre);
		 $loSmarty->assign('scNroDNI', $lo->pcNroDNI);
		 $loSmarty->assign('scNroRUC', $lo->pcNroRUC);
		 $loSmarty->assign('scDirec', $lo->pcDirec);
		 $loSmarty->assign('scTelef', $lo->pcTelef);
		 $loSmarty->assign('scEstado', $lo->pcEstado);
		 $loSmarty->assign('scCIIU', $lo->pcCIIU);
		 $loSmarty->assign('scSiglas', $lo->pcSiglas);
		 $loSmarty->assign('scPriori', $lo->pcPriori);
         $loSmarty->display('Plantillas/ERP1030.tpl');
      }
   } else if (@$_POST['Salir'] == 'Salir') {
       fxHeader('Mnu0000.php?Id=1000');
   } else if (@$_POST['Aceptar'] == 'Aceptar') {
         $lcTipo = $_REQUEST['pcTipCli'];
		 $_SESSION['lcTipo']=$lcTipo;
		 if($lcTipo=='A'){
	       $loSmarty->assign('scBehavior', '3'); 
		 }elseif($lcTipo=='B'){
		   $loSmarty->assign('scBehavior', '4'); 
		 }elseif($lcTipo=='C'){
		   $loSmarty->assign('scBehavior', '5'); 
		 }
		 $loSmarty->assign('scTipo', $lcTipo); 
		 $loSmarty->assign('saCodA', $_SESSION['paCodA']);
		 $loSmarty->assign('saCodB', $_SESSION['paCodB']);
		 $loSmarty->assign('saCodC', $_SESSION['paCodC']);
		 $loSmarty->display('Plantillas/ERP1030.tpl');
       
   } else if (@$_POST['Grabar'] == 'Grabar') {
      $lo = new CMtoClientes();
	  if($_SESSION['lcTipo']=='A'){
	  $lo->pcTipo =$_SESSION['lcTipo'];
      $lo->pcCodCli = $_REQUEST['pcCodCli'];
      $lo->pcNombre = $_REQUEST['pcNombre'];
      $lo->pcNroDNI = $_REQUEST['pcNroDNI'];
      $lo->pcNroRUC = '9999999999';
      $lo->pcNombre = $_REQUEST['pcNombre'];
      $lo->pcDirec  = $_REQUEST['pcDirec'];
      $lo->pcTelef  = $_REQUEST['pcTelef'];
      $lo->pcEstado = $_REQUEST['pcEstado'];      
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
	  $lo->pcCIIU = '9999';
	  $lo->pcSiglas = 'S/S';
	  $lo->pcPriori = $_REQUEST['pcPriori'];
	  echo '<br>N'.$lo->pcPriori;
	  }else{
	  $lo->pcTipo =$_SESSION['lcTipo'];
      $lo->pcCodCli = $_REQUEST['pcCodCli'];
      $lo->pcNombre = $_REQUEST['pcNombre'];
      $lo->pcNroDNI = '99999999';
      $lo->pcNroRUC = $_REQUEST['pcNroRUC'];
      $lo->pcNombre = $_REQUEST['pcNombre'];
      $lo->pcDirec  = $_REQUEST['pcDirec'];
      $lo->pcTelef  = $_REQUEST['pcTelef'];
      $lo->pcEstado = $_REQUEST['pcEstado'];      
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
	  $lo->pcCIIU = $_REQUEST['pcCIIU'];
	  $lo->pcSiglas = $_REQUEST['pcSiglas'];	  
	  $lo->pcPriori = $_REQUEST['pcPriori'];
	  echo '<br>E'.$lo->pcPriori;
	  }
	  $lo->plNuevo=$_SESSION['plNuevo'];
      $llOk = $lo->omGrabarCliente();//OJO EMM FALTA GRABAR Y ACTUALIZAR
      if (!$llOk) { // Error
		 fxAlert(fxXmlError($lo->pcError));
         fxInit();
      } else {
         fxInit();
         fxAlert('GRABACION CONFORME');
      }
   } else {
      fxInit();
   }

   function fxInit() {
      global $loSmarty;
      $lo = new CMtoClientes();
      $llOk = $lo->omInitClientes();
      if (!$llOk) { // Error
	  //die('000');
         fxHeader('Mnu0000.php?Id=4000', fxXmlError($lo->pcError));
      } else { 
	  //die('111');
         $_SESSION['paData']   = $lo->paData;
         $_SESSION['paEstado'] = $lo->paEstado;
         $_SESSION['paTipCli'] = $lo->paTipCli;
         $_SESSION['paCIIU'] = $lo->paCIIU;
	     $_SESSION['paCodA'] = $lo->paCodA;
	     $_SESSION['paCodB'] = $lo->paCodB;
	     $_SESSION['paCodC'] = $lo->paCodC;
		 $_SESSION['paPriori'] = $lo->paPriori;
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->assign('saTipCli', $_SESSION['paTipCli']);
         $loSmarty->display('Plantillas/ERP1030.tpl');
      }
   }
?>