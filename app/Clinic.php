<?php

namespace App;

use Illuminate\Database\Eloquent\Model;

class Clinic extends Model
{
    protected $fillable = [
		'address',
		'chiros',
    	'clinic_name',
		'contact_number',
		'email',
		'fax_number',
		'meters',
		'website',
		'postcode',
		'latitude',
		'longitude',
    ];

    public static function boot()
    {
        parent::boot();
        static::creating(function ($model) {
        	$model->chiros = json_encode($model->chiros);
        });
        static::updating(function ($model) {
        	$model->chiros = json_encode($model->chiros);
        });
    }
}
