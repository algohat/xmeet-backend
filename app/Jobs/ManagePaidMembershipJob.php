<?php

namespace App\Jobs;

use App\Libraries\Membership;
use App\Models\UserPackage;
use App\Models\UserPackageFeature;
use Carbon\Carbon;
use Illuminate\Bus\Queueable;
use Illuminate\Contracts\Queue\ShouldQueue;
use Illuminate\Foundation\Bus\Dispatchable;
use Illuminate\Queue\InteractsWithQueue;
use Illuminate\Queue\SerializesModels;
use Illuminate\Support\Facades\Log;

class ManagePaidMembershipJob implements ShouldQueue
{
    use Dispatchable, InteractsWithQueue, Queueable, SerializesModels;

    public $user = null;
    public $user_package = null;
    public $time_before;

    /**
     * Create a new job instance.
     *
     * @return void
     */
    public function __construct($payload)
    {
        $this->user = $payload['user'];
        $this->time_before = $payload['time_before'] ?? null;
    }

    /**
     * Execute the job.
     *
     * @return void
     */
    public function handle()
    {
        //get active premium package
        $this->user_package = UserPackage::where('user_id', $this->user->id)
            ->where('package_id', '!=', 1)
            ->where('status', 2)
            ->first();

        if ($this->user_package) {
            $user_current_package = $this->user_package;
            if ($user_current_package->end_time != null) {
                if ($user_current_package->end_time < Carbon::now()) {

                    //Update in UserPackage and UserPackageFeature
                    UserPackage::where('id', $this->user_package->id)->update(['status' => 3]); // 1=pending;2=active;3=expired;4=cancelled;5=inactive;6=renewed
                    UserPackageFeature::where('user_package_id', $this->user_package->id)->update(['status' => 3]); // 1=pending;2=active;3=expired;4=cancelled;5=inactive

                    //Active the free package
                    $payload = ['user' => $this->user];
                    $membership = new Membership($payload);
                    $membership->updateDefaultSubscriptionStatus(2);

                }
            } else {
                Log::info("This package for unlimited time");
            }

        } else {
            Log::info("no active premium package found");
        }
    }
}
