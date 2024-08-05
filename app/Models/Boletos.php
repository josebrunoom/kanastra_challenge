<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Boletos extends Model
{
    /**
     * The attributes that are mass assignable.
     *
     * @var array
     */

     use HasFactory;

    protected $table = 'boletos';

    protected $fillable = [
        'name',
        'governmentId',
        'email',
        'status',
        'debtAmount',
        'debtDueDate',
        'debtId'
    ];

}