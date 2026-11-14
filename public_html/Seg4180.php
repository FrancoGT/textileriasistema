<?php
   // Reportes de evaluaciones
   require('libFPDF/fpdf.php');
   require_once 'class/class.ezpdf.php';
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CConsultas.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG4180', 'Mnu0000.php?Id=6000'))
      return;
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Reporte'] == 'Reporte') { //PPP
      fxReporte();
   } else if (@$_POST['Volver'] == 'Volver') {
      fxVolver();
   } else if (@$_POST['Consultar'] == 'Consultar') {
      fxConsultar();
   } else if (@$_POST['Aplicar'] == 'Aplicar') {
      fxAplicar();
   } else {
      fxInit();
   }
  
   function fxInit() {
      global $loSmarty;
      $lo = new CConsultas();
      $llOk = $lo->omInitEvaluaciones();
      if (!$llOk) {
         fxHeader('Mnu0000.php?Id=6000', fxXmlError($lo->pcError));
         return;
      }
      $_SESSION['paTipEva'] = $lo->paTipEva;
      $loSmarty->assign('saTipEva', $_SESSION['paTipEva']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg4180.tpl');
   }

   function fxAplicar() {
      global $loSmarty;
      $lo = new CConsultas();
      $lo->pcTipEva = $_REQUEST['pcTipEva'];
      $llOk = $lo->omAplicarEvaluaciones();
      if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
         $loSmarty->assign('saTipEva', $_SESSION['paTipEva']);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg4180.tpl');
         fxAlert($lcMessage);
         return;
      }
      $_SESSION['paNroEva'] = $lo->paNroEva;
      $loSmarty->assign('saNroEva', $_SESSION['paNroEva']);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display('Plantillas/Seg4180.tpl');
   }
   
   function fxConsultar() {
      global $loSmarty;
      $lo = new CConsultas();
      $lo->pcNroEva = $_REQUEST['pcNroEva'];
      $llOk = $lo->omConsultarEvaluaciones();
      if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
         $loSmarty->assign('saNroEva', $_SESSION['paNroEva']);
         $loSmarty->assign('scBehavior', '1');
         $loSmarty->display('Plantillas/Seg4180.tpl');
         fxAlert($lcMessage);
      }
      //$_SESSION['paData']   = $lo->paData;
      $_SESSION['pcNroEva'] = $lo->pcNroEva;
      $_SESSION['pdFecEva'] = $lo->pdFecEva;
      $_SESSION['pcHorEva'] = $lo->pcHorEva;
      $_SESSION['pcKilome'] = $lo->pcKilome;
      $_SESSION['pcTren']   = $lo->pcTren;
      //$loSmarty->assign('saData',   $_SESSION['paData']);
      $loSmarty->assign('scNroEva', $_SESSION['pcNroEva']);
      $loSmarty->assign('sdFecEva', $_SESSION['pdFecEva']);
      $loSmarty->assign('scHorEva', $_SESSION['pcHorEva']);
      $loSmarty->assign('scKilome', $_SESSION['pcKilome']);
      $loSmarty->assign('scTren',   $_SESSION['pcTren']);
      $loSmarty->assign('scDesSub', $lo->pcDesSub);
      $loSmarty->assign('scDesEst', $lo->pcDesEst);
      $loSmarty->assign('scNomSp1', $lo->pcNomSp1);
      $loSmarty->assign('scNomSp2', $lo->pcNomSp2);
      $loSmarty->assign('scDescri', $lo->pcDescri);
      $loSmarty->assign('scDesPry', $lo->pcDesPry);
      $loSmarty->assign('scResult', $lo->pcResult);
      $loSmarty->assign('scBehavior', '2');
      $loSmarty->display('Plantillas/Seg4180.tpl');
   }

   function fxVolver() {
      global $loSmarty;
      $loSmarty->assign('saTipEva', $_SESSION['paTipEva']);
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display('Plantillas/Seg4180.tpl');
   }
   
/////////////////   
   function fxReporte() {
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
      if ($lcTipo == '1')
         fxGenerarRep1($lo->paLinea);
      else if ($lcTipo == '2')
         fxGenerarRep2($lo->paLinea);
   }
   
   function fxGenerarRep1($p_aLinea) {
      $loPdf = &new Cezpdf('a4');
      $loPdf->selectFont('fonts/Helvetics.afm');
      $loPdf->addInfo(array('Title' => 'Gestion de Seguridad',
                            'Subject' => 'REPORTE',));
      $titles= array('CCODPER' => 'CODIGO',
                     'CNOMBRE' => 'NOMBRE',
                     'NNOTA' => 'NOTA',);
      $options = array(
         'shadeCol'=>array(0.9,0.9,0.9),
         'xOrientation'=>'center',
         'width'=>500       
      );
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
      $loPdf->ezStream();
   }
   
   function fxGenerarRep2($p_aLinea) {
      $loPdf = &new Cezpdf('a4');
      $loPdf->selectFont('fonts/Helvetics.afm');
      $loPdf->addInfo(array('Title' => 'Gestion de Seguridad',
                            'Subject' => 'REPORTE',));
      $titles= array('CCODCUR' => 'CURSO',
                     'DFECHA' => 'FECHA',
                     'CDESCRI' => 'DESCRIPCION',
                     'NNOTA' => 'NOTA',);
      $options = array(
         'shadeCol'=>array(0.9,0.9,0.9),
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
      $loPdf->ezStream();
   }
?>
