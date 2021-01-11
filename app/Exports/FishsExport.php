<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Model\Money;
use Maatwebsite\Excel\Facades\Excel;
class FishsExport implements FromCollection, WithHeadings
{
    public $temp = '';
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    public function __construct($query = null){
        $this->temp = $query->toArray();
        
    }

    public function getStatus($status){
        if($status == 1) return 'Current';
        else if($status == -1) return 'Die because of hungry';
        else if($status == 2) return 'Die because of over life cycle';
    }

    public function collection()
    {
        //Affiliate Commission
        
        //$percentArr = [1=>0.01, 2=>0.02, 3=>0.03];

        $level = array(1 => 'Admin', 0 => 'Member', 2 => 'Finance', 3 => 'Support', 4 => 'Customer', 5 => 'Bot');
        $fishs = $this->temp;
        // dd($money);
        $result = [];
        foreach ($fishs as $row) {
            
            $result[] = array(
                '0' => $row['ID'],
                '1' => $row['Owner'],
                '2' => $row['Pool'],
                '3' => $row['From'],
                '4' => date('d-m-Y H:i:s', $row['Born']),
                '5' => gmdate("H:i:s", $row['GrowTime']),
                '6' => $row['ActiveTime']? date('d-m-Y H:i:s', $row['ActiveTime']): 0,
                // '7' => $row->fishTypes->Name,
                '8' => $row['F'],
                '9' => $this->getStatus($row['Status']),
            );
        }
        return (collect($result));
    }
    public function headings(): array
    {
        
        return [
            'ID',
            'Owner',
            'Pool',
            'From',
            'Born',
            'Grow Time',
            'Active Time',
            'F',
            'Status',
        ];
        
    }

}
