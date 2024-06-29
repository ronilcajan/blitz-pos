<?php
namespace App\Exports;

use App\Models\ProductCategory;
use App\Models\ProductUnit;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\RegistersEventListeners;
use Maatwebsite\Excel\Concerns\WithEvents;
use Maatwebsite\Excel\Concerns\WithHeadings;
use Maatwebsite\Excel\Events\AfterSheet;
use Maatwebsite\Excel\Concerns\WithColumnWidths;

class ProductsExportTemplate implements FromCollection, WithHeadings, WithEvents, WithColumnWidths
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
            'barcode',
            'size',
            'color',
            'dimension',
            'unit',
            'product_type',
            'brand',
            'manufacturer',
            'visible',
            'expiration_date',
            'description',
            'category',
            'sku',
            'min_quantity',
            'in_store',
            'in_warehouse',
            'base_price',
            'markup_price',
            'price',
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {
        $categories = ProductCategory::pluck('name')->toArray();
        $units = ProductUnit::pluck('name')->toArray();

        $categoriesValues = implode(',', $categories);
        $unitsValues = implode(',', $units);
        $typeValues = 'sellable,consumable';
        $publishedValues = 'published,hide';

        for ($row = 2; $row <= 100; $row++) {
            $validation = $event->sheet->getCell("F{$row}")->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input error');
            $validation->setError('Value is not in list.');
            $validation->setPromptTitle('Pick from list');
            $validation->setPrompt('Please pick a value from the drop-down list.');
            $validation->setFormula1('"' . $categoriesValues . '"');
        }

        for ($row = 2; $row <= 100; $row++) {
            $validation = $event->sheet->getCell("G{$row}")->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input error');
            $validation->setError('Value is not in list.');
            $validation->setPromptTitle('Pick from list');
            $validation->setPrompt('Please pick a value from the drop-down list.');
            $validation->setFormula1('"' . $typeValues . '"');
        }

        for ($row = 2; $row <= 100; $row++) {
            $validation = $event->sheet->getCell("J{$row}")->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input error');
            $validation->setError('Value is not in list.');
            $validation->setPromptTitle('Pick from list');
            $validation->setPrompt('Please pick a value from the drop-down list.');
            $validation->setFormula1('"' . $publishedValues . '"');
        }

        for ($row = 2; $row <= 100; $row++) {
            $validation = $event->sheet->getCell("M{$row}")->getDataValidation();
            $validation->setType(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::TYPE_LIST);
            $validation->setErrorStyle(\PhpOffice\PhpSpreadsheet\Cell\DataValidation::STYLE_INFORMATION);
            $validation->setAllowBlank(false);
            $validation->setShowInputMessage(true);
            $validation->setShowErrorMessage(true);
            $validation->setShowDropDown(true);
            $validation->setErrorTitle('Input error');
            $validation->setError('Value is not in list.');
            $validation->setPromptTitle('Pick from list');
            $validation->setPrompt('Please pick a value from the drop-down list.');
            $validation->setFormula1('"' . $unitsValues . '"');
        }


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
        $columnsToUnlock = ['A', 'B', 'C', 'D', 'E','F', 'G', 'H', 'I','J', 'K', 'L', 'M', 'N', 'O', 'P', 'Q', 'R', 'S'];
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
