<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Page extends Model
{
    protected $table = 'pages';

    protected $fillable = ['name', 'section', 'raw_html', 'formatted_html', 'category', 'page_updated_at'];

    protected $dates = ['page_updated_at'];

    public function getRouteKeyName()
    {
        return 'name';
    }
}
