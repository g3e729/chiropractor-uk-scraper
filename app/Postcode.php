<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Postcode extends Model
{
    public $timestamps = false;

    protected $fillable = [
		'postcode',
		'x',
		'y',
		'latitude',
		'longitude',
    ];
}
