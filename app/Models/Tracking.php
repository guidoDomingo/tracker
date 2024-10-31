<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Tracking extends Model
{
    use HasFactory;

    protected $fillable = ['device_id', 'latitude', 'longitude', 'last_tracked_at'];

    // Desactiva las timestamps automáticas si usas `last_tracked_at`
    public $timestamps = false;
}
