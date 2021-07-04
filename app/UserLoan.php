<?php

namespace App;
use Illuminate\Database\Eloquent\Model;

class UserLoan extends Model
{

    protected $table = 'users_loan';

    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */
    protected $fillable = [
        'user_id', 'loan_amount', 'loan_term', 'approved', 'closed'
    ];

    /**
     * The attributes that should be hidden for arrays.
     *
     * @var array
     */
    protected $hidden = [];

    public function loanLimit($a=null) {
        return UserLoan::all();
    }

    public function add($data) {
        return self::create($data)->id;
    }


    public function User()
    {
        return $this->belongsTo('App\User');
    }


}
