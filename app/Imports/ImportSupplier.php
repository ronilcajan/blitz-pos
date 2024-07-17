<?php

namespace App\Imports;

use App\Models\Supplier;
use Maatwebsite\Excel\Concerns\ToModel;
use Maatwebsite\Excel\Concerns\WithHeadingRow;

class ImportSupplier implements ToModel, WithHeadingRow
{
    /**
    * @param array $row
    *
    * @return \Illuminate\Database\Eloquent\Model|null
    */
    public function model(array $row)
    {

        $supplierAttributes = [
            'name' => $row['name'],
            'contact_person' => $row['contact_person'],
            'email' => $row['email'],
            'phone' => $row['phone'],
            'store_id' => auth()->user()->store_id,
            'address' => $row['address'],
        ];

        if($row['name'] != null){
            Supplier::create($supplierAttributes);
        }
      

    }

    public function batchSize(): int{
        return 1000;
    }
}
