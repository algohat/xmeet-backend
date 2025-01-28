<?php

namespace App\Console\Commands;

use App\Models\UserPackageFeature;
use Carbon\Carbon;
use Illuminate\Console\Command;

class UserFreePackageReset extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'reset:user-free-package';

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
        UserPackageFeature::whereHas('user_package.user')
            ->whereHas('user_package', function ($query) {
                $query->where('package_id', 1)
                    ->where('status', 2);
            })
            ->where('value', '!=', 0)
            ->whereNotNull('value')
            ->where('expiration_date_time', '<=', Carbon::now())
            ->with('user_package.user')
            ->chunkById(100, function ($expiredFeatures) {
                foreach ($expiredFeatures as $feature) {
                    // Check if the feature is expired
                    if (Carbon::now()->greaterThanOrEqualTo($feature->expiration_date_time)) {
                        // Reset the used_amount and extend expiration_date_time by 1 day
                        $feature->used_amount = 0;
                        // Calculate new expiration_date_time based on time_limit and time_option
                        $timeLimit = $feature->time_limit ?? 1; // Default to 1 if null
                        $timeOption = $feature->time_option ?? 'day'; // Default to 'day' if null
                        $feature->expiration_date_time = Carbon::now()->{"add" . ucfirst($timeOption)}($timeLimit);
                        $feature->save();
                    }
                }
            });
        $this->info('Used amounts reset and expiration dates extended for a batch of expired features.');
    }
}
