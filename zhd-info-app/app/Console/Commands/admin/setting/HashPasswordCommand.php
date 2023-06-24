<?php

namespace App\Console\Commands\admin\setting;

use App\Models\User;
use Illuminate\Console\Command;
use Illuminate\Support\Facades\Hash;

class HashPasswordCommand extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:hash-password-command';

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
        foreach($users as $user) {
            $user->password = Hash::make($user->password);
            $user->save();
        }
    }
}
