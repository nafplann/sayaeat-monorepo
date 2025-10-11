<?php

namespace App\Console\Commands;

use App\Enums\PermissionsEnum;
use App\Models\Permission;
use Illuminate\Console\Command;

class GeneratePermission extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'app:generate-permission {permission}';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Generate new permissions based on \App\Enums\PermissionsEnum value';

    /**
     * Execute the console command.
     */
    public function handle()
    {
        foreach (PermissionsEnum::cases() as $case) {
            if (str_contains($case->value, $this->argument('permission'))) {
                Permission::firstOrCreate([
                    'name' => $case->value,
                ], [
                    'name' => $case->value
                ]);
            }
        }
    }
}
