<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use App\Services\ContractService;

class ExpireOldContractsCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'contracts:expire-old';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Expire contracts that have passed their expiry date';
    protected $contractService;


      public function __construct(ContractService $contractService)
    {
        parent::__construct();
        $this->contractService = $contractService;
    }
    /**
     * Execute the console command.
     */
    public function handle()
    {
          $this->info('Starting contract expiry process...');

        try {
            $expiredCount = $this->contractService->expireOldContracts();
            
            if ($expiredCount > 0) {
                $this->info("Successfully expired {$expiredCount} contracts.");
            } else {
                $this->info('No contracts to expire.');
            }

            // Send expiry alerts for contracts expiring soon
            $alertCount = $this->contractService->sendExpiryAlerts();
            
            if ($alertCount > 0) {
                $this->info("Sent expiry alerts for {$alertCount} contracts.");
            } else {
                $this->info('No expiry alerts to send.');
            }

            return Command::SUCCESS;

        } catch (\Exception $e) {
            $this->error('Error processing contract expiry: ' . $e->getMessage());
            return Command::FAILURE;
        }
    }
}
