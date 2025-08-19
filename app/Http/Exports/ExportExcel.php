<?php
namespace App\Http\Exports;

use Maatwebsite\Excel\Concerns\FromArray;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;
use PhpOffice\PhpSpreadsheet\Style\Border;
use PhpOffice\PhpSpreadsheet\Cell\Coordinate;

class ExportExcel implements FromArray, WithHeadings, ShouldAutoSize, WithStyles
{
    protected $columns;
    protected $rows;

    public function __construct(array $columns, $rows)
    {
        $this->columns = $columns;
        $this->rows = $rows;
    }

    public function headings(): array
    {
        return collect($this->columns)->pluck('label')->toArray();
    }

    public function array(): array
    {
        if(is_object($this->rows[0])){
            return collect($this->rows)->map(function ($row) {
                return collect($this->columns)->map(function ($col) use ($row) {
                    $field = $col['name'];
                    return $row->$field ?? null;
                })->toArray();
            })->toArray();
        }

        return collect($this->rows)->toArray();
    }

    public function styles(Worksheet $sheet)
    {
        $columnCount = count($this->columns);
        $rowCount = count($this->rows) + 1; // +1 for header
        $endColumn = Coordinate::stringFromColumnIndex($columnCount);

        $fullRange = "A1:{$endColumn}{$rowCount}";

        // Border dan warna semua cell
        $sheet->getStyle($fullRange)->applyFromArray([
            'font' => [
                'color' => ['argb' => 'FF000000'],
            ],
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                ],
            ],
        ]);

        // Style khusus header
        $sheet->getStyle("A1:{$endColumn}1")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => 'center',
                'vertical'   => 'center',
            ],
        ]);

        return [];
    }
}
