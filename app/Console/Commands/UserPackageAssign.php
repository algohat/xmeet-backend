<?php

namespace App\Console\Commands;

use App\Libraries\Membership;
use App\Models\User;
use Illuminate\Console\Command;

class UserPackageAssign extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'user:package-assign';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Command description';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        $users = User::get();

        foreach ($users as $user) {
            if (!@$user->userPackage) {
                $this->info("Processing Free package for user ID: " . $user->id);
                (new Membership())->createUserDefaultPackage($user);
                $this->info("Free package Assigned");
            } else {
                $this->info("Package is already exist for user ID: " . $user->id);
            }
        }

        return 0;
    }
}
