<?php
require_once "Clases/CBase.php";

class CMtoFactores extends CBase {
   public $paEstado = null;
   public $pcCodUsu = null;
   public $pcDescri = null;
   public $pcEstado = null;
   public $pcCodFcr = null;
   public $paTipo   = null;
   public $pcTipo   = null;
   public $paNorma  = null;
   public $paCodFcr = null;

   public function __construct() {
      parent::__construct();
      $this->paTipo   = null;
      $this->paEstado = null;
      $this->paCodFcr = null;
   }

   // Inicia mantenimiento de Factores
   public function omInit() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      //Cargar tipos de norma
      $RS = $loSql->omExec("SELECT cCodFcr, cDescri FROM S02MFCR WHERE cTipo = '0' AND SUBSTR(cCodFcr, 1, 1) != '0' ORDER BY cCodFcr");
      while ($laFila = $loSql->fetch($RS)) {
         $this->paTipo[] = array($laFila[0], $laFila[1]);
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

   public function omCargarFactorespPorTipo() {
      if (empty($this->pcTipo)) {
         $this->pcError = '<DATA><ERROR>TIPO DE NORMA NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      //Traer descripcion de cabecera de Factores
      $RS = $loSql->omExec("SELECT cDescri FROM S02MFCR WHERE cCodFcr = '$this->pcTipo'");
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE CABECERA DE NORMA NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcDescri = $laFila[0];
      $lcTipo = trim($this->pcTipo);
      $RS = $loSql->omExec("SELECT cCodFcr, cDescri, cEstado FROM S02MFCR WHERE cCodFcr LIKE '$lcTipo%' AND cTipo = '1' ORDER BY cCodFcr");
      $loSql->omDisconnect();
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paCodFcr[] = array($i, $laFila[0], $laFila[1], $laFila[1], '0');
      }
      return true;
   }

    // Grabar Factores
   public function omGrabarFactores() {
      if (empty($this->pcCodFcr)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE NORMA NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->paCodFcr) or count($this->paCodFcr) == 0) {
         $this->pcError = '<DATA><ERROR>NO HAY DETALLE DE Factores</ERROR></DATA>';
         return false;
      }
      // Valida detalle
      $lcTipo = trim($this->pcCodFcr);
      $j = strlen($lcTipo);
      foreach($this->paCodFcr as $i) {
         if (empty($i[1])) {
            $this->pcError = '<DATA><ERROR>CODIGO DE NORMA VACIO</ERROR></DATA>';
            return false;
         } else if (substr($i[1], 0, $j) != $lcTipo) {
            $this->pcError = '<DATA><ERROR>CODIGO DE NORMA NO CORRESPONDE A CABECERA</ERROR></DATA>';
            return false;
         } else if (trim($i[1]) === $lcTipo) {
            $this->pcError = '<DATA><ERROR>CODIGO DE NORMA ES IGUAL A CABECERA</ERROR></DATA>';
            return false;
         } if (empty($i[2])) {
            $this->pcError = '<DATA><ERROR>DESCRIPCION DE NORMA VACIA</ERROR></DATA>';
            return false;
         } if (empty($i[3])) {
            $this->pcError = '<DATA><ERROR>ESTADO DE NORMA NO DEFINIDA</ERROR></DATA>';
            return false;
         }
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      foreach($this->paCodFcr as $i) {
         if ($i[4] == '0') {
            $lcSql = "UPDATE S02MFCR SET cDescri = UPPER('$i[2]'), cEstado = '$i[3]', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cCodFcr = '$i[1]'";
         } else {
            $lcSql = "INSERT INTO S02MFCR (cCodFcr, cDescri, cEstado, cTipo, cCodUsu) VALUES ('$i[1]', '$i[2]', '$i[3]', '1', '$this->pcCodUsu')";
         }
         $llOk = $loSql->omExec($lcSql);
      }
      $loSql->omDisconnect();
      return true;
   }
   
   // Grabar cabecera Factores
   public function omGrabarCabecera() {
      if (empty($this->pcCodFcr)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE NORMA NO DEFINIDO</ERROR></DATA>';
         return false;
      } else if (empty($this->pcDescri)) {
          $this->pcError = '<DATA><ERROR>DESCRIPCION DE LA NORMA ESTA VACIA</ERROR></DATA>';
          return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = '<DATA><ERROR>CODIGO DE USUARIO NO DEFINIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcCodFcr = strtoupper(trim($this->pcCodFcr));
      if ($this->plNuevo) {
         $RS = $loSql->omExec("SELECT cCodFcr FROM S02MFCR WHERE cCodFcr = '$lcCodFcr'");
         $laFila = $loSql->fetch($RS);
         if (!empty($laFila[0])) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE NORMA YA EXISTE</ERROR></DATA>';
            return false;
         }
         $lcSql = "INSERT INTO S02MFCR (cCodFcr, cTipo, cDescri, cEstado, cCodUsu) VALUES (UPPER('$lcCodFcr'), '0', UPPER('$this->pcDescri'), 'A', '$this->pcCodUsu')";
      } else {
         $RS = $loSql->omExec("SELECT cCodFcr FROM S03MNOR WHERE cCodFcr = '$lcCodFcr'");
         $laFila = $loSql->fetch($RS);
         if (empty($laFila[0])) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE NORMA NO DEFINIDO</ERROR></DATA>';
            return false;
         }
         $lcSql = "UPDATE S02MFCR SET cDescri = '$this->pcDescri', cCodUsu = '$this->pcCodUsu', tModifi = SYSDATE WHERE cCodFcr = '$lcCodFcr'";
      }
      $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      return true;
   }
   
   
    // Aplica codigo de Factores
    public function omAplicarFactores()
    {
        // Condiciones de excepcion
        if (empty($this->pcCodFcr)) {
            $this->pcError = 'CODIGO DE Factores VACIO';
            return false;
        }
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        // Carga Factores
        $lcSql = "SELECT cDescri, cEstado FROM S02MFCR WHERE cCodFcr = '$this->pcCodFcr'";
        $RS = $loSql->omExec($lcSql);
        $loSql->omDisconnect();
        $laFila = $loSql->fetch($RS);
        $this->pcDescri = $laFila[0];
        if (empty($this->pcDescri)) {
            $this->pcError = 'CODIGO DE NORMA NO EXISTE';
            $llOk = false;
        }
        $this->pcEstado = $laFila[1];
        return $llOk;
    }

    //////////////////////////////////////////


   // Grabar tipo (cabecera) de norma
   public function omGrabarTipo() {
      if (empty($this->pcTipo)) {
         $this->pcError = 'TIPO DE NORMA NO DEFINIDO';
         return false;
      } else if (empty($this->pcDescri)) {
         $this->pcError = 'DESCRIPCION DE LA NORMA ESTA VACIA';
         return false;
      } else if (empty($this->pcCodUsu)) {
         $this->pcError = 'CODIGO DE USUARIO NO DEFINIDO';
         return false;
      } else if (empty($this->pcTipo) AND !$this->plNuevo) {
         $this->pcError = 'TIPO DE NORMA NO DEFINIDO';
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
         $RS = $loSql->omExec("SELECT cCodFcr FROM S03MNOR WHERE cCodFcr = '$this->pcCodFcr'");
         $laFila = $loSql->fetch($RS);
         if (!empty($laFila[0])) {
            $this->pcError = 'TIPO DE NORMA REPETIDO';
            return false;
         }
         $lcSql = "INSERT INTO S03MNOR (cCodFcr, cTipo, cDescri, cEstado, cCodUsu) VALUES ('$this->pcTipo', '0', '$this->pcDescri', 'A','$this->pcCodUsu')";
      } else {
         $RS = $loSql->omExec("SELECT cCodFcr FROM S02MFCR WHERE cCodFcr = '$this->pcCodFcr'");
         $laFila = $loSql->fetch($RS);
         if ($laFila[0] == null) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE NORMA NO DEFINIDO</ERROR></DATA>';
            return false;
         }
         $lcSql = "UPDATE S02MFCR SET cDescri = '$this->pcDescri', cEstado = '$this->pcEstado', cCodUsu = '$this->pcCodUsu', tModifi = NOW() WHERE cCodFcr = '$this->pcCodFcr'";
      }
      $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      return true;
    }


    // Buscar empresas
    public function omBuscarFactores()
    {
        // Condiciones de excepcion
        if (empty($this->pcDescri)) {
            $this->pcError = '<DATA><ERROR>DESCRIPCION INVALIDA</ERROR></DATA>';
            return false;
        }
        $this->paCodFcr = null;
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        $lcDescri = trim($this->pcDescri) . '%';
        $lcSql = "SELECT cCodFcr, cDescri FROM S02MFCR WHERE cDescri LIKE '$lcDescri' ORDER BY cDescri";
        $RS = $loSql->omExec($lcSql);
        $loSql->omDisconnect();
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
            $this->paCodFcr[] = array($laFila[0], $laFila[1]);
            $i++;
        }
        if ($i == 0) {
            $this->pcError = '<DATA><ERROR>NO HAY Factores QUE CUMPLAN CONDICION</ERROR></DATA>';
            $llOk = false;
        }
        return $llOk;
    }

    //////////////////////////

    // Buscar activos fijos
    public function xomBuscarActivoFijo()
    {
        // Condiciones de excepcion
//      if (empty($this->pcCodAfj)) {
//         $this->pcError = 'CODIGO DE ACTIVO FIJO VACIO';
//         return false;
//      }
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        $lcSql = "SELECT cDescri, dFecUso FROM ADM.E03MAFJ WHERE cCodAfj = '$this->pcCodAfj' ORDER BY cDescri";
        $RS = $loSql->omExec($lcSql);
        $loSql->omDisconnect();
        //if OJOFPM
        $laFila = $loSql->fetch($RS);
        $this->pcDescri = $laFila[0];
        if (empty($this->pcDescri)) {
            $this->pcError = 'CODIGO DE ACTIVO FIJO NO EXISTE';
            $llOk = false;
        }
        $this->pdFecUso = $laFila[1];
        return $llOk;
    }

    public function omMtoActivFijo()
    {
        // Condiciones de excepcion
        if (empty($this->pcCodAfj)) {
            $this->pcError = 'CODIGO DE ACTIVO FIJO VACIO';
            return false;
        }
        $llOk = $this->omAplicarActivoFijo();
        if (!$llOk)
            return false;
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        // Trae tipo de relacion
        $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 3), cDescri FROM V_S01TTAB WHERE cCodTab = '805'");
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
            $i++;
            $this->paTipMto[] = array($laFila[0], $laFila[1]);
        }
        if ($i == 0) {
            $loSql->omDisconnect();
            $this->pcError = 'NO HAY TIPOS DE RELACION DEFINIDOS [337]';
            return false;
        }
        // Trae derecho habientes
        $lcSql = "SELECT cconcep, dprogra FROM ADM.E03DMTO WHERE cCodAfj = '$this->pcCodAfj'";
        $RS = $loSql->omExec($lcSql);
        while ($laFila = $loSql->fetch($RS)) {
            $this->paDatMto[] = array($laFila[0], $laFila[1]);;
        }
        $loSql->omDisconnect();
        return $llOk;
    }

    // Grabar fecha de uso del activo fijo
    public function omGrabarFechaUso()
    {
        // Condiciones de excepcion
        if (empty($this->pcCodAfj)) {
            $this->pcError = 'CODIGO DE ACTIVO FIJO VACIO';
            return false;
        } else if (empty($this->pcFecUso)) {
            $this->pcError = 'FECHA DE USO VACIA';
            return false;
        }
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        $lcSql = "UPDATE ADM.E03MAFJ SET dFecUso = '$this->pcFecUso' WHERE cCodAfj = '$this->pcCodAfj'";
        $loSql->omExec($lcSql);
        $loSql->omDisconnect();
        return true;
    }

    // Grabar activo fijo
    public function omGrabarActivoFijo()
    {
        // Condiciones de excepcion
        if (!$this->plNuevo and empty($this->pcCodAfj)) {
            $this->pcError = 'CODIGO DE ACTIVO FIJO VACIO';
            return false;
        } else if (empty($this->pcDescri)) {
            $this->pcError = 'DESCRIPCION DE ACTIVO FIJO VACIA';
            return false;
        } else if (empty($this->pcMarca)) {
            $this->pcError = 'MARCA DE ACTIVO FIJO VACIA';
            return false;
        } else if (!$this->mxValDate($this->pdFecIng)) {
            $this->pcError = 'FECHA INCORRECTA';
            return false;
        } else if (empty($this->pcSerie)) {
            $this->pcError = 'NUMERO DE SERIE DE ACTIVO FIJO VACIA';
            return false;
        } else if (empty($this->pcCodUsu)) {
            $this->pcError = 'CODIGO DE USUARIO VACIO';
            return false;
        } else if (!isValidFloat($this->pnPrecio)) {
            $this->pcError = 'PRECIO NO VALIDO';
            return false;
        } else if (!isValidFloat($this->pnTipCam)) {
            $this->pcError = 'TIPO DE CAMBIO NO VALIDO';
            return false;
        }
        $this->pcDescri = strtoupper($this->pcDescri);
        $this->pcMarca = strtoupper($this->pcMarca);
        $this->pcSerie = strtoupper($this->pcSerie);
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        if (!$this->plNuevo) {
            $RS = $loSql->omExec("SELECT cCodAfj FROM ADM.E03MAFJ WHERE cCodAfj = '$this->pcCodAfj'");
            $laFila = $loSql->fetch($RS);
            if ($laFila[0] == null) {
                $this->pcError = '<DATA><ERROR>CODIGO DE ACTIVO FIJO [' . $this->pcCodAfj . '] NO EXISTE</ERROR></DATA>';
                $loSql->omDisconnect();
                return false;
            }
            $lcSql = "UPDATE ADM.E03MAFJ SET cDescri = '$this->pcDescri', cMarca = '$this->pcMarca', cSerie = '$this->pcSerie', cCodUsu = '$this->pcCodUsu' WHERE cCodAfj = '$this->pcCodAfj'";
        } else {
            // Tasa de depreciacion y cuenta contable
            $RS = $loSql->omExec("SELECT nTasDep, cCtaCnt FROM ADM.E03TAFJ WHERE cTipAfj = '$this->pcTipAfj'");
            $laFila = $loSql->fetch($RS);
            if ($laFila[0] == null) {
                $loSql->omDisconnect();
                $this->pcError = '<DATA><ERROR>TIPO DE ACTIVO FIJO [' . $this->pcTipAfj . '] NO ESTA DEFINIDO</ERROR></DATA>';
                return false;
            }
            $lnTasDep = $laFila[0];
            $lcCtaCnt = trim($laFila[1]);
            // Valida cuenta contable
            $lcSql = "SELECT COUNT(cCtaCnt) FROM CNT.D01MCTA WHERE cCtaCnt LIKE '$lcCtaCnt%'";
            //       die($lcSql);
            $RS = $loSql->omExec("SELECT COUNT(cCtaCnt) FROM CNT.D01MCTA WHERE cCtaCnt LIKE '$lcCtaCnt%'");
            $laFila = $loSql->fetch($RS);
            if ($laFila[0] == 0) {
                $loSql->omDisconnect();
                $this->pcError = '<DATA><ERROR>CUENTA CONTABLE [' . $lcCtaCnt . '] NO ESTA DEFINIDA</ERROR></DATA>';
                return false;
            } else if ($laFila[0] > 1) {
                $loSql->omDisconnect();
                $this->pcError = '<DATA><ERROR>CUENTA CONTABLE [' . $lcCtaCnt . '] NO ES DE ULTIMO NIVEL</ERROR></DATA>';
                return false;
            }
            // Ste codigo de AF.
            $RS = $loSql->omExec('SELECT MAX(cCodAfj) FROM ADM.E03MAFJ');
            $laFila = $loSql->fetch($RS);
            if ($laFila[0] == null) {
                $lcCodAfj = '000000';
            } else {
                $lcCodAfj = $laFila[0];
            }
            $lnCodAfj = $lcCodAfj + 1;
            $lcCodAfj = right('00000' . (string)$lnCodAfj, 6);
            $lcSql = "INSERT INTO ADM.E03MAFJ (cCodAfj, cDescri, cMarca, cSerie, dFecIng, cTipAfj, cCodAlm, cMoneda, nPrecio, nTipCam, cCodPrv, cEstado, cTipAdq, cEstBie, nTasDep, cCtaCnt, cCodUsu) VALUES ('$lcCodAfj', '$this->pcDescri', '$this->pcMarca', '$this->pcSerie', '$this->pdFecIng', '$this->pcTipAfj', '$this->pcCodAlm', '$this->pcMoneda', '$this->pnPrecio', '$this->pnTipCam', '$this->pcCodPrv', 'A', 'A', 'A', '$lnTasDep', '$lcCtaCnt', '$this->pcCodUsu')";
//         die($lcSql);
        }
        $llOk = $loSql->omExec($lcSql);
        $loSql->omDisconnect();
        if (!$llOk) {
            $this->pcError = '<DATA><ERROR>ERROR AL REALIZAR LA GRABACION. REINTENTE</ERROR></DATA>';
        }
        return $llOk;
    }

    public function omBuscarActivoFijo()
    {
        // Condiciones de excepcion
        if (empty($this->pcDescri)) {
            $this->pcError = '<DATA><ERROR>DESCRIPCION INVALIDA</ERROR></DATA>';
            return false;
        }
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        $lcDescri = trim($this->pcDescri) . '%';
        $lcSql = "SELECT cCodAfj, cDescri FROM ADM.E03MAFJ WHERE cDescri LIKE '$lcDescri' ORDER BY cDescri";
        $RS = $loSql->omExec($lcSql);
        if (!$RS) {
            $loSql->omDisconnect();
            $this->pcError = $loSql->pcError;
            return false;
        }
        while ($laFila = $loSql->fetch($RS)) {
            $this->paCodAfj[] = array($laFila[0], $laFila[1]);
        }
        $loSql->omDisconnect();
        $llOk = $this->omBuscarArea();
        return $llOk;
    }


    public function omCargarMtoActivoFijo()
    {
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        // Carga monedas
        $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), TRIM(cDescri) FROM V_S01TTAB WHERE cCodTab = '007'");
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
            $i++;
            $this->paMoneda[] = array($laFila[0], $laFila[1]);
        }
        if ($i == 0) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>TABLA DE MONEDAS [007] NO DEFINIDA</ERROR></DATA>';
            $llOk = false;
        }
        // Carga almacenes
        $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 2), TRIM(cDescri) FROM V_S01TTAB WHERE cCodTab = '326'");
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
            $i++;
            $this->paCodAlm[] = array($laFila[0], $laFila[1]);
        }
        if ($i == 0) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>TABLA DE ALMACENES [326] NO DEFINIDA</ERROR></DATA>';
            $llOk = false;
        }
        // Carga proveedores
        $RS = $loSql->omExec("SELECT cCodPrv, TRIM(cDescri) FROM ADM.B02MPRV ORDER BY cDescri");
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
            $i++;
            $this->paCodPrv[] = array($laFila[0], $laFila[1]);
        }
        if ($i == 0) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>NO HAY PROVEEDORES DEFINIDOS</ERROR></DATA>';
            $llOk = false;
        }
        // Carga tipos de activo fijo
        $RS = $loSql->omExec("SELECT cTipAfj, cDescri FROM ADM.E03TAFJ ORDER BY cTipAfj");
        $loSql->omDisconnect();
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
            $this->paTipAfj[] = array($laFila[0], $laFila[1]);
            $i++;
        }
        if ($i == 0) {
            $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE ACTIVO FIJO DEFINIDOS [E03TAFJ]</ERROR></DATA>';
            $llOk = false;
        }
        return $llOk;
    }

    public function omBuscarArea()
    {
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        // Trae tipo de relacion
        $RS = $loSql->omExec("SELECT cArea, cDescri FROM S01TARE WHERE LENGTH(TRIM(cArea)) = 6");
        while ($laFila = $loSql->fetch($RS)) {
            $this->paArea[] = array($laFila[0], $laFila[1]);
        }
        $loSql->omDisconnect();
        return $llOk;
    }

    public function omGrabarMantenimiento()
    {
        // Condiciones de excepcion
        if (empty($this->paDatMto)) {
            $this->pcError = 'FECHA DE MANTENIMIENTO VACIA';
            return false;
        } else if (empty($this->pcCodUsu)) {
            $this->pcError = 'CODIGO DE USUARIO VACIO';
            return false;
        }
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        $lcCodAfj = $this->paDatMto[0][0];
        $RS = $loSql->omExec("SELECT cCodAfj FROM ADM.E03MAFJ WHERE cCodAfj = '$lcCodAfj'");
        $laFila = $loSql->fetch($RS);
        IF ($laFila[0] == null) {
            $loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>CODIGO DE ACTIVO FIJO NO EXISTE</ERROR></DATA>';
            return false;
        }
        $loSql->omExec("DELETE FROM ADM.E03DMTO WHERE cCodAfj = '$lcCodAfj'");
        foreach ($this->paDatMto as $i) {
            $lcSql = "INSERT INTO ADM.E03DMTO (ccodafj, cconcep, dprogra, cglosa, ccodusu) VALUES
                  ('$i[0]', '$i[1]', '$i[2]', '', '$this->pcCodUsu')";
            $loSql->omExec($lcSql);
        }
        $loSql->omDisconnect();
        return true;
    }

    public function omBuscarAfj()
    {
        // Condiciones de excepcion
        /*if (empty($this->pcNombre)) {
           $this->pcError = 'NOMBRE A BUSCAR NO ESTA DEFINIDO';
           return false;
        }*/
        $loSql = new CSql();
        $llOk = $loSql->omConnect();
        if (!$llOk) {
            $this->pcError = $loSql->pcError;
            return false;
        }
        $lcNombre = trim($this->pcNombre) . '%';
        $lcSql = "SELECT cCodEmp, cNombre FROM ADM.E01MEMP WHERE cNombre LIKE '$lcNombre' ORDER BY cNombre";
        $RS = $loSql->omExec($lcSql);
        if (!$RS) {
            $loSql->omDisconnect();
            $this->pcError = $loSql->pcError;
            return false;
        }
        $i = 0;
        while ($laFila = $loSql->fetch($RS)) {
            $this->paCodEmp[] = array($laFila[0], $laFila[1], $i);
            $i++;
        }
        $loSql->omDisconnect();
        return $llOk;
    }
}

?>
