<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class SuppliersExportTemplate implements FromCollection, WithHeadings, WithEvents, WithColumnWidths
{
    use RegistersEventListeners;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return collect([]);
    }

    public function headings(): array
    {
        return [
            'name',
            'contact_person',
            'email',
            'phone',
            'address',
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {
        // Adjust row heights based on data length in the 'description' column (column L)
        for ($row = 2; $row <= 100; $row++) {
            $descriptionCell = $event->sheet->getCell("L{$row}");
            $descriptionLength = strlen($descriptionCell->getValue());
            $rowHeight = 15 + ($descriptionLength / 50 * 15); // Adjust the multiplier as needed
            $event->sheet->getRowDimension($row)->setRowHeight($rowHeight);
        }

        // Protect the sheet
        $event->sheet->getProtection()->setSheet(true);
        $event->sheet->getProtection()->setPassword('your_password');

        // Make specific columns writable
        $columnsToUnlock = range('A', 'F');
        foreach ($columnsToUnlock as $column) {
            for ($row = 2; $row <= 100; $row++) {
                $event->sheet->getStyle("{$column}{$row}")->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
            }
        }
    }

    public function columnWidths(): array
    {
        $headings = $this->headings();
        $columnWidths = [];
        foreach ($headings as $key => $heading) {
            // Adjust the width based on the length of the heading text
            $columnWidths[chr(65 + $key)] = strlen($heading) + 5; // Adding some extra padding
        }
        return $columnWidths;
    }
}
