<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class LawyerDocument extends Model
{
    use HasFactory;
    public $timestamps = true;
    protected $fillable =['file_path','lawyer_id','order_id','status', 'reject_reason','file_name'];
}
