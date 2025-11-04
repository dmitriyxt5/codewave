<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Command extends Model
{
    protected $fillable = ['items', 'subject_id', 'leader_id', 'member_ids', 'link', 'balls'];

    
    protected $casts = [
        'member_ids' => 'array',
        'items' => 'array'
    ];

    public function topic()
    {
        return $this->belongsTo(Topic::class);
    }

    public function leader()
    {
        return $this->belongsTo(User::class, 'leader_id');
    }

    
    public function members()
    {
        $memberIds = is_string($this->member_ids) ? json_decode($this->member_ids, true) : $this->member_ids;
        return User::whereIn('id', $memberIds ?? [])->get();
    }
}
