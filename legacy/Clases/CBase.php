<?php
require_once 'class/PHPExcel.php';
require_once 'class/RTFTable.php';

//------------------------------------------------------
// Clase Base
//------------------------------------------------------
class CBase 
{
   public $pcError;

   function __construct() {
   }
   
   public function mxValDate($p_dFecha) {
      $laFecha = explode('-', $p_dFecha);
      return checkdate((int)$laFecha[1], (int)$laFecha[2], (int)$laFecha[0]);
   }

   protected function mxError($p_cXml) {
      if (empty($p_cXml)) {
         $this->pcError = '<DATA><ERROR>ERROR EN CADENA DE DATOS XML (DB)</ERROR></DATA>';
         return false;
      }
      $xml = new SimpleXMLElement($p_cXml);
      $xml->asXML();
      $lcError = $xml->ERROR;
      if (!empty($lcError)) {
         $this->pcError = $lcError;
         return false;
      }
      return true;
   }
}

//------------------------------------------------------
// Conexion a Base de Datos
//------------------------------------------------------
class CSql extends CBase {
   public    $pnNumRow = null;
   protected $h        = null;
   protected $DB       = null;

   public function __construct() {
      parent::__construct();
     $this->DB = 'OR';
   }

   public function omDisconnect() {
      if ($this->h) {
         mysqli_close($this->h);
      }
      $this->h = null;
   }

   public function omConnect() 
   {
	    // PHP 8.1+ lanza excepciones por defecto en mysqli; el codigo legacy espera "false".
	    mysqli_report(MYSQLI_REPORT_OFF);
	    // Credenciales desde variables de entorno (Docker / .env de Symfony).
	    $lcHost = $_SERVER['DATABASE_HOST'] ?? getenv('DATABASE_HOST') ?: 'localhost';
	    $lnPort = (int) ($_SERVER['DATABASE_PORT'] ?? getenv('DATABASE_PORT') ?: 3306);
	    $lcUser = $_SERVER['DATABASE_USER'] ?? getenv('DATABASE_USER') ?: 'root';
	    $lcPass = $_SERVER['DATABASE_PASSWORD'] ?? getenv('DATABASE_PASSWORD') ?: '';
	    $lcName = $_SERVER['DATABASE_NAME'] ?? getenv('DATABASE_NAME') ?: 'textileria';
	    $lcChar = $_SERVER['DATABASE_CHARSET'] ?? getenv('DATABASE_CHARSET') ?: 'utf8mb4';
	    try
	    {
	         $this->h = @mysqli_connect($lcHost, $lcUser, $lcPass, $lcName, $lnPort);
	         if ($this->h) {
	            mysqli_set_charset($this->h, $lcChar);
	         }
	    } catch (Throwable $e)
    	{
           $this->h = false;
           error_log('CSql::omConnect '.$e->getMessage());
        }
        if (!$this->h) {
           $this->pcError = "<DATA><ERROR>NO SE PUDO CONECTAR A LA BASE DE DATOS</ERROR></DATA>";
           return false;
        }
      return true;
    }
   public function omExec($p_cSql) {
      if (!$this->h) {
         $this->pcError = '<DATA><ERROR>NO HAY CONEXION A LA BASE DE DATOS</ERROR></DATA>';
         return false;
      }
      $lcSql = substr(strtoupper(trim($p_cSql)), 0, 6);
      if ($lcSql === "SELECT") {
          $this->pnNumRow = 0;
          $RS = mysqli_query($this->h,$p_cSql);
          if (!($RS)) {
              $this->pcError = '<DATA><ERROR>ERROR AL EJECUTAR COMANDO SQL</ERROR></DATA>';
              return false;
          }
          $this->pnNumRow = mysqli_num_rows($RS);
          return $RS;
      } else {
          $RS = mysqli_query($this->h,$p_cSql);
          if (mysqli_affected_rows($this->h) == 0)
             if (!($this->h)) {
                $this->pcError = '<DATA><ERROR>LA OPERACION NO AFECTO ALA BD</ERROR></DATA>';
                return false;
             }
          return true;
       }
     
   }
   
   public function fetch($RS) {
     // PHP 8: mysqli_fetch_array(false) lanza TypeError; el legacy esperaba null.
     if (!($RS instanceof mysqli_result)) {
        return null;
     }
     return mysqli_fetch_array($RS);
   }

   // Metodos de transaccion invocados por el codigo legacy (no existian -> error fatal).
   public function begin() {
      return $this->h ? mysqli_begin_transaction($this->h) : false;
   }

   public function commit() {
      return $this->h ? mysqli_commit($this->h) : false;
   }

   public function rollback() {
      return $this->h ? mysqli_rollback($this->h) : false;
   }
}

// Clase que maneja hojas Excel
class CXls extends CBase {
   public $pcData, $pcFilXls;
   protected $loXls, $lo;

   public function __construct() {
      parent::__construct();
      $this->loXls = new PHPExcel();
      $this->lo = PHPExcel_IOFactory::createReader('Excel2007');
   }
   
   public function openXls($p_cFilXls, $p_cCodUsu) {                  
      try {
         $this->loXls = $this->lo->load('./Xls/'.$p_cFilXls.'.xlsx');
      } catch (Exception $e) {
         echo 'ExcepciÃ³n capturada: ',  $e->getMessage(), "\n";
      }
      try {
         $this->pcFilXls = './Ficheros/'.$p_cFilXls.'_'.$p_cCodUsu.'.xlsx';
      } catch (Exception $e) {
         echo 'ExcepciÃ³n capturada: ',  $e->getMessage(), "\n";
      }
   }
   
   public function sendXls($p_nSheet, $p_cCol, $p_nRow, $p_xValue) {      
      $this->loXls->setActiveSheetIndex($p_nSheet)->setCellValue($p_cCol.$p_nRow, $p_xValue);
      return;
   }
   
   public function closeXls() {    
      $lo = PHPExcel_IOFactory::createWriter($this->loXls, 'Excel2007');
      $lo->save($this->pcFilXls);
   }
}

// Clase que maneja cadenas XML
class CXml extends CBase {
   public $pcData, $pcLabel, $pcValor;

   public function __construct() {
      parent::__construct();
   }

   public function omBuscar() {
      $llOk = false;
      $lcLabel = trim($this->pcLabel);
      echo $this->pcLabel;
      if (empty($lcLabel)) {
         $this->lcError = '<DATA><ERROR>ETIQUETA DE BUSQUEDA NO DEFINIDA</ERROR></DATA>';
         return false;
      }
      $lcData = trim($this->pcData);
      $lcData = str_replace('<', '[', $lcData);
      $lcData = str_replace('>', ']', $lcData);
      $lcLabel1 = '[' . $lcLabel . ']';
      $lcLabel2 = '[/' . $lcLabel . ']';
      $i = strpos($lcData, $lcLabel1);
      if ($i === false) {
         $this->lcError = '<DATA><ERROR>Etiqueta de busqueda de inicio no encontrada</ERROR></DATA>';
         return false;
      }
      $j = strpos($lcData, $lcLabel2);
      if ($j === false) {
         $this->lcError = '<DATA><ERROR>Etiqueta de busqueda de cierre no encontrada</ERROR></DATA>';
         return false;
      }
      $i = $i + strlen($lcLabel1);
      $this->pcValor = substr($lcData, $i, $j - $i);
      $this->pcValor = str_replace($lcLabel1, '', $this->pcValor);
      return true;
   }
}

class CRtf extends CBase 
{
   public $pcFile, $paArray, $pcFilRet, $pcCodUsu, $paAArray;
   protected $lcFolXls, $lcFolSal, $lcFilRet, $lcFilInp, $lcTodo, $lp;
  
   function __construct () {
      parent::__construct();
      $this->paArray = null;
      $this->lcFolXls = './Xls/';
      $this->lcFolSal = './Ficheros/';
   }
   
   public function omInicializar() {
      $lcFile1 = $this->lcFolXls.$this->pcFile.'.rtf';
      if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }elseif (!is_file($lcFile1)) {
         $this->pcError = '<DATA><ERROR>ARCHIVO DE ORIGEN NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcFilRet = $this->lcFolSal.$this->pcFile.'_'.$this->pcCodUsu.'.doc';
      $this->lp = fopen($this->pcFilRet, 'w');
      // Lee archivo formato
      $laTexto = file($lcFile1);
      $lnSize = fxCount($laTexto);
      $this->lcTodo = '';
      for ($i = 0;$i < $lnSize;$i++) {
          $this->lcTodo .= $lcTodo.$laTexto[$i];
      }
      return true;
   }
   
   protected function mxTerminar() {
      fputs($this->lp, $this->lcTodo);
      fclose($this->lp);
      return true;
   }
   
   public function omGenerar($p_lClose = false) {
      if (!(is_array($this->paArray) and fxCount($this->paArray) > 0)) {
         fclose($this->lp);
         $this->pcError = '<DATA><ERROR>ARREGLO DE DATOS NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Reemplazo de variables
      foreach ($this->paArray as $lcValor1 => $lcValor2) {
         $lcValor2 = utf8_decode($lcValor2);
         $this->lcTodo = str_replace($lcValor1, $lcValor2 ,$this->lcTodo);
      }
      if ($p_lClose) {
         $this->mxTerminar();
      }
      return true;
   }
   
   public function omGenerarArray($p_lClose = false) {
      if (!(is_array($this->paAArray) and fxCount($this->paAArray) > 0)) {
         fclose($this->lp);
         $this->pcError = '<DATA><ERROR>ARREGLO DE DATOS NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      foreach ($this->paAArray as $lcValor1 => $lcValor2) {
         $loTabla = new RTFTable($lcValor2[0], $lcValor2[1]);
         if ($lcValor2[3]=='') {
            $loTabla->SetWideColsTable(round(10500/$lcValor2[0]));
         }else {
            for ($k = 0;$k < fxCount($lcValor2[3]);$k++) {
                $loTabla->SetWideColTable($k,$lcValor2[3][$k]);
            }
         }
         //Llenar Tabla cn arreglo pos:2
         for ($i = 0;$i < fxCount($lcValor2[2]);$i++) {
             for ($j = 0;$j < fxCount($lcValor2[2][0]);$j++) {
                 $lcValor2[2][$i][$j] = utf8_decode($lcValor2[2][$i][$j]);
                 if ($j ==0 ) {
                    //Centrado
                    $loTabla->SetElementCell($i,$j,'\\qc '.$lcValor2[2][$i][$j]);
                 }else
                    $loTabla->SetElementCell($i,$j,' '.$lcValor2[2][$i][$j]);
             }
         }
         $this->lcTodo = str_replace($lcValor1,$loTabla->GetTable() ,$this->lcTodo);
      }
      if ($p_lClose) {
         $this->mxTerminar();
      }
      return true;
   }
   
   protected function mxLeerArchivo() {
      if (!is_file($this->pcFile)) {
         $this->pcError = '<DATA><ERROR>ARCHIVO DE ORIGEN NO EXISTE</ERROR></DATA>';
         return false;
      }
      $laTexto = file($this->pcFile);
      $lnSize = fxCount($laTexto);
      $lcTodo = '';
      for ($i = 0;$i < $lnSize;$i++) {
          $lcTodo = $lcTodo.$laTexto[$i];
      }
      return $lcTodo;
   }
   
   public function omProcesar() {
      $this->lcFilRet = $this->lcFolSal.$this->pcFile.'_'.$this->pcCodUsu.'.rtf';//-- DEFINIMOS EL NOMBRE DEL NUEVO FICHERO
      $this->pcFile = $this->lcFolXls.$this->pcFile.'.rtf';
      if ($lcTexto = $this->mxLeerArchivo()) {
         $lp = fopen($this->lcFilRet, 'w');
         if (is_array($this->paArray) and fxCount($this->paArray) > 0) {
            foreach($this->paArray as $lcValor1 =>$lcValor2) {//-- REEMPLAZAMOS LAS VARIABLES
               $lcValor2 = utf8_decode($lcValor2);
               $lcTexto = str_replace($lcValor1, $lcValor2 ,$lcTexto);
            }
         }
         fputs($lp, $lcTexto);
         fclose($lp);
         header ('Content-Disposition: attachment;filename = '.$this->lcFilRet.'\n\n');
         header ('Content-Type: application/octet-stream');
         readfile($this->lcFilRet);
      }
   }
}

function right($lcCadena, $count) {
   return substr($lcCadena, ($count * -1));
}

function left($lcCadena, $count) {
   return substr($lcCadena, 0, $count);
}

function fxInitSession() {
   $llOk = true;
   if (!isset($_SESSION['gcCodUsu']))
      $llOk = false;
   return $llOk;
}

function fxNombreMes($p_cMes) {
   $laNomMes = array('EN', 'FE', 'MZ', 'AB', 'MY', 'JN', 'JL', 'AG', 'SE', 'OC', 'NO', 'DI');
   return $laNomMes[intval($p_cMes) - 1];
}

function fxSanearCadena($p_cCadena) {
   $lcCadena = utf8_encode($p_cCadena);
   $lcCadena = str_replace(array('Ã±', 'Ã‘', 'ÃƒÂ±','Ãƒâ€˜'), 'N', $lcCadena);
   $lcCadena = trim($lcCadena);
   $lcCadena = str_replace(array('Ã§', 'Ã‡', 'ÃƒÂ§', 'ÃƒÂ‡'), 'C', $lcCadena);
   $lcCadena = str_replace(array('Ã¡', 'Ã ', 'Ã¤', 'Ã¢', 'Âª', 'Ã', 'Ã€', 'Ã‚', 'Ã„', 'ÃƒÂ¡', 'Ãƒ ', 'ÃƒÂ¤', 'ÃƒÂ¢', 'Ã‚Âª', 'ÃƒÂ', 'ÃƒÂ€', 'ÃƒÂ‚', 'ÃƒÂ„'), 'A', $lcCadena);
   $lcCadena = str_replace(array('Ã©', 'Ã¨', 'Ã«', 'Ãª', 'Ã‰', 'Ãˆ', 'ÃŠ', 'Ã‹', 'ÃƒÂ©', 'ÃƒÂ¨', 'ÃƒÂ«', 'ÃƒÂª', 'ÃƒÂ‰', 'ÃƒÂˆ', 'ÃƒÂŠ', 'ÃƒÂ‹'), 'E', $lcCadena);
   $lcCadena = str_replace(array('Ã­', 'Ã¬', 'Ã¯', 'Ã®', 'Ã', 'ÃŒ', 'Ã', 'ÃŽ', 'ÃƒÂ­', 'ÃƒÂ¬', 'ÃƒÂ¯', 'ÃƒÂ®', 'ÃƒÂ', 'ÃƒÂŒ', 'ÃƒÂ', 'ÃƒÂŽ'), 'I', $lcCadena);
   $lcCadena = str_replace(array('Ã³', 'Ã²', 'Ã¶', 'Ã´', 'Ã“', 'Ã’', 'Ã–', 'Ã”', 'ÃƒÂ³', 'ÃƒÂ²', 'ÃƒÂ¶', 'ÃƒÂ´', 'ÃƒÂ“', 'ÃƒÂ’', 'ÃƒÂ–', 'ÃƒÂ”'), 'O', $lcCadena);
   $lcCadena = str_replace(array('Ãº', 'Ã¹', 'Ã¼', 'Ã»', 'Ãš', 'Ã™', 'Ã›', 'Ãœ', 'ÃƒÂº', 'ÃƒÂ¹', 'ÃƒÂ¼', 'ÃƒÂ»', 'ÃƒÂš', 'ÃƒÂ™', 'ÃƒÂ›', 'ÃƒÂœ'), 'U', $lcCadena);
   //Esta parte se encarga de eliminar cualquier caracter extraÃ±o
   $lcCadena = str_replace(array('\\', 'Â¨', 'Âº', '~', '|' , '\'', 'Â·', '$', '%', "'" , 'Â¡' , '&', 'Â¿', '^', '`', 'Â¨', 'Â´'), '', $lcCadena);
   $lcCadena = strtoupper($lcCadena);
   return $lcCadena;
}

function VMas($p_cCadena) {
   $lcCadena = str_replace(' ', '+', $p_cCadena);
   return $lcCadena;
}

function fxEliminarFila($laData, $laCheck) {
   // Numero de columnas
   $lnCol = 0;
   foreach($laData as $i) {
      foreach($i as $j) {
         $lnCol++;
      }
      break;
   }
   // Elimina filas
   $k = 0;
   $laData1 = null;
   foreach($laData as $i) {
      $llOk = true;
      foreach($laCheck as $j) {
         if ($i[0] == $j) {
            $llOk = false;
            break;
         }
      }
      if ($llOk) {
         $k++;
         $laData1[$k] = array();
         $laData1[$k][0] = $k;
         for ($j = 1;$j <= $lnCol;$j++) {
             $laData1[$k][$j] = $i[$j];
         }
      }
   }
   return $laData1;
}

function fxBuscarFila($laData, $laCheck, $lnCol) {
   $k = 0;
   $llOk = false;
   foreach($laData as $i) {
      $k++;
      foreach($laCheck as $j) {
         if ($i[0] == $j) {
         $llOk = true;
            break;
         }
      }
     if ($llOk)
        break;
   }
   $lcCodigo = $laData[$k][$lnCol];
   return $lcCodigo;
}

function fxValDelete($p_aCheck, $p_aDataB) {
   $llOk = true;
   if (isset($p_aCheck)) {
      foreach($p_aCheck as $i) {
         foreach($p_aDataB as $j) {
            if ($i == $j[0]) {
               $llOk = false;
               break;
            }
         }
         if (!$llOk)
            break;
      }
   }
   return $llOk;
}

function fxValRepeated($p_aData, $p_nCol) {
   $i = 0;
   if (!isset($p_aData))
      return false;
   if (fxCount($p_aData) == 1) 
      return false;
   foreach($p_aData as $laFila) {
      $i++;
      $j = 0;
      foreach($p_aData as $laFila1) {
         $j++;
         if ($i <= $j) {
            continue;
         }
         if ($laFila[$p_nCol] == $laFila1[$p_nCol]) {
            return true;
         }
      }
   }
   return false;
}

function fxAlert($p_Message) {
   echo "<script type='text/javascript'>";
   echo "alert('$p_Message')";
   echo '</script>';
}
function fx2Date($p_cFecha){
  try {
   //$x1 = date('Y-m-d', strtotime($p_cFecha));
   $x1 = date(strtotime($p_cFecha));
  } catch (Exception $e) {
      $x1=null;
     echo 'ExcepciÃ³n capturada: '.$e->getMessage();
  }
  return $x1;
}
function fxXmlError($p_cXml) {
   $lcMessage = null;
   try {
      $xml = new SimpleXMLElement($p_cXml);
      $xml->asXML();
      $lcMessage = $xml->ERROR;
   }catch (Exception $e) {
      echo 'ExcepciÃ³n capturada: '.$e->getMessage();
   }
   return $lcMessage;
}

function fxValDate($p_dFecha) {
   $laFecha = explode('-', $p_dFecha);
   return checkdate((int)$laFecha[1], (int)$laFecha[2], (int)$laFecha[0]);
}
   
function fxValTime($p_cTime) {
   $laTime = explode(':', $p_cTime);
   if (!($laTime[0] >= '00' and $laTime[0] <= '24')) 
      return false;
   if (!($laTime[1] >= '00' and $laTime[1] <= '59')) 
      return false;
   return true;
}

function fxCallIndex($p_cMensaje) {
   if (empty($p_cMensaje)) {
      echo "<script> window.location='index.php';</script>";
   } else {
      echo '<script>';
      echo "alert('$p_cMensaje');";
      echo "window.location='index.php';</script>";
   }
}

function fxHeader($p_cLocation, $p_cMensaje = '') {
   if (empty($p_cMensaje)) {
      $lcScript = "window.location='$p_cLocation';";
   } else {
      $lcScript = "alert('$p_cMensaje');window.location='$p_cLocation';";
   }
   echo '<script>'.$lcScript.'</script>';
}

function fxValidarRuc($p_cNroRuc) {
      $lcNroRuc = trim($p_cNroRuc);
      if (strlen($lcNroRuc) != 11) {
         return false;
      } elseif (!preg_match("/^[[:digit:]]+$/", $lcNroRuc)) {
         return false;
      }
      $laFactor = array(5, 4, 3, 2, 7, 6, 5, 4, 3, 2);
      $lnSuma = 0;
      for ($i = 0; $i < 10; $i++) {
          $lnSuma += $laFactor[$i] * substr($lcNroRuc, $i, 1);
      }
      $lnResto = ($lnSuma % 11);
      $lnResto = 11 - $lnResto;
      $lnResto = ($lnResto == 10) ? 0 : $lnResto;
      if ($lnResto != substr($lcNroRuc, 10, 1)) {
         return false;
      }
      return true;
   }
   
function fxValEmail($p_cEmail) {
   $lcEmail = trim($p_cEmail);
   if (strpos($lcEmail,' ')) {
      return false;
   }
   if (!strpos($lcEmail,'@')) {
      return false;
   }
   if (strpos('@',$lcEmail,1)) {
      return false;
   }
   $lcCorreo = explode('@', $lcEmail);
   if (empty($lcCorreo[0])) {
      return false;
   }
   if (empty($lcCorreo[1])) {
      return false;
   }
   $j = explode('.', $lcCorreo[1]);
   if (empty($j[0])) {
      return false;
   }
   if (empty($j[1])) {
      return false;
   }
   return true;
}

function fxAddDate($p_dFecha, $p_nDias) {
   $ldFecha = strtotime($p_nDias. ' day', strtotime($p_dFecha));
   $ldFecha = date('Y-m-d', $ldFecha);
   return $ldFecha;
}

function fxValMonto($p_cValor) {
   if (is_int($p_cValor)) {
      if ($p_cValor <= 0) 
         return false;
      return true;
   }
   if (is_float($p_cValor)) {
      if ($p_cValor <= 0) 
         return false;
      return true;
   }
   return false;
}

function fxTiempoPerdido($p_cTime) {
   $lcTime = substr($p_cTime, 0, 2);
   if (!($lcTime >= '00' and $lcTime <= '99')) 
      return false;
   $lcTime = substr($p_cTime, 2, 1);
   if (($lcTime != 'd')) 
      return false;
   $lcTime = substr($p_cTime, 3, 2);
   if (!($lcTime >= '00' and $lcTime <= '99')) 
      return false;
   $lcTime = substr($p_cTime, 5, 1);
   if (($lcTime != 'h')) 
      return false;
   return true;
}

class CSendEmail extends CBase {
   public $pcForWho, $pcFrom, $pcSubject, $pcMessage;
 
   function __construct () {
      parent::__construct();
   }
   
   public function omSend() {
      if (empty($this->pcForWho)) {
         $this->pcError = '<DATA><ERROR>Email de destino no definido</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcFrom)) {
         $this->pcError = '<DATA><ERROR>Email de origen no definido</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcSubject)) {
         $this->pcError = '<DATA><ERROR>Tema del email no definido</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcMessage)) {
         $this->pcError = '<DATA><ERROR>Texto del email no definido</ERROR></DATA>';
         return false;
      }
      $lcHeaders = 'From: '.$this->pcFrom.'\r\n' .
                   'Reply-To: '.$this->pcFrom.'\r\n' .
                   'X-Mailer: PHP/' . phpversion();
      mail($this->pcForWho, $this->pcSubject, $this->pcMessage, $lcHeaders);
      return true;
   }
}

function fxReadPdf($p_oPdf) {
   $lcFile = rand().'.pdf';
   $pdfcode = $p_oPdf->ezOutput(1);

   $fp = fopen($lcFile, 'w');
   fwrite($fp, $pdfcode);
   fclose($fp);
   return $lcFile;
//     echo "<a href = '$lcFile' TARGET='_new'>Archivo de Salida</a>";
}
?>