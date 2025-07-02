<?php

namespace App\Http\Console\Commands;

use App\Services\SociollaH2HServices;
use Illuminate\Console\Command;

class ScrapSociollaCommand extends Command {

    protected $signature = 'scrap-sociolla:fetch';

    protected $description = 'Scrapping sociolla products';

    protected $sociollaH2HServices;

    public function __construct(SociollaH2HServices $sociollaH2HServices)
    {
        parent::__construct();
        $this->sociollaH2HServices = $sociollaH2HServices;
    }

    public function handle() {
        $this->info('Initiating sociolla scrapper');
        try {
            $this->info('Getting total product count...');
            $resultCount = $this->sociollaH2HServices->getTotalData();
            $this->info('Total product : '. $resultCount);
            $this->info('Fetching product (Please wait) ...');
            $resultFetch = $this->sociollaH2HServices->getSkincareDetail(10, $resultCount);
            $this->info('Finished fetching product');
            $this->info($resultFetch['skincare_added'].' Skincare Added.');
            $this->info($resultFetch['categories_added'].' Categories Added.');
            $this->info($resultFetch['criteria_added'].' Criteria Added.');
        } catch(\Exception $e) {
            $this->info('Error :'.$e->getMessage());
        }
        $this->info('Scrapping Complete');
    }
}
