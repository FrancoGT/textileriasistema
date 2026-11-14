<?php
   require_once 'Libs/Smarty.class.php';
   require_once "Clases/CLogin.php";
   session_start();
   $asyncLogin = ($_SERVER['REQUEST_METHOD'] ?? '') === 'POST'
      && strtolower($_SERVER['HTTP_X_REQUESTED_WITH'] ?? '') === 'xmlhttprequest';
   if ($asyncLogin) {
      header('Content-Type: application/json; charset=utf-8');
      header('Cache-Control: no-store');
      if (!is_string($_POST['pcCodigo'] ?? null) || !is_string($_POST['pcClave'] ?? null)
         || strlen($_POST['pcCodigo']) > 40 || strlen($_POST['pcClave']) > 80) {
         http_response_code(400);
         echo json_encode(['ok' => false, 'message' => 'Ingrese su código y clave.']);
         return;
      }
      $_REQUEST['pcCodigo'] = $_POST['pcCodigo'];
      $_REQUEST['pcClave'] = $_POST['pcClave'];
   }
   $loSmarty = new Smarty;
   if (!isset($_REQUEST["pcCodigo"]) || !isset($_REQUEST["pcClave"])) {
      if (!fxInitSession()) {
         fxCallIndex("NO HA INICIADO SESION");
      } else {
         $loSmarty->assign("scNombre", $_SESSION["gcNombre"]);
         $loSmarty->assign("scCodigo", $_SESSION["gcCodUsu"]);
         $loSmarty->assign("scTipo", $_SESSION["gcTipo"]);
         $loSmarty->assign("scNivel", @$_GET['Id']);
         $loginTemplate = !empty($_SESSION['login_change_password']) ? 'Plantillas/Mnu1000.tpl' : 'Plantillas/Mnu0000.tpl';
         unset($_SESSION['login_change_password']);
         $loSmarty->display($loginTemplate);
      }
   } else {      
      $lcCodigo = $_REQUEST["pcCodigo"];
      $lcClave = $_REQUEST["pcClave"];
      $lo = new CLogin();
      $lo->pcCodigo = $lcCodigo;
      $lo->pcClave = $lcClave;
      $llOk = $lo->omLogin();
      if (!$llOk) { // Error                                        
         if ($asyncLogin) {
            http_response_code(401);
            echo json_encode(['ok' => false, 'message' => 'No se pudo iniciar sesión. Verifique su código y clave e intente nuevamente.']);
            return;
         }
         $loSmarty->assign("scNivel", "0000");
         $loSmarty->display("Plantillas/Index.tpl");
         fxAlert($lo->pcError);
      } else {         
         $_SESSION['gcCodUsu'] = $lo->pcCodigo;
         $_SESSION['gcNombre'] = $lo->pcNombre; 
         if ($asyncLogin) {
            session_regenerate_id(true);
            $_SESSION['login_change_password'] = $lo->pnCambio == 1;
            echo json_encode(['ok' => true, 'redirect' => 'Mnu0000.php?Id=0000']);
            return;
         }
         if ($lo->pnCambio == 1){
            $loSmarty->assign('scNombre', $lo->pcNombre);
            $loSmarty->display("Plantillas/Mnu1000.tpl");
         } else {
            $loSmarty->assign('scNivel', '0000');
            $loSmarty->assign('scCodigo', $lo->pcCodigo);
            $loSmarty->assign('scNombre', $lo->pcNombre);
            $loSmarty->display("Plantillas/Mnu0000.tpl");
         }
      }
   }
?>
