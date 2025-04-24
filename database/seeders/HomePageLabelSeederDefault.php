<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\CabBookingLayout;
use Carbon\Carbon;
use DB;
use Log;

class HomePageLabelSeederDefault extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        $already = CabBookingLayout::get()->pluck('slug');

        $home_page = [
            ['title' => 'Vendors',  'slug' => 'vendors',  'order_by' => 1,  'created_at' => Carbon::now()],
            ['title' => 'Featured Products',  'slug' => 'featured_products',  'order_by' => 2,  'created_at' => Carbon::now()],
            ['title' => 'New Products',  'slug' => 'new_products',  'order_by' => 3,  'created_at' => Carbon::now()],
            ['title' => 'On Sale',  'slug' => 'on_sale',  'order_by' => 4,  'created_at' => Carbon::now()],
            ['title' => 'Best Sellers',  'slug' => 'best_sellers',  'order_by' => 5,  'created_at' => Carbon::now()],
            ['title' => 'Brands',  'slug' => 'brands',  'order_by' => 6,  'created_at' => Carbon::now()],
            ['title' => 'Long Term Service',  'slug'     => 'long_term_service',  'order_by' => 7,  'created_at' => Carbon::now()],
            ['title' => 'Recently Viewed',  'slug'  => 'recently_viewed',  'order_by'   => 7,  'created_at' => Carbon::now()],
            ['title' => 'Spotlight Deals',  'slug'  => 'spotlight_deals',  'order_by'   => 8,  'created_at' => Carbon::now()],
            ['title' => 'Top Rated',  'slug'  => 'top_rated',  'order_by'   => 9,  'created_at' => Carbon::now()],
            ['title' => 'NavCategories',  'slug'  => 'nav_categories',  'order_by'   => 10,  'created_at' => Carbon::now()],
            ['title' => 'Single Category Products',  'slug'  => 'single_category_products',  'order_by'   => 11,  'created_at' => Carbon::now()],
            ['title' => 'Selected Products',  'slug'  => 'selected_products',  'order_by'   => 11,  'created_at' => Carbon::now()],
            ['title' => 'Most Popular Products',  'slug'  => 'most_popular_products',  'order_by'   => 12,  'created_at' => Carbon::now()],
            ['title' => 'Ordered Products',  'slug'  => 'ordered_products',  'order_by'   => 13,  'created_at' => Carbon::now()]
        ];

        if ($already->count() == 0) {
            DB::statement('SET FOREIGN_KEY_CHECKS=0;');
            DB::table('cab_booking_layouts')->truncate();
            DB::statement('SET FOREIGN_KEY_CHECKS=1;');
            DB::table('cab_booking_layouts')->insert($home_page);
        } else {
            foreach ($home_page as $option) {
                if ($already->contains($option['slug'])) {
                    CabBookingLayout::where('slug', $option['slug'])->update($option);
                } else {
                    $home_page = CabBookingLayout::insert($option);
                }
            }
        }
    }
}
