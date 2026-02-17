<?php

namespace App\Http\Exports;

use App\Http\Helpers\CustomHelper;
use Carbon\Carbon;
use App\Models\PurchaseOrder;
use Maatwebsite\Excel\Concerns\WithStyles;
use PhpOffice\PhpSpreadsheet\Style\Border;
use Maatwebsite\Excel\Concerns\WithMapping;
use Maatwebsite\Excel\Concerns\WithHeadings;
use PhpOffice\PhpSpreadsheet\Style\Alignment;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\ShouldAutoSize;
use PhpOffice\PhpSpreadsheet\Worksheet\Worksheet;

class ExportVendorPo implements FromCollection, WithHeadings, WithMapping, ShouldAutoSize, WithStyles
{
    private $type;
    private $year;
    private $rowNumber = 0;

    function __construct($type, $year = null)
    {
        if ($type == 'open') {
            $this->type = 'open';
        } else if ($type == 'close') {
            $this->type = 'close';
        } else {
            $this->type = 'all';
        }
        $this->year = $year;
    }

    public function collection()
    {
        $dataset = new PurchaseOrder;

        if ($this->type != 'all') {
            $dataset = $dataset->where('status', strtolower($this->type));
        }

        if ($this->year && $this->year != 'all') {
            $dataset = $dataset->whereYear('date_po', $this->year);
        }
        return $dataset->select(
            'subkon_id',
            'po_number',
            'date_po',
            'job_name',
            'job_description',
            'job_value',
            'tax_ppn',
            'total_value_with_tax',
            'due_date',
            'status'
        )->get();
    }

    public function headings(): array
    {
        return [
            'No',
            trans('backpack::crud.subkon.column.name'),
            trans('backpack::crud.po.column.po_number'),
            trans('backpack::crud.po.column.date_po'),
            trans('backpack::crud.po.column.job_name'),
            trans('backpack::crud.po.column.job_description'),
            trans('backpack::crud.po.column.job_value'),
            trans('backpack::crud.po.column.tax_ppn'),
            trans('backpack::crud.po.column.total_value_with_tax'),
            trans('backpack::crud.po.column.due_date'),
            trans('backpack::crud.po.column.status'),
        ];
    }

    public function map($entry): array
    {
        $this->rowNumber++;

        $subkonName = $entry->subkon->name ?? '-';
        $date_po = $entry->date_po
            ? Carbon::parse($entry->date_po)->translatedFormat('d F Y')
            : '-';

        $due_date = $entry->due_date
            ? Carbon::parse($entry->due_date)->translatedFormat('d F Y')
            : '-';

        $status = strtoupper($entry->status ?? '-');

        return [
            $this->rowNumber,
            $subkonName,
            $entry->po_number ?? '-',
            $date_po,
            $entry->job_name ?? '-',
            $entry->job_description ?? '-',
            $entry->job_value ?? 0,
            $entry->tax_ppn ?? 0,
            $entry->total_value_with_tax ?? 0,
            $due_date,
            $status,
        ];
    }

    public function styles(Worksheet $sheet)
    {
        $highestColumn = 'K'; // Ganti jika jumlah kolom berubah

        $sheet->getStyle("A1:{$highestColumn}" . ($this->rowNumber + 1))->applyFromArray([
            'borders' => [
                'allBorders' => [
                    'borderStyle' => Border::BORDER_THIN,
                    'color' => ['argb' => 'FF000000'],
                ],
            ],
        ]);

        $sheet->getStyle("A1:{$highestColumn}1")->applyFromArray([
            'font' => ['bold' => true],
            'alignment' => [
                'horizontal' => Alignment::HORIZONTAL_CENTER,
                'vertical' => Alignment::VERTICAL_CENTER,
            ],
        ]);
    }
}
