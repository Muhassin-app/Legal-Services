<?php

namespace App\Services;

use App\Models\ClientPreference;

class InventoryService
{
    # check if inventory system on 
    public static function checkIfInventoryOn()
    {
        $preference = ClientPreference::first();
        if ((checkColumnExists('client_preferences', 'need_inventory_service')) && @$preference->need_inventory_service == 1 && !empty(@$preference->inventory_service_key_url) && !empty(@$preference->inventory_service_key_code))
            return $preference;
        else
            return false;
    }
}
