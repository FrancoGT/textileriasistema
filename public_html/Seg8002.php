<?php
   // Registro y mantenimiento de empleados
   echo 'Iniciando ...<br>';
   require_once "Libs/Smarty.class.php";
   require_once "Clases/CBase.php";
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   echo 'Iniciando objeto ...<br>';
   $lo = new CSendEmail();
   $lo->pcForWho = 'fparedesmarchena@hotmail.com';
   $lo->pcFrom = 'fpm1960@hotmail.com';
   $lo->pcSubject = 'Seguimiento';
   $lo->pcMessage = '¡Saludos...!';
   echo 'Enviando email ...<br>';
   $lo->omSend();
?>
