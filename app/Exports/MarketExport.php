<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Model\Money;
use Maatwebsite\Excel\Facades\Excel;
class MarketExport implements FromCollection, WithHeadings
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
                '0' => $row['_id'],
                '1' => $row['UserSell'] ?? "",
                '2' => $row['UserBuy'] ?? "",
                '3' => $row['UserBalanceSell'] ?? "",
                '4' => $row['UserBalanceBuy'] ?? "",
                '5' => $row['PriceEUSD'] ?? "",
                '6' => $row['PriceGold'] ?? "",
                '7' => $row['Type'] ?? "",
                '8' => $row['Item'] ?? "",
                '9' => $row['Sold'][0]->id ?? "", 
                '10' => $row['Sold'][0]->user ?? "", 
                '11' => date('d-m-Y H:i:s', $row['Sold'][0]->time ?? 0),
                '12' => $row['Cancel'] ?? "",
                '13' => ($row['Status'] == 1) ? 'Sold' : (($row['Status'] == 2) ? 'Cancelled' : 'On sale'),
                '14' => date('d-m-Y H:i:s', $row['DateTime']),
            );
        }
        return (collect($result));
    }
    public function headings(): array
    {
        
        return [
            'ID',
            'User Sell',
            'User Buy',
            'User Balance Sell',
            'User Balance Buy',
            'Price EUSD',
            'Price Gold',
            'Type',
            'Item',
            'Sold Id',
            "Sold User",
            "Sold Time",
            'Cancel',
            'Status',
            'Time',
            'Password',
        ];
        
    }

    public function styles(Worksheet $sheet)
    {
        $sheet->mergeCells('J1:K1:L1');

        // foreach (range(1, 7) as $number) {
        //     $sheet->getStyle('C' . $number)->getAlignment()->applyFromArray(
        //         array('horizontal' => 'left')
        //     );
        // }
    }

}
