<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class Download extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'down';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

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
     * @return int
     */
    public function handle()
    {
        $res = file_get_contents('https://wallhaven.cc/search?q=id%3A1&sorting=random&ref=fp&seed=smU9W&page=9');
        $out = [];
        preg_match('https://wallhaven.cc/[\^\s]*/[\^\s]*',$res,$out);
        dd($out);
    }
}
