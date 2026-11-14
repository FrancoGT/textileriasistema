<?php
   // Reportes de licencias
   require('libFPDF/fpdf.php');
   require_once 'class/class.ezpdf.php';
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CReportes.php';
   require_once 'Clases/CMtoPerfiles.php';
   require_once 'Clases/CConsultasXls.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG4110', 'Mnu0000.php?Id=6000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Reporte'] == 'Reporte') {
      fxReporte();
      fxInit();
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
      $loSmarty->display('Plantillas/Seg4110.tpl');
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
      $llOk = $lo->omLicencias();
      if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
         fxInit();
         fxAlert($lcMessage);
         return;
      }
      if ($lcTipo == '1') {
         $lcFilRet = fxGenerarRep1($lo->paLinea);
      } else if ($lcTipo == '2') {
         $lcFilRet = fxGenerarRep2($lo->paLinea);
      }
      $loSmarty->assign('scFilRet', $lcFilRet);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg4110.tpl');
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
      $llOk = $lo->omLicencias();
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
         $llOk = $lo->omRepLic1();
         if (!$llOk) {
            fxInit();
            fxAlert(fxXmlError($lo->pcError));
            
         }
      } else if ($lcTipo == '2') {
         $llOk = $lo->omRepLic2();
         if (!$llOk) {
            fxInit();
            fxAlert(fxXmlError($lo->pcError));
         }
      }
      $loSmarty->assign("saData", $lo->paData); 
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->display('Plantillas/Seg4110.tpl');      
   }

   
   function fxGenerarRep1($p_aLinea) {
      $loPdf = &new Cezpdf('a4');
      //$loPdf->cezpdf->Stream(); 
      $loPdf->selectFont('fonts/Helvetics.afm');
      $loPdf->addInfo(array('Title' => 'Gestion de Seguridad',
                            'Subject' => 'REPORTE',));
      $titles= array('CCODPER' => 'CODIGO',
                     'CNOMBRE' => 'NOMBRE',
                     'CTIPLIC' => 'TIPO',
                     'CDESLIC' => 'DESCRIPCION',
                     'DFECVIG' => 'FECHA VIGENCIA',
                     'DFECFIN' => 'FECHA CADUCIDAD',);
      $options = array(
         'shadeCol'=>array(0.9,0.9,0.9),
         'xOrientation'=>'center',
         'width'=>500       
      );
      $loAll = $loPdf->openObject();
      $loPdf->saveState();
      $loPdf->setStrokeColor(0, 0, 0, 1);
      $loPdf->addText(260, 800, 10, 'PERSONAL CON');
      $loPdf->line(20, 770, 550, 770);
      $loPdf->addText(250, 780, 10, 'LICENCIAS VIGENTES');
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
         $laData[] = array('CCODPER' => utf8_decode($laLinea[0]),
                           'CNOMBRE' => utf8_decode($laLinea[1]),
                           'CTIPLIC' => utf8_decode($laLinea[2]),
                           'CDESLIC' => utf8_decode($laLinea[5]),
                           'DFECVIG' => utf8_decode($laLinea[3]),
                           'DFECFIN' => utf8_decode($laLinea[4]),);
      }
      $laData[] = array('CCODPER' => utf8_decode('*'),
                        'CNOMBRE' => utf8_decode('*'),
                        'CTIPLIC' => utf8_decode('*'),
                        'CDESLIC' => utf8_decode('*'),
                        'DFECVIG' => utf8_decode('*'),
                        'DFECFIN' => utf8_decode('*'),);
      $loPdf->ezTable($laData, $titles, '', array('xOrientation'=>'center', 'width'=>500,
                      'cols'=>array('CCODPER'=>array('width'=>55, 'justification'=>'center'),
                                    'CNOMBRE'=>array('width'=>190, 'justification'=>'left'),
                                    'CTIPLIC'=>array('width'=>40, 'justification'=>'center'),
                                    'CDESLIC'=>array('width'=>120, 'justification'=>'center'),
                                    'DFECVIG'=>array('width'=>80, 'justification'=>'center'),
                                    'DFECFIN'=>array('width'=>80, 'justification'=>'center'))));
      ob_end_clean();                           
      $lcFilRet = fxReadPdf($loPdf);
      return $lcFilRet;
   }
   
   function fxGenerarRep2($p_aLinea) {
      $loPdf = &new Cezpdf('a4');
      $loPdf->selectFont('fonts/Helvetics.afm');
      $loPdf->addInfo(array('Title' => 'Gestion de Seguridad',
                            'Subject' => 'REPORTE',));
      $titles= array('CCODPER' => 'CODIGO',
                     'CNOMBRE' => 'NOMBRE',
                     'CTIPLIC' => 'TIPO',
                     'CDESCRI' => 'DESCRIPCION',
                     'DFECVEN' => 'FECHA VENCIMIENTO',);
      $options = array(
         'shadeCol'=>array(0.9,0.9,0.9),
         'xOrientation'=>'center',
         'width'=>500       
      );
      $loAll = $loPdf->openObject();
      $loPdf->saveState();
      $loPdf->setStrokeColor(0, 0, 0, 1);
      $loPdf->addText(260, 800, 10, 'PERSONAL CON');
      $loPdf->line(20, 770, 550, 770);
      $loPdf->addText(250, 780, 10, 'LICENCIAS VENCIDAS');
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
         $laData[] = array('CCODPER' => utf8_decode($laLinea[0]),
                           'CNOMBRE' => utf8_decode($laLinea[1]),
                           'CTIPLIC' => utf8_decode($laLinea[2]),
                           'CDESCRI' => utf8_decode($laLinea[4]),
                           'DFECVEN' => utf8_decode($laLinea[3]),);
      }
      $loPdf->ezTable($laData, $titles, '', array('xOrientation'=>'center', 'width'=>500,
                      'cols'=>array('CCODPER'=>array('width'=>55, 'justification'=>'center'),
                                    'CNOMBRE'=>array('width'=>190, 'justification'=>'left'),
                                    'CTIPLIC'=>array('width'=>40, 'justification'=>'center'),
                                    'CDESCRI'=>array('width'=>120, 'justification'=>'center'),
                                    'DFECVEN'=>array('width'=>80, 'justification'=>'center'))));
      ob_end_clean();                           
      fxReadPdf($loPdf);
      $lcFilRet = fxReadPdf($loPdf);
      return $lcFilRet;
   }
?>
