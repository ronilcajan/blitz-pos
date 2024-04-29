<?php

namespace App\Exports;

use App\Models\User;
use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\WithHeadings;

class UsersExport implements FromCollection, WithHeadings
{
    private $users;

    /**
    * @return \Illuminate\Support\Collection
    */
    public function collection()
    {
        return  $this->users = User::query()
                ->with('roles')
                ->orderBy('id', 'DESC')
                ->where('id', '!=', 1)
                ->get()
                ->map(function ($user) {
                    return [
                        'id' => $user->id,
                        'name' => $user->name,
                        'email' => $user->email,
                        'phone' => $user->phone,
                        'address' => $user->address,
                        'role' => $user->roles[0]?->name,
                        'status' => $user->status->getLabelText(),
                        'created_at' => $user->created_at->format('M d, Y h:i: A'),
                    ];
                });

    }

    public function headings(): array
    {
        return [
            'id',
            'name',
            'email',
            'phone',
            'address',
            'role',
            'status',
            'created_at',
        ];
    }
}
