<?php
   // Registro de comprobantes
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoProveedores.php';require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP1000', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   }elseif (@$_POST['Boton0'] == 'Salir') {       
      $lcScript = "window.location='Mnu0000.php?Id=0000';";
      echo '<script>'.$lcScript.'</script>';   
   }else if (@$_POST['Boton0'] == 'Escoger') {
      fxEscoger();   
   }else if (@$_POST['Boton0'] == 'Editar') {
      fxEditar();   
   }else if (@$_POST['Boton0'] == 'Nuevo') {
      fxNuevo();
   }else if (@$_POST['Boton1'] == 'Grabar') {
      fxGrabar();
   }else if (@$_POST['Boton3'] == 'Grabar') {
      fxGrabarPersonalProveedor();
   }else{
      fxInit();      
   }
   
   function fxInit() {
      global $loSmarty;      
      $lo = new CMtoProveedores();
      $lo->omInitProveedores();      
      unset($_SESSION['paEstado']);
      unset($_SESSION['paSexo']);
      unset($_SESSION['pcCodPrv']);
      unset($_SESSION['paLinPro']);      
      unset($_SESSION['paTipPer']);            
      $_SESSION['paEstado'] = $lo->paEstado;
      $_SESSION['pcCodPrv'] = $lo->pcCodPrv;
      $_SESSION['paSexo'] = $lo->paSexo;
      $_SESSION['paLinPro'] = $lo->paLinPro;
      $_SESSION['paTipos'] = $lo->paTipos;
      $_SESSION['paTipPer'] = $lo->paTipPer;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('saData', $lo->paData);      
      $loSmarty->display('Plantillas/ERP1060.tpl');       
   }
   
      
   function fxEscoger(){
      global $loSmarty;                        
      $_SESSION['nuevo']=0;
      $lo = new CMtoProveedores();
      $lo->pcCodPrv = $_REQUEST['pcCodPrv'];                  
      $llOk = $lo->omInitPersonal();
      $loSmarty->assign('scBehavior', '1');      
      $loSmarty->assign('saCodPer', $lo->paCodPer);      
      $loSmarty->assign('saEstado', $_SESSION['paEstado']);
      $loSmarty->assign('saTipos',  $_SESSION['paTipos']);
      $loSmarty->assign('scCodPrv', $_SESSION['pcCodPrv']);            
      $loSmarty->display('Plantillas/ERP1060.tpl'); 
   }
   
   function fxNuevo(){
      global $loSmarty;            
      $loSmarty->assign('scBehavior', '1');      
      $_SESSION['nuevo']=0;            
      $_SESSION['flag']='nuevo';
      $loSmarty->assign('saSexo',   $_SESSION['paSexo']);
      $loSmarty->assign('saLinPro', $_SESSION['paLinPro']);
      $loSmarty->assign('saTipos',  $_SESSION['paTipos']);
      $loSmarty->assign('saTipPer',  $_SESSION['paTipPer']);
      $loSmarty->assign('flag',  $_SESSION['flag']);
      $loSmarty->display('Plantillas/ERP1060.tpl'); 
   }
   
   function fxProveedor(){
      global $loSmarty;      
      $lo = new CMtoCompras();
      $llOk = $lo->omInitProveedor();
      if (!$llOk){
          fxAlert ($lo->pcError);
          fxInit();
      }else{
          $loSmarty->assign('scBehavior', '4');    
          $loSmarty->assign('saEstado',   $lo->paEstado);        
          $loSmarty->display('Plantillas/ERP1060.tpl');  
      }
   }
   
   function fxAñadir() {
      global $loSmarty;
      $lo = new CMtoCompras();            
      $lo->pcCodPrv = $_REQUEST['pcCodPrv'];                  
      $lo->pcCodArS   = $_REQUEST['pcCodArS'];                  
      $lo->pcNroCom = $_REQUEST['pcNroCom'];                       
      $lo->pcCodCom = $_REQUEST['pcCodCom'];
      $lo->pnCantidad = $_REQUEST['pnCantidad'];      
      $llOk = $lo->omAñadeArticulo();             
      if(!$llOk){          
          fxAlert($lo->pcError);              
      }
      fxAplicar();
   }
   
         
   function fxEliminar(){
      global $loSmarty;
      $lo = new CMtoProveedores();      
      $lo->pcCodPrv = @$_REQUEST['pcCodPrv'];
      $llOk = $lo->omEliminarProveedor();
      if (!$llOk) { // Error         
         fxInit();
         fxAlert($lo->pcError);
         
      } else {         
         fxInit();
         fxAlert('PROVEEDOR ELIMINADO');
      }
   }
   
   
   function fxGrabar(){
      global $loSmarty;
      $lo = new CMtoProveedores();            
      $lo->pcCodPrv = @$_REQUEST['pcCodPrv'];
      $lo->pcNombre = @$_REQUEST['pcNombre'];            
      $lo->pcNroDni = @$_REQUEST['pcNroDni'];
      $lo->pcTipPer = @$_REQUEST['pcTipPer'];
      $lo->pcNroRuc = @$_REQUEST['pcNroRuc'];
      $lo->pcDirecc = @$_REQUEST['pcDirecc'];
      $lo->pdFecNac = @$_REQUEST['pdFecNac'];
      $lo->pcEstado = @$_REQUEST['pcEstado'];
      $lo->paTipArs = @$_REQUEST['paTipArs'];
      $lo->pcTexCon = @$_REQUEST['pcTexCon'];            
      $lo->nuevo = $_SESSION['nuevo'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omGrabarProveedor();
      if (!$llOk) { // Error         
         fxAlert($lo->pcError);
         fxInit();
      } else {                  
         fxAlert('PROVEEDOR GRABADO');
         fxInit();
      }
   }
   
   function fxGrabarPersonalProveedor(){
      global $loSmarty;
      $lo = new CMtoProveedores();      
      $lo->pcNombre = @$_REQUEST['pcNombre'];            
      $lo->pcSexo   = @$_REQUEST['pcSexo'];
      $lo->pcNroDni = @$_REQUEST['pcNroDni'];
      $lo->pcNroRuc = @$_REQUEST['pcNroRuc'];      
      $lo->pcTipPer = @$_REQUEST['pcTipPer'];      
      $lo->pcDirecc = @$_REQUEST['pcDirecc'];
      $lo->pcTexCon = @$_REQUEST['pcTexCon'];      
      $lo->paTipArs = @$_REQUEST['paTipArs'];
      $lo->pdFecNac = @$_REQUEST['pdFecNac'];
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];      
      $lo->nuevo  = $_SESSION['nuevo'];           
      $llOk = $lo->omGrabarProveedor();
      if (!$llOk) { // Error         
         fxAlert($lo->pcError);
         fxInit();
      } else {                  
         fxAlert('PROVEEDOR GRABADO');
         fxInit();
      } 
   }
   
   function fxEditar(){
       global $loSmarty;
       $lo = new CMtoProveedores();
       $lo->pcCodPrv = $_REQUEST['pcCodPrv'];                     
       $_SESSION['nuevo']=1;
       $_SESSION['flag']='editar';
       $llOk = $lo->omEditarProveedor();
       if(!$llOk){
           fxInit();
           fxAlert($lo->pcError);
       }else{
           $loSmarty->assign('scCodPrv', $lo->pcCodPrv);      
           $loSmarty->assign('scNombre', $lo->pcNombre);                            
           $loSmarty->assign('scTexCon', $lo->pcTexCon);
           $loSmarty->assign('scNroDni', $lo->pcNroDni);
           $loSmarty->assign('scNroRuc', $lo->pcNroRuc);
           $loSmarty->assign('sdFecNac', $lo->pdFecNac);
           $loSmarty->assign('scDirecc', $lo->pcDirecc);           
           $loSmarty->assign('saTipos' , $_SESSION['paTipos']);
           $loSmarty->assign('saTipPer',  $_SESSION['paTipPer']);
           $loSmarty->assign('flag',  $_SESSION['flag']);
           $loSmarty->assign('saArsTip', $lo->paTipArs);           
           $loSmarty->assign('scBehavior', 1);      
           $loSmarty->display('Plantillas/ERP1060.tpl');       
       }
       
   }
   
?>