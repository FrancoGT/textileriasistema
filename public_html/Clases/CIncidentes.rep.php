<?php
require_once 'Clases/CBase.php';

// Reportes de incidentes
class CIncidentesRep extends CBase {
   public $pcNroInc, $pdFecInc, $pmDescri, $pcFilRet;
   protected $lnError;

   public function __construct() {
      parent::__construct();
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
      // Datos de cabecera del incidente
      $RS = $loSql->omExec("SELECT TO_CHAR(dFecInc, 'yyyy-mm-dd') AS dFecInc, cHorInc, cLugar, mDescri, mLesPer, cRepFis, cLesPer, cDamPro, nCosto FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
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
	  $lnCosto = $laFila['NCOSTO'].' US$';
      $lcLesPer = ($laFila['CLESPER']) == 'S'? 'X': ' ';
      $lcDamPro1 = substr($laFila['CDAMPRO'], 0, 1) == 'N' ? ' ' : 'X';
      $lcDamPro2 = substr($laFila['CDAMPRO'], 1, 1) == 'N' ? ' ' : 'X';
      $lcDamPro3 = substr($laFila['CDAMPRO'], 2, 1) == 'N' ? ' ' : 'X';
      $lcDamPro4 = substr($laFila['CDAMPRO'], 3, 1) == 'N' ? ' ' : 'X';
      $lcDamPro = ($laFila['CDAMPRO'] == 'NNNN') ? ' ' : 'X';
      // Lugares
      $RS = $loSql->omExec("SELECT cDescri FROM V_S02TTAB WHERE cCodTab = '008' AND cCodigo = '$lcLugar'");
      $laFila = $loSql->fetch($RS);
      $lcDesLug = (!empty($laFila[0])) ? $laFila[0] : '*** NO DEFINIDO ***';
      // Tipo de incidente (con/sin investigacion)
      $lcTipo = 'C';
      $RS = $loSql->omExec("SELECT cNroInc FROM S02DINC WHERE cNroInc = '$this->pcNroInc' AND cTipo = '$lcTipo'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $lcTipo = 'I';
      }
     // Fecha planificada de cierre
      $RS = $loSql->omExec("SELECT TO_CHAR(MAX(dFecha1),'yyyy-mm-dd') FROM S02DINC WHERE cNroInc = '$this->pcNroInc' AND cTipo = '$lcTipo'");
      $laFila = $loSql->fetch($RS);
	  $ldFecMay = fx2Date($laFila[0]);
//	  / Fecha real de cierre
      $RS = $loSql->omExec("SELECT COUNT(*) FROM S02DINC WHERE cNroInc = '$this->pcNroInc' AND cTipo = '$lcTipo' AND dFecha2 IS NULL");
      $laFila = $loSql->fetch($RS);
	  if ($laFila[0] == 0) {
         $RS = $loSql->omExec("SELECT TO_CHAR(MAX(dFecha2),'yyyy-mm-dd') FROM S02DINC WHERE cNroInc = '$this->pcNroInc' AND cTipo = '$lcTipo'");
         $laFila = $loSql->fetch($RS);
         if (!empty($laFila[0])) {
            if ($ldFecMay < $laFila[0]) {
               $ldFecMay = fx2Date($laFila[0]);
            }
         }
      }
      $lcPlazo = $ldFecMay - fx2Date($ldFecInc);
	  //VER
	  $lcPlazo = floor($lcPlazo / 86400);
	  $lcPlazo = (($lcPlazo<0)||($lcPlazo>360)) ? '-- dias':($lcPlazo.' dias');
      // Personal involucrado
      $RS = $loSql->omExec("SELECT cNombre, cDesCar, cCodPer, cNroDni FROM V_S02DPIN WHERE cNroInc = '$this->pcNroInc' ORDER BY cCodPer ASC");
      $lcTexto1 = $lcTexto2 = '';
	  $llFirst = true;
      while ($laFila = $loSql->fetch($RS)) {
         if (!$llFirst) {
            $lcTexto1 .= '\line ---------------------------------------------------------------------------------------------------------- \line ';
            $lcTexto2 .= '\line ';
         }
         $llFirst = false;
         $lcTexto2 .= '- '.$laFila[0] . '-' . $laFila[1];
         $lcTexto1 .= 'Nombre: '. $laFila[0] . '\line ';
         // Historico de incidentes de cada personal involucrado
         if ($laFila[2] != '999999'){
            $RS1 = $loSql->omExec("SELECT A.cNroInc, TO_CHAR(B.dFecInc,'yyyy-mm-dd'), B.mDescri, C.cDescri AS cDesTip 
                                   FROM S02DPIN A
                                   INNER JOIN S02MINC B ON A.cNroInc=B.cNroInc
                                   LEFT OUTER JOIN V_S02TTAB C ON B.cTipInc = C.cCodigo And C.cCodTab = '032'
                                   WHERE A.cCodPer = '$laFila[2]' AND A.cNroInc! = '$this->pcNroInc' Order BY B.dFecInc DESC");
         } else {
            $RS1 = $loSql->omExec("SELECT A.cNroInc, TO_CHAR(B.dFecInc,'yyyy-mm-dd'), B.mDescri,C.cDescri AS cDesTip 
                                   FROM S02DPIN A
                                   INNER JOIN S02MINC B ON A.cNroInc = B.cNroInc
                                   LEFT OUTER JOIN V_S02TTAB C ON B.cTipInc = C.cCodigo And C.cCodTab = '032'
                                   WHERE A.cNroDni = '$laFila[3]' AND A.cNroInc != '$this->pcNroInc' Order BY B.dFecInc DESC");
         }
         $i = 0;
         while($laFila1 = $loSql->fetch($RS1)) {
            $lcTexto1 .= ('\line '.'Nro: '.$laFila1[0].' Fecha: '.$laFila1[1].' Tipo: '.$laFila1[3]);
            $i++;
            if ($i == 3)
               break;
         }
      }
      // Unidades involucradas
      $RS = $loSql->omExec("SELECT cDescri,cCodUni FROM (SELECT A.cNroInc, A.cCodUni, B.cDescri FROM S02DUIN A
                            INNER JOIN S02MUNI B ON A.cCodUni = B.cCodUni WHERE A.cCodUni != '99999'
                            UNION 
                            SELECT cNroInc, cCodUni, cDescri FROM S02DUIN WHERE cCodUni = '99999') 
                            WHERE cNroInc = '$this->pcNroInc'");
      $lcUniInv = '';
      $lcUniInv1 = '';
      $llFirst = true;
      while ($laFila = $loSql->fetch($RS)) {
         $lcUniInv .= (!$llFirst) ? '\line ' : '';
         $lcUniInv1 .= (!$llFirst) ? '\line ' : '';
         $llFirst = false;
         $lcUniInv .= trim($laFila[0]);
         $lcUniInv1 .= trim($laFila[1]);
      }
     // Unidades involucradas y daño
      $RS = $loSql->omExec("SELECT * FROM (SELECT B.cDescri, A.cDamage FROM S02DUIN A INNER JOIN S02MUNI B ON A.cCodUni = B.cCodUni 
                            WHERE A.cCodUni != '99999' AND cNroInc = '$this->pcNroInc'
                            UNION 
                            SELECT cDescri, cDamage FROM S02DUIN WHERE cCodUni = '99999' AND cNroInc = '$this->pcNroInc') ORDER BY cDescri");
      $laUniInvDa = null;
      $laUniInvDa = array(array('\b Vehículos / Equipos / Propiedad'), array('\b Daños'));
      while($laFila = $loSql->fetch($RS)){
         array_push($laUniInvDa[0], $laFila[0]);
         array_push($laUniInvDa[1], $laFila[1]);
      }
     //Supervisores Inmediatos
      $RS = $loSql->omExec("SELECT B.cNombre FROM S02DPIN A INNER JOIN S02MPER B ON A.cSuperv = B.cCodper 
                            WHERE cNroInc = '$this->pcNroInc' ORDER BY A.cCodPer ASC");
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
      if (empty($laFila[0])) {
         $lmEveCro = '';
      } else $lmEveCro = nl2br($laFila[0]->load());
	   $lmEveCro=str_replace('<br />', '\line ', $lmEveCro);
	// Datos del personal involucrado
      $RS = $loSql->omExec("SELECT * FROM (SELECT cNroInc, cNombre, 'N/C' AS cDesDis, cNroDni, cActivi, cUbicac, 'N/C' AS cSuperv, cEntren,cCodPer 
                            FROM S02DPIN WHERE cCodPer = '999999'
                            UNION
                            SELECT A.cNroInc, B.cNombre, D.cDescri AS cDesDis, B.cNroDni, A.cActivi, A.cUbicac, 
                            C.cNombre AS Superv, A.cEntren,B.cCodPer FROM S02DPIN A
                            INNER JOIN S02MPER B ON A.cCodPer = B.cCodPer
                            INNER JOIN S02MPER C ON A.cSuperv = C.cCodPer
                            LEFT JOIN V_S02TTAB D ON A.cCarDis = D.cCodigo AND D.cCodTab = '045')
                            WHERE cNroInc = '$this->pcNroInc' ORDER BY cCodPer ASC");
      $laPerInv = null;
	  $laPerInv = array(array('\b DATOS'));
      while ($laFila = $loSql->fetch($RS)) {
         $RS1= $loSql->omExec("SELECT cCodCur, TO_CHAR(dFecha, 'yyyy-mm-dd') AS DFECHA,cDescri  FROM V_S02DCAP WHERE cCodPer = '$laFila[8]' order by dFecha DESC, cCodCur");
         $laFilaw = $loSql->fetch($RS1);
         $RS2= $loSql->omExec("SELECT cCodNor, cDesNor, TO_CHAR(dFecha, 'yyyy-mm-dd') AS DFECHA FROM V_S02DCAP WHERE cCodPer = '$laFila[8]' AND cCodCur = '$laFilaw[0]'");
         while ($laFilax = $loSql->fetch($RS2)) {
            $lcTemp1 .= ' \line Codigo: '.$laFilax[0].$laFilax[1];
         }
         $lcTemp = ' \fs20'.trim($laFila[1]).'\line - Cargo: '.trim($laFila[2]).'\line - Nro. DNI: '.trim($laFila[3]).'\line - Entrenamiento: '.trim(($laFila[7] == 'N')? 'NO': 'SI').'\line - Actividad: '.trim($laFila[4]).'\line - Ubicacion: '.trim($laFila[5]).'\line - Supervisor Inmediato: '.trim($laFila[6]).'\line - Ultima Capacitación: '.trim($laFilaw[2]).' - '.trim($laFilaw[1]).'\line - Normas:'.$lcTemp1;
         $lcTemp1 = null;
         array_push($laPerInv[0],$lcTemp);
      }
     //Medio Abmiente SELECT cperpro,cTieper,cDamAmb FROM S02MINC
      $RS = $loSql->omExec("SELECT mDamAmb, cPerPro, cTieper, cDamAmb FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      $lcPerPro=($laFila['CPERPRO'] == 'N')? ' ': 'X';
      $lcTiePer=$laFila['CTIEPER'];
      $lcDamAmb1 = substr($laFila['CDAMAMB'], 1, 1) == 'N'? ' ': 'X';
      $lcDamAmb2 = substr($laFila['CDAMAMB'], 2, 1) == 'N'? ' ': 'X';
      $lcDamAmb3 = substr($laFila['CDAMAMB'], 3, 1) == 'N'? ' ': 'X';
      $lcDamAmb = substr($laFila['CDAMAMB'],1,3) == 'NNN'? ' ': 'X';
	  $lmDamAmb=$laFila['MDAMAMB']->load();
      $lcActivi=$laFila['CACTIVI'];
      //Factor Critico y Conlusiones
      $RS = $loSql->omExec("SELECT mFacCri, mConclu FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      $lmFacCri = $laFila['MFACCRI']->load();
      $lmConclu = $laFila['MCONCLU']->load();
      //Causas Inmediatas 1
      $RS = $loSql->omExec("SELECT B.cDescri,A.mObserv 
                            FROM S02DFCR A INNER JOIN S02MFCR B ON A.cCodFcr = B.cCodFcr
                            WHERE cNroInc = '$this->pcNroInc' AND A.cCodFcr LIKE 'PS%'");
      $lcActPrac = '';
      $lcComAP = '';
      $llFirst = true;
      while($laFila = $loSql->fetch($RS)){
         $lcActPrac .= (!$llFirst)? '\line ':'';
         $lcComAP .= (!$llFirst)? '\line ':'';
         $llFirst = false;
         $lcActPrac.='- '.$laFila[0];
         $lcComAP.='- '.$laFila[1]->load();
      }
      // Condicion subestandar
      $RS = $loSql->omExec("SELECT B.cDescri, A.mObserv 
                            FROM S02DFCR A INNER JOIN S02MFCR B ON A.cCodFcr = B.cCodFcr
                            WHERE cNroInc = '$this->pcNroInc' AND A.cCodFcr LIKE 'CS%'");
      $lcCondi = '';
      $lcCondi1 = '';
      $llFirst = true;
      while($laFila = $loSql->fetch($RS)){
         $lcCondi .= (!$llFirst)? '\line ':'';
         $lcCondi1 .= (!$llFirst)? '\line ':'';
         $llFirst = false;
         $lcCondi .='- '.$laFila[0];
         $lcCondi1 .='- '.$laFila[1]->load();
      }
      // Unidades y funciones
      $RS = $loSql->omExec("SELECT cCodUni, cDescri, cFuncio FROM V_S02DUIN
                            WHERE cNroInc = '$this->pcNroInc' ORDER BY cDescri");
      $lcCodUnd = $lcDescri = $lcFuncio = '';
      $llFirst = true;
      while ($laFila = $loSql->fetch($RS)){
         $lcCodUni .= (!$llFirst) ? '\line ' : '';
         $lcDescri .= (!$llFirst) ? '\line ' : '';
         $lcFuncio .= (!$llFirst) ? '\line ' : '';
         $llFirst = false;
         $lcCodUni .= '- '.$laFila[0];
         $lcDescri .= '- '.$laFila[1];
         $lcFuncio .= '- '.$laFila[2];
      }
      //Causas Basicas1
      $RS = $loSql->omExec("SELECT B.cDescri, A.mObserv 
                            FROM S02DFCR A INNER JOIN S02MFCR B ON A.cCodFcr = B.cCodFcr
                            WHERE cNroInc = '$this->pcNroInc' AND A.cCodFcr LIKE 'FP%'");
      $lcFacPer = '';
      $lcObsPer = '';
      $llFirst = true;
      while($laFila = $loSql->fetch($RS)){
         $lcFacPer .= (!$llFirst)? '\line ':'';
         $lcObsPer .= (!$llFirst)? '\line ':'';
         $llFirst = false;
         $lcFacPer .= '- '.$laFila[0];
         $lcObsPer .= '- '.$laFila[1]->load();
      }
      $RS = $loSql->omExec("SELECT B.cDescri, A.mObserv 
                            FROM S02DFCR A INNER JOIN S02MFCR B ON A.cCodFcr = B.cCodFcr
                            WHERE cNroInc = '$this->pcNroInc' AND A.cCodFcr LIKE 'FT%'");
      $lcFacTra = '';
      $lcObsTra = '';
      $llFirst = true;
      while ($laFila = $loSql->fetch($RS)){
         $lcFacTra .= (!$llFirst)? '\line ':'';
         $lcObsTra .= (!$llFirst)? '\line ':'';
         $llFirst = false;
         $lcFacTra .= '- '.$laFila[0];
         $lcObsTra .= '- '.$laFila[1]->load();
      }
      //Control de Gestion1
      $RS = $loSql->omExec("SELECT B.cDescri, A.mObserv 
                            FROM S02DFCR A INNER JOIN S02MFCR B ON A.cCodFcr = B.cCodFcr
                            WHERE cNroInc = '$this->pcNroInc' AND A.cCodFcr LIKE 'CG%'");
      $lcConGes = $lcObsGes = '';
      $llFirst = true;
      while($laFila = $loSql->fetch($RS)){
         $lcConGes .= (!$llFirst)? '\line ':'';
         $lcObsGes .= (!$llFirst)? '\line ':'';
         $llFirst = false;
         $lcConGes .='- '.$laFila[0];
         $lcObsGes .='- '.$laFila[1]->load();
      }
      //Comentarios causas Inmediatas y causas Basicas mComSbe y mComFac y mComCge
      $RS = $loSql->omExec("SELECT mComSbe,mComFac,mComCge FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      $lmComSbe=$laFila['MCOMSBE']->load();
      $lmComFac=$laFila['MCOMFAC']->load();
      $lmComCge=$laFila['MCOMCGE']->load();
      //Planes de Accion
      $RS = $loSql->omExec("SELECT B.cDescri AS cAccTom, A.cDescri, A.cCodPer, TO_CHAR(A.dFecha1,'yyyy-mm-dd') AS DFECHA1, TO_CHAR(A.dFecha2,'yyyy-mm-dd')AS DFECHA2, C.cDescri AS cCondic,A.mComent
                            FROM S02DINC A
                            LEFT OUTER JOIN V_S02TTAB B ON A.cAccTom=B.cCodigo AND B.cCodTab='033'
                            LEFT OUTER JOIN V_S02TTAB C ON A.cCondic=C.cCodigo AND C.cCodTab='034'
                            WHERE cNroInc='$this->pcNroInc' AND A.cTipo='C' ORDER BY dFecha1");
      $laPlaAcc = null;
      $laPlaAcc = array(array('\b Tipo de Acción'), array('\b Descripción'),array('\b Responsable'),array('\b Fecha Planificada'),array('\b F. Cierre'));
      while ($laFila = $loSql->fetch($RS)) {
         $lcHisto = $laFila[6]->load();
         $lcCrono = '';
         $lcFechas = '';
         $llFirst0 = true;
         while($lcHisto!=''){
            $lcCrono .= (!$llFirst0)? '\line ':'';
            $lcFechas = substr($lcHisto, 0, 27);
            $lcHisto = substr($lcHisto, 27, iconv_strlen($lcHisto));
            if ((substr($lcHisto, 0, strpos($lcHisto, 'FE')))==''){
			   $lcContent = substr($lcHisto, 0, iconv_strlen($lcHisto));
               $lcHisto = '';
               //$llFirst0 = false;
               $lcCrono .= $lcFechas.'\line '.$lcContent;
               break;
            }
            $lcContent = substr($lcHisto, 0, strpos($lcHisto, 'FE'));
            $lcHisto = substr($lcHisto, strpos($lcHisto, 'FE'), iconv_strlen($lcHisto));
            $llFirst0 = false;
            $lcCrono .= $lcFechas.'\line '.$lcContent;
         } 
		 array_push($laPlaAcc[0],'\fs18\\qc '.$laFila[0]);
         array_push($laPlaAcc[1],('\fs18'.$laFila[1].'\fs18\line '.$lcCrono));
         //personal y cargo
         $RS1 = $loSql->omExec("SELECT B.cNombre ,A.cDescri AS cCarDis FROM TABLE (F_S02MPER_1('$laFila[2]')) B
                                LEFT OUTER JOIN V_S02TTAB A ON B.cCarDis=A.cCodigo AND A.cCodTab='045'");
         $laFila1 = $loSql->fetch($RS1);
         $Temp = $laFila1[0].'\fs18\line '.$laFila1[1];       
         array_push($laPlaAcc[2],'\fs18'.$Temp);
         array_push($laPlaAcc[3],'\fs18\\qc '.$laFila[3]);
         array_push($laPlaAcc[4],'\fs18\\qc '.$laFila[4]);
      }
      $RS1 = $loSql->omExec("SELECT cCodPer FROM S02MINC WHERE cnroinc ='$this->pcNroInc'");
      $i = $loSql->fetch($RS1);
      $RS = $loSql->omExec("SELECT cNombre, cDesDis FROM TABLE (F_S02MPER_1('$i[0]'))");
      $laFila = $loSql->fetch($RS);
      $lcDesDisr = $laFila[1];
      $lcNombrer = $laFila[0];
      //Equipo de Investigacion
      $RS = $loSql->omExec("SELECT cCodPer,B.cDescri AS cEstado, cLider FROM S02DEIV A
                            LEFT OUTER JOIN V_S02TTAB B ON A.cEstado=B.cCodigo AND B.cCodTab='001'
                            WHERE cNroInc='$this->pcNroInc'");
      $laTabla2 = null;
      $laTabla2 = array(array('\b Nombre'), array('\b Cargo'),array('\b Area'),array('\b Estado'),array('\b Lider'),array('\b Firma'));
      while($laFila = $loSql->fetch($RS)){
         $RS1 = $loSql->omExec("SELECT cNombre, cDesCar, cDesAre FROM TABLE (F_S02MPER_1('$laFila[0]'))");
         $laFila1 = $loSql->fetch($RS1);
         array_push($laTabla2[0], '\fs18'.$laFila1[0]);
         array_push($laTabla2[1], '\fs18\\qc '.$laFila1[1]);
         array_push($laTabla2[2], '\fs18\\qc '.$laFila1[2]);
         array_push($laTabla2[3], '\fs18\\qc '.$laFila[1]);
         array_push($laTabla2[4], '\fs18\\qc '.(substr($laFila[2],0,1) == 'S' ? 'LIDER' : ' '));
      }
      //Eficacia y comentario 
      $RS = $loSql->omExec("SELECT cEficaz, mComent FROM S02MINC WHERE cNroInc='$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      $lcEficaz = ($laFila[0] == 'S') ? 'SI' : 'NO';
      $lmComent = $laFila[1]->load();
      $loSql->omDisconnect();
	  $lcNotDam='X';
	  if ($lcLesPer == 'X' or $lcDamPro == 'X'){
	     $lcNotDam=' ';
	  }
      // Construir Informe en RTF
      $laArray = array('#*DFECINC*#'=>$ldFecInc, '#*MDESCRI*#'=>$lmDescri,
                       '#*CHORINC*#'=>$lcHora,   '#*CDESLUGAR*#'=>$lcDesLug,
                       '#*CNOMTRAB*#'=>$lcTexto2, '#*CEQUIPINVO*#'=>$lcUniInv,
                       '#*CSUPERVINMEDIAT*#'=>$lcSupInm, '#*CEVENTOSCRON*#'=>$lmEveCro,
                       '#*MLESPER*#'=>$lmLesPer, '#*CREPFIS*#'=>$lcRepFis,
                       '#*CISLP*#'=>$lcNotDam, '#*V1*#'=>$lcLesPer,
                       '#*V3*#'=>$lcDamPro1, '#*V4*#'=>$lcDamPro2,
                       '#*V5*#'=>$lcDamPro3, '#*V6*#'=>$lcDamPro4,
                       '#*V2*#'=>$lcDamPro, '#*CPERPRO*#'=>$lcPerPro,
                       '#*CTIEPER*#'=>$lcTiePer, '#*V7*#'=>$lcDamAmb1,
                       '#*V8*#'=>$lcDamAmb2, '#*V9*#'=>$lcDamAmb3,
                       '#*V10*#'=>$lcDamAmb, '#*MDAMAMB*#'=>$lmDamAmb,
                       '#*CTIEPER*#'=>$lcTiePer, '#*CACTIVI*#'=>$lcActivi,
                       '#*MCOMCI*#'=>$lmComSbe, '#*MCOMCBR*#'=>$lmComFac,
                       '#*MCOMCG*#'=>$lmComCge, '#*MFACCRI*#'=>$lmFacCri,
                       '#*MCONCLU*#'=>$lmConclu, '#*CNOMBRER*#'=>$lcNombrer,
                       '#*CDESDISR*#'=>$lcDesDisr, '#*CPLAZO*#'=>$lcPlazo,
                       '#*CEFICACIA*#'=>$lcEficaz, '#*COBSERVACION*#'=>$lmComent,
                       '#*CACTPRAC*#'=>$lcActPrac, '#*CCOMAP*#'=>$lcComAP,
                       '#*CCONDI2*#'=>$lcCondi, '#*CCONDI1*#'=>$lcCondi1,
                       '#*CFPER*#'=>$lcFacPer, '#*CCOMFPER*#'=>$lcObsPer,
                       '#*CFTRAB*#'=>$lcFacTra, '#*CCOMFTRAB*#'=>$lcObsTra,
                       '#*CCG*#'=>$lcConGes, '#*CCOMCG*#'=>$lcObsGes,
                       '#*CDES*#'=>$lcDescri, '#*CID*#'=>$lcCodUni,
                       '#*CFUNCIO*#'=>$lcFuncio, '#*ClcHistInc*#'=>$lcHisInc,
                       '#*CHISTORIAL*#'=>$lcTexto1, '#*NCOSTO*#'=>$lnCosto
                        );
      $laDim = array(1600,3700,2900,1200,1200);
	  $laDim1 = array(3600,1500,1600,900,900);
      $laAArray = array('#*CPERINV*#'=>array(count($laPerInv), count($laPerInv[0]), $laPerInv,''),
                        '#*TABLAED*#'=>array(count($laUniInvDa), count($laUniInvDa[0]), $laUniInvDa,''),
                        '#*TABLA1*#'=>array(count($laPlaAcc), count($laPlaAcc[0]), $laPlaAcc,$laDim),
                        '#*TABLA2*#'=>array(count($laTabla2), count($laTabla2[0]), $laTabla2,$laDim1)
                        );
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
   
   // Reporte: informe de investigacion de incidentes111
   public function omInformeInvestigacion1() {
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
      $RS = $loSql->omExec("SELECT B.cDescri AS cLugar, TO_CHAR(A.dFecInc,'yyyy-mm-dd') AS DFECHA, C.cDescri AS cDesEmp,D.cDescri AS cDesCla,
                            E.cDescri AS cDesTip, A.cNroInc, A.mDescri,F.cNombre AS cNomPe1,G.cNombre AS cNomPe2,
                            H.cDescri AS cDesArea1, I.cDescri AS cDesArea2,A.mSugere,J.cDescri AS cConsec,
                            K.cDescri AS cPotenc, A.mAnaPre, A.cReqInv,L.cNombre AS cNomRes,M.cDescri AS cEstaci, 
                            N.cDescri AS cDesDiv, O.cDescri AS cDesPry, A.cKilome
                            FROM S02MINC A
                            LEFT OUTER JOIN V_S02TTAB B ON A.cLugar=B.cCodigo AND B.cCodTab='008'
                            INNER JOIN S02MEMP C ON A.cCodEmp=C.cCodEmp
                            LEFT OUTER JOIN V_S02TTAB D ON A.cClaInc=D.cCodigo AND D.cCodTab='031'
                            LEFT OUTER JOIN V_S02TTAB E ON A.cTipInc=E.cCodigo AND E.cCodTab='032'
                            INNER JOIN S02MPER F ON A.cCodPe1=F.cCodPer
                            INNER JOIN S02MPER G ON A.cCodPe2=G.cCodPer
                            LEFT OUTER JOIN V_S02TTAB H ON A.cArea1=H.cCodigo AND H.cCodTab='030'
                            LEFT OUTER JOIN V_S02TTAB I ON A.cArea2=I.cCodigo AND I.cCodTab='030'
                            LEFT OUTER JOIN V_S02TTAB J ON A.cConsec=J.cCodigo AND J.cCodTab='011'
                            LEFT OUTER JOIN V_S02TTAB K ON A.cPotenc=K.cCodigo AND K.cCodTab='011'
                            INNER JOIN S02MPER L ON A.cCodPer=L.cCodPer
                            LEFT OUTER JOIN V_S02TTAB M ON A.cEstaci=M.cCodigo AND M.cCodTab='021'
                            LEFT OUTER JOIN V_S02TTAB N ON A.cSubDiv=N.cCodigo AND N.cCodTab='020'
                            INNER JOIN S02MPRY O ON A.cCodPry=O.cCodPry
                            WHERE cNroInc='$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO EXISTE</ERROR></DATA>';
         return false;
      }
     $lcLugar=$laFila['CLUGAR'].' / '.(substr($laFila['CESTACI'],0,1)=='*'?'NC':$laFila['CESTACI']).' / '.$laFila['CKILOME'];
     $ldFecha=$laFila['DFECHA'];
     $lcDesEmp=$laFila['CDESEMP'];
     $lcDesCla=$laFila['CDESCLA'];
     $lcDesTip=$laFila['CDESTIP'];
     $lcNroInc=$this->pcNroInc;
     $lmDescri=$laFila['MDESCRI']->load();
     $lcNomPe1=$laFila['CNOMPE1'];
     $lcNomPe2=$laFila['CNOMPE2'];
     $lcDesArea1=$laFila['CDESAREA1'];
     $lcDesArea2=$laFila['CDESAREA2'];
     $lmSugere=$laFila['MSUGERE']->load();
     $lcConsec=$laFila['CCONSEC'];
     $lcPotenc=$laFila['CPOTENC'];
     $lmAnaPre=$laFila['MANAPRE']->load();
     $lcReqInv=substr($laFila['CREQINV'],0,1)=='S'?'SI':'NO';
     $lcNomRes=$laFila['CNOMRES'];
	 $lcDesDiv=$laFila['CDESDIV'];
	 $lcDesPry=$laFila['CDESPRY'];
     //Planes de Accion
      $RS = $loSql->omExec("SELECT B.cDescri AS cAccTom, A.cDescri, A.cCodPer, 
                            TO_CHAR(A.dFecha1,'yyyy-mm-dd') AS DFECHA1, 
                            TO_CHAR(A.dFecha2,'yyyy-mm-dd')AS DFECHA2, 
                            C.cDescri AS cCondic, A.mComent
                            FROM S02DINC A
                            LEFT OUTER JOIN V_S02TTAB B ON A.cAccTom=B.cCodigo AND B.cCodTab='033'
                            LEFT OUTER JOIN V_S02TTAB C ON A.cCondic=C.cCodigo AND C.cCodTab='034'
                            WHERE cNroInc='$this->pcNroInc' AND A.cTipo='I' ORDER BY dFecha1");
      $laPlaAcc = null;
      $laPlaAcc = array(array('\b Tipo de Acción'), array('\b Descripción'),array('\b Responsable'),array('\b Fecha Planificada'),array('\b F. Cierre'));
      while ($laFila = $loSql->fetch($RS)) {
         $lcHisto = $laFila[6]->load();
         $lcCrono = '';
         $lcFechas = '';
         $llFirst0 = true;
         while($lcHisto!=''){
            $lcCrono .= (!$llFirst0)? '\line ':'';
            $lcFechas = substr($lcHisto, 0, 27);
            $lcHisto = substr($lcHisto, 27, iconv_strlen($lcHisto));
            if ((substr($lcHisto, 0, strpos($lcHisto, 'FE')))==''){
			   $lcContent = substr($lcHisto, 0, iconv_strlen($lcHisto));
               $lcHisto = '';
               //$llFirst0 = false;
               $lcCrono .= $lcFechas.'\line '.$lcContent;
               break;
            }
            $lcContent = substr($lcHisto, 0, strpos($lcHisto, 'FE'));
            $lcHisto = substr($lcHisto, strpos($lcHisto, 'FE'), iconv_strlen($lcHisto));
            $llFirst0 = false;
            $lcCrono .= $lcFechas.'\line '.$lcContent;
         }
       array_push($laPlaAcc[0],'\\qc '.$laFila[0]);
       array_push($laPlaAcc[1],($laFila[1].'\line '.$lcCrono));
       //personal y cargo
         $RS1 = $loSql->omExec("SELECT B.cNombre ,A.cDescri AS cCarDis FROM TABLE (F_S02MPER_1('$laFila[2]')) B
                                LEFT OUTER JOIN V_S02TTAB A ON B.cCarDis=A.cCodigo AND A.cCodTab='045'");
         $laFila1 = $loSql->fetch($RS1);
         $Temp = $laFila1[0].'\line '.$laFila1[1];       
         array_push($laPlaAcc[2],$Temp);
         array_push($laPlaAcc[3],'\\qc '.$laFila[3]);
         array_push($laPlaAcc[4],'\\qc '.$laFila[4]);
      }
     $loSql->omDisconnect();
     // Construir Informe en RTF
      $laArray = array('#*CLUGAR*#'=>$lcLugar, '#*DFECHA*#'=>$ldFecha, '#*CDESEMP*#'=>$lcDesEmp,
                      '#*CDESCLA*#'=>$lcDesCla, '#*CDESTIP*#'=>$lcDesTip, '#*CNROINC*#'=>$lcNroInc,
                      '#*MDESCRI*#'=>$lmDescri, '#*CNOMPE1*#'=>$lcNomPe1, '#*CNOMPE2*#'=>$lcNomPe2,
                      '#*CDESAREA1*#'=>$lcDesArea1, '#*CDESAREA2*#'=>$lcDesArea2, '#*MSUGERE*#'=>$lmSugere,
                      '#*CCONSEC*#'=>$lcConsec, '#*CPO*#'=>$lcPotenc, '#*MANAPRE*#'=>$lmAnaPre,
                      '#*CREQINV*#'=>$lcReqInv, '#*CNOMRES*#'=>$lcNomRes,'#*CDESDIV*#'=>$lcDesDiv,
					  '#*CDESPRY*#'=>$lcDesPry
                  );
      $laDim=array(1400,4800,2000,1200,1200);
      $laAArray = array('#*TABLA*#'=>array(count($laPlaAcc), count($laPlaAcc[0]), $laPlaAcc,$laDim),
                        );
      $lo = new CRtf();
      $lo->pcFile = 'ACR_005_';
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
      $this->pcFilRet = $lo->pcFilRet;
	  //echo "<a href = '$lo->pcFilRet'>Archivo de Salida</a>";
      return true;
   }

   // Reporte de estado de incidentes
   public function omEstadoIncidentes() {
      if (!fxValDate($this->pdFecIni)) {
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
      // Actualiza estado de incidentes
      $llOk = $this->mxActualizarEstado();
      if (!$llOk) {
         return false;
      }
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Abre archivo de salida
      $this->pcFilRep = 'SEG5230_'.$this->pcCodUsu.'.txt';
      $lpFile = fopen($this->pcFilRep, 'w');
      $RS = $loSql->omExec("SELECT cNroInc FROM S02MINC WHERE dFecInc BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd')");
      while ($laFila = $loSql->fetch($RS)) {
         $RS1 = $loSql->omExec("SELECT cNroInc, cNombre, dFecInc, cDesEmp, cDesPry FROM V_S02MINC_1 WHERE cNroInc = '$laFila[0]'");
         $i = $loSql->fetch($RS1);
         fputs($lpFile, '* NUMERO   : '.$i['CNROINC']."\n");
         fputs($lpFile, '  ENCARGADO: '.$i['CNOMBRE']."\n");
         fputs($lpFile, '  FECHA    : '.$i['DFECINC']."\n");
         fputs($lpFile, '  EMPRESA  : '.$i['CDESEMP']."\n");
         fputs($lpFile, '  PROYECTO : '.$i['CDESPRY']."\n");
         // Detalle del plan de trabajo
         fputs($lpFile, '------------------------------------------------------------------------'."\n");
         fputs($lpFile, 'RESPONSABLE               ACCION       F.INICIO   F.FIN      DESCRIPCION'."\n");
         fputs($lpFile, '------------------------------------------------------------------------'."\n");
         $RS1 = $loSql->omExec("SELECT cNombre, cDesAcc, TO_CHAR(dFecha1, 'yyyy-mm-dd') AS dFecha1, TO_CHAR(dFecha2, 'yyyy-mm-dd') AS dFecha2, cDescri FROM V_S02DINC WHERE cNroInc = '$laFila[0]' ORDER BY dFecha1");
         while($i = $loSql->fetch($RS1)) {
            $lcNombre = (strlen($i['CNOMBRE']) < 25) ? $i['CNOMBRE'].str_repeat(' ', 25) : $i['CNOMBRE'];
            $lcDesAcc = (strlen($i['CDESACC']) < 12) ? $i['CDESACC'].str_repeat(' ', 12) : $i['CDESACC'];
            $ldFecha2 = (empty($i['DFECHA2'])) ? '          ' : $i['DFECHA2'];
            fputs($lpFile, substr($lcNombre, 0, 25).' '.substr($lcDesAcc, 0, 12).' '.$i['DFECHA1'].' '.$ldFecha2.' '.$i['CDESCRI']."\n");
         }
      }
      fclose($lpFile);
      return true;
   }

   // Reporte de estado de incidentes
   public function omConsultarIncidentes($p_aIncNro) {
      if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($p_aIncNro)) {
         $this->pcError = '<DATA><ERROR>NO HA SELECCIONADO NINGUN INCIDENTE</ERROR></DATA>';
         return false;
      }
      // Actualiza estado de incidentes
      $llOk = $this->mxActualizarEstado();
      if (!$llOk) {
         return false;
      }
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Abre archivo de salida
      $this->pcFilRep = 'SEG5230_'.$this->pcCodUsu.'.txt';
      $lpFile = fopen($this->pcFilRep, 'w');
      foreach($p_aIncNro as $lcNroInc){
	     $RS1 = $loSql->omExec("SELECT cNroInc, cNombre, dFecInc, cDesEmp, cDesPry FROM V_S02MINC_1 WHERE cNroInc = '$lcNroInc'");
         $i = $loSql->fetch($RS1);
         fputs($lpFile, '* NUMERO   : '.$i['CNROINC']."\n");
         fputs($lpFile, '  ENCARGADO: '.$i['CNOMBRE']."\n");
         fputs($lpFile, '  FECHA    : '.$i['DFECINC']."\n");
         fputs($lpFile, '  EMPRESA  : '.$i['CDESEMP']."\n");
         fputs($lpFile, '  PROYECTO : '.$i['CDESPRY']."\n");
         // Detalle del plan de trabajo
         fputs($lpFile, '------------------------------------------------------------------------'."\n");
         fputs($lpFile, 'RESPONSABLE               ACCION       F.INICIO   F.FIN      DESCRIPCION'."\n");
         fputs($lpFile, '------------------------------------------------------------------------'."\n");
         $RS1 = $loSql->omExec("SELECT cNombre, cDesAcc, TO_CHAR(dFecha1, 'yyyy-mm-dd') AS dFecha1, TO_CHAR(dFecha2, 'yyyy-mm-dd') AS dFecha2, cDescri FROM V_S02DINC WHERE cNroInc = '$lcNroInc' ORDER BY dFecha1");
         while($i = $loSql->fetch($RS1)) {
            $lcNombre = (strlen($i['CNOMBRE']) < 25) ? $i['CNOMBRE'].str_repeat(' ', 25) : $i['CNOMBRE'];
            $lcDesAcc = (strlen($i['CDESACC']) < 12) ? $i['CDESACC'].str_repeat(' ', 12) : $i['CDESACC'];
            $ldFecha2 = (empty($i['DFECHA2'])) ? '          ' : $i['DFECHA2'];
            fputs($lpFile, substr($lcNombre, 0, 25).' '.substr($lcDesAcc, 0, 12).' '.$i['DFECHA1'].' '.$ldFecha2.' '.$i['CDESCRI']."\n");
         }
      }
      fclose($lpFile);
      return true;
   }

   // Cierre de dia
   public function omCerrarDia() {
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         return false;
      }
      // Fecha y hora
      $RS = $loSql->omExec("SELECT TO_CHAR(SYSDATE, 'yyyy-mm-dd hh:ss') FROM DUAL");
      $laFila = $loSql->fetch($RS);
      $lcFecSis = $laFila[0];
      // Dia de la semana
      $RS = $loSql->omExec("SELECT TO_CHAR(SYSDATE, 'd') FROM DUAL");
      $laFila = $loSql->fetch($RS);
      $lcDia = $laFila[0];
      // Abre log
      $this->pcFilRep = 'SEG9999.log';
      $lpFile = fopen($this->pcFilRep, 'a+');
      // Cabecera
      fputs($lpFile, '****************************'."\n");
      fputs($lpFile, '* PROCESO DE CIERRE DIARIO *'."\n");
      fputs($lpFile, '* FECHA: '.$lcFecSis.'  *'."\n");
      fputs($lpFile, '****************************'."\n");
      // Actualiza estado de incidentes
      $llOk = $this->mxActualizarEstado();
      fputs($lpFile, 'ACTUALIZANDO ESTADO DE INCIDENTES ...');
      if (!$llOk) {
         fputs($lpFile, fxXmlError($this->pcError)."\n");
      } else {
         fputs($lpFile, 'OK'."\n");
      }
      // Valida plan de accion de incidentes
      fputs($lpFile, 'VALIDANDO PLAN DE ACCION ...');
      $llOk = $this->mxValidarPlanAccion($lpFile);
      if (!$llOk) {
         fputs($lpFile, fxXmlError($this->pcError)."\n");
      } elseif ($this->lnError == 0) {
         fputs($lpFile, 'OK'."\n");
      }
      // Envio de emails recordatorios a personal encargado de incidentes
      fputs($lpFile, 'ENVIANDO RECORDATORIOS DE PLANES DE ACCION PENDIENTES ...');
      $llOk = $this->mxPlanAccionPendiente($lpFile);
      if (!$llOk) {
         fputs($lpFile, fxXmlError($this->pcError)."\n");
      } elseif ($this->lnError == 0) {
         fputs($lpFile, 'OK'."\n");
      }
      // Envio de emails de vencimiento de licencias los viernes
      if ($lcDia == '5') {
         fputs($lpFile, 'ENVIANDO RECORDATORIOS DE LICENCIAS A VENCER ...');
         $llOk = $this->mxVencimientoLicencias($lpFile);
      }
      fclose($lpFile);
      return true;
   }

   // Envio de emails recordatorios de vencimiento de licencias
   protected function mxPlanAccionPendiente($p_pFile) {
      // Conexion DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         $this->lnError = 2;
         return false;
      }
      // Selecciona acciones no terminadas en fecha planificada
      $RS = $loSql->omExec("SELECT TO_CHAR(SYSDATE, 'yyyy-mm-dd') FROM DUAL");
      $laFila = $loSql->fetch($RS);
      $lcFecSis = $laFila[0];
      $RS = $loSql->omExec("SELECT A.cNroInc, A.dFecha1, A.cCodPer, B.cCodPer AS cCodRes, cArea1, cArea2 FROM S02DINC A INNER JOIN S02MINC B ON A.cNroInc = B.cNroInc WHERE A.dFecha1 < TO_DATE('$lcFecSis', 'yyyy-mm-dd') AND cEstado = 'P'");
      while ($laFila = $loSql->fetch($RS)) {
         // Busca responsables por area
         $lcArea = $laFila['CAREA1'];
         $RS1 = $loSql->omExec("SELECT mTexto FROM V_S02TTAB WHERE cCodigo = '$lcArea' AND cCodTab = '030' AND cTipReg = '0'");
         $i = $loSql->fetch($RS);
         $lcEmail = $i[0];
         $lcArea = $laFila['CAREA2'];
         $RS1 = $loSql->omExec("SELECT mTexto FROM V_S02TTAB WHERE cCodigo = '$lcArea' AND cCodTab = '030' AND cTipReg = '0'");
         $i = $loSql->fetch($RS);
         $lcEmail .= $i[0];
         // Busca email de personal responsable de incidente y responsable de plan de accion
         $lcCodPer = $laFila['CCODPER'];
         $RS1 = $loSql->omExec("SELECT cEmail FROM S02MPER WHERE cCodPer = '$lcCodPer'");
         $i = $loSql->fetch($RS);
         $lcEmail .= $i[0];
         $lcCodPer = $laFila['CCODRES'];
         $RS1 = $loSql->omExec("SELECT cEmail FROM S02MPER WHERE cCodPer = '$lcCodPer'");
         $i = $loSql->fetch($RS);
         $lcEmail .= $i[0];
         // Junta emails y elimina duplicados OJOFPM FALTA
         // Envia email
         $lo = new CSendEmail();
         $lo->pcForWho = $lcEmail;
         $lo->pcFrom =   'webmaster@perurail.com';
         $lo->pcSubject = 'Recordatorio Plan de Accion de Incidentes';
         $lo->pcMessage = $lcTexto1;
         $llOk = $lo->omSend();
         if (!$llOk) {
            $this->pcError = $lo->pcError;
            return false;
         }
      }
      $loSql->omDisconnect();
      return true;
   }
   
   // Envio de emails recordatorios de vencimiento de licencias
   protected function mxVencimientoLicencias($p_pFile) {
      // Conexion DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         $this->lnError = 2;
         return false;
      }
      // Selecciona responsables de licencias
      // 
      // Selecciona licencias por vencer en 90 dias ordenados por tipo de licencias
      // Acumula licencias por vencer
      // Envia email recordatorio
      
      $loSql->omDisconnect();
      return true;
   }

   // Valida plan de accion
   protected function mxValidarPlanAccion($p_pFile) {
      $this->lnError = 0;
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         $this->lnError = 2;
         return false;
      }
      $RS = $loSql->omExec("SELECT cNroInc, cReqInv FROM S02MINC ORDER BY cNroInc DESC");
      while ($laFila = $loSql->fetch($RS)) {
         $lcNroInc = $laFila[0];
         $lcReqInv = $laFila[1];
         $RS1 = $loSql->omExec("SELECT COUNT(*) FROM S02DINC WHERE cNroInc = '$lcNroInc' AND cTipo = 'C'");
         $i = $loSql->fetch($RS1);
         if (empty($i[0])) {
            $this->lnError = 1;
            fputs($p_pFile, 'NUMERO: '.$lcNroInc.' NO TIENE PLAN DE ACCION INICIAL'."\n");
         }
         if ($lcReqInv == 'S') {
            $RS1 = $loSql->omExec("SELECT COUNT(*) FROM S02DINC WHERE cNroInc = '$lcNroInc' AND cTipo = 'I'");
            $i = $loSql->fetch($RS1);
            if (empty($i[0])) {
               $this->lnError = 1;
               fputs($p_pFile, 'NUMERO: '.$lcNroInc.' NO TIENE PLAN DE ACCION DE INVESTIGACION'."\n");
            }
         }
      }
      $loSql->omDisconnect();
      return true;
   }

   // Actualiza estado de incidentes
   protected function mxActualizarEstado() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $RS = $loSql->omExec("SELECT cNroInc, cReqInv FROM S02MINC WHERE cEstado = 'P'");
      while ($laFila = $loSql->fetch($RS)) {
         $lcNroInc = $laFila[0];
         $lcReqInv = $laFila[1];
         $RS1 = $loSql->omExec("SELECT cCondic FROM S02DINC WHERE cNroInc = '$lcNroInc' AND cTipo = 'C' AND cCondic = 'P'");
         $i = $loSql->fetch($RS1);
         if (empty($i[0]) and $lcReqInv == 'N') {
            // Actualiza estado de incidente sin investigacion
            $llOk = $loSql->omExec("UPDATE S02MINC SET cEstado = 'E' WHERE cNroInc = '$lcNroInc'");
            if (!$llOk) {
               $loSql->rollback();
               $loSql->omDisconnect();
               $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR EL ESTADO DE LOS INCIDENTES</ERROR></DATA>';
               return false;
            }
         } elseif (empty($i[0])) {
            $RS1 = $loSql->omExec("SELECT cCondic FROM S02DINC WHERE cNroInc = '$lcNroInc' AND cTipo = 'I' AND cCondic = 'P'");
            $i = $loSql->fetch($RS1);
            if (empty($i[0])) {
               // Actualiza estado de incidente con investigacion
               $llOk = $loSql->omExec("UPDATE S02MINC SET cEstado = 'E' WHERE cNroInc = '$lcNroInc'");
               if (!$llOk) {
                  $loSql->rollback();
                  $loSql->omDisconnect();
                  $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR EL ESTADO DE LOS INCIDENTES</ERROR></DATA>';
                  return false;
               }
            }
         }
      }
      $loSql->omDisconnect();
      return true;
   }
}
?>