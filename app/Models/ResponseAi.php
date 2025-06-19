<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ResponseAi extends Model
{
    use HasFactory;

    protected $table = 'response_ai_table'; // Siguraduhin na tama ang table name
    protected $fillable = ['chat_id', 'question', 'ai_answer', 'model_used'];

}