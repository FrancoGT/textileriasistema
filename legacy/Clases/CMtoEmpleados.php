<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Mantenimiento de Empleados
//-------------------------------------
class CMtoEmpleados extends CBase {
   public $paEstado, $pcEstado, $pcCodUsu, $pcCodPer, $pcCodSan, $pcPerEmp, $paCodPer, $paCodSan, $paCodNor, $paPerEmp,
          $pdFecIni, $pmObserv, $pdFecha, $pdCese, $pcNombre, $pcDirecc, $pcSexo, $paSexo, $paTipo, $pcTipo, $pmDatos,
          $paIndex, $paData, $paNombre, $paCodEmp, $plNuevo, $paEstCiv, $paCargo, $paCargos, $pcNroDni, $pcSector,
          $paSector, $pcDesSec, $paCarDis, $paAreas, $paCodPry;
   
   public function __construct() {
      parent::__construct();
      $this->paEstado = null;
      $this->paSexo   = null;
      $this->paTipo   = null;
      $this->paEstCiv = null;
      $this->paCargo  = null;
      $this->paCargos = null;
      $this->paData   = null;
      $this->paCodEmp = null;
      $this->paCodPer = null;
      $this->paSector = null;
      $this->paCarDis = null;
      $this->paAreas  = null;
      $this->paCodPry = null;
   }
   
   // Inicializa historia de personal
   public function omInitHistoria() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga cargo disgregado
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 3), cDescri FROM V_E01TTAB WHERE cCodTab = '045'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCarDis[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TABLA DE CARGOS DISGREGADOS [045] DEFINIDA</ERROR></DATA>';
         return false;
      }
      // Carga sector
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '037'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paSector[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY SECTORES DEFINIDOS. TABLA [037]</ERROR></DATA>';
         return false;
      }
      // Carga area
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), cDescri FROM V_E01TTAB WHERE cCodTab = '030'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paAreas[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY AREAS DEFINIDAS. TABLA [030]</ERROR></DATA>';
         return false;
      }
      // Cargos
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 3), cDescri FROM V_E01TTAB WHERE cCodTab = '015'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCargos[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY CARGOS DEFINIDOS</ERROR></DATA>';
         return false;
      }
      // Empresas
      $RS = $loSql->omExec("SELECT cCodEmp, cDescri FROM E01MEMP WHERE cEstado = 'A' ORDER BY nOrden, cDescri");
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
      // Proyectos
      $RS = $loSql->omExec("SELECT cCodPry, cDescri FROM E01MPRY WHERE cEstado = 'A' ORDER BY cCodPry");
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
      // Carga Empleados
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM E01MPER WHERE cEstado = 'A' AND SUBSTR(cCodPer, 1, 1) != '9' ORDER BY cNombre");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY EMPLEADOS ACTIVOS</ERROR></DATA>';
         return false;
      }
      return true;
   }
  
   // Aplicar historia de personal
   public function omAplicarHistoria() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL VACIO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga nombre
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM E01MPER WHERE cCodPer = '$this->pcCodPer'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcNombre = $laFila[1];
      // Carga historia de personal
      $RS = $loSql->omExec("SELECT cCodEmp, TO_CHAR(dFecIni, 'YYYY-MM-DD'), cCarDis, cCargo, cSector FROM E01HPER WHERE cCodPer = '$this->pcCodPer' ORDER BY dFecIni");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($i, $laFila[0], $laFila[1], $laFila[2], $laFila[3], $laFila[4]);
      }
      return true;
   }

   // Grabar historico de empleados
   public function omGrabarHistoria() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE EMPLEADO ESTA VACIO</ERROR></DATA>';
         return false;
      } else if (empty($this->paData) or fxCount($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA GRABAR</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
     // Valida si hay fechas invalidas
      foreach($this->paData as $i) {
         if (!fxValDate($i[2])) {
            $this->pcError = '<DATA><ERROR>FECHA DE INICIO INVALIDA</ERROR></DATA>';
            return false;
         }
      }
      // Valida si hay fechas repetidas
      $llFlag = fxValRepeated($this->paData, 2);
      if ($llFlag) {
         $this->pcError = '<DATA><ERROR>HAY FECHAS REPETIDAS</ERROR></DATA>';
         return false;
      }
      // Conecta DB
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $llOk = $loSql->omExec("DELETE FROM E01HPER WHERE cCodPer = '$this->pcCodPer'");
      if (!$llOk) {
         $loSql->rollback();
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR REGISTROS ANTERIORES</ERROR></DATA>';
         return false;
      }
      foreach($this->paData as $i) {
         $lcSql = "INSERT INTO E01HPER (cCodPer, cCodEmp, dFecIni, cCarDis, cCargo, cSector, cPuesto, cCodPry, cCodUsu) VALUES ('$this->pcCodPer', '$i[1]', TO_DATE('$i[2]', 'YYYY-MM-DD'), '$i[3]', '$i[4]', '$i[5]', 'B', '$i[7]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL INSERTAR REGISTROS EN HISTORICO</ERROR></DATA>';
            return false;
         }
      }
      $loSql->omDisconnect();
      return $llOk;
   }
   
   // Grabar personal empleado
   public function omGrabarEmpleado() {
      if (empty($this->pcNombre)) {
         $this->pcError = '<DATA><ERROR>NOMBRE DEL EMPLEADO ESTA VACIO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcDirecc)) {
         $this->pcError = '<DATA><ERROR>DIRECCION DEL EMPLEADO NO DEFINIDA</ERROR></DATA>';
         return false;
      } else if (empty($this->pcNroDni)) {
         $this->pcError = '<DATA><ERROR>DNI DEL EMPLEADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (strlen($this->pcNroDni)!=8) {
         $this->pcError = '<DATA><ERROR>DNI DEL EMPLEADO NO TIENE TODOS LOS DIGITOS</ERROR></DATA>';
         return false;
      } else if (empty($this->pcEmail)) {
         $this->pcError = '<DATA><ERROR>EMAIL DEL EMPLEADO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (!fxValEmail($this->pcEmail)) {
         $this->pcError = '<DATA><ERROR>EMAIL DEL EMPLEADO INVALIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodPer) AND !$this->plNuevo) {
         $this->pcError = '<DATA><ERROR>CODIGO DE EMPLEADO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $this->pcNombre = strtoupper($this->pcNombre);
      $this->pcDirecc = strtoupper($this->pcDirecc);
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      if ($this->plNuevo) {
         $RS = $loSql->omExec("SELECT MAX(cCodPer) FROM E01MPER WHERE SUBSTR(cCodPer, 1, 1) != '9'");
         $laFila = $loSql->fetch($RS);
         $lcCodPer = '000000';
         if (!empty($laFila[0]))
            $lcCodPer = $laFila[0];
         $lnCodPer = (int)$lcCodPer + 1;
         $lcCodPer = (string)$lnCodPer;
         $lcCodPer = right('00000'.trim($lcCodPer), 6);
         $lcSql = "INSERT INTO E01MPER (cCodPer, cNombre, cDirecc, cSexo, cEstCiv, cEstado, cNroDni, cEmail, cCodUsu) VALUES ('$lcCodPer', '$this->pcNombre','$this->pcDirecc', '$this->pcSexo', '$this->pcEstCiv', '$this->pcEstado', '$this->pcNroDni', '$this->pcEmail', '$this->pcCodUsu')";
      } else {
         $RS = $loSql->omExec("SELECT cCodPer FROM E01MPER WHERE cCodPer = '$this->pcCodPer'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE EMPLEADO NO EXISTE</ERROR></DATA>';
            return false;
         }
         $lcSql = "UPDATE E01MPER SET cNombre = '$this->pcNombre', cDirecc = '$this->pcDirecc', cSexo = '$this->pcSexo', cEstCiv = '$this->pcEstCiv', cEstado = '$this->pcEstado', cNroDni = '$this->pcNroDni', cEmail = '$this->pcEmail', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE() WHERE cCodPer = '$this->pcCodPer'";
      }
      $llOk = $loSql->omExec($lcSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR EN GRABACIÓN DE PERSONAL</ERROR></DATA>';
         return false;
      }
      if ($this->plNuevo) {
         // Carga Empleados nuevamente
         $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM E01MPER WHERE SUBSTR(cCodPer, 1, 1) != '9' ORDER BY CNOMBRE");
         while ($laFila = $loSql->fetch($RS)) {
            $this->paCodPer[] = array($laFila[0], $laFila[1]);
         }
      }
      $loSql->omDisconnect();
      return true;
   }

   // Inicia mantenimiento de empleados
   public function omCargarMtoEmpleados() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Estados
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '001'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DEFINIDOS. TABLA [001]</ERROR></DATA>';
         return false;
      }
      // Carga Sexo
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '017'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paSexo[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY SEXO DEFINIDO. TABLA [001]</ERROR></DATA>';
         return false;
      }
      // Carga Empleados
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM E01MPER WHERE SUBSTR(cCodPer, 1, 1) != '9' ORDER BY CNOMBRE");
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
      // Carga estado civil
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '018'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstCiv[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
	     $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE SANCION DEFINIDOS. TABLA [001]</ERROR></DATA>';
         return false;
      }
	  $loSql->omDisconnect();
      return true;
   }

   // Inicia mantenimiento de empleados
   public function omCargarMtoEmpleados_XXX() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Empresas
      $RS = $loSql->omExec("SELECT cCodEmp, cDescri FROM E01MEMP WHERE cEstado = 'A' ORDER BY nOrden, cDescri");
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
      // Carga Puesto
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '022'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paPuesto[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PUESTO DEFINIDO. TABLA [001]</ERROR></DATA>';
         return false;
      }
      // Carga Sector
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '037'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paSector[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY SECTOR DEFINIDO. TABLA [001]</ERROR></DATA>';
         return false;
      }
      // Carga Estados
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '001'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DEFINIDOS. TABLA [001]</ERROR></DATA>';
         return false;
      }
      // Carga Sexo
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '017'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paSexo[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY SEXO DEFINIDO. TABLA [001]</ERROR></DATA>';
         return false;
      }
      // Carga el Cargo del Personal
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 3), cDescri FROM V_E01TTAB WHERE cCodTab = '015'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCatego[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY CARGOS DEFINIDOS. TABLA [001]</ERROR></DATA>';
         return false;
      }
      // Carga Empleados
      $RS = $loSql->omExec("SELECT cCodPer, cNombre FROM E01MPER WHERE SUBSTR(cCodPer, 1, 1) != '9' ORDER BY CNOMBRE");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAL ACTIVO</ERROR></DATA>';
         return false;
      }
      // Carga estado civil
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '018'");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstCiv[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE SANCION DEFINIDOS. TABLA [001]</ERROR></DATA>';
         return false;
      }
      return true;
   }

   // Aplica codigo de personal empleado
   public function omAplicar() {
      if (empty($this->pcCodPer)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE EMPLEADO VACIO</ERROR></DATA>';
          return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      //cargar personal en categoria, puesto, empresa actual
      $RS = $loSql->omExec("SELECT * FROM E01MPER WHERE CCODPER='$this->pcCodPer'");
      $loSql->omDisconnect();
      $laFila = $loSql->fetch($RS);
      if (empty($laFila['CNOMBRE'])) {
         $this->pcError = '<DATA><ERROR>CODIGO DE EMPLEADO NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcNombre = $laFila['CNOMBRE'];
      $this->pcNroDni = $laFila['CNRODNI'];
      $this->paData = $laFila;
      return true;
   }
   
   // Mantenimiento de puente cursos - normas
   public function omMtoPuentePersonal() {
      $this->paData = null;
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Curso
      $lcSql = "SELECT cNombre FROM E01MPER WHERE cCodPer = '$this->pcCodPer'";
      $RS = $loSql->omExec($lcSql);
      $laFila = $loSql->fetch($RS);
      $this->pcNombre = $laFila[0];
      if ($this->pcNombre == null) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE EMPLEADO NO EXISTE</ERROR></DATA>';
         return false;
      }
      // Trae empresas en las que trabajan los empleados
      $lcSql = "SELECT cCodEmp, TO_CHAR(dFecIni,'yyyy-mm-dd'), dCese, mDatos FROM E01PPER  WHERE cCodPer = '$this->pcCodPer' ORDER BY cCodEmp";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($i, $laFila[0], $laFila[1], $laFila[2], $laFila[3]->load());
      }
      $this->paCodEmp = null;
      $lcSql = "SELECT cCodEmp, cDescri FROM E01MEMP WHERE cEstado = 'A' ORDER BY nOrden, cDescri";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodEmp[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY EMPRESAS DEFINIDAS</ERROR></DATA>';
         $llOk = false;
      }
      return $llOk;
   }
   
   // Buscar empleados por nombre
   public function omBuscarEmpleados() {
      if (empty($this->pcNombre)) {
         $this->pcError = '<DATA><ERROR>NOMBRE VACIO</ERROR></DATA>';
         return false;
      }
      $this->paCodPer = null;
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcNombre = trim($this->pcNombre).'%';
      $lcSql = "SELECT cCodPer, cNombre FROM E01MPER WHERE cNombre LIKE '$lcNombre' ORDER BY cNombre";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY EMPLEADOS QUE CUMPLAN CONDICION</ERROR></DATA>';
         $llOk = false;
      }
      return $llOk;
   } 
   
   public function omInitPersonalSancion(){
      $llOk = $this->omCargarMtoEmpleados();
      if (!$llOk) {
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Empleado
      $lcSql = "SELECT cCodPer, cNombre FROM E01MPER WHERE cEstado = 'A' AND cCodPer != '999999'  ORDER BY CNOMBRE ";
      $RS = $loSql->omExec($lcSql);
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodPer[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
        $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY EMPLEADOS ACTIVOS</ERROR></DATA>';
         return false;
      }
     // Carga normas 
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM E01MNOR WHERE cEstado = 'A' AND cTipo = '1' ORDER BY cCodNor");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodNor[] = array($laFila[0], trim($laFila[0]).'-'.$laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY NORMAS DEFINIDAS</ERROR></DATA>';
         return false;
      }
      // Codigo de sanciones
      $lcSql = "SELECT cCodSan, cDescri FROM E01TSAN WHERE cEstado = 'A' ORDER BY cCodSan";
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodSan[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY SANCIONES DEFINIDAS</ERROR></DATA>';
         return false;
      }
      return true;
   }
   
   // Busca persona para registrar sancion(es)
   public function omAplicarPersonalSancion() {
      if (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE EMPLEADO VACIO</ERROR></DATA>';
         return false;
      }
      $llOk = $this->omCargarMtoEmpleados();
      if (!$llOk) {
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
     // Traer sanciones
      $this->paCodSan = null;
      $RS = $loSql->omExec("SELECT cCodSan, cDescri FROM E01TSAN ORDER BY cCodSan");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodSan[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY SANCIONES DEFINIDAS</ERROR></DATA>';
         return false;
      }
     // Normas
      $this->paCodNor = null;
      $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM E01MNOR WHERE cEstado = 'A' AND CTIPO = 1 ORDER BY cDescri");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodNor[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY NORMAS DEFINIDAS</ERROR></DATA>';
         $llOk = false;
      }
      return $llOk;
   }   

   // Editar sancion de persona
   public function omEditarSancion() {
      if (empty($this->pcCodigo)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE SANCION VACIO</ERROR></DATA>';
         return false;
      }
      $llOk = $this->omCargarMtoEmpleados();
      if (!$llOk) {
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Normas
      $this->paData = null;
      $RS = $loSql->omExec("SELECT cCodNor, cDesNor FROM V_E01DSAN WHERE cCodigo = '$this->pcCodigo'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($i, $laFila[0], trim($laFila[0]).'-'.$laFila[1]);
      }
      $RS = $loSql->omExec("SELECT cCodSan, TO_CHAR(dFecha,'yyyy-mm-dd'), cEstado, mObserv FROM E01PSAN WHERE cCodigo = '$this->pcCodigo'");
      $loSql->omDisconnect();
      $laFila = $loSql->fetch($RS);
      $this->pcCodSan = $laFila[0];
      $this->pdFecha  = $laFila[1];
      $this->pcEstado = $laFila[2];
      $this->pmObserv = $laFila[3]->load();
      return $llOk;
   }   
   
   public function omMtoPersonalSancion() {
      $llOk = $this->omAplicar();
      if (!$llOk) {
         return false;
      }
      $this->paData = null;
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Trae sanciones del empleado
      $RS = $loSql->omExec("SELECT cCodigo, cDescri, TO_CHAR(dFecha, 'yyyy-mm-dd') FROM V_E01PSAN  WHERE cCodPer = '$this->pcCodPer' ORDER BY dFecha");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[$i] = array($i, $laFila[0], $laFila[1],$laFila[2]);
      }
      return $llOk;
   }
   
   public function omGrabarPersonalSancion(){
      if (!$this->mxValDate($this->pdFecha)) {
         $this->pcError = '<DATA><ERROR>FECHA ESTA VACIA</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodPer)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE PERSONAL VACIO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pmObserv)) {
         $this->pcError = '<DATA><ERROR>CAMPO OBSERVACIONES VACIO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcCodSan)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE SANCION VACIO</ERROR></DATA>';
         return false;
      } elseif (empty($this->pcEstado)) {
         $this->pcError = '<DATA><ERROR>ESTADO VACIO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>USUARIO VACIO</ERROR></DATA>';
         return false;
      } else if (fxCount($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY NORMAS PARA GRABAR</ERROR></DATA>';
         return false;
      } else if (!$this->plNuevo and empty($this->pcCodigo)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE SANCION NO ESTA DEFINIDO</ERROR></DATA>';
         return false;
      }
      // Evalua normas repetidas
      for ($i = 1; $i <= fxCount($this->paData); $i++) {
          for ($j = $i + 1; $j <= fxCount($this->paData); $j++) {
              if ($this->paData[$i][1] == $this->paData[$j][1]) {
                  $this->pcError = '<DATA><ERROR>NORMA ['.$this->paData[$j][1].'] ESTA REPETIDA</ERROR></DATA>';
                  return false;
              }
          } 
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      if ($this->plNuevo) {
         $RS = $loSql->omExec('SELECT MAX(cCodigo) FROM E01PSAN');
         $laFila = $loSql->fetch($RS);
         if ($laFila[0] == null) {
            $lcCodigo = '000001';
         } else {
            $lcCodigo = $laFila[0];
            $lnCodigo = (int)$lcCodigo + 1;
            $lcCodigo = (string)$lnCodigo;
            $lcCodigo = right('00000'.trim($lcCodigo), 6);
         }
         $lcSql = "INSERT INTO E01PSAN (cCodigo, cCodPer, cCodSan, dFecha, cEstado, mObserv, cCodUsu) VALUES ('$lcCodigo', '$this->pcCodPer', '$this->pcCodSan', TO_DATE('$this->pdFecha','yyyy/mm/dd'), '$this->pcEstado', '$this->pmObserv', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL GRABAR CABECERA DE SANCIONES [E01PSAN]</ERROR></DATA>';
            return false;
         }
      } else {
         $lcCodigo = $this->pcCodigo;
         $RS = $loSql->omExec("SELECT cCodigo FROM E01PSAN WHERE cCodigo = '$lcCodigo'");
         $laFila = $loSql->fetch($RS);
         if ($laFila[0] == null) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>NO EXISTE CODIGO DE SANCION ['.$lcCodigo.'] EN [E01PSAN]</ERROR></DATA>';
            return false;
         }
         $lcSql = "UPDATE E01PSAN SET cCodSan = '$this->pcCodSan', dFecha = TO_DATE('$this->pdFecha','yyyy/mm/dd'), tModifi= SYSDATE, cEstado = '$this->pcEstado', mObserv = '$this->pmObserv' WHERE cCodigo = '$lcCodigo'";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL ACTUALIZAR PUENTE DE SANCIONES [E01PSAN]</ERROR></DATA>';
            return false;
         }
         $llOk = $loSql->omExec("DELETE FROM E01DSAN WHERE cCodigo = '$lcCodigo'");
         if (!$llOk) {
            $loSql->rollback();
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL DEPURAR SANCIONES ANTERIORES [E01DSAN]</ERROR></DATA>';
            return false;
         }
      }
      foreach($this->paData as $i) {
         $lcSql = "INSERT INTO E01DSAN (cCodigo, cCodNor, cCodUsu) VALUES ('$lcCodigo', '$i[1]', '$this->pcCodUsu')";
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
            $loSql->rollback();
            $this->pcError = '<DATA><ERROR>ERROR AL GRABAR DETALLE DE NORMAS DE SANCION [E01DSAN]</ERROR></DATA>';
            break;
         }
      }
      $loSql->omDisconnect();
      return $llOk;
   }
}
?>