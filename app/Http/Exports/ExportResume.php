<?php
namespace App\Http\Exports;


use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportResume implements FromView, WithStyles, WithColumnWidths
{
    protected $data;
    protected $total;

    public function __construct($data)
    {
        // $this->data = $data;
        // $this->total = $total;
        $this->data = $data;
    }

    public function view(): View
    {
        return view('exports.resume-excel', [
            'data' => $this->data,
        ]);
    }

    // Styling mirip PDF
    public function styles(Worksheet $sheet)
    {
        // Header style

        $lastRow = 3;
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'F5F5F5']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin'
                ]
            ]
        ]);

        // Data table border
        $start_border = $lastRow + 1;
        $total_baris = count($this->data['list']['invoice_1']);
        $lastRow += ($total_baris + 2);
        $sheet->getStyle("A{$start_border}:C{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin'
                ]
            ]
        ]);

        // Total row style
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'EAEAEA']
            ]
        ]);

        // Nominal column align right
        // $sheet->getStyle("C2:C{$lastRow}")->getAlignment()->setHorizontal('right');

        // BATAS
        $lastRow = $lastRow + 2;
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'F5F5F5']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin'
                ]
            ]
        ]);

        // Data table border
        $start_border = $lastRow + 1;
        $total_baris = count($this->data['list']['invoice_2']);
        $lastRow += ($total_baris + 2);
        $sheet->getStyle("A{$start_border}:C{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin'
                ]
            ]
        ]);

        // Total row style
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'EAEAEA']
            ]
        ]);
        // $sheet->getStyle("C{$lastRow}:C{$lastRow}")->getAlignment()->setHorizontal('right');

        // BATAS
        $lastRow = $lastRow + 2;
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'F5F5F5']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin'
                ]
            ]
        ]);

        // Data table border
        $start_border = $lastRow + 1;
        $total_baris = count($this->data['list']['invoice_3']);
        $lastRow += ($total_baris + 2);
        $sheet->getStyle("A{$start_border}:C{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin'
                ]
            ]
        ]);

        // Total row style
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'EAEAEA']
            ]
        ]);

        // BATAS
        $lastRow = $lastRow + 2;
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'F5F5F5']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin'
                ]
            ]
        ]);

        // Data table border
        $start_border = $lastRow + 1;
        $total_baris = count($this->data['list']['invoice_4']);
        $lastRow += ($total_baris + 2);
        $sheet->getStyle("A{$start_border}:C{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin'
                ]
            ]
        ]);

        // Total row style
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'EAEAEA']
            ]
        ]);

        // BATAS
        $lastRow = $lastRow + 2;
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'F5F5F5']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin'
                ]
            ]
        ]);

        // Data table border
        $start_border = $lastRow + 1;
        $total_baris = count($this->data['list']['invoice_5']);
        $lastRow += ($total_baris + 2);
        $sheet->getStyle("A{$start_border}:C{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin'
                ]
            ]
        ]);

        // Total row style
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'EAEAEA']
            ]
        ]);

        // BATAS
        $lastRow = $lastRow + 2;
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'F5F5F5']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin'
                ]
            ]
        ]);

        // Data table border
        $start_border = $lastRow + 1;
        $total_baris = count($this->data['list']['invoice_6']);
        $lastRow += ($total_baris + 2);
        $sheet->getStyle("A{$start_border}:C{$lastRow}")->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin'
                ]
            ]
        ]);

        // Total row style
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'EAEAEA']
            ]
        ]);

        // BATAS
        $lastRow = $lastRow + 2;
        $sheet->getStyle("A{$lastRow}:C{$lastRow}")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => ['horizontal' => 'center', 'vertical' => 'center'],
            'fill' => [
                'fillType' => 'solid',
                'color' => ['rgb' => 'F5F5F5']
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => 'thin'
                ]
            ]
        ]);
        return [];
    }

    // Atur lebar kolom
    public function columnWidths(): array
    {
        return [
            'A' => 8,
            'B' => 40,
            'C' => 25,
        ];
    }
}
