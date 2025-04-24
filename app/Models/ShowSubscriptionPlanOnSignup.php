<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class ShowSubscriptionPlanOnSignup extends Model
{
    use HasFactory;
    protected $fillable = ['show_plan_customer','every_sign_up','every_app_open'];
}
