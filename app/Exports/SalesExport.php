<?php

namespace App\Exports;

use App\Models\Sale;
use Carbon\Carbon;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithColumnWidths;
use Maatwebsite\Excel\Concerns\WithHeadings;

class SalesExport implements FromCollection, WithHeadings, WithColumnWidths
{
    private $from_date;
    private $to_date;

    public function __construct(array $date)
    {
        $this->from_date = $date['from_date'];
        $this->to_date = $date['to_date'];
    }

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        $from_date = $this->from_date;
        $to_date = $this->to_date ? Carbon::parse($this->to_date)->endOfDay() : null;

        $query = Sale::with(['customer', 'user']);

        // Apply date filtering only if both dates are provided
        if ($from_date && $to_date) {
            $query->whereBetween('created_at', [$from_date, $to_date]);

        }

        return $query->get()->map(function ($sale) {
            return [
                'transaction' => $sale->tx_no,
                'payment_method' => $sale->payment_method,
                'items' => $sale->quantity,
                'discount' => $sale->discount,
                'amount' =>  number_format($sale->total,2),
                'status' => $sale->status->getLabelText(),
                'customer' => $sale->customer?->name,
                'cashier' => $sale->user?->name,
                'created_at' => $sale->created_at->format('M d, Y h:i A'),
            ];
        });
    }


    public function headings(): array
    {
        return [
            'transaction',
            'payment_method',
            'items',
            'discount',
            'amount',
            'status',
            'customer',
            'cashier',
            'created_at',
        ];
    }

    public function columnWidths(): array
    {
        $data = $this->collection();
        $headings = $this->headings();
        $columnWidths = [];

        foreach ($headings as $key => $heading) {
            $maxLength = strlen($heading); // Start with the heading length

            // Iterate through the data to find the maximum length in each column
            foreach ($data as $item) {
                $value = $item[$heading] ?? '';
                $maxLength = max($maxLength, strlen($value));
            }

            // Adjust the width based on the maximum length found
            $columnWidths[chr(65 + $key)] = $maxLength + 5; // Adding some extra padding
        }

        return $columnWidths;
    }
}
