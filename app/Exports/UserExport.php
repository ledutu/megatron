<?php

namespace App\Exports;

use Maatwebsite\Excel\Concerns\FromCollection;
use Maatwebsite\Excel\Concerns\Exportable;
use Maatwebsite\Excel\Concerns\WithHeadings;
use App\Model\User;
use DB;
class UserExport implements FromCollection, WithHeadings
{
  
    /**
    * @return \Illuminate\Support\Collection
    */
    public $temp = '';
    use Exportable;
    public function __construct($query = null){
     
        $this->temp = $query->toArray();
     
    }
    public function collection()
    {
        $level = array(1 => 'Admin', 0 => 'Member', 2 => 'Finance', 3 => 'Support', 4 => 'Customer', 5 => 'BOT', 10=>'Admin View');
        $user = $this->temp;
        $result = [];
        $currency = DB::table('currency')->pluck('Currency_Symbol', 'Currency_ID')->toArray();
        // dd($currency);
        foreach ($user as $row) {
            if ($row['google2fa_User']) {
                $row['google2fa_User'] = "Enable";
            } else {
                $row['google2fa_User'] = "Disable";
            }
            $kyc = 'Unverify';
            if(isset($row['Profile_Status'])){
                if($row['Profile_Status'] == 1){
                    $kyc = 'Verified';
                }else{
                    $kyc = 'Waiting';
                }
            }
            $listAddress = [];
            foreach($row['address_deposit'] as $address){
                 $listAddress[$currency[$address['Address_Currency']]] = $address['Address_Address'];
            }
            // dd($listAddress);
            $result[] = array(
                '0' => $row['User_ID'],
                '1' => $row['User_Email'],
                '2' => $row['User_RegisteredDatetime'],
                '3' => $row['User_Parent'],
                '4' => $row['User_Tree'],
                // '5' => $row['User_SunTree'],
                '5' => $level[$row['User_Level']],
                '6' => ($row['User_EmailActive'] ? 'Active' : 'None'),
                '7' => $row['google2fa_User'],
                '8' => $kyc,
                '9' => isset($listAddress['USDT']) ? $listAddress['USDT'] : '',
          
            );
        }
        return (collect($result));

    }
    public function headings(): array
    {
        
        return [
            'ID', 'Email', 'Registred DateTime', 'ID Parent', 'Binary Tree', 'Level', 'Status', 'Auth', 'KYC', 'Deposit USDT', 'Deposit ETH', 'Deposit BTC'
        ];
        
    }
}
