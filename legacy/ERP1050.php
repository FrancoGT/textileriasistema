<?php
   // Registro de insumos
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CMtoInsumos.php';   
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('ERP1050', 'Mnu0000.php?Id=1000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   }elseif (@$_POST['Boton1'] == 'Pedido') {
      fxCompra();
   } elseif (@$_POST['Boton1'] == 'Salir') {       
      $lcScript = "window.location='Mnu0000.php?Id=0000';";
      echo '<script>'.$lcScript.'</script>';      
   } elseif (@$_POST['Boton1'] == 'Editar') {
      fxEditar();
   } elseif (@$_POST['Boton1'] == 'Nuevo') {
      fxNuevo();
   } elseif (@$_POST['Boton1'] == 'Eliminar') {
      fxEliminar();   
   } elseif (@$_POST['Boton2'] == 'Grabar') {
      fxGrabar();      
   } else{      
      fxInit();      
   }
   
    function fxInit() {
      global $loSmarty;
      $lo = new CMtoInsumos();       
      $lo->pcCodPrv = @$_REQUEST['pcCodPrv'];      
      unset($_SESSION['paCodPrv']);
      unset($_SESSION['paCodPer']);
      unset($_SESSION['pcNroCom']);      
      unset($_SESSION['nuevo']);
      unset($_SESSION['paTipArs']);
      $llOk = $lo->omInitProveedores(); 
      $_SESSION['paTipArs'] = $lo->paTipArs;
      if(!$llOk){
          fxAlert($lo->pcError);
          $loSmarty->assign('scBehavior', '0');          
          $loSmarty->display('Plantillas/ERP1050.tpl'); 
      }else{
        $_SESSION['paCodPrv'] = $lo->paCodPrv;
        $_SESSION['paUniMed'] = $lo->paUniMed; 
        $_SESSION['paMoneda'] = $lo->paMoneda;             
        $_SESSION['paSubTip'] = $lo->paSubTip;
        $loSmarty->assign('saData', $lo->paData);          
        $loSmarty->assign('saCodPrv', $lo->paCodPrv);          
        $loSmarty->assign('scBehavior', '0');          
        $loSmarty->display('Plantillas/ERP1050.tpl'); 
      }
   }
      
   function fxNuevo(){
      global $loSmarty;
      $lo = new CMtoInsumos();
      $_SESSION['nuevo'] = 0;         
      $loSmarty->assign('scBehavior', '1');              
      $loSmarty->assign('saTipArs', $_SESSION['paTipArs']); 
      $loSmarty->assign('saUniMed', $_SESSION['paUniMed']); 
      $loSmarty->assign('saMoneda', $_SESSION['paMoneda']);       
      $loSmarty->assign('saSubTip', $_SESSION['paSubTip']);
      $loSmarty->display('Plantillas/ERP1050.tpl'); 
   }
   
   function fxEditar(){
      global $loSmarty;
      $lo = new CMtoInsumos();
      $lo->pcCodArs = @$_REQUEST['pcCodArs'];        
      $_SESSION['pcCodArs'] = $lo->pcCodArs;
      $_SESSION['nuevo'] = 1;
      $llOk = $lo->omInitEditar();
      if(!$llOk){
          fxInit();
          fxAlert($lo->pcError);
      }else{
          $loSmarty->assign('scBehavior', '1');
          $loSmarty->assign('scCodPrv', $_SESSION['pcCodPrv']);
          $loSmarty->assign('scNomPrv', $_SESSION['pcNomPrv']);
          $loSmarty->assign('saUniMed', $_SESSION['paUniMed']);
          $loSmarty->assign('saSubTip', $_SESSION['paSubTip']);
          $loSmarty->assign('scDescri', $lo->pcDescri);
          $loSmarty->assign('scUniMed', $lo->pcUniMed);          
          $loSmarty->assign('snPrec',   $lo->pnPrec);
          $loSmarty->assign('snCanGra', $lo->pnCanGra);
          $loSmarty->assign('saCanGra', $_SESSION['pnCanGra']);          
          $lo->paTipo = $_SESSION['paTipos'];          
          for($i=0;$lo->pcArsTip[0][$i];$i++){
              $temp = $lo->pcArsTip[0][$i];
              $lo->pcArsTip[$i][0] = $temp;           
          }
          $loSmarty->assign('scArsTip', $lo->pcArsTip);
          $loSmarty->display('Plantillas/ERP1050.tpl');  
      }
   }
  
   
   function fxAplicar() {
      global $loSmarty;
      $lo = new CMtoInsumos();      
      $lo->pcCodPrv = @$_REQUEST['pcCodPrv'];	        
      $_SESSION['pcCodPrv']=$lo->pcCodPrv;
      $llOk = $lo->omInitArtiProveedores();
      if (!$llOk) { // Error
         fxInit();
         fxAlert($lo->pcError);
      } else {         
         $_SESSION['pcNomPrv'] = $lo->pcNomPrv;         
         $loSmarty->assign('saData',   $lo->paData);         
         $loSmarty->assign('scCodPrv', $lo->pcCodPrv);
         $loSmarty->assign('scNomPrv', $lo->pcNomPrv);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/ERP1050.tpl');
      }
   }
   
   function fxEliminar(){
      global $loSmarty;
      $lo = new CMtoInsumos();      
      $lo->pcCodArs = @$_REQUEST['pcCodArs'];
      $llOk = $lo->omEliminarProducto();
      if (!$llOk) { // Error
          fxInit();
          fxAlert($lo->pcError);
      } else {         
          fxInit();
         fxAlert('ARTICULO ELIMINADO');
      }
   }
   
   function fxGrabar(){
      global $loSmarty;
      $lo = new CMtoInsumos();                        
      $lo->pcSubTip = @$_REQUEST['pcSubTip'];
      $lo->pcDescri = @$_REQUEST['pcDescri'];
      $lo->pcUniMed = @$_REQUEST['pcUniMed'];      
      $lo->pnCanGra = @$_REQUEST['pnCanGra'];      
      $lo->pnPrecio = @$_REQUEST['pnPrecio'];  
      $lo->nuevo = $_SESSION['nuevo'];
      $lo->cCoduUsu = $_SESSION['gcCodUsu'];
      $lo->pcCodArs = $_SESSION['pcCodArs'];
      $llOk = $lo->omGrabarArticulo();
      if (!$llOk) { // Error
          fxInit();
         fxAlert($lo->pcError);
      } else {         
          fxInit();
         fxAlert('ARTICULO GRABADO');
      }
   }

  
?>

