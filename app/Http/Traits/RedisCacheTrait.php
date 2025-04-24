<?php
namespace App\Http\Traits;
use Log;
use Illuminate\Support\Facades\Redis;
use Illuminate\Http\Request;


trait RedisCacheTrait{
    public $cache_minutes = '3600';
    public $radius = '2';

    public function storeLocations($locations,$data='',$loc_key = 'geo_fence:locations') {
        $redis = Redis::connection();
        $redis->pipeline(function ($pipe) use ($locations,$data,$loc_key) {
            //foreach ($locations as $location) {
                $pipe->geoadd($loc_key, $locations[0]['longitude'], $locations[0]['latitude'], $locations[0]['key']);
                Redis::set($locations[0]['key'], json_encode($data));
                Redis::expire($locations[0]['key'], $this->cache_minutes);
            //}
        });
    }

    public function isPointInRadius($latitude, $longitude, $radius= '5', $key='geo_fence:locations') {

        $redis = Redis::connection();
        //echo $key;
        $ret = [];
        $result = $redis->georadius($key, $longitude, $latitude, $radius, 'km', 'WITHDIST');
        $ret = $result;
        if(@$result[0][0]) {
            $cachedResult = Redis::get($result[0][0]);
            //$cachedResult['cacheKey'] = $cacheKey??'';
            if ($cachedResult) {
                $ret['data'] = json_decode($cachedResult);
            } 
        } 
        return $ret;

    }

    public function deleteKeysContainingWord(Request $request)
    {

        try {
            $redis = Redis::connection();
            $redis->select(0);
            $keys = $redis->keys('*:'.$request->code.'*');
            foreach ($keys as $key) {
                shell_exec("redis-cli DEL ".$key);
            }
            return response()->json([
                "success" => true,
                'message' => "Keys containing the word \"redis\" have been deleted.",
            ]);
        } catch (\Exception $e) {
            return response()->json([
                "success" => false,
                'message' => "Something went wrong!",
            ]);
        }
        
    }       
}
