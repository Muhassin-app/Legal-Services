<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;

class SetStoragePermissions extends Command
{
    protected $signature = 'set:storage-permissions';
    protected $description = 'Set permissions (chmod -R 777) on the storage directory';

    public function __construct()
    {
        parent::__construct();
    }

    public function handle()
    {
        $storagePath = storage_path();
        chmod($storagePath, 0777);

        $this->info('Permissions set (chmod -R 777) on the storage directory.');
    }
}
