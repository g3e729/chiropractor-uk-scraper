<?php

namespace App\Http\Controllers;

use App\Postcode;
use App\Services\ScrapeService;
use App\Http\Controllers\Controller;
use Illuminate\Http\Request;

class ChiropractorController extends Controller
{
	public function index()
	{
		$postcodes = Postcode::all();

		return view('index', compact('postcodes'));
	}

	public function store(Request $request)
	{
		$postcode = Postcode::find($request->get('postcode_id'));

		$data = (new ScrapeService)->chiropractor($postcode->latitude, $postcode->longitude);

		return $data;
	}
}
