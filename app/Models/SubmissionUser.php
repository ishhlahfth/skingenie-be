<?php

namespace App\Models;

use App\Traits\HasUUID;
use Illuminate\Database\Eloquent\Model;

class SubmissionUser extends Model
{
    use HasUUID;
    protected $table = 'user';
    protected $primaryKey = 'uuid';
    public $incrementing = false;
    protected $keyType = 'string';

    protected $fillable = [
        'uuid',
        'full_name',
        'email',
        'phone',
    ];
}
