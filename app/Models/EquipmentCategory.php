<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EquipmentCategory extends Model
{
    use HasFactory;

    protected $fillable = ['code', 'name', 'description'];

    public function equipments()
    {
        return $this->hasMany(Equipment::class, 'category', 'code');
    }
}