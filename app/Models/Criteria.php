<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Criteria extends Model
{

    protected $table = 'criteria';
    protected $primaryKey = 'id';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'id',
        'criteria_name',
        'description',
        'type',
        'criteria_key',
        'criteria_value',
    ];
}
