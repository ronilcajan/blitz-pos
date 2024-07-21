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
            'name', 'barcode', 'size', 'color', 'dimension', 'unit', 'usage_type', 'brand', 'visible',
            'expiration_date', 'description', 'category', 'sku', 'min_quantity', 'in_store',
            'in_warehouse', 'base_price', 'markup_price', 'discount_rate', 'discount_type', 'tax_rate',
            'tax_type', 'sale_price'
        ];
    }

    public static function afterSheet(AfterSheet $event)
    {
        $rows = 500;

        $categories = ProductCategory::pluck('name')->toArray();
        $units = ProductUnit::pluck('name')->toArray();

        $categoriesValues = !empty($categories) ? implode(',', $categories) : null;
        $unitsValues = !empty($units) ? implode(',', $units) : null;
        $typeValues = 'sellable,internal_use';
        $publishedValues = 'published,hide';
        $discountType = 'none,flat,percentage';
        $taxType = 'none,flat,percentage';

        // Function to apply data validation
        $applyValidation = function($cell, $formula) use ($event) {
            if ($formula) {
                $validation = $event->sheet->getCell($cell)->getDataValidation();
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
                $validation->setFormula1($formula);
            }
        };

        // Apply validations
        for ($row = 2; $row <= $rows; $row++) {
            $applyValidation("F{$row}", $unitsValues ? '"' . $unitsValues . '"' : null);
            $applyValidation("G{$row}", '"' . $typeValues . '"');
            $applyValidation("I{$row}", '"' . $publishedValues . '"');
            $applyValidation("L{$row}", $categoriesValues ? '"' . $categoriesValues . '"' : null);
            $applyValidation("T{$row}", '"' . $discountType . '"');
            $applyValidation("V{$row}", '"' . $taxType . '"');

            $basePriceCell = "Q{$row}";
            $markupPriceCell = "R{$row}";
            $discountRateCell = "S{$row}";
            $discountTypeCell = "T{$row}";
            $taxRateCell = "U{$row}";
            $taxTypeCell = "V{$row}";

            $salePriceFormula = "=IF($discountTypeCell=\"flat\", $basePriceCell + $markupPriceCell - $discountRateCell, IF($discountTypeCell=\"percentage\", $basePriceCell + $markupPriceCell - ($basePriceCell + $markupPriceCell) * $discountRateCell / 100, $basePriceCell + $markupPriceCell))";
            $salePriceFormula .= "+IF($taxTypeCell=\"flat\", $taxRateCell, IF($taxTypeCell=\"percentage\", ($basePriceCell + $markupPriceCell) * $taxRateCell / 100, 0))";

            $event->sheet->setCellValue("W{$row}", $salePriceFormula);
        }

        // Protect the sheet
        $event->sheet->getProtection()->setSheet(true);
        $event->sheet->getProtection()->setPassword('your_password');

        // Make specific columns writable
        $columnsToUnlock = range('A', 'W');
        foreach ($columnsToUnlock as $column) {
            for ($row = 2; $row <= $rows; $row++) {
                $event->sheet->getStyle("{$column}{$row}")->getProtection()->setLocked(\PhpOffice\PhpSpreadsheet\Style\Protection::PROTECTION_UNPROTECTED);
            }
        }

        // Define the border style
        $styleArray = [
            'borders' => [
                'right' => [
                    'borderStyle' => \PhpOffice\PhpSpreadsheet\Style\Border::BORDER_THICK,
                    'color' => ['argb' => 'FF0000FF'], // Blue color
                ],
            ],
        ];

        // Apply the border style to the range A2:W100
        $event->sheet->getStyle('W1:W100')->applyFromArray($styleArray);

        // Define the background fill style for cells outside column W
        $fillStyleArray = [
            'fill' => [
                'fillType' => \PhpOffice\PhpSpreadsheet\Style\Fill::FILL_SOLID,
                'startColor' => [
                    'argb' => 'FFEEEEEE', // Muted color (light gray)
                ],
            ],
        ];

        // Apply the background fill style to cells outside column W
        $event->sheet->getStyle('X1:AZ100')->applyFromArray($fillStyleArray); // Adjust the range as needed
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

