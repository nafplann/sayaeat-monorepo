<?php

namespace App\Policies;

use App\Models\Menu;
use App\Models\Merchant;
use App\Models\Order;
use App\Models\ShipmentOrder;
use App\Models\User;
use Illuminate\Database\Eloquent\Model;

class BasePolicy
{
    /**
     * Create a new policy instance.
     */
    public function __construct()
    {
        //
    }

    /**
     * Determine whether the user can view the model.
     */
    public function view(User $user, Model $model): bool
    {
        if (!$user->isOwner()) {
            return true;
        }

        switch ($model) {
            case $model instanceof Merchant:
                return $user->id === $model->owner_id;
            case $model instanceof Menu:
            case $model instanceof Order:
                $merchantIds = $user->merchants->pluck('id')->toArray();
                return in_array($model->merchant_id, $merchantIds);
            case $model instanceof ShipmentOrder:
                return false;
            default:
                return true;
        }
    }

    /**
     * Determine whether the user can update the model.
     */
    public function update(User $user, Model $model): bool
    {
        if (!$user->isOwner()) {
            return true;
        }

        switch ($model) {
            case $model instanceof Merchant:
                return $user->id === $model->owner_id;
            case $model instanceof Menu:
                $merchantIds = $user->merchants->pluck('id')->toArray();
                return in_array($model->merchant_id, $merchantIds);
            default:
                return true;
        }
    }

    /**
     * Determine whether the user can delete the model.
     */
    public function delete(User $user, Model $model): bool
    {
        if (!$user->isOwner()) {
            return true;
        }

        switch ($model) {
            case $model instanceof Merchant:
                return $user->id === $model->owner_id;
            case $model instanceof Menu:
                $merchantIds = $user->merchants->pluck('id')->toArray();
                return in_array($model->merchant_id, $merchantIds);
            default:
                return true;
        }
    }
}
