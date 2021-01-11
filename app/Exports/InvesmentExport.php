<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Model\Money;
use Maatwebsite\Excel\Facades\Excel;
class InvesmentExport implements FromCollection, WithHeadings
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


        $level = array(1 => 'Admin', 0 => 'Member', 2 => 'Finance', 3 => 'Support', 4 => 'Customer', 5 => 'Bot');
        $money = $this->temp;
        
        $result = [];
        foreach ($money as $row) {
            if ($row['investment_Status'] == 1) {
                
                $row['investment_Status'] = "Success";
            } else {
                $row['investment_Status'] = "Cancel";
            }
            
            $result[] = array(
                '0' => $row['investment_ID'],
                '1' => $row['investment_User'],
                '2' => $row['investment_Amount'],
                '3' => $row['Currency_Symbol'],
                '4' => $row['investment_Rate'],
                '5' => Date('Y-m-d H:i:s', $row['investment_Time']),
                '6' => $row['investment_Status'],
            );
        }
        return (collect($result));
    }
    public function headings(): array
    {
        
        return [
            'investment_ID',
            'investment_User',
            'investment_Amount',
            'investment_Currency',
            'investment_Rate',
            'investment_Time',
            'investment_Status',
        ];
        
    }

}
