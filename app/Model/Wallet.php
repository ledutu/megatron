<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Wallet extends Model
{
    protected $table = "address";

    protected $fillable = ['Address_ID','Address_Currency', 'Address_Address', 'Address_User', 'Address_CreateAt', 'Address_UpdateAt', 'Address_IsUse', 'Address_Comment'];

    public $timestamps = true;

    const CREATED_AT = 'Address_CreateAt';
    const UPDATED_AT = 'Address_UpdateAt';

	 
	static function checkWallet($user, $curency){

        $address = Wallet::where('Address_User', $user)->where('Address_IsUse', 1)->where('Address_Currency', $curency)->select('Address_Address', 'Address_Currency')->first();
        return $address;
    }
}
