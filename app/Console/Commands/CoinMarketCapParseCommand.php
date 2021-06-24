<?php

namespace App\Console\Commands;

use App\Services\CoinMarketCapService;
use Illuminate\Console\Command;

class CoinMarketCapParseCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'coin-market-cap-parse';

    private $capService;

    /**
     * Create a new command instance.
     *
     * @return void
     */
    public function __construct(CoinMarketCapService $capService)
    {
        parent::__construct();

        $this->capService = $capService;
    }

    public function handle()
    {
        $this->capService->insertData('3m');
        $this->capService->updateProjectGrowth();
        $this->capService->recountRating();
    }
}
