<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CServicios.php';
   session_start();
   $lo = new CServicios();
   $llOk = $lo->omEmailRecodatorio();
   if (!$llOk) {
      echo fxXmlError($lo->pcError);
   } else {
      echo 'ENVIO DE EMAILS TERMINADO SATISFACTORIAMENTE';
   }
?>
