<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Skincare extends Model
{

    protected $table = 'skincare';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'skincare_name',
        'rating',
        'img_url',
        'sociolla_url',
        'description',
        'ingredients',
        'how_to_use',
        'min_price',
        'max_price',
        'slug',
        'total_reviews',
        'total_recommend',
    ];

    public function ref_criteria() {
        return $this->hasMany(SkincareCriteria::class, 'skincare_id', 'id');
    }

    public function ref_category() {
        return $this->hasMany(SkincareCat::class, 'skincare_id', 'id');
    }

    public function scopeBycat($query, $category_id = null) {
        $query->whereHas('ref_category', function ($q) use ($category_id) {
            if($category_id !== null) {
                $q->where('category_id', $category_id);
            }
            return $q;
        });
        $query->whereHas('ref_criteria');
        return $query;
    }
}
