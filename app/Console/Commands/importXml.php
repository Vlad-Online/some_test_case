<?php

namespace App\Console\Commands;

use App\Import;
use App\Offer;
use Illuminate\Console\Command;

class importXml extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'import:xml';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Import XML files from data directory';


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
        echo "Start import\r\n";
        $time    = time();
        $imports = glob(base_path().'/data/import*.xml');
        $offers  = glob(base_path().'/data/offers*.xml');

        echo "Importing products\r\n";
        foreach ($imports as $importPath) {
            echo "Parsing file ".$importPath."\r\n";
            $import = new Import($importPath);
            $import->checkColumn($import->getCitySlug());
            foreach ($import->getProductIterator() as $productData) {
                $import->importProduct($productData);
            }
        }

        echo "Importing offers\r\n";
        foreach ($offers as $offerPath) {
            echo "Parsing file ".$offerPath."\r\n";
            $offer = new Offer($offerPath);
            foreach ($offer->getProductIterator() as $productData) {
                $offer->importProduct($productData);
            }
        }
        $endTime = time();
        $minutes = intdiv($endTime - $time, 60);
        $seconds = ($endTime - $time) % 60;
        $seconds = $seconds < 10 ? '0'.$seconds : $seconds;
        echo "Total time: ".$minutes.':'.$seconds."\r\n";
        echo "Finished\r\n";
    }


}
