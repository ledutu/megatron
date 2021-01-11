<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Model\Money;
use Maatwebsite\Excel\Facades\Excel;

class GiftCodeExport implements FromCollection, WithHeadings
{
    public $temp = '';
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    public function __construct($query = null){
        $this->temp = $query->toArray();
        
    }

    public function collection()
    {
        //Affiliate Commission
        //$percentArr = [1=>0.01, 2=>0.02, 3=>0.03];
        $level = array(1 => 'Admin', 0 => 'Member', 2 => 'Finance', 3 => 'Support', 4 => 'Customer', 5 => 'Bot');
        $giftcode = $this->temp;
        $result = [];
        foreach ($giftcode as $row) {
            $result[] = array(
                '0' => $row->GiftCode_ID,
                '1' => $row->GiftCode_User,
                '2' => $row->GiftCode_User_Use ?? '',
                '3' => $row->GiftCode_Code,
                '4' => $row->Package_Name,
                '5' => $row->Package_Amount,
                '6' => date('Y-m-d', $row->GiftCode_Time),
                '7' => date('Y-m-d', $row->GiftCode_Expiration_Time),
            );
        }
        return (collect($result));
    }
    public function headings(): array
    {
        return [
            'ID',
            'User created',
            'User used',
            'Code',
            'Name package',
            'Amount',
            'Created date',
            'Expiration date',
        ];
        
    }

}
