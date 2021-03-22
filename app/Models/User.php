<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class User extends Model
{
    use HasFactory;

    protected $table = 'user';

    protected $fillable = [
        'name',
        'email',
        'phone_number',
        'date_of_birth',
        'is_active',
    ];

    public static $required = [
        'name',
        'email',
        'phone_number',
        'date_of_birth',
        'is_active',
    ];

    public static $per_page = 5;

    public $timestamps = true;

    public static function rules($method) {
        return [
            'name' => ($method == 'POST') ? 'required' : 'filled',
            'email' => (($method == 'POST') ? 'required' : 'filled') . '|email',
            'phone_number' => ($method == 'POST') ? 'required' : 'filled',
            'date_of_birth' => (($method == 'POST') ? 'required' : 'filled') . '|date',
            'is_active' => (($method == 'POST') ? 'required' : 'filled') . '|boolean',
        ];
    }
}
