<?php
require_once "Clases/CBase.php";

//-------------------------------------
// Mantenimiento de ALmacenes
//-------------------------------------
class CMtoDespacho extends CBase {
   public $paEstado, $pcEstado, $pcCodUsu, $pcCodPer, $pcCodSan, $pcPerEmp, $paCodPer, $paCodSan, $paCodNor, $paPerEmp,
          $pdFecIni, $pmObserv, $pdFecha, $pdCese, $pcNombre, $pcDirecc, $pcSexo, $paSexo, $paTipo, $pcTipo, $pmDatos,
          $paIndex, $paData, $paNombre, $paCodEmp, $plNuevo, $paEstCiv, $paCargo, $paCargos, $pcNroDni, $pcSector,
          $paSector, $pcDesSec, $paCarDis, $paAreas, $paCodPry, $pcNomAlm,$pcDirec;
   
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
   public function omInit() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Almacenes
      $RS = $loSql->omExec("SELECT cCodAlm,cNomAlm FROM E01MALM");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodAlm[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $this->paCodAlm[] = array('9999', 'CREAR NUEVO ALMACEN');
      }
      // Carga Estados
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
	  // Carga tipos d eAlmacen
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '003'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paTipo[] = array($laFila[0], $laFila[1]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TIPOS DE ALMACEN DEFINIDOS . TABLA [003]</ERROR></DATA>';
         return false;
      }

      return true;
   }
   
   public function omRealizaRecepcion(){
      if (empty($this->pcCodPed)) {
         $this->pcError = '<DATA><ERROR>NO SELECCIONO PEDIDO</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql="SELECT CESTADO FROM E01CPED WHERE CCODPED = '$this->pcCodPed'";
      $RS = $loSql->omExec($lcSql);    
      $laFila = $loSql->fetch($RS);
      $this->pcEstado = $laFila[0];
      //SI EL PEDIDO ESTAQ ESTÁ PENDIENTE
      if($this->pcEstado == 'A'){
        // Carga detalle de la cabecera
        $lcSql="UPDATE E01CPED SET CESTADO = 'I' WHERE CCODPED = '$this->pcCodPed'";
        $RS = $loSql->omExec($lcSql);    
        $this->pcMesg = 'PEDIDO ENTEGADO EXITOSAMENTE, SE CARGARÁ REPORTE';
      }
      elseif($this->pcEstado == 'I'){
          $this->pcMesg = 'PEDIDO YA HA SIDO ENTREGADO, SE CARGARÁ REPORTE';
      }else{
          $this->pcError = 'LA OPERACIÓN NO SE PUDO REALIZAR';
      }
      
      // Carga datos para pdf
      $lcSql="SELECT A.CCODPED, B.CCODPROD, C.CDESCRI, B.NCANT, A.DFECLIS FROM E01CPED A 
                INNER JOIN E01DPED B ON A.CCODPED = B.CCODPED
                INNER JOIN E01MPROD C ON B.CCODPROD = C.CCODPROD 
                WHERE A.CCODPED = '$this->pcCodPed'";      
      $RS = $loSql->omExec($lcSql);      
      $i=0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paData[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3], $laFila[4]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PEDIDOS</ERROR></DATA>';
         return false;
      }
      $loSql->omDisconnect();
      return true;
   }  

   // Inicializa historia de personal
   public function omInitPedidos() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga todos los tipos
      $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '098'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paTipo[] = array($laFila[0], $laFila[1]);
         $i++;
      }	  
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY TIPO DE PERSONA TABLA[098]</ERROR></DATA>';
         return false;
      }
	  //carga NroDNI
      $RS = $loSql->omExec("SELECT cCodCli,cNRoDni FROM E01MCLI WHERE cTipo='A'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paNroDNI[] = array($laFila[0], $laFila[1]);
         $i++;
      }	  
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAS NATURALES EN CLIENTES</ERROR></DATA>';
         return false;
      }
      //carga NroRUC
      $RS = $loSql->omExec("SELECT cCodCli,cNRoDni FROM E01MCLI WHERE cTipo!='A'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paNroRUC[] = array($laFila[0], $laFila[1]);
         $i++;
      }	  
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PERSONAS JURIDICAS EN CLIENTES</ERROR></DATA>';
         return false;
      }
      //cargar prioridades
	  $RS = $loSql->omExec("SELECT SUBSTR(cCodigo, 1, 1), cDescri FROM V_E01TTAB WHERE cCodTab = '002'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paPriori[] = array($laFila[0], $laFila[1]);
         $i++;
      }	  
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PRIORIDADES EN TABLA[002]</ERROR></DATA>';
         return false;
      }
	  //cargar productos
      $RS = $loSql->omExec("SELECT CCODPROD,CDESCRI  FROM E01MPROD WHERE cTipo = 'A' ");//EEMM
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paCodProd[] = array($laFila[0], $laFila[1]);
         $i++;
      }	  
	  if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>PRODUCTOS REGISTRADOS</ERROR></DATA>';
         return false;
      }	  
      $loSql->omDisconnect();
      return true;
   }

   public function omAplicar(){
      if (empty($this->pcCodAlm)) {
         $this->pcError = '<DATA><ERROR>NO SELECCIONO ALMACEN</ERROR></DATA>';
         return false;
      }
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Almacenes 
      $RS = $loSql->omExec("SELECT cCodAlm,cNomAlm,cDirec,cEstado,cTipo FROM E01MALM WHERE CCODALM ='$this->pcCodAlm'");
      $laFila = $loSql->fetch($RS);
	  $this->pcNomAlm=$laFila[1];
	  //Carga Insumos del Almacen
	  $RS = $loSql->omExec("SELECT CCODINS,CDESCRI,CDESUNI,NPRECIO,NCANT FROM V_E01PALM WHERE CCODALM ='$this->pcCodAlm'");
      $i = 0;
      while ($laFila = $loSql->fetch($RS)) {
         $this->paDataID[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3], $laFila[4]);
         $i++;
      }
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY INSUMOS EN ALMACEN</ERROR></DATA>';
         return false;
      }

	  $loSql->omDisconnect();
      return true;
   }
   
   
   //PEDIDOS PRA PRODUCCION
   public function omInitPedidosRep1(){
      $loSql = new CSql();      
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      // Carga Pedidos
      $lcSql="SELECT A.cCodPed, A.cCodCli, A.cNombre, A.DFECLIS, C.CDESCRI FROM V_E01CPED A "
              . "INNER JOIN E01CPED B ON A.CCODPED = B.CCODPED "
              . "INNER JOIN V_E01TTAB C ON C.CCODIGO = B.CESTADO "
              . "WHERE A.CESTADOPL='C' AND A.CESTADOPR='E' AND C.CCODTAB = '001'";
      $RS = $loSql->omExec($lcSql);
      $i = 0;      
      while ($laFila = $loSql->fetch($RS)) {
          if($laFila[4]=='ACTIVO'){
                  $laFila[4]= 'PENDIENTE';
          }elseif ($laFila[4]=='INACTIVO'){
                  $laFila[4]= 'ENTREGADO'; 
          }
         $this->paDataPed[] = array($laFila[0], $laFila[1], $laFila[2], $laFila[3], $laFila[4],$laFila[5]);
         $i++;
      }      
      if ($i == 0) {
         $loSql->omDisconnect();
         $this->pcError = '<DATA><ERROR>NO HAY PEDIDOS PARA PRODUCCION</ERROR></DATA>';
         return false;
      }
	  $loSql->omDisconnect();
	  return true;
   }

  
}
?>