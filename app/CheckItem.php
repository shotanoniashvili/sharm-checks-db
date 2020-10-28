<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CheckItem extends Model
{
    protected $fillable = [
        'check_id',
        'op_name',
        'gel',
        'eur',
        'rur',
        'usd',
        'source',
        'destination',
        'brand',
        'doc_type',
        'comment'
    ];

    protected $appends = ['is_finished'];

    public function statuses() {
        return $this->hasMany(CheckItemStatus::class);
    }

    public function finishedBy() {
        return $this->belongsTo(User::class, 'finished_by');
    }

    public function getIsFinishedAttribute() {
        return $this->finished_by !== null;
    }
}
