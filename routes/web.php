<?php

Route::get('/', 'ChiropractorController@index')->name('chiropractor.index');
Route::post('/', 'ChiropractorController@store');


Route::get('clinics/{clinic}', function (App\Clinic $clinic) {
	dd($clinic->chiros);
});
