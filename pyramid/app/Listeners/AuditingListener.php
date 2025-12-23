<?php

namespace App\Listeners;

use App\Models\User;
use OwenIt\Auditing\Events\Auditing;

class AuditingListener
{
    /**
     * Create the event listener.
     */
    public function __construct()
    {
        //
    }

    /**
     * Handle the event.
     */
    public function handle(Auditing $event): bool
    {
        if (get_class($event->model) === User::class) {
            $changes = $event->model->getChanges();

            if (count($changes) === 1 && array_key_first($changes) === 'remember_token') {
                return false;
            }
        }

        return true;
    }
}
