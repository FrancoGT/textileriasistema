<?php
/**
 * Shim de compatibilidad PHPExcel -> PhpOffice\PhpSpreadsheet.
 *
 * PHPExcel 1.8 esta abandonado y no compila en PHP 8 (sintaxis $str{0}, each(), etc.).
 * Este archivo expone los nombres de clase que usa el codigo legacy
 * (PHPExcel, PHPExcel_IOFactory, PHPExcel_Style_*) sobre PhpSpreadsheet,
 * instalado con Composer (phpoffice/phpspreadsheet).
 */

use PhpOffice\PhpSpreadsheet\IOFactory;
use PhpOffice\PhpSpreadsheet\Spreadsheet;

if (!class_exists('PHPExcel', false)) {
    class PHPExcel extends Spreadsheet
    {
    }

    final class PHPExcel_IOFactory
    {
        /** Nombres de formato de PHPExcel -> PhpSpreadsheet */
        private const TYPES = [
            'Excel2007' => 'Xlsx',
            'Excel5' => 'Xls',
            'Excel2003XML' => 'Xml',
            'OOCalc' => 'Ods',
            'SYLK' => 'Slk',
            'Gnumeric' => 'Gnumeric',
            'CSV' => 'Csv',
            'HTML' => 'Html',
        ];

        public static function createReader(string $readerType = 'Excel2007'): \PhpOffice\PhpSpreadsheet\Reader\IReader
        {
            return IOFactory::createReader(self::TYPES[$readerType] ?? $readerType);
        }

        public static function createWriter(Spreadsheet $spreadsheet, string $writerType = 'Excel2007'): \PhpOffice\PhpSpreadsheet\Writer\IWriter
        {
            return IOFactory::createWriter($spreadsheet, self::TYPES[$writerType] ?? $writerType);
        }

        public static function load(string $filename): Spreadsheet
        {
            return IOFactory::load($filename);
        }

        public static function identify(string $filename): string
        {
            $type = IOFactory::identify($filename);

            return array_search($type, self::TYPES, true) ?: $type;
        }
    }

    class_alias(\PhpOffice\PhpSpreadsheet\Style\Alignment::class, 'PHPExcel_Style_Alignment');
    class_alias(\PhpOffice\PhpSpreadsheet\Style\Border::class, 'PHPExcel_Style_Border');
    class_alias(\PhpOffice\PhpSpreadsheet\Style\Color::class, 'PHPExcel_Style_Color');
    class_alias(\PhpOffice\PhpSpreadsheet\Style\Fill::class, 'PHPExcel_Style_Fill');
    class_alias(\PhpOffice\PhpSpreadsheet\Style\Font::class, 'PHPExcel_Style_Font');
    class_alias(\PhpOffice\PhpSpreadsheet\Style\NumberFormat::class, 'PHPExcel_Style_NumberFormat');
    class_alias(\PhpOffice\PhpSpreadsheet\Cell\DataType::class, 'PHPExcel_Cell_DataType');
    class_alias(\PhpOffice\PhpSpreadsheet\Worksheet\Worksheet::class, 'PHPExcel_Worksheet');
    class_alias(\PhpOffice\PhpSpreadsheet\Exception::class, 'PHPExcel_Exception');
}
