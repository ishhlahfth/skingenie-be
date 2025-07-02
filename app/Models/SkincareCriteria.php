<?php

namespace App\Models;

use App\Traits\HasUUID;
use Illuminate\Database\Eloquent\Model;

class SkincareCriteria extends Model
{
    use HasUUID;

    protected $table = 'skincare_criteria';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'skincare_id',
        'criteria_id',
    ];
    public function criteria_detail() {
        return $this->hasMany(Criteria::class, 'id', 'criteria_id');
    }
}
