<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Activity extends Model
{
    use HasFactory;
    protected $fillable = [
        'subject',
        'class',
        'group',
        'filePaths',
        'dateRemise',
        'intitule',
        'type',
        'matiere',
        'user_id',
        'description'
    ];
    public function students()
    {
        return $this->belongsToMany(User::class, 'soummision', 'activity_id', 'student_id')
            ->withPivot('status', 'lecture');
    }
    public function prof()
    {
        return $this->belongsTo(User::class, 'user_id');
    }
}
