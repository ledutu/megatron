<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Model\Money;
use Maatwebsite\Excel\Facades\Excel;
class WalletTempExport implements FromCollection, WithHeadings
{
    public $temp = '';
    /**
    * @return \Illuminate\Support\Collection
    */
    use Exportable;
    public function __construct($query = null){

        $this->temp = $query;
        
    }
    public function collection()
    {
        
        $level = array(1 => 'Admin', 0 => 'Member', 2 => 'Finance', 3 => 'Support', 4 => 'Customer', 5 => 'Bot');
        $argSymbol = [
            1 => 'BTC',
            2 => 'ETH',
            8 => 'BST',
            3 => 'BANK',
            5 => 'USD',
        ]; 
        $money = json_decode(json_encode($this->temp), true);
        $result = [];
        foreach ($money as $row) {
            if ($row['Money_Temp_Status'] == 1) {
            	$row['Money_Temp_Status'] = 'Success';
            } else {
            	$row['Money_Temp_Status'] = 'Waiting';
            }

           
            // '6' => $row['Money_Currency'] == 8 ? $row['Money_USDT'] : ($row['Money_Currency'] == 3 ? $row['Money_USDT'] * $row['Money_Rate'] : $row['Money_USDT']/$row['Money_Rate']),
            $result[] = array(
                '0' => $row['Money_Temp_ID'],
                '1' => $row['Money_Temp_User'],
                '2' => $level[$row['User_Level']],
                '3' => 'Interest Commission',
                '4' => $row['Money_Temp_Comment'],
                '5' => $row['Money_Temp_Time'],
                '6' => $row['Money_Temp_Amount'],
                '7' => $argSymbol[$row['Money_Temp_Currency']],
                '8' => $row['Money_Temp_Rate'],
                '9' => $row['Money_Temp_Amount'] * $row['Money_Temp_Rate'],
                '10' => 0,
                '11' => 0,
                '12' => $row['Money_Temp_Status']

            );
        }
        return (collect($result));
    }
    public function headings(): array
    {
        
        return [
            'ID',
            'User ID',
            'User Level',
            'Action',
            'Comment',
            'DateTime',
            'Amount Coin',
            'Currency',
            'Rate',
            'USD',
            'Fee Coin',
            'Fee USD',
            'Status'
        ];
        
    }}
