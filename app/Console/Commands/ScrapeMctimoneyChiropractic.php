<?php

namespace App\Console\Commands;

use App\Postcode;
use App\Services\ScrapeTwo;
use Illuminate\Console\Command;

class ScrapeMctimoneyChiropractic extends Command
{
    protected $processed = 0;
    
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'scrape:mctimoney_chiropractic';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Scrape data from https://www.mctimoney-chiropractic.org.';

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct()
    {
        parent::__construct();
    }

    /**
     * Execute the console command.
     *
     * @return mixed
     */
    public function handle()
    {
        $start = now();
        
        $this->move();

        print 'ScrapeMctimoneyChiropractic.php done! [' . now()->diffInSeconds($start) . ']';
    }

    private function move($page = 1)
    {
        request()->merge(compact('page'));
        // 39
        // $postcodes = Postcode::paginate(100);
        
        $postcodes = Postcode::whereIn('id', [39])->paginate(100);

        foreach ($postcodes as $postcode) {
            ++$this->processed;
            $start_time = now();

            print ' - post code "' . $postcode->postcode . '" [' . $this->processed . '/' . $postcodes->total() . ']: ';

            sleep(rand(2, 4));

            $data = (new ScrapeTwo)->mctimoneyChiropractic($postcode);
            print now()->diffInSeconds($start_time) . ' seconds with ' . $data->count() .' items.' . PHP_EOL;
        }

        print PHP_EOL . PHP_EOL . '> sleeping...' . PHP_EOL . PHP_EOL;
        sleep(rand(1, 2));

        if ($page != $postcodes->lastPage()) {
            ++$page;
            print 'Proceed to page ' . $page . PHP_EOL;
            
            $this->move(++$page);   
        }
    }
}
