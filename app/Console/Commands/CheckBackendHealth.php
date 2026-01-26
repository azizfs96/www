<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\BackendHealthCheckService;

class CheckBackendHealth extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'backend:health-check';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'فحص صحة جميع السيرفرات الخلفية وتنفيذ Failover عند الحاجة';

    /**
     * Execute the console command.
     */
    public function handle(BackendHealthCheckService $healthCheckService)
    {
        $this->info('بدء فحص صحة السيرفرات الخلفية...');
        
        $healthCheckService->checkAllBackends();
        
        $this->info('تم إكمال فحص صحة السيرفرات الخلفية.');
        
        return Command::SUCCESS;
    }
}
