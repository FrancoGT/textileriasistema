<?php
   header('Content-Type: text/txt; charset=UTF-8');
   session_start();
   $lcCodPry = $_REQUEST['Id'];
   $laTmp = null;
   foreach($_SESSION['paCodPrc'] AS $laFila) {
      if (trim($laFila[0]) == trim($lcCodPry))
         $laTmp[] = array($laFila[1], $laFila[2]);
   }
   $llFirst = true;
   $lcTexto = "[";
   foreach($laTmp as $laFila) {
      if (!$llFirst)
         $lcTexto.=',';
	  $llFirst = false;
      $lcTexto.="{'Texto':'$laFila[1]','Valor':'$laFila[0]'}";
   }
   $lcTexto.="]";
   echo $lcTexto;
?>