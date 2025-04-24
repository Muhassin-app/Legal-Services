<?php
namespace Database\Seeders;
use DB;
use App\Models\Type;
use Illuminate\Database\Seeder;

class TypeSeeder extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     * service_type :: you can see this in constants.php in config
     */
    public function run(){
        DB::statement('SET FOREIGN_KEY_CHECKS=0;');
        DB::table('types')->truncate();
        DB::statement('SET FOREIGN_KEY_CHECKS=1;');
        $types = array(
            [
                'id' => 1,
                'service_type' => 'products_service',
                'sequence' => 2,
                'title' => 'Product',
                'image' => 'product.png',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
            ],
            [
                'id' => 2,
                'service_type' => 'pick_drop_parent_service',
                'sequence' => 7,
                'title' => 'Pickup/Parent',
                'image' => 'pickup_delivery.png',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.'
            ],
            [
                'id' => 3,
                'service_type' => 'products_service',
                'sequence' => 3,
                'title' => 'Vendor',
                'image' => 'vendor.png',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
            ],
            [
                'id' => 4,
                'sequence' => 4,
                'service_type' => 'products_service',
                'title' => 'Brand',
                'image' => 'brand.png',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
            ],
            [
                'id' => 5,
                'service_type' => 'products_service',
                'sequence' => 6,
                'title' => 'Celebrity',
                'image' => 'celebrity.png',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
            ],
            [
                'id' => 6,
                'sequence' => 1,
                'service_type' => 'products_service',
                'title' => 'Subcategory',
                'image' => 'subcategory.png',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
            ],
            [
                'id' => 7,
                'service_type' => 'pick_drop_service',
                'sequence' => 6,
                'title' => 'Pickup/Delivery',
                'image' => 'dispatcher.png',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
            ],
            [
                'id' => 8,
                'service_type' => 'on_demand_service',
                'sequence' => 7,
                'title' => 'On Demand Service',
                'image' => 'ondemand.png',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
            ],
            [
                'id' => 9,
                'service_type' => 'laundry_service',
                'sequence' => 8,
                'title' => 'Laundry',
                'image' => 'laundry.png',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
            ],
            [
                'id' => 10,
                'service_type' => 'rental_service',
                'sequence' => 9,
                'title' => 'Rental Service',
                'image' => 'rental.png',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                
            ],
            [
                'id' => 11,
                'sequence' => 10,
                'title' => 'Food',
                'service_type' => 'products_service',
                'image' => 'home_five.png',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                
            ],
            [
                'id' => 12,
                'sequence' => 11,
                'title' => 'Appointment',
                'service_type' => 'appointment_service',
                'image' => 'appointment.png',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                
            ],
            [
                'id' => 13,
                'sequence' => 12,
                'title' => 'P2P',
                'service_type' => 'p2p',
                'image' => 'P2P.png',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                
            ],
            [
                'id' => 14,
                'sequence' => 13,
                'title' => 'Car Rental',
                'service_type' => 'car_rental',
                'image' => 'template-ten.PNG',
                'description' => 'Lorem Ipsum is simply dummy text of the printing and typesetting industry.',
                
            ]
        );
        foreach ($types as $type) {
           Type::upsert($type, ['id', 'title','description', 'sequence','image']);
        }
    }
}
