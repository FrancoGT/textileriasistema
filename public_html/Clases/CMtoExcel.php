<?php
require_once "Clases/CBase.php";
require_once "class/PHPExcel.php";

//-------------------------------------
// Mantenimiento de Empleados
//-------------------------------------
class CMtoExcel extends CBase {   
   public $plNuevo, $pcError;
   
   public function __construct() {
      parent::__construct();      
   }
   
   public function xxx() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
      $lcSql = "select ccodper , cnombre from s03mper";
      $RS = $loSql->omExec($lcSql);      
      $objPHPExcel = new PHPExcel();
      //$objPHPExcel->setActiveSheetIndex(0);
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B3', 'Código');      
      $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C3', 'Nombre');
      $rowCount = 4;
      while ($laFila = $loSql->fetch($RS)) {                     
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$rowCount, "$laFila[0]");
         $objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount)->getNumberFormat()->setFormatCode('0000');
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$rowCount, "$laFila[1]");
         $rowCount++;
      }
      $objPHPExcel->getActiveSheet()->setTitle("Hoja de prueba");      
      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');      
      $objWriter->save('prueba.xlsx');
   }

   public function sendToExcel1() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }      
      $objPHPExcel = new PHPExcel();
      $objReader = PHPExcel_IOFactory::createReader('Excel2007');      
      $objPHPExcel = $objReader->load('./Xls/S01MIFR.xlsx');//Solo para cabecera      
      $objPHPExcel->setActiveSheetIndex(0);      
      $objPHPExcel->getActiveSheet()->SetCellValue('B2', 'Hola mundo');
      // Color rojo al texto
      $objPHPExcel->getActiveSheet()->getStyle('B2')->getFont()->getColor()->setARGB(PHPExcel_Style_Color::COLOR_RED);
      // Texto alineado a la derecha
      $objPHPExcel->getActiveSheet()->getStyle('B2')->getAlignment()->setHorizontal(PHPExcel_Style_Alignment::HORIZONTAL_RIGHT);
      // Damos un borde a la celda
      $objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getTop()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
      $objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getBottom()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
      $objPHPExcel->getActiveSheet()->getStyle('B2')->getBorders()->getLeft()->setBorderStyle(PHPExcel_Style_Border::BORDER_THICK);
      //Guardamos el archivo en formato Excel 2007
      //Si queremos trabajar con Excel 2003, basta cambiar el 'Excel2007' por 'Excel5' y el nombre del archivo de salida cambiar su formato por '.xls'
      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');
      $objWriter->save("Archivo_salida.xlsx");
   }
   
   public function sendToExcel() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }      
      //$objPHPExcel = new PHPExcel();
      $objPHPExcel = new CXls();
      $objReader = PHPExcel_IOFactory::createReader('Excel2007');      
      $objPHPExcel = $objReader->load('./Xls/S01MIFR.xlsx');//Solo para cabecera
      $objWorksheet = $objPHPExcel->getActiveSheet();                  /*
      for ($col = 'B'; $col != 'Z'; $col++) { //ajusta la celda al tamaño de la celda
         $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
      }            */
      $col = 'A';
      $rowCount=0;
      foreach ($objWorksheet->getRowIterator() as $row) {
         $rowCount++;                        
         $cellIterator = $row->getCellIterator();
         $cellIterator->setIterateOnlyExistingCells(false);          
         if($rowCount==3)
            foreach ($cellIterator as $cell) {               
               $str= $cell->getValue();
               //$objPHPExcel->setActiveSheetIndex(0)->setCellValue($col.'3', "$str");               
               $col++;
            }       
      }
      //Bordes de Celda
      /*$objPHPExcel->getActiveSheet()->getStyle('B3:X3')->getBorders()->getAllBorders()->setBorderStyle(PHPExcel_Style_Border::BORDER_THIN);      
      //Color de Celda
      $objPHPExcel->getActiveSheet()->getStyle('B3:X3')->applyFromArray(
                              array('fill' => array(
                                                    'type' => PHPExcel_Style_Fill::FILL_SOLID,
                                                    'color' => array('rgb' => 'FFFF33')
                                              )
                              )
       );*/


      $lcSql = "SELECT CNUMERO, DFECHA, CHORA, CDESINC, CTREN, CDESEMP, CDESPRY, CSUBDIV, CKILOME, CDESLUG, CNOMMAQ, CNOMJEF, CNOMBRQ, CHERIDO, CCODFCR, CDESARE, NCOSTO, CDEMORA, MEQUINV, MDESCRI, MDETHER, MMEDADO, CCODUSU from V_S02MIFR";
                
      $RS = $loSql->omExec($lcSql);    
      $loSql->omDisconnect();      
      /*
      for($laFila = $loSql->fetch($RS), $col = 'B', $valorPos = 0; $col !='Y'; $col++,$rowCount++,$valorPos++){
         $objPHPExcel->getActiveSheet()->getColumnDimension($col)->setAutoSize(true);
         if(!(is_long($laFila[$rowCount]))){
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue($col.$rowCount, "$laFila[$rowCount]");
            if($col=='B')
               $objPHPExcel->getActiveSheet()->getStyle($col.$rowCount)->getNumberFormat()->setFormatCode('0000');
         }
         else {
            $aut = (string)$laFila[$rowCount]->load();
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('T'.$rowCount, "$aut");            
         }
      }
      $col = 'B';*/
      while ($laFila = $loSql->fetch($RS)) {                     
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('B'.$rowCount, "$laFila[0]");
         $objPHPExcel->getActiveSheet()->getStyle('B'.$rowCount)->getNumberFormat()->setFormatCode('0000');         
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('C'.$rowCount, "$laFila[1]");
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('D'.$rowCount, "$laFila[2]");
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('E'.$rowCount, "$laFila[3]");
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('F'.$rowCount, "$laFila[4]");
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('G'.$rowCount, "$laFila[5]");
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('H'.$rowCount, "$laFila[6]");
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('I'.$rowCount, "$laFila[7]");
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('J'.$rowCount, "$laFila[8]");
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('K'.$rowCount, "$laFila[9]");         
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('L'.$rowCount, "$laFila[10]");
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('M'.$rowCount, "$laFila[11]");
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('N'.$rowCount, "$laFila[12]");
         if($laFila[13]=='N')
            $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O'.$rowCount, "No");         
         else{
            if($laFila[13]=='S')
               $objPHPExcel->setActiveSheetIndex(0)->setCellValue('O'.$rowCount, "Si");         
         }         
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('P'.$rowCount, "$laFila[14]");
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('Q'.$rowCount, "$laFila[15]");
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('R'.$rowCount, "$laFila[16]");
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('S'.$rowCount, "$laFila[17]");
         $aut = (string)$laFila[18]->load();
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('T'.$rowCount, "$aut");
         $aut = (string)$laFila[19]->load();
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('U'.$rowCount, "$aut");
         $aut = (string)$laFila[20]->load();
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('V'.$rowCount, "$aut");
         $aut = (string)$laFila[21]->load();
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('W'.$rowCount, "$aut");
         $objPHPExcel->setActiveSheetIndex(0)->setCellValue('X'.$rowCount, "$laFila[22]");          
         $col++;
         $rowCount++;
      }
      $objPHPExcel->getActiveSheet()->setTitle("Pag 1");      
      $objWriter = PHPExcel_IOFactory::createWriter($objPHPExcel, 'Excel2007');            
      $objWriter->save('./Ficheros/S01MIFR_'.$this->gcCodUsu.'.xlsx');     
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
      // Carga Subdivisión
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
   public function InitExcelEditar() {
      $loSql = new CSql();
      $llOk = $loSql->omConnect();
      if (!$llOk) {
         $this->pcError = $loSql->pcError;
         return false;
      }
        // Carga Ferrovia
      $lcSql = "SELECT* FROM S02MIFR ";
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
      // Carga Subdivisión
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
      // Carga Estados
    /*  $lcSql = "SELECT A.CNUMERO, A.DFECHA, A.CHORA, H.CDESCRI AS INCIDENTE, A.CTREN, E.CDESCRI AS EMPRESA, F.CDESCRI AS PROYECTO, I.CDESCRI AS SIBDIVISION, A.CKILOME, K.CDESCRI AS LUGAR, B.CNOMBRE AS NOMDMAC, C.CNOMBRE AS NOMDJEF, D.CNOMBRE AS BREQUERO, J.CDESCRI AS HERIDO, A.CCODFCR, G.CDESCRI AS AREA, A.NCOSTO, A.CDEMORA, A.MEQUINV, A. MDESCRI, A.MDETHER, A.MMEDADO, A.CCODUSU, A.TMODIFI from S02MIFR A 
                INNER JOIN S02MPER B ON B.CCODPER = A.CCODMAQ 
                INNER JOIN S02MPER C ON A.CCODJEF = C.CCODPER 
                INNER JOIN S02MPER D ON D.CCODPER = A.CCODBRQ 
                INNER JOIN S02MEMP E ON A.CCODEMP = E.CCODEMP 
                INNER JOIN S02MPRY F ON A.CCODPRY = F.CCODPRY
                INNER JOIN S02TTAB G ON G.CCODIGO = A.CAREA 
                INNER JOIN S02TTAB H ON H.CCODIGO = A.CINCFER 
                INNER JOIN S02TTAB I ON I.CCODIGO = A.CSUBDIV 
                INNER JOIN S02TTAB J ON J.CCODIGO = A.CHERIDO 
                INNER JOIN S02TTAB K ON K.CCODIGO = A.CLUGAR WHERE G.CCODTAB = '030' AND H.CCODTAB = '043' AND I.CCODTAB = '020' AND J.CCODTAB = '005' AND K.CCODTAB = '037' AND K.CTIPREG='1'";
      $RS = $loSql->omExec($lcSql);            
      $laFila = $loSql->fetch($RS);
      $this->paNumero = $laFila[0];
      $this->paFecha = $laFila[1];
      $this->paHora = $laFila[2];
      $this->paIncFer = $laFila[3];
      $this->paTren = $laFila[4];
//      $this->paCodEmp = $laFila[5];
      $this->paCodJef = $laFila[6];
      $this->paCodBrq = $laFila[7];
      $this->paCodPry = $laFila[8];
      $this->paKilome = $laFila[9];
      $this->paLugar = $laFila[10];
      $this->paCodMaq = $laFila[11];*/
         
      
      
      
   }
   
   public function BuscarExcel() {   
      $lo = new CMtoExcel();
      $lo->gcCodUsu=$this->gcCodUsu;
      $lo->sendToExcel();      
      $lcFolder = './Ficheros/';                  
      if (!is_dir($lcFolder)) {
         return true;
      }            
      $this->paData = null;
      $lcDir = opendir($lcFolder);            
      while ($lcFile = readdir($lcDir)) {         
         if ($lcFile == 'S01MIFR_'.$this->gcCodUsu.'.xlsx'){             
            $this->paData[] = array($lcFolder.$lcFile, $lcFile);                     
         }
      } 
   }   
}
?>
