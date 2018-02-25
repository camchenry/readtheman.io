<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Section extends Model
{
    protected $table = 'sections';

    public function getRouteKeyName()
    {
        return 'section';
    }

    public function getUrl()
    {
        return \URL::to("/section/{$this->section}");
    }
}
