<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Company extends Model
{
    protected $fillable = [
        'company_name',
        'street_address',
        'representative_name',
    ];

    // Companyモデルがitemsテーブルとリレーション関係を結ぶためのメソッドです
    public function items()
    {
        return $this->hasMany(Item::class);
    }
}
