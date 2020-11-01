<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Check extends Model
{
    protected $fillable = ['name','user_id', 'status', 'is_archive', 'is_visible'];

    protected $appends = ['is_finished', 'is_paid'];

    public function user() {
        return $this->belongsTo(User::class);
    }

    public function items() {
        return $this->hasMany(CheckItem::class);
    }

    public function getIsFinishedAttribute() {
        return $this->status === 'completed' || $this->status === 'rejected';
    }

    public function getIsPaidAttribute() {
        return  $this->status === 'completed';
    }
}
