<?php
   // Registro y mantenimiento de personal operativo
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoPedidos.php';
   require_once 'Clases/CDocRTF.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP2010', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif ($_POST['Boton0'] == 'Detalle') {
      fxDetalle();
   } elseif ($_POST['Boton'] == 'Nuevo Cliente') {
      fxHeader('ERP1030.php');
   } elseif ($_POST['Boton'] == 'Cargar Datos') {
      fxCargar();
   } elseif ($_POST['Boton0'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } elseif ($_POST['Boton0'] == 'Asignar Insumos') {
      fxAsignar();
   } elseif ($_POST['Boton0'] == 'Aceptar') {
      $_SESSION['llFirst'] = true;
      fxAceptar();
   } elseif ($_POST['Boton2'] == 'Agregar') {
      fxAgregar();
   } elseif ($_POST['Boton2'] == 'Nuevo') {
      fxHeader('ERP1070.php');
   } elseif ($_POST['Boton3'] == 'Calcular') {
      fxCalcular();
   } elseif ($_POST['Boton1'] == 'Cancelar') {
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saDataA', $_SESSION['paDataA']);
	  $loSmarty->display('Plantillas/ERP2010.tpl');   
   } elseif ($_POST['Boton4'] == 'Grabar') {
      fxGrabar();
   } elseif ($_POST['Boton1'] == 'Eliminar') {
      $_SESSION['paDataI'] = fxEliminarFila($_SESSION['paDataI'], $_REQUEST['paCheck']);
      $loSmarty->assign('scBehavior', '1'); 
	  $loSmarty->assign('scCodIns', $_SESSION['pcCodIns']);
      $loSmarty->assign('saCodIns', $_SESSION['paCodIns']);
	  $loSmarty->assign('scOrigen', $_SESSION['pcOrigen']);
	  $loSmarty->assign('scDestino', $_SESSION['pcDestino']);
	  $loSmarty->assign('saCodAlm', $_SESSION['paCodAlm']);
      $loSmarty->assign('saDataP',$laData);
      $loSmarty->display('Plantillas/ERP2010.tpl');
      } elseif ($_POST['Boton1'] == 'Cancelar') {
         $loSmarty->assign('scBehavior', '0');
		 $loSmarty->assign('saDataP', $_SESSION['paDataP']);
         $loSmarty->display('Plantillas/ERP2010.tpl');
   } else {
   //INIT
      $lo = new CMtoPedidos();
      $llOk = $lo->omInitPedidos();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else { 
         $_SESSION['paTipo'] = $lo->paTipo;
		 $_SESSION['paNroDNI'] = $lo->paNroDNI;
		 $_SESSION['paNroRUC'] = $lo->paNroRUC;
		 $_SESSION['paPriori'] = $lo->paPriori;
		 $_SESSION['paCodProd'] = $lo->paCodProd;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saTipo', $_SESSION['paTipo']);
	  $loSmarty->display('Plantillas/ERP2010.tpl');
   }
   function fxDetalle(){//muestra los insumos del almacen
      global $loSmarty;
	  $lo = new CMtoAlmacenes();
	  $lo->pcCodAlm = $_REQUEST['pcCodAlm'];
      $llOk = $lo->omAplicar();
	  if (!$llOk) { // Error
         fxHeader('ERP2010.php', fxXmlError($lo->pcError));
      } else { 
		 $_SESSION['pcCodAlm']=$lo->pcCodAlm;
         $loSmarty->assign('scBehavior', '2');
         $loSmarty->assign('saDataPD', $lo->paDataID);
		 $loSmarty->assign('scCodAlm', $_SESSION['pcCodAlm']);
		 $loSmarty->assign('scNomAlm', $lo->pcNomAlm);
	     $loSmarty->display('Plantillas/ERP2010.tpl');
   
      }
   }
   function fxAsignar(){
      global $loSmarty;
	  $_SESSION['llFirst'] = true;
	  $loSmarty->assign('scBehavior', '1');
	  $loSmarty->assign('saCodAlm', $_SESSION['paCodAlm']);
	  $loSmarty->assign('saCodIns', $_SESSION['paCodIns']);
	  $loSmarty->display('Plantillas/ERP2010.tpl');
   }
   
   function fxAgregar(){
      global $loSmarty;
	  if ($_SESSION['llFirst']){
	     $laData = null;
	     $_SESSION['llFirst'] = false;
	  }else{
         $laData = $_SESSION['paDataP'];
	  }
	  
	  $_SESSION['pcNroDNI'] = $_REQUEST['pcNroDNI'];
	  $_SESSION['pcNroRUC'] = $_REQUEST['pcNroRUC'];
	  $_SESSION['pdFecLis'] = $_REQUEST['pdFecLis'];
	  $_SESSION['pcPriori'] = $_REQUEST['pcPriori'];
      $i = fxCount($laData) + 1;
	  $lo = new CMtoPedidos();
	  $lo->pcCodProd = $_REQUEST['pcCodProd'];
      $llOk = $lo->omProducto();
      $laData[$i] = array($lo->paDataP[0][0], $lo->paDataP[0][1], $lo->paDataP[0][2]);
	  //$laData[$i] = array($lo->paDataP[0], $lo->paDataP[1], $lo->paDataP[2]);//EMM
	  $_SESSION['paDataP']=$laData;
	  $loSmarty->assign('scTipo', $_SESSION['pcTipo']);
      $loSmarty->assign('scBehavior', '1'); 
      $loSmarty->assign('saNroDNI', $_SESSION['paNroDNI']);
	  $loSmarty->assign('scNroDNI', $_SESSION['pcNroDNI']);
	  $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
	  $loSmarty->assign('saNroRUC', $_SESSION['paNroRUC']);
	  $loSmarty->assign('sdFecLis', $_SESSION['pdFecLis']);
	  $loSmarty->assign('scNroRUC', $_SESSION['pcNroRUC']);
	  $loSmarty->assign('saPriori', $_SESSION['paPriori']);
	  $loSmarty->assign('scDirec', $_SESSION['pcDirec']);
	  $loSmarty->assign('scTelef', $_SESSION['pcTelef']);
	  $loSmarty->assign('scPriori', $_SESSION['pcPriori']);
	  $loSmarty->assign('saCodProd', $_SESSION['paCodProd']);
	  $loSmarty->assign('saDataP',$laData);
      $loSmarty->display('Plantillas/ERP2010.tpl');		           

   }
   
   function fxGrabar() {
      global $loSmarty; 
      $lo = new CMtoPedidos();
	  
	  $lo->paDataF = $_SESSION['paDataF'];
	  if($_SESSION['pcTipo']=='A')
	      $lo->pcCodCli = $_SESSION['pcNroDNI'];
	  else
	      $lo->pcCodCli = $_SESSION['pcNroRUC'];	  
	  $lo->pcTotal = $_SESSION['pcTotal'];
	  $lo->pdFecLis = $_REQUEST['pdFecLis'];
	  $lo->pcPriori = $_SESSION['pcPriori'];
	  $lo->pcCodUsu = $_SESSION['gcCodUsu'];
	  $lo->pcVV = $_SESSION['pcVV'];
	  $lo->pcIGV = $_SESSION['pcIGV'];
	  $llOk = $lo->omGrabarTabla();
      if (!$llOk) {
          fxAlert(fxXmlError($lo->pcError));
		$loSmarty->assign('scTipo', $_SESSION['pcTipo']);
      $loSmarty->assign('scBehavior', '1'); 
      $loSmarty->assign('saNroDNI', $_SESSION['paNroDNI']);
	  $loSmarty->assign('scNroDNI', $_SESSION['pcNroDNI']);
	  $loSmarty->assign('sdFecLis', $_SESSION['pdFecLis']);
	  $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
	  $loSmarty->assign('saNroRUC', $_SESSION['paNroRUC']);
	  $loSmarty->assign('scNroRUC', $_SESSION['pcNroRUC']);
	  $loSmarty->assign('saPriori', $_SESSION['paPriori']);
	  $loSmarty->assign('scPriori', $_SESSION['pcPriori']);
	  $loSmarty->assign('saCodProd', $_SESSION['paCodProd']);
	  $loSmarty->assign('saDataP',$lo->paDataF);
	  $loSmarty->assign('scTotal', $lo->pcTotal);	  
	  $loSmarty->assign('scVV', $lo->pcVV);	  
	  $loSmarty->assign('scIGV', $lo->pcIGV);	  
      $loSmarty->display('Plantillas/ERP2010.tpl');		           
      } else {
	     fxAlert('GRABACION CONFORME');
		 $lo = new CDocRTF();
		 $lo->pcCodUsu=$_SESSION['gcCodUsu'];
         $llOk = $lo->omPedido();
         if (!$llOk) {            
            fxAlert(fxXmlError($lo->pcError));
			fxHeader('ERP2010.php');
         } else {
	    	 $loSmarty->assign('scFilRet', $lo->pcFilRet);
             $loSmarty->assign('scBehavior', '2');
             $loSmarty->display('Plantillas/ERP2010.tpl');
         }	   
      }
   }
   function fxAceptar() {
      global $loSmarty; 
	  $_SESSION['pcTipo'] =$_REQUEST['pcTipo'];
	  $loSmarty->assign('scTipo', $_SESSION['pcTipo']);
      $loSmarty->assign('scBehavior', '1'); 
      $loSmarty->assign('saNroDNI', $_SESSION['paNroDNI']);
	  $loSmarty->assign('sdFecLis', $_SESSION['pdFecLis']);
	  $loSmarty->assign('saNroRUC', $_SESSION['paNroRUC']);
	  $loSmarty->assign('scPriori', $_SESSION['pcPriori']);
	  $loSmarty->assign('saPriori', $_SESSION['paPriori']);
	  $loSmarty->assign('saCodProd', $_SESSION['paCodProd']);
      $loSmarty->display('Plantillas/ERP2010.tpl');		           
      
   }
   
   function fxCalcular() {
      global $loSmarty; 
	  $lnTotal=0;
	  $laData = $_REQUEST['paDataP'];
	  $laDat=null;
	  $j=0;
	  foreach($_REQUEST['paDataP'] as $i){
	      $tot=$laData[$i[0]][2]*$laData[$i[0]][3];
		  $laDat[$j] = array($laData[$i[0]][0],$laData[$i[0]][1], $laData[$i[0]][2],$laData[$i[0]][3], $tot);
		  $lnTotal=$lnTotal+$tot;
		  $j++;
	  }
	  $_SESSION['paDataF']=$laDat;
	  $_SESSION['pcTotal']=$lnTotal;
	  $_SESSION['pcVV']=round($lnTotal/(1.18),2);
	  $_SESSION['pcIGV']=round(($lnTotal-$_SESSION['pcVV']),2);	  
	  //ACA IRA EL IGV
      $loSmarty->assign('scTipo', $_SESSION['pcTipo']);
      $loSmarty->assign('scBehavior', '1'); 
      $loSmarty->assign('saNroDNI', $_SESSION['paNroDNI']);
	  $loSmarty->assign('scNroDNI', $_SESSION['pcNroDNI']);
	  $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
	  $loSmarty->assign('saNroRUC', $_SESSION['paNroRUC']);
	  $loSmarty->assign('sdFecLis', $_SESSION['pdFecLis']);
	  $loSmarty->assign('scVV', $_SESSION['pcVV']);
	  $loSmarty->assign('scIGV', $_SESSION['pcIGV']);
	  	  $loSmarty->assign('scDirec', $_SESSION['pcDirec']);
	  $loSmarty->assign('scTelef', $_SESSION['pcTelef']);

	  $loSmarty->assign('scPriori', $_SESSION['pcPriori']);
	  $loSmarty->assign('scNroRUC', $_SESSION['pcNroRUC']);
	  $loSmarty->assign('saPriori', $_SESSION['paPriori']);
	  $loSmarty->assign('saCodProd', $_SESSION['paCodProd']);
	  $loSmarty->assign('saDataP',$laDat);
	  $loSmarty->assign('scTotal', $lnTotal);
      $loSmarty->display('Plantillas/ERP2010.tpl');		           
      
   }
   
   function fxCargar() {
      global $loSmarty; 
	  $lo = new CMtoPedidos();
	  if($_SESSION['pcTipo']=='A')
	    $lo->pcCodCli = $_REQUEST['pcNroDNI'];
	  else 
	    $lo->pcCodCli = $_REQUEST['pcNroRUC'];
      $llOk = $lo->omCargaCliente();
	  $_SESSION['pcNombre']=$lo->pcNombre;
	  $_SESSION['pcDirec']=$lo->pcDirec;
	  $_SESSION['pcTelef']=$lo->pcTelef;
	  $loSmarty->assign('scBehavior', '1'); 
	  $loSmarty->assign('scTipo', $_SESSION['pcTipo']);
	  $loSmarty->assign('saNroDNI', $_SESSION['paNroDNI']);
	  $loSmarty->assign('scNroDNI', $lo->pcCodCli);
	  $loSmarty->assign('scDirec', $lo->pcDirec);
	  $loSmarty->assign('scTelef', $lo->pcTelef);
	  $loSmarty->assign('saNroRUC', $_SESSION['paNroRUC']);
	  $loSmarty->assign('scNroRUC', $lo->pcCodCli);
	  $loSmarty->assign('sdFecLis', $_SESSION['pdFecLis']);
	  $loSmarty->assign('scPriori', $_SESSION['pcPriori']);
	  $loSmarty->assign('scNombre', $_SESSION['pcNombre']);
	  $loSmarty->assign('saPriori', $_SESSION['paPriori']);
      $loSmarty->assign('saCodProd', $_SESSION['paCodProd']);

      $loSmarty->display('Plantillas/ERP2010.tpl');		           
      
   }
?>