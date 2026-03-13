<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Habit extends Model
{
    protected $fillable = [
        'user_id',
        'title',
        'description',
        'frequency',
        'target_days',
        'color',
        'is_active',
    ];
    public function uer(){
        return $this->belongsTo(User::class);
    }

    public function logs(){
        return $this->hasMany(HabitLog::class);
    }
}
