<?php
require_once 'Clases/CBase.php';
require_once 'class/PHPExcel.php';

// Reportes de cursos
class CSancionesRep extends CBase {
   public $pcCodPer, $pdFecIni, $pdFecFin, $paCodPer, $pcCodUsu, $pcFilRep;

   public function __construct() {
      parent::__construct();
      $this->paCodPer = null;
   }
   
   // Init de sanciones por personal, desde-hasta
   public function omInit(){
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      //Carga Empleados
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE cEstado = 'A' ORDER BY cNombre");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL ACTIVO</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   // Reporte de sanciones por personal
   public function omSancionesPersonal() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>PERSONAL NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecIni)) {
         $this->pcError = '<DATA><ERROR>FECHA INICIAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecFin)) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif ($this->pdFecFin < $this->pdFecFin) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL ES MENOR QUE FECHA INICIAL</ERROR></DATA>';
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
      $lcSql = "SELECT cCodigo, cCodNor, cDesNor, cCodSan, cDesSan, TO_CHAR(dFecha, 'yyyy-mm-dd') FROM V_S02DSAN 
                WHERE cCodPer = '$this->pcCodPer' AND cEstado = 'A' AND 
                dFecha BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd')
                ORDER BY dFecha DESC, cCodigo, cCodNor";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      // Manda a Excel la informacion
      $loXls = new CXls();
      $loXls->openXls('SEG5150', $this->pcCodUsu);
      // Manda cabecera
      $loXls->sendXls(0, 'C', 3, "'".$this->pcCodPer);
      $loXls->sendXls(0, 'D', 3, $lcNombre);
      $loXls->sendXls(0, 'C', 4, $this->pdFecIni);
      $loXls->sendXls(0, 'C', 5, $this->pdFecFin);
      $lnRow = 7;
      while ($laFila = $loSql->fetch($RS)) {
         $loXls->sendXls(0, 'B', $lnRow, "'".$laFila[0]);
         $loXls->sendXls(0, 'C', $lnRow, $laFila[1]);
         $loXls->sendXls(0, 'D', $lnRow, $laFila[2]);
         $loXls->sendXls(0, 'E', $lnRow, "'".$laFila[3]);
         $loXls->sendXls(0, 'F', $lnRow, $laFila[4]);
         $loXls->sendXls(0, 'G', $lnRow, $laFila[5]);
         $lnRow++;
      }
      $loXls->closeXls();
      if ($lnRow == 7) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
     }
      $this->pcFilRep = $loXls->pcFilXls;
      return true;
   }
   
   
   ///////////////////////////////////////////////

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
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR ORDER BY cCodNor");
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
      }elseif (empty($this->pcCodNor)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE NORMA NO DEFINIDA</ERROR></DATA>';
         return false;
      }elseif (empty($this->pcCodUsu)) {
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
      // Manda a Excel la informacion
      $llFirst = true;
      $loXls = new CXls();
      $loXls->openXls('SEG5130', $this->pcCodUsu);
      $lnRow = 6;
      while ($laFila = $loSql->fetch($RS)) {
         if ($llFirst) {
       echo $laFila[0];
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
      //echo "<a href = '$lo->pcFilRep'>Archivo de Salida</a>";
      return true;
   }
   public function omRepPersonalTipo() {
      if (empty($this->pcTipo)) {
          $this->pcError = '<DATA><ERROR>TIPO NO DEFINIDO</ERROR></DATA>';
           return false;
      }elseif (!fxValDate($this->pdFecIni)) {
           $this->pcError = '<DATA><ERROR>FECHA INICIAL INVALIDA</ERROR></DATA>';
           return false;
      }elseif (!fxValDate($this->pdFecFin)) {
          $this->pcError = '<DATA><ERROR>FECHA FINAL INVALIDA</ERROR></DATA>';
           return false;
      }elseif ($this->pdFecFin<$this->pdFecIni) {
          $this->pcError = '<DATA><ERROR>FECHA FINAL MENOR A FECHA INICIAL</ERROR></DATA>';
           return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cCodCur, cDescri, cDesTip, TO_CHAR(dFecha,'yyyy-mm-dd') AS DFECHA, cTiempo, cNomDoc 
         FROM V_S02MCUR_1 
         WHERE cTipo = '$this->pcTipo' and dFecha 
         BETWEEN TO_DATE('$this->pdFecIni','yyyy-mm-dd') AND TO_DATE('$this->pdFecFin','yyyy-mm-dd') ORDER BY dFecha DESC";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      // Manda a Excel la informacion
      $llFirst = true;
      $loXls = new CXls();
      $loXls->openXls('SEG5140', $this->pcCodUsu);
      $lnRow = 4;
      while ($laFila = $loSql->fetch($RS)) {
         if ($llFirst) {
           $loXls->sendXls(0, 'D', 2, $laFila['CDESTIP']);
            $llFirst = false;
         }
         $loXls->sendXls(0, 'B', $lnRow, (string)$laFila['CCODCUR']);
         $loXls->sendXls(0, 'C', $lnRow, $laFila['CDESCRI']);
         $loXls->sendXls(0, 'D', $lnRow, $laFila['CDESTIP']);
         $loXls->sendXls(0, 'E', $lnRow, $laFila['DFECHA']);
        $loXls->sendXls(0, 'F', $lnRow, $laFila['CTIEMPO']);
         $loXls->sendXls(0, 'G', $lnRow, $laFila['CNOMDOC']);
         $lnRow++;
      }
      $loXls->closeXls();
     if ($lnRow == 4) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
     }
      $this->pcFilRep = $loXls->pcFilXls;
      //echo "<a href = '$lo->pcFilRep'>Archivo de Salida</a>";
      return true;
   }
   public function omRepPersonalTipoDatos() {
      if (empty($this->pcTipo)) {
          $this->pcError = '<DATA><ERROR>TIPO NO DEFINIDO</ERROR></DATA>';
           return false;
      }elseif ($this->pmDatos) {
           $this->pcError = '<DATA><ERROR>DESCRIPCION INVALIDA</ERROR></DATA>';
           return false;
      }elseif (!fxValDate($this->pdFecIni)) {
           $this->pcError = '<DATA><ERROR>FECHA INICIAL INVALIDA</ERROR></DATA>';
           return false;
      }elseif (!fxValDate($this->pdFecFin)) {
          $this->pcError = '<DATA><ERROR>FECHA FINAL INVALIDA</ERROR></DATA>';
           return false;
      }elseif ($this->pdFecFin < $this->pdFecIni) {
          $this->pcError = '<DATA><ERROR>FECHA FINAL MENOR A FECHA INICIAL</ERROR></DATA>';
           return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cCodCur, cDescri, cDesTip, TO_CHAR(dFecha,'yyyy-mm-dd') AS DFECHA, cTiempo, cNomDoc, mDatos 
         FROM V_S02MCUR_1 
         WHERE cTipo = '$this->pcTipo' AND mDatos='$this->pmDatos'
         and dFecha 
         BETWEEN TO_DATE('$this->pdFecIni','yyyy-mm-dd') AND TO_DATE('$this->pdFecFin','yyyy-mm-dd') ORDER BY dFecha DESC";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      // Manda a Excel la informacion
      $llFirst = true;
      $loXls = new CXls();
      $loXls->openXls('SEG5150', $this->pcCodUsu);
      $lnRow = 5;
      while ($laFila = $loSql->fetch($RS)) {
         if ($llFirst) {
           $loXls->sendXls(0, 'D', 2, $laFila['CDESTIP']);
                $loXls->sendXls(0, 'D', 3, $laFila['MDATOS']->load());
            $llFirst = false;
         }
         $loXls->sendXls(0, 'B', $lnRow, (string)$laFila['CCODCUR']);
         $loXls->sendXls(0, 'C', $lnRow, $laFila['CDESCRI']);
         $loXls->sendXls(0, 'D', $lnRow, $laFila['CDESTIP']);
         $loXls->sendXls(0, 'E', $lnRow, $laFila['DFECHA']);
        $loXls->sendXls(0, 'F', $lnRow, $laFila['CTIEMPO']);
         $loXls->sendXls(0, 'G', $lnRow, $laFila['CNOMDOC']);
         $lnRow++;
      }
      $loXls->closeXls();
     if ($lnRow == 5) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
     }
      $this->pcFilRep = $loXls->pcFilXls;
      //echo "<a href = '$lo->pcFilRep'>Archivo de Salida</a>";
      return true;
   }
   public function omReporteCurso() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>PERSONAL NO DEFINIDO</ERROR></DATA>';
         return false;
      }elseif (!fxValDate($this->pdFecIni)) {
         $this->pcError = '<DATA><ERROR>FECHA INICIAL INVALIDA</ERROR></DATA>';
         return false;
      }elseif (!fxValDate($this->pdFecFin)) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL INVALIDA</ERROR></DATA>';
         return false;
      }elseif ($this->pdFecFin < $this->pdFecIni) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL ES MENOR QUE FECHA INCIAL</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
/*      $lcSql = "SELECT A.cCodPer, A.cCodCur, A.nNota, B.cNombre, C.cTipo, F.cDescri AS cDesTip,
                            C.cDescri AS cNomCur, C.cNomDoc, TO_CHAR(C.dFecha,'yyyy-mm-dd') AS DFECHA,
                            C.cTiempo, C.mdatos, E.cCodNor, E.cDescri 
                            FROM S02DCAP A 
                            INNER JOIN S03MPER B ON A.cCodPer = B.cCodPer 
                            INNER JOIN S02MCUR C ON A.cCodCur = C.cCodCur 
                            INNER JOIN S02PCUR D ON C.cCodCur = D.cCodCur 
                            INNER JOIN S02MNOR E ON D.cCodNor = E.cCodNor 
                            INNER JOIN V_S02TTAB F ON C.cTipo = F.cCodigo 
                            AND F.cCodTab = '055' 
                            where A.ccodper = '$this->pcCodPer' AND 
                            dFecha BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd') 
                            order by ccodcur, ccodnor";*/
      $lcSql = "SELECT cCodPer, cCodCur, nNota, cNombre, cTipo, cDesTip, cNomCur, cNomDoc, 
                TO_CHAR(dFecha,'yyyy-mm-dd') AS dFecha, cTiempo, mDatos, cCodNor, cDescri 
                FROM V_S02DCAP_1
                WHERE cCodPer = '$this->pcCodPer' AND 
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
         }elseif ($lcCodCur != $laFila['CCODCUR']) {
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
         }else {
            array_push($laTabla[0],$laFila['CCODNOR']);
            array_push($laTabla[1],$laFila['CDESCRI']);
            $lmDatos = $laFila['MDATOS']->load();
         }
      }
      array_push($laTabla[0], '');
      array_push($laTabla[1], $lmDatos);
      $loSql->omDisconnect();
      // Construir Informe en RTF
      $laArray = array('#*DFECHA*#'=>$ldFecha,
                       '#*DFECINI*#'=>$this->pdFecIni,
                       '#*DFECFIN*#'=>$this->pdFecFin, 
                       '#*CNOMBRE*#'=>$lcNombre);
      $laDim = array(1300,9200);
      $laAArray = array('#*TABLA1*#'=>array(fxCount($laTabla), fxCount($laTabla[0]), $laTabla, $laDim));
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
      echo "<a href = '$lo->pcFilRet'>Archivo de Salida</a>";
      return true;
   }
}
?>