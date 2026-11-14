<?php
   // Reportes de personal
   require('libFPDF/fpdf.php');
   require_once 'class/class.ezpdf.php';
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CReportes.php';
   require_once 'Clases/CMtoPerfiles.php';
   require_once 'Clases/CConsultasXls.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG4140', 'Mnu0000.php?Id=6000'))
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
      $loSmarty->display('Plantillas/Seg4140.tpl');
   }

   function fxExcel() {
      global $loSmarty;
      $lo = new CReportes();
      $llOk = $lo->omPersonal();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         return;
      }
      $laData = $lo->paData;
      $lo = new CConsultasXls();
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->paDataLic = $laData;
      $llOk = $lo->omRepPer();
      if (!$llOk) {
         fxInit();
         fxAlert(fxXmlError($lo->pcError));
      }
      $loSmarty->assign("saData", $lo->paData); 
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->display('Plantillas/Seg4140.tpl');      
   }

   function fxReporte() {
      global $loSmarty;
      $lo = new CReportes();
      $llOk = $lo->omPersonal();
      if (!$llOk) {
         fxAlert(fxXmlError($lo->pcError));
         return;
      }
      $loPdf = &new Cezpdf('A4','landscape');
      $loPdf->selectFont('fonts/Helvetics.afm');
      $loPdf->addInfo(array('Title' => 'Gestion de Seguridad',
                            'Subject' => 'REPORTE DE PERSONAL',));
      $laTitles= array('CCODPER' => 'CODIGO',
                       'CNOMBRE' => 'NOMBRE',
                       'CDESCRI' => 'EMPRESA',
                       'CDESCAR' => 'CARGO',
                       'CDESPUE' => 'PUESTO',
                       'CDESSEC' => 'SECTOR',);
      $options = array('shadeCol'=>array(0.9, 0.9, 0.9),
                       'xOrientation'=>'left',
                       'width'=>500);
      $ldFecSis = date('Y-m-d');
      $lcTime = date('H:i:s');
      $loAll = $loPdf->openObject();
      $loPdf->saveState();
      $loPdf->setStrokeColor(0, 0, 0, 1);
      $loPdf->addText(20, 560, 10, 'SEGURIDAD OPERATIVA');
      $loPdf->addText(20, 550, 10, 'SEG4140');
      $loPdf->addText(750, 560, 10, $ldFecSis);
      $loPdf->addText(750, 550, 10, 'PAG.');
      $loPdf->ezStartPageNumbers(800, 550, 10, 'left', '{PAGENUM}', 1);
      $loPdf->addText(360, 560, 10, 'RELACION DE PERSONAL');
      $loPdf->line(20, 545, 800, 545);
      $loPdf->line(20, 30, 800, 30);
      $loPdf->addText(20, 20, 10, $lcTime);
      $loPdf->restoreState();
      $loPdf->closeObject();
      $loPdf->addObject($loAll, 'all');
      $loPdf->ezSetCmMargins(2.5, 2, 2, 2);
      $laData = null;
      foreach ($lo->paData as $laLinea) {
         $laData[] = array('CCODPER' => utf8_decode($laLinea[0]),
                           'CNOMBRE' => utf8_decode($laLinea[1]),
                           'CDESCRI' => utf8_decode($laLinea[2]),
                           'CDESCAR' => utf8_decode($laLinea[3]),
                           'CDESPUE' => utf8_decode($laLinea[4]),
                           'CDESSEC' => utf8_decode($laLinea[5]));
      }
      $loPdf->ezTable($laData, $laTitles, '', array('xOrientation'=>'center', 'width'=>500, 'fontSize'=>8,
                      'cols'=>array('CCODPER' => array('width'=>45, 'justification'=>'rigth'),
                                    'CNOMBRE' => array('width'=>250, 'justification'=>'rigth'),
                                    'CDESCRI' => array('width'=>100, 'justification'=>'left'),
                                    'CDESCAR' => array('width'=>100, 'justification'=>'left'),
                                    'CDESPUE' => array('width'=>100, 'justification'=>'left'),
                                    'CDESSEC' => array('width'=>90, 'justification'=>'left'))));
      ob_end_clean();									
      $lcFilRet = fxReadPdf($loPdf);
      $loSmarty->assign('scFilRet', $lcFilRet);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg4140.tpl');
   }
?>
