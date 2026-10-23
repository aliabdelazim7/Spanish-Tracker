<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Model;

class Course extends Model
{
    protected $fillable = [
        'slug',
        'title',
        'title_ar',
        'description_ar',
        'target_language',
        'source_language',
        'is_published',
    ];

    protected $casts = [
        'is_published' => 'boolean',
    ];

    public function levels()
    {
        return $this->hasMany(CefrLevel::class)->orderBy('order_index');
    }
}
