<?php
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CBase.php';
   session_start();
   unset($_SESSION["gcNombre"]);
   unset($_SESSION["gcCodUsu"]);
   unset($_SESSION["gcTipo"]);
/*
$loSql = new CSql();
$llOk = $loSql->omConnect();
if(!llOk){
  $this->pcError = $loSql->pcError;
 return false;
}
$lcSql = 'SELECT * FROM E01MPER';
$RS = $loSql->omExec($lcSql);
$laFila = $loSql->fetch($RS);
print_r($laFila);
*/
   $loSmarty = new Smarty;
   $loSmarty->display('Plantillas/Index.tpl');
?>