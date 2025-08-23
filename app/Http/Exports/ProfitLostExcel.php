<?php
namespace App\Http\Exports;


use Illuminate\Contracts\View\View;
use Maatwebsite\Excel\Concerns\FromView;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithStyles;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ProfitLostExcel implements FromView, WithStyles, WithColumnWidths, WithEvents
{
    protected $profit_lost;
    protected $report;

    public function __construct($profit_lost, $report)
    {
        $this->profit_lost = $profit_lost;
        $this->report = $report;
    }

    public function view(): View
    {
        return view('exports.profit-lost-detail-excel', [
            'profit_lost' => $this->profit_lost,
            'report' => $this->report,
        ]);
    }

    public function registerEvents(): array
    {
        return [
            AfterSheet::class => function(AfterSheet $event) {
                $sheet = $event->sheet->getDelegate();

                $sheet->getStyle('B6:B17')
                      ->getNumberFormat()
                      ->setFormatCode('#,##0');

            },
        ];
    }

    /**
     * Styling Excel
     */
    public function styles(Worksheet $sheet)
    {

        return [
            1 => ['font' => ['bold' => true, 'size' => 14], 'alignment' => ['horizontal' => 'center']],
            2 => ['font' => ['bold' => true, 'size' => 12], 'alignment' => ['horizontal' => 'center']],
            3 => ['font' => ['size' => 12], 'alignment' => ['horizontal' => 'center']],

            5 => ['font' => ['bold' => true], 'alignment' => ['horizontal' => 'center']],

            'A7' => ['font' => ['bold' => true]], // A
            'A11' => ['font' => ['bold' => false]], // Total Biaya
            'A12' => ['font' => ['bold' => false]], // C
            'A13' => ['font' => ['bold' => false]], // D
            'A14' => ['font' => ['bold' => true]], // E

            'A6:B17' => ['font' => ['size' => 14]],
        ];
    }

    /**
     * Atur lebar kolom
     */
    public function columnWidths(): array
    {
        return [
            'A' => 50,
            'B' => 25,
        ];
    }
}
