<?php
namespace Database\Seeders;

use DB;
use Illuminate\Database\Seeder;
use Carbon\Carbon;
use App\Models\SubscriptionFeaturesListUser;

class SubscriptionFeaturesListUserSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $features_count = DB::table('subscription_features_list_user')->count();
        $features = array(
            array(
                'id' => 1,
                'title' => 'Free Delivery',
                'Description' => '',
                'status' => 1,
                'created_at' =>  Carbon::now(),
                'updated_at' => Carbon::now()
            ),
            array(
                'id' => 2,
                'title' => '% Off On Order',
                'Description' => '',
                'status' => 1,
                'created_at' =>  Carbon::now(),
                'updated_at' => Carbon::now()
            )
        );
        if($features_count == 0)
        {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('subscription_features_list_user')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');

            DB::table('subscription_features_list_user')->insert($features);
        }
        else{
            foreach ($features as $option) {
                $features_list = SubscriptionFeaturesListUser::where('id', $option['id'])->first();
                if ($features_list !== null) {
                    $features_list->update(['title' => $option['title'], 'description' => $option['Description'], 'status' => $option['status']]);
                } else {
                    SubscriptionFeaturesListUser::create([
                        'id' => $option['id'],
                        'title' => $option['title'],
                        'Description' => $option['Description'],
                        'status' => $option['status']
                    ]);
                }
            }
        }
    }
}
