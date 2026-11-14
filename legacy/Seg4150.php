<?php
   // Reporte de normas
   require('libFPDF/fpdf.php');
   require_once 'class/class.ezpdf.php';
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CReportes.php';
   require_once 'Clases/CMtoPerfiles.php';
   require_once 'Clases/CConsultasXls.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG4150', 'Mnu0000.php?Id=6000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Reporte'] == 'Reporte') {
      fxReporte();
   } else if (@$_POST['Excel'] == 'Excel') {
      fxExcel();
      fxInit();
   } elseif (@$_POST['Boton4'] == 'Cancelar') {
      fxInit();
   } else {
      fxInit();
   }
  
   function fxInit() {
      global $loSmarty;
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg4150.tpl');
   }
   
   function fxExcel() {
      global $loSmarty;
      $lo = new CReportes();
      $llOk = $lo->omNormas();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         return;
      }
      $laData = $lo->paData;
      $lo = new CConsultasXls();
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->paDataLic = $laData;
      $llOk = $lo->omRepNor();
      if (!$llOk) {
         fxInit();
         fxAlert(fxXmlError($lo->pcError));
      }
      $loSmarty->assign("saData", $lo->paData); 
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->display('Plantillas/Seg4150.tpl');      
   }
   
   function fxReporte() {
      global $loSmarty;
      $lo = new CReportes();
      $llOk = $lo->omNormas();
      if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
         fxInit();
         fxAlert($lcMessage);
         return;
      }
      //print_r($lo->paData); die();
      $loPdf = new Cezpdf('A4', 'portrait');
      $loPdf->selectFont('fonts/Helvetics.afm');
      $loPdf->addInfo(array('Title' => 'Gestion de Seguridad',
                            'Subject' => 'REPORTE DE NORMAS',));
      $laTitles= array('CCODNOR' => 'CODIGO',
                       'CTIPO'   => 'TIPO',
                       'CDESCRI' => 'DESCRIPCION',);
      $options = array('shadeCol'=>array(0.9, 0.9, 0.9),
                       'xOrientation'=>'left',
                       'width'=>500);
      $ldFecSis = date('Y-m-d');
      $lcTime = date('H:i:s');
      $loAll = $loPdf->openObject();
      $loPdf->saveState();
      $loPdf->setStrokeColor(0, 0, 0, 1);
      $loPdf->addText(20, 800, 10, 'SEGURIDAD OPERATIVA');
      $loPdf->addText(20, 790, 10, 'SEG4150');
      $loPdf->addText(530, 790, 10, $ldFecSis);
      $loPdf->addText(530, 800, 10, 'PAG.');
      $loPdf->ezStartPageNumbers(580, 800, 10, 'left', '{PAGENUM}', 1);
      $loPdf->addText(260, 800, 10, 'RELACION DE NORMAS');
      $loPdf->line(20, 780, 580, 780);
      $loPdf->line(20, 30, 800, 30);
      $loPdf->addText(20, 20, 10, $lcTime);      
      $loPdf->restoreState();
      $loPdf->closeObject();
      $loPdf->addObject($loAll, 'all'); 
      $loPdf->ezSetCmMargins(2.5, 2, 2, 2);
      $laData = null;
      foreach ($lo->paData as $laLinea) {
         $laData[] = array('CCODNOR' => utf8_decode($laLinea[0]),
                           'CTIPO'   => utf8_decode($laLinea[1]),
                           'CDESCRI' => utf8_decode($laLinea[2]));
      }
      $loPdf->ezTable($laData, $laTitles, '', array('xOrientation'=>'center', 'width'=>500, 'fontSize'=>8,
                      'cols'=>array('CCODNOR' => array('width'=>70, 'justification'=>'left'),
                                    'CTIPO'   => array('width'=>30, 'justification'=>'center'),
                                    'CDESCRI' => array('width'=>400, 'justification'=>'left'))));
      ob_end_clean();									
      $lcFilRet = fxReadPdf($loPdf);
      $loSmarty->assign('scFilRet', $lcFilRet);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg4150.tpl');
   }
?>