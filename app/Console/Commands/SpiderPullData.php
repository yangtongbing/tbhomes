<?php

namespace App\Console\Commands;

use App\Repositories\SpiderRepository;
use Illuminate\Console\Command;

class SpiderPullData extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'SpiderPullData';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = '爬虫拉数据';

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
        $repository = new SpiderRepository();
        $repository->ceshi();
    }
}
