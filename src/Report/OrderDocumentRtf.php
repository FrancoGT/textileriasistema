<?php

declare(strict_types=1);

namespace App\Report;

use Symfony\Component\DependencyInjection\Attribute\Autowire;

/**
 * Fills templates/documents/order.rtf, the order document that the client
 * receives. The table markup is the output of the legacy RTFTable class with
 * the settings that the order screen used.
 */
final class OrderDocumentRtf
{
    private const WIDTHS = [3500, 1900, 1500, 1500];
    private const HEADERS = ['DESCRIPCION DE PRODUCTO', 'PRECIO C/U', 'CANTIDAD', 'TOTAL'];
    private const CELL_BORDERS = '\clvertalt\clbrdrt\brdrth\brdrw1 \clbrdrl\brdrth\brdrw1 \clbrdrb\brdrth\brdrw1 \clbrdrr\brdrth\brdrw1 \cellx';

    public function __construct(#[Autowire('%kernel.project_dir%/templates/documents/order.rtf')] private readonly string $template)
    {
    }

    /**
     * @param array{code: string, name: string, address: string, number: string, phone: string, saleValue: float, tax: float, total: float} $order
     * @param list<array{0: string, 1: string, 2: string, 3: string}> $lines description, unit price, quantity, total
     */
    public function render(array $order, array $lines): string
    {
        $values = ['#*CODPED*#' => $order['code'], '#*CNOMBRE*#' => $order['name'], '#*CDIREC*#' => $order['address'],
            '#*CNUMERO*#' => $order['number'], '#*CTELEF*#' => $order['phone'], '#*VV*#' => self::number($order['saleValue']),
            '#*IGV*#' => self::number($order['tax']), '#*TOTAL*#' => self::number($order['total'])];
        $document = (string) file_get_contents($this->template);
        foreach ($values as $placeholder => $value) {
            $document = str_replace($placeholder, $this->text($value), $document);
        }

        return str_replace('#*TABLA*#', $this->table($lines), $document);
    }

    /** @param list<array{0: string, 1: string, 2: string, 3: string}> $lines */
    private function table(array $lines): string
    {
        $rows = [array_map(static fn (string $header): string => '\qc \b '.$header, self::HEADERS)];
        foreach ($lines as $line) {
            $rows[] = array_map(fn (string $cell): string => ' \qc '.$this->text($cell), $line);
        }
        $cells = '';
        $edge = 0;
        foreach (self::WIDTHS as $width) {
            $edge += $width;
            $cells .= self::CELL_BORDERS.$edge." \n";
        }
        $border = '\brdrs\brdrw1\brdrcf1 ';
        $table = "\n\\par\\ltrrow\\trowd\\trgaph0\\trleft0\\trql\\trhdr \n\\trbrdrt".$border.'\trbrdrl'.$border.'\trbrdrb'.$border.'\trbrdrr'.$border
            ."\n\\trbrdrh".$border.'\trbrdrv'.$border."\n";
        foreach ($rows as $index => $row) {
            if ($index > 0) {
                $table .= "\n\\row\\pard \n\n\\ltrrow\\trowd\\trgaph0\\trleft0\\trql \n";
            }
            $table .= $cells."\n\\pard\\intbl\\pard\\plain \n";
            foreach ($row as $cell) {
                $table .= '\pard\plain\intbl\ltrpar'.$cell."\\cell \n";
            }
        }

        return $table."\n\\row\\pard \n";
    }

    // The template declares code page 1252. Braces and backslashes would end the RTF group or start a control word.
    private function text(string $value): string
    {
        return mb_convert_encoding(strtr($value, ['\\' => '\\\\', '{' => '\{', '}' => '\}']), 'Windows-1252', 'UTF-8');
    }

    // PHP's own float to string conversion, as the legacy screen printed the amounts.
    private static function number(float $value): string
    {
        return (string) $value;
    }
}
