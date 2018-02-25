<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = 'pages';

    protected $fillable = [
        'name',
        'section',
        'raw_html',
        'formatted_html',
        'category',
        'page_updated_at',
        'source',
        'os',
        'description',
        'short_description',
        'table_of_contents_html',
    ];

    protected $dates = ['page_updated_at'];

    public function getRouteKeyName()
    {
        return 'name';
    }

    public function getUrl()
    {
        return \URL::to("/pages/{$this->section}/{$this->name}");
    }
}
