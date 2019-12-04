<?php

namespace App\Services;

use Goutte\Client;
use App\Clinic;
use Illuminate\Support\Arr;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Log;

use Behat\Mink\Mink;

class ScrapeTwo
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

		// $items = $crawler->filter('#ctl00_HTML_GridView1 .violet.title')->each(function ($node)
		// 	use ($postcode, $website) {

		// 	$client2 = new Client();
		// 	$crawler2 = $client2->request('GET', $website . $node->attr('href'));
		// 	$name = $node->text();

		// 	$clinics = $crawler2->filter('.tab-pane')->each(function ($node2) 
		// 		use ($postcode, $name) {
				
		// 		if ($node2->attr('id') != 'details') {
		// 			$info = $node2->filter('.labelstyle')->each(function ($node3) {
		// 				return $node3->text();
		// 			});

		// 			$info = array_reverse($info);
		// 			$length = count($info);
		// 			--$length;

		// 			$temp = [
		// 				'clinic_name' => null,
		// 				'email' => null,
		// 				'address' => null,
		// 				'contact_number' => null,
		// 				'chiros' => [$name],
		// 				'postcode' => $postcode->postcode,
		// 				'latitude' => $postcode->latitude,
		// 				'longitude' => $postcode->longitude
		// 			];

		// 			foreach ($info as $i => $text) {

		// 				$checker = strtolower(str_replace(' ', '', $text));

		// 				$email = filter_var($text, FILTER_VALIDATE_EMAIL);

		// 				if ($email) {
		// 					$temp['email'] = $email;

		// 					continue;
		// 				}

		// 				$website = filter_var(Str::start($text, 'https://'), FILTER_VALIDATE_URL);

		// 				if ($i == 0 && $website) {
		// 					$temp['website'] = $website;

		// 					continue;
		// 				}

		// 				if ($i == $length) {
		// 					$temp['clinic_name'] = $text;

		// 					if (isset($address)) {
		// 						$temp['address'] = implode(array_reverse($address));
		// 					} else {
		// 						Log::error('[' . $postcode->postcode . '] clinic name:' . $text . ' no address.');
		// 					}

		// 					return $temp;
		// 				}

		// 				if (is_numeric($checker)) {
		// 					$temp['contact_number'] = $text;

		// 					continue;
		// 				}

		// 				if ($i < $length && isset($temp['contact_number'])) {
		// 					$address[] = $text;

		// 					continue;
		// 				}
		// 			}
		// 		}
		// 	});


		// 	return array_filter($clinics);
		// });

		// $form = $crawler->filter('#aspnetForm')->form();
		// $form['__VIEWSTATEENCRYPTED'] = 2;

		// $values = $form->getValues();

		// $crawlerX = $client->submit($form);

		// dd($crawlerX->filter('#ctl00_HTML_GridView1 .violet.title')->first()->text());


		// set the default session name

		// $crawler = $crawler->addContent('<script type="text/javascript">
		// 	theForm.__EVENTTARGET.value = "ctl00$HTML$GridView1";
		// 	theForm.__EVENTARGUMENT.value = "Page$2";
		// 	theForm.submit();
		// </script>');

		$newHtml = '<script type="text/javascript">
			function jimits() {
				theForm.__EVENTTARGET.value = "ctl00$HTML$GridView1";
				theForm.__EVENTARGUMENT.value = "Page$2";
				theForm.submit();
			}

			jimits();
		</script>';

		$xq = $crawler->add($newHtml);

		dd($xq);

		$items = Arr::flatten($items, 1);

		dd($items);

		return $this->insertToClinic($items);
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
				$contact_number = trim($addr->nextAll()->text()) == 'No phone number listed' ? null : $addr->nextAll()->text();
				return [
					'address' => trim(preg_replace('/(\v|\s)+/', ' ', $addr->text())),
					'chiros' => [$name],
					'clinic_name' => $name,
					'contact_number' => $contact_number,
					'email' => null,
					'postcode' => $postcode->postcode,
					'latitude' => $postcode->latitude,
					'longitude' => $postcode->longitude
				];
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

		return $this->insertToClinic($items);
	}

}
