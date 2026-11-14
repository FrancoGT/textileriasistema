<?php
require_once 'Clases/CBase.php';
require_once 'class/PHPExcel.php';

// Reportes de pruebas
class CPruebasRep extends CBase {
   public $pcNroEva, $paTipo, $paSuperv, $paSubDiv, $paData, $pcSuperv, $paCodPer, $pcFilRet;

   public function __construct() {
      parent::__construct();
      $this->paTipo = $this->paSuperv = $this->paSubDiv = $this->paData = $this->paCodPer = null;
   }
   
   public function omInit() {
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Tipo
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab ='044'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipo[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE EVALUACION DEFINIDOS [044]</ERROR></DATA>';
         return false;
      }
      // Subdivision
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE cCodTab ='020'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paSubDiv[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY SUBDIVISIONES DEFINIDAS [020]</ERROR></DATA>';
         return false;
      }
      // Supervisores
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE cCodPer IN (
                            SELECT DISTINCT cSuperv FROM (
                            SELECT cSuperv FROM S02MEVA UNION 
                            SELECT cSuper2 AS cSuperv FROM S02MEVA)) AND cCodPer != '999999' ORDER BY cNombre");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paSuperv[] = array($laFila[0], $laFila[1]);
      }
      $this->paSuperv[] = array('999999', '* TODOS');
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY SUPERVISORES DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Personal
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE SUBSTR(cCodPer, 1, 1) != '9' ORDER BY cNombre");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
      }
      $this->paCodPer[] = array('999999', '* TODOS');
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL DEFINIDO</ERROR></DATA>';
         return false;
      }
      return true;
   }

   public function omBuscar() {
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      //Carga Datos
      $RS = $loSql->omExec("SELECT cNroEva, TO_CHAR(dFecEva, 'yyyy-mm-dd'), cSuperv, cSuper2, cSubDiv FROM S02MEVA WHERE cNroEva IN 
                            (SELECT cNroEva FROM S02MEVA WHERE SUBSTR(cNroEva, 1, 1) = '$this->pcTipo' AND 
                            dFecEva BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd') 
                            UNION
                            SELECT cNroEva FROM S02MEVA WHERE SUBSTR(cNroEva, 1, 1) = '$this->pcTipo' AND 
                            dFecEva BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd'))
                            ORDER BY dFecEva DESC");
      while ($laFila = $loSql->fetch($RS)) {
         // Filtra por supervisor
         if ($this->pcSuperv != '999999') {
            if (!($laFila[2] == $this->pcSuperv or $laFila[3] == $this->pcSuperv)) {
               continue;
            }
         }
         // Filtra por personal
         if ($this->pcCodPer != '999999') {
            $RS1 = $loSql->omExec("SELECT cCodPer FROM S02DEVA WHERE cNroEva = '$laFila[0]' AND cCodPer = '$this->pcCodPer'");
            $i = $loSql->fetch($RS1);
            if (empty($i[0])) {
               continue;
            }
         }
         // Nombre de supervisor 1
         $RS1 = $loSql->omExec("SELECT cNombre FROM S02MPER WHERE cCodPer = '$laFila[2]'");
         $i = $loSql->fetch($RS1);
         $lcNombr1 = (empty($i[0])) ? '*** ERROR ***' : $i[0];
         // Nombre de supervisor 2
         $RS1 = $loSql->omExec("SELECT cNombre FROM S02MPER WHERE cCodPer = '$laFila[3]'");
         $i = $loSql->fetch($RS1);
         $lcNombr2 = (empty($i[0])) ? '*** ERROR ***' : $i[0];
         // Descripcion de subdivision
         $RS1 = $loSql->omExec("SELECT cDescri FROM V_S02TTAB WHERE cCodTab = '020' AND cCodigo='$laFila[4]'");
         $i = $loSql->fetch($RS1);
         $lcDescri = (empty($i[0])) ? '*** ERROR ***' : $i[0];
         // Genera arreglo
         $this->paData[] = array($laFila[0], $laFila[1], $lcNombr1, $lcNombr2, $lcDescri);
      }
      $loSql->omDisconnect();
      return true;
   }
   
   // Aplica numero de incidente
   public function omAplicar() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Evaluacion
      $RS = $loSql->omExec("SELECT TO_CHAR(dFecInc, 'yyyy-mm-dd'), mDescri FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      $this->pdFecInc = $laFila[0];
      if (empty($this->pdFecInc)) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pmDescri = $laFila[1]->load();
      $loSql->omDisconnect();
      return true;
   }
   
   //Reporte Velocidad por Radar
   public function omEvaluacion1() {
      if (empty($this->pcNroEva)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION INVALIDA</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $RS = $loSql->omExec("SELECT TO_CHAR(A.dFecEva, 'yyyy-mm-dd') AS DFECEVA, A.cHorEva, A.cTren, A.cKilome, A.cVelReg, A.CVelAut, A.cRestri, A.mObserv, A.cCompos, 
                            B.cDescri As cTipVag, C.cDescri AS cDesUn1, D.cDescri AS cDesUn2, E.cDescri AS cDesUn3, G.cDescri AS cConDic, H.cDescri AS cConCli, I.cDescri As cResult,
                            M.cDEscri AS cSubDiv, K.cDEscri AS cCodpry, L.cDescri AS cCodEmp,  A.cCodUn1, A.cCodUn2, A.cCodUn3, Z.cDescri AS cDesEst
                            FROM S02MEVA A 
                            INNER JOIN S02MUNI C ON A.cCodun1 = C.cCodUni 
                            INNER JOIN S02MUNI D ON A.cCodun2 = D.cCodUni 
                            INNER JOIN S02MUNI E ON A.cCodun3 = E.cCodUni 
                            LEFT OUTER JOIN V_S02TTAB B ON A.cTipVag = B.cCodigo AND B.cCodTab = '002'
                            LEFT OUTER JOIN V_S02TTAB G ON A.cConDic = G.cCodigo AND G.cCodTab = '004'
                            LEFT OUTER JOIN V_S02TTAB H ON A.cConCli = H.cCodigo AND H.cCodTab = '005'
                            LEFT OUTER JOIN V_S02TTAB I ON A.cResult = I.cCodigo AND I.cCodTab = '003'
                            LEFT OUTER JOIN V_S02TTAB M ON A.cSubDiv = M.cCodigo AND M.cCodTab = '020'
                            INNER JOIN S02MPRY K ON A.cCodpry = K.cCodpry 
                            INNER JOIN S02MEMP L ON A.cCodEmp = L.cCOdEMP
                            LEFT OUTER JOIN V_S02TTAB Z ON A.cEstaci = Z.cCodigo AND Z.cCodTab = '021'
                            WHERE cNroEva = '$this->pcNroEva'");
      $laFila = $loSql->fetch($RS);
      $ldFecEva = $laFila['DFECEVA'];
      $lcHorEva = $laFila['CHOREVA'];
      $lcTren   = $laFila['CTREN'];
      $lcKilome = $laFila['CKILOME'];
      $lcVelReg = $laFila['CVELREG'];
      $lcVelAut = $laFila['CVELAUT'];
      $lcRestri = substr($laFila['CRESTRI'], 0, 1) == 'N' ? 'NO': 'SI';
      $lmObserv = $laFila['MOBSERV']->load();
      $lcCompos = $laFila['CCOMPOS'];
	  $lcEstaci = $laFila['CDESEST'];
//	  print_r($laFila);
      $lcEstaci = (empty($lcEstaci)) ? 'N/C' : $lcEstaci;
      $lcTipVag = $laFila['CTIPVAG'];
      $lcCodpry = $laFila['CCODPRY'];
      $lcCodEmp = $laFila['CCODEMP'];
      $lcDesUn1 = trim($laFila['CCODUN1']) == '99999' ? ' ' : trim($laFila['CCODUN1']);
      $lcDesUn2 = trim($laFila['CCODUN2']) == '99999' ? ' ' : trim($laFila['CCODUN2']);
      $lcDesUn3 = trim($laFila['CCODUN3']) == '99999' ? ' ' : trim($laFila['CCODUN3']);
      $lcDesUn  = $lcDesUn1.'/'.$lcDesUn2.'/'.$lcDesUn3;
      $lcSubDiv = $laFila['CSUBDIV'];
      $lcConDic = $laFila['CCONDIC'];
      $lcConCli = $laFila['CCONCLI'];
      $lcResult = $laFila['CRESULT'];
      $RS1 = $loSql->omExec("SELECT DISTINCT A.cNombre, C.cDescri FROM S02MPER A 
                             INNER JOIN S02DEVA B ON A.cCodPer = B.cCodPer
                             LEFT OUTER JOIN V_S02TTAB C ON B.cPuesto = C.cCodigo AND C.cCodTab = '022'
                             WHERE cNroEva = '$this->pcNroEva'");
      $lcMaq  = 'S/E';
      $lcBreq = 'S/E';
      $lcJefe = 'S/E';
      while($laFila = $loSql->fetch($RS1)) {
         if (substr($laFila[1], 0, 1) == 'M') {
            $lcMaq = $laFila[0];
         } elseif (substr($laFila[1], 0, 1) == 'B') {
            $lcBreq = $laFila[0];
         } elseif (substr($laFila[1], 0, 1) == 'J') {
            $lcJefe=$laFila[0];
         }
      }
      $RS = $loSql->omExec("SELECT A.cNombre AS cSuperv FROM S02MPER A INNER JOIN S02MEVA B ON A.cCodPer=B.cSuperv WHERE cNroEva='$this->pcNroEva'");
      $laFila = $loSql->fetch($RS);
      $lcSuperv = $laFila['CSUPERV'];
      $RS = $loSql->omExec("SELECT A.cNombre AS cSuper2 FROM S02MPER A INNER JOIN S02MEVA B ON A.cCodPer=B.cSuper2 WHERE cNroEva='$this->pcNroEva'");
      $laFila = $loSql->fetch($RS);
      $lcSuper2 = $laFila['CSUPER2'];
      $loSql->omDisconnect();
      // Construir Informe en RTF
      $laArray = array('#*DFECEVA*#'=>$ldFecEva, '#*CHOREVA*#'=>$lcHorEva, 
                       '#*CTREN*#'=>$lcTren,     '#*CKILOME*#'=>$lcKilome, 
                       '#*CVELREG*#'=>$lcVelReg, '#*CVELAUT*#'=>$lcVelAut, 
                       '#*CRESTRI*#'=>$lcRestri, '#*MOBSERV*#'=>$lmObserv, 
                       '#*CCOMPOS*#'=>$lcCompos, '#*V1*#'=>$lcTipVag, 
                       '#*V2*#'=>$lcConDic,      '#*CDESUN*#'=>$lcDesUn, 
                       '#*CSUBDIV*#'=>$lcSubDiv, '#*CCONDIC*#'=>$lcConDic, 
                       '#*CCONCLI*#'=>$lcConCli, '#*CRESULT*#'=>$lcResult, 
                       '#*CSUPERV*#'=>$lcSuperv, '#*CSUPER2*#'=>$lcSuper2, 
                       '#*CNROEVA*#'=>$this->pcNroEva, '#*CMAQ*#'=>$lcMaq, 
                       '#*CBREQ*#'=>$lcBreq,     '#*CCODPRY*#'=>$lcCodpry, 
                       '#*CCODEMP*#'=>$lcCodEmp, '#*CJEFE*#'=>$lcJefe, '#*CDESEST*#'=>$lcEstaci);
      //$laAArray = array();
      $lo = new CRtf();
      $lo->pcFile = 'EVAVELRAD1';
      $lo->pcCodUsu = $_SESSION['gcCodUsu'];
      $llOk = $lo->omInicializar();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR 1</ERROR></DATA>';
         return false;
      }
      $lo->paArray = $laArray;
      $llOk = $lo->omGenerar(true);
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR 2</ERROR></DATA>';
         return false;
      }
	  $this->pcFilRet=$lo->pcFilRet;
      //echo "<a href = '$lo->pcFilRet'>Archivo de Salida</a>";
      return true;
   }
   
   //Reporte Eficiencia en terreno
   public function omEvaluacion2() {
      if (empty($this->pcNroEva)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION INVALIDA</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $RS = $loSql->omExec("SELECT TO_CHAR(A.dFecEva, 'yyyy-mm-dd') AS DFECEVA, A.cHorEva, A.cTren, A.cKilome, A.mObserv, A.cCompos, 
                            B.cDescri AS cTipVag, G.cDescri AS cEstaci, C.cDescri AS cDesUn1, 
                            D.cDescri AS cDesUn2, E.cDescri AS cDesUn3, F.cDescri AS cSubDiv, K.cDEscri AS cCodpry, L.cDescri AS cCodEmp,
                            A.cCodUn1, A.cCodUn2, A.cCodUn3
                            FROM S02MEVA A 
                            INNER JOIN V_S02TTAB B ON A.cTipVag=B.cCodigo AND B.cCodTab='002'
                            INNER JOIN S02MUNI C ON A.cCodun1=C.cCodUni 
                            INNER JOIN S02MUNI D ON A.cCodun2=D.cCodUni 
                            INNER JOIN S02MUNI E ON A.cCodun3=E.cCodUni 
                            LEFT OUTER JOIN V_S02TTAB F ON A.cSubDiv=F.cCodigo AND F.cCodTab='020'
                            LEFT OUTER JOIN V_S02TTAB G ON A.cEstaci=G.cCodigo AND G.cCodTab='021'
                            INNER JOIN S02MPRY K ON A.cCodpry=K.cCodpry 
                            INNER JOIN S02MEMP L ON A.cCodEmp=L.cCOdEMP
                            WHERE cNroEva='$this->pcNroEva'");
      $laFila = $loSql->fetch($RS);
      $ldFecEva = $laFila['DFECEVA'];
      $lcHorEva = $laFila['CHOREVA'];
      $lcTren   = $laFila['CTREN'];
      $lcKilome = $laFila['CKILOME'];
      $lmObserv = $laFila['MOBSERV']->load();
      $lcCompos = $laFila['CCOMPOS'];
      $lcTipVag = $laFila['CTIPVAG'];
      $lcEstaci = $laFila['CESTACI'];
	  $lcEstaci = (empty($lcEstaci)) ? 'N/C' : $lcEstaci;
      $lcCodPry = $laFila['CCODPRY'];
      $lcCodEmp = $laFila['CCODEMP'];
      $lcDesUn1 = trim($laFila['CCODUN1']) == '99999' ? ' ' : trim($laFila['CCODUN1']);
      $lcDesUn2 = trim($laFila['CCODUN2']) == '99999' ? ' ' : trim($laFila['CCODUN2']);
      $lcDesUn3 = trim($laFila['CCODUN3']) == '99999' ? ' ' : trim($laFila['CCODUN3']);
      $lcDesUn  = $lcDesUn1.'/'.$lcDesUn2.'/'.$lcDesUn3;
      $lcSubDiv = $laFila['CSUBDIV'];
     //MQ- BR- JT
      $RS = $loSql->omExec("SELECT DISTINCT B.cNombre, C.cDescri AS cDesPue, A.cCodPer,A.cPuesto
                            FROM S02DEVA A 
                            INNER JOIN S02MPER B ON A.cCodPer = B.cCodPer
                            LEFT OUTER JOIN V_S02TTAB C ON A.cPuesto = C.cCodigo AND C.cCodTab = '022'
                            WHERE cNroEva = '$this->pcNroEva'");
      while ($laFila = $loSql->fetch($RS)) {
          if ($laFila[3] == 'M'){
             $lcMaq =$laFila[0];
          } elseif ($laFila[3] == 'B'){
             $lcBreq =$laFila[0];
          } elseif ($laFila[3] == 'J'){
             $lcJefe =$laFila[0];
          }
      }
      $laTabla  = array(array('\b Codigo'), array('\b Norma'), array('\b MQ'), array('\b JT'), array('\b BR'));
      $lcCodNor = $lcResMQ = $lcResBR = $lcResJT = '-';
      $llFirst = true;
      $RS = $loSql->omExec("SELECT A.cCodNor, B.cDescri AS cDesNor, C.cDescri AS cDesRes, A.cPuesto 
                            FROM S02DEVA A
                            INNER JOIN S02MNOR B ON A.cCodNor=B.cCodNor
                            LEFT OUTER JOIN V_S02TTAB C ON A.cResult = C.cCodigo AND C.cCodTab = '003'
                            WHERE A.cNroEva = '$this->pcNroEva' ORDER BY A.cCodNor");
      $RS1 = $loSql->omExec("SELECT COUNT(*)
                            FROM S02DEVA A
                            INNER JOIN S02MNOR B ON A.cCodNor=B.cCodNor
                            LEFT OUTER JOIN V_S02TTAB C ON A.cResult = C.cCodigo AND C.cCodTab = '003'
                            WHERE A.cNroEva = '$this->pcNroEva' ORDER BY A.cCodNor");
       $llEnd = $loSql->fetch($RS1);
       $count=0;
       while ($laFila = $loSql->fetch($RS)) {
          $count++;
        if ($llFirst){
           $lcCodNor=$laFila[0];
           $lcDesNor=$laFila[1];
           if ($laFila[3] == 'M'){
             $lcResMQ = $laFila[2]; 
           }elseif ($laFila[3] == 'B'){
             $lcResBR = $laFila[2];
           }elseif ($laFila[3] == 'J'){
             $lcResJT = $laFila[2]; 
           }
           $llFirst=false;
        }elseif ($lcCodNor == $laFila[0] ){
           if ($laFila[3] == 'M'){
             $lcResMQ = $laFila[2]; 
           }elseif ($laFila[3] == 'B'){
             $lcResBR = $laFila[2];
           }elseif ($laFila[3] == 'J'){
             $lcResJT = $laFila[2]; 
           }
        }elseif ($lcCodNor != $laFila[0] ) {
           array_push($laTabla[0], $lcCodNor);
           array_push($laTabla[1], $lcDesNor);
           array_push($laTabla[2], '\\qc '.$lcResMQ);
           array_push($laTabla[3], '\\qc '.$lcResJT);
           array_push($laTabla[4], '\\qc '.$lcResBR);
           $lcCodNor=$laFila[0];
           $lcDesNor=$laFila[1];
           $lcResMQ = '-';
           $lcResBR = '-';
           $lcResJT = '-';
           if ($laFila[3] == 'M'){
             $lcResMQ = $laFila[2]; 
           }elseif ($laFila[3] == 'B'){
             $lcResBR = $laFila[2];
           }elseif ($laFila[3] == 'J'){
             $lcResJT = $laFila[2]; 
           }
        }
      if($count==$llEnd[0]){
           array_push($laTabla[0], $lcCodNor);
           array_push($laTabla[1], $lcDesNor);
           array_push($laTabla[2], '\\qc '.$lcResMQ);
           array_push($laTabla[3], '\\qc '.$lcResJT);
           array_push($laTabla[4], '\\qc '.$lcResBR);
      }
   }
     $RS = $loSql->omExec("SELECT A.cNombre AS cSuperv FROM S02MPER A INNER JOIN S02MEVA B ON A.cCodPer=B.cSuperv WHERE cNroEva='$this->pcNroEva'");
      $laFila = $loSql->fetch($RS);
      $lcSuperv = $laFila['CSUPERV'];
      $loSql->omDisconnect();
      // Construir Informe en RTF
     $laArray = array('#*DFECEVA*#'=>$ldFecEva, 
                       '#*CHOREVA*#'=>$lcHorEva, 
                       '#*CTREN*#'=>$lcTren, 
                       '#*CKILOME*#'=>$lcKilome, 
                       '#*MOBSERV*#'=>$lmObserv, 
                       '#*CCOMPOS*#'=>$lcCompos, 
                       '#*V1*#'=>$lcTipVag, 
                       '#*CESTACI*#'=>$lcEstaci, 
                       '#*CDESUN*#'=>$lcDesUn, 
                       '#*CSUBDIV*#'=>$lcSubDiv, 
                       '#*CRESULT*#'=>$lcResult, 
                       '#*CSUPERV*#'=>$lcSuperv, 
                       '#*CNROEVA*#'=>$this->pcNroEva, 
                       '#*CMAQ*#'=>$lcMaq, 
                       '#*CBREQ*#'=>$lcBreq,
                       '#*CJEFE*#'=>$lcJefe,
                       '#*CCODPRY*#'=>$lcCodPry, 
                       '#*CCODEMP*#'=>$lcCodEmp,
                      );
      $Dim=array(1500, 5300, 900, 900, 900);
      $laAArray = array('#*TABLA*#'=>array(count($laTabla), count($laTabla[0]), $laTabla, $Dim)
                       );
      $lo = new CRtf();
      $lo->pcFile = 'EVAEFIT';
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
      $llOk = $lo->omGenerarArray(true);
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR 3</ERROR></DATA>';
         return false;
      }
	  $this->pcFilRet=$lo->pcFilRet;
      //echo "<a href = '$lo->pcFilRet'>Archivo de Salida</a>";
      return true;
   }
   
   //Reporte Evaluacion en conduccion de trenes
   public function omEvaluacion3() {
      if (empty($this->pcNroEva)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION INVALIDA</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql="SELECT TO_CHAR(A.dFecEva, 'yyyy-mm-dd') AS DFECEVA, A.mObserv, A.cHorEva, B.cDescri AS cEstaci, C.cDescri AS cEstacf, A.cHorSer, A.cTren,
                            D.cDescri AS cDesUn1, E.cDescri AS cDesUn2, F.cDescri AS cDesUn3, A.cCompos, G.cNombre, K.cDEscri AS cCodpry, L.cDescri AS cCodEmp, I.cDEscri AS cSubDiv, 
                            A.cKilome, A.cKilomf, H.cDescri AS cTipVag, M.cDescri As cResult, A.cCodUn1, A.cCodUn2, A.cCodUn3
                            FROM S02MEVA A
                            LEFT OUTER JOIN V_S02TTAB M ON A.cResult = M.cCodigo AND M.cCodTab = '027'
                            LEFT OUTER JOIN V_S02TTAB B ON A.cEstaci = B.cCodigo AND B.cCodTab = '021'
                            LEFT OUTER JOIN V_S02TTAB C ON A.cEstacf = C.cCodigo AND C.cCodTab = '021'
                            LEFT OUTER JOIN V_S02TTAB H ON A.cTipVag = H.cCodigo AND H.cCodTab = '002'
                            INNER JOIN S02MUNI D ON A.cCodun1 = D.cCodUni 
                            INNER JOIN S02MUNI E ON A.cCodun2 = E.cCodUni 
                            INNER JOIN S02MUNI F ON A.cCodun3 = F.cCodUni
                            INNER JOIN S02MPER G ON A.cSuperv = G.cCodPer 
                            INNER JOIN S02MPRY K ON A.cCodpry = K.cCodpry 
                            INNER JOIN S02MEMP L ON A.cCodEmp = L.cCOdEMP
                            LEFT OUTER JOIN V_S02TTAB I ON A.cSubDiv = I.cCodigo AND I.cCodTab = '020'
                            WHERE cNroEva = '$this->pcNroEva'";
	  $RS = $loSql->omExec($lcSql);
	  $laFila = $loSql->fetch($RS);
      $ldFecEva = $laFila['DFECEVA'];
      $lcHorEva = $laFila['CHOREVA'];
	  $lcEstaci = $laFila['CESTACI'];
	  $lcEstacf = $laFila['CESTACF'];
      $lcEstaci = (empty($lcEstaci)) ? 'N/C' : $lcEstaci;
      $lcEstacf = (empty($lcEstacf)) ? 'N/C' : $lcEstacf;
      $lcHorSer = $laFila['CHORSER'];
      $lcCompos = $laFila['CCOMPOS'];
      $lcNombre = $laFila['CNOMBRE'];
      $lcCodpry = $laFila['CCODPRY'];
      $lcCodEmp = $laFila['CCODEMP'];
      $lcSubDiv = $laFila['CSUBDIV'];
      $lcKilome = $laFila['CKILOME'];
      $lcKilomf = $laFila['CKILOMF'];
      $lcTipVag = $laFila['CTIPVAG'];
      $lcResult = $laFila['CRESULT'];
      $lmObserv = $laFila['MOBSERV']->load();
      $lcTren = $laFila['CTREN'];
      $lcDesUn1 = trim($laFila['CCODUN1']) == '99999' ? ' ' : trim($laFila['CCODUN1']);
      $lcDesUn2 = trim($laFila['CCODUN2']) == '99999' ? ' ' : trim($laFila['CCODUN2']);
      $lcDesUn3 = trim($laFila['CCODUN3']) == '99999' ? ' ' : trim($laFila['CCODUN3']);
      $lcDesUn  = $lcDesUn1.'/'.$lcDesUn2.'/'.$lcDesUn3;
      $RS2 = $loSql->omExec("SELECT B.cDescri AS cEvaCon, A.cResult
                             FROM S02DEVA A
                             LEFT OUTER JOIN V_S02TTAB B ON A.cEvaCon = B.cCodigo And B.cCodTab = '026'
                             WHERE cNroEva = '$this->pcNroEva'");
      $laObs = null;
      $laObs = array(array('\b OBSERVACIONES'), array('\b RES'));
      while($laFila = $loSql->fetch($RS2)) {
         array_push($laObs[0], $laFila[0]);
         array_push($laObs[1], (substr($laFila[1], 0, 1) == 'S' ?'\\qc SI':'\\qc NO'));
      }
      $RS1 = $loSql->omExec("SELECT DISTINCT B.cNombre AS cMaq FROM S02DEVA A INNER JOIN S02MPER B ON A.cCodPer=B.cCodPer WHERE cNroEva='$this->pcNroEva'");
      $laFila = $loSql->fetch($RS1);
      $lcMaq = $laFila['CMAQ'];
      $loSql->omDisconnect();
      // Construir Informe en RTF
      $laArray = array('#*DFECEVA*#'=>$ldFecEva, 
                       '#*CHOREVA*#'=>$lcHorEva, 
                       '#*CTREN*#'=>$lcTren, 
                       '#*CCOMPOS*#'=>$lcCompos, 
                       '#*CESTACI*#'=>$lcEstaci, 
                       '#*CCODPRY*#'=>$lcCodpry, 
                       '#*CCODEMP*#'=>$lcCodEmp, 
                       '#*CESTACF*#'=>$lcEstacf, 
                       '#*CHORSER*#'=>$lcHorSer, 
                       '#*CDESUN*#'=>$lcDesUn, 
                       '#*CNOMBRE*#'=>$lcNombre, 
                       '#*CNROEVA*#'=>$this->pcNroEva, 
                       '#*CMAQ*#'=>$lcMaq, 
                       '#*MOBSERV*#'=>$lmObserv, 
                       '#*CKILOME*#'=>$lcKilome, 
                       '#*CKILOMF*#'=>$lcKilomf, 
                       '#*CTIPVAG*#'=>$lcTipVag, 
                       '#*CRESULT*#'=>$lcResult, 
                       '#*CSUBDIV*#'=>$lcSubDiv
                      );
      $Dime=array(10000, 500);
      $laAArray = array('#*TABLAOBS*#'=>array(count($laObs), count($laObs[0]), $laObs, $Dime)
                       );
      $lo = new CRtf();
      $lo->pcFile = 'EVACONTE';
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
      $llOk = $lo->omGenerarArray(true);
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR 3</ERROR></DATA>';
         return false;
      }
      $this->pcFilRet=$lo->pcFilRet;
	  //echo "<a href = '$lo->pcFilRet'>Archivo de Salida</a>";
      return true;
   }
   
   // Conduccion de tractos
   public function omEvaluacion4() {
      if (empty($this->pcNroEva)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION INVALIDA</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
     $lcSql="SELECT TO_CHAR(A.dFecEva, 'yyyy-mm-dd') AS DFECEVA, H.cDescri AS cTipVag, A.mObserv, A.cHorEva, B.cDescri AS cEstaci, C.cDescri AS cEstacf, A.cHorSer, A.cTren,
                            D.cDescri AS cDesUn1, E.cDescri AS cDesUn2, F.cDescri AS cDesUn3, A.cCompos, G.cNombre,
                            I.cDEscri AS cSubDiv, A.cKilome, A.cKilomf, J.cDescri AS cResult, K.cDEscri AS cCodpry, L.cDescri AS cCodEmp,
                     A.cCodUn1, A.cCodUn2, A.cCodUn3
                            FROM S02MEVA A
                            LEFT OUTER JOIN V_S02TTAB B ON A.cEstaci=B.cCodigo AND B.cCodTab='021'
                            LEFT OUTER JOIN V_S02TTAB C ON A.cEstacf=C.cCodigo AND C.cCodTab='021'
                            INNER JOIN S02MUNI D ON A.cCodun1=D.cCodUni 
                            INNER JOIN S02MUNI E ON A.cCodun2=E.cCodUni 
                            INNER JOIN S02MUNI F ON A.cCodun3=F.cCodUni
                            INNER JOIN S02MPER G ON A.cSuperv=G.cCodPer 
                            LEFT OUTER JOIN V_S02TTAB H ON A.cTipVag=H.cCodigo AND H.cCodTab='002'
                            LEFT OUTER JOIN V_S02TTAB I ON A.cSubDiv=I.cCodigo AND I.cCodTab='020'
                            LEFT OUTER JOIN V_S02TTAB J ON A.cResult=J.cCodigo AND J.cCodTab='027'
                            INNER JOIN S02MPRY K ON A.cCodpry=K.cCodpry 
                            INNER JOIN S02MEMP L ON A.cCodEmp=L.cCOdEMP
                            WHERE cNroEva = '$this->pcNroEva'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $ldFecEva = $laFila['DFECEVA'];
      $lcHorEva = $laFila['CHOREVA'];
      $lcEstaci = $laFila['CESTACI'];
      $lcEstacf = $laFila['CESTACF'];
	  $lcEstaci = (empty($lcEstaci))? 'N/C':$lcEstaci;
      $lcEstacf = (empty($lcEstacf))? 'N/C':$lcEstacf;
      $lcHorSer = $laFila['CHORSER'];
      $lcCompos = $laFila['CCOMPOS'];
      $lcNombre = $laFila['CNOMBRE'];
      $lmObserv = $laFila['MOBSERV']->load();
      $lcTren = $laFila['CTREN'];
      $lcDesUn1 = trim($laFila['CCODUN1']) == '99999' ? ' ' : trim($laFila['CCODUN1']);
      $lcDesUn2 = trim($laFila['CCODUN2']) == '99999' ? ' ' : trim($laFila['CCODUN2']);
      $lcDesUn3 = trim($laFila['CCODUN3']) == '99999' ? ' ' : trim($laFila['CCODUN3']);
      $lcDesUn  = $lcDesUn1.'/'.$lcDesUn2.'/'.$lcDesUn3;
      $lcTipVag = $laFila['CTIPVAG'];
      $lcCodpry = $laFila['CCODPRY'];
      $lcCodEmp = $laFila['CCODEMP'];
      $lcSubDiv = $laFila['CSUBDIV'];
      $lcResult = $laFila['CRESULT'];
      $lcKilome = $laFila['CKILOME'];
      $lcKilomf = $laFila['CKILOMF'];
      $RS2 = $loSql->omExec("SELECT B.cDescri AS cEvaCon, A.cResult FROM S02DEVA A
                             LEFT OUTER JOIN V_S02TTAB B ON A.cEvaCon = B.cCodigo AND B.cCodTab = '036'
                             WHERE cNroEva = '$this->pcNroEva'");
      $laObs = null;
      $laObs = array(array('\b OBSERVACIONES'), array('\b RES'));
      while($laFila = $loSql->fetch($RS2)) {
         array_push($laObs[0], $laFila[0]);
         array_push($laObs[1], (substr($laFila[1], 0, 1) == 'S' ?'\\qc SI':'\\qc NO'));
      }
      $RS1 = $loSql->omExec("SELECT DISTINCT B.cNombre AS cMaq FROM S02DEVA A INNER JOIN S02MPER B ON A.cCodPer=B.cCodPer WHERE cNroEva='$this->pcNroEva'");
      $laFila = $loSql->fetch($RS1);
      $lcMaq = $laFila['CMAQ'];
      $loSql->omDisconnect();
      // Construir Informe en RTF
      $laArray = array('#*DFECEVA*#'=>$ldFecEva, 
                       '#*CHOR*#'=>$lcHorEva, 
                       '#*CTREN*#'=>$lcTren, 
                       '#*CCOMPOS*#'=>$lcCompos, 
                       '#*CESTACI*#'=>$lcEstaci, 
                       '#*CESTACF*#'=>$lcEstacf, 
                       '#*CHORSER*#'=>$lcHorSer, 
                       '#*CDESUN*#'=>$lcDesUn, 
                       '#*CNOMBRE*#'=>$lcNombre, 
                       '#*CNROEVA*#'=>$this->pcNroEva, 
                       '#*CMAQ*#'=>$lcMaq, 
                       '#*MOBSERV*#'=>$lmObserv, 
                       '#*V1*#'=>$lcTipVag, 
                       '#*CCODPRY*#'=>$lcCodpry, 
                       '#*CCODEMP*#'=>$lcCodEmp, 
                       '#*CSUBDIV*#'=>$lcSubDiv, 
                       '#*CRESULT*#'=>$lcResult, 
                       '#*CKILOME*#'=>$lcKilome, 
                       '#*CKILOMF*#'=>$lcKilomf, 
                      );
      $Dime = array(10000, 500);
      $laAArray = array('#*TABLAOBS*#'=>array(count($laObs), count($laObs[0]), $laObs, $Dime)
                       );
      $lo = new CRtf();
      $lo->pcFile = 'EVACONTA';
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
      $llOk = $lo->omGenerarArray(true);
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR 3</ERROR></DATA>';
         return false;
      }
      $this->pcFilRet=$lo->pcFilRet;
	  //echo "<a href = '$lo->pcFilRet'>Archivo de Salida</a>";
      return true;
   }
   
   // Reporte: informe de investigacion de incidentes
   public function omInformeInvestigacion() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $RS = $loSql->omExec("SELECT TO_CHAR(dFecInc, 'yyyy-mm-dd') AS DFECINC, cHorInc, cLugar, mDescri, mLesPer, cRepFis, cLesPer, cDamPro FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO EXISTE</ERROR></DATA>';
         return false;
      }
      $ldFecInc = $laFila['DFECINC'];
      $lmDescri = $laFila['MDESCRI']->load();
      $lcHora = $laFila['CHORINC'];
      $lcLugar = $laFila['CLUGAR'];
      $lmLesPer = $laFila['MLESPER']->load();
      $lcRepFis = $laFila['CREPFIS'];
      $lcLesPer = ($laFila['CLESPER']) == 'S' ? 'SI': 'NO';
      $lcDamPro1 = substr($laFila['CDAMPRO'], 0, 1) == 'N' ? 'NO': 'SI';
      $lcDamPro2 = substr($laFila['CDAMPRO'], 1, 1) == 'N' ? 'NO': 'SI';
      $lcDamPro3 = substr($laFila['CDAMPRO'], 2, 1) == 'N' ? 'NO': 'SI';
      $lcDamPro4 = substr($laFila['CDAMPRO'], 3, 1) == 'N' ? 'NO': 'SI';
      $lcDamPro = ($laFila['CDAMPRO'] == 'NNNN')? 'NO': 'SI';
      $RS = $loSql->omExec("SELECT cDescri FROM V_S02TTAB WHERE cCodTab = '008' AND cCodigo = '$lcLugar'");
      $laFila = $loSql->fetch($RS);
      $lcDesLug = '*** NO DEFINIDO ***';
      if (!empty($laFila[0])) {
         $lcDesLug = $laFila[0];
      }
      // Personal involucrado
      $RS = $loSql->omExec("SELECT B.cNombre, C.cDescri AS cDesCar FROM S02DPIN A 
                            INNER JOIN S02MPER B ON A.cCodPer = B.cCodPer
                            LEFT OUTER JOIN V_S02TTAB C ON A.cCarDis = C.cCodigo AND C.cCodTab = '045'
                            WHERE cNroInc = '$this->pcNroInc'");
      $lcNomTrab = '';
      $llFirst = true;
      while ($laFila = $loSql->fetch($RS)) {
         $lcNomTrab .= (!$llFirst)? '\line ':'';
         $llFirst = false;
         $lcNomTrab .= trim($laFila[0]).' - '.trim($laFila[1]);
      }
      // Unidades involucradas
      $RS = $loSql->omExec("SELECT cDescri FROM (SELECT A.cNroInc, A.cCodUni, B.cDescri FROM S02DUIN A
                            INNER JOIN S02MUNI B ON A.cCodUni = B.cCodUni WHERE A.cCodUni != '99999'
                            UNION 
                            SELECT cNroInc, cCodUni, cDescri FROM S02DUIN WHERE cCodUni = '99999') 
                            WHERE cNroInc = '$this->pcNroInc'");
      $lcUniInv = '';
      $llFirst = true;
      while ($laFila = $loSql->fetch($RS)) {
         $lcUniInv .= (!$llFirst)? '\line ':'';
         $llFirst = false;
         $lcUniInv .= trim($laFila[0]);
      }
      // Unidades involucradas y daño
      $RS = $loSql->omExec("SELECT * FROM (SELECT B.cDescri, A.cDamage FROM S02DUIN A INNER JOIN S02MUNI B ON A.cCodUni = B.cCodUni 
                            WHERE A.cCodUni != '99999' AND cNroInc = '$this->pcNroInc'
                            UNION 
                            SELECT cDescri, cDamage FROM S02DUIN WHERE cCodUni = '99999' AND cNroInc = '$this->pcNroInc') ORDER BY cDescri");
      $laUniInvDa = null;
      $laUniInvDa = array(array('Vehículos / Equipos / Propiedad'), array('Daños'));
      while($laFila = $loSql->fetch($RS)) {
         array_push($laUniInvDa[0], $laFila[0]);
         array_push($laUniInvDa[1], $laFila[1]);
      }
      //Supervisores Inmediatos
      $RS = $loSql->omExec("SELECT B.cNombre FROM S02DPIN A INNER JOIN S02MPER B ON A.cSuperv = B.cCodper 
                            WHERE cNroInc = '$this->pcNroInc'");
      $lcSupInm = '';
      $llFirst = true;
      while ($laFila = $loSql->fetch($RS)) {
         $lcSupInm .= (!$llFirst)? '\line ':'';
         $llFirst = false;
         $lcSupInm .= trim($laFila[0]);
      }
      //Eventos Cronologicos
      $RS = $loSql->omExec("SELECT mEveCro FROM S02DCAU WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      $lmEveCro = $laFila[0]->load();
      // Datos del personal involucrado
      $RS = $loSql->omExec("SELECT * FROM (SELECT cNroInc, cNombre, 'N/C' AS cDesDis, cNroDni, cActivi, cUbicac, 'N/C' AS cSuperv, cEntren, cCodPer 
                            FROM S02DPIN WHERE cCodPer = '999999'
                            UNION
                            SELECT A.cNroInc, B.cNombre, D.cDescri AS cDesDis, B.cNroDni, A.cActivi, A.cUbicac, 
                            C.cNombre AS Superv, A.cEntren, B.cCodPer FROM S02DPIN A
                            INNER JOIN S02MPER B ON A.cCodPer = B.cCodPer
                            INNER JOIN S02MPER C ON A.cSuperv = C.cCodPer
                            LEFT JOIN V_S02TTAB D ON A.cCarDis = D.cCodigo AND D.cCodTab = '045')
                            WHERE cNroInc = '$this->pcNroInc'");
      $laPerInv= array(array('DATOS'));
      while ($laFila = $loSql->fetch($RS)) {
         $RS1 = $loSql->omExec("SELECT cCodCur, TO_CHAR(dFecha, 'yyyy-mm-dd') AS DFECHA, cDescri  FROM V_S02DCAP WHERE cCodPer = '$laFila[8]' order by dFecha DESC, cCodCur");
         $laFilaw = $loSql->fetch($RS1);
         $RS2= $loSql->omExec("SELECT cCodNor, cDesNor, TO_CHAR(dFecha, 'yyyy-mm-dd') AS DFECHA FROM V_S02DCAP WHERE cCodPer = '$laFila[8]' AND cCodCur = '$laFilaw[0]'");
         while ($laFilax = $loSql->fetch($RS2)) {
            $lcTemp1 .= ' \line Codigo: '.$laFilax[0].$laFilax[1];
         }
         $lcTemp = trim($laFila[1]).'\line - Cargo: '.trim($laFila[2]).'\line - Nro. DNI: '.trim($laFila[3]).'\line - Entrenamiento: '.trim(($laFila[7] == 'N')? 'NO': 'SI').'\line - Actividad: '.trim($laFila[4]).'\line - Ubicacion: '.trim($laFila[5]).'\line - Supervisor Inmediato: '.trim($laFila[6]).'\line - Ultima Capacitación: '.trim($laFilaw[2]).' - '.trim($laFilaw[1]).'\line - Normas:'.$lcTemp1;
         $lcTemp1 = null;
         array_push($laPerInv[0], $lcTemp);
      }
      //Medio Abmiente SELECT cperpro, cTieper, cDamAmb FROM S02MINC
      $RS = $loSql->omExec("SELECT mDamAmb, cPerPro, cTieper, cDamAmb FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      $lcPerPro=($laFila['CPERPRO'] == 'N')? 'NO': 'SI';
      $lcTiePer=$laFila['CTIEPER'];
      $lcDamAmb1 = substr($laFila['CDAMAMB'], 0, 1) == 'N' ? 'NO': 'SI';
      $lcDamAmb2 = substr($laFila['CDAMAMB'], 1, 1) == 'N' ? 'NO': 'SI';
      $lcDamAmb3 = substr($laFila['CDAMAMB'], 2, 1) == 'N' ? 'NO': 'SI';
      $lcDamAmb = ($laFila['CDAMAMB'] == 'NNN')? 'NO': 'SI';
      $lmDamAmb = $laFila['MDAMAMB']->load();
      $lcActivi = $laFila['CACTIVI'];
      //Factor Critico y Conlusiones
      $RS = $loSql->omExec("SELECT mFacCri, mConclu FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFacCri = null;
      $laFacCri = array(array('Factor Crítico'), array('Conclusiones'));
      while($laFila = $loSql->fetch($RS)) {
         array_push($laFacCri[0], $laFila[0]->load());
         array_push($laFacCri[1], $laFila[1]->load());
      }
      //Causas Inmediatas
      $RS = $loSql->omExec("SELECT B.cDescri, A.mObserv 
                            FROM S02DFCR A INNER JOIN S02MFCR B ON A.cCodFcr=B.cCodFcr
                            WHERE cNroInc = '$this->pcNroInc' AND A.cCodFcr LIKE 'PS%'");
      $laActSub = null;
      $laActSub = array(array('Causa'), array('Observación'));
      while($laFila = $loSql->fetch($RS)) {
         array_push($laActSub[0], $laFila[0]);
         array_push($laActSub[1], $laFila[1]->load());
      }
      $RS = $loSql->omExec("SELECT B.cDescri, A.mObserv 
                            FROM S02DFCR A INNER JOIN S02MFCR B ON A.cCodFcr=B.cCodFcr
                            WHERE cNroInc = '$this->pcNroInc' AND A.cCodFcr LIKE 'CS%'");
      $laPracSub = null;
      $laPracSub = array(array('Causa'), array('Observación'));
      while($laFila = $loSql->fetch($RS)) {
         array_push($laPracSub[0], $laFila[0]);
         array_push($laPracSub[1], $laFila[1]->load());
      }
      //Causas Basicas
      $RS = $loSql->omExec("SELECT B.cDescri, A.mObserv 
                            FROM S02DFCR A INNER JOIN S02MFCR B ON A.cCodFcr=B.cCodFcr
                            WHERE cNroInc = '$this->pcNroInc' AND A.cCodFcr LIKE 'FP%'");
      $laFacPer = null;
      $laFacPer = array(array('Causa'), array('Observación'));
      while($laFila = $loSql->fetch($RS)) {
         array_push($laFacPer[0], $laFila[0]);
         array_push($laFacPer[1], $laFila[1]->load());
      }
      $RS = $loSql->omExec("SELECT B.cDescri, A.mObserv 
                            FROM S02DFCR A INNER JOIN S02MFCR B ON A.cCodFcr=B.cCodFcr
                            WHERE cNroInc = '$this->pcNroInc' AND A.cCodFcr LIKE 'FT%'");
      $laFacTrab = null;
      $laFacTrab = array(array('Causa'), array('Observación'));
      while($laFila = $loSql->fetch($RS)) {
         array_push($laFacTrab[0], $laFila[0]);
         array_push($laFacTrab[1], $laFila[1]->load());
      }
      //Control de Gestion
      $RS = $loSql->omExec("SELECT B.cDescri, A.mObserv 
                            FROM S02DFCR A INNER JOIN S02MFCR B ON A.cCodFcr=B.cCodFcr
                            WHERE cNroInc = '$this->pcNroInc' AND A.cCodFcr LIKE 'CG%'");
      $laCG = null;
      $laCG = array(array('Sistemas, estándares, cumplimientos'), array('Observación'));
      while($laFila = $loSql->fetch($RS)) {
         array_push($laCG[0], $laFila[0]);
         array_push($laCG[1], $laFila[1]->load());
      }
      //Comentarios causas Inmediatas y causas Basicas mComSbe y mComFac
      $RS = $loSql->omExec("SELECT mComSbe, mComFac FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      $lmComSbe=$laFila['MCOMSBE']->load();
      $lmComFac=$laFila['MCOMFAC']->load();
      $loSql->omDisconnect();
      // Construir Informe en RTF
      $laArray = array('#*DFECINC*#'=>$ldFecInc, 
                       '#*MDESCRI*#'=>$lmDescri, 
                       '#*CHORINC*#'=>$lcHora, 
                       '#*CDESLUGAR*#'=>$lcDesLug, 
                       '#*CNOMTRAB*#'=>$lcNomTrab, 
                       '#*CEQUIPINVO*#'=>$lcUniInv, 
                       '#*CSUPERVINMEDIAT*#'=>$lcSupInm, 
                       '#*CEVENTOSCRON*#'=>$lmEveCro, 
                       '#*MLESPER*#'=>$lmLesPer, 
                       '#*CREPFIS*#'=>$lcRepFis, 
                       '#*CISLP*#'=>$lcLesPer, 
                       '#*V1*#'=>$lcLesPer, 
                       '#*V3*#'=>$lcDamPro1, 
                       '#*V4*#'=>$lcDamPro2, 
                       '#*V5*#'=>$lcDamPro3, 
                       '#*V6*#'=>$lcDamPro4, 
                       '#*V2*#'=>$lcDamPro, 
                       '#*CPERPRO*#'=>$lcPerPro, 
                       '#*CTIEPER*#'=>$lcTiePer, 
                       '#*V7*#'=>$lcDamAmb1, 
                       '#*V8*#'=>$lcDamAmb2, 
                       '#*V9*#'=>$lcDamAmb3, 
                       '#*V10*#'=>$lcDamAmb, 
                       '#*MDAMAMB*#'=>$lmDamAmb, 
                       '#*CTIEPER*#'=>$lcTiePer, 
                       '#*CACTIVI*#'=>$lcActivi, 
                       '#*MCOMSBE*#'=>$lmComSbe, 
                       '#*MCOMFAC*#'=>$lmComFac
                        );
      $laAArray = array('#*CPERINV*#'=>array(count($laPerInv), count($laPerInv[0]), $laPerInv), 
                        '#*TABLAED*#'=>array(count($laUniInvDa), count($laUniInvDa[0]), $laUniInvDa), 
                        '#*TABLAFC*#'=>array(count($laFacCri), count($laFacCri[0]), $laFacCri), 
                        '#*TABLAAS*#'=>array(count($laActSub), count($laActSub[0]), $laActSub), 
                        '#*TABLAPS*#'=>array(count($laPracSub), count($laPracSub[0]), $laPracSub), 
                        '#*TABLAFP*#'=>array(count($laFacPer), count($laFacPer[0]), $laFacPer), 
                        '#*TABLAFT*#'=>array(count($laFacTrab), count($laFacTrab[0]), $laFacTrab), 
                        '#*TABLACG*#'=>array(count($laCG), count($laCG[0]), $laCG));
      $lo = new CRtf();
      $lo->pcFile = 'ACR_006_';
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
      echo "<a href = '$lo->pcFilRet'>Archivo de Salida</a>";
      return true;
   }
   
   // Inicializa reporte de cabeceras
   public function omInit1() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Tipo
      $i = 0;
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab ='044'");
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipo[]=array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE EVALUACION DEFINIDOS [044]</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   public function omPruebasCabecera() {
      if (empty($this->pcTipo)) {
         $this->pcError = '<DATA><ERROR>TIPO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
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
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Descripcion del tipo
      $RS = $loSql->omExec("SELECT cDescri FROM V_S02TTAB WHERE SUBSTR(cCodigo, 1, 1) = '$this->pcTipo' AND cCodTab = '044'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = "<DATA><ERROR>TIPO DE EVALUACION [$this->pcTipo] NO ESTA DEFINIDO EN TABLA [044]</ERROR></DATA>";
         return false;
      }
      $lcDescri = $laFila[0];
      // Trae datos
      $RS = $loSql->omExec("SELECT * FROM V_S02MEVA_1 WHERE SUBSTR(cNroEva, 1, 1) = '$this->pcTipo' AND
                            dFecEva BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd')
                     ORDER BY dFecEva");
      // Instancia objeto Excel
      $loXls = new CXls();
      $loXls->openXls('SEG5210', $this->pcCodUsu);
      // Manda a Excel la cabecera
      $loXls->sendXls(0, 'C', 3, $lcDescri);
      $loXls->sendXls(0, 'C', 4, $this->pdFecIni);
      $loXls->sendXls(0, 'C', 5, $this->pdFecFin);
      // Manda a Excel la informacion
      $lnRow = 7;
      while ($laFila = $loSql->fetch($RS)) {
         $loXls->sendXls(0, 'B', $lnRow, $laFila['CNROEVA']);
         $loXls->sendXls(0, 'C', $lnRow, $laFila['DFECEVA']);
         $loXls->sendXls(0, 'D', $lnRow, $laFila['CHOREVA']);
         $loXls->sendXls(0, 'E', $lnRow, $laFila['CDESSD1']);
         $loXls->sendXls(0, 'F', $lnRow, $laFila['CDESES1']);
         $loXls->sendXls(0, 'G', $lnRow, $laFila['CDESVAG']);
         $loXls->sendXls(0, 'H', $lnRow, $laFila['CNOMSUP']);
         $loXls->sendXls(0, 'I', $lnRow, $laFila['CDESES2']);
         $loXls->sendXls(0, 'J', $lnRow, $laFila['CKILOMF']);
         $loXls->sendXls(0, 'K', $lnRow, $laFila['CHORSER']);
         $loXls->sendXls(0, 'L', $lnRow, $laFila['CNOMSP2']);
         $loXls->sendXls(0, 'M', $lnRow, $laFila['CDESCON']);
         $loXls->sendXls(0, 'N', $lnRow, $laFila['CDESCLI']);
         $loXls->sendXls(0, 'O', $lnRow, $laFila['CVELAUT']);
         $loXls->sendXls(0, 'P', $lnRow, $laFila['CVELREG']);
         $loXls->sendXls(0, 'Q', $lnRow, $laFila['CRESTRI']);
         $loXls->sendXls(0, 'R', $lnRow, $laFila['CDESUN1']);
         $loXls->sendXls(0, 'S', $lnRow, $laFila['CDESUN2']);
         $loXls->sendXls(0, 'T', $lnRow, $laFila['CDESUN3']);
         $loXls->sendXls(0, 'U', $lnRow, $laFila['CTREN']);
         $loXls->sendXls(0, 'V', $lnRow, $laFila['CCOMPOS']);
         $loXls->sendXls(0, 'W', $lnRow, $laFila['CDESPRY']);
         $loXls->sendXls(0, 'X', $lnRow, $laFila['CDESEMP']);
         $loXls->sendXls(0, 'Y', $lnRow, $laFila['CDESRES']);
         $loXls->sendXls(0, 'Z', $lnRow, $laFila['CKILOME']);
         $loXls->sendXls(0, 'AA', $lnRow, $laFila['MOBSERV']->load());
         $lnRow++;
      }
      $loXls->closeXls();
      $loSql->omDisconnect();
      if ($lnRow == 7) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
      }
      $this->pcFilRep = $loXls->pcFilXls;
      return true;
   }
   
   // Inicializa reporte de evaluaciones por personal
   public function omInit2() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Tipo
      $i = 0;
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab ='044'");
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipo[]=array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE EVALUACION DEFINIDOS [044]</ERROR></DATA>';
         return false;
      }
      // Carga personal
      $i = 0;
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE SUBSTR(cCodPer, 1, 1) != '9' ORDER BY cNombre");
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL PARA CONSULTAR</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   public function omPruebasPersonal() {
      if (empty($this->pcTipo)) {
         $this->pcError = '<DATA><ERROR>TIPO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL INVALIDO</ERROR></DATA>';
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
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Descripcion del tipo
      $RS = $loSql->omExec("SELECT cDescri FROM V_S02TTAB WHERE SUBSTR(cCodigo, 1, 1) = '$this->pcTipo' AND cCodTab = '044'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = "<DATA><ERROR>TIPO DE EVALUACION [$this->pcTipo] NO ESTA DEFINIDO EN TABLA [044]</ERROR></DATA>";
         return false;
      }
      $lcDescri = $laFila[0];
      // Nombre del personal
      $RS = $loSql->omExec("SELECT cNombre FROM S02MPER WHERE cCodPer = '$this->pcCodPer'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = "<DATA><ERROR>CODIGO DE PERSONAL NO EXISTE</ERROR></DATA>";
         return false;
      }
      $lcNombre = $laFila[0];
      $loXls = new CXls();
     if ($this->pcTipo == 'E') {
        $lcSql =  "SELECT * FROM V_S02MEVA_2 where substr(CNroEva,1,1)='$this->pcTipo' AND 
                   dFecEva BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd')
                 AND cCodPer = '$this->pcCodPer'
                    ORDER BY dFecEva";
      $loXls->openXls('SEG5220E', $this->pcCodUsu);
      
     } elseif ($this->pcTipo == 'C') {
                $lcSql =  "SELECT * FROM V_S02MEVA_2 where substr(CNroEva,1,1)='$this->pcTipo' AND 
                        dFecEva BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd')
                 AND cCodPer = '$this->pcCodPer'
                    ORDER BY dFecEva";     
             $loXls->openXls('SEG5220C', $this->pcCodUsu);
      } elseif ($this->pcTipo == 'R') {
                $lcSql =  "SELECT * FROM V_S02MEVA_2 where substr(CNroEva,1,1)='$this->pcTipo' AND 
                        dFecEva BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd')
                 AND cCodPer = '$this->pcCodPer'
                    ORDER BY dFecEva";     
             $loXls->openXls('SEG5220R', $this->pcCodUsu);
      } elseif ($this->pcTipo == 'T') {
                $lcSql =  "SELECT * FROM V_S02MEVA_2 where substr(CNroEva,1,1)='$this->pcTipo' AND 
                        dFecEva BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd')
                 AND cCodPer = '$this->pcCodPer'
                    ORDER BY dFecEva";     
             $loXls->openXls('SEG5220C', $this->pcCodUsu);
      }
      $RS = $loSql->omExec($lcSql);
      // Manda a Excel la cabecera
      $loXls->sendXls(0, 'C', 3, $lcNombre);
      $loXls->sendXls(0, 'C', 4, $lcDescri);
      $loXls->sendXls(0, 'C', 5, $this->pdFecIni);
      $loXls->sendXls(0, 'C', 6, $this->pdFecFin);
      // Manda a Excel la informacion
      $lnRow = 8;
      while ($laFila = $loSql->fetch($RS)) {
         $loXls->sendXls(0, 'B', $lnRow, $laFila['CDESPUE']);
         $loXls->sendXls(0, 'C', $lnRow, $laFila['CNROEVA']);
         $loXls->sendXls(0, 'D', $lnRow, $laFila['DFECEVA']);
         $loXls->sendXls(0, 'E', $lnRow, $laFila['CHOREVA']);
         $loXls->sendXls(0, 'F', $lnRow, $laFila['CDESSD1']);
         $loXls->sendXls(0, 'G', $lnRow, $laFila['CDESES1']);
         $loXls->sendXls(0, 'H', $lnRow, $laFila['CDESVAG']);
         $loXls->sendXls(0, 'I', $lnRow, $laFila['CNOMSUP']);
         $loXls->sendXls(0, 'J', $lnRow, $laFila['CDESES2']);
         $loXls->sendXls(0, 'K', $lnRow, $laFila['CKILOMF']);
         $loXls->sendXls(0, 'L', $lnRow, $laFila['CHORSER']);
         $loXls->sendXls(0, 'M', $lnRow, $laFila['CNOMSP2']);
         $loXls->sendXls(0, 'N', $lnRow, $laFila['CDESCON']);
         $loXls->sendXls(0, 'O', $lnRow, $laFila['CDESCLI']);
         $loXls->sendXls(0, 'P', $lnRow, $laFila['CVELAUT']);
         $loXls->sendXls(0, 'Q', $lnRow, $laFila['CVELREG']);
         $loXls->sendXls(0, 'R', $lnRow, $laFila['CRESTRI']);
         $loXls->sendXls(0, 'S', $lnRow, $laFila['CDESUN1']);
         $loXls->sendXls(0, 'T', $lnRow, $laFila['CDESUN2']);
         $loXls->sendXls(0, 'U', $lnRow, $laFila['CDESUN3']);
         $loXls->sendXls(0, 'V', $lnRow, $laFila['CTREN']);
         $loXls->sendXls(0, 'W', $lnRow, $laFila['CCOMPOS']);
         $loXls->sendXls(0, 'X', $lnRow, $laFila['CDESPRY']);
         $loXls->sendXls(0, 'Y', $lnRow, $laFila['CDESEMP']);
         $loXls->sendXls(0, 'Z', $lnRow, $laFila['CKILOME']);
         $loXls->sendXls(0, 'AA', $lnRow, $laFila['MOBSERV']->load());
       if ($this->pcTipo == 'E') {
          $loXls->sendXls(0, 'AB', $lnRow, $laFila['CCODNOR']);
          $loXls->sendXls(0, 'AC', $lnRow, $laFila['CDESNOR']);
          $loXls->sendXls(0, 'AD', $lnRow, $laFila['CRESULT'] == 'B'?'BUENO':'MALO');
       } elseif ($this->pcTipo == 'C') {
          $loXls->sendXls(0, 'AB', $lnRow, $laFila['CEVACON']);
          $loXls->sendXls(0, 'AC', $lnRow, $laFila['CDESEVA']);
          $loXls->sendXls(0, 'AD', $lnRow, $laFila['CRESULT'] == 'S'?'SI':'NO');
         $loXls->sendXls(0, 'AE', $lnRow, $laFila['CDESRES']);
       } elseif ($this->pcTipo == 'T') {
          $loXls->sendXls(0, 'AB', $lnRow, $laFila['CEVACON']);
          $loXls->sendXls(0, 'AC', $lnRow, $laFila['CDESEVA']);
          $loXls->sendXls(0, 'AD', $lnRow, $laFila['CRESULT'] == 'S'?'SI':'NO');
          $loXls->sendXls(0, 'AE', $lnRow, $laFila['CDESRES']);
       } elseif ($this->pcTipo == 'R') {
          $loXls->sendXls(0, 'AB', $lnRow, $laFila['CRESGEN'] == 'B'?'BUENO':'MALO');
       }
         $lnRow++;
      }
      $loXls->closeXls();
      $loSql->omDisconnect();
      if ($lnRow == 8) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA MOSTRAR</ERROR></DATA>';
         return false;
      }
      $this->pcFilRep = $loXls->pcFilXls;
      return true;
   }
}
?>