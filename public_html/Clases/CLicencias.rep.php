<?php
require_once 'Clases/CBase.php';
require_once 'class/PHPExcel.php';

// Reportes de cursos
class CLicenciasRep extends CBase {
   public $pcCodPer, $paCodPer, $pcCodUsu, $pcFilRep;

   public function __construct() {
      parent::__construct();
      $this->paCodPer = null;
   }
   
   // Init de licencias por personal
   public function omInit(){
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      //Carga Empleados
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE cEstado = 'A' ORDER BY cNombre");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL ACTIVO</ERROR></DATA>';
         return false;
      }
      // Carga Tipo
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo,1,1), cDescri FROM V_S02TTAB WHERE cCodTab ='044'");
      $loSql->omDisconnect();
     $j = 0;
     while ($laFila = $loSql->fetch($RS)) {
         $this->paTipo[]=array($laFila[0],$laFila[1]);
         $j++;
      }
      if ($j == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY EVALUACIONES ACTIVO</ERROR></DATA>';
         return false;
      }
      return true;
   }
   // Reporte de Evaluacion Conduccion de Trenes
   public function omReportePruebas() {
      if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }elseif (empty($this->pcTipo)) {
         $this->pcError = '<DATA><ERROR>TIPO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecIni)) {
         $this->pcError = '<DATA><ERROR>FECHA DE INICIO INVALIDA</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecFin)) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif ($this->pdFecIni > $this->pdFecFin) {
         $this->pcError = '<DATA><ERROR>FECHA INICIAL MAYOR QUE FECHA FINAL</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Personal  -----------------------------OJO PERSONAL 99999 CN PRUEBA
     //todos los que rindieorn eficiencia de terreno
      $lcSql = "SELECT DISTINCT B.cCodper,C.cNombre,substr(A.cNroEva,1,1) AS cTipo FROM S02MEVA A
                            INNER JOIN S02DEVA B ON A.cNroEva=B.cNroEva
                            INNER JOIN S02MPER C ON B.cCodPer=C.cCodPer
                            WHERE substr(A.cNroEva,1,1)='$this->pcTipo'AND cCodPer!='999999'
                            ORDER BY cNombre";
      $RS = $loSql->omExec($lcSql);
     // Manda a Excel la informacion
      $loXls = new CXls();
      $loXls->openXls('SEG5181', $this->pcCodUsu);
      // Manda cabecera
      $lnRow = 6;
      while ($laFila = $loSql->fetch($RS)) {
         $lcSql = "SELECT cDesDis,cDesSec,cDesPry,cDescri FROM TABLE (F_S02MPER_1('$laFila[0]'))";
         $RS1 = $loSql->omExec($lcSql);
         $laFila1 = $loSql->fetch($RS1);
       //--
       //Contar Evaluaciones buenas malas y total
        $lcSql = "SELECT cNroEva,cResult FROM S02DEVA WHERE cCodPer ='$laFila[0]' AND substr(cNroEva,1,1)='$this->pcTipo' ORDER BY cNroEva";
       $RS2 = $loSql->omExec($lcSql);
        $llFirst=true;
        $llMalas=0;
        $llTotal=0;
        $llNroEva=null;
       $flag=0;
       while ($laFila2 = $loSql->fetch($RS2)) {
              if(!$llFirst){
                $llNroEva=$laFila2[0];
                  $llFirst=false;
               if($laFila2[1]=='N'){
                  $llMalas++;
                  $flag=1;
               }
            }
            if($llNroEva!=$laFila2[0]){
                 $llTotal++;
               $llNroEva=$laFila2[0];
               $flag=0;
               if($laFila2[1]=='N'){
                  $llMalas++;
                  $flag=1;
               }
            }elseif($flag==0){
                  if($laFila2[1]=='N'){
                  $llMalas++;
                  $flag=1;
               }
            }
      }
       //--
         $loXls->sendXls(0, 'A', $lnRow, $laFila[1]);
         $loXls->sendXls(0, 'B', $lnRow, $laFila1[0]);
         $loXls->sendXls(0, 'C', $lnRow, $laFila1[1]);
         $loXls->sendXls(0, 'D', $lnRow, $laFila1[2]);
         $loXls->sendXls(0, 'E', $lnRow, $laFila1[3]);
       $loXls->sendXls(0, 'F', $lnRow, ($llTotal-$llMalas));
       $loXls->sendXls(0, 'G', $lnRow, $llMalas);
       $loXls->sendXls(0, 'H', $lnRow, $llTotal);
         $lnRow++;
      }
      $loSql->omDisconnect();
      $loXls->closeXls();
      if ($lnRow == 6) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
     }
      $this->pcFilRep = $loXls->pcFilXls;
      return true;
   }
   //Evaluacion de velocidad por radar
   public function omReportePruebas2() {
      if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }elseif (empty($this->pcTipo)) {
         $this->pcError = '<DATA><ERROR>TIPO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecIni)) {
         $this->pcError = '<DATA><ERROR>FECHA DE INICIO INVALIDA</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecFin)) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif ($this->pdFecIni > $this->pdFecFin) {
         $this->pcError = '<DATA><ERROR>FECHA INICIAL MAYOR QUE FECHA FINAL</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Personal  -----------------------------OJO PERSONAL 99999 CN PRUEBA
     //todos los que rindieorn eficiencia de terreno
      $lcSql = "SELECT DISTINCT B.cCodper,C.cNombre,substr(A.cNroEva,1,1) AS cTipo FROM S02MEVA A
                            INNER JOIN S02DEVA B ON A.cNroEva=B.cNroEva
                            INNER JOIN S02MPER C ON B.cCodPer=C.cCodPer
                            WHERE substr(A.cNroEva,1,1)='$this->pcTipo'
                            ORDER BY cNombre";
      $RS = $loSql->omExec($lcSql);
     // Manda a Excel la informacion
      $loXls = new CXls();
      $loXls->openXls('SEG5183', $this->pcCodUsu);
      // Manda cabecera
      $lnRow = 6;
      while ($laFila = $loSql->fetch($RS)) {
         $lcSql = "SELECT cDesDis,cDesSec,cDesPry,cDescri FROM TABLE (F_S02MPER_1('$laFila[0]'))";
         $RS1 = $loSql->omExec($lcSql);
         $laFila1 = $loSql->fetch($RS1);
       //--
       //Contar Evaluaciones buenas malas y total
        $lcSql = "SELECT cNroEva,cResult FROM S02DEVA WHERE cCodPer ='$laFila[0]' AND substr(cNroEva,1,1)='$this->pcTipo' ORDER BY cNroEva";
       $RS2 = $loSql->omExec($lcSql);
        $llFirst=true;
        $llMalas=0;
        $llTotal=0;
        $llNroEva=null;
       $flag=0;
       while ($laFila2 = $loSql->fetch($RS2)) {
              if(!$llFirst){
                $llNroEva=$laFila2[0];
                  $llFirst=false;
               if($laFila2[1]=='M'){
                  $llMalas++;
                  $flag=1;
               }
            }
            if($llNroEva!=$laFila2[0]){
                 $llTotal++;
               $llNroEva=$laFila2[0];
               $flag=0;
               if($laFila2[1]=='M'){
                  $llMalas++;
                  $flag=1;
               }
            }elseif($flag==0){
                  if($laFila2[1]=='M'){
                  $llMalas++;
                  $flag=1;
               }
            }
      }
       //--
         $loXls->sendXls(0, 'A', $lnRow, $laFila[1]);
         $loXls->sendXls(0, 'B', $lnRow, $laFila1[0]);
         $loXls->sendXls(0, 'C', $lnRow, $laFila1[1]);
         $loXls->sendXls(0, 'D', $lnRow, $laFila1[2]);
         $loXls->sendXls(0, 'E', $lnRow, $laFila1[3]);
       $loXls->sendXls(0, 'F', $lnRow, ($llTotal-$llMalas));
       $loXls->sendXls(0, 'G', $lnRow, $llMalas);
       $loXls->sendXls(0, 'H', $lnRow, $llTotal);
         $lnRow++;
      }
      $loSql->omDisconnect();
      $loXls->closeXls();
      if ($lnRow == 6) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
     }
      $this->pcFilRep = $loXls->pcFilXls;
      return true;
   }
   //Eficiencia en terreno
   public function omReportePruebas1() {
      if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }elseif (empty($this->pcTipo)) {
         $this->pcError = '<DATA><ERROR>TIPO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecIni)) {
         $this->pcError = '<DATA><ERROR>FECHA DE INICIO INVALIDA</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecFin)) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif ($this->pdFecIni > $this->pdFecFin) {
         $this->pcError = '<DATA><ERROR>FECHA INICIAL MAYOR QUE FECHA FINAL</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Personal  -----------------------------OJO PERSONAL 99999 CN PRUEBA
     //todos los que rindieorn eficiencia de terreno
      $lcSql = "SELECT DISTINCT B.cCodper,C.cNombre,substr(A.cNroEva,1,1) AS cTipo FROM S02MEVA A
                            INNER JOIN S02DEVA B ON A.cNroEva=B.cNroEva
                            INNER JOIN S02MPER C ON B.cCodPer=C.cCodPer
                            WHERE substr(A.cNroEva,1,1)='$this->pcTipo'
                            ORDER BY cNombre";
      $RS = $loSql->omExec($lcSql);
     // Manda a Excel la informacion
      $loXls = new CXls();
      $loXls->openXls('SEG5182', $this->pcCodUsu);
      // Manda cabecera
      $lnRow = 6;
      while ($laFila = $loSql->fetch($RS)) {
         $lcSql = "SELECT cDesDis,cDesSec,cDesPry,cDescri FROM TABLE (F_S02MPER_1('$laFila[0]'))";
         $RS1 = $loSql->omExec($lcSql);
         $laFila1 = $loSql->fetch($RS1);
       //--
       //Contar Evaluaciones buenas malas y total
        $lcSql = "SELECT cNroEva,cResult FROM S02DEVA WHERE cCodPer ='$laFila[0]' AND substr(cNroEva,1,1)='$this->pcTipo' ORDER BY cNroEva";
       $RS2 = $loSql->omExec($lcSql);
        $llFirst=true;
        $llMalas=0;
        $llTotal=0;
        $llNroEva=null;
       $flag=0;
       while ($laFila2 = $loSql->fetch($RS2)) {
              if(!$llFirst){
                $llNroEva=$laFila2[0];
                  $llFirst=false;
               if($laFila2[1]=='M'){
                  $llMalas++;
                  $flag=1;
               }
            }
            if($llNroEva!=$laFila2[0]){
                 $llTotal++;
               $llNroEva=$laFila2[0];
               $flag=0;
               if($laFila2[1]=='M'){
                  $llMalas++;
                  $flag=1;
               }
            }elseif($flag==0){
                  if($laFila2[1]=='M'){
                  $llMalas++;
                  $flag=1;
               }
            }
      }
       //--
         $loXls->sendXls(0, 'A', $lnRow, $laFila[1]);
         $loXls->sendXls(0, 'B', $lnRow, $laFila1[0]);
         $loXls->sendXls(0, 'C', $lnRow, $laFila1[1]);
         $loXls->sendXls(0, 'D', $lnRow, $laFila1[2]);
         $loXls->sendXls(0, 'E', $lnRow, $laFila1[3]);
       $loXls->sendXls(0, 'F', $lnRow, ($llTotal-$llMalas));
       $loXls->sendXls(0, 'G', $lnRow, $llMalas);
       $loXls->sendXls(0, 'H', $lnRow, $llTotal);
         $lnRow++;
      }
      $loSql->omDisconnect();
      $loXls->closeXls();
      if ($lnRow == 6) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
     }
      $this->pcFilRep = $loXls->pcFilXls;
      return true;
   }
   //Reporte evaluacion de tractos
   public function omReportePruebas3() {
      if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }elseif (empty($this->pcTipo)) {
         $this->pcError = '<DATA><ERROR>TIPO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecIni)) {
         $this->pcError = '<DATA><ERROR>FECHA DE INICIO INVALIDA</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecFin)) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif ($this->pdFecIni > $this->pdFecFin) {
         $this->pcError = '<DATA><ERROR>FECHA INICIAL MAYOR QUE FECHA FINAL</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Personal  -----------------------------OJO PERSONAL 99999 CN PRUEBA
     //todos los que rindieorn eficiencia de terreno
      $lcSql = "SELECT DISTINCT B.cCodper,C.cNombre,substr(A.cNroEva,1,1) AS cTipo FROM S02MEVA A
                            INNER JOIN S02DEVA B ON A.cNroEva=B.cNroEva
                            INNER JOIN S02MPER C ON B.cCodPer=C.cCodPer
                            WHERE substr(A.cNroEva,1,1)='$this->pcTipo'
                            ORDER BY cNombre";
      $RS = $loSql->omExec($lcSql);
     // Manda a Excel la informacion
      $loXls = new CXls();
      $loXls->openXls('SEG5184', $this->pcCodUsu);
      // Manda cabecera
      $lnRow = 6;
      while ($laFila = $loSql->fetch($RS)) {
         $lcSql = "SELECT cDesDis,cDesSec,cDesPry,cDescri FROM TABLE (F_S02MPER_1('$laFila[0]'))";
         $RS1 = $loSql->omExec($lcSql);
         $laFila1 = $loSql->fetch($RS1);
       //--
       //Contar Evaluaciones buenas malas y total
        $lcSql = "SELECT cNroEva,cResult FROM S02DEVA WHERE cCodPer ='$laFila[0]' AND substr(cNroEva,1,1)='$this->pcTipo' ORDER BY cNroEva";
       $RS2 = $loSql->omExec($lcSql);
        $llFirst=true;
        $llMalas=0;
        $llTotal=0;
        $llNroEva=null;
       $flag=0;
       while ($laFila2 = $loSql->fetch($RS2)) {
              if(!$llFirst){
                $llNroEva=$laFila2[0];
                  $llFirst=false;
               if($laFila2[1]=='N'){
                  $llMalas++;
                  $flag=1;
               }
            }
            if($llNroEva!=$laFila2[0]){
                 $llTotal++;
               $llNroEva=$laFila2[0];
               $flag=0;
               if($laFila2[1]=='N'){
                  $llMalas++;
                  $flag=1;
               }
            }elseif($flag==0){
                  if($laFila2[1]=='N'){
                  $llMalas++;
                  $flag=1;
               }
            }
      }
       //--
         $loXls->sendXls(0, 'A', $lnRow, $laFila[1]);
         $loXls->sendXls(0, 'B', $lnRow, $laFila1[0]);
         $loXls->sendXls(0, 'C', $lnRow, $laFila1[1]);
         $loXls->sendXls(0, 'D', $lnRow, $laFila1[2]);
         $loXls->sendXls(0, 'E', $lnRow, $laFila1[3]);
       $loXls->sendXls(0, 'F', $lnRow, ($llTotal-$llMalas));
       $loXls->sendXls(0, 'G', $lnRow, $llMalas);
       $loXls->sendXls(0, 'H', $lnRow, $llTotal);
         $lnRow++;
      }
      $loSql->omDisconnect();
      $loXls->closeXls();
      if ($lnRow == 6) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
     }
      $this->pcFilRep = $loXls->pcFilXls;
      return true;
   }
   // Reporte de licencias por personal
   public function omLicenciasPersonal() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>PERSONAL NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Personal
      $RS = $loSql->omExec("SELECT cNombre FROM S02MPER WHERE cCodPer = '$this->pcCodPer'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL NO EXISTE</ERROR></DATA>';
         return false;
      }
      $lcNombre = $laFila[0];
      $lcSql = "SELECT cTipLic, cDesLic, cNroLic, TO_CHAR(dFecVig, 'yyyy-mm-dd'), 
                TO_CHAR(dFecFin, 'yyyy-mm-dd'), cDesEst, cAutori, cRestri, cDesSec, cDesEmp 
                FROM V_S02DLIC WHERE cCodPer = '$this->pcCodPer' ORDER BY dFecVig";
     $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      // Manda a Excel la informacion
      $loXls = new CXls();
      $loXls->openXls('SEG5160', $this->pcCodUsu);
      // Manda cabecera
      $loXls->sendXls(0, 'C', 3, "'".$this->pcCodPer);
      $loXls->sendXls(0, 'D', 3, $lcNombre);
      $lnRow = 5;
      while ($laFila = $loSql->fetch($RS)) {
         $loXls->sendXls(0, 'B', $lnRow, $laFila[0]);
         $loXls->sendXls(0, 'C', $lnRow, $laFila[1]);
         $loXls->sendXls(0, 'D', $lnRow, $laFila[2]);
         $loXls->sendXls(0, 'E', $lnRow, $laFila[3]);
         $loXls->sendXls(0, 'F', $lnRow, $laFila[4]);
         $loXls->sendXls(0, 'G', $lnRow, $laFila[5]);
         $loXls->sendXls(0, 'H', $lnRow, $laFila[6]);
         $loXls->sendXls(0, 'I', $lnRow, $laFila[7]);
         $loXls->sendXls(0, 'J', $lnRow, $laFila[9]);
         //$loXls->sendXls(0, 'K', $lnRow, $laFila[9]);
         $lnRow++;
      }
      $loXls->closeXls();
      if ($lnRow == 5) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
     }
      $this->pcFilRep = $loXls->pcFilXls;
      return true;
   }

   public function omLicenciasPersonalVigente() {
      if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cNombre, cTipLic, cDesLic, cNroLic, TO_CHAR(dFecVig, 'yyyy-mm-dd') AS dFecVig, 
                TO_CHAR(dFecFin, 'yyyy-mm-dd') AS dFecFin, cDesEst, cAutori, cDesRes, cDesSec, cDesEmp 
                FROM V_S02DLIC WHERE  cEstado = 'A' OR cEstado = 'D'
                ORDER BY cNombre,dFecVig";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      // Manda a Excel la informacion
      $loXls = new CXls();
      $loXls->openXls('SEG5170', $this->pcCodUsu);
      // Manda cabecera
      $lnRow = 4;
      while ($laFila = $loSql->fetch($RS)) {
         $loXls->sendXls(0, 'B', $lnRow, $laFila[0]);
         $loXls->sendXls(0, 'C', $lnRow, $laFila[1]);
         $loXls->sendXls(0, 'D', $lnRow, $laFila[2]);
         $loXls->sendXls(0, 'E', $lnRow, "'".$laFila[3]);
         $loXls->sendXls(0, 'F', $lnRow, $laFila[4]);
         $loXls->sendXls(0, 'G', $lnRow, $laFila[5]);
         $loXls->sendXls(0, 'H', $lnRow, $laFila[6]);
         $loXls->sendXls(0, 'I', $lnRow, $laFila[7]);
         $loXls->sendXls(0, 'J', $lnRow, $laFila['CDESSEC']);
         $loXls->sendXls(0, 'K', $lnRow, $laFila['CDESRES']);
         $loXls->sendXls(0, 'L', $lnRow, $laFila['CDESEMP']);
         $lnRow++;
      }
      $loXls->closeXls();
      if ($lnRow == 4) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
     }
      $this->pcFilRep = $loXls->pcFilXls;
      return true;
   }

   public function omLicenciasVencer() {
      if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecIni)) {
         $this->pcError = '<DATA><ERROR>FECHA INICIAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecFin)) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif ($this->pdFecFin < $this->pdFecIni) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL MENOR QUE FECHA INICIAL</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cNombre, cTipLic, cDesLic, cNroLic, TO_CHAR(dFecVig, 'yyyy-mm-dd') AS dFecVig, 
                TO_CHAR(dFecFin, 'yyyy-mm-dd') AS dFecFin, cDesEst, cAutori, cDesRes, cDesSec, cDesEmp 
                FROM V_S02DLIC WHERE  cEstado IN ('A', 'D') AND dFecFin BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd')
                ORDER BY cNombre,dFecVig";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      // Manda a Excel la informacion
      $loXls = new CXls();
      $loXls->openXls('SEG5190', $this->pcCodUsu);
      $loXls->sendXls(0, 'C', 3, $this->pdFecIni);
      $loXls->sendXls(0, 'C', 4, $this->pdFecFin);
      // Manda cabecera
      $lnRow = 6;
      while ($laFila = $loSql->fetch($RS)) {
         $loXls->sendXls(0, 'B', $lnRow, $laFila[0]);
         $loXls->sendXls(0, 'C', $lnRow, $laFila[1]);
         $loXls->sendXls(0, 'D', $lnRow, $laFila[2]);
         $loXls->sendXls(0, 'E', $lnRow, "'".$laFila[3]);
         $loXls->sendXls(0, 'F', $lnRow, $laFila[4]);
         $loXls->sendXls(0, 'G', $lnRow, $laFila[5]);
         $loXls->sendXls(0, 'H', $lnRow, $laFila[6]);
         $loXls->sendXls(0, 'I', $lnRow, $laFila[7]);
         $loXls->sendXls(0, 'J', $lnRow, $laFila['CDESSEC']);
         $loXls->sendXls(0, 'K', $lnRow, $laFila['CDESRES']);
         $loXls->sendXls(0, 'L', $lnRow, $laFila['CDESEMP']);
         $lnRow++;
      }
      $loXls->closeXls();
      if ($lnRow == 6) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
     }
      $this->pcFilRep = $loXls->pcFilXls;
      return true;
   }
}
?>