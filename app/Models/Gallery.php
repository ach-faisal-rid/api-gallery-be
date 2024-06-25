<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Gallery extends Model
{
    use HasFactory;

    protected $table = 'galleries';

    protected $fillable = [
        'name',
        'file_name',
        'file_type',
        'file_size'
    ];

    protected $guarded = [
        'id'
    ];

    // relasi ke user
    public function user() {
        return $this->belongsTo(User::class, 'users_id', 'id');
    }

}