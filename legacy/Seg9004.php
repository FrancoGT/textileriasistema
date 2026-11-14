<?php
require_once "Libs/Smarty.class.php";
require_once "Clases/CMtoNormas.php";
session_start();
$lcVolver = $_SESSION['pcVolver'].'.php';
$loSmarty = new Smarty;
if (!fxInitSession()) {
    fxCallIndex('NO HA INICIADO SESIÓN');
} else if ((@$_POST['Aceptar'] == "Aceptar")) {
    $lcCodNor = $_REQUEST["pcCodNor"];
    if (empty($lcCodNor)) {
        $loSmarty->assign("scAviso", "NO HA ELEGIDO NORMAS");
        $loSmarty->assign("scVolver", "Seg9004.php");
        $loSmarty->display("Plantillas/SIS1000.tpl");
    } else {
        $_SESSION['pcCodNor'] = $lcCodNor;
        fxHeader($lcVolver.'?Id=Aceptar');
    }
} else if ((@$_POST['Aplicar'] == "Aplicar")) {
    $lo = new CMtoNormas();
    $lo->pcDescri = $_REQUEST["pcDescri"];
    $llOk = $lo->omBuscarNormas();
    if (!$llOk) { // Error
        $loSmarty->assign("scAviso", $lo->pcError);
        $loSmarty->assign("scVolver", "Seg9004.php");
        $loSmarty->display("Plantillas/SIS1000.tpl");
    } else {
        $loSmarty->assign("scBehavior", '1');
        $loSmarty->assign("saCodNor", $lo->paCodNor);
        $loSmarty->assign("scVolver", $lcVolver);
        $loSmarty->display("Plantillas/Seg9004.tpl");
    }
} else {
    $loSmarty->assign("scBehavior", '0');
    $loSmarty->assign("scVolver", $lcVolver);
    $loSmarty->display("Plantillas/Seg9004.tpl");
}
?>
