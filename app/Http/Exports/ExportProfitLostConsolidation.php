<?php

namespace App\Http\Exports;

use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use Illuminate\Contracts\View\View;

class ExportProfitLostConsolidation implements FromView, WithStyles, WithColumnWidths
{
    protected $data;

    public function __construct($data)
    {
        $this->data = $data;
    }

    public function view(): View
    {
        return view('exports.profit-lost-consolidation-excel', [
            'data' => $this->data
        ]);
    }

    // Styling Excel
    public function styles(Worksheet $sheet)
    {
        $sheet->getStyle('A1:C12')->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THIN,
                    'color' => ['argb' => '000000'],
                ],
            ],
        ]);

        $sheet->getStyle('A1:C1')->getFont()->setBold(true);
        $sheet->getStyle('A7:C7')->getFont()->setBold(true);
        $sheet->getStyle('A10:C10')->getFont()->setBold(true);
        $sheet->getStyle('A15:C15')->getFont()->setBold(true);

        $sheet->getStyle('B:C')->getAlignment()->setHorizontal('right');

        return [];
    }

    public function columnWidths(): array
    {
        return [
            'A' => 40, // kolom deskripsi
            'B' => 20, // kolom biaya normal
            'C' => 20, // kolom biaya bold
        ];
    }
}
