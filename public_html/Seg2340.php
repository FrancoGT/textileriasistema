<?php
   require_once "Libs/Smarty.class.php";
   require_once "Clases/CConsultasXls.php";
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2340', 'Mnu0000.php?Id=6000'))
      return;
   if (!fxInitSession()) { 
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Mostrar'] == "Mostrar") {
      fxMostrar();
   } else if (@$_POST['Salir'] == "Salir") {      
      fxHeader('Mnu0000.php?Id=6000');
   } else if (@$_POST['Generar'] == "Generar") {
      fxGenerar();
   } else {      
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->display("Plantillas/Seg2340.tpl");
   }   
   
   function fxMostrar() {
      $objReader = PHPExcel_IOFactory::createReader('Excel2007');
      $objReader->setReadDataOnly(true);
      $objPHPExcel = $objReader->load('./Xls/prueba1.xlsx');
      $objWorksheet = $objPHPExcel->getActiveSheet();
      echo '<table>' . "\n";
      foreach ($objWorksheet->getRowIterator() as $row) {
         echo '<tr>' . "\n";
         $cellIterator = $row->getCellIterator();
         $cellIterator->setIterateOnlyExistingCells(false);
         foreach ($cellIterator as $cell) {
            echo '<td>' . $cell->getValue() . '</td>' . "\n";
         } 
         echo '</tr>' . "\n";
      }
      echo '</table>' . "\n";
   }
   
   function fxGenerar() {
      global $loSmarty;
	  $lo = new CConsultasXls();
	  $lo->gcCodUsu = $_SESSION["gcCodUsu"];
	  $lo->omIncidentesLicencias();
	  $loSmarty->assign("saData", $lo->paData);
      $loSmarty->assign('scBehavior', '1');
      $loSmarty->display("Plantillas/Seg2340.tpl");
   }
   
?>
