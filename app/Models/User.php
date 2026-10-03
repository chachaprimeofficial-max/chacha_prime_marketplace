<?php
namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;

class User extends Authenticatable
{
    use Notifiable;

    protected $table='users';
    protected $fillable=['name','email','password','phone','status'];
    protected $hidden=['password','remember_token','two_factor_secret','two_factor_recovery_codes'];
    protected $casts=['email_verified_at'=>'datetime','two_factor_enabled'=>'boolean','password'=>'hashed'];
}