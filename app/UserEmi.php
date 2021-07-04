<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class UserEmi extends Model
{

    protected $table = 'users_emi';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'loan_id', 'emi_amount', 'week', 'payment_status'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function add($data) {
        return self::create($data);
    }

    public function User()
    {
        return $this->belongsTo('App\User');
    }

}
