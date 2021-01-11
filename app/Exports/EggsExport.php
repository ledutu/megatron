<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Model\Money;
use Maatwebsite\Excel\Facades\Excel;
class EggsExport implements FromCollection, WithHeadings
{
    public $temp = '';
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    public function __construct($query = null){
        $this->temp = $query->toArray();
        
    }

    public function checkWaitingActive($status) {
        if($status){
            return $status == 1? 'Waiting Active': 'Active';
        } else {
            return 'Active';
        }
    }

    public function collection()
    {
        //Affiliate Commission
        
        //$percentArr = [1=>0.01, 2=>0.02, 3=>0.03];

        $level = array(1 => 'Admin', 0 => 'Member', 2 => 'Finance', 3 => 'Support', 4 => 'Customer', 5 => 'Bot');
        $eggs = $this->temp;
        // dd($money);
        $result = [];
        foreach ($eggs as $row) {
            
            $result[] = array(
                '0' => $row['ID'],
                '1' => $row['Owner'],
                '2' => $row['Pool'],
                '3' => $row['BuyFrom'] ?? "",
                '4' => date('d-m-Y H:i:s', $row['BuyDate']),
                '5' => gmdate("H:i:s", $row['HatchesTime']),
                '6' => $row['ActiveTime']? date('d-m-Y H:i:s', $row['ActiveTime']): 0,
                '7' => $row['Percent'] ?? "0",
                '8' => $row['F'] ?? "0",
                '9' => $this->checkWaitingActive($row['WaitingActive'] ?? 0),
                '10' => $row['Status'] == 2 ? 'Hatches' : ($row['Status'] == 1 ? ($row['ActiveTime'] ? 'Activated' : 'Waiting') : "Canceled"),
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
            'Buy From',
            'Buy Date',
            'Hatches Time',
            'Active Time',
            'Percent',
            'F',
            'Waiting Active',
            'Status',
        ];
        
    }

}
