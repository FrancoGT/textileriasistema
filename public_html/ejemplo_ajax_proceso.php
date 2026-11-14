<?php 
 // open the file
 $file=fopen("enrique.txt","w");
 
 //fputs($file,$_POST['valorCaja1']);
 //fputs($file,$_POST['valorCaja2']);
 fputs($file,$_POST['pcEmail']);
 fputs($file,"ABC");
 
 //close the file.
 fclose($file);
 if (!preg_match("/^\d+\.\d\d$/", $_POST['pcEmail'])) {
      $resultado = "no es numerico".$_POST['pcEmail'];     }
 else{
      $resultado = "OK"; }
echo $resultado;
?>
