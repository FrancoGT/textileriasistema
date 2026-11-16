<?php

declare(strict_types=1);

namespace App\Report;

/** Builds the "REPORTE DE PEDIDOS" document of the dispatch screen. */
final class OrderReportPdf
{
    private const COLUMNS = ['PEDIDO' => 80, 'CODIGO' => 80, 'NOMBRE' => 200, 'CANTIDAD' => 70, 'FECHA DE TERMINO' => 80];

    /** @param list<list<string>> $lines order, product, description, quantity, date */
    public function render(array $lines): string
    {
        $pdf = new class('P', 'pt', 'A4') extends \FPDF {
            public function Header(): void
            {
                $this->SetFont('Helvetica', '', 10);
                $this->Text(220, 42, 'REPORTE DE PEDIDOS');
                $this->Line(20, 72, 550, 72);
            }

            public function Footer(): void
            {
                $this->SetFont('Helvetica', '', 10);
                $this->Line(20, 812, 550, 812);
                $this->Text(490, 824, 'PAGINA '.$this->PageNo());
            }
        };
        $pdf->SetTitle('Reporte de Pedidos');
        $pdf->SetSubject('REPORTE');
        $pdf->SetMargins(43, 80, 43);
        $pdf->SetAutoPageBreak(true, 113);
        $pdf->AddPage();
        $left = (595.28 - array_sum(self::COLUMNS)) / 2;
        $pdf->SetFont('Helvetica', 'B', 10);
        $pdf->SetX($left);
        foreach (self::COLUMNS as $title => $width) {
            $pdf->Cell($width, 16, $title, 1, 0, 'C');
        }
        $pdf->Ln();
        $pdf->SetFont('Helvetica', '', 9);
        foreach ($lines as $index => $line) {
            $pdf->SetFillColor(230, 230, 230);
            $pdf->SetX($left);
            foreach (array_values(self::COLUMNS) as $column => $width) {
                $pdf->Cell($width, 14, $this->latin1($line[$column] ?? ''), 1, 0, \in_array($column, [3, 4], true) ? 'C' : 'L', 1 === $index % 2);
            }
            $pdf->Ln();
        }

        return $pdf->Output('S');
    }

    // FPDF core fonts use Windows-1252.
    private function latin1(string $text): string
    {
        return mb_convert_encoding($text, 'Windows-1252', 'UTF-8');
    }
}
