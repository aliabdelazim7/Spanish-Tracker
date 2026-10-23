<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Unit extends Model
{
    protected $fillable = [
        'cefr_level_id',
        'title',
        'title_ar',
        'title_en',
        'description_ar',
        'description_en',
        'icon',
        'skill_category',
        'order_index',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
        'order_index' => 'integer',
    ];

    public function level()
    {
        return $this->belongsTo(CefrLevel::class, 'cefr_level_id');
    }

    public function lessons()
    {
        return $this->hasMany(Lesson::class)->orderBy('order_index');
    }
}
