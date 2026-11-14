<?php
   // Reportes de cursos
   require('libFPDF/fpdf.php');
   require_once 'class/class.ezpdf.php';
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CReportes.php';
   require_once 'Clases/CMtoPerfiles.php';
   require_once 'Clases/CConsultasXls.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG4130', 'Mnu0000.php?Id=6000'))
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
      $loSmarty->display('Plantillas/Seg4130.tpl');
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
      $llOk = $lo->omSanciones();
      if (!$llOk) {
         fxInit();
         fxAlert(fxXmlError($lo->pcError));
         return;
      }
      if ($lcTipo == '1') {
         $lcFilRet = fxGenerarRep1($lo->paLinea);
      } elseif ($lcTipo == '2') {
         $lcFilRet = fxGenerarRep2($lo->paLinea);
      }
      $loSmarty->assign('scFilRet', $lcFilRet);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg4130.tpl');
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
      $llOk = $lo->omSanciones();
      if (!$llOk) {
         fxInit();
         fxAlert(fxXmlError($lo->pcError));
         return;
      }
      $laData = $lo->paLinea;
      $lo = new CConsultasXls();
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $lo->paDataLic = $laData;
      if ($lcTipo == '1') {
         $llOk = $lo->omRepSan1();
         if (!$llOk) {
            fxInit();
            fxAlert(fxXmlError($lo->pcError));
            
         }
      } else if ($lcTipo == '2') {
         $llOk = $lo->omRepSan2();
         if (!$llOk) {
            fxInit();
            fxAlert(fxXmlError($lo->pcError));
         }
      }
      $loSmarty->assign("saData", $lo->paData); 
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->display('Plantillas/Seg4130.tpl');      
   }
   
   function fxGenerarRep1($p_aLinea) {
      $loPdf = &new Cezpdf('a4');
      $loPdf->selectFont('fonts/Helvetics.afm');
      $loPdf->addInfo(array('Title' => 'Gestion de Seguridad',
                            'Subject' => 'REPORTE',));
      $laTitles= array('CCODPER' => 'CODIGO',
                       'CNOMBRE' => 'NOMBRE',);
      $options = array('shadeCol'=>array(0.9,0.9,0.9),
                       'xOrientation'=>'center',
                       'width'=>500);
      $ldFecSis = date('Y-m-d');
      $lcTime = date('H:i:s');
      $loAll = $loPdf->openObject();
      $loPdf->saveState();
      $loPdf->setStrokeColor(0, 0, 0, 1);
      $loPdf->addText(20, 810, 10, 'GESTION DE SEGURIDAD');
      $loPdf->addText(20, 800, 10, 'SEG4130');
      $loPdf->addText(500, 810, 10, $ldFecSis);
      $loPdf->addText(500, 800, 10, 'PAG.');
      $loPdf->ezStartPageNumbers(550, 800, 10, 'left', '{PAGENUM}', 1);
      $loPdf->addText(260, 810, 10, 'REPORTE DE SANCIONES');
      $loPdf->line(20, 790, 550, 790);
      $loPdf->line(20, 30, 550, 30);
      $loPdf->addText(20, 20, 10, $lcTime);      
      $loPdf->restoreState();
      $loPdf->closeObject();
      $loPdf->addObject($loAll, 'all'); 
      $loPdf->ezSetCmMargins(1.5, 1.5, 1.5, 1.5);
      $lcCodSan = '*';
      $laData = null;
      foreach ($p_aLinea as $laLinea) {
         if ($lcCodSan == '*') {
            $loPdf->ezText('');
            $loPdf->ezText(utf8_decode('* SANCIÓN: ').$laLinea[0].' - '.$laLinea[2].' - '. trim($laLinea[1]));
            $loPdf->ezText('');
            $lcCodSan = $laLinea[0];
         } else if ($lcCodSan != $laLinea[0]) {
            $loPdf->ezTable($laData, $laTitles, '', array('xOrientation'=>'left', 'width'=>500,
                            'cols'=>array('CCODPER'=>array('width'=>55, 'justification'=>'center'),
                                          'CNOMBRE'=>array('width'=>230, 'justification'=>'left'))));
            $loPdf->ezText('');
            $loPdf->ezText(utf8_decode('* SANCIÓN: ').$laLinea[0].' - '.$laLinea[2].' - '. trim($laLinea[1]));
            $loPdf->ezText('');
            $lcCodSan = $laLinea[0];
            $laData = null;
         }
         $laData[] = array('CCODPER' => utf8_decode($laLinea[3]),
                           'CNOMBRE' => utf8_decode($laLinea[4]),);
      }
      $loPdf->ezTable($laData, $laTitles, '', array('xOrientation'=>'left', 'width'=>500,
                      'cols'=>array('CCODPER'=>array('width'=>55, 'justification'=>'center'),
                                    'CNOMBRE'=>array('width'=>230, 'justification'=>'left'))));
      ob_end_clean();									
      $lcFilRet = fxReadPdf($loPdf);
      return $lcFilRet;
   }
   
   function fxGenerarRep2($p_aLinea) {
      $loPdf = &new Cezpdf('a4');
      $loPdf->selectFont('fonts/Helvetics.afm');
      $loPdf->addInfo(array('Title' => 'Gestion de Seguridad',
                            'Subject' => 'REPORTE',));
      $laTitles= array('CCODSAN' => 'SANCION',
                     'DFECHA' => 'FECHA',
                     'CCODPER' => 'CODIGO',
                     'CNOMBRE' => 'NOMBRE',);
      $options = array('shadeCol'=>array(0.9,0.9,0.9),
                       'xOrientation'=>'center',
                       'width'=>500);
      $loAll = $loPdf->openObject();
      $loPdf->saveState();
      $loPdf->setStrokeColor(0, 0, 0, 1);
      $loPdf->addText(260, 800, 10, 'REPORTE DE SANCIONES');
      $loPdf->line(20, 770, 550, 770);
      $loPdf->addText(250, 780, 10, 'POR NORMA');
      $loPdf->line(20, 30, 550, 30);
      $loPdf->addText(490, 18, 10, 'PAGINA');
      $loPdf->restoreState();
      $loPdf->closeObject();
      $loPdf->addObject($loAll, 'all'); 
      $loPdf->ezSetCmMargins(2.8, 4, 1.5, 1.5);
//      $loPdf->ezStartPageNumbers(560, 19, 11, $posicion, $formato, 1);
      $loPdf->ezStartPageNumbers(560, 19, 11, null, null, 1);
      $lcCodNor = '*';
      $laData = null;
      foreach ($p_aLinea as $laLinea) {
         if ($lcCodNor == '*') {
            $loPdf->ezText('');
            $loPdf->ezText($laLinea[0].' '.$laLinea[1]);
            $loPdf->ezText('');
            $lcCodNor = $laLinea[0];
         } else if ($lcCodNor != $laLinea[0]) {
            $loPdf->ezTable($laData, $laTitles, '', array('xOrientation'=>'center', 'width'=>500,
                            'cols'=>array('CCODSAN'=>array('width'=>65, 'justification'=>'center'),
                                          'DFECHA'=>array('width'=>65, 'justification'=>'center'),
                                          'CCODPER'=>array('width'=>55, 'justification'=>'center'),
                                          'CNOMBRE'=>array('width'=>230, 'justification'=>'left'))));
            $loPdf->ezText('');
            $loPdf->ezText($laLinea[0].' '.$laLinea[1]);
            $loPdf->ezText('');
            $lcCodNor = $laLinea[0];
            $laData = null;
         }
         $laData[] = array('CCODSAN' => utf8_decode($laLinea[2]),
                           'DFECHA' => utf8_decode($laLinea[5]),
                           'CCODPER' => utf8_decode($laLinea[3]),
                           'CNOMBRE' => utf8_decode($laLinea[4]),);
      }
      $loPdf->ezTable($laData, $laTitles, '', array('xOrientation'=>'center', 'width'=>500,
                      'cols'=>array('CCODSAN'=>array('width'=>65, 'justification'=>'center'),
                                    'DFECHA'=>array('width'=>65, 'justification'=>'center'),
                                    'CCODPER'=>array('width'=>55, 'justification'=>'center'),
                                    'CNOMBRE'=>array('width'=>230, 'justification'=>'left'))));
      ob_end_clean();									
      $lcFilRet = fxReadPdf($loPdf);
      return $lcFilRet;
}
?>
