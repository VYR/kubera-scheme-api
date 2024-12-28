<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;
use Illuminate\Database\Eloquent\SoftDeletes;


class Setting extends Model
{
    use HasFactory;
    use SoftDeletes;


    protected $fillable = [
        'setting_details',
        'setting_name',
    ];

    protected function casts(): array
    {
        return [
            'setting_details' => 'array',
        ];
    }
}