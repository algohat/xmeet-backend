<?php

namespace App\Libraries;

use App\Models\Package;
use App\Models\PackageFeature;
use App\Models\UserPackage;
use App\Models\UserPackageFeature;
use Carbon\Carbon;
use Illuminate\Support\Facades\Auth;

class Membership
{
    public $user_package;
    public $user = null;
    public $package_info = null;
    public $package_time_period = null;
    public $payment_status = 1;
    public $request_input = null;
    public $apple_response_data = null;
    public $google_response_data = null;

    public function __construct($payload = null)
    {
        $this->user                 = $payload['user'] ?? null;
        $this->package_info         = $payload['package_info'] ?? null;
        $this->user_package         = $payload['user_package'] ?? null;
        $this->package_time_period  = $payload['package_time_period'] ?? null;
        $this->payment_status       = $payload['payment_status'] ?? 1;
        $this->request_input        = $payload['request_input'] ?? null;
        $this->apple_response_data  = $payload['apple_response_data'] ?? null;
        $this->google_response_data = $payload['google_response_data'] ?? null;
    }

    public function createUserDefaultPackage($user)
    {
        $this->user_package = UserPackage::where('user_id', $user->id)->where('status', 2)->where('package_id', 1)->with(['userPackageFeature'])->first();
        if (!$this->user_package) {
            $package_info = Package::find(1);
            $user_new_package                      = new UserPackage();
            $user_new_package->user_id             = $user->id;
            $user_new_package->package_id          = 1;
            $user_new_package->package_name        = $package_info->name;
            $user_new_package->package_type        = $package_info->type;
            $user_new_package->description         = $package_info->description;
            $user_new_package->status              = 2;
            $user_new_package->user_original_id    = $user->id;
            $user_new_package->user_name           = $user->name;
            $user_new_package->user_phone          = $user->phone;
            $user_new_package->user_email          = $user->email;
            $user_new_package->start_time          = $user->created_at;
            $user_new_package->save();

            $this->user_package = $user_new_package;

            $this->createUserPackageFeature($user_new_package->package_id);
            $this->user_package = UserPackage::where('user_id', $user->id)->where('status', 2)->with(['userPackageFeature'])->first();

        } else {
            if (count($this->user_package->userPackageFeature) == 0) {
                $this->createUserPackageFeature($this->user_package->package_id);
                $this->user_package = UserPackage::where('user_id', $user->id)->where('status', 2)->with(['userPackageFeature'])->first();
            }
        }
        return $this->user_package;
    }

    public function createUserPackage()
    {

        $package_end_time = null;
        $user_new_package                      = new UserPackage();
        $user_new_package->package_id          = $this->package_info->id;
        $user_new_package->package_name        = $this->package_info->package_name;
        $user_new_package->package_description = $this->package_info->description;
        $user_new_package->user_id             = $this->user->id;
        $user_new_package->user_original_id    = $this->user->id;
        $user_new_package->status              = 2;
        $user_new_package->payment_status      = $this->payment_status;

        if ($this->request_input && @$this->request_input['verificationData']['source']) {
            $user_new_package->store_name = $this->request_input['verificationData']['source'];
        }
        if ($this->request_input && @$this->request_input['verificationData']['source']) {
            $user_new_package->transaction_medium = $this->request_input['verificationData']['source'] == 'google_play' ? 'google' : 'apple';
        }
        if ($this->request_input && @$this->request_input['purchaseID']) {
            $user_new_package->order_id = $this->request_input['purchaseID'];
        }
        if ($this->request_input && @$this->request_input['purchaseID']) {
            $user_new_package->purchase_id = $this->request_input['purchaseID'];
        }
        if ($this->request_input && @$this->request_input['transactionDate']) {
            $timestampInSeconds = $this->request_input['transactionDate'] / 1000;
            $transactionDate = date('Y-m-d H:i:s', $timestampInSeconds);
            $user_new_package->transaction_date = $transactionDate;
        }
        if ($this->request_input && @$this->request_input['productID']) {
            $user_new_package->product_id = $this->request_input['productID'];
        }
        if ($this->request_input && @$this->request_input['auto_renewing']) {
            $user_new_package->auto_renewing = $this->request_input['auto_renewing'];
        }
        if ($this->request_input && @$this->request_input['verificationData']) {
            $localVerificationData = $this->request_input['verificationData']['localVerificationData'] ?? null;

            if ($localVerificationData) {
                $localVerificationDataArray = json_decode($localVerificationData, true);
                if (is_array($localVerificationDataArray)) {
                    $user_new_package->purchase_token = $localVerificationDataArray['purchaseToken'] ?? null;;
                }
            }
        }
        if (!empty($this->apple_response_data) && $this->apple_response_data['original_transaction_id']) {
            $user_new_package->transaction_id = $this->apple_response_data['original_transaction_id'];
        }

        $user_new_package->start_time = Carbon::now();

        if ($this->package_time_period) {
            $user_new_package->package_time_period_id    = $this->package_time_period->id;
            $user_new_package->package_validity          = $this->package_time_period->validity;
            $user_new_package->package_validity_type     = $this->package_time_period->validity_type;
            $user_new_package->price                     = $this->package_time_period->price;
            $package_end_time = $this->getExpiredTime($this->package_time_period->validity, strtolower($this->package_time_period->validity_type));
            $user_new_package->end_time                  = $package_end_time;
        }

        //UPDATE CURRENT PACKAGE AND THEN ADD NEW PACKAGE
        UserPackage::where('user_id', Auth::id())->where('status', 2)->update(['status' => 5]);

        $user_new_package->save();

        $this->user_package = $user_new_package;
        $this->createUserPackageFeature($this->package_info->id, null);
        $this->user_package = UserPackage::where('user_id', Auth::id())->where('status', 2)->with(['userPackageFeature'])->first();
        $this->user_package = $user_new_package;

        return $this->user_package;
    }

    public function createUserPackageFeature($package_id, $package_period_id = null)
    {
        if ($package_id) {
            if (!$package_period_id) {
                $package_features = PackageFeature::where('package_id', $package_id)->get();
            } else {
                $package_features = PackageFeature::where('package_id', $package_id)->get();
            }

            if(!empty($package_features)) {
                foreach ($package_features as $package_feature) {
                    $user_pkg_feature                       = new UserPackageFeature();
                    $user_pkg_feature->user_package_id      = $this->user_package->id;
                    $user_pkg_feature->package_feature_id   = $package_feature->id;
                    $user_pkg_feature->feature_type         = $package_feature->feature_type ;
                    $user_pkg_feature->value                = $package_feature->value;
                    $user_pkg_feature->time_limit           = $package_feature->time_limit;
                    $user_pkg_feature->time_option          = $package_feature->time_option;
                    $user_pkg_feature->description          = $package_feature->description;

                    $expiration_date_time = $this->getExpiredTime($package_feature->time_limit, strtolower($package_feature->time_option));
                    $user_pkg_feature->expiration_date_time = $expiration_date_time;
                    $user_pkg_feature->status               = 2;
                    $user_pkg_feature->save();
                }
            }
        }
    }

    public function getExpiredTime($time_limit, $time_option)
    {
        $now = Carbon::now();
        switch ($time_option) {
            case 'minute':
                $now->addMinutes($time_limit);
                break;
            case 'hour':
                $now->addHour($time_limit);
                break;
            case 'day':
                $now->addDay($time_limit);
                break;
            case 'month':
                $now->addMonth($time_limit);
                break;
            case 'year':
                $now->addYear($time_limit);
                break;
            default:
                $now = null;
                break;
        }

        return $now;
    }

    /**
     * Update the default subscription status
     *
     * @param $status
     */
    public function updateDefaultSubscriptionStatus($status) {
        $userPackage = UserPackage::where('user_id', $this->user->id)->where('package_id', 1)->first();
        if ($userPackage) {
            $userPackage->status = $status;
            $userPackage->save();

            // Update expiration_date_time for associated features for active package
            if($status == 2) {
                $features = UserPackageFeature::where('user_package_id', $userPackage->id)
                    ->where('value', '!=', 0)
                    ->whereNotNull('value')
                    ->get();

                if($features) {
                    foreach ($features as $feature) {
                        $timeLimit  = $feature->time_limit ?? 1;  // Default to 1 if null
                        $timeOption = $feature->time_option ?? 'day'; // Default to 'day' if null

                        $feature->used_amount = 0;
                        $feature->expiration_date_time = Carbon::now()->{"add" . ucfirst($timeOption)}($timeLimit);
                        $feature->save();
                    }
                }
            }
        }
    }

    public static function checkActivePackageDetails($user_id)
    {
        $user_package = UserPackage::where('user_id', $user_id)->where('status', 2)->first();
        if ($user_package) {
            $user_package->userPackageFeature = UserPackageFeature::where('user_package_id', $user_package->id)->get();
            return $user_package;
        }
        return null;
    }

    public static function checkUserLikeAmountExist($userId)
    {
        $user_package = UserPackage::where('user_id', $userId)->where('status', 2)->first();
        if (!$user_package) {
            return false;
        }

        $like_feature = UserPackageFeature::where('tag', 'like')->where('user_package_id', $user_package->id)->first();

        if ($like_feature && $like_feature->used_amount >= $like_feature->value) {
            $message = 'You have already used the maximum number of free likes for ' . $like_feature->time_limit . ' ' . $like_feature->time_option;
            return false;
        }

        return true;
    }

    public static function checkFriendRequestAmountExist($userId)
    {
        $user_package = UserPackage::where('user_id', $userId)->where('status', 2)->first();
        if (!$user_package) {
            return false;
        }

        $friend_request_feature = UserPackageFeature::where('tag', 'friend_request')->where('user_package_id', $user_package->id)->first();

        if ($friend_request_feature && $friend_request_feature->used_amount >= $friend_request_feature->value) {
            $message = 'You have already used the maximum number of free Friend Request for ' . $friend_request_feature->time_limit . ' ' . $friend_request_feature->time_option;
            return false;
        }

        return true;
    }

    public static function adjustLikeAmount($userId)
    {
        $user_package = UserPackage::where('user_id', $userId)->where('status', 2)->first();
        if (!$user_package) {
            return false;
        }

        $like_feature = UserPackageFeature::where('tag', 'like')->where('user_package_id', $user_package->id)->first();

        if ($like_feature && $like_feature->used_amount < $like_feature->value) {
            $like_feature->used_amount = ($like_feature->used_amount ?? 0) + 1;
            $like_feature->save();
        }

        return true;
    }

    public static function adjustFriendRequestAmount($userId)
    {
        $user_package = UserPackage::where('user_id', $userId)->where('status', 2)->first();
        if (!$user_package) {
            return false;
        }

        $friend_request_feature = UserPackageFeature::where('tag', 'friend_request')->where('user_package_id', $user_package->id)->first();

        if ($friend_request_feature && $friend_request_feature->used_amount < $friend_request_feature->value) {
            $friend_request_feature->used_amount = ($friend_request_feature->used_amount ?? 0) + 1;
            $friend_request_feature->save();
        }

        return true;
    }



}
