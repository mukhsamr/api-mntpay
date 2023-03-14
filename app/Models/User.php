<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory;

    protected $hidden = ['remember_token', 'pin'];
    protected $guarded = ['id'];
    public $timestamps = false;

    public function buyerTopups()
    {
        return $this->hasMany(Topup::class, 'penerima');
    }

    public function buyerTransactions()
    {
        return $this->hasMany(Transaction::class, 'pengirim');
    }

    public function sellerTransactions()
    {
        return $this->hasMany(Transaction::class, 'penerima');
    }

    public function withdraws()
    {
        return $this->hasMany(Withdraw::class, 'penerima');
    }
}
