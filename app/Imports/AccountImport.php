<?php
namespace App\Imports;

use App\Models\Account;
use App\Models\User;
use Maatwebsite\Excel\Row;
use Maatwebsite\Excel\Concerns\OnEachRow;
use Maatwebsite\Excel\Concerns\WithStartRow;

class AccountImport implements OnEachRow, WithStartRow
{

    public function onRow(Row $row)
    {
        $rowIndex = $row->getIndex();
        $row      = $row->toArray();
        $account = Account::where('code', $row[1])->first();
        if($account == null){
            $account = new Account;
            $account->code = $row[1];
        }
        $account->name = $row[2];
        $account->level = $row[3];
        $account->type = $row[4];
        $account->finance_statement = $row[5];
        $account->save();
    }

    public function startRow(): int
    {
        return 3;
    }
}
