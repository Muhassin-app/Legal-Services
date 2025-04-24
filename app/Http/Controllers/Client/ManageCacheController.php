<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\Controller;
use App\Models\User;
use Illuminate\Http\Request;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\CacheManagement;
use App\Http\Traits\{ApiResponser,RedisCacheTrait};
use Illuminate\Support\Facades\Cache;

class ManageCacheController extends Controller
{
    use ApiResponser,RedisCacheTrait;


    public function index(Request $request)
    {
        $additionalPreferences = getAdditionalPreference(['is_cache_enable_for_home','cache_reset_time_for_home','cache_radius_for_home'],1);

        // dd($additionalPreferences);
        return view('backend/cacheManagement/index',compact('additionalPreferences'));
    }
  
}