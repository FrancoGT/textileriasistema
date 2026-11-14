<?php
   header('Content-Type: text/txt; charset=UTF-8');
   session_start();
   $lcEstaci = $_GET['Id'];
   $llError = false;
   if (empty($lcEstaci)) {
      $llError = true;
   }  if (strlen(trim($lcEstaci)) != 11) {
      $llError = true;
   }
   if ($llError) {
      $lcTexto = "{'CNOMPRV':'*** ERROR ***'}";
      echo $lcTexto;
	  return;
   }
   $lcNomPrv = '*** ERROR ***';
   foreach($_SESSION['paCodPrv'] as $laFila) {
      if ($laFila[0] == $lcEstaci) {
         $lcNomPrv = trim($laFila[1]);
         break;
      }
   }
   $lcTexto = "{'CNOMPRV':'$lcNomPrv'}";
   $_SESSION['pcNomPrv'] = $lcNomPrv;
   echo $lcTexto;
?>