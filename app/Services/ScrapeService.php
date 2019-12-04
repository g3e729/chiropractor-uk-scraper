<?php

namespace App\Services;

use Goutte\Client;
use App\Clinic;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;

class ScrapeService
{
	protected $items = [];

	private function insertToClinic(array $data) {
		$items = collect([]);

		foreach ($data as $temp) {
			$item = Clinic::where('clinic_name', $temp['clinic_name'])
				->where('address', $temp['address'])
				->first();

			if ($item) {
				// $temp['chiros'] = array_merge($item->chiros, $temp['chiros']);
				$item->update($temp);
			} else {
				$item = Clinic::create($temp);
			}

			if ($item) {
				$items->push($item);
			}
		}
		
		return $items;
	}

    public function chiropracticUk($postcode)
    {
    	$attributes = [
			'meters',
			'address',
			'contact_number',
			'website',
		];

		$loc = $postcode->postcode;
		$rad = 15;
		$order = 'dist';

		$url = 'https://chiropractic-uk.co.uk/find-a-chiropractor/?';
		$url .= http_build_query(compact('loc', 'rad', 'order'));

		$client = new Client();
		$crawler = $client->request('GET', $url);

		$items = $crawler->filter('.facr-clinic')->each(function ($node) use ($postcode, $attributes) {
			$clinic_name = $node->filter('.facr-title a')->text();

			$texts = $node->filter('.facr-full p')->each(function ($node) {
				return $node->text();
			});

			$proceed = false;
			$key = 0;
			$i = 0;
			$items = [];
			$default = [
				'clinic_name' => null,
				'email' => null,
				'postcode' => $postcode->postcode,
				'latitude' => $postcode->latitude,
				'longitude' => $postcode->longitude
			];
			$temp = $default;
			$chiros = [];

			foreach ($texts as $text) {
				$checker = strtolower(str_replace(' ', '', $text));

				if (preg_match('/(\v|\s)+/', $checker)) {
					$proceed = true;
				}

				if (! in_array($checker, ["getdirections", "less"])) {

					if ($proceed) {
						$email = filter_var($text, FILTER_VALIDATE_EMAIL);

						if ($email) {
							$temp['email'] = $email;

							continue;
						}

						if (isset($temp['contact_number']) && is_numeric($checker)) {
							$temp['fax_number'] = $text;

							continue;
						}

						$temp[$attributes[$i] ?? $i] = trim(preg_replace('/(\v|\s)+/', ' ', $text));
						++$i;
					} else {
						$chiros[] = $text;
					}

				}

				if ($checker == 'less') {
					$temp['clinic_name'] = $clinic_name;
					$temp['chiros'] = $chiros;

					$items[] = $temp;

					$proceed = false;
					$i = 0;
					$temp = $default;
					$chiros = [];
					++$key;

					continue;
				}
			}

			return $items;
		});

		$items = Arr::flatten($items, 1);

		return $this->insertToClinic($items);
	}

    public function mctimoneyChiropractic($postcode)
    {
		$website = 'https://www.mctimoney-chiropractic.org/';
		$url = $website . 'ChiroSearch.aspx?';
		$txtSearch = $postcode->postcode;
		$human = true;
		$animal = false;

		$url .= http_build_query(compact('txtSearch', 'human', 'animal'));

		$client = new Client();
		$crawler = $client->request('GET', $url);

		$chiros = $crawler->filter('#ctl00_HTML_GridView1 .violet.title')->each(function ($node) {
			return ['name' => $node->text(), 'url' => $node->attr('href')];
		});

		$items = collect([]);

		foreach ($chiros as $chiropractor) {
			$crawler = $client->request('GET', $website . $chiropractor['url']);

			$clinics = array_filter($crawler->filter('.tab-pane')->each(function ($node) {
				if ($node->attr('id') != 'details') {
					return $node->filter('.labelstyle')->each(function ($node2) {
						return $node2->text();
					});
				}
			}));

			foreach($clinics as $info) {
				$info = array_reverse($info);
				$length = count($info);
				--$length;

				$temp = [
					'clinic_name' => null,
					'email' => null,
					'address' => null,
					'contact_number' => null,
					'chiros' => [$chiropractor['name']],
					'postcode' => $postcode->postcode,
					'latitude' => $postcode->latitude,
					'longitude' => $postcode->longitude
				];

				foreach ($info as $i => $text) {

					$checker = strtolower(str_replace(' ', '', $text));

					$email = filter_var($text, FILTER_VALIDATE_EMAIL);

					if ($email) {
						$temp['email'] = $email;

						continue;
					}

					$website = filter_var(Str::start($text, 'https://'), FILTER_VALIDATE_URL);

					if ($i == 0 && $website) {
						$temp['website'] = $website;

						continue;
					}

					if ($i == $length) {
						$temp['clinic_name'] = $text;
						$temp['address'] = implode(array_reverse($address));

						$item = Clinic::where('clinic_name', $temp['clinic_name'])
							->where('address', $temp['address'])
							->first();

						if ($item) {
							// $temp['chiros'] = array_merge($item->chiros, $temp['chiros']);
							$item->update($temp);
						} else {
							$item = Clinic::create($temp);
						}

						if ($item) {
							$items->push($item);
						}
						
						$items->push($item);

						continue;
					}

					if (is_numeric($checker)) {
						$temp['contact_number'] = $text;

						continue;
					}

					if ($i < $length && isset($temp['contact_number'])) {
						$address[] = $text;

						continue;
					}
				}
			}
		}

		return $items;
	}


    public function ggcUk($postcode, $page = 0)
    {
		$website = 'https://www.gcc-uk.org/';
		$url = $website . 'search/chiro_results';

		if ($page) {
			$url .= '/' . 'P' . ($page * 10);
		}

		$url .= '?';

		$chiro_postcode = $postcode->postcode;
		$chiro_latitude = $postcode->latitude;
		$chiro_longitude = $postcode->longitude;

		$url .= http_build_query(compact('chiro_postcode', 'chiro_latitude', 'chiro_longitude'));

		$client = new Client();
		$crawler = $client->request('GET', $url);
		
		$items = [];

		$temp = $crawler->filter('.chiro_card')->each(function ($node) use ($postcode) {
			$name = $node->filter('.card-title')->text();
			// $email = $node->filter('.fa-envelope')->parents()->parents()->text();

			$branches = $node->filter('.card-deck .card-body')->each(function ($node) use ($name, $postcode) {
				$addr = $node->filter('p');
				$address = trim(preg_replace('/(\v|\s)+/', ' ', $addr->text()));
				$contact_number = $addr->nextAll()->text();
				$latitude = $postcode->latitude;
				$longitude = $postcode->longitude;
				$clinic_name = $name;
				$chiros = [$name];

				$temp = compact('address', 'chiros', 'clinic_name', 'contact_number', 'email', 'latitude', 'longitude');

				$item = Clinic::where('clinic_name', $temp['clinic_name'])
					->where('address', $temp['address'])
					->first();

				if ($item) {
					// $temp['chiros'] = array_merge($item->chiros, $temp['chiros']);
					$item->update($temp);
				} else {
					$item = Clinic::create($temp);
				}
			});

			return $branches;
		});

		$items = array_merge($items, $temp);
		$items = Arr::flatten($items, 1);

		$this->items = array_merge($this->items, $items);

		$total_page = $crawler->filter('.pagination li a')->count() - 2;

		if ($page < $total_page) {
			$page++;
			$this->ggcUk($postcode, $page);
		}

		$this->items = collect($this->items);

		return $this->items;
	}

}
