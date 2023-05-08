<?php

namespace Tests\Example\Models;

use Illuminate\Database\Eloquent\Model;

class TestModel extends Model
{
    protected $table = 'tests';

    protected $fillable = ['name'];

    public $timestamps = false;
}
