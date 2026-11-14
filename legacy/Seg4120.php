<?php
   // Reportes de cursos
   require('libFPDF/fpdf.php');
   require_once 'class/class.ezpdf.php';
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CConsultasXls.php';
   require_once 'Clases/CReportes.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG4120', 'Mnu0000.php?Id=6000'))
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
      $loSmarty->display('Plantillas/Seg4120.tpl');
   }
   
   function fxReporte() {
      global $loSmarty;
      $lcTipo = $_REQUEST['pcTipo'];
      if (empty($lcTipo)) {
         fxInit();
         return;
      }
      $lo = new CReportes();
      $lo->pcTipo = $lcTipo;
      $llOk = $lo->omCursos();
      if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
         fxInit();
         fxAlert($lcMessage);
         return;
      }
      if ($lcTipo == '1') {
         $lcFilRet = fxGenerarRep1($lo->paLinea);
      } elseif ($lcTipo == '2') {
         $lcFilRet = fxGenerarRep2($lo->paLinea);
      }
      $loSmarty->assign('scFilRet', $lcFilRet);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg4120.tpl');
   }

   function fxExcel() {
      global $loSmarty;
      $lcTipo = $_REQUEST['pcTipo'];
      if (empty($lcTipo)) {
         fxInit();
         return;
      }
      $lo = new CReportes();
      $lo->pcTipo = $lcTipo;
      $llOk = $lo->omCursos();
      if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
         fxInit();
         fxAlert($lcMessage);
         return;
      }
      $laData = $lo->paLinea;
      $lo = new CConsultasXls();
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->paDataLic = $laData;
      if ($lcTipo == '1') {
         $llOk = $lo->omRepCur1();
         if (!$llOk) {
            fxInit();
            fxAlert(fxXmlError($lo->pcError));
            
         }
      } else if ($lcTipo == '2') {
         $llOk = $lo->omRepCur2();
         if (!$llOk) {
            fxInit();
            fxAlert(fxXmlError($lo->pcError));
         }
      }
      $loSmarty->assign("saData", $lo->paData); 
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->display('Plantillas/Seg4120.tpl');      
   }
   
   function fxGenerarRep1($p_aLinea) {
      $loPdf = new Cezpdf('a4');
      $loPdf->selectFont('fonts/Helvetics.afm');
      $loPdf->addInfo(array('Title' => 'Gestion de Seguridad',
                            'Subject' => 'REPORTE',));
      $titles= array('CCODPER' => 'CODIGO',
                     'CNOMBRE' => 'NOMBRE',
                     'NNOTA' => 'NOTA',);
      $options = array('shadeCol'=>array(0.9,0.9,0.9),
                       'xOrientation'=>'center',
                       'width'=>500);
      $loAll = $loPdf->openObject();
      $loPdf->saveState();
      $loPdf->setStrokeColor(0, 0, 0, 1);
      $loPdf->addText(260, 800, 10, 'REPORTE DE CURSOS');
      $loPdf->line(20, 770, 550, 770);
      $loPdf->addText(250, 780, 10, 'CURSO - PERSONAL');
      $loPdf->line(20, 30, 550, 30);
      $loPdf->addText(490, 18, 10, 'PAGINA');
      $loPdf->restoreState();
      $loPdf->closeObject();
      $loPdf->addObject($loAll, 'all');
      $loPdf->ezSetCmMargins(2.8, 4, 1.5, 1.5);
//      $loPdf->ezStartPageNumbers(560, 19, 11, $posicion, $formato, 1);
      $loPdf->ezStartPageNumbers(560, 19, 11, null, null, 1);
      $lcCodCur = '*';
      $laData = null;
      foreach ($p_aLinea as $laLinea) {
         if ($lcCodCur == '*') {
            $loPdf->ezText($laLinea[0].' '.$laLinea[2].' '. trim($laLinea[1]));
            $lcCodCur = $laLinea[0];
         } else if ($lcCodCur != $laLinea[0]) {
            $loPdf->ezTable($laData, $titles, '', array('xOrientation'=>'center', 'width'=>500,
                            'cols'=>array('CCODPER'=>array('width'=>55, 'justification'=>'center'),
                                          'CNOMBRE'=>array('width'=>230, 'justification'=>'left'),
                                          'NNOTA'=>array('width'=>40, 'justification'=>'center'))));
            $loPdf->ezText($laLinea[0].' '.$laLinea[2].' '. trim($laLinea[1]));
            $lcCodCur = $laLinea[0];
            $laData = null;
         }
         $laData[] = array('CCODPER' => utf8_decode($laLinea[3]),
                           'CNOMBRE' => utf8_decode($laLinea[4]),
                           'NNOTA' => utf8_decode($laLinea[5]),);
      }
      $loPdf->ezTable($laData, $titles, '', array('xOrientation'=>'center', 'width'=>500,
                      'cols'=>array('CCODPER'=>array('width'=>55, 'justification'=>'center'),
                                    'CNOMBRE'=>array('width'=>230, 'justification'=>'left'),
                                    'NNOTA'=>array('width'=>40, 'justification'=>'center'))));
      //$loPdf->ezStream();
      ob_end_clean();
      $lcFilRet = fxReadPdf($loPdf);
      return $lcFilRet;
   }
   
   function fxGenerarRep2($p_aLinea) {
      $loPdf = new Cezpdf('a4');
      $loPdf->selectFont('fonts/Helvetics.afm');
      $loPdf->addInfo(array('Title' => 'Gestion de Seguridad',
                            'Subject' => 'REPORTE',));
      $titles= array('CCODCUR' => 'CURSO',
                     'DFECHA' => 'FECHA',
                     'CDESCRI' => 'DESCRIPCION',
                     'NNOTA' => 'NOTA',);
      $options = array('shadeCol'=>array(0.9,0.9,0.9),
                       'xOrientation'=>'center',
                       'width'=>500       
      );
      $loAll = $loPdf->openObject();
      $loPdf->saveState();
      $loPdf->setStrokeColor(0, 0, 0, 1);
      $loPdf->addText(260, 800, 10, 'REPORTE DE CURSOS');
      $loPdf->line(20, 770, 550, 770);
      $loPdf->addText(250, 780, 10, 'PERSONAL POR CURSO');
      $loPdf->line(20, 30, 550, 30);
      $loPdf->addText(490, 18, 10, 'PAGINA');
      $loPdf->restoreState();
      $loPdf->closeObject();
      $loPdf->addObject($loAll, 'all');
      $loPdf->ezSetCmMargins(2.8, 4, 1.5, 1.5);
//      $loPdf->ezStartPageNumbers(560, 19, 11, $posicion, $formato, 1);
      $loPdf->ezStartPageNumbers(560, 19, 11, null, null, 1);
      $lcCodPer = '*';
      $laData = null;
      foreach ($p_aLinea as $laLinea) {
         if ($lcCodPer == '*') {
            $loPdf->ezText('');
            $loPdf->ezText($laLinea[0].' '.$laLinea[1]);
            $loPdf->ezText('');
            $lcCodPer = $laLinea[0];
         } else if ($lcCodPer != $laLinea[0]) {
            $loPdf->ezTable($laData, $titles, '', array('xOrientation'=>'center', 'width'=>500,
                            'cols'=>array('CCODCUR'=>array('width'=>50, 'justification'=>'center'),
                                          'DFECHA'=>array('width'=>80, 'justification'=>'center'),
                                          'CDESCRI'=>array('width'=>240, 'justification'=>'left'),
                                          'NNOTA'=>array('width'=>40, 'justification'=>'center'))));
            $loPdf->ezText('');
            $loPdf->ezText($laLinea[0].' '. trim($laLinea[1]));
            $loPdf->ezText('');
            $lcCodPer = $laLinea[0];
            $laData = null;
         }
         $laData[] = array('CCODCUR' => utf8_decode($laLinea[2]),
                           'DFECHA' => utf8_decode($laLinea[4]),
                           'CDESCRI' => utf8_decode($laLinea[3]),
                           'NNOTA' => utf8_decode($laLinea[5]),);
      }
      $loPdf->ezTable($laData, $titles, '', array('xOrientation'=>'center', 'width'=>500,
                      'cols'=>array('CCODCUR'=>array('width'=>50, 'justification'=>'center'),
                                    'DFECHA'=>array('width'=>80, 'justification'=>'center'),
                                    'CDESCRI'=>array('width'=>240, 'justification'=>'left'),
                                    'NNOTA'=>array('width'=>40, 'justification'=>'center'))));
      ob_end_clean();
      $lcFilRet = fxReadPdf($loPdf);
      return $lcFilRet;
   }
?>
