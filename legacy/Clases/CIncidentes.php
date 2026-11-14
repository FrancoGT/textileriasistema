<?php
require_once 'Clases/CBase.php';
require_once 'Clases/CEnvioEmails.php';

//------------------------------------------------------------------------------------------
// Registro de Pruebas de eficiencia en terreno, conduccion de trenes y velocidad por radar
//------------------------------------------------------------------------------------------
class CIncidentes extends CBase {
   public $paCodHer, $paCodPer, $paCodEmp, $paCodPry, $paAreas, $paClaInc, $paTipInc, $paData, $paData1, $paData2,
          $paData3, $paData4, $paData5, $paDataF, $paConsec, $paLugar, $paCargos, $pmDatos, $pmData, $pcConsec,
          $pcPotenc, $pmAnaPre, $pcReqInv, $paCodPin, $paCodUin, $paCodUni, $paCodFcr, $paFacCr1, $paFacCr2, 
          $paFacCr3, $paFacCr4, $paFacCr5, $pmCausa1, $pmCausa2, $pmCausa3, $pmCausa4, $pmCausa5, $pmComen1, 
          $pmComen2, $pmComen3, $pmComen4, $pmComen5, $pmEveCro, $pmFacCri, $pcNombre, $pcFecha1, $pcDescri, 
          $pmComent, $pcCondic, $pnSerial, $pmDamPro, $pmConGes, $paPerCod, $pcCodPer, $paSubDiv, $paEstaci, 
          $paCodPrc, $pcCodPrc, $pcKilome, $pcSubDiv, $paCarDis, $paPerTer, $pmConclu, $pnCosto, $pcEficaz,
          $pmDesDet, $paEstado;
   protected $lcEmail;

   public function __construct() {
      parent::__construct();
      $this->paCodHer = $this->paCodPer = $this->paCodEmp = $this->paCodPry = $this->paData = $this->paDataF = 
      $this->paAreas = $this->paClaInc = $this->paTipInc = $this->paConsec = $this->paPotenc = $this->paLugar = 
      $this->paConsec = $this->paLugar = $this->paCodUin = $this->paCodUni = $this->paCodPin = $this->paCodFcr = 
      $this->paCargos = $this->paPerCod = $this->paData1 = $this->paData2 = $this->paData3 = $this->paData4 = 
      $this->paData5 = $this->paFacCr1 = $this->paFacCr2 = $this->paFacCr3 = $this->paFacCr4 = $this->paFacCr5 = 
      $this->paSubDiv = $this->paEstaci = $this->paCodPrc = $this->paCarDis = $this->paPerTer = $this->paEstado = null;
   }

   public function omInitIncidentes() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga maestro de unidades
      $RS = $loSql->omExec("SELECT cCodUni, cDescri FROM S02MUNI WHERE cEstado = 'A' ORDER BY cDescri");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodUni[$i] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY UNIDADES/EQUIPOS DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Carga empresas
      $RS = $loSql->omExec("SELECT cCodEmp, cDescri FROM S02MEMP WHERE SUBSTR(cCodEmp, 1, 1) != '9' AND cEstado = 'A' ORDER BY nOrden, cDescri");
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
      // Carga proyectos
      $RS = $loSql->omExec("SELECT cCodPry, cDescri FROM S02MPRY WHERE cEstado = 'A' ORDER BY cDescri");
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
      // Carga procesos de cada proyecto
      foreach($this->paCodPry as $laTmp) {
         $RS = $loSql->omExec("SELECT cCodPry, cCodPrc, cDescri FROM S02PPRC WHERE cCodPry = '$laTmp[0]' ORDER BY cCodPrc");
         $i = 0;
         while ($laFila = $loSql->fetch($RS)) {
            $i++;
            $this->paCodPrc[] = array($laFila[0], $laFila[1], $laFila[2]);
         }
         if ($i == 0) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>NO HAY PROCESOS DEFINIDOS PARA PROYECTO ['.$laTmp[0].']</ERROR></DATA>';
            return false;
         }
      }
      // Carga cargos
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 3), cDescri FROM V_S02TTAB WHERE cCodTab = '015'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCargos[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY CARGOS [015] DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Carga cargos disgregados
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 3), cDescri FROM V_S02TTAB WHERE cCodTab = '045'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCarDis[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>TABLA [045] DE CARGOS DISGREGADOS NO ESTA DEFINIDA</ERROR></DATA>';
         return false;
      }
      // Carga estaciones
      $RS = $loSql->omExec("SELECT TRIM(cCodigo), cDescri FROM V_S02TTAB WHERE cCodTab = '021'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstaci[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>TABLA DE ESTACIONES [021] NO ESTA DEFINIDA</ERROR></DATA>';
         return false;
      }
      // Carga subdivision
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE cCodTab = '020'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paSubDiv[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>TABLA DE SUBDIVISION [020] NO ESTA DEFINIDA</ERROR></DATA>';
         return false;
      }
      // Carga areas
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE cCodTab = '030'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paAreas[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY AREAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      // Carga consecuencias
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '011'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paConsec[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY CONSECUENCIAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      // Carga potencial
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '011'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paPotenc[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY POTENCIALES DEFINIDAS</ERROR></DATA>';
         return false;
      }
      // Carga clasificacion de incidentes
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '031'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paClaInc[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CLASIFICACION DE INCIDENTES NO DEFINIDA</ERROR></DATA>';
         return false;
      }
      // Tipo de incidente
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '032'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paTipInc[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>TIPO DE INCIDENTES NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Lugar
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '008'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paLugar[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>TABLA DE CONSECUENCIA NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Accion a Tomar
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE cCodTab = '033'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paAccTom[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>TABLA DE ACCIONES A TOMAR NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Condiciones
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '034' ORDER BY CCODIGO DESC");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCondic[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>TABLA DE  CONDICIONES NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Carga con cargo responsable
      $RS = $loSql->omExec("SELECT A.cCodPer, A.cNombre FROM S02MPER A WHERE (SELECT cCargo FROM TABLE (F_S02MPER_1(A.cCodPer))) IN ('001', '002', '003') ORDER BY A.cNombre");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paPerCod[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL DEFINIDO CON CARGO DE RESPONSABILIDAD</ERROR></DATA>';
         return false;
      }
      // Carga personal inactivo codigo 9
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE cEstado = 'I' AND SUBSTR(cCodPer, 1, 1) = '9' ORDER BY cNombre");
      while ($laFila = $loSql->fetch($RS)) {
         $this->paPerTer[] = array($laFila[0], $laFila[1]);
      }
      // Carga personal
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE cEstado = 'A' ORDER BY cNombre");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL DEFINIDO</ERROR></DATA>';
         return false;
      }
      return true;
   }
   //*****
   public function omInitInvestigacion(){
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Personal de investigacion
      $RS = $loSql->omExec("SELECT A.cCodPer, A.cNombre FROM S02MPER A WHERE CESTADO ='A' ORDER BY A.cNombre");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
         $i++;
      }
     if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL PARA ASIGNAR AL EQUIPO DE INVESTIGACION</ERROR></DATA>';
         return false;
      }
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '001'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>TABLA DE ESTADOS NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   public function omGrabarEquipo(){
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      } if (empty($this->paData) or fxCount($this->paData) == 0){
         $this->pcError = '<DATA><ERROR>NO HAY DATOS DEL EQUIPO DE INVESTIGACION</ERROR></DATA>';
         return false;
      }
      $llOk = fxValRepeated($this->paData, 1);
      if ($llOk) {
         $this->pcError = '<DATA><ERROR>HAY PERSONAL REPETIDO</ERROR></DATA>';
         return false;
      }
      $i = $j = 0;
      foreach($this->paData as $laFila) {
    if(!($laFila[3]=='S' or $laFila[3]=='N')){
            $this->pcError = '<DATA><ERROR>CAMPO PARA DEFINIR LIDER DEBE SER S/N</ERROR></DATA>';
            return false;   
         }
    if ($laFila[4] == 'A') {
            $j++;
            if ($laFila[3] == 'S') {
            $i++;
         }
        }
      }
      if ($i != 1) {
         $this->pcError = '<DATA><ERROR>NO HA DEFINIDO PERSONAL LIDER</ERROR></DATA>';
         return false;   
      }
      if ($j == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY EQUIPO DE INVESTIGACION ACTIVO</ERROR></DATA>';
         return false;   
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $RS = $loSql->omExec("SELECT cNroInc FROM S02MINC where cNroInc = '$this->pcNroInc'");
      
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO EXISTE</ERROR></DATA>';
         return false;
      }
      $llOk = $loSql->omExec("DELETE FROM S02DEIV where cNroInc = '$this->pcNroInc'");
      if (!$llOk) {
         $loSql->rollback();
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR DETALLE DE EQUIPO DE INVESTIGACION</ERROR></DATA>';
         return false;
      }
      foreach($this->paData as $i) {
        $lcSql = "INSERT INTO S02DEIV (cNroInc, cCodPer, cLider, cEstado, cCodUsu) VALUES ('$this->pcNroInc','$i[1]', '$i[3]', '$i[4]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR EQUIPO DE INVESTIGACION</ERROR></DATA>';
            return false;
         }
      }
      $loSql->omDisconnect();
      return true;
   }
   public function omAplicar8(){
      $llOk = $this->omAplicar1();
      if (!$llOk) {
         return false;
      } elseif ($this->pcReqInv == 'N') {
         $this->pcError = '<DATA><ERROR>INCIDENTE NO REQUIERE INVESTIGACION</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga detalle de personal involucrado
      $this->paData=null;
      $lcSql = "SELECT A.cCodPer, B.cNombre, A.cLider, A.cEstado FROM S02DEIV A 
                INNER JOIN S02MPER B ON A.cCodPer = B.cCodPer
                WHERE cNroInc = '$this->pcNroInc'";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($i, $laFila[0], $laFila[1], $laFila[2], $laFila[3]);
      }
      $loSql->omDisconnect();
      return true;
   }
   //*****
   public function omInitBusqueda() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '038'");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paEstado[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>TABLA DE ESTADO DE INCIDENTES NO DEFINIDA [038]</ERROR></DATA>';
         $llOk = false;
      }
      return $llOk;
   }

   public function omAgregarPersonalInvolucrado(){
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>PERSONAL INVOLUCRADO VACIO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT cNombre, cCodEmp, cDescri, cCarDis, cDesDis FROM TABLE (F_S02MPER_1('$this->pcCodPer'))";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = '<DATA><ERROR>CODIGO DE EMPLEADO NO EXISTE</ERROR></DATA>';
         return false;
      } else if (empty($laFila[1]) && $this->pcCodPer != '999999') {
         $this->pcError = '<DATA><ERROR>PERSONAL NO TIENE DEFINIDA LA EMPRESA</ERROR></DATA>';
         return false;
      } else if (empty($laFila[3])&& $this->pcCodPer != '999999') {
         $this->pcError = '<DATA><ERROR>PERSONAL NO TIENE DEFINIDO EL CARGO</ERROR></DATA>';
         return false;
      }
      $this->paData[] = array($this->pcCodPer, $laFila[0], $laFila[1], $laFila[2], $laFila[3], $laFila[4]);
      return $llOk;
   }
   
   // Consulta incidentes
   public function omConsultar($p_cReqInv = 'N') {
      if (!fxValDate($this->pdFecIni)) {
         $this->pcError = '<DATA><ERROR>FECHA INICIAL ERRADA</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecFin)) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL ERRADA</ERROR></DATA>';
         return false;
      } elseif ($this->pdFecFin < $this->pdFecIni) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL MENOR QUE FECHA INICIAL</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcEstado)) {
         $this->pcError = '<DATA><ERROR>ESTADO DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Conecta con DB              
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      if ($p_cReqInv == 'N'){
         $lcSql = "SELECT A.cNroInc, A.mDescri, TO_CHAR(A.dFecInc,'yyyy-mm-dd') AS DFECINC, B.cDescri AS cDesEst FROM S02MINC A 
                   LEFT OUTER JOIN V_S02TTAB B ON B.cCodTab = '038' AND A.cEstado = B.cCodigo 
                   WHERE A.dFecInc BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd')AND
                   A.cEstado = '$this->pcEstado' ORDER BY cNroInc DESC";
      } else {
         $lcSql = "SELECT A.cNroInc, A.mDescri, TO_CHAR(A.dFecInc,'yyyy-mm-dd') AS DFECINC, B.cDescri AS cDesEst FROM S02MINC A 
                   LEFT OUTER JOIN V_S02TTAB B ON B.cCodTab = '038' AND A.cEstado = B.cCodigo 
                   WHERE A.dFecInc BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd') AND 
                   A.cEstado = '$this->pcEstado' AND A.cReqInv = 'S' ORDER BY cNroInc DESC";
      }
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      while ($laFila = $loSql->fetch($RS)) {
         $lcDescri = substr($laFila['MDESCRI']->load(), 0, 100);
         $this->paNroInc[] = array($laFila['CNROINC'], $lcDescri, $laFila['DFECINC'], $laFila['CDESEST']);
      }
      return true;
   }
   
   // Buscar incidentes
   public function omBuscarIncidente() {
      if (empty($this->pcTipo)) {
         $this->pcError = '<DATA><ERROR>TIPO DE BUSQUEDA NO DEFINIDA</ERROR></DATA>';
         return false;
      } else if ($this->pcTipo == '1' and empty($this->pcDescri)) {
         $this->pcError = '<DATA><ERROR>DESCRIPCION VACIA</ERROR></DATA>';
         return false;
      }         
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      if ($this->pcTipo == '1') {
         $lcDescri = empty($this->pcDescri).'%';
         $lcSql = "SELECT cNroInc, mDescri FROM S02MINC WHERE mDescri LIKE '$lcDescri' ORDER BY dFecInc, cHorInc DESC";
      } else {
         $lcSql = "SELECT cNroInc, mDescri FROM S02MINC WHERE cEstado = '$this->pcEstado' ORDER BY dFecInc, cHorInc DESC";
      }
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $lcDescri = substr($laFila[1]->load(), 0, 50);
         $this->paNroInc[] = array($laFila[0], $lcDescri, 'PENDIENTE');
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY INCIDENTES REGISTRADOS</ERROR></DATA>';
         $llOk = false;
      }
      return $llOk;
    }

   public function omGrabar1(){
      if (empty($this->pmComent)) {
         $this->pcError = '<DATA><ERROR>ERROR: COMENTARIO VACIO.</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcEficaz)) {
         $this->pcError = '<DATA><ERROR>INDICADOR DE EFICAZ NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Conectar DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $RS = $loSql->omExec("SELECT cNroInc FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO EXISTE</ERROR></DATA>';
         return false;
      }
      $llOk = $loSql->omExec("UPDATE S02MINC SET mComent = '$this->pmComent', cEficaz = '$this->pcEficaz' WHERE cNroInc = '$this->pcNroInc'");
      $loSql->omDisconnect();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR EVALUACION</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   // Graba incidente
   public function omGrabar() {
      if (!fxValDate($this->pcFecInc)) {
         $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE INCIDENTE ['.$this->pcFecInc.']</ERROR></DATA>';
         return false;
      } elseif (!fxValTime($this->pcHorInc)) {
         $this->pcError = '<DATA><ERROR>ERROR EN HORA DE INCIDENTE ['.$this->pcHorInc.']</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodEmp)) {
         $this->pcError = '<DATA><ERROR>EMPRESA NO DEFINIDA</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodPry)) {
         $this->pcError = '<DATA><ERROR>PROYECTO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcLugar)) {
         $this->pcError = '<DATA><ERROR>LUGAR DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcClaInc)) {
         $this->pcError = '<DATA><ERROR>CLASIFICACION DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcTipInc)) {
         $this->pcError = '<DATA><ERROR>TIPO DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodPe1)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE REPORTADOR NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcArea1)) {
         $this->pcError = '<DATA><ERROR>AREA DE REPORTADOR NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodPe2)) {
         $this->pcError = '<DATA><ERROR>CODIGO PERSONAL COMUNICADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcArea2)) {
         $this->pcError = '<DATA><ERROR>AREA DE PERSONAL COMUNICADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pmDescri)) {
         $this->pcError = '<DATA><ERROR>ERROR: DESCRIPCION DEL INCIDENTE VACIO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pmSugere)) {
         $this->pcError = '<DATA><ERROR>ERROR: SUGERENCIA VACIA</ERROR></DATA>';
         return false;
      } elseif ($this->pcEstaci == '99' and (empty($this->pcKilome))) {
         $this->pcError = '<DATA><ERROR>ERROR: PK VACIA</ERROR></DATA>';
         return false;
      } elseif ($this->pcEstaci == '99' and (strlen(trim($this->pcKilome)) > 8)) {
         $this->pcError = '<DATA><ERROR>LONGITUD DE PK ES MAYOR QUE 8</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (!$this->plNuevo and empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $this->pcKilome = trim($this->pcKilome);
      // Arregla numero de reporte fisico
      $this->pcRepFis = trim($this->pcRepFis);
      if (empty($this->pcRepFis))
         $this->pcRepFis = '';
      $this->pcRepFis = right('00000000'.$this->pcRepFis, 8);
      // Conecta con DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Valida PK de estacion
      if ($this->pcEstaci != '99') {
         $this->pcKilome = $this->pcEstaci;
      }
      // Valida subdivision
      $RS = $loSql->omExec("SELECT cCodigo FROM V_S02TTAB WHERE cCodTab = '020' AND cCodigo = '$this->pcSubDiv'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = '<DATA><ERROR>CODIGO DE SUBDIVISION NO EXISTE</ERROR></DATA>';
         $loSql->omDisconnect();
         return false;
      }
      // Valida estacion
      $RS = $loSql->omExec("SELECT cCodigo FROM V_S02TTAB WHERE cCodTab = '021' AND cCodigo = '$this->pcEstaci'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = '<DATA><ERROR>CODIGO DE ESTACION NO EXISTE</ERROR></DATA>';
         $loSql->omDisconnect();
         return false;
      }
      // Carga emails de responsables
      $llOk = $this->mxCargarEmails($loSql, $this->pcArea1, $this->pcArea2);
      if (!$llOk) {
         $loSql->omDisconnect();
         return false;
      }
      // Emails de personas involucradas
      $RS = $loSql->omExec("SELECT cNombre, cEmail FROM S02MPER WHERE cCodPer = '$this->pcCodPe1'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONA QUE REPORTA NO EXISTE</ERROR></DATA>';
         $loSql->omDisconnect();
         return false;
      }
      $lcNombr1 = trim($laFila[0]);
      $lcTmp = trim($laFila[1]);
      if (fxValEmail($lcTmp)) {
         $this->lcEmail .= ','.$lcTmp;
      }
      $RS = $loSql->omExec("SELECT cNombre, cEmail FROM S02MPER WHERE cCodPer = '$this->pcCodPe2'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONA A QUIEN SE REPORTA NO EXISTE</ERROR></DATA>';
         $loSql->omDisconnect();
         return false;
      }
      $lcNombr2 = trim($laFila[0]);
      $lcTmp = trim($laFila[1]);
      if (fxValEmail($lcTmp)) {
         $this->lcEmail .= ','.$lcTmp;
      }
      if (!$this->plNuevo) {
         $RS = $loSql->omExec("SELECT cNroInc FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO EXISTE</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $lcSql = "UPDATE S02MINC SET dFecInc = TO_DATE('$this->pcFecInc', 'yyyy-mm-dd'), cHorInc = '$this->pcHorInc',
                   cCodEmp = '$this->pcCodEmp', cCodPry = '$this->pcCodPry', cLugar = '$this->pcLugar', cClaInc = '$this->pcClaInc',
                   cTipInc = '$this->pcTipInc', cCodPe1 = '$this->pcCodPe1', cArea1 = '$this->pcArea1', cCodPe2 = '$this->pcCodPe2',
                   cArea2 = '$this->pcArea2', mDescri = '$this->pmDescri', mSugere = '$this->pmSugere', cSubDiv = '$this->pcSubDiv',
                   cKilome = '$this->pcKilome', cEstaci = '$this->pcEstaci', cCodPrc = '$this->pcCodPrc', cRepFis = '$this->pcRepFis',
                   cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cNroInc = '$this->pcNroInc'";
		 $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR EVALUACION</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      } else {
         $RS = $loSql->omExec("SELECT MAX(cNroInc) FROM S02MINC");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $lcNroInc = '000000';
         } else {
            $lcNroInc = $laFila[0];
         }
         $lnNroEva = (int)$lcNroInc + 1;
         $lcNroInc = (string)$lnNroEva;
         $this->pcNroInc = right('00000'.trim($lcNroInc), 6);
         $lcSql = "INSERT INTO S02MINC (cNroInc, cEstado, dFecInc, cHorInc, cCodEmp, cCodPry, cLugar, cClaInc, cTipInc, cCodPe1,
                   cArea1, cCodPe2, cArea2, mDescri, mSugere, cCodPer, cSubDiv, cKilome, cEstaci, cCodPrc, cRepFis, cCodUsu) VALUES 
                   ('$this->pcNroInc', 'P', TO_DATE('$this->pcFecInc', 'YYYY-MM-DD'), '$this->pcHorInc', '$this->pcCodEmp',
                   '$this->pcCodPry', '$this->pcLugar', '$this->pcClaInc', '$this->pcTipInc', '$this->pcCodPe1',
                   '$this->pcArea1', '$this->pcCodPe2', '$this->pcArea2', '$this->pmDescri', '$this->pmSugere', 
                   '999999', '$this->pcSubDiv', '$this->pcKilome', '$this->pcEstaci', '$this->pcCodPrc', '$this->pcRepFis', '$this->pcCodUsu')";
	     $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INGRESAR INCIDENTE</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      }
      $loSql->omDisconnect();
      // Elimina emails repetidos
      $this->mxEliminaEmailRepetidos();
      // Envio de emails
      $lcFecha = date('Y-m-d H:i:s'); 
      $lcTexto = "Incidente Reportado: $this->pcNroInc".chr(13);
      $lcTexto .= "Fecha y hora: $lcFecha".chr(13);
      $lcTexto .= "De : $lcNombr1".chr(13);
      $lcTexto .= "Para : $lcNombr2".chr(13);
      $lcTexto .= "Descripcion: $this->pmDescri".chr(13);
      $lo = new CSendEmail();
      $lo->pcForWho = $this->lcEmail;
      $lo->pcFrom =   'webmaster@perurail.com';
      $lo->pcSubject = 'Incidente Reportado';
      $lo->pcMessage = $lcTexto;
      $llOk = $lo->omSend();
      if (!$llOk) {
         $this->pcError = $lo->pcError;
         return false;
      }
      return true;
   }
   
   public function omGrabarDetalleIncidente() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      } if (!($this->pcReqInv == 'S' or $this->pcReqInv == 'N')) {
         $this->pcError = '<DATA><ERROR>INDICADOR SI REQUIERE INVESTIGACION INCORRECTO</ERROR></DATA>';
         return false;
      } if (empty($this->pmAnaPre)) {
         $this->pcError = '<DATA><ERROR>DETALLE DEL ANALISIS PRELIMINAR ESTA VACIO</ERROR></DATA>';
         return false;
      } if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO ESTA VACIO</ERROR></DATA>';
         return false;
      } if (empty($this->paData) or fxCount($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DETALLE DE INCIDENTES</ERROR></DATA>';
         return false;
      }
      // Evalua detalle de seguimiento de FCR
      foreach($this->paData as $i) {
         if (empty($i[2])) {
            $this->pcError = '<DATA><ERROR>DESCRIPCION DEL INCIDENTE ESTA VACIA</ERROR></DATA>';
            return false;
         } elseif (!fxValDate($i[4])) {
            $this->pcError = '<DATA><ERROR>ERROR EN FORMATO DE FECHA</ERROR></DATA>';
            return false;
         }
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Saca emails para envio de alerta y fecha de incidente
      $RS = $loSql->omExec("SELECT mDescri, TO_CHAR(dFecInc, 'yyyy-mm-dd') AS dFecInc FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO EXISTE</ERROR></DATA>';
         return false;
      }
      $ldFecInc = $laFila['DFECINC'];
      // Valida fecha de plan de accion con fecha de incidente
      foreach($this->paData as $i) {
         if ($i[4] < $ldFecInc) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>FECHA(S) DE PLAN DE ACCION ANTERIOR A FECHA DE INCIDENTE</ERROR></DATA>';
            return false;
         }
      }
      // Actualiza maestro de incidentes
      $lcSql = "UPDATE S02MINC SET cConsec = '$this->pcConsec', cPotenc = '$this->pcPotenc', mAnaPre = '$this->pmAnaPre', cReqInv = '$this->pcReqInv', cCodPer = '$this->pcCodPer', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE where cNroInc = '$this->pcNroInc'";
      $llOk = $loSql->omExec($lcSql);
      if (!$llOk) {
         $loSql->rollback();
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR DATOS DE LA CABECERA DEL INCIDENTE</ERROR></DATA>';
         return false;
      }
      $lcSql = "DELETE FROM S02DINC WHERE cNroInc = '$this->pcNroInc' AND cTipo = 'I'";
      $llOk = $loSql->omExec($lcSql);
      if (!$llOk) {
         $loSql->rollback();
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR DETALLE DEL INCIDENTE</ERROR></DATA>';
         return false;
      }
      foreach($this->paData as $i) {
         if ($i[5] == 'P') {
            $lcSql = "INSERT INTO S02DINC (cNroInc, cAccTom, cDescri, cCodPer, dFecha1, cCondic, cCodUsu) VALUES 
                      ('$this->pcNroInc', '$i[1]', '$i[2]', '$i[3]', TO_DATE('$i[4]', 'YYYY-MM-DD'), '$i[5]', '$this->pcCodUsu')";
         } else {
            $lcSql = "INSERT INTO S02DINC (cNroInc, cAccTom, cDescri, cCodPer, dFecha1, dFecha2, cCondic, cCodUsu) VALUES 
                      ('$this->pcNroInc', '$i[1]', '$i[2]', '$i[3]', TO_DATE('$i[4]', 'YYYY-MM-DD'), SYSDATE, '$i[5]', '$this->pcCodUsu')";
         }
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            break;
         }
      }
      $loSql->omDisconnect();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR AL INGRESAR DETALLE INCIDENTE</ERROR></DATA>';
         return false;
      }
      // Envio de emails
      $lo = new CEnvioEmails();
      $lo->pcNroInc = $this->pcNroInc;
      $lo->paData   = $this->paData;
      $llOk = $lo->omEnviar1();
      if (!$llOk) {
         $this->pcError = $lo->pcError;
         return false;
      }
      return $llOk;
   }

   // Carga emails de responsable superior y responsables de areas
   protected function mxCargarEmails($p_oSql, $p_cArea1, $p_cArea2) {
      // Email de personal maximo responsable
      $RS = $p_oSql->omExec("SELECT mTexto FROM S02TTAB WHERE cCodTab = '030' AND cTipReg = '1' AND cCodigo = '99'");
      $laFila = $p_oSql->fetch($RS);
      $this->lcEmail = trim($laFila[0]->load());
      // Email de personal responsable de areas
      if ($p_cArea1 == $p_cArea2 and $p_cArea1 != '99') {
         $RS = $p_oSql->omExec("SELECT mTexto FROM S02TTAB WHERE cCodTab = '030' AND cTipReg = '1' AND cCodigo = '$p_cArea1'");
         $laFila = $p_oSql->fetch($RS);
         $lcTmp = $laFila[0]->load();
         $this->lcEmail .= ','.$lcTmp;
      } else {
         if ($p_cArea1 != '99') {
            $RS = $p_oSql->omExec("SELECT mTexto FROM S02TTAB WHERE cCodTab = '030' AND cTipReg = '1' AND cCodigo = '$p_cArea1'");
            $laFila = $p_oSql->fetch($RS);
            $lcTmp = trim($laFila[0]->load());
            $this->lcEmail .= ','.$lcTmp;
         }
         if ($p_cArea2 != '99') {
            $RS = $p_oSql->omExec("SELECT mTexto FROM S02TTAB WHERE cCodTab = '030' AND cTipReg = '1' AND cCodigo = '$p_cArea2'");
            $laFila = $p_oSql->fetch($RS);
            $lcTmp = trim($laFila[0]->load());
            $this->lcEmail .= ','.$lcTmp;
         }
      }
      return true;
   }
   
   protected function mxEliminaEmailRepetidos() {
      $laTmp = explode(',', $this->lcEmail);
      // Elimina repetidos
      $laEmail = null;
      for ($i = 0; $i < fxCount($laTmp); $i++) {
          $llFlag = true;
          for ($j = 0; $j < fxCount($laEmail); $j++) {
              if ($laTmp[$i] == $laEmail[$j]) {
                 $llFlag = false;
                 break;
              }
          }
          if ($llFlag and fxValEmail($laTmp[$i])) {
             $laEmail[] = array($laTmp[$i]);
          }
      }
      $this->lcEmail = '';
      foreach($laEmail as $i) {
          $this->lcEmail .= $i[0].',';
      }
   }

   // Define responsables para elementos de causa raiz
   public function omGrabarCausaRaiz() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->paData) or fxCount($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DETALLE DE INCIDENTES</ERROR></DATA>';
         return false;
      }
      // Evalua datos de seguimiento
      foreach($this->paData as $i) {
         if (empty($i[2])) {
            $this->pcError = '<DATA><ERROR>DESCRIPCION DEL INCIDENTE ESTA VACIA</ERROR></DATA>';
            return false;
         } else if (!fxValDate($i[4])){
            $this->pcError = '<DATA><ERROR>ERROR EN FORMATO DE FECHA</ERROR></DATA>';
            return false;
         }
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Valida numero de incidente
      $RS = $loSql->omExec("SELECT TO_CHAR(dFecInc,'yyyy-mm-dd') AS DFECINC FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = "<DATA><ERROR>NUMERO DE COMPROBANTE [$this->pcNroInc] NO EXISTE</ERROR></DATA>";
         $loSql->omDisconnect();
         return false;
      }
      $ldFecInc = $laFila[0];
      // Valida Fecha de Incidente en Plan de Accion
      foreach($this->paData as $i) {
         if ($i[4] < $ldFecInc){
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>FECHA DE PLAN DE ACCION ES MENOR QUE LA FECHA DEL INCIDENTE</ERROR></DATA>';
            return false;
         }
      }
      // Borra elementos anteriores
      $llOk = $loSql->omExec("DELETE FROM S02DINC WHERE cNroInc = '$this->pcNroInc' AND cTipo = 'C'");
      if (!$llOk) {
         $loSql->rollback();
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR DETALLE DE ANALISIS CAUSA RAIZ</ERROR></DATA>';
         return false;
      }
      // Adiciona nuevos elementos
      foreach($this->paData as $i) {
         if ($i[5] == 'P') {
            $lcSql = "INSERT INTO S02DINC (cNroInc, cAccTom, cDescri, cCodPer, dFecha1, cCondic, cTipo, cCodUsu) VALUES 
                      ('$this->pcNroInc','$i[1]', '$i[2]', '$i[3]', TO_DATE('$i[4]', 'YYYY-MM-DD'), '$i[5]', 'C', '$this->pcCodUsu')";
         } else {
            $lcSql = "INSERT INTO S02DINC (cNroInc, cAccTom, cDescri, cCodPer, dFecha1, dFecha2, cCondic, cTipo, cCodUsu) VALUES 
                      ('$this->pcNroInc','$i[1]', '$i[2]', '$i[3]', TO_DATE('$i[4]', 'YYYY-MM-DD'), SYSDATE, '$i[5]', 'C', '$this->pcCodUsu')";
         }
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            break;
         }
      }
      $loSql->omDisconnect();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR AL INGRESAR DETALLE CAUSA RAIZ</ERROR></DATA>';
         return false;
      }
      // Envio de emails
      $lo = new CEnvioEmails();
      $lo->pcNroInc = $this->pcNroInc;
      $lo->paData   = $this->paData;
      $llOk = $lo->omEnviar2();
      if (!$llOk) {
         $this->pcError = $lo->pcError;
         return false;
      }
      return true;
   }

   // Graba personal y unidades involucradas
   public function omGrabarPUInvInc() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      } if (empty($this->paCodPin) or fxCount($this->paCodPin) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DETALLE DE PERSONAL INVOLUCRADO</ERROR></DATA>';
         return false;
      } if (empty($this->paCodUin) or fxCount($this->paCodUin) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DETALLE DE UNIDADES INVOLUCRADAS</ERROR></DATA>';
         return false;
      }
      //no se repita personal
      $laCodPin = null;
      foreach ($this->paCodPin as $i) {
         if ($i[1] != '999999')
            $laCodPin[] = array($i[1]);
      }
      $llok = fxValRepeated($laCodPin, 0);
      if ($llok) {
         $this->pcError = '<DATA><ERROR>EL PERSONAL INVOLUCRADO NO SE PUEDE REPETIR</ERROR></DATA>';
         return false;
      }
      foreach($this->paCodPin as $i) {
         if (empty($i[7]) && substr($i[1],0,5) == '99999') {
            $this->pcError = '<DATA><ERROR>NUMERO DE DNI VACIO</ERROR></DATA>';
            return false;
         } elseif (empty($i[8]) && substr($i[1],0,5) == '99999') {
            $this->pcError = '<DATA><ERROR>NOMBRE DE PERSONA INVOLUCRADA VACIA</ERROR></DATA>';
            return false;
         } elseif (empty($i[9])) {
            $this->pcError = '<DATA><ERROR>ACTIVIDAD DE PERSONA INVOLUCRADA VACIA</ERROR></DATA>';
            return false;
         } elseif (empty($i[10])) {
            $this->pcError = '<DATA><ERROR>UBICACION DE PERSONA INVOLUCRADA VACIA</ERROR></DATA>';
            return false;
         } elseif (substr($i[1],0,5) != '99999' and $i[1] == $i[11]) {
            $this->pcError = '<DATA><ERROR>PERSONAL INVOLUCRADO NO PUEDE SER SUPERVISOR DE SI MISMO</ERROR></DATA>';
            return false;
         }
      }
      // Valida que no se repita unidad
      $i = 0;
      foreach($this->paCodUin as $laFila) {
         $i++;
         for ($j = $i + 1; $j <= fxCount($this->paCodUin); $j++) {
             if ($laFila[1] == $this->paCodUin[$j][1] && substr($laFila[1],0,5) != '99999') {
                $this->pcError = '<DATA><ERROR>LA UNIDAD INVOLUCRADA NO SE PUEDE REPETIR</ERROR></DATA>';
                return false;
             }
         }
      }
     // Valida datos de unidades
      foreach($this->paCodUin as $i) {
         if (empty($i[2]) && substr($i[1],0,5) == '99999') {
            $this->pcError = '<DATA><ERROR>DESCRIPCION DE UNIDAD VACIA</ERROR></DATA>';
            return false;
         } elseif (empty($i[3])) {
            $this->pcError = '<DATA><ERROR>DAÑO DE UNIDAD INVOLUCRADA VACIA</ERROR></DATA>';
            return false;
         } elseif (empty($i[4])) {
            $this->pcError = '<DATA><ERROR>FUNCION DE EQUIPO O UNIDAD NO DEFINIDA</ERROR></DATA>';
            return false;
         }
      }
     // Conectar DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $llOk = $loSql->omExec("DELETE FROM S02DPIN where cNroInc = '$this->pcNroInc'");
      if (!$llOk) {
         $loSql->rollback();
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR DETALLE PERSONAL INVOLUCRADO</ERROR></DATA>';
         return false;
      }
      foreach($this->paCodPin as $i) {
         $lcEntren = ($i[12] == 'S') ? 'S' : 'N';
         if ($i[1] == '999999') {
            $lcSql = "INSERT INTO S02DPIN (cNroInc, cCodPer, cCodEmp, cNroDni, cNombre, cActivi, cUbicac, cPuesto, cEntren, cCodUsu) VALUES ('$this->pcNroInc', '$i[1]', '999', '$i[7]', UPPER('$i[8]'), UPPER('$i[9]'), UPPER('$i[10]'), ' ', '$lcEntren', '$this->pcCodUsu')";
         } else {
            $lcSql = "INSERT INTO S02DPIN (cNroInc, cCodPer, cCodEmp, cCarDis, cActivi, cUbicac, cSuperv, cPuesto, cEntren, cCodUsu) VALUES ('$this->pcNroInc','$i[1]', '$i[3]', '$i[5]', UPPER('$i[9]'), UPPER('$i[10]'), '$i[11]', ' ', '$lcEntren', '$this->pcCodUsu')";
         }
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR PERSONAL INVOLUCRADO</ERROR></DATA>';
            return false;
         }
      }
      $llOk = $loSql->omExec("DELETE FROM S02DUIN WHERE cNroInc = '$this->pcNroInc'");
      if (!$llOk) {
         $loSql->rollback();
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR DETALLE DE UNIDADES INVOLUCRADAS</ERROR></DATA>';
         return false;
      }
      foreach($this->paCodUin as $i) {
         if (substr($i[1],0,5) == '99999') {
            $lcSql = "INSERT INTO S02DUIN (cNroInc, cCodUni, cDescri, cDamage, cFuncio, cCodUsu) VALUES ('$this->pcNroInc','$i[1]', UPPER('$i[2]'), UPPER('$i[3]'), UPPER('$i[4]'), '$this->pcCodUsu')";
            $llOk = $loSql->omExec($lcSql);
         } else {
            $lcSql = "INSERT INTO S02DUIN (cNroInc, cCodUni, cDamage, cFuncio, cCodUsu) VALUES ('$this->pcNroInc','$i[1]', UPPER('$i[3]'), UPPER('$i[4]'), '$this->pcCodUsu')";
            $llOk = $loSql->omExec($lcSql);
         }
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR UNIDADES INVOLUCRADAS</ERROR></DATA>';
            return false;
         }
      }
      $loSql->omDisconnect();
      return $llOk;
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
      $RS = $loSql->omExec("SELECT TO_CHAR(dFecInc, 'yyyy-mm-dd'), cHorInc, cCodEmp, cCodPry, cLugar, cClaInc, cTipInc, cCodPe1, cArea1, cCodPe2, cArea2, mDescri, mSugere, cKilome, cCodPrc, cSubDiv, cRepFis, cEstaci FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      $this->pcFecInc = $laFila[0];
      if (empty($this->pcFecInc)) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcHorInc = $laFila[1];
      $this->pcCodEmp = $laFila[2];
      $this->pcCodPry = $laFila[3];
      $this->pcLugar  = $laFila[4];
      $this->pcClaInc = $laFila[5];
      $this->pcTipInc = $laFila[6];
      $this->pcCodPe1 = $laFila[7];
      $this->pcArea1  = $laFila[8];
      $this->pcCodPe2 = $laFila[9];
      $this->pcArea2 =  $laFila[10];
      $this->pmDescri = $laFila[11]->load();
      $this->pmSugere = $laFila[12]->load();
      $this->pcKilome = $laFila['CKILOME'];
      $this->pcCodPrc = $laFila['CCODPRC'];
      $this->pcSubDiv = $laFila['CSUBDIV'];
      $this->pcRepFis = $laFila['CREPFIS'];
      $this->pcEstaci = $laFila['CESTACI'];
      $loSql->omDisconnect();
      return true;
   }

   // Aplica numero de incidente
   public function omAplicar1() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga incidente
      $RS = $loSql->omExec("SELECT TO_CHAR(dFecInc, 'yyyy-mm-dd'), cHorInc, cCodEmp, cCodPry, cLugar, cClaInc, cTipInc, cCodPe1, cArea1, cCodPe2, cArea2, mDescri, mSugere, cConsec, cPotenc, mAnaPre, cReqInv, cDesEmp, cDesPry, cNombr1, cNombr2, cCodPer, mConclu, nCosto, B.cDescri AS cDesDiv, C.cDescri AS cDesEst, cKilome FROM V_S02MINC A LEFT OUTER JOIN V_S02TTAB B ON A.cSubDiv= B.cCodigo AND B.cCodTab='020' LEFT OUTER JOIN V_S02TTAB C ON A.cEstaci= C.cCodigo AND C.cCodTab='021' WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO EXISTE</ERROR></DATA>';
         return false;
      }
      // Lugar
      $RS = $loSql->omExec("SELECT cDescri FROM V_S02TTAB WHERE cCodTab = '008' AND SUBSTR(cCodigo, 1, 1) = '$laFila[4]'");
      $laTmp = $loSql->fetch($RS);
      if (empty($laTmp[0]))
         $laTmp[0] = '* NO DEFINIDO *';
      $lcDesLug = $laTmp[0];
      // Clasificacion
      $RS = $loSql->omExec("SELECT cDescri FROM V_S02TTAB WHERE cCodTab = '031' AND SUBSTR(cCodigo, 1, 1) = '$laFila[5]'");
      $laTmp = $loSql->fetch($RS);
      if (empty($laTmp[0]))
         $laTmp[0] = '* NO DEFINIDO *';
      $lcDesCla = $laTmp[0];
      // Tipo
      $RS = $loSql->omExec("SELECT cDescri FROM V_S02TTAB WHERE cCodTab = '032' AND SUBSTR(cCodigo, 1, 1) = '$laFila[6]'");
      $laTmp = $loSql->fetch($RS);
      if (empty($laTmp[0]))
         $laTmp[0] = '* NO DEFINIDO *';
      $lcDesTip = $laTmp[0];
      // Area 1
      $RS = $loSql->omExec("SELECT cDescri FROM V_S02TTAB WHERE cCodTab = '030' AND SUBSTR(cCodigo, 1, 2) = '$laFila[8]'");
      $laTmp = $loSql->fetch($RS);
      if (empty($laTmp[0]))
         $laTmp[0] = '* NO DEFINIDO *';
      $lcArea1 = $laTmp[0];
      // Area 2
      $RS = $loSql->omExec("SELECT cDescri FROM V_S02TTAB WHERE cCodTab = '030' AND SUBSTR(cCodigo, 1, 2) = '$laFila[10]'");
      $laTmp = $loSql->fetch($RS);
      if (empty($laTmp[0]))
         $laTmp[0] = '* NO DEFINIDO *';
      $lcArea2 = $laTmp[0];
     $lcEstaci =substr($laFila['CDESEST'],0,1)=='*'?'NC':$laFila['CDESEST'];
      // Dato de salida
      $this->pmDatos = 'FECHA        : '.$laFila[0].chr(13);
      $this->pmDatos .= 'HORA         : '.$laFila[1].chr(13);
      $this->pmDatos .= 'EMPRESA      : '.$laFila[17].chr(13);
      $this->pmDatos .= 'PROYECTO     : '.$laFila[18].chr(13);
      $this->pmDatos .= 'LUGAR        : '.$lcDesLug.'/'.$lcEstaci.'/'.$laFila['CKILOME'].chr(13);
      $this->pmDatos .= 'SUBDIVISION  : '.$laFila['CDESDIV'].chr(13);
      $this->pmDatos .= 'CLASIFICACIÓN: '.$lcDesCla.chr(13);
      $this->pmDatos .= 'TIPO         : '.$lcDesTip.chr(13);
      $this->pmDatos .= 'REPORTE DE   : '.$laFila[19].chr(13);
      $this->pmDatos .= 'AREA (R)     : '.$lcArea1.chr(13);
      $this->pmDatos .= 'COMUNICADO   : '.$laFila[20].chr(13);
      $this->pmDatos .= 'AREA (C)     : '.$lcArea2.chr(13);
      $this->pmDatos .= 'DESCRIPCIÓN  : '.$laFila[11]->load().chr(13);
      $this->pmDatos .= 'SUGERENCIA   : '.$laFila[12]->load().chr(13);
      $this->pmDatos .= 'CONCLUSIONES : '.$laFila['MCONCLU']->load().chr(13);
      $this->pcConsec = $laFila[13];
      $this->pcPotenc = $laFila[14];
      $this->pmAnaPre = $laFila['MANAPRE']->load();
      $this->pcReqInv = $laFila[16];
      $this->pcCodPer = $laFila[21];
      $this->pnCosto  = $laFila['NCOSTO'];
     // Carga Evento Cronologico y Descripcion del Incidente
      $this->pmEveCro = $this->pmDesDet = ' ';
     $lcSql ="SELECT cNroInc, mDesDet, mEveCro FROM S02DCAU WHERE cNroInc = '$this->pcNroInc'";
      $RS = $loSql->omExec($lcSql);
     $laFila = $loSql->fetch($RS);
     if (!empty($laFila[0])) {
        $this->pmEveCro = $laFila['MEVECRO']->load();
        $this->pmDesDet = $laFila['MDESDET']->load();
      }
      // Carga detalle de evaluaciones
      $lcSql ="SELECT cAccTom, cDescri, cCodPer, TO_CHAR(dFecha1, 'yyyy-mm-dd'), TO_CHAR(dFecha2, 'yyyy-mm-dd'), cCondic, nSerial FROM S02DINC WHERE cNroInc = '$this->pcNroInc' AND cTipo = 'I'";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($i, $laFila[0], $laFila[1], $laFila[2], $laFila[3], $laFila[4], $laFila[5], $laFila[6]);
      }
      return true;
   }

   public function omAplicar2() {
      $llOk = $this->omAplicar1();
      if (!$llOk) {
         return false;
      } elseif ($this->pcReqInv == 'N') {
         $this->pcError = '<DATA><ERROR>INCIDENTE NO REQUIERE INVESTIGACION</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga detalle de personal involucrado
      $lcSql = "SELECT A.cCodPer, B.cNombre AS cDesNom,  A.cCodEmp, C.cDescri, A.cCarDis, D.cDescri AS cDesDis, 
                A.cNroDni,A.cNombre, A.cActivi, A.cUbicac, A.cSuperv, A.cEntren FROM S02DPIN A
                LEFT JOIN S02MPER B ON A.cCodPer = B.cCodPer
                LEFT JOIN S02MEMP C ON A.cCodEmp = C.cCodEmp
                LEFT JOIN V_S02TTAB D ON A.cCarDis = D.cCodigo AND D.cCodTab = '045'
                WHERE cNroInc = '$this->pcNroInc'";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPin[$i] = array($i, $laFila[CCODPER], $laFila[CDESNOM], $laFila[CCODEMP], $laFila[CDESCRI],
                                     $laFila[CCARDIS], $laFila[CDESDIS], $laFila[CNRODNI], $laFila[CNOMBRE],
                                     $laFila[CACTIVI], $laFila[CUBICAC], $laFila[CSUPERV], $laFila[CENTREN]);
      }
      // Carga detalle de unidades
      $lcSql ="SELECT cCodUni, cDescri, cDamage, cFuncio FROM V_S02DUIN WHERE cNroInc = '$this->pcNroInc'";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodUin[$i] = array($i, $laFila[0], $laFila[1], $laFila[2], $laFila[3]);
      }
      $loSql->omDisconnect();
      return true;
   }

   // Graba prueba de eficiencia de conduccion
   public function omGrabarPruebaConduccion() {
      if (empty($this->paDataP) or fxCount($this->paDataP) == 0) {
         $this->pcError = '<DATA><ERROR>DATOS DE PERSONAL EVALUADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->paData) or fxCount($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>DATOS DE NORMAS INCUMPLIDAS NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (!fxValDate($this->pcFecInc)) {
         $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE EVALUACION ['.$this->pcFecInc.']</ERROR></DATA>';
         return false;
      } else if (!fxValTime($this->pcHorInc)) {
         $this->pcError = '<DATA><ERROR>ERROR EN HORA DE PRUEBA-EVALUACION ['.$this->pcHorInc.']</ERROR></DATA>';
         return false;
      } else if (empty($this->pcKilome)) {
          $this->pcError = '<DATA><ERROR>KILOMETRAJE (PK) INICIAL NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcKilomf)) {
          $this->pcError = '<DATA><ERROR>KILOMETRAJE (PK) FINAL NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if ($this->pcKilome == $this->pcKilomf) {
          $this->pcError = '<DATA><ERROR>ERROR: KILOMETRAJE (PK) INICIAL ES IGUAL A KILOMETRAJE FINAL</ERROR></DATA>';
          return false;
      } else if (empty($this->pcTren)) {
          $this->pcError = '<DATA><ERROR>TREN NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcEstaci)) {
          $this->pcError = '<DATA><ERROR>ESTACION INICIAL NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (empty($this->pcEstacf)) {
          $this->pcError = '<DATA><ERROR>ESTACION FINAL NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if ($this->pcEstaci == $this->pcEstacf) {
          $this->pcError = '<DATA><ERROR>ERROR: ESTACION INICIAL ES IGUAL ESTACION FINAL</ERROR></DATA>';
          return false;
      } else if (!fxValTime($this->pcHorSer)) {
          $this->pcError = '<DATA><ERROR>HORAS DE SERVICIOS INVALIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcResult)) {
          $this->pcError = '<DATA><ERROR>RESULTADO NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcTipVag)) {
          $this->pcError = '<DATA><ERROR>TIPO DE VAGON NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcSuperv)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE SUPERVISOR NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodUni)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodUsu)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (!$this->plNuevo and empty($this->pcNroInc)) {
          $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
          return false;
      }
      // Valida repeticiones en arreglos
      $llOk = fxValRepeated($this->paData, 1);
      if ($llOk) {
         $this->pcError = '<DATA><ERROR>ERROR: HAY NORMAS REPETIDAS</ERROR></DATA>';
         return false;
      }
      $llOk = fxValRepeated($this->paDataP, 2);
      if ($llOk) {
         $this->pcError = '<DATA><ERROR>ERROR: PUESTO DE PERSONAL REPETIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      if (!$this->plNuevo) {
         $RS = $loSql->omExec("SELECT cNroInc FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO EXISTE</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $lcSql = "UPDATE S02MINC SET dFecInc = TO_DATE('$this->pcFecInc', 'yyyy-mm-dd'), cHorInc = '$this->pcHorInc', cHorSer = '$this->pcHorSer', cKilome = '$this->pcKilome', cKilomf = '$this->pcKilomf', cTren = UPPER('$this->pcTren'), cEstaci = '$this->pcEstaci', cEstacf = '$this->pcEstacf', cResult = '$this->pcResult', cTipVag = '$this->pcTipVag', cSuperv = '$this->pcSuperv', cCodUni = '$this->pcCodUni', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cNroInc = '$this->pcNroInc'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR EVALUACION</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM S02DNOR WHERE cNroInc = '$this->pcNroInc'");
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR NORMAS INCUMPLIDAS ANTIGUAS</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM S02DPER WHERE cNroInc = '$this->pcNroInc'");
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR PERSONAL EVALUADO ANTIGUO</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      } else {
         $RS = $loSql->omExec("SELECT MAX(cNroInc) FROM S02MINC WHERE cNroInc LIKE 'C%'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $lcNroInc = '00000';
         } else {
            $lcNroInc = substr($laFila[0], 1);
         }
         $lnNroEva = (int)$lcNroInc + 1;
         $lcNroInc = (string)$lnNroEva;
         $this->pcNroInc = 'C'.right('0000'.trim($lcNroInc), 5);
         $lcSql = "INSERT INTO S02MINC (cNroInc, dFecInc, cHorInc, cHorSer, cKilome, cKilomf, cTren, cCompos, cSubDiv, cEstaci, cEstacf, cResult, cTipVag, cSuperv, cCodUni, cCodUsu) VALUES ('$this->pcNroInc', TO_DATE('$this->pcFecInc', 'yyyy-mm-dd'), '$this->pcHorInc', '$this->pcHorSer', '$this->pcKilome', '$this->pcKilomf', UPPER('$this->pcTren'), ' ', ' ', '$this->pcEstaci', '$this->pcEstacf', '$this->pcResult', '$this->pcTipVag', '$this->pcSuperv', '$this->pcCodUni', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INGRESAR PRUEBA-EVALUACION</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      }
      foreach($this->paData as $i) {
         $lcSql = "INSERT INTO S02DNOR (cNroInc, cCodNor, cCodUsu) VALUES ('$this->pcNroInc', '$i[1]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR NORMAS INCUMPLIDAS</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      }
      foreach($this->paDataP as $i) {
         $lcSql = "INSERT INTO S02DPER (cNroInc, cCodPer, cPuesto, cCodUsu) VALUES ('$this->pcNroInc', '$i[1]', '$i[2]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR PERSONAL EVALUADO</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      }
      $loSql->omDisconnect();
      return true;
   }

   // Graba prueba de velocidad por radar
   public function omGrabarPruebaRadar() {
      if (empty($this->paDataP) or fxCount($this->paDataP) == 0) {
         $this->pcError = '<DATA><ERROR>DATOS DE PERSONAL EVALUADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->paData) or fxCount($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>DATOS DE NORMAS INCUMPLIDAS NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (!fxValDate($this->pcFecInc)) {
         $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE EVALUACION ['.$this->pcFecInc.']</ERROR></DATA>';
         return false;
      } else if (!fxValTime($this->pcHorInc)) {
         $this->pcError = '<DATA><ERROR>ERROR EN HORA DE PRUEBA-EVALUACION ['.$this->pcHorInc.']</ERROR></DATA>';
         return false;
      } else if (empty($this->pcKilome)) {
         $this->pcError = '<DATA><ERROR>KILOMETRAJE (PK) INICIAL NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcTren)) {
         $this->pcError = '<DATA><ERROR>TREN NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcResult)) {
         $this->pcError = '<DATA><ERROR>RESULTADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcTipVag)) {
         $this->pcError = '<DATA><ERROR>TIPO DE VAGON NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcSuperv)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PRIMER SUPERVISOR NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcSuper1)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE SEGUNDO SUPERVISOR NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUni)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE UNIDAD NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCompos)) {
          $this->pcError = '<DATA><ERROR>COMPOSICIÓN NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (empty($this->pcSubDiv)) {
          $this->pcError = '<DATA><ERROR>SUBDIVISIÓN NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCondic)) {
          $this->pcError = '<DATA><ERROR>CONDICIÓN DE VÍA NO DEFINIDAS</ERROR></DATA>';
          return false;
      } else if (empty($this->pcConCli)) {
          $this->pcError = '<DATA><ERROR>CONDICIÓN CLIMÁTICA NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (empty($this->pcVelAut)) {
          $this->pcError = '<DATA><ERROR>VELOCIDAD MÁXIMA AUTORIZADA NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (empty($this->pcVelReg)) {
          $this->pcError = '<DATA><ERROR>VELOCIDAD REGISTRADA NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (!($this->pcRestri == 'S' OR $this->pcRestri == 'N')) {
          $this->pcError = '<DATA><ERROR>ERROR: RESTRICCION DEBE SER [S] O [N]</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (!$this->plNuevo and empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Valida repeticiones en arreglos
      $llOk = fxValRepeated($this->paData, 1);
      if ($llOk) {
         $this->pcError = '<DATA><ERROR>ERROR: HAY NORMAS REPETIDAS</ERROR></DATA>';
         return false;
      }
      $llOk = fxValRepeated($this->paDataP, 2);
      if ($llOk) {
         $this->pcError = '<DATA><ERROR>ERROR: PUESTO DE PERSONAL REPETIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      if (!$this->plNuevo) {
         $RS = $loSql->omExec("SELECT cNroInc FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO EXISTE</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $lcSql = "UPDATE S02MINC SET dFecInc = TO_DATE('$this->pcFecInc', 'yyyy-mm-dd'), cHorInc = '$this->pcHorInc', cKilome = '$this->pcKilome', cTren = UPPER('$this->pcTren'), cResult = '$this->pcResult', cTipVag = '$this->pcTipVag', cSuperv = '$this->pcSuperv', cSuper2 = '$this->pcSuper1', cCodUni = '$this->pcCodUni', cCondic = '$this->pcCondic', cConCli = '$this->pcConCli', cVelAut = '$this->pcVelAut', cVelReg = '$this->pcVelReg', cRestri = '$this->pcRestri', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cNroInc = '$this->pcNroInc'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR EVALUACION</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM S02DNOR WHERE cNroInc = '$this->pcNroInc'");
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR NORMAS INCUMPLIDAS ANTIGUAS</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM S02DPER WHERE cNroInc = '$this->pcNroInc'");
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR PERSONAL EVALUADO ANTIGUO</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      } else {
         $RS = $loSql->omExec("SELECT MAX(cNroInc) FROM S02MINC WHERE cNroInc LIKE 'R%'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $lcNroInc = '00000';
         } else {
            $lcNroInc = substr($laFila[0], 1);
         }
         $lnNroEva = (int)$lcNroInc + 1;
         $lcNroInc = (string)$lnNroEva;
         $this->pcNroInc = 'R'.right('0000'.trim($lcNroInc), 5);
         $lcSql = "INSERT INTO S02MINC (cNroInc, dFecInc, cHorInc, cHorSer, cKilome, cTren, cCompos, cSubDiv, cEstaci, cResult, cTipVag, cSuperv, cSuper2, cCodUni, cCondic, cConCli, cVelAut, cVelReg, cRestri, cCodUsu) VALUES ('$this->pcNroInc', TO_DATE('$this->pcFecInc', 'yyyy-mm-dd'), '$this->pcHorInc', ' ', '$this->pcKilome', UPPER('$this->pcTren'), '$this->pcCompos', '$this->pcSubDiv', ' ', '$this->pcResult', '$this->pcTipVag', '$this->pcSuperv', '$this->pcSuper1', '$this->pcCodUni', '$this->pcCondic', '$this->pcConCli', '$this->pcVelAut', '$this->pcVelReg', '$this->pcRestri', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INGRESAR PRUEBA-EVALUACION DE CONTROL DE VELOCIDAD POR RADAR</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      }
      foreach($this->paData as $i) {
         $lcSql = "INSERT INTO S02DNOR (cNroInc, cCodNor, cCodUsu) VALUES ('$this->pcNroInc', '$i[1]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR NORMAS INCUMPLIDAS</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
      }
      foreach($this->paDataP as $i) {
         $lcSql = "INSERT INTO S02DPER (cNroInc, cCodPer, cPuesto, cCodUsu) VALUES ('$this->pcNroInc', '$i[1]', '$i[2]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR PERSONAL EVALUADO</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
       }
      $loSql->omDisconnect();
      return true;
   }
   
   public function omAplicar3() {
      $llOk = $this->omAplicar1();
      if (!$llOk) {
         return false;
      } elseif ($this->pcReqInv == 'N') {
         $this->pcError = '<DATA><ERROR>INCIDENTE NO REQUIERE INVESTIGACION</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
     $llOk = $this->mxFactoresRiesgo($loSql);
      if (!$llOk) {
        $loSql->omDisconnect();
         return false;
      }
      // Carga factores criticos de riesgo FCR procesos sub-estandares PSE-5
      $RS = $loSql->omExec("SELECT cCodFcr, mObserv FROM S02DFCR WHERE cNroInc = '$this->pcNroInc' AND cCodFcr LIKE 'PSE%' ORDER BY cCodFcr");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $lcObserv = $laFila[1]->load();
         $this->paData1[$i] = array($i, $laFila[0], $lcObserv);
      }
      // Carga factores criticos de riesgo FCR procesos sub-estandares CSE-6
      $RS = $loSql->omExec("SELECT cCodFcr, mObserv FROM S02DFCR WHERE cNroInc = '$this->pcNroInc' AND cCodFcr LIKE 'CSE%' ORDER BY cCodFcr");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $lcObserv = $laFila[1]->load();
         $this->paData2[$i] = array($i, $laFila[0], $lcObserv);
      }
      // Carga factores criticos de riesgo FCR factor personal FP-7
      $RS = $loSql->omExec("SELECT cCodFcr, mObserv FROM S02DFCR WHERE cNroInc = '$this->pcNroInc' AND cCodFcr LIKE 'FP%' ORDER BY cCodFcr");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $lcObserv = $laFila[1]->load();
         $this->paData3[$i] = array($i, $laFila[0], $lcObserv);
      }
      // Carga factores criticos de riesgo FCR factor trabajo FT-8
      $RS = $loSql->omExec("SELECT cCodFcr, mObserv FROM S02DFCR WHERE cNroInc = '$this->pcNroInc' AND cCodFcr LIKE 'FT%' ORDER BY cCodFcr");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $lcObserv = $laFila[1]->load();
         $this->paData4[$i] = array($i, $laFila[0], $lcObserv);
      }
      // Carga factores criticos de riesgo FCR factor control gestion CG-9
      $RS = $loSql->omExec("SELECT cCodFcr, mObserv FROM S02DFCR WHERE cNroInc = '$this->pcNroInc' AND cCodFcr LIKE 'CG%' ORDER BY cCodFcr");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $lcObserv = $laFila[1]->load();
         $this->paData5[$i] = array($i, $laFila[0], $lcObserv);
      }
     $this->mxComentarios($loSql);
      // Carga causas
      $RS = $loSql->omExec("SELECT cNroInc, mEveCro, mDesDet FROM S02DCAU WHERE cNroInc = '$this->pcNroInc'");
      $loSql->omDisconnect();
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0]))
         return true;
      $this->pmEveCro = $laFila[1]->load();
      $this->pmDesDet = $laFila[2]->load();
      return true;
   }
   protected function mxComentarios($p_oSql){
   // Trae comentarios de P/C subestandares y factores T/P
      $RS = $p_oSql->omExec("SELECT mComSbe, mComFac, mFacCri, mConGes, mConclu,mComCge FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $p_oSql->fetch($RS);
      $this->pmComSbe = $laFila[0]->load();
      $this->pmComFac = $laFila[1]->load();
      $this->pmFacCri = $laFila[2]->load();
      $this->pmConGes = $laFila[3]->load();
      $this->pmConclu = $laFila[4]->load();
      $this->pmComCge = $laFila[5]->load();
   }
   protected function mxFactoresRiesgo($p_oSql){
   // Carga Tablas procesos sub-estandares PSE-5
      $RS = $p_oSql->omExec("SELECT cCodFcr, cDescri FROM S02MFCR WHERE cCodFcr LIKE 'PSE%' ORDER BY cCodFcr");
      $i = 0;
      while ($laFila = $p_oSql->fetch($RS)) {
         $i++;
         $this->paFacCr1[$i] = array($laFila[0], trim($laFila[0]).' '.$laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>TABLA DE FACTORES CRITICOS DE RIESGO - PROCESOS SUBESTANDAR NO ESTA DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Carga Tablas condiciones sub-estandares CSE-5
      $RS = $p_oSql->omExec("SELECT cCodFcr, cDescri FROM S02MFCR WHERE cCodFcr LIKE 'CSE%' ORDER BY cCodFcr");
      $i = 0;
      while ($laFila = $p_oSql->fetch($RS)) {
         $i++;
         $this->paFacCr2[$i] = array($laFila[0], trim($laFila[0]).' '.$laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>TABLA DE FACTORES CRITICOS DE RIESGO - CONDICIONES SUBESTANDAR NO ESTA DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Carga tabla factor personal FP-7
      $RS = $p_oSql->omExec("SELECT cCodFcr, cDescri FROM S02MFCR WHERE cCodFcr LIKE 'FP%' ORDER BY cCodFcr");
      $i = 0;
      while ($laFila = $p_oSql->fetch($RS)) {
         $i++;
         $this->paFacCr3[$i] = array($laFila[0], trim($laFila[0]).' '.$laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>TABLA DE FACTORES CRITICOS DE RIESGO - FACTOR PERSONAL NO ESTA DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Carga tabla factor personal FT-8
      $RS = $p_oSql->omExec("SELECT cCodFcr, cDescri FROM S02MFCR WHERE cCodFcr LIKE 'FT%' ORDER BY cCodFcr");
      $i = 0;
      while ($laFila = $p_oSql->fetch($RS)) {
         $i++;
         $this->paFacCr4[$i] = array($laFila[0], trim($laFila[0]).' '.$laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>TABLA DE FACTORES CRITICOS DE RIESGO - FACTOR DE TRABAJO NO ESTA DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Carga tabla control de gestion CG-9
      $RS = $p_oSql->omExec("SELECT cCodFcr, cDescri FROM S02MFCR WHERE cCodFcr LIKE 'CG%' ORDER BY cCodFcr");
      $i = 0;
      while ($laFila = $p_oSql->fetch($RS)) {
         $i++;
         $this->paFacCr5[$i] = array($laFila[0], trim($laFila[0]).' '.$laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>TABLA DE FACTORES CRITICOS DE RIESGO - CONTROL DE GESTION</ERROR></DATA>';
         return false;
      }
     return true;
   }
   // Graba prueba de eficiencia de conduccion
   public function omGrabarCausas() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->paDataF) or fxCount($this->paDataF) == 0) {
         $this->pcError = '<DATA><ERROR>DATOS DE FACTORES CRITICOS NO DEFINIDO</ERROR></DATA>';
         return false;
      }   
      // Verifica que no hayan valores repetidos
      $llOk = fxValRepeated($this->paDataF, 1);
      if ($llOk) {
         $this->pcError = '<DATA><ERROR>FACTOR CRITICO DE RIESGO REPETIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Graba factores criticos de riesgo
      $llOk = $loSql->omExec("DELETE FROM S02DFCR WHERE cNroInc = '$this->pcNroInc'");
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR FACTORES CRITICOS DE RIESGO ANTERIORES</ERROR></DATA>';
         return false;
      }
      foreach($this->paDataF as $i) {
         $lcSql = "INSERT INTO S02DFCR (cNroInc, cCodFcr, cEstado, cCodUsu) VALUES ('$this->pcNroInc', '$i[1]', 'A', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
      }
      // Graba causas
      $llOk = $loSql->omExec("DELETE FROM S02DCAU WHERE cNroInc = '$this->pcNroInc'");
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR CAUSAS ANTERIORES</ERROR></DATA>';
         return false;
      }
      $lcSql ="INSERT INTO S02DCAU (cNroInc, mCausa1, mCausa2, mCausa3, mCausa4, mCausa5, mComen1, mComen2, mComen3, mComen4, mComen5, mEveCro, mFacCri, cCodUsu) VALUES ('$this->pcNroInc', '$this->pmCausa1', '$this->pmCausa2', '$this->pmCausa3', '$this->pmCausa4', '$this->pmCausa5', '$this->pmComen1', '$this->pmComen2', '$this->pmComen3', '$this->pmComen4', '$this->pmComen5', ' ', '$this->pmFacCri', '$this->pcCodUsu')";
      $llOk = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      if (!$llOk)
         $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR CAUSAS</ERROR></DATA>';
      return $llOk;
   }
    
   public function omGrabarCausasInmediatas() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->paData1) or fxCount($this->paData1) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS DE PROCESOS SUBESTANDARES</ERROR></DATA>';
         return false;
      } else if (empty($this->paData2) or fxCount($this->paData2) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS DE CONDICION SUBESTANDAR</ERROR></DATA>';
         return false;
      } else if (empty($this->pmComSbe)) {
         $this->pcError = '<DATA><ERROR>COMENTARIOS DE PROCESOS Y CONDICIONES SUBESTANDAR VACIO</ERROR></DATA>';
         return false;
      }   
      $llOk = fxValRepeated($this->paData1, 1);
      if ($llOk) {
         $this->pcError = '<DATA><ERROR>PROCESOS SUBESTANDAR REPETIDO</ERROR></DATA>';
         return false;
      }
      $llOk = fxValRepeated($this->paData2, 1);
      if ($llOk) {
         $this->pcError = '<DATA><ERROR>CONDICION SUBESTANDAR REPETIDO</ERROR></DATA>';
         return false;
      }
     foreach($this->paData1 as $i){
       if(empty($i[2])){
         $this->pcError = '<DATA><ERROR>DESCRIPCION DE COMENTARIOS VACIO</ERROR></DATA>';
         return false;
         }
     }
     foreach($this->paData2 as $i){
       if(empty($i[2])){
         $this->pcError = '<DATA><ERROR>DESCRIPCION DE COMENTARIOS VACIO</ERROR></DATA>';
         return false;
         }
     }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Elimina anteriores procesos sub-estandares
      $llOk = $loSql->omExec("DELETE FROM S02DFCR WHERE cNroInc = '$this->pcNroInc' AND cCodFcr LIKE 'PSE%'");
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR FACTORES CRITICOS DE RIESGO ANTERIORES</ERROR></DATA>';
         return false;
      }
      // Elimina anteriores condiciones sub-estandares
      $llOk = $loSql->omExec("DELETE FROM S02DFCR WHERE cNroInc = '$this->pcNroInc' AND cCodFcr LIKE 'CSE%'");
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR FACTORES CRITICOS DE RIESGO ANTERIORES</ERROR></DATA>';
         return false;
      }
      foreach($this->paData1 as $i) {
         $lcSql = "INSERT INTO S02DFCR (cNroInc, cCodFcr, mObserv, cEstado, cCodUsu) VALUES ('$this->pcNroInc', '$i[1]', '$i[2]', 'A', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
      }
      foreach($this->paData2 as $i) {
         $lcSql = "INSERT INTO S02DFCR (cNroInc, cCodFcr, mObserv, cEstado, cCodUsu) VALUES ('$this->pcNroInc', '$i[1]', '$i[2]', 'A', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
      }
      $llOk = $loSql->omExec("UPDATE S02MINC SET mComSbe = '$this->pmComSbe', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cNroInc = '$this->pcNroInc'");
      $loSql->omDisconnect();
      if (!$llOk)
         $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR CAUSAS</ERROR></DATA>';
      return $llOk;
   }

    public function omGrabarCausasBasicas() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->paData3) or fxCount($this->paData3) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS DE FACTORES PERSONALES</ERROR></DATA>';
         return false;
      } else if (empty($this->paData4) or fxCount($this->paData4) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS DE FACTORES DE TRABAJO</ERROR></DATA>';
         return false;
      } else if (empty($this->pmComFac)) {
         $this->pcError = '<DATA><ERROR>COMENTARIOS DE FACTORES PERSONALES/TRABAJO VACIO</ERROR></DATA>';
         return false;
      }   
      $llOk = fxValRepeated($this->paData3, 1);
      if ($llOk) {
         $this->pcError = '<DATA><ERROR>FACTORES PERSONALES REPETIDO</ERROR></DATA>';
         return false;
      }
      $llOk = fxValRepeated($this->paData4, 1);
      if ($llOk) {
         $this->pcError = '<DATA><ERROR>FACTORES DE TRABAJO REPETIDO</ERROR></DATA>';
         return false;
      }
     foreach($this->paData3 as $i){
       if(empty($i[2])){
         $this->pcError = '<DATA><ERROR>DESCRIPCION DE COMENTARIOS VACIO</ERROR></DATA>';
         return false;
         }
     }
     foreach($this->paData4 as $i){
       if(empty($i[2])){
         $this->pcError = '<DATA><ERROR>DESCRIPCION DE COMENTARIOS VACIO</ERROR></DATA>';
         return false;
         }
     }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Elimina anteriores procesos sub-estandares
      $llOk = $loSql->omExec("DELETE FROM S02DFCR WHERE cNroInc = '$this->pcNroInc' AND cCodFcr LIKE 'FP%'");
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR FACTORES PERSONALES ANTERIORES</ERROR></DATA>';
         return false;
      }
      // Elimina anteriores condiciones sub-estandares
      $llOk = $loSql->omExec("DELETE FROM S02DFCR WHERE cNroInc = '$this->pcNroInc' AND cCodFcr LIKE 'FT%'");
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR FACTORES DE TRABAJO ANTERIORES</ERROR></DATA>';
         return false;
      }
      foreach($this->paData3 as $i) {
         $lcSql = "INSERT INTO S02DFCR (cNroInc, cCodFcr, mObserv, cEstado, cCodUsu) VALUES ('$this->pcNroInc', '$i[1]', '$i[2]', 'A', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
      }
      foreach($this->paData4 as $i) {
         $lcSql = "INSERT INTO S02DFCR (cNroInc, cCodFcr, mObserv, cEstado, cCodUsu) VALUES ('$this->pcNroInc', '$i[1]', '$i[2]', 'A', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
      }
      $llOk = $loSql->omExec("UPDATE S02MINC SET mComFac = '$this->pmComFac', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cNroInc = '$this->pcNroInc'");
      $loSql->omDisconnect();
      if (!$llOk)
         $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR CAUSAS</ERROR></DATA>';
      return $llOk;
   }

   public function omGrabarControlGestion() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->paData5) or fxCount($this->paData5) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS DE CONTROL DE GESTION</ERROR></DATA>';
         return false;
      } else if (empty($this->pmComCge)) {
         $this->pcError = '<DATA><ERROR>LOS COMENTARIOS DE CONTROL DE GESTION NO ESTAN DEFINIDAS</ERROR></DATA>';
         return false;
      }   
      $llOk = fxValRepeated($this->paData5, 1);
      if ($llOk) {
         $this->pcError = '<DATA><ERROR>CONTROL DE GESTION REPETIDO</ERROR></DATA>';
         return false;
      }
     foreach($this->paData5 as $i){
       if(empty($i[2])){
         $this->pcError = '<DATA><ERROR>DESCRIPCION DE COMENTARIOS VACIO</ERROR></DATA>';
         return false;
         }
     }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Elimina anteriores procesos control de gestion
      $llOk = $loSql->omExec("DELETE FROM S02DFCR WHERE cNroInc = '$this->pcNroInc' AND cCodFcr LIKE 'CG%'");
      if (!$llOk) {
         $loSql->rollback();
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR CONTROL DE GESTION ANTERIORES</ERROR></DATA>';
         return false;
      }
      foreach($this->paData5 as $i) {
         $lcSql = "INSERT INTO S02DFCR (cNroInc, cCodFcr, mObserv, cEstado, cCodUsu) VALUES ('$this->pcNroInc', '$i[1]', '$i[2]', 'A', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR DETALLE DE CONTROL DE GESTION</ERROR></DATA>';
            return false;
         }
      }
      $llOk = $loSql->omExec("UPDATE S02MINC SET mComCge= '$this->pmComCge',cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cNroInc = '$this->pcNroInc'");
      if (!$llOk) {
         $loSql->rollback();
      }
      $loSql->omDisconnect();
      if (!$llOk)
         $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR TABLA DE INCIDENTES</ERROR></DATA>';
      return $llOk;
   }

   public function omGrabarFactorCritico() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pmFacCri)) {
         $this->pcError = '<DATA><ERROR>DETALLE DE FACTOR CRITICO VACIO</ERROR></DATA>';
         return false;
      }   
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $llOk = $loSql->omExec("UPDATE S02MINC SET mFacCri = '$this->pmFacCri', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cNroInc = '$this->pcNroInc'");
      $loSql->omDisconnect();
      if (!$llOk)
         $this->pcError = '<DATA><ERROR>ERROR AL GRABAR FACTOR CRITICO</ERROR></DATA>';
      return $llOk;
   }
   public function omGrabarConclusion() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pmConclu)) {
         $this->pcError = '<DATA><ERROR>DETALLE DE FACTOR CRITICO VACIO</ERROR></DATA>';
         return false;
      }   
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $llOk = $loSql->omExec("UPDATE S02MINC SET mConclu = '$this->pmConclu', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cNroInc = '$this->pcNroInc'");
      $loSql->omDisconnect();
      if (!$llOk)
         $this->pcError = '<DATA><ERROR>ERROR AL GRABAR CONCLUSION</ERROR></DATA>';
      return $llOk;
   }
   // Graba prueba de eficiencia de conduccion
   public function omGrabarAnalisisDetalladoIncidente() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE EVALUACION NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pmEveCro)) {
         $this->pcError = '<DATA><ERROR>DATOS DE EVENTOS CRONOLOGICOS NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pmDesDet)) {
         $this->pcError = '<DATA><ERROR>DATOS DE DESCRIPCION DE DETALLE DEL INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $RS = $loSql->omExec("SELECT nSerial FROM S02DCAU WHERE cNroInc = '$this->pcNroInc'");
     $laFila = $loSql->fetch($RS);
     if (empty($laFila[0])) {
         $lcSql ="INSERT INTO S02DCAU (cNroInc, mEveCro, mDesDet, cCodUsu) VALUES ('$this->pcNroInc', '$this->pmEveCro', '$this->pmDesDet', '$this->pcCodUsu')";
      } else {
         $lcSql ="UPDATE S02DCAU SET mEveCro = '$this->pmEveCro',  mDesDet = '$this->pmDesDet', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cNroInc = '$this->pcNroInc'";
      }
     $llOk = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR AL GRABAR DETALLE DEL INCIDENTE</ERROR></DATA>';
         return false;
      }
      return $llOk;
   }

   public function omEditarSeguimiento() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pnSerial)) {
         $this->pcError = '<DATA><ERROR>IDENTIFICADOR DE SEGUIMIENTO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "SELECT B.cNombre, TO_CHAR(A.dFecha1, 'YYYY-MM-DD'), A.cDescri, A.mComent, A.cCondic FROM S02DINC A INNER JOIN S02MPER B ON A.cCodPer = B.cCodPer WHERE cNroInc = '$this->pcNroInc' AND nSerial = $this->pnSerial";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR EN IDENTIFICADOR O NUMERO DE INCIDENTE</ERROR></DATA>';
         return false;
      }
      $this->pcNombre = $laFila[0];
      $this->pcFecha1 = $laFila[1];
      $this->pcDescri = $laFila[2];
      $this->pmComent = trim($laFila[3]->load());
      $this->pcCondic = $laFila[4];
      return true;
   }

   public function omGrabarSeguimiento() {
      if (empty($this->pmComent)) {
         $this->pcError = '<DATA><ERROR>ERROR: COMENTARIO VACIO</ERROR></DATA>';
         return false;
      } else if (empty($this->pnSerial)) {
         $this->pcError = '<DATA><ERROR>IDENTIFICADOR SE SEGUIMIENTO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCondic)) {
         $this->pcError = '<DATA><ERROR>CONDICION DE EVENTO DE SEGUIMIENTO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Valida condicion
      $RS = $loSql->omExec("SELECT cCodigo FROM V_S02TTAB WHERE cCodTab = '034' AND SUBSTR(cCodigo, 1, 1) = '$this->pcCondic'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CONDICION ['.$this->pcCondic.'] NO DEFINIDA EN TABLA [034]</ERROR></DATA>';
         return false;
      }
      $lcSql = "SELECT mComent FROM S02DINC WHERE nSerial = $this->pnSerial";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR EN IDENTIFICADOR, NO EXISTE</ERROR></DATA>';
         return false;
      }
      $lcComent = trim($laFila[0]->load()).chr(13).'FECHA: '.date('Y-m-d H:i:s').chr(13).trim($this->pmComent);
      $ldFecha2 = ($this->pcCondic == 'E') ? date("Y-m-d"): null;
      $lcSql = "UPDATE S02DINC SET mComent = '$lcComent', cCondic = '$this->pcCondic', dFecha2 = TO_DATE('$ldFecha2', 'YYYY-MM-DD') WHERE nSerial = $this->pnSerial";
      $llOk = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      if (!llOk) {
         $this->pcError = '<DATA><ERROR>ERROR AL GRABAR COMENTARIO</ERROR></DATA>';
      }
      return $llOk;
   }

   // Aplica numero de incidente para investigacion
   public function omAplicar4() {
      $llOk = $this->omAplicar1();
     if (!$llOk) {
         return false;
      } elseif ($this->pcReqInv == 'N') {
         $this->pcError = '<DATA><ERROR>INCIDENTE NO REQUIERE INVESTIGACION</ERROR></DATA>';
         return false;
      }
     $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga datos de investigacion
      $RS = $loSql->omExec("SELECT cNroInc, mTestig, cDamnif, cLesPer, mLesPer, cDamPro, cDamAmb, mDerram, cTiePer, mDamPro, mDamAmb,cPerPro FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $loSql->omDisconnect();
      $laFila = $loSql->fetch($RS);
     if (empty($laFila[0])) {
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO EXISTE</ERROR></DATA>';
         return false;
      }
     $this->pmTestig = $laFila[1]->load();
      $this->pcDamnif = $laFila[2];
      $this->pcLesPer = $laFila[3];
     $this->pmLesPer = $laFila[4]->load();
     $this->pcDamPro = $laFila[5];
      $this->pcDamAmb = $laFila[6];
      if (empty($laFila[7])){
         $this->pmDerram = '';
      }else{
         $this->pmDerram = $laFila[7]->load();}
      $this->pcTiePer = $laFila[8];
      if (empty($laFila[9]))
         $this->pmDamPro = '';
      else
         $this->pmDamPro = $laFila[9]->load();
     $this->pmDamAmb = $laFila[10]->load();
      $this->pcPerPro = $laFila['CPERPRO'];
      return true;
   }

   public function omGrabarInvestigacion() {
      $this->pmDerram = (empty($this->pmDerram)) ? 'N' : 'S';
      $this->pcTiePer = (empty($this->pcTiePer)) ? '00d00h' : $this->pcTiePer;
      $this->pnCosto  = (empty($this->pnCosto)) ? 0 : $this->pnCosto;
      $this->pmLesPer = (empty($this->pmLesPer)) ? ' ' : $this->pmLesPer;
      $this->pmDamPro = (empty($this->pmDamPro)) ? ' ' : $this->pmDamPro;
      $this->pmDamAmb = (empty($this->pmDamAmb)) ? ' ' : $this->pmDamAmb;
     if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO ESTA DEFINIDO</ERROR></DATA>';
         return false;
      } elseif (!fxTiempoPerdido($this->pcTiePer)) {
         $this->pcError = '<DATA><ERROR>ERROR EN TIEMPO PERDIDO [00d00h]</ERROR></DATA>';
         return false;
      } elseif (fxValMonto($this->pnCosto)) {
         $this->pcError = '<DATA><ERROR>VALOR DEL COSTO NO ES VALIDO</ERROR></DATA>';
         return false;
      } elseif (substr($this->pcDamAmb, 0, 1) == 'S') {
         $llOk = false;
         for ($i =1; $i <= 3; $i++) {
            if (substr($this->pcDamAmb, $i, 1) == 'S') {
               $llOk = true;
               break;
            }
         }
         if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR EN ELEMENTO DEL DAÑO AMBIENTAL</ERROR></DATA>';
            return false;
         }
         if ($this->pmDamAmb == ' ') {
            $this->pcError = '<DATA><ERROR>DESCRIPCION DEL DAÑO AMBIENTAL ESTA VACIO</ERROR></DATA>';
            return false;
         }
      } elseif ($this->pcLesPer == 'S') {
       if ($this->pmLesPer == ' ') {
            $this->pcError = '<DATA><ERROR>DESCRIPCION DE LESIONES PERSONALES ESTA VACIO</ERROR></DATA>';
            return false;
         }
      } elseif (substr($this->pcDamnif, 0, 1) == 'S') {
        $llOk = false;
         for ($i =0; $i <= 3; $i++) {
            if (substr($this->pcDamPro, $i, 1) == 'S') {
               $llOk = true;
               break;
            }
         }
         if (!$llOk) {
          $this->pcError = '<DATA><ERROR>ERROR EN ELEMENTO DE DAÑO PERSONAL/PROPIEDAD</ERROR></DATA>';
            return false;
         }
         if ($this->pmDamPro == ' ') {
            $this->pcError = '<DATA><ERROR>DESCRIPCION DEL DAÑO PERSONAL/PROPIEDAD ESTA VACIO</ERROR></DATA>';
            return false;
         }
      } elseif (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } elseif ($this->pcPerPro == "S") {
         if ((substr($this->pcTiePer, 0, 2) == '00' and substr($this->pcTiePer, 3, 2) == '00' and $this->pnCosto <= 0)) {
            $this->pcError = '<DATA><ERROR>ERROR EN ELEMENTOS DE PERDIDA EN EL PROCESO</ERROR></DATA>';
            return false;
         }
      }
     if (substr($this->pcDamAmb, 0, 1) == 'N') {
         $this->pcDamAmb = 'NNNN';
         $this->pmDamAmb = ' ';
      } elseif ($this->pcPerPro == 'N') {
         $this->pcTiePer = '00d00h';
         $this->pnCosto  = 0;
      } elseif (substr($this->pcLesPer, 0, 1) == 'S'){
         $this->pmLesPer = ' ';
      } elseif (substr($this->pcDamAmb, 0, 1) == 'N') {
         $this->pcDamAmb = 'NNNN';
         $this->pmDamAmb = ' ';
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "UPDATE S02MINC SET cDamnif = '$this->pcDamnif', cLesPer = '$this->pcLesPer', mLesPer = '$this->pmLesPer',
                cDamPro = '$this->pcDamPro', cDamAmb = '$this->pcDamAmb', mDerram = '$this->pmDerram', mDamPro = '$this->pmDamPro',
                cTiePer = '$this->pcTiePer', nCosto = '$this->pnCosto', mDamAmb = '$this->pmDamAmb', cCodUsu = '$this->pcCodUsu', cPerPro= '$this->pcPerPro',
                tModifi = SYSDATE WHERE cNroInc = '$this->pcNroInc'";
      $llOk = $loSql->omExec($lcSql);
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR AL GRABAR</ERROR></DATA>';
      }
      $loSql->omDisconnect();
      return $llOk;
   }
   
   // Aplica numero de incidente
   public function omAplicar5() {
      $llOk = $this->omAplicar1();
      $this->paData = null;
     if (!$llOk)
         return false;
      $lcFolder = './FILES/'.$this->pcNroInc.'/';
      if (!is_dir($lcFolder)) {
         return true;
      }
     $lcDir = opendir($lcFolder);
      while ($lcFile = readdir($lcDir)) {
         if (strpos($lcFile, '.pdf') >= 1 or strpos($lcFile, '.png') >= 1 or strpos($lcFile, '.jpg') >= 1 or strpos($lcFile, '.jpeg') >= 1)
            $this->paData[] = array($lcFolder.$lcFile, $lcFile);
      }
     return true;
   }
   
   // Aplica numero de incidente
   public function omAplicar6() {
      $llOk = $this->omAplicar1();
     if (!$llOk) {
         return false;
      } elseif ($this->pcReqInv == 'N') {
         $this->pcError = '<DATA><ERROR>INCIDENTE NO REQUIERE INVESTIGACION</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga datos de eficaz y comentarios
      $RS = $loSql->omExec("SELECT cEficaz, mComent FROM S02MINC WHERE cNroInc = '$this->pcNroInc'");
      $laFila = $loSql->fetch($RS);
      $this->pcEficaz = $laFila[0];
      $this->pmComent = $laFila[1]->load();
     // Carga seguimiento de causa-raiz
      $this->paData = null;
      $lcSql = "SELECT cAccTom, cDescri, cCodPer, TO_CHAR(dFecha1, 'yyyy-mm-dd'), TO_CHAR(dFecha2, 'yyyy-mm-dd'), cCondic, nSerial FROM S02DINC WHERE cNroInc = '$this->pcNroInc' AND cTipo = 'C'";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($i, $laFila[0], $laFila[1], $laFila[2], $laFila[3], $laFila[4], $laFila[5], $laFila[6]);
      }
      return true;
   }
   
   public function omAplicar7() {
      $llOk = $this->omAplicar1();
      if (!$llOk) {
         return false;
      } elseif ($this->pcReqInv == 'N') {
         $this->pcError = '<DATA><ERROR>INCIDENTE NO REQUIERE INVESTIGACION</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $this->mxComentarios($loSql);
      $llOk = $this->mxFactoresRiesgo($loSql);
      $loSql->omDisconnect();
      return $llOk;
   }
   
   // Sube PDF a servidor
   public function omSubir() {
      if (empty($this->pcNroInc)) {
         $this->pcError = '<DATA><ERROR>NÚMERO DE INCIDENTE VACIO</ERROR></DATA>';
         return false;
      } else if (empty($this->paFiles['file']['name'])) {
         $this->pcError = 'NOMBRE Y RUTA DEL ARCHIVO VACIO</ERROR></DATA>';
         return false;
      } else if (empty($this->paFiles['file']['tmp_name'])) {
         $this->pcError = '<DATA><ERROR>NOMBRE Y RUTA DEL ARCHIVO ORIGEN VACIO</ERROR></DATA>';
         return false;
      } else if (!($this->paFiles['file']['type'] == 'application/pdf' or $this->paFiles['file']['type'] == 'application/binary' or $this->paFiles['file']['type'] == 'image/png' or $this->paFiles['file']['type'] == 'image/jpeg')) {
         $this->pcError = '<DATA><ERROR>NO ES UN ARCHIVO PDF - '.$this->paFiles['file']['type'].'</ERROR></DATA>';
         return false;
      }
      $lcFolder = './FILES/'.$this->pcNroInc;
      if (!is_dir($lcFolder)){
          mkdir($lcFolder, 0777, false);
      }
      chmod($lcFolder, 0777);
      $llOk = move_uploaded_file($this->paFiles['file']['tmp_name'], $lcFolder.'/'.$this->paFiles['file']['name']);
      if (!$llOk) {
         $this->pcError = '<DATA><ERROR>ERROR AL SUBIR EL ARCHIVO PDF</ERROR></DATA>';
      }
      return $llOk;
   }
   
   public function omInitConsultas() {
      // Conectar DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Estado del incidente
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '034'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DE INCIDENTE [034] DEFINIDOS</ERROR></DATA>';
         return false;
      }
      return true;
   }
}
?>