<?php

use Illuminate\Foundation\Inspiring;
use Illuminate\Support\Facades\Artisan;

Artisan::command('inspire', function () {
    $this->comment(Inspiring::quote());
})->purpose('Display an inspiring quote');


// Register the contract expiry command
Artisan::command('contracts:expire-old', function () {
    $contractService = app(\App\Services\ContractService::class);
    
    $this->info('Starting contract expiry process...');

    try {
        $expiredCount = $contractService->expireOldContracts();
        
        if ($expiredCount > 0) {
            $this->info("Successfully expired {$expiredCount} contracts.");
        } else {
            $this->info('No contracts to expire.');
        }

        // Send expiry alerts for contracts expiring soon
        $alertCount = $contractService->sendExpiryAlerts();
        
        if ($alertCount > 0) {
            $this->info("Sent expiry alerts for {$alertCount} contracts.");
        } else {
            $this->info('No expiry alerts to send.');
        }

        return 0; // Success
    } catch (\Exception $e) {
        $this->error('Error processing contract expiry: ' . $e->getMessage());
        return 1; // Failure
    }
})->purpose('Expire contracts that have passed their expiry date');