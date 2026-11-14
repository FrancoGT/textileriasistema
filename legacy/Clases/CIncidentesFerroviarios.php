<?php
require_once "Clases/CBase.php";

class CIncidentesFerroviarios extends CBase {
   public $pcIncFer, $paCodEmp, $paCodPry, $paCodPer, $paCodFcr, $paIncFer, $paSubDiv, $paLugAcc, $paAreas, $plNuevo,
          $pcNumero, $pdFecha, $pcHora, $pcTren, $pcCodEmp, $pcCodPry, $pcSubDiv, $pcKilome, $pcLugar,
          $pcCodMaq, $pcCodJef, $pcCodBrq, $pcHerido, $pcCodFcr, $pcArea, $pnCosto, $pcDemora, $pmEquInv, $pmDescri,
          $pmDetHer, $pmMedAdo, $pcCodUsu, $pcEstaci, $paEstaci, $paCodUni, $paData, $pcFecha, $pmCausas;

   public function __construct() {
      parent::__construct();
      $this->paCodEmp = null;
      $this->paCodPry = null;
      $this->paCodPer = null;
      $this->paCodFcr = null;
      $this->paIncFer = null;
      $this->paSubDiv = null;
      $this->paLugAcc = null;
      $this->paAreas  = null;
      $this->paEstaci = null;
      $this->paCodUni = null;
      $this->paData   = null;
   }

   public function omInitIncidentes() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga unidades
      $RS = $loSql->omExec("SELECT cCodUni, cDescri FROM S02MUNI WHERE cEstado = 'A' ORDER BY cCodUni");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodUni[$i] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>MAESTRO DE UNIDADES NO ESTA DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Carga empresas
      $RS = $loSql->omExec("SELECT cCodEmp, cDescri FROM S02MEMP WHERE cEstado = 'A' ORDER BY nOrden, cDescri");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodEmp[$i] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>MAESTRO DE EMPRESAS NO ESTA DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Carga proyectos
      $RS = $loSql->omExec("SELECT cCodPry, cDescri FROM S02MPRY WHERE cEstado = 'A' ORDER BY cDescri");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPry[$i] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>MAESTRO DE PROYECTOS NO ESTA DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Carga personal: maquinista/chofer, ...
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM S02MPER WHERE cEstado = 'A' ORDER BY cNombre");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>MAESTRO DE PERSONAL NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Carga causas
      $RS = $loSql->omExec("SELECT cCodFcr, cDescri FROM S02MFCR WHERE cEstado = 'A' AND cTipo = '1' ORDER BY cCodFcr");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodFcr[] = array($laFila[0], trim($laFila[0]).'-'.$laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>MAESTRO DE CAUSAS (FCR) NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Carga estaciones
      $RS = $loSql->omExec("SELECT TRIM(cCodigo), cDescri FROM V_S02TTAB WHERE cCodTab = '021'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstaci[$i] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>TABLA DE ESTACIONES ESTA DEFINIDA [021]</ERROR></DATA>';
         return false;
      }
      // Carga tipo de incidente ferroviario
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 3), cDescri FROM V_S02TTAB WHERE cCodTab = '043'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paIncFer[$i] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>TABLA DE TIPO DE INCIDENTE FERROVIARIO NO ESTA DEFINIDA [043]</ERROR></DATA>';
         return false;
      }
      // Carga subdivisiones
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE cCodTab = '020'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paSubDiv[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY SUBDIVISIONES DEFINIDAS. TABLA [020]</ERROR></DATA>';
         return false;
      }
      // Carga lugar de accidente
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '008'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paLugAcc[] = array($laFila[0], $laFila[1]);
     }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>TABLA DE LUGARES NO DEFINIDA. TABLA [008]</ERROR></DATA>';
         return false;
      }
      // Carga area
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_S02TTAB WHERE cCodTab = '030'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paAreas[] = array($laFila[0], $laFila[1]);
      }
      $loSql->omDisconnect();
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>TABLA AREAS NO DEFINIDA. [030]</ERROR></DATA>';
         return false;
      }
      return true;
   }

   // Aplica numero de incidente
   public function omAplicar() {
      if (empty($this->pcNumero)) {
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga incidente ferroviario
      $RS = $loSql->omExec("SELECT TO_CHAR(dFecha, 'yyyy-mm-dd') AS cFecha, cIncFer, cTren, cHora, cCodEmp, cCodPry,
                            cCodMaq, cCodJef, cCodBrq, cCodFcr, cHerido, cLugar, cArea, nCosto, cDemora, mDescri, 
                            mDetHer, mMedAdo, cKilome, cSubDiv, TRIM(cEstaci) AS CESTACI, mCausas FROM S02MIFR WHERE cNumero = '$this->pcNumero'");
      $laFila = $loSql->fetch($RS);
      $this->pcFecha = $laFila['CFECHA'];
      if (empty($this->pcFecha)) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcIncFer = $laFila['CINCFER'];
      $this->pcTren   = $laFila['CTREN'];
      $this->pcHora   = $laFila['CHORA'];
      $this->pcCodEmp = $laFila['CCODEMP'];
      $this->pcCodPry = $laFila['CCODPRY'];
      $this->pcCodMaq = $laFila['CCODMAQ'];
      $this->pcCodJef = $laFila['CCODJEF'];
      $this->pcCodBrq = $laFila['CCODBRQ'];
      $this->pcCodFcr = $laFila['CCODFCR'];
      $this->pcHerido = $laFila['CHERIDO'];
      $this->pcLugar  = $laFila['CLUGAR'];
      $this->pcArea   = $laFila['CAREA'];
      $this->pnCosto  = $laFila['NCOSTO'];
      $this->pcDemora = $laFila['CDEMORA'];
      $this->pmDescri = $laFila['MDESCRI']->load();
      $this->pmDetHer = $laFila['MDETHER']->load();
      $this->pmMedAdo = $laFila['MMEDADO']->load();
      $this->pmCausas = $laFila['MCAUSAS']->load();
      $this->pcKilome = $laFila['CKILOME'];
      $this->pcSubDiv = $laFila['CSUBDIV'];
      $this->pcEstaci = $laFila['CESTACI'];
      // Unidades involucradas
      $RS = $loSql->omExec("SELECT A.*, B.cDescri AS cDescr1 FROM S02DIFR A INNER JOIN S02MUNI B ON A.cCodUni = B.cCodUni WHERE A.cNumero = '$this->pcNumero' ORDER BY A.cCodUni");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $lcDescri = $laFila['CDESCRI'];
         if ($laFila['CCODUNI'] != '99999') {
            $lcDescri = $laFila['CDESCR1'];
         }
         $this->paData[] = array($i, $laFila['CCODUNI'], $lcDescri);
      }
      $loSql->omDisconnect();
      return true;
   }

   // Graba incidente
   public function omGrabar() {
      $this->pcDemora = strtolower($this->pcDemora);
      $lnCosto = floatval($this->pnCosto);
      if (!fxValDate($this->pdFecha)) {
         $this->pcError = '<DATA><ERROR>ERROR EN FECHA DE INCIDENTE FERROVIARIO ['.$this->pdFecha.']</ERROR></DATA>';
         return false;
      } else if (!fxValTime($this->pcHora)) {
         $this->pcError = '<DATA><ERROR>ERROR EN HORA DE INCIDENTE ['.$this->pcHora.']</ERROR></DATA>';
         return false;
      } else if (empty($this->paData) or fxCount($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS DE UNIDADES INVOLUCRADAS</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodEmp)) {
          $this->pcError = '<DATA><ERROR>EMPRESA NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodPry)) {
          $this->pcError = '<DATA><ERROR>PROYECTO NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcLugar)) {
          $this->pcError = '<DATA><ERROR>LUGAR DE INCIDENTE NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcIncFer)) {
          $this->pcError = '<DATA><ERROR>TIPO DE INCIDENTE FERROVIARIO NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodMaq)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE MAQUINISTA (CHOFER) NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodJef)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE JEFE DE TREN NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodBrq)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE BREQUERO NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if ($this->pcEstaci == '99' and (empty($this->pcKilome))) {
          $this->pcError = '<DATA><ERROR>ERROR: KILOMETRAJE NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if ($this->pcEstaci == '99' and (!(floatval($this->pcKilome) >= 0 and floatval($this->pcKilome) <= 999999.999))) {
          $this->pcError = '<DATA><ERROR>KILOMETRAJE NO ESTA EN RANGO PERMITIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcTren)) {
          $this->pcError = '<DATA><ERROR>TREN NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (empty($this->pcArea)) {
          $this->pcError = '<DATA><ERROR>AREA NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (empty($this->pcSubDiv)) {
          $this->pcError = '<DATA><ERROR>SUBDIVISION NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodFcr)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE CAUSA NO DEFINIDA</ERROR></DATA>';
          return false;
      } else if (empty($this->pmDescri)) {
          $this->pcError = '<DATA><ERROR>ERROR: DESCRIPCION DEL INCIDENTE FERROVIARIO VACIO</ERROR></DATA>';
          return false;
      } else if (empty($this->pmMedAdo)) {
          $this->pcError = '<DATA><ERROR>ERROR: NO HA DEFINIDO LAS MEDIDAS ADOPTADAS</ERROR></DATA>';
          return false;
      } else if (empty($this->pmCausas)) {
          $this->pcError = '<DATA><ERROR>ERROR: NO HAY DESCRICIPCIÓN DE LAS CAUSAS</ERROR></DATA>';
          return false;
      } else if (!fxTiempoPerdido($this->pcDemora)) {
         $this->pcError = '<DATA><ERROR>ERROR EN VALOR DE DEMORA</ERROR></DATA>';
         return false;
      } else if ($lnCosto < 0) {
         $this->pcError = '<DATA><ERROR>ERROR: COSTO NO PUEDE SER NEGATIVO</ERROR></DATA>';
         return false;
      } else if ($this->pcHerido == 'S' and empty($this->pmDetHer)) {
          $this->pcError = '<DATA><ERROR>ERROR: NO HA DEFINIDO EL DETALLE DE LOS HERIDOS</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodUsu)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
          return false;
      } else if (!$this->plNuevo and empty($this->pcNumero)) {
          $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE FERROVIARIO NO DEFINIDO</ERROR></DATA>';
          return false;
      }
      // Arregla variables
      if ($this->pcHerido == 'N') {
         $this->pmDetHer = ' ';
      }
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
     // Valida estacion
      $RS = $loSql->omExec("SELECT cCodigo FROM V_S02TTAB WHERE cCodTab = '021' AND cCodigo = '$this->pcEstaci'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $this->pcError = '<DATA><ERROR>ESTACION ['.$this->pcEstaci.'] NO DEFINIDA</ERROR></DATA>';
         $loSql->omDisconnect();
         return false;
      }
      if (!$this->plNuevo) {
         $RS = $loSql->omExec("SELECT cNumero FROM S02MIFR WHERE cNumero = '$this->pcNumero'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $this->pcError = '<DATA><ERROR>NUMERO DE INCIDENTE DE FERROCARRIL NO EXISTE</ERROR></DATA>';
            $loSql->omDisconnect();
            return false;
         }
         $lcSql = "UPDATE S02MIFR SET dFecha  = TO_DATE('$this->pdFecha', 'yyyy-mm-dd'), cHora = '$this->pcHora', cIncFer = '$this->pcIncFer',
                   cTren = '$this->pcTren', cCodEmp = '$this->pcCodEmp', cCodPry = '$this->pcCodPry', cSubDiv = '$this->pcSubDiv',
                   cKilome = '$this->pcKilome', cLugar  = '$this->pcLugar', cCodMaq = '$this->pcCodMaq', cCodJef = '$this->pcCodJef',
                   cCodBrq = '$this->pcCodBrq', cHerido = '$this->pcHerido', cCodFcr = '$this->pcCodFcr', cArea = '$this->pcArea',
                   nCosto = $lnCosto, cDemora = '$this->pcDemora', mEquInv = ' ', mDescri = '$this->pmDescri',
                   mDetHer = '$this->pmDetHer', mMedAdo = '$this->pmMedAdo', cEstaci = '$this->pcEstaci', mCausas = '$this->pmCausas',
                   cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cNumero = '$this->pcNumero'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR EVALUACION</ERROR></DATA>';
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM S02DIFR WHERE cNumero = '$this->pcNumero'");;
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR UNIDADES INVOLUCRADAS</ERROR></DATA>';
            return false;
         }
         foreach($this->paData as $i) {
            $lcDescri = ' ';
            if ($i[1] == '99999')
               $lcDescri = $i[2];
            $lcSql = "INSERT INTO S02DIFR (cNumero, cCodUni, cDescri, cCodUsu) VALUES ('$this->pcNumero', '$i[1]', '$lcDescri', '$this->pcCodUsu')";
            $llOk = $loSql->omExec($lcSql);
            if (!$llOk) {
               $loSql->rollback();
               $loSql->omDisconnect();
               $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR UNIDADES INVOLUCRADAS</ERROR></DATA>';
               return false;
            }
         }
      } else {
         $lcNumero = '000000';
         $RS = $loSql->omExec("SELECT MAX(cNumero) FROM S02MIFR");
         $laFila = $loSql->fetch($RS);
         if (!empty($laFila[0])) {
            $lcNumero = $laFila[0];
         }
         $lnNumero = (int)$lcNumero + 1;
         $lcNumero = (string)$lnNumero;
         $this->pcNumero = right('00000'.trim($lcNumero), 6);
         $lcSql = "INSERT INTO S02MIFR (cNumero, dFecha, cHora, cIncFer, cTren, cCodEmp, cCodPry, cSubDiv, cKilome,
                   cLugar, cCodMaq, cCodJef, cCodBrq, cHerido, cCodFcr, cArea, nCosto, cDemora, mEquInv, mDescri,
                   mDetHer, mMedAdo, cEstaci, mCausas, cCodUsu) VALUES ('$this->pcNumero', TO_DATE('$this->pdFecha', 'yyyy-mm-dd'),
                   '$this->pcHora', '$this->pcIncFer', '$this->pcTren', '$this->pcCodEmp', '$this->pcCodPry', '$this->pcSubDiv',
                   '$this->pcKilome', '$this->pcLugar', '$this->pcCodMaq', '$this->pcCodJef', '$this->pcCodBrq', '$this->pcHerido',
                   '$this->pcCodFcr', '$this->pcArea', $lnCosto, '$this->pcDemora', ' ', '$this->pmDescri',
                   '$this->pmDetHer', '$this->pmMedAdo', '$this->pcEstaci', '$this->pmCausas', '$this->pcCodUsu')";
		 $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR INCIDENTES FERROVIARIOS</ERROR></DATA>';
            return false;
         }
         foreach($this->paData as $i) {
            $lcDescri = ' ';
            if ($i[1] == '99999')
                $lcDescri = $i[2];
            $lcSql = "INSERT INTO S02DIFR (cNumero, cCodUni, cDescri, cCodUsu) VALUES ('$this->pcNumero', '$i[1]', '$lcDescri', '$this->pcCodUsu')";
            $llOk = $loSql->omExec($lcSql);
            if (!$llOk) {
               $loSql->rollback();
               $loSql->omDisconnect();
               $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR UNIDADES INVOLUCRADAS</ERROR></DATA>';
               return false;
            }
         }
      }
      $loSql->omDisconnect();
      return true;
   }
   
   public function omConsultar() {
      if (!fxValDate($this->pdFecIni)) {
         $this->pcError = '<DATA><ERROR>FECHA INICIAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif (!fxValDate($this->pdFecFin)) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL INVALIDA</ERROR></DATA>';
         return false;
      } elseif ($this->pdFecFin < $this->pdFecIni) {
         $this->pcError = '<DATA><ERROR>FECHA FINAL ES MENOR QUE FECHA INICIAL</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Trae datos
/*      $RS = $loSql->omExec("SELECT cNumero, TO_CHAR(dFecha, 'yyyy-mm-dd'), cTren, ROWNUM FROM (
                            SELECT cNumero, dFecha, cTren FROM S02MIFR 
                            WHERE dFecha BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd')
                            ORDER BY dFecha, cNumero) WHERE ROWNUM >= $lnReg1 AND ROWNUM <= $lnReg2");*/
      $RS = $loSql->omExec("SELECT cNumero, TO_CHAR(dFecha,'yyyy-mm-dd') AS DFECHA, cTren FROM S02MIFR 
                            WHERE dFecha BETWEEN TO_DATE('$this->pdFecIni', 'yyyy-mm-dd') AND TO_DATE('$this->pdFecFin', 'yyyy-mm-dd')
                            ORDER BY dFecha, cNumero");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[] = array($laFila[0], $laFila[1], $laFila[2]);
      }
     if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY INCIDENTES FERROVIARIOS PARA CONSULTAR</ERROR></DATA>';
         return false;
     }
      return true;
   }
}
?>
