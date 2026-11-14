<?php
   require_once 'Clases/CBase.php';
   require_once 'Libs/Smarty.class.php';
   require_once 'Clases/CConsultasXls.php';
   require_once 'Clases/CMtoPerfiles.php';
   session_cache_limiter();
   session_start();
   $loSmarty = new Smarty;
   if (!fxValidarPermiso('SEG2410', 'Mnu0000.php?Id=6000'))
      return;
   $lcEvaTip = 'R';
   if (!fxInitSession()) {
      fxCallIndex('NO HA INICIADO SESIÓN');
   } else if (@$_POST['Buscar'] == 'Buscar') {
      $_SESSION['pcVolver'] = 'Seg2410';
      $_SESSION['pcEvaTip'] = $lcEvaTip;
      fxHeader('Seg9006.php'); 
   } else if (@$_POST['Aplicar'] == 'Aplicar' or @$_GET['Id'] == 'Aceptar') {
      $lo = new CConsultasXls();
      if (@$_GET['Id'] == 'Aceptar') 
         $lo->pcNroEva = $_SESSION['pcNroEva'];
      else  
         $lo->pcNroEva = $lcEvaTip.$_REQUEST['pcNroEva'];
      $llOk = $lo->omPruebaRadar();
      if (!$llOk) {
         $lcMessage = fxXmlError($lo->pcError);
         $loSmarty->assign('scBehavior', '0');
         $loSmarty->display('Plantillas/Seg2410.tpl');
         fxAlert($lcMessage);
      } else {
         $lcNomMaq  = null;
         $lcCnomjef = null;
         $lcNombrq  = null;
         foreach($lo->paData as $i)   {
            if ($i[1] == 'M')
               $lcNomMaq = $i[0];
            if ($i[1] == 'J')
               $lcCnomjef = $i[0];
            if ($i[1] == 'B')
               $lcNombrq = $i[0];
         }
         $lcLocomo = '';
         if ($lo->paFila['CCODUN1'] != '99999')
            $lcLocomo = $lcLocomo.' '.$lo->paFila['CDESUN1'];
         if ($lo->paFila['CCODUN2'] != '99999')
            $lcLocomo = $lcLocomo.' '.$lo->paFila['CDESUN2'];
         if ($lo->paFila['CCODUN3'] != '99999')
            $lcLocomo = $lcLocomo.' '.$lo->paFila['CDESUN3'];
         $lcRestri = 'NO';
         if ($lo->paFila['CRESTRI'] = 'S')
            $lcRestri = 'SI';
         $laArray = array(
            '#*DFECHA*#'=>$lo->paFila['DFECEVA'],
            '#*CHORA*#'=>$lo->paFila['CHOREVA'],
            '#*CTREN*#'=>$lo->paFila['CTREN'],
            '#*CTIPO*#'=>$lo->paFila['CDESVAG'],
            '#*CLOCOMO*#'=>$lcLocomo,
            '#*CNOMMAQ*#'=>$lcNomMaq,
            '#*CNOMJEF*#'=>$lcCnomjef,
            '#*CCOMPOS*#'=>$lo->paFila['CCOMPOS'],
            '#*CNOMBRQ*#'=>$lcNombrq,
            '#*CSUBDIV*#'=>$lo->paFila['CDESDIV'],
            '#*CKILOME*#'=>$lo->paFila['CKILOME'],
            '#*CTIPO2*#'=>'***',
            '#*CCONCLI*#'=>$lo->paFila['CDESCLI'],
            '#*CVELMAX*#'=>$lo->paFila['CVELAUT'],
            '#*CVELMIN*#'=>$lo->paFila['CVELREG'],
            '#*CRESTRIC*#'=>$lcRestri,
            '#*CRESULT*#'=>$lo->paFila['CDESRES'],    
            '#*COBSERV*#'=>$lo->paFila['MOBSERV']->load(),
            '#*CSUPERV1*#'=>$lo->paFila['CNOMSP1'],
            '#*CSUPERV2*#'=>$lo->paFila['CNOMSP2'],);
         $lo = new CRtf();
         $lo->pcFile = 'S03MEVA2';
         $lo->paArray = $laArray;
         $lo->omProcesar();
      } 
   } else {
      $loSmarty->assign('scBehavior', '0');
      $loSmarty->assign('scEvaTip', $lcEvaTip);
      $loSmarty->display('Plantillas/Seg2410.tpl');
   }
?>