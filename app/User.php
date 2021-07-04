<?php

namespace App;

use Illuminate\Notifications\Notifiable;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Passport\HasApiTokens;
use Illuminate\Database\Eloquent\Model;

class User extends Authenticatable
{
    use Notifiable, HasApiTokens;

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'customer_id', 'name', 'phone', 'password'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [
        'password', 'remember_token',
    ];

    public function createPassportToken($user){
        return $user->createToken('ASPIRE-LOAN-APP')->accessToken;
    }

    public function login(){
        return Auth::user();
    }

    public function add($data) {
        return self::create($data);
    }

    public function accountLimit($phone) {
       
        return User::find(1)->where('phone', $phone)->count();
    }

    public function Loan()
    {
        return $this->hasMany('App\UserLoan');
    }

    public function Emi()
    {
        return $this->hasMany('App\UserEmi');
    }

}
