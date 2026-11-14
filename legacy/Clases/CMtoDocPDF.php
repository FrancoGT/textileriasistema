<?php
include "Clases/CBase.php";

class CMtoDocPDF extends CBase {
   public $paCodTep = null;
   public $pcTitulo = null;
   public $pcAutores= null;
   
/*   public $pcComent = null;
   public $pcYear   = null;
   public $pcTipDoc = null;
   public $pcCod    = null;
   public $pcCodUsu = null;
   public $paTipDoc = null;
   public $paCodEnt = null;
   public $pcCodTep = null;
   public $pcArray  = null;
   public $pcResume = null;
   public $pcNombre = null;
   public $pcFile   = null;
   public $pcFile1  = null;
   public $pcTypeF  = null;*/

   // Aplica la busqueda de codigo de texto
   function omAplicar() {
      if (empty($this->pcCodTep)) {
         $this->pcError = 'CODIGO DE TEXTO VACIO';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Titulo del texto
      $lcSql = "SELECT cTitulo FROM L02MTEP WHERE cCodTep = '$this->pcCodTep'";
      $RS = $loSql->omExec($lcSql);
      $laFila = pg_fetch_row($RS);
      $this->pcTitulo = $laFila[0];
      if (empty($this->pcTitulo)) {
         $loSql->omDisconnect();
         $this->pcError = 'NO EXISTE CODIGO DE TEXTO';
         return false;
      }
      // Autores
      $this->pcAutores = '';
      $lcSql = "SELECT B.cNombre FROM L02PTEP A INNER JOIN L01MPER B ON A.cCodPer = B.cCodPer WHERE A.cCodTep = '$this->pcCodTep' ORDER BY cNombre";
      $RS = $loSql->omExec($lcSql);
      while ($laFila = pg_fetch_row($RS)) {
         $this->pcAutores = $this->pcAutores.'<br>'.$laFila[0];
      }
      $loSql->omDisconnect();
      return true;
   }

   function omAgregar() {
      if (empty($this->pcCodTep)) {
         $this->pcError = 'CODIGO VACIO';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cTitulo, cResume FROM L02MTEP WHERE cCodTep = '$this->pcCodTep'";
      $RS = $loSql->omExec($lcSql);
      if (pg_num_rows($RS) == 0) {
         $loSql->omDisconnect();
         $this->pcError = 'NO EXISTE CODIGO';
         return false;
      }
      $laFila = pg_fetch_row($RS);
      $loSql->omDisconnect();
      $this->pcTitulo = $laFila[0];
      $this->pcResume = $laFila[1];
      return true;
   }
   
   function omBuscarTexto() {
////////////
// Iniciar para plantilla de nuevo Documento PDF
       if (empty($this->pcTitulo)) {
         $this->pcError = 'CODIGO DE AUTOR VACIO';
         return false;
      }
       $loSql = new CSql();
       $llOk = $loSql->omConnect();
       if($llOk){
       $lcSql = "SELECT * from l02mtep where ccodtep = '".$this->pcTitulo."'";
       $RS = $loSql->omExec($lcSql);
       if (!$RS) {
         $this->pcError = 'ERROR EN BUSQUEDA DE TEXTO. AVISE A TI';
         $loSql->omDisconnect();
         return false;
      }
      if (pg_num_rows($RS) == 0) {
         $loSql->omDisconnect();
         $this->pcError = 'NO EXISTE CODIGO DE AUTOR';
         return false;
      }
      $laFila = pg_fetch_row($RS);
      $this->pcCod     = $laFila[0];
      $this->pcTitulo  = $laFila[1];
      $this->pcResumen = $laFila[2];
      }
      $loSql->omDisconnect();
      return true;
   }
   
   function omBuscar() {
      if (empty($this->pcTitulo)) {
         $this->pcError = 'TITULO VACIO';
         return false;
      }
      $lcTitulo= trim($this->pcTitulo)."%";
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cCodTep, cTitulo FROM L02MTEP WHERE cTitulo LIKE '$lcTitulo'";
      $RS = $loSql->omExec($lcSql);
      if (pg_num_rows($RS) == 0) {
         $loSql->omDisconnect();
         $this->pcError = 'NO HAY TITULO BUSCADO';
         return false;
      }
      while ($laFila = pg_fetch_row($RS)) {
         $this->paCodTep[] = array($laFila[0], $laFila[1]);
      }    
      $loSql->omDisconnect();
      return true;
   }

   // Sube PDF a servidor
   function omSubir() {
      if (empty($this->pcCodTep)) {
         $this->pcError = "CODIGO DE TEXTO VACIO";
         return false;
      } else if (empty($this->pcFile)) {
         $this->pcError = "NOMBRE Y RUTA DEL ARCHIVO VACIO";
         return false;
      } else if (empty($this->pcFile1)) {
         $this->pcError = "NOMBRE Y RUTA DEL ARCHIVO ORIGEN VACIO";
         return false;
      } else if (!($this->pcTypeF == 'application/pdf' or $this->pcTypeF == 'application/binary')) {
         $this->pcError = 'NO ES UN ARCHIVO PDF - '.$this->pcTypeF;
         return false;
      }
      $i = 0;
      if (!is_dir("./PDF/".$this->pcCodTep))
         mkdir("./PDF/".$this->pcCodTep, 0777, false);
      chmod("./PDF/".$this->pcCodTep, 0777);
      $lcDirect = "./PDF/".$this->pcCodTep;
      $directorio = opendir($lcDirect); 
      $lcPath = $lcDirect;
      while ($lcFile = readdir($directorio)) {
         if ((strpos($lcFile, "pdf")) || (strpos($lcFile, "PDF"))) {
            if (is_file($lcPath."/".$i."-".$this->pcFile) == 1){
               unlink($lcPath."/".$i."-".$this->pcFile);
               $lcPath = $lcDirect."/";
               if (!(move_uploaded_file($this->pcFile1, $lcPath.$i."-".$this->pcFile))) {
                  $this->pcError = "ERROR AL SUBIR EL ARCHIVO PDF";
                  return false;
               }
               return true;
            }
            $i++;
         }
      }
      $lcPath = $lcDirect."/";
      if (!(move_uploaded_file($this->pcFile1, $lcPath.$i."-".$this->pcFile))) {
         $this->pcError = "ERROR AL SUBIR EL ARCHIVO PDF";
         return false;
      }
      return true;
   }

   function omGrabar()
   {
      if (empty($this->pcCod)) {
       $this->pcError = "CODIGO VACIO";
            return false;
      } else {
      if (empty($this->pcNombre)) {
       $this->pcError = "NOMBRE DOC VACIO";
            return false;
      }    
      }
       if (!is_dir("./PDF/".$this->pcCod)) mkdir("./PDF/".  $this->pcCod,0777,FALSE);
       $path ="./PDF/".  $this->pcCod."/";
       if (!is_file($path.$this->pcNombre)) unlink($path.$this->pcNombre);    
       if (move_uploaded_file($_FILES['userfile']['tmp_name'], $path."/".  $this->pcNombre))
               echo "El archivo ha sido cargado correctamente.";
           else{
               $this->pcError = 'Ocurrió algún error al subir el fichero. No pudo guardarse.';
               return FALSE;
           }
       return true;
   }
   
   
   function insert_document()
    {
        $conn = pg_connect("user=".DB_USER." password=".DB_PASS." port=".DB_PORT." dbname=".DB_NAME." host=".DB_HOST);
        if ($conn) {
            $variable1 = $_POST['Titulo'];
            $variable2 = $_POST['COMENTARIO'];
            $variable3 = $_POST['year'];
            $variable4 = $_POST['Documento'];
            $variable5 = $_POST['Entidad'];
            $variable6 = '9999';
            $r = "SELECT insert_document2('$variable1','$variable2','$variable3','$variable4','$variable5','$variable6')";
            $result = pg_query($conn,$r);
            $ID = pg_result($result,0,0);
        }
   else
   {
            echo "Conexión Erronea";
            exit;
   }                        
   return $ID;
    }
}
        
?>  
