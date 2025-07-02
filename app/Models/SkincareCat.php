<?php

namespace App\Models;

use App\Traits\HasUUID;
use Illuminate\Database\Eloquent\Model;

class SkincareCat extends Model
{
    use HasUUID;

    protected $table = 'skincare_category';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'skincare_id',
        'category_id',
    ];

    public function ref_skincare() {
        return $this->hasOne(Skincare::class, 'id', 'skincare_id');
    }
}
