<?php

namespace App\Model;

use Illuminate\Database\Eloquent\Model;

class Profile extends Model
{
    protected $table = 'profile';
    protected $fillable = ['Profile_User', 'Profile_Passport_ID', 'Profile_Full_Name', 'Profile_Passport_Image', 'Profile_Passport_Image_Selfie', 'Profile_Time', 'Profile_Status'];
    public $timestamps = false;

    protected $primaryKey = 'Profile_ID';
}
