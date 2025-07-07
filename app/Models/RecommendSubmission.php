<?php

namespace App\Models;

use App\Traits\HasUUID;
use Illuminate\Database\Eloquent\Model;

class RecommendSubmission extends Model
{
    use HasUUID;

    protected $table = 'recommend_submission';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'category_uuid',
        'price_start',
        'price_end',
        'user_uuid',
        'rec_skincare_uuid',
    ];

    public function ref_skincare()
    {
        return $this->hasOne(Skincare::class, 'id', 'rec_skincare_uuid');
    }

    public function ref_category()
    {
        return $this->hasOne(Categories::class, 'id', 'category_uuid');
    }
}
