<?php
require_once 'Clases/CBase.php';
require_once 'class/PHPExcel.php';

//-------------------------------------
// Mantenimiento de Empleados
//-------------------------------------
class CConsultasXls extends CBase {
   public $pcCodUsu, $paData, $paFila, $paCodPer, $paCodEmp, $paCodPry, $paTipInc, $paArea, $paCodUni,
          $paSubDiv, $paHeridos, $paLugar, $paCodFcr, $pcNroEva, $paCodCur, $paCodNor, $paTipCur, $paTipEva,
          $paTipVag, $paSuperv, $paTipLic, $paEstado, $paSecAut, $paRestri, $paSector, $paCodSan, $paDataLic;
   protected $lcFilXls;
   
   public function __construct() {
      parent::__construct();
      $this->paFila = $this->paCodPer =  $this->paCodEmp = $this->paCodPry = $this->paTipInc = $this->paArea = 
      $this->paCodUni = $this->paSubDiv = $this->paData = $this->paLugar = $this->paHeridos = $this->paCodCur =
      $this->paCodNor = $this->paTipCur = $this->paTipEva = $this->paTipVag = $this->paSuperv = 
      $this->paTipLic = $this->paEstado = $this->paSecAut = $this->paRestri = $this->paSector = 
      $this->paCodSan = $this->paDataLic =null;
   }

   public function omInitIncidentesFerroviarios() {
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }  
      // Trae personal
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE SUBSTR(cCodPer, 1, 1) != '9' ORDER BY cNombre");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL ACTIVO</ERROR></DATA>';
         return false;
      }
      $this->paCodPer[] = array('*', '*** TODOS ***');
      // Carga causas
      $RS = $loSql->omExec("SELECT cCodFcr, cDescri FROM S02MFCR WHERE cEstado = 'A' AND cTipo = '1' ORDER BY cCodFcr");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodFcr[] = array($laFila[0], trim($laFila[0]).'-'.$laFila[1]);
      }
      $this->paCodFcr[] = array('*', '*** TODAS ***');
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>MAESTRO DE CAUSAS (FCR) NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Unidades
      $RS = $loSql->omExec("SELECT cCodUni, cDescri FROM S02MUNI WHERE cCodUni != '99999' ORDER BY cCodUni");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodUni[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY UNIDADES ACTIVAS</ERROR></DATA>';
         return false;
      }
      $this->paCodUni[] = array('*', '* TODOS');
      // Empresas
      $RS = $loSql->omExec("SELECT cCodEmp, cDescri FROM S02MEMP WHERE cCodEmp != '999' ORDER BY nOrden, cCodEmp");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodEmp[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY EMPRESAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      $this->paCodEmp[] = array('*', '*** TODAS ***');
      // Carga Estaciones
      $RS = $loSql->omExec("SELECT TRIM(cCodigo), cDescri FROM V_S02TTAB WHERE cCodTab = '021'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstaci[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ESTACIONES DEFINIDAS. TABLA [021]</ERROR></DATA>';
         return false;
      }
      // Proyectos
      $RS = $loSql->omExec("SELECT cCodPry, cDescri FROM S02MPRY ORDER BY cCodPry");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPry[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PROYECTOS DEFINIDOS</ERROR></DATA>';
         return false;
      }
      $this->paCodPry[] = array('*', '*** TODOS ***');
      // Tipo de incidente
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE CCODTAB = '043'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipInc[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE INCIDENTES DEFINIDOS</ERROR></DATA>';
         return false;
      }
      $this->paTipInc[] = array('*', '*** TODOS ***');
      // Lugar
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE CCODTAB = '008' AND cCodigo != 'X'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paLugar[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY LUGARES DEFINIDOS</ERROR></DATA>';
         return false;
      }
      $this->paLugar[] = array('*', '*** TODOS ***');
      // Heridos
      $this->paHeridos[] = array('S', 'HERIDOS');
      $this->paHeridos[] = array('N', 'SIN HERIDOS');
      $this->paHeridos[] = array('*', '*** TODOS ***');
      // Area
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE CCODTAB = '030'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paArea[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY AREAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      $this->paArea[] = array('*', '*** TODOS ***');
      // Sub-division
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE CCODTAB = '020'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paSubDiv[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY SUB-DIVISIONES DEFINIDAS</ERROR></DATA>';
         return false;
      }
      $this->paSubDiv[] = array('*', '*** TODAS ***');
      return true;
   }
   
   public function omIncidentesFerroviarios() {
      if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodEmp)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE EMPRESA NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodPry)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PROYECTO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcTipInc)) {
         $this->pcError = '<DATA><ERROR>TIPO DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcArea)) {
         $this->pcError = '<DATA><ERROR>AREA NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcSubDiv)) {
         $this->pcError = '<DATA><ERROR>SUB-DIVISION NO DEFINIDA</ERROR></DATA>';
         return false;
      } elseif (empty($this->paCodUni) or fxCount($this->paCodUni) == 0) {
         $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD(ES) NO DEFINIDO(S)</ERROR></DATA>';
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
      } elseif ($this->pcKilome > $this->pcKilomf) {
         $this->pcError = '<DATA><ERROR>KILOMETRAJE INICIAL ES MAYOR QUE KILOMETRAJE FINAL</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodFcr)) {
         $this->pcError = '<DATA><ERROR>CAUSA NO DEFINIDA</ERROR></DATA>';
         return false;
      } elseif (!preg_match("/^\d+\.\d\d$/", $this->pnCosto)) {
         $this->pcError = '<DATA><ERROR>ERROR EN COSTO (FORMATO 9999.99)</ERROR></DATA>';
         return false;
      }
	  ///([0-9]{4})\/([0-9]{2})\/([0-9]{2})/i","$3/$2/$1
      $llOk = $this->mxIncidentesFerroviarios();
      if (!$llOk)
         return false;
      $llOk = $this->mxCargarXls();
      return $llOk;
   }
   
   public function omCharlas() {
      $llOk = $this->mxCharlas();
      if (!$llOk) {
         return false;
      }
      $llOk = $this->mxCargarXls();
      return $llOk;
   }
   
   public function omCharlasTipo() {
      $llOk = $this->mxCharlasTipo();
      if (!$llOk) {
         return false;
      }
      $llOk = $this->mxCargarXls();
      return $llOk;
   }
   
   public function omCharlasTipo2() {
      $llOk = $this->mxCharlasTipo2();
      if (!$llOk) {
         return false;
      }
      $llOk = $this->mxCargarXls();
      return $llOk;
   }
 
   public function omIncidentesLicencias() {
      $llOk = $this->mxIncidentesLicencias();
      if (!$llOk) {
         return false;
      }
      $llOk = $this->mxCargarXls();
      return $llOk;
   }
      
   protected function mxIncidentesFerroviarios() {
      $this->lcFilXls = 'S01MIFR';
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Unidades
      $llTodos = false;
      foreach ($this->paCodUni as $lcCodUni) {
         if ($lcCodUni == '*') {
            $llTodos = true;
            break;
         }
      }
      if (!$llTodos) {
         $llFirst = true;
         $lcSql = '';
         foreach ($this->paCodUni as $lcCodUni) {
            if ($llFirst) {
               $lcSql = "cCodUni IN ('$lcCodUni'";
               $llFirst = false;
            } else {
               $lcSql .= ", '$lcCodUni'";
            }
         }
         $i = 0;
         $lcSql = "SELECT A.cNumero FROM S02DIFR A INNER JOIN S02MIFR B ON A.cNumero = B.cNumero
                   WHERE B.Dfecha BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND 
                   TO_DATE('$this->pdFecFin', 'yyyy-mm-dd') AND $lcSql)";
         $RS = $loSql->omExec($lcSql);
         $llFirst = true;
         $lcNroSql = '';
         while ($laFila = $loSql->fetch($RS)) {
            $i++;
            if ($llFirst) {
               $lcNroSql = "cNumero IN ('$laFila[0]'";
               $llFirst = false;
            } else {
               $lcNroSql .= ", '$laFila[0]'";
            }
         }
         if ($i == 0) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
            return false;
         }
      }
      // Trae datos de incidentes
      $lcSql = "SELECT CNUMERO, DFECHA, CHORA, CDESINC, CTREN, CDESEMP, CDESPRY, CDESDIV, CDESLUG,
                CNOMMAQ, CNOMJEF, CNOMBRQ, CCODFCR, CDESARE, NCOSTO, CDEMORA, MEQUINV, MDESCRI, CHERIDO,
                MDETHER, MMEDADO, CDESEST, CKILOME FROM V_S02MIFR_1 ";
      if ($this->pcCodPer != '*') {
         $llFlag = true;
         $lcSql .= "WHERE (cCodMaq = '$this->pcCodPer' OR cCodJef = '$this->pcCodPer' OR cCodBrq = '$this->pcCodPer') ";
      }
      if ($this->pcCodEmp != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cCodEmp = TRIM('$this->pcCodEmp') ";
      } elseif ($this->pcCodEmp != '*') {
         $lcSql .= "AND cCodEmp = TRIM('$this->pcCodEmp') ";
      }
      if ($this->pcCodPry != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cCodPry = TRIM('$this->pcCodPry') ";
      } elseif ($this->pcCodPry != '*') {
         $lcSql .= "AND cCodPry = TRIM('$this->pcCodPry') ";
      }
      if ($this->pcTipInc != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cIncFer = '$this->pcTipInc' ";
      } elseif ($this->pcTipInc != '*') {
         $lcSql .= "AND cIncFer = '$this->pcTipInc' ";
      }
      if ($this->pcArea != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cArea = '$this->pcArea' ";
      } elseif ($this->pcTipInc != '*') {
         $lcSql .= "AND cArea = '$this->pcArea' ";
      }
      if ($this->pcSubDiv != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cSubDiv = '$this->pcSubDiv' ";
      } elseif ($this->pcSubDiv != '*') {
         $lcSql .= "AND cSubDiv = '$this->pcSubDiv' ";
      }
      if ($this->pcCodFcr != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cCodFcr = TRIM('$this->pcCodFcr') ";
      } elseif ($this->pcCodFcr != '*' and $llFlag) {
         $lcSql .= "AND cCodFcr = TRIM('$this->pcCodFcr') ";
      }
      if (!$llTodos and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE $lcNroSql) ";
      } elseif (!$llTodos and $llFlag) {
         $lcSql .= "AND $lcNroSql) ";
      }
      if (!$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE ";
      } else {
         $lcSql .= "AND ";
      }
      $lcSql .= "nCosto >= $this->pnCosto AND dFecha BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND 
                 TO_DATE('$this->pdFecFin', 'yyyy-mm-dd') AND
                 TO_NUMBER(TRIM(cKilome), '999999.999') >= $this->pcKilome AND 
                 TO_NUMBER(TRIM(cKilome), '999999.999') <= $this->pcKilomf";
      $RS = $loSql->omExec($lcSql);
      $loXls = new CXls();
      $loXls->openXls($this->lcFilXls, $this->pcCodUsu);
      $lnRow = 4;
      while ($laFila = $loSql->fetch($RS)) {
         if ($this->pcLugar != '*') {
            if ($laFila['CLUGAR'] != $this->pcLugar)
               continue;
         }
         if ($this->pcHeridos != '*') {
            if ($laFila['CHERIDO'] != $this->pcHeridos)
               continue;
         }
         // Busca descripcion de unidad
         $llFirst = true;
         $lcSql = "SELECT cCodUni FROM S02DIFR WHERE cNumero = '$laFila[0]' ORDER BY cCodUni";
         $lcCodUni = '';
         $RS1 = $loSql->omExec($lcSql);
         while ($laTmp = $loSql->fetch($RS1)) {
            if ($llFirst) {
               $lcCodUni .= $laTmp[0];
            } else {
               $lcCodUni .= '/'.$laTmp[0];
            }
         }
         $loXls->sendXls(0, 'B', $lnRow, $laFila[0]);
         $loXls->sendXls(0, 'C', $lnRow, $laFila[1]);
         $loXls->sendXls(0, 'D', $lnRow, $laFila[2]);
         $loXls->sendXls(0, 'E', $lnRow, $laFila[3]);
         $loXls->sendXls(0, 'F', $lnRow, $laFila[4]);
         $loXls->sendXls(0, 'G', $lnRow, $lcCodUni);
         $loXls->sendXls(0, 'H', $lnRow, $laFila[5]);
         $loXls->sendXls(0, 'I', $lnRow, $laFila[6]);
         $loXls->sendXls(0, 'J', $lnRow, $laFila[7]);
         $loXls->sendXls(0, 'K', $lnRow, $laFila[21]);
         $loXls->sendXls(0, 'L', $lnRow, $laFila[22]);
         $loXls->sendXls(0, 'M', $lnRow, $laFila[8]);
         $loXls->sendXls(0, 'N', $lnRow, $laFila[9]);
         $loXls->sendXls(0, 'O', $lnRow, $laFila[10]);
         $loXls->sendXls(0, 'P', $lnRow, $laFila[11]);
         $lcValor = (string)$laFila[17]->load();
         $loXls->sendXls(0, 'Q', $lnRow, "$lcValor");
         $lcValor = ($laFila[18]=='N')? 'NO':'SI';
         $loXls->sendXls(0, 'R', $lnRow, "$lcValor");
         $lcValor = (string)$laFila[19]->load();
         $loXls->sendXls(0, 'S', $lnRow, "$lcValor");
         $loXls->sendXls(0, 'T', $lnRow, $laFila[12]);
         $loXls->sendXls(0, 'U', $lnRow, $laFila[13]);
         $lcValor = (string)$laFila[20]->load();
         $loXls->sendXls(0, 'V', $lnRow, "$lcValor");
         $loXls->sendXls(0, 'W', $lnRow, $laFila[14]);
         $loXls->sendXls(0, 'X', $lnRow, $laFila[15]);
         $lnRow++;
      } 
      $loSql->omDisconnect();
      $loXls->closeXls();
      if ($lnRow == 4) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   protected function mxCharlas(){
      $this->lcFilXls = 'S01CHAR';
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT A.cCodPer, A.cCodCur, A.nNota, B.cNombre, C.cTipo, F.cDescri AS cDesTip, C.cDescri AS cNomCur, C.cNomDoc,
                TO_CHAR(C.dFecha,'yyyy-mm-dd') AS DFECHA, C.cTiempo, C.mdatos, E.cCodNor, E.cDescri FROM S02DCAP A 
                INNER JOIN S03MPER B ON A.cCodPer = B.cCodPer
                INNER JOIN S02MCUR C ON A.cCodCur = C.cCodCur
                INNER JOIN S02PCUR D ON C.cCodCur = D.cCodCur
                INNER JOIN S02MNOR E ON D.cCodNor = E.cCodNor
                INNER JOIN V_S02TTAB F ON C.cTipo = F.cCodigo AND F.cCodTab = '055'
                where A.ccodper = '$this->pcCodPer' AND 
                dFecha BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd') 
                order by ccodcur, ccodnor";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $loXls = new CXls();
      $loXls->openXls($this->lcFilXls, $this->gcCodUsu);
      $lnRow = 7;
      while ($laFila = $loSql->fetch($RS)) {
         $loXls->sendXls(0, 'B', $lnRow, $laFila['CCODPER']);
         $loXls->sendXls(0, 'C', $lnRow, $laFila['CNOMBRE']);
         $loXls->sendXls(0, 'D', $lnRow, $laFila['CDESTIP']);
         $loXls->sendXls(0, 'E', $lnRow, $laFila['CNOMCUR']);
         $loXls->sendXls(0, 'F', $lnRow, $laFila['CNOMDOC']);
         $loXls->sendXls(0, 'G', $lnRow, $laFila['DFECHA']);
         $loXls->sendXls(0, 'H', $lnRow, $laFila['CTIEMPO']);
         $loXls->sendXls(0, 'I', $lnRow, $laFila['CCODNOR']);
         $loXls->sendXls(0, 'J', $lnRow, $laFila['CDESCRI']);
         $loXls->sendXls(0, 'K', $lnRow, $laFila['NNOTA']);
         $lnRow++;
      } 
      $loXls->closeXls();
      return true;
   }
   
   protected function mxCharlasTipo(){
      $this->lcFilXls = 'S01CHART';
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT A.cCodPer, A.cCodCur, A.nNota, B.cNombre, C.cTipo, F.cDescri AS cDesTip, C.cDescri AS cNomCur, C.cNomDoc,
                TO_CHAR(C.dFecha,'yyyy-mm-dd') AS DFECHA, C.cTiempo, C.mdatos, E.cCodNor, E.cDescri FROM S02DCAP A 
                INNER JOIN S03MPER B ON A.cCodPer = B.cCodPer
                INNER JOIN S02MCUR C ON A.cCodCur = C.cCodCur
                INNER JOIN S02PCUR D ON C.cCodCur = D.cCodCur
                INNER JOIN S02MNOR E ON D.cCodNor = E.cCodNor
                INNER JOIN V_S02TTAB F ON C.cTipo = F.cCodigo AND F.cCodTab = '055'
                where F.cCodigo = '$this->pcTipo' AND F.cCodTab='055' AND 
                dFecha BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd') 
                order by ccodcur, ccodnor";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $loXls = new CXls();
      $loXls->openXls($this->lcFilXls, $this->gcCodUsu);
      $lnRow = 7;
      while ($laFila = $loSql->fetch($RS)) {
         $loXls->sendXls(0, 'B', $lnRow, $laFila['CCODPER']);
         $loXls->sendXls(0, 'C', $lnRow, $laFila['CNOMBRE']);
         $loXls->sendXls(0, 'D', $lnRow, $laFila['CDESTIP']);
         $loXls->sendXls(0, 'E', $lnRow, $laFila['CNOMCUR']);
         $loXls->sendXls(0, 'F', $lnRow, $laFila['CNOMDOC']);
         $loXls->sendXls(0, 'G', $lnRow, $laFila['DFECHA']);
         $loXls->sendXls(0, 'H', $lnRow, $laFila['CTIEMPO']);
         $loXls->sendXls(0, 'I', $lnRow, $laFila['CCODNOR']);
         $loXls->sendXls(0, 'J', $lnRow, $laFila['CDESCRI']);
         $loXls->sendXls(0, 'K', $lnRow, $laFila['NNOTA']);
         $lnRow++;
      } 
      $loXls->closeXls();
      return true;
   }
   
   protected function mxCharlasTipo2(){
      $this->lcFilXls = 'S01CHART2';
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT A.cCodPer, A.cCodCur, A.nNota, B.cNombre, C.cTipo, F.cDescri AS cDesTip, C.cDescri AS cNomCur, C.cNomDoc,
                TO_CHAR(C.dFecha,'yyyy-mm-dd') AS DFECHA, C.cTiempo, C.mdatos, E.cCodNor, E.cDescri FROM S02DCAP A 
                INNER JOIN S03MPER B ON A.cCodPer = B.cCodPer
                INNER JOIN S02MCUR C ON A.cCodCur = C.cCodCur
                INNER JOIN S02PCUR D ON C.cCodCur = D.cCodCur
                INNER JOIN S02MNOR E ON D.cCodNor = E.cCodNor
                INNER JOIN V_S02TTAB F ON C.cTipo = F.cCodigo AND F.cCodTab = '055'
                where F.cCodigo = '$this->pcTipo' AND F.cCodTab='055' AND A.ccodper = '$this->pcCodPer' AND 
                dFecha BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd') 
                order by ccodcur, ccodnor";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $loXls = new CXls();
      $loXls->openXls($this->lcFilXls, $this->gcCodUsu);
      $lnRow = 7;
      while ($laFila = $loSql->fetch($RS)) {
         $loXls->sendXls(0, 'B', $lnRow, $laFila['CCODPER']);
         $loXls->sendXls(0, 'C', $lnRow, $laFila['CNOMBRE']);
         $loXls->sendXls(0, 'D', $lnRow, $laFila['CDESTIP']);
         $loXls->sendXls(0, 'E', $lnRow, $laFila['CNOMCUR']);
         $loXls->sendXls(0, 'F', $lnRow, $laFila['CNOMDOC']);
         $loXls->sendXls(0, 'G', $lnRow, $laFila['DFECHA']);
         $loXls->sendXls(0, 'H', $lnRow, $laFila['CTIEMPO']);
         $loXls->sendXls(0, 'I', $lnRow, $laFila['CCODNOR']);
         $loXls->sendXls(0, 'J', $lnRow, $laFila['CDESCRI']);
         $loXls->sendXls(0, 'K', $lnRow, $laFila['NNOTA']);
         $lnRow++;
      } 
      $loXls->closeXls();
      return true;
   }

   protected function mxIncidentesLicencias() {
      $this->lcFilXls = 'S02DLIC';
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }  
      $lcSql = "SELECT CNOMBRE, CNROLIC, CTIPLIC, DFECVIG, DFECFIN, CESTADO, CAUTORI, CRESTRI FROM V_s02dlic ORDER BY CNOMBRE";                 
      $RS = $loSql->omExec($lcSql);      
      $loSql->omDisconnect();
      $loXls = new CXls();      
      $loXls->openXls($this->lcFilXls, $this->gcCodUsu);
      $lnRow = 4;      
      while ($laFila = $loSql->fetch($RS)) {
         $loXls->sendXls(0, 'B', $lnRow, "$laFila[0]");
         $loXls->sendXls(0, 'C', $lnRow, "$laFila[1]");
         $loXls->sendXls(0, 'D', $lnRow, "$laFila[2]");
         $loXls->sendXls(0, 'E', $lnRow, "$laFila[3]");
         $loXls->sendXls(0, 'F', $lnRow, "$laFila[4]");
         $loXls->sendXls(0, 'G', $lnRow, "$laFila[5]");
         $loXls->sendXls(0, 'H', $lnRow, "$laFila[6]");
         $loXls->sendXls(0, 'I', $lnRow, "$laFila[7]");             
         $lnRow++;
      }       
      $loXls->closeXls();
      return true;
   }
   
   protected function mxCargarXls() {
      $lcFolder = './Ficheros/';
      if (!is_dir($lcFolder)) {
         $this->pcError = '<DATA><ERROR>FOLDER DE ARCHIVOS XLS NO ESTA DEFINIDO</ERROR></DATA>';
         return false;
      }  
      $this->paData = null;      
      $lcDir = opendir($lcFolder);      
      while ($lcFile = readdir($lcDir)) {
         if ($lcFile == $this->lcFilXls.'_'.$this->pcCodUsu.'.xlsx'){
            $this->paData[] = array($lcFolder.$lcFile, $lcFile);   
         }
      } 
     return true;
   } 
   
   public function InitExcelNuevo() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Incidente
      $lcSql = "select cCodigo, cDescri from s02ttab where ccodtab='043' and cTipReg = '1' order by cDescri";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paIncFer[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      // Carga Empresa
      $lcSql = "select cCodEmp, cDescri from s02memp where cEstado='A' order by nOrden, cCodEmp";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodEmp[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY EMPRESAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      // Carga Jefe
      $lcSql = "select cCodPer, cNombre from s02mper where cEstado='A' order by cCodPer";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodJef[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY JEFES DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Carga Proyectos
      $lcSql = "select cCodPry, cDescri from S02Mpry where cEstado='A' order by cCodPry";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPry[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PROYECTOS DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Carga Subdivision
      $lcSql = "select cCodigo, cDescri from s02ttab where ccodtab = '020' and cTipReg = '1' order by cDescri";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paSubDiv[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY LUGARES DEFINIDOS</ERROR></DATA>';
         return false;
      } 
      // Carga Lugar
      $lcSql = "select cCodigo, cDescri from s02ttab where ccodtab = '037' and cTipReg = '1' order by cDescri";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paLugar[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY LUGARES DEFINIDOS</ERROR></DATA>';
         return false;
      } 
       // Carga Area
      $lcSql = "select cCodigo, cdescri from s02ttab where ccodtab = '030' and cTipReg = '1' order by cDescri";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paArea[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY AREAS DEFINIDAS</ERROR></DATA>';
         return false;
      } 
      // Carga Herido
      $lcSql = "select cCodigo, cdescri from s02ttab where ccodtab = '005' and cTipReg = '1' order by cDescri";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paHerido[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY AREAS DEFINIDAS</ERROR></DATA>';
         return false;
      } 
       // Carga Maquinario
      $lcSql = "select cCodPer, cNombre from s02mper where cEstado='A' order by cCodPer";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodMaq[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY JEFES DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Carga Brequero
      $lcSql = "select cCodPer, cNombre from s02mper where cEstado='A' order by cCodPer";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodBrq[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY BREQUERO DEFINIDOS</ERROR></DATA>';
         return false;
      }
      $loSql->omDisconnect();
      return true;
   }
   
   public function omPruebaRadar() {
      if (empty($this->pcNroEva)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Evaluacion
      $RS = $loSql->omExec("SELECT TO_CHAR(dFecEva, 'yyyy-mm-dd') as dFecEva, cHorEva, cKilome, cTren, cCompos, cDesDiv, cDesEst, cDesVag, cNomSp1, cCodUn1, cDesUn1,cCodUn2, cDesUn2, cCodUn3, cDesUn3, cKilomf, cDesEsf, cHorSer, cVelAut, cVelReg, cRestri, cNomSp2, cCodEmp, cCodPry, cDesCli, cDesRes, mObserv FROM V_S02DEVA_2 WHERE CNROEVA = '$this->pcNroEva'");
      $this->paFila = $loSql->fetch($RS);
      if (empty($this->paFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO EXISTE</ERROR></DATA>';
         return false;
      }
      // Carga normas incumplidas o items evaluados     
      $RS = $loSql->omExec("SELECT CNOMBRE,CPUESTO FROM V_S02DEVA_1 WHERE CNROEVA ='$this->pcNroEva'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[] = array($laFila[0], $laFila[1]);
      }
     return true;
   }
   
   public function omPruebaEficienciaDeTerreno() {
      if (empty($this->pcNroEva)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcNomMaq = $lcCnomjef = $lcNombrq  = null;
      // Carga Evaluacion
      $RS = $loSql->omExec("SELECT cNomSp1, cNroEva, TO_CHAR(dFecEva, 'yyyy-mm-dd') as dFecEva, cHorEva, cDesDiv, cKilome, cDesEst, cTren, cDesVag, cCodUn1, cDesUn1, cCodUn2, cDesUn2, cCodUn3, cDesUn3, mObserv, cCompos FROM V_S02DEVA_2 WHERE CNROEVA = '$this->pcNroEva'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO EXISTE</ERROR></DATA>';
         return false;
      }
      // Carga normas incumplidas o items evaluados     
      $RS = $loSql->omExec("SELECT CNOMBRE,CPUESTO FROM V_S02DEVA_1 WHERE CNROEVA ='$this->pcNroEva'");
      while ($i = $loSql->fetch($RS)) {
         if ($i[1] == 'M')
            $lcNomMaq = $i[0];
         if ($i[1] == 'J')
            $lcCnomjef = $i[0];
         if ($i[1] == 'B')
            $lcNombrq = $i[0];
      }
      // Carga normas incumplidas o items evaluados     
      $RS = $loSql->omExec("SELECT cDescri, cDesRes FROM V_S02DEVA_3 WHERE CNROEVA ='$this->pcNroEva' ORDER BY CCODPER");
      while ($i = $loSql->fetch($RS)) {
         $laNorma[] = array($i[0], $i[1]);
      }
      $lcLocomo = '';
      if ($laFila['CCODUN1'] != '99999')
         $lcLocomo = $lcLocomo.' '.$laFila['CDESUN1'];
      if ($laFila['CCODUN2'] != '99999')
         $lcLocomo = $lcLocomo.' '.$laFila['CDESUN2'];
      if ($laFila['CCODUN3'] != '99999')
         $lcLocomo = $lcLocomo.' '.$laFila['CDESUN3'];
      $laArray = array(
         '#*CSUPERV*#'=>$laFila['CNOMSP1'],
         '#*CNROEVA*#'=>$laFila['CNROEVA'],
         '#*DFECHA*#'=>$laFila['DFECEVA'],
         '#*CHORA*#'=>$laFila['CHOREVA'],
         '#*CSUBDIV*#'=>$laFila['CDESDIV'],
         '#*CKILOME*#'=>$laFila['CKILOME'],
         '#*CESTACI*#'=>$laFila['CDESEST'],
         '#*CTREN*#'=>$laFila['CTREN'],
         '#*CTIPO*#'=>$laFila['CDESVAG'],
         '#*CLOCOMO*#'=>$lcLocomo,
         '#*CNOMMAQ*#'=>$lcNomMaq,
         '#*CNOMJEF*#'=>$lcCnomjef,
         '#*CNOMBRQ*#'=>$lcNombrq,
         '#*COBSERV*#'=>$laFila['MOBSERV']->load(),
         '#*CCOMPOS*#'=>$laFila['CCOMPOS'],
         '#*CNORM01*#'=>$laNorma[0][0],
         '#*CNORM02*#'=>$laNorma[1][0],
         '#*CNORM03*#'=>$laNorma[2][0],
         '#*CNORM04*#'=>$laNorma[3][0],
         '#*CNORM05*#'=>$laNorma[4][0],
         '#*CNORM06*#'=>$laNorma[5][0],
         '#*CNORM07*#'=>$laNorma[6][0],
         '#*CNORM08*#'=>$laNorma[7][0],
         '#*CNORM09*#'=>$laNorma[8][0],
         '#*CNORM010*#'=>$laNorma[9][0],
         '#*CNORM011*#'=>$laNorma[10][0],
         '#*CNORM012*#'=>$laNorma[11][0],
         '#*CNORM013*#'=>$laNorma[12][0],
         '#*CNORM014*#'=>$laNorma[13][0],
         '#*CSINO01*#'=>$laNorma[0][1],
         '#*CSINO02*#'=>$laNorma[1][1],
         '#*CSINO03*#'=>$laNorma[2][1],
         '#*CSINO04*#'=>$laNorma[3][1],
         '#*CSINO05*#'=>$laNorma[4][1],
         '#*CSINO06*#'=>$laNorma[5][1],
         '#*CSINO07*#'=>$laNorma[6][1],
         '#*CSINO08*#'=>$laNorma[7][1],
         '#*CSINO09*#'=>$laNorma[8][1],
         '#*CSINO010*#'=>$laNorma[9][1],
         '#*CSINO011*#'=>$laNorma[10][1],
         '#*CSINO012*#'=>$laNorma[11][1],
         '#*CSINO013*#'=>$laNorma[12][1],
         '#*CSINO014*#'=>$laNorma[13][1]
         );
      $lo = new CRtf();
      $lo->pcFile = 'S02MEVA1';
      $lo->paArray = $laArray;
      $lo->omProcesar();
      return true;
   }
   
   public function InitExcelEditar() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
        // Carga Ferrovia
      $lcSql = "SELECT * FROM S02MIFR ";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->paNumero = $laFila[0];
      $this->paFecha = $laFila[1];
      $this->paHora = $laFila[2];
      $this->paIncFer = $laFila[3];
      $this->paTren = $laFila[4];
      $this->paCodEmp= $laFila[5];
      $this->paCodPry= $laFila[6];
      $this->paSubDiv= $laFila[7];
      $this->paKilome= $laFila[8];
      $this->paLugar= $laFila[9];
      $this->paCodMaq= $laFila[10];
      $this->paCodJef= $laFila[11];
      $this->paCodBrq= $laFila[12];
      $this->paHerido= $laFila[13];
      $this->paCodFcr = $laFila[14];
      $this->paArea= $laFila[15];
      $this->paCosto = $laFila[16];
      $this->paDemora = $laFila[17];
      $this->paEquinv = $laFila[18];
      $this->paDescri = $laFila[19];
      $this->paDether = $laFila[20];
      $this->paMedado = $laFila[21];
      // Carga Incidente
      $lcSql = "select cCodigo from s02ttab where ccodtab='043' and cTipReg = '1' and cCodigo = '$this->paIncFer'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->paIncFer = $laFila[0];
      // Carga Empresa
      $lcSql = "select cCodEmp from s02memp where cEstado = 'A' and cCodEmp = '$this->paCodEmp'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->paCodEmp = $laFila[0];
      // Carga Jefe
      $lcSql = "select cCodPer from s02mper where cEstado = 'A' and cCodPer = '$this->paCodJef'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->paCodJef = $laFila[0];
      // Carga Brequero
      $lcSql = "select cCodPer from s02mper where cEstado = 'A' and cCodPer = '$this->paCodBrq'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->paCodBrq = $laFila[0];
      // Carga Proyecto
      $lcSql = "select cCodPry from s02mpry where cEstado = 'A' and cCodPry = '$this->paCodPry'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->paCodPry = $laFila[0];
      // Carga SubdivisiÃ³n
      $lcSql = "select cCodigo from s02ttab where ccodtab = '020' and cTipReg = '1' and cCodigo = '$this->paSubDiv'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->paSubDiv = $laFila[0];
      // Carga Lugar      
      $lcSql = "select cCodigo from s02ttab where ccodtab='037' and cTipReg = '1' and cCodigo = '$this->paLugar'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->paLugar = $laFila[0];
      // Carga Maquinario 
      $lcSql = "select cCodPer from s02mper where cEstado = 'A' and cCodPer = '$this->paCodMaq'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->paCodMaq = $laFila[0];
      // Carga Area 
      $lcSql = "select cCodigo from s02ttab where ccodtab='030' and cTipReg = '1' and cCodigo = '$this->paArea'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->paArea = $laFila[0];
      // Carga Clima 
      $lcSql = "select cCodigo from s02ttab where ccodtab='005' and cTipReg = '1' and cCodigo = '$this->paHerido'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->paHerido = $laFila[0];
   }
   
   public function BuscarExcel() {
      $lo = new CMtoConsultas();
      $lo->pcCodUsu=$this->pcCodUsu;
      $lo->omIncidentesFerroviarios();
      $lcFolder = './Ficheros/';
      if (!is_dir($lcFolder)) {
         return true;
      }  
      $this->paData = null;
      $lcDir = opendir($lcFolder);
      while ($lcFile = readdir($lcDir)) {
         if ($lcFile == 'S01MIFR_'.$this->pcCodUsu.'.xlsx'){
            $this->paData[] = array($lcFolder.$lcFile, $lcFile);   
         }
      } 
   } 
   
   // Init para cubo de cursos
   public function omInitCursos() {
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }  
      // Trae personal
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE SUBSTR(cCodPer, 1, 1) != '9' ORDER BY cNombre");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL ACTIVO</ERROR></DATA>';
         return false;
      }
      $this->paCodPer[] = array('*', '*** TODOS ***');
      // Carga normas
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR WHERE cTipo = '1' ORDER BY cCodNor");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodNor[] = array($laFila[0], trim($laFila[0]).'-'.$laFila[1]);
      }
      $this->paCodNor[] = array('*', '*** TODAS ***');
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>MAESTRO DE NORMAS (S02MNOR) NO TIENE DATOS</ERROR></DATA>';
         return false;
      }
      // Cursos
      $RS = $loSql->omExec("SELECT cCodCur, cDescri FROM S02MCUR ORDER BY cCodCur");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodCur[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY CURSOS DEFINIDOS</ERROR></DATA>';
         return false;
      }
      $this->paCodCur[] = array('*', '* TODOS');
      // Tipo de curso
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE CCODTAB = '055'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipCur[] = array($laFila[0], $laFila[1]);
      }
      $this->paTipCur[] = array('*', '* TODOS');
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE CURSO DEFINIDOS. SUB-TABLA [055]</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   // Genera excel para cursos
   public function omGenerarCursos() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodCur)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE CURSO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodNor)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE NORMA NO DEFINIDO</ERROR></DATA>';
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
      } elseif (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcTipCur)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE TIPO DE CURSO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $llOk = $this->mxGenerarCursos();
      if (!$llOk)
         return false;
      $llOk = $this->mxCargarXls();
      return $llOk;
   }

   protected function mxGenerarCursos() {
      $this->lcFilXls = 'S02DCAP';
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $llFirst = true;
      $lcSql = "SELECT * FROM V_S02DCAP_2 ";
      // Personal
      if ($this->pcCodPer != '*') {
         $lcSql.="WHERE cCodPer = '$this->pcCodPer'";
         $llFirst = false;
      }
      // Cursos
      if ($this->pcCodCur != '*') {
         if ($llFirst) {
            $lcSql.="WHERE cCodCur = '$this->pcCodCur' ";
         } else {
            $lcSql.="AND cCodCur = '$this->pcCodCur' ";
         }
         $llFirst = false;
      }
      // Tipo de Curso
      if ($this->pcTipCur != '*') {
         if ($llFirst) {
            $lcSql.="WHERE cTipo = '$this->pcTipCur' ";
         } else {
            $lcSql.="AND cTipo = '$this->pcTipCur' ";
         }
         $llFirst = false;
      }
      // Normas
      if ($this->pcCodNor != '*') {
         if ($llFirst) {
            $lcSql.="WHERE cCodNor = '$this->pcCodNor' ";
         } else {
            $lcSql.="AND cCodNor = '$this->pcCodNor' ";
         }
         $llFirst = false;
      }
      // Fechas
      $lcSql.=($llFirst) ? 'WHERE ' : 'AND ';
      $lcSql.="dFecha >= TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND dFecha <= TO_DATE('$this->pdFecFin', 'yyyy-mm-dd') ORDER BY cNombre";
      $RS = $loSql->omExec($lcSql);
      $loXls = new CXls();
      $loXls->openXls($this->lcFilXls, $this->pcCodUsu);
      $lnRow = 4;
      while ($laFila = $loSql->fetch($RS)) {
         $lcDesNor = $laFila['CDESNOR'];
         if (substr($laFila['CCODNOR'], 0, 1) == 'Z') {
            $lcDesNor = $laFila['MDATOS']->load();
         }
         $loXls->sendXls(0, 'B', $lnRow, (string)$laFila['CCODPER']);
         $loXls->sendXls(0, 'C', $lnRow, $laFila['CNOMBRE']);
         $loXls->sendXls(0, 'D', $lnRow, (string)$laFila['CCODCUR']);
         $loXls->sendXls(0, 'E', $lnRow, $laFila['CDESCRI']);
         $loXls->sendXls(0, 'F', $lnRow, $laFila['DFECHA']);
         $loXls->sendXls(0, 'G', $lnRow, $laFila['CTIEMPO']);
         $loXls->sendXls(0, 'H', $lnRow, (string)$laFila['CTIPO']);
         $loXls->sendXls(0, 'I', $lnRow, $laFila['CDESTIP']);
         $loXls->sendXls(0, 'J', $lnRow, $laFila['NNOTA']);
         $loXls->sendXls(0, 'K', $lnRow, $laFila['CCODNOR']);
         $loXls->sendXls(0, 'L', $lnRow, $lcDesNor);
         $lnRow++;
      } 
      $loSql->omDisconnect();
      $loXls->closeXls();
      if ($lnRow == 4) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   public function omInitEvaluaciones() {
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }  
      // Trae personal
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE SUBSTR(cCodPer, 1, 1) != '9' ORDER BY cNombre");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL ACTIVO</ERROR></DATA>';
         return false;
      }
      $this->paCodPer[] = array('*', '*** TODOS ***');
      // Carga supervisores
      $RS = $loSql->omExec("SELECT cSuperv, cNombre FROM V_SUPERVISORES WHERE SUBSTR(cSuperv, 1, 1) != '9' ORDER BY cNombre");
      while ($laFila = $loSql->fetch($RS)) {
         $this->paSuperv[] = array($laFila[0], $laFila[1]);
      }
      $this->paSuperv[] = array('*', '*** TODOS ***');
      // Carga normas
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR WHERE cTipo = '1' ORDER BY cCodNor");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodNor[] = array($laFila[0], trim($laFila[0]).'-'.$laFila[1]);
      }
      $this->paCodNor[] = array('*', '*** TODAS ***');
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>MAESTRO DE NORMAS (S02MNOR) NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Unidades
      $RS = $loSql->omExec("SELECT cCodUni, cDescri FROM S02MUNI WHERE cCodUni != '99999' ORDER BY cCodUni");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodUni[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY UNIDADES ACTIVAS</ERROR></DATA>';
         return false;
      }
      $this->paCodUni[] = array('*', '* TODOS');
      // Empresas
      $RS = $loSql->omExec("SELECT cCodEmp, cDescri FROM S02MEMP WHERE cCodEmp != '999' ORDER BY nOrden, cCodEmp");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodEmp[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY EMPRESAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      $this->paCodEmp[] = array('*', '*** TODAS ***');
      // Carga Estaciones
      $RS = $loSql->omExec("SELECT TRIM(cCodigo), cDescri FROM V_S02TTAB WHERE cCodTab = '021'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstaci[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ESTACIONES DEFINIDAS. TABLA [021]</ERROR></DATA>';
         return false;
      }
      // Proyectos
      $RS = $loSql->omExec("SELECT cCodPry, cDescri FROM S02MPRY ORDER BY cCodPry");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPry[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PROYECTOS DEFINIDOS</ERROR></DATA>';
         return false;
      }
      $this->paCodPry[] = array('*', '*** TODOS ***');
      // Tipo de evaluacion
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE CCODTAB = '044'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipEva[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE EVALUACION DEFINIDOS. SUBTABLA [044]</ERROR></DATA>';
         return false;
      }
      $this->paTipEva[] = array('*', '*** TODOS ***');
      // Tipo de vagon
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE CCODTAB = '002'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipVag[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TIPO DE VAGON DEFINIDOS. SUBTABLA [002]</ERROR></DATA>';
         return false;
      }
      $this->paTipVag[] = array('*', '*** TODOS ***');
      // Heridos
      $this->paHeridos[] = array('S', 'HERIDOS');
      $this->paHeridos[] = array('N', 'SIN HERIDOS');
      $this->paHeridos[] = array('*', '*** TODOS ***');
/*      // Area
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE CCODTAB = '030'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paArea[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY AREAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      $this->paArea[] = array('*', '*** TODOS ***');*/
      // Sub-division
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE CCODTAB = '020'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paSubDiv[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY SUB-DIVISIONES DEFINIDAS</ERROR></DATA>';
         return false;
      }
      $this->paSubDiv[] = array('*', '*** TODAS ***');
      return true;
   }

   public function omEvaluacionesExcel() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodEmp)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE EMPRESA NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodPry)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PROYECTO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcTipEva)) {
         $this->pcError = '<DATA><ERROR>TIPO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcSubDiv)) {
         $this->pcError = '<DATA><ERROR>SUB-DIVISION NO DEFINIDA</ERROR></DATA>';
         return false;
      } elseif (empty($this->paCodUni) or fxCount($this->paCodUni) == 0) {
         $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD(ES) NO DEFINIDO(S)</ERROR></DATA>';
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
      } elseif ($this->pcKilome > $this->pcKilomf) {
         $this->pcError = '<DATA><ERROR>KILOMETRAJE INICIAL ES MAYOR QUE KILOMETRAJE FINAL</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodNor)) {
         $this->pcError = '<DATA><ERROR>NORMA NO DEFINIDA</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $llOk = $this->mxEvaluacionesExcel();
      if (!$llOk)
         return false;
      $llOk = $this->mxCargarXls();
      return $llOk;
   }

   protected function mxEvaluacionesExcel() {
      $this->lcFilXls = 'S02DEVA';
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = 'SELECT * FROM V_S02MEVA_3 ';
      // Unidades //OJO EMM
      $llTodos = false;
      foreach ($this->paCodUni as $lcCodUni) {
         if ($lcCodUni == '*') {
            $llTodos = true;
            break;
         }
      }
      $llFlag = false;
      if (!$llTodos) {
         $llFirst = true;
         $lcTmp = '';
         $lcTmp1 = '';
         $lcTmp2 = '';
         foreach ($this->paCodUni as $lcCodUni) {
            if ($llFirst) {
               $lcTmp = "WHERE cCodUn1 IN ('$lcCodUni'";
               $lcTmp2 = " AND cCodUn2 IN ('$lcCodUni'";
               $lcTmp3 = " AND cCodUn3 IN ('$lcCodUni'";
               $llFirst = false;
            } else {
               $lcTmp .= ", '$lcCodUni'";
               $lcTmp2 .= ", '$lcCodUni'";
               $lcTmp3 .= ", '$lcCodUni'";
            }
         }
         $lcSql.=$lcTmp.')';
         $lcSql.=$lcTmp2.')';
         $lcSql.=$lcTmp3.')';
         $llFlag = true;
      }
      
      /*
      
      // Unidades
      $llTodos = false;
      foreach ($this->paCodUni as $lcCodUni) {
         if ($lcCodUni == '*') {
            $llTodos = true;
            break;
         }
      }
      $llFlag = false;
      if (!$llTodos) {
         $llFirst = true;
         $lcTmp = '';
         foreach ($this->paCodUni as $lcCodUni) {
            if ($llFirst) {
               $lcTmp = "WHERE cCodUni IN ('$lcCodUni'";
               $llFirst = false;
            } else {
               $lcTmp .= ", '$lcCodUni'";
            }
         }
         $lcSql.=$lcTmp.')';
         $llFlag = true;
      }
      
      */
      
      if ($this->pcCodPer != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql.="WHERE cCodPer= '$this->pcCodPer' ";
      } elseif ($this->pcCodPer != '*'){
         $lcSql.="AND cCodPer= '$this->pcCodPer' ";
      }
      if ($this->pcCodEmp != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cCodEmp = TRIM('$this->pcCodEmp') ";
      } elseif ($this->pcCodEmp != '*') {
         $lcSql .= "AND cCodEmp = TRIM('$this->pcCodEmp') ";
      }
      if ($this->pcCodPry != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cCodPry = TRIM('$this->pcCodPry') ";
      } elseif ($this->pcCodPry != '*') {
         $lcSql .= "AND cCodPry = TRIM('$this->pcCodPry') ";
      }
      if ($this->pcTipEva != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE SUBSTR(cNroEva, 1, 1) = '$this->pcTipEva' ";
      } elseif ($this->pcTipEva != '*') {
         $lcSql .= "AND SUBSTR(cNroEva, 1, 1) = '$this->pcTipEva' ";
      }
      if ($this->pcSubDiv != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cSubDiv = '$this->pcSubDiv' ";
      } elseif ($this->pcSubDiv != '*') {
         $lcSql .= "AND cSubDiv = '$this->pcSubDiv' ";
      }
      if ($this->pcCodNor != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cCodNor = TRIM('$this->pcCodNor') ";
      } elseif ($this->pcCodNor != '*') {
         $lcSql .= "AND cCodNor = TRIM('$this->pcCodNor') ";
      }
      $lcSql.=(!$llFlag) ? 'WHERE ' : 'AND ';
      $lcSql.="dFecEva BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND 
               TO_DATE('$this->pdFecFin', 'yyyy-mm-dd') AND
               TO_NUMBER(TRIM(cKilome), '999999.999') >= $this->pcKilome AND 
               TO_NUMBER(TRIM(cKilome), '999999.999') <= $this->pcKilomf";
      $RS = $loSql->omExec($lcSql);
      $loXls = new CXls();
      $loXls->openXls($this->lcFilXls, $this->pcCodUsu);
      $lnRow = 4;
      while ($laFila = $loSql->fetch($RS)) {
         $loXls->sendXls(0, 'B', $lnRow, $laFila['CDESEVA']);
         $loXls->sendXls(0, 'C', $lnRow, $laFila['CNROEVA']);
         $loXls->sendXls(0, 'D', $lnRow, $laFila['DFECEVA']);
         $loXls->sendXls(0, 'E', $lnRow, $laFila['CHOREVA']);
         $loXls->sendXls(0, 'F', $lnRow, $laFila['CDESDIV']);
         $loXls->sendXls(0, 'G', $lnRow, $laFila['CDESEST']);
         $loXls->sendXls(0, 'H', $lnRow, $laFila['CDESESF']);
         $loXls->sendXls(0, 'I', $lnRow, $laFila['CDESVAG']);
         $loXls->sendXls(0, 'J', $lnRow, $laFila['CNOMSUP']);
         $loXls->sendXls(0, 'K', $lnRow, $laFila['CNOMSP2']);
         $loXls->sendXls(0, 'L', $lnRow, $laFila['CKILOME']);
         $loXls->sendXls(0, 'M', $lnRow, $laFila['CKILOMF']);
         $loXls->sendXls(0, 'N', $lnRow, $laFila['CHORSER']);
         $loXls->sendXls(0, 'O', $lnRow, $laFila['CDESCON']);
         $loXls->sendXls(0, 'P', $lnRow, $laFila['CDESCLI']);
         $loXls->sendXls(0, 'Q', $lnRow, $laFila['CVELAUT']);
         $loXls->sendXls(0, 'R', $lnRow, $laFila['CVELREG']);
         $loXls->sendXls(0, 'S', $lnRow, $laFila['CDESUN1']);
         $loXls->sendXls(0, 'T', $lnRow, $laFila['CDESUN2']);
         $loXls->sendXls(0, 'U', $lnRow, $laFila['CDESUN3']);
         $loXls->sendXls(0, 'V', $lnRow, $laFila['CTREN']);
         $loXls->sendXls(0, 'W', $lnRow, $laFila['CCOMPOS']);
         $loXls->sendXls(0, 'X', $lnRow, $laFila['CDESPRY']);
         $loXls->sendXls(0, 'Y', $lnRow, $laFila['CDESEMP']);
         $loXls->sendXls(0, 'Z', $lnRow, $laFila['CDESRES']);
         $loXls->sendXls(0, 'AA', $lnRow, $laFila['CNOMPER']);
         $loXls->sendXls(0, 'AB', $lnRow, $laFila['CDESNOR']);
         $loXls->sendXls(0, 'AC', $lnRow, $laFila['CDESPUE']);
         $loXls->sendXls(0, 'AD', $lnRow, $laFila['CDESRSD']);
         $lcValor = (string)$laFila['MOBSERV']->load();
         $loXls->sendXls(0, 'AE', $lnRow, "$lcValor");
         $lnRow++;
      } 
      $loSql->omDisconnect();
      $loXls->closeXls();
      if ($lnRow == 4) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   // Init licencias
   public function omInitLicencias() {
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }  
      // Trae personal
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE SUBSTR(cCodPer, 1, 1) != '9' ORDER BY cNombre");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL ACTIVO</ERROR></DATA>';
         return false;
      }
      $this->paCodPer[] = array('*', '*** TODOS ***');
      // Tipo de licencias
      $RS = $loSql->omExec("SELECT cTipLic, cDescri FROM S02MLIC ORDER BY cDescri");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipLic[] = array($laFila[0], $laFila[1]);
      }
      $this->paTipLic[] = array('*', '*** TODAS ***');
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>TIPOS DE LICENCIA NO DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Estado de licencia
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '035'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ESTADOS DE LICENCIA NO DEFINIDOS. SUB-TABLA [035]</ERROR></DATA>';
         return false;
      }
      $this->paEstado[] = array('*', '*** TODAS ***');
      // Sector autorizado
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '046'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paSecAut[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>SECTORES AUTORIZADOS NO DEFINIDOS. SUB-TABLA [046]</ERROR></DATA>';
         return false;
      }
      $this->paSecAut[] = array('*', '*** TODOS ***');
      // Restricciones
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '047'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paRestri[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>RESTRICCIONES NO DEFINIDAS. SUB-TABLA [047]</ERROR></DATA>';
         return false;
      }
      $this->paRestri[] = array('*', '*** TODAS ***');
      // Sector
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '037'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paSector[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>SECTORES NO DEFINIDOS. SUB-TABLA [037]</ERROR></DATA>';
         return false;
      }
      $this->paSector[] = array('*', '*** TODAS ***');
      // Empresas
      $RS = $loSql->omExec("SELECT cCodEmp, cDescri FROM S02MEMP WHERE cCodEmp != '999' ORDER BY nOrden, cCodEmp");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodEmp[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY EMPRESAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      $this->paCodEmp[] = array('*', '*** TODAS ***');
      return true;
   }

   public function omLicenciasExcel() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodEmp)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE EMPRESA NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcTipLic)) {
         $this->pcError = '<DATA><ERROR>TIPO DE LICENCIAS NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcEstado)) {
         $this->pcError = '<DATA><ERROR>ESTADO DE LICENCIA NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcSecAut)) {
         $this->pcError = '<DATA><ERROR>SECTOR AUTORIZADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcSector)) {
         $this->pcError = '<DATA><ERROR>SECTOR NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdVigIni)) {
         $this->pcError = '<DATA><ERROR>FECHA INICIAL DE VIGENCIA INVALIDA</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdVigFin)) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL DE VIGENCIA INVALIDA</ERROR></DATA>';
         return false;
      } elseif ($this->pdVigFin < $this->pdVigIni) {
         $this->pcError = '<DATA><ERROR>FECHA DE VIGENCIA FINAL MENOR QUE FECHA INICIAL</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdVenIni)) {
         $this->pcError = '<DATA><ERROR>FECHA INICIAL DE VENCIMIENTO INVALIDA</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdVenFin)) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL DE VENCIMIENTO INVALIDA</ERROR></DATA>';
         return false;
      } elseif ($this->pdVenFin < $this->pdVenIni) {
         $this->pcError = '<DATA><ERROR>FECHA DE VENCIMIENTO FINAL MENOR QUE FECHA INICIAL</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $llOk = $this->mxLicenciasExcel();
      if (!$llOk)
         return false;
      $llOk = $this->mxCargarXls();
      return $llOk;
   }

   protected function mxLicenciasExcel() {
      $this->lcFilXls = 'S02DLIC1';
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $llFlag = false;
      $lcSql = 'SELECT * FROM V_S02DLIC ';
      if ($this->pcCodPer != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql.="WHERE cCodPer= '$this->pcCodPer' ";
      }
      if ($this->pcCodEmp != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cCodEmp = TRIM('$this->pcCodEmp') ";
      } elseif ($this->pcCodEmp != '*') {
         $lcSql .= "AND cCodEmp = TRIM('$this->pcCodEmp') ";
      }
      if ($this->pcTipLic != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cTipLic = '$this->pcTipLic' ";
      } elseif ($this->pcTipLic != '*') {
         $lcSql .= "AND cTipLic = '$this->pcTipLic' ";
      }
      if ($this->pcSector != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cSector = '$this->pcSector' ";
      } elseif ($this->pcSector != '*') {
         $lcSql .= "AND cSector = '$this->pcSector' ";
      }
      if ($this->pcSecAut != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cSecAut = '$this->pcSecAut' ";
      } elseif ($this->pcSector != '*') {
         $lcSql .= "AND cSecAut = '$this->pcSecAut' ";
      }
      $lcSql.=(!$llFlag) ? 'WHERE ' : 'AND ';
      $lcSql.="dFecVig BETWEEN TO_DATE('$this->pdVigIni', 'yyyy-mm-dd') AND 
               TO_DATE('$this->pdVigFin', 'yyyy-mm-dd') AND
               dFecFin BETWEEN TO_DATE('$this->pdVenIni', 'yyyy-mm-dd') AND 
               TO_DATE('$this->pdVenFin', 'yyyy-mm-dd') ORDER BY cNombre";
      $RS = $loSql->omExec($lcSql);
      $loXls = new CXls();
      $loXls->openXls($this->lcFilXls, $this->pcCodUsu);
      $lnRow = 4;
      while ($laFila = $loSql->fetch($RS)) {
         $loXls->sendXls(0, 'B', $lnRow, $laFila['CNOMBRE']);
         $loXls->sendXls(0, 'C', $lnRow, $laFila['CDESLIC']);
         $loXls->sendXls(0, 'D', $lnRow, $laFila['CNROLIC']);
         $loXls->sendXls(0, 'E', $lnRow, $laFila['DFECVIG']);
         $loXls->sendXls(0, 'F', $lnRow, $laFila['DFECFIN']);
         $loXls->sendXls(0, 'G', $lnRow, $laFila['CDESEST']);
         $loXls->sendXls(0, 'H', $lnRow, $laFila['CAUTORI']);
         $loXls->sendXls(0, 'I', $lnRow, $laFila['CDESRES']);
         $loXls->sendXls(0, 'J', $lnRow, $laFila['CDESSTA']);
         $loXls->sendXls(0, 'K', $lnRow, $laFila['CDESSEC']);
         $loXls->sendXls(0, 'L', $lnRow, $laFila['CDESEMP']);
         $lnRow++;
      } 
      $loSql->omDisconnect();
      $loXls->closeXls();
      if ($lnRow == 4) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
      }
      return true;
   }

   // Init Sanciones
   public function omInitSanciones() {
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }  
      // Trae personal
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE SUBSTR(cCodPer, 1, 1) != '9' ORDER BY cNombre");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL ACTIVO</ERROR></DATA>';
         return false;
      }
      $this->paCodPer[] = array('*', '*** TODOS ***');
      // Normas
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR ORDER BY cCodNor");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodNor[] = array($laFila[0], $laFila[0].' - '.$laFila[1]);
      }
      $this->paCodNor[] = array('*', '*** TODAS ***');
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NORMAS NO DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Tipos de Sancion
      $RS = $loSql->omExec("SELECT cCodSan, cDescri FROM S02TSAN ORDER BY cCodSan");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodSan[] = array($laFila[0], $laFila[0].' - '.$laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>ESTADOS DE LICENCIA NO DEFINIDOS. SUB-TABLA [035]</ERROR></DATA>';
         return false;
      }
      $this->paCodSan[] = array('*', '*** TODAS ***');
      return true;
   }
   
   public function omSancionesExcel() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodNor)) {
         $this->pcError = '<DATA><ERROR>NORMA NO DEFINIDA</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodSan)) {
         $this->pcError = '<DATA><ERROR>SANCION NO DEFINIDA</ERROR></DATA>';
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
      } elseif (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $llOk = $this->mxSancionesExcel();
      if (!$llOk)
         return false;
      $llOk = $this->mxCargarXls();
      return $llOk;
   }

   protected function mxSancionesExcel() {
      $this->lcFilXls = 'S02DSAN1';
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $llFlag = false;
      $lcSql = 'SELECT * FROM V_S02DSAN ';
      if ($this->pcCodPer != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql.="WHERE cCodPer = '$this->pcCodPer' ";
      }
      if ($this->pcCodSan != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cCodSan = '$this->pcCodSan' ";
      } elseif ($this->pcCodSan != '*') {
         $lcSql .= "AND cCodSan = '$this->pcCodSan' ";
      }
      if ($this->pcCodNor != '*' and !$llFlag) {
         $llFlag = true;
         $lcSql .= "WHERE cCodNor = '$this->pcCodNor' ";
      } elseif ($this->pcCodNor != '*') {
         $lcSql .= "AND cCodNor = '$this->pcCodNor' ";
      }
      $lcSql.=(!$llFlag) ? 'WHERE ' : 'AND ';
      $lcSql.="dFecha BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND 
               TO_DATE('$this->pdFecFin', 'yyyy-mm-dd') ORDER BY cNombre";
      $RS = $loSql->omExec($lcSql);
      $loXls = new CXls();
      $loXls->openXls($this->lcFilXls, $this->pcCodUsu);
      $lnRow = 4;
      while ($laFila = $loSql->fetch($RS)) {
         $loXls->sendXls(0, 'B', $lnRow, (string)$laFila['CCODIGO']);
         $loXls->sendXls(0, 'C', $lnRow, $laFila['CNOMBRE']);
         $loXls->sendXls(0, 'D', $lnRow, $laFila['CDESSAN']);
         $loXls->sendXls(0, 'E', $lnRow, $laFila['DFECHA']);
         $loXls->sendXls(0, 'F', $lnRow, $laFila['CDESNOR']);
         $lcValor = $laFila['MOBSERV']->load();
         $loXls->sendXls(0, 'G', $lnRow, "$lcValor");
         $lnRow++;
      } 
      $loSql->omDisconnect();
      $loXls->closeXls();
      if ($lnRow == 4) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
      }
      return true;
   }
  
}
?>
