<?php
   // Registro y mantenimiento de personal operativo
   require('libFPDF/fpdf.php');
   require_once 'Libs/Smarty.class.php';
   require_once 'class/class.ezpdf.php';
   require_once 'Clases/CMtoDespacho.php';
   require_once 'Clases/CBase.php';    
   
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   /*if (!fxValidarPermiso('ERP9010', 'Mnu0000.php?Id=1000'))
      return;*/
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } elseif ($_POST['Boton0'] == 'Salir') {
      fxHeader('Mnu0000.php?Id=1000');
   } elseif ($_POST['Boton0'] == 'Aplicar') {
      fxAplicar();      
   } else {
       unset($_SESSION['lcFilRet']);
       fxInit();
   }
   
   function fxInit() {
       $lo = new CMtoDespacho();
      $llOk = $lo->omInitPedidosRep1();
      if (!$llOk) { // Error
         fxHeader('Mnu0000.php?Id=1000', fxXmlError($lo->pcError));
      } else {          
         $_SESSION['paDataPed'] = $lo->paDataPed;
         fxScreen();
      }
   }
   
   function fxScreen() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      if($_SESSION['lcFilRet']){
          $loSmarty->assign('scFilRet', $_SESSION['lcFilRet']);          
      }
      $loSmarty->assign('saDataPed', $_SESSION['paDataPed']);
      $loSmarty->display('Plantillas/ERP9010.tpl');
   }
   
   function fxAplicar() {
      global $loSmarty;
      $lo = new CMtoDespacho();
      $lo->pcCodPed = $_REQUEST['pcCodPed'];
      $lo->pcEstado = $_REQUEST['scEstado'];
      $_SESSION['pcCodPed'] = $lo->pcCodPed;
      $llOk = $lo->omRealizaRecepcion();  
      $lcFilRet  = fxPDF($lo->paData);
      $_SESSION['lcFilRet'] = $lcFilRet;
      if (!$llOk) { // Error
	 fxAlert(fxXmlError($lo->pcError));
         fxScreen();
      } else {
          fxAlert($lo->pcMesg);
          fxInit();
      }
       
   }
   
   function fxPDF($p_aLinea){
       $loPdf = new Cezpdf('a4');       
      //$loPdf->cezpdf->Stream(); 
      $loPdf->selectFont('fonts/Helvetics.afm');
      $loPdf->addInfo(array('Title' => 'Reporte de Pedidos',
                            'Subject' => 'REPORTE',));
      $titles= array('CCODPED' => 'PEDIDO',
                     'CCODPRO' => 'CODIGO',
                     'CDESCRI' => 'NOMBRE',
                     'NCANT' => 'CANTIDAD',
                     'DFECLIS' => 'FECHA DE TERMINO',);
      $options = array(
         'shadeCol'=>array(0.9,0.9,0.9),
         'xOrientation'=>'center',
         'width'=>500       
      );
      $loAll = $loPdf->openObject();
      $loPdf->saveState();
      $loPdf->setStrokeColor(0, 0, 0, 1);
      $loPdf->addText(220, 800, 10, 'REPORTE DE PEDIDOS');
      $loPdf->line(20, 770, 550, 770);      
      $loPdf->line(20, 30, 550, 30);
      $loPdf->addText(490, 18, 10, 'PAGINA');
      $loPdf->restoreState();
      $loPdf->closeObject();
      $loPdf->addObject($loAll, 'all'); 
      $loPdf->ezSetCmMargins(2.8, 4, 1.5, 1.5);
//      $loPdf->ezStartPageNumbers(560, 19, 11, $posicion, $formato, 1);
      $loPdf->ezStartPageNumbers(560, 19, 11, null, null, 1);
      $llFirst = true;
      $laData = null;
      foreach ($p_aLinea as $laLinea) {
         $laData[] = array('CCODPED' => utf8_decode($laLinea[0]),
                           'CCODPRO' => utf8_decode($laLinea[1]),
                           'CDESCRI' => utf8_decode($laLinea[2]),
                           'NCANT'   => utf8_decode($laLinea[3]),
                           'DFECLIS' => utf8_decode($laLinea[4]),);
      }
      $laData[] = array('CCODPED' => utf8_decode('*'),
                        'CCODPRO' => utf8_decode('*'),
                        'CDESCRI' => utf8_decode('*'),
                        'NCANT' => utf8_decode('*'),
                        'DFECLIS' => utf8_decode('*'),);
      $loPdf->ezTable($laData, $titles, '', array('xOrientation'=>'center', 'width'=>100,
                      'cols'=>array('CCODPED'=>array('width'=>55, 'justification'=>'center'),
                                    'CCODPED'=>array('width'=>80, 'justification'=>'left'),
                                    'CCODPRO'=>array('width'=>80, 'justification'=>'left'),
                                    'CDESCRI'=>array('width'=>200, 'justification'=>'left'),
                                    'NCANT'=>array('width'=>70, 'justification'=>'center'),
                                    'DFECLIS'=>array('width'=>80, 'justification'=>'center'))));
      ob_end_clean();                           
      $lcFilRet = fxReadPdf($loPdf);
      return $lcFilRet;
   }   
   
   
   
?>