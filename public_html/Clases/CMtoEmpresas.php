<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Mantenimiento de empresas
//-------------------------------------
class CMtoEmpresas extends CBase {
   public $paEstado, $pcDescri, $pcEstado, $pcCodUsu, $pcCodEmp, $pmDatos, $paCodEmp, $paData;

   public function __construct() {
      parent::__construct();
      $this->paEstado = null;
      $this->pcDescri = null;
      $this->paData = null;
   }

   // Inicia mantenimiento de empresas
   public function omCargarMtoEmpresas() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga empresas
      $RS = $loSql->omExec("SELECT cCodEmp, cDescri FROM S02MEMP WHERE SUBSTR(cCodEmp, 1, 1) != '9' ORDER BY nOrden, cDescri");
      while ($laFila = $loSql->fetch($RS)) {
         $this->paData[] = array($laFila[0], $laFila[1]);
      }
      $RS = $loSql->omExec("SELECT cCodEmp, cDescri FROM S02MEMP WHERE SUBSTR(cCodEmp, 1, 1) = '9'");
      while ($laFila = $loSql->fetch($RS)) {
         $this->paData[] = array($laFila[0], $laFila[1]);
      }
      // Carga Estados
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '001'");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paEstado[] = array($laFila[0], $laFila[1]);
      }
      if ($i == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DEFINIDOS. TABLA [001]</ERROR></DATA>';
         return false;
      }
      return true;
    }

    public function omInitEmpresas()
    {
        $llOk = $this->omCargarMtoEmpresas();
        if (!$llOk)
            return false;
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        // Carga Empresa
        $lcSql = "SELECT cCodEmp, cDescri FROM S02MEMP WHERE cEstado = 'A' ORDER BY nOrden, cDescri";
        $RS = $loSql->omExec($lcSql);
        $loSql->omDisconnect();
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
            $i++;
            $this->paCodEmp[] = array($laFila[0], $laFila[1]);
        }
        if ($i == 0) {
            $this->pcError = '<DATA><ERROR>NO HAY EMPRESAS ACTIVAS</ERROR></DATA>';
            return false;
        }
        return true;
    }

    // Aplica codigo de proyecto
    public function omAplicar()
    {
        // Condiciones de excepcion
        if (empty($this->pcCodEmp)) {
            $this->pcError = 'CODIGO DE EMPRESA VACIO';
            return false;
        }
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        // Carga
        $lcSql = "SELECT cDescri, to_char(dFecIni,'yyyy-mm-dd'), cEstado, mDatos FROM S02MEMP WHERE cCodPry = '$this->pcCodPry'";
        $RS = $loSql->omExec($lcSql);
        $laFila = $loSql->fetch($RS);
        $this->pcDescri = $laFila[0];
        if (empty($this->pcDescri)) {
            $this->pcError = 'CODIGO DE PROYECTO NO EXISTE';
            $llOk = false;
        }
        $this->pdFecIni = $laFila[1];
        $this->pcEstado = $laFila[2];
        $this->pmDatos = $laFila[3];

        $this->paCodPer = null;
        $lcSql = "SELECT cCodPer, cNombre FROM S02MPER WHERE cEstado = 'A' ORDER BY cNombre";
        $RS = $loSql->omExec($lcSql);
        $loSql->omDisconnect();
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
            $this->paCodPer[] = array($laFila[0], $laFila[1]);
            $i++;
        }
        if ($i == 0) {
            $this->pcError = '<DATA><ERROR>NO HAY EMPLEADOS DEFINIDOS</ERROR></DATA>';
            $llOk = false;
        }
        return $llOk;
    }

    public function initDetalleEmpresas()
    { // OJOFPM REVISAR NOMENCLATURA
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        // Carga Empresa
        $lcSql = "SELECT cCodEmp, cDescri FROM S02MEMP WHERE cEstado = 'A' ORDER BY nOrden, cDescri";
        $RS = $loSql->omExec($lcSql);
        $loSql->omDisconnect();
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
            $i++;
            $this->paCodEmp[] = array($laFila[0], $laFila[1]);
        }
        if ($i == 0) {
            $this->pcError = '<DATA><ERROR>NO HAY EMPRESAS ACTIVAS</ERROR></DATA>';
            return false;
        }
        return true;
    }

    // Aplica codigo de proyecto
    public function omAplicar1()
    {
        // Condiciones de excepcion
        if (empty($this->pcCodEmp)) {
            $this->pcError = 'CODIGO DE EMPRESA VACIO';
            return false;
        }
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        // Carga Empresa
        $RS = $loSql->omExec("SELECT cDescri, cEstado, mDatos FROM S02MEMP WHERE cCodEmp = '$this->pcCodEmp'");
        $loSql->omDisconnect();
        $laFila = $loSql->fetch($RS);
        $this->pcDescri = $laFila[0];
        if (empty($this->pcDescri)) {
            $this->pcError = '<DATA><ERROR>CODIGO DE EMPRESA NO EXISTE</ERROR></DATA>';
            $llOk = false;
        }
        $this->pcEstado = $laFila[1];
        $this->pmDatos = $laFila[2]->load();
        return $llOk;
    }

   public function omGrabarEmpresa() {
      // Condiciones de excepcion
      if (empty($this->pcCodEmp)) {
         $this->pcError = '<DATA><ERROR><DATA><ERROR>CODIGO DE EMPRESA NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcDescri)) {
         $this->pcError = 'DESCRIPCION DEL PROYECTO ESTA VACIA</ERROR></DATA></ERROR></DATA>';
         return false;
      } else if (empty($this->pmDatos)) {
         $this->pcError = '<DATA><ERROR>DATOS VARIOS NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO';
         return false;
      } else if (empty($this->pcCodEmp) AND !$this->plNuevo) {
         $this->pcError = '<DATA><ERROR>CODIGO DE EMPRESA NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $this->pcDescri = strtoupper($this->pcDescri);
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
          $this->pcError = $loSql->pcError;
          return false;
      }
      if ($this->plNuevo) {
         $RS = $loSql->omExec("SELECT cCodEmp FROM S02MEMP WHERE cCodEmp = '$this->pcCodEmp'");
         $laFila = $loSql->fetch($RS);
         if (!empty($laFila[0])) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE EMPRESA REPETIDO</ERROR></DATA>';
            return false;
         }
         $lcSql = "INSERT INTO S02MEMP (cCodEmp, cDescri, cEstado, mDatos, cCodUsu) VALUES ('$this->pcCodEmp', '$this->pcDescri', '$this->pcEstado', '$this->pmDatos', '$this->pcCodUsu')";
      } else {
         $RS = $loSql->omExec("SELECT cCodEmp FROM S02MEMP WHERE cCodEmp = '$this->pcCodEmp'");
         $laFila = $loSql->fetch($RS);
         if ($laFila[0] == null) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE EMPRESA NO DEFINIDO</ERROR></DATA>';
            return false;
         }
         $lcSql = "UPDATE S02MEMP SET cDescri = '$this->pcDescri', cEstado = '$this->pcEstado', mDatos = '$this->pmDatos', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cCodEmp = '$this->pcCodEmp'";
      }
      $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      return true;
   }

    // Buscar empresas
    public function omBuscarEmpresas()
    {
        // Condiciones de excepcion
        if (empty($this->pcDescri)) {
            $this->pcError = '<DATA><ERROR>DESCRIPCION INVALIDA</ERROR></DATA>';
            return false;
        }
        $this->paCodEmp = null;
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        $lcDescri = trim($this->pcDescri) . '%';
        $lcSql = "SELECT cCodEmp, cDescri FROM S02MEMP WHERE cDescri LIKE '$lcDescri' ORDER BY cDescri";
        $RS = $loSql->omExec($lcSql);
        $loSql->omDisconnect();
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
            $this->paCodEmp[] = array($laFila[0], $laFila[1]);
            $i++;
        }
        if ($i == 0) {
            $this->pcError = '<DATA><ERROR>NO HAY EMPRESAS QUE CUMPLAN CONDICION</ERROR></DATA>';
            $llOk = false;
        }
        return $llOk;
    }

    public function omCargarAreas()
    {
        $llOk = $this->omAplicar();
        if (!$llOk) {
            return false;
        }
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        // Carga Area
        $RS = $loSql->omExec("SELECT cArea, cDescri, cEstado FROM S02tare WHERE cCodEmp = '$this->pcCodEmp'");
        $laFila = $loSql->fetch($RS);
        $this->pcDescri = $laFila[0];
        if ($this->pcDescri == null) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE AREA NO EXISTE</ERROR></DATA>';
            return false;
        }
        // Carga normas
        $RS = $loSql->omExec("SELECT cCodNor, cDescri FROM S02MNOR WHERE cEstado = 'A' ORDER BY cDescri");
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
            $this->paCodNor[] = array($laFila[0], $laFila[1]);
            $i++;
        }
        if ($i == 0) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>NO HAY NORMAS DEFINIDAS</ERROR></DATA>';
            $llOk = false;
        }
        // Trae normas del curso
        $lcSql = "SELECT cCodNor, mDatos FROM S02PCUR WHERE cCodCur = '$this->pcCodCur' ORDER BY cCodNor";
        $RS = $loSql->omExec($lcSql);
        $loSql->omDisconnect();
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
            $i++;
            $this->paData[$i] = array($i, $laFila[0], $laFila[1]->load());
        }
        return $llOk;
    }

    public function omTablasMtoAreas()
    {
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        $lcSql = "SELECT cDescri FROM S02MLIC ORDER BY cDescri";
        $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_S02TTAB WHERE cCodTab = '001'");
        $loSql->omDisconnect();
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
            $i++;
            $this->paEstado[] = array($laFila[0], $laFila[1]);
        }
        if ($i == 0) {
            $this->pcError = '<DATA><ERROR>TABLA [001] NO ESTA DEFINIDA EN [S01TTAB]</ERROR></DATA>';
            return false;
        }
        return true;
    }

    public function omInitMtoAreas()
    {
        $llOk = $this->omAplicar();
        if (!$llOk) {
            return false;
        }
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        $RS = $loSql->omExec("SELECT cArea, cDescri, cEstado FROM S02TARE WHERE cCodEmp = '$this->pcCodEmp' ORDER BY cArea");
        $loSql->omDisconnect();
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
            $i++;
            $this->paData[$i] = array($i, $laFila[0], $laFila[1], $laFila[2]);
        }
        return true;
    }

    public function omGrabarAreas()
    {
        if (empty($this->paData) or count($this->paData) == 0) {
            $this->pcError = '<DATA><ERROR>NO HAY DATOS PARA GRABAR</ERROR></DATA>';
            return false;
        } else if (empty($this->pcCodEmp)) {
            $this->pcError = '<DATA><ERROR>CODIGO DE EMPRESA VACIO</ERROR></DATA>';
            return false;
        } else if (empty($this->pcCodUsu)) {
            $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
            return false;
        }
        // Evalua repetidos
        $llOk = fxValRepeated($this->paData, 1);
        if ($llOk) {
            $this->pcError = '<DATA><ERROR>HAY CODIGOS REPETIDOS</ERROR></DATA>';
            return false;
        }
        // Valida que datos no este vacios
        $i = 0;
        foreach ($this->paData as $laFila) {
            if (empty($laFila[1]) or empty($laFila[2])) {
                $this->pcError = '<DATA><ERROR>ERROR HAY DATOS VACIOS</ERROR></DATA>';
                return false;
            }
        }
        // Conecta con DB
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        // Valida Empresa
        $RS = $loSql->omExec("SELECT cDescri FROM S02MEMP WHERE cCodEmp = '$this->pcCodEmp'");
        $laFila = $loSql->fetch($RS);
        if ($laFila[0] == null) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE EMPRESA NO EXISTE</ERROR></DATA>';
            return false;
        }
        foreach ($this->paData as $i) {
            $RS = $loSql->omExec("SELECT cArea FROM S02TARE WHERE cCodEmp = '$this->pcCodEmp' AND cArea = '$i[1]'");
            $laFila = $loSql->fetch($RS);
            if ($laFila[0] == null) {
                $lcSql = "INSERT INTO S02TARE (cCodEmp, cArea, cDescri, cEstado, cCodUsu) VALUES ('$this->pcCodEmp', '$i[1]', '$i[2]', '$i[3]', '$this->pcCodUsu')";
            } else {
                $lcSql = "UPDATE S02TARE SET cDescri = '$i[2]', cEstado = '$i[3]', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cCodEmp = '$this->pcCodEmp' AND cArea = '$i[1]'";
            }
            $llOk = $loSql->omExec($lcSql);
            if (!$llOk)
                break;
        }
        $loSql->omDisconnect();
        if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL GRABAR AREAS. REINTENTE</ERROR></DATA>';
        }
        return $llOk;
    }

}


//////////////////////////

   