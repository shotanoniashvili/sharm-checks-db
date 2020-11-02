<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class CheckItem extends Model
{
    private $sadzaglishvilisID = 3;

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

    protected $appends = ['is_finished', 'can_finish', 'is_approved'];

    public function statuses() {
        return $this->hasMany(CheckItemStatus::class);
    }

    public function finishedBy() {
        return $this->belongsTo(User::class, 'finished_by');
    }

    public function getIsFinishedAttribute() {
        return $this->finished_by !== null;
    }

    public function getIsApprovedAttribute() {
        return $this->statuses()->where('is_accepted', false)->count() === 0 || $this->statuses()->where(['user_id' => $this->sadzaglishvilisID, 'is_accepted' => true])->count() > 0;
    }

    public function getCanFinishAttribute() {
        return $this->is_approved;
    }
}
