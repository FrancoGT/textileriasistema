<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Mantenimiento de Productos
//-------------------------------------
class CMtoProductos extends CBase {
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
   
   
   // Grabar Producto En Pedidos
   public function omNuevoProducto() {
      if (empty($this->pcDescri)) {
         $this->pcError = '<DATA><ERROR>DESCRIPCION DEL PRODUCTO ESTA VACIO</ERROR></DATA>';
         return false;
      }
      $this->pcDescri = strtoupper($this->pcDescri);
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $RS = $loSql->omExec("SELECT MAX(cCodProd) FROM E01MPROD WHERE SUBSTR(cCodProd, 1, 1) != '9'");
      $laFila = $loSql->fetch($RS);
      $lcCodPer = '000000';
      if (!empty($laFila[0]))
          $lcCodPer = $laFila[0];
      $lnCodPer = (int)$lcCodPer + 1;
      $lcCodPer = (string)$lnCodPer;
      $lcCodPer = right('00000'.trim($lcCodPer), 6);
      $lcSql = "INSERT INTO E01MPROD (cCodProd, cDescri, cEstado,cTipo cCodUsu, tmodifi) VALUES ('$lcCodPer', '$this->pcDescri','$this->pcEstado','N', '$this->pcCodUsu',SYSDATE())";
	echo '<br>1)'.$lcSql;
      $llOk = $loSql->omExec($lcSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR EN GRABACIÓN PRODUCTO</ERROR></DATA>';
         return false;
      }	  
      $loSql->omDisconnect();
      return true;
   }
   
   // Grabar Producto En Mtenimiento
   public function omGrabarTabla() {
      if (empty($this->pcDescri)) {
         $this->pcError = '<DATA><ERROR>DESCRIPCION DE PRODUCTO VACIA</ERROR></DATA>';
         return false;
      }elseif (empty($this->paData) or count($this->paData) == 0) {
         $this->pcError = '<DATA><ERROR>DETALLE DE INSUMOS NO DEFINIDA</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }

      // Borra anterior data
	  $lcSql="DELETE FROM E01PPROD WHERE cCodProd = '$this->pcCodProd'";
      $llOk = $loSql->omExec($lcSql);
      if (!$llOk) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR DETALLE DE PRODUCTO ['.$this->pcCodProd.']</ERROR></DATA>';
         return false;
      }
      foreach ($this->paData as $i) {
         $lcCodIns = strtoupper(trim($i[0]));
         $lcSql = "INSERT INTO E01PPROD (cCodProd, cCodIns,cEstado,cCodUsu, tModifi) VALUES 
                 ('$this->pcCodProd', '$i[0]', 'A', '$this->pcCodUsu', SYSDATE())";
				 echo '<br>3)'.$lcSql;
         $llOk = $loSql->omExec($lcSql);
         if (!$llOk) {
	    echo '<br>'.$lcSql;
     		$loSql->omDisconnect();
            $this->pcError = '<DATA><ERROR>ERROR AL ELIMINAR DETALLE DE PRODUCTO ['.$this->pcCodProd.']</ERROR></DATA>';
			return false;
         }
      }
      $loSql->omDisconnect();
      return true;
   }

   // Inicia mantenimiento de Productos
   public function omCargarMtoProductos() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga  producto
      $RS = $loSql->omExec("SELECT CCODPROD,CDESCRI,CDESEST,CDESTIP FROM V_E01MPROD");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paDataP[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3]);
      }
	  if ($i == 0) {
          $loSql->omDisconnect();
          $this->pcError = '<DATA><ERROR>NO HAY PRODUCTOS</ERROR></DATA>';
          return false;
      }
	  //Carga estados
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '001'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paEstado[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY ESTADOS DEFINIDOS. TABLA [001]</ERROR></DATA>';
         return false;
      }
	  //Carga Insummos
      //$RS = $loSql->omExec("SELECT CCODINS,CDESCRI FROM E01MINS WHERE CESTADO='A'");
	  $RS = $loSql->omExec("SELECT CCODINS,CDESCRI FROM E01MINS");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodIns[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY INSUMOS DEFINIDOS. TABLA [001]</ERROR></DATA>';
         return false;
      }
	  $loSql->omDisconnect();
      return true;
   }

   // Aplica codigo de producto
   public function omAplicar() {
      
      if (empty($this->pcCodProd)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE PRODUCTO VACIO</ERROR></DATA>';
          return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      //cargar producto
	  $lcSql = "SELECT cDescri FROM E01MPROD WHERE CCODPROD='$this->pcCodProd'";
	  echo '<br>1)'.$lcSql;
      $RS = $loSql->omExec($lcSql);
      $loSql->omDisconnect();
      $laFila = $loSql->fetch($RS);
      if (empty($laFila[0])) {
	     $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>CODIGO DE PRODUCTO NO EXISTE</ERROR></DATA>';
         return false;
      }
      $this->pcDescri = $laFila[0];
      // Carga Insumos de producto
	  $lcSql="SELECT cCodIns,cDesIns,cDesUni,nPrecio FROM V_E01PPROD WHERE cCodProd = '$this->pcCodProd'";
	  echo '<br>2)'.$lcSql;
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paData[] = array($laFila[0], $laFila[1],$laFila[2],$laFila[3]);
      }
      if ($i == 0) {
	    $this->paData=null;
      }
	  $loSql->omDisconnect();
      return true;
   }
   
   // Trae datos del insumo
   public function omInsumo() {
      if (empty($this->pcCodIns)) {
          $this->pcError = '<DATA><ERROR>CODIGO DE INSUMO VACIO</ERROR></DATA>';
          return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Insumos de producto
	  $lcSql="SELECT cCodIns,cDescri,cDesUni,nPrecio FROM V_E01MINS WHERE cCodIns = '$this->pcCodIns'";
      $RS = $loSql->omExec($lcSql);
	  echo $lcSql;
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $i++;
         $this->paDataI[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3]);
      }
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY INSUMOS DEFINIDOS. TABLA [001]</ERROR></DATA>';
         return false;
      }
	  $loSql->omDisconnect();
      return true;
   }

}
?>