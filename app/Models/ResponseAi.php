<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponseAi extends Model
{
    use HasFactory;

    protected $table = 'response_ai_table'; // Siguraduhin na tama ang table name
    protected $fillable = ['question', 'ai_answer']; // Ilagay ang mga column na pwedeng i-mass assign
}