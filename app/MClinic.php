<?php

namespace App;

use Jenssegers\Mongodb\Eloquent\Model;

class MClinic extends Model
{
	protected $connection = 'mongodb_conn';
	protected $table = 'clinics';

    protected $fillable = [
		'address',
		'chiros',
    	'clinic_name',
		'contact_number',
		'email',
		'fax_number',
		'meters',
		'website',
		'latitude',
		'longitude',
    ];
}
