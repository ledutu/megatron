<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Model\Money;
use Maatwebsite\Excel\Facades\Excel;

class EggTransferExport implements FromCollection, WithHeadings
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
        $egg = $this->temp;
        $result = [];
        foreach ($egg as $row) {
            $result[] = array(
                '0' => $row['Log_ID'],
                '1' => $row['Log_User'],
                '2' => substr($row['Log_Comment'], -6),
                '3' => $row['Log_Comment'],
                '4' => $row['Log_CreatedAt'],
                '5' => $row['Log_Status'] == 1 ? 'Success' : 'Cancel',
            );
        }
        return (collect($result));
    }
    public function headings(): array
    {
        return [
            '#',
            'User Transfer',
            'User Give',
            'Comment',
            'Date',
            'Status',
        ];
        
    }

}
