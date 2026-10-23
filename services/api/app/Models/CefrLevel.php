<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class CefrLevel extends Model
{
    protected $fillable = [
        'course_id',
        'code',
        'name',
        'name_ar',
        'description_ar',
        'order_index',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'order_index' => 'integer',
    ];

    public function course()
    {
        return $this->belongsTo(Course::class);
    }

    public function units()
    {
        return $this->hasMany(Unit::class)->orderBy('order_index');
    }
}
