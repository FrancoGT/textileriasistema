<?php
require_once 'Clases/CBase.php';
require_once 'class/PHPExcel.php';

// Reportes de cursos
class CCursosRep extends CBase {
   public $pcNroInc, $pdFecInc, $pmDescri, $paCodNor, $paCodPer, $pcCodUsu, $pcFilRet;

   public function __construct() {
      parent::__construct();
      $this->paCodNor = $this->paCodPer = null;
   }
   
   // Init de capacitaciones por personal, desde-hasta
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
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL ACTIVO</ERROR></DATA>';
         return false;
      }
      //CargaTipo
      $RS = $loSql->omExec("SELECT cCodigo, cDescri FROM V_S02TTAB WHERE cCodTab='055'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paTipo[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY EMPLEADOS ACTIVOS</ERROR></DATA>';
         return false;
      }
      //CargaDatos
      $RS = $loSql->omExec("SELECT cCodCur,mDatos FROM V_S02MCUR_1 ORDER BY cCodCUR DESC");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paDatos[] = array($laFila[0], $laFila[1]->load());
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY DATOS EN DESCRIPCION</ERROR></DATA>';
         return false;
      }
      $loSql->omDisconnect();
      return true;
   }

   // Init de capacitaciones por personal y norma
   public function omInit1(){
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Empleados
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE cEstado = 'A' ORDER BY cNombre");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL ACTIVO</ERROR></DATA>';
         return false;
      }
      // Carga normas
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR WHERE cEstado = 'A' ORDER BY cCodNor");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodNor[] = array($laFila[0], trim($laFila[0]).' '.$laFila[1]);
         $i++;
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY NORMAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      return true;
   }

   public function omRepPersonalNormal() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>PERSONAL NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodNor)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE NORMA NO DEFINIDA</ERROR></DATA>';
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
      $lcSql = "SELECT cCodPer, cCodCur, nNota, cNombre, cTipo, cDesTip, cNomCur, cNomDoc, 
                TO_CHAR(dFecha,'yyyy-mm-dd') AS dFecha, cTiempo, cCodNor, cDescri 
                FROM V_S02DCAP_1
                WHERE cCodPer = '$this->pcCodPer' AND cCodNor = '$this->pcCodNor'
                ORDER BY dFecha DESC";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      // MANDa a Excel la informacion
      $llFirst = true;
      $loXls = new CXls();
      $loXls->openXls('SEG5130', $this->pcCodUsu);
      $lnRow = 6;
      while ($laFila = $loSql->fetch($RS)) {
         if ($llFirst) {
            $loXls->sendXls(0, 'C', 3, (string)$laFila[0]);
            $loXls->sendXls(0, 'D', 3, $laFila[3]);
            $loXls->sendXls(0, 'C', 4, $laFila[10]);
            $loXls->sendXls(0, 'D', 4, $laFila[11]);
            $llFirst = false;
         }
         $loXls->sendXls(0, 'B', $lnRow, (string)$laFila[1]);
         $loXls->sendXls(0, 'C', $lnRow, $laFila[6]);
         $loXls->sendXls(0, 'D', $lnRow, $laFila[5]);
         $loXls->sendXls(0, 'E', $lnRow, $laFila[8]);
       $lxNota = ($laFila[2] < 0) ? 'S/N' : $laFila[2];
         $loXls->sendXls(0, 'F', $lnRow, $lxNota);
         $loXls->sendXls(0, 'G', $lnRow, $laFila[9]);
         $loXls->sendXls(0, 'H', $lnRow, $laFila[7]);
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

   public function omRepPersonalTipo() {
      if (empty($this->pcTipo)) {
         $this->pcError = '<DATA><ERROR>TIPO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecIni)) {
         $this->pcError = '<DATA><ERROR>FECHA INICIAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecFin)) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif ($this->pdFecFin<$this->pdFecIni) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL MENOR A FECHA INICIAL</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cCodCur, cDescri, cDesTip, TO_CHAR(dFecha,'yyyy-mm-dd') AS dFecha, cTiempo, cNomDoc,
                cCodPer, cNombre, nNota FROM V_S02MCUR_1 
                WHERE cTipo = '$this->pcTipo' AND 
                dFecha BETWEEN TO_DATE('$this->pdFecIni','yyyy-mm-dd') AND TO_DATE('$this->pdFecFin','yyyy-mm-dd') 
                ORDER BY dFecha, cNombre";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      // Manda a Excel la informacion
      $llFirst = true;
      $loXls = new CXls();
      $loXls->openXls('SEG5140', $this->pcCodUsu);
      $loXls->sendXls(0, 'C', 3, $this->pdFecIni);
      $loXls->sendXls(0, 'C', 4, $this->pdFecFin);
      $lnRow = 6;
      while ($laFila = $loSql->fetch($RS)) {
         if ($llFirst) {
            $loXls->sendXls(0, 'D', 2, $laFila['CDESTIP']);
            $llFirst = false;
         }
         $loXls->sendXls(0, 'B', $lnRow, (string)$laFila['CCODCUR']);
         $loXls->sendXls(0, 'C', $lnRow, $laFila['CDESCRI']);
         $loXls->sendXls(0, 'D', $lnRow, $laFila['DFECHA']);
         $loXls->sendXls(0, 'E', $lnRow, $laFila['CTIEMPO']);
         $loXls->sendXls(0, 'F', $lnRow, $laFila['CNOMDOC']);
         $loXls->sendXls(0, 'G', $lnRow, $laFila['CCODPER']);
         $loXls->sendXls(0, 'H', $lnRow, $laFila['CNOMBRE']);
         $loXls->sendXls(0, 'I', $lnRow, ($laFila['NNOTA'] == -1) ? 'S/N' : $laFila['NNOTA']);
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
   
   public function omReporteCurso() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>PERSONAL NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecIni)) {
         $this->pcError = '<DATA><ERROR>FECHA INICIAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecFin)) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif ($this->pdFecFin < $this->pdFecIni) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL ES MENOR QUE FECHA INCIAL</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cCodPer, cCodCur, nNota, cNombre, cTipo, cDesTip, cNomCur, cNomDoc, 
                TO_CHAR(dFecha,'yyyy-mm-dd') AS dFecha, cTiempo, mDatos, cCodNor, cDescri 
                FROM V_S02DCAP_1 WHERE cCodPer = '$this->pcCodPer' AND 
                dFecha BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd') 
                ORDER BY cCodCur, cCodNor";
      $RS = $loSql->omExec($lcSql);
      $llFirst = true;
      $laTabla = null;
      $laTabla = array(array(),array());
      $i = 0;
      while($laFila = $loSql->fetch($RS)){
         if ($llFirst) {
            $lcNombre = $laFila['CNOMBRE'];
            array_push($laTabla[0], '');
            array_push($laTabla[1], '');
            array_push($laTabla[0], '\b2'.'CURSO');
            array_push($laTabla[1], $laFila['CNOMCUR']);
            array_push($laTabla[0], '\b2'.'EXPOSITOR');
            array_push($laTabla[1], $laFila['CNOMDOC']);
            array_push($laTabla[0], '\b2'.'NORMA');
            array_push($laTabla[1], '\b2'.'DESCRIPCION');
            array_push($laTabla[0], $laFila['CCODNOR']);
            array_push($laTabla[1], $laFila['CDESCRI']);
            $llFirst = false;
            $lcCodCur = $laFila['CCODCUR'];
         } elseif ($lcCodCur != $laFila['CCODCUR']) {
            array_push($laTabla[0], '');
            array_push($laTabla[1], $lmDatos);
            array_push($laTabla[0], '\b2'.'CURSO');
            array_push($laTabla[1], $laFila['CNOMCUR']);
            array_push($laTabla[0], '\b2'.'EXPOSITOR');
            array_push($laTabla[1], $laFila['CNOMDOC']);
            array_push($laTabla[0], '\b2'.'NORMA');
            array_push($laTabla[1], '\b2'.'DESCRIPCION');
            array_push($laTabla[0],$laFila['CCODNOR']);
            array_push($laTabla[1],$laFila['CDESCRI']);
            $lmDatos = $laFila['MDATOS']->load();
            $lcCodCur = $laFila['CCODCUR'];
         } else {
            array_push($laTabla[0],$laFila['CCODNOR']);
            array_push($laTabla[1],$laFila['CDESCRI']);
            $lmDatos = $laFila['MDATOS']->load();
         }
      }
      array_push($laTabla[0], '');
      array_push($laTabla[1], $lmDatos);
      $loSql->omDisconnect();
      // Construir Informe en RTF
      $laArray = array('#*DFECHA*#'=>$ldFecha, '#*DFECINI*#'=>$this->pdFecIni,
                       '#*DFECFIN*#'=>$this->pdFecFin, '#*CNOMBRE*#'=>$lcNombre);
      $laDim = array(1300, 9200);
      $laAArray = array('#*TABLA1*#'=>array(count($laTabla), count($laTabla[0]), $laTabla, $laDim));
      $lo = new CRtf();
      $lo->pcFile = 'CURSO01_';
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omInicializar();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR 1</ERROR></DATA>';
         return false;
      }
      $lo->paArray = $laArray;
      $llOk = $lo->omGenerar();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR 2</ERROR></DATA>';
         return false;
      }
      $lo->paAArray = $laAArray;
      $llOk = $lo->omGenerarArray();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR 3</ERROR></DATA>';
         return false;
      }
      $lo->paAArray = $laAArray;
      $llOk = $lo->omGenerarArray(true);
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR 3</ERROR></DATA>';
         return false;
      }
      $this->pcFilRet = $lo->pcFilRet;
      return true;
   }
   
   // Resumen, con total de participantes
   public function omRepPersonalTipo1() {
      if (empty($this->pcTipo)) {
         $this->pcError = '<DATA><ERROR>TIPO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecIni)) {
         $this->pcError = '<DATA><ERROR>FECHA INICIAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecFin)) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif ($this->pdFecFin<$this->pdFecIni) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL MENOR A FECHA INICIAL</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT * FROM (
                SELECT DISTINCT A.cCodCur, A.cDescri, A.cDesTip, TO_CHAR(A.dFecha,'yyyy-mm-dd') AS dFecha, A.cTiempo, A.cNomDoc,
                (SELECT COUNT(B.cCodPer) FROM S02DCAP B WHERE B.cCodCur = A.cCodCur) AS nNumPar
                FROM V_S02MCUR_1 A WHERE A.cTipo = '01') 
                ORDER BY dFecha";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      // Manda a Excel la informacion
      $llFirst = true;
      $loXls = new CXls();
      $loXls->openXls('SEG5240', $this->pcCodUsu);
      $loXls->sendXls(0, 'C', 3, $this->pdFecIni);
      $loXls->sendXls(0, 'C', 4, $this->pdFecFin);
      $lnRow = 6;
      while ($laFila = $loSql->fetch($RS)) {
         if ($llFirst) {
            $loXls->sendXls(0, 'D', 2, $laFila['CDESTIP']);
            $llFirst = false;
         }
         $loXls->sendXls(0, 'B', $lnRow, (string)$laFila['CCODCUR']);
         $loXls->sendXls(0, 'C', $lnRow, $laFila['CDESCRI']);
         $loXls->sendXls(0, 'D', $lnRow, $laFila['DFECHA']);
         $loXls->sendXls(0, 'E', $lnRow, $laFila['CTIEMPO']);
         $loXls->sendXls(0, 'F', $lnRow, $laFila['CNOMDOC']);
         $loXls->sendXls(0, 'G', $lnRow, $laFila['NNUMPAR']);
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