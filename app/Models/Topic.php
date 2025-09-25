<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class Topic extends Model
{
    use HasFactory;

    /**
     * The attributes that are mass assignable.
     *
     * @var array<int, string>
     */
    protected $fillable = [
        'subject_id',
        'name',
        'objective',
        'tasks',
        'description',
        'type',
        'created_at',
        'updated_at',
    ];
    public function test()
    {
        return $this->hasOne(Test::class);
    }
}
