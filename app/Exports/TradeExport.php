<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Model\Money;
use Maatwebsite\Excel\Facades\Excel;
class TradeExport implements FromCollection, WithHeadings
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
        $tradelist = $this->temp;
  
        $result = [];
        foreach ($tradelist as $row) {
            
          
          	if($row['GameBet_Status']==1){
            	 $row['GameBet_Status'] = 'Win';
            }else if($row['GameBet_Status']==2){
            	 $row['GameBet_Status'] = 'Lose';
            }else{
            	 $row['GameBet_Status'] = 'Draw';
            }
          	
          
          
            $result[] = array(
                '0' => 	$row['GameBet_SubAccountUser'],
          		  '1'=> 	'User Level '.$level[$row['GameBet_SubAccountLevel']],
              	'2'=> 	$row['GameBet_Type'],
         		    '3'=>	$row['GameBet_Symbol'],
              	'4'=> 	$row['GameBet_Currency']==5?'USDT':'',
              	'5'=>   $row['GameBet_Amount'],
              	'6'=> 	$row['GameBet_AmountWin'],
              	'7'=> 	$row['GameBet_SubAccountEndBalance'],
              	'8'=>	$row['GameBet_Status'] ,
              	'9'=>	 date('Y-m-d H:i:s', $row['GameBet_datetime']),
            );
        }
        return (collect($result));
    }
    public function headings(): array
    {
        
        return [
            'User ID',
            'User Level',
            'Action',
            'Symbol',
          
            'Currency',
             'Amount ',
            'Profit',
            'Balance',
            'Status',
           	'DateTime',
          
        ];
        
    }

}
