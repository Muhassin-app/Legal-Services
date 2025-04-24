<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;

class TollPass extends Seeder
{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run()
    {
        \DB::table('toll_pass_origin')->delete();
 
        $toll_pass = array(
            array(
                'id' => 1,
                'toll_pass' => 'TOLL_PASS_UNSPECIFIED',
                'desc' => 'Not used. If this value is used, then the request fails.'
            ),
            array(
                'id' => 2,
                'toll_pass' => 'IN_FASTAG',
                'desc' => 'India'
            ),
            array(
                'id' => 3,
                'toll_pass' => 'IN_LOCAL_HP_PLATE_EXEMPT',
                'desc' => 'India, HP state plate exemption'
            ),
            array(
                'id' => 4,
                'toll_pass' => 'US_WA_GOOD_TO_GO',
                'desc' => 'WA, USA.'
            ),
            array(
                'id' => 5,
                'toll_pass' => 'AU_ETOLL_TAG',
                'desc' => 'One of many Sydney toll pass providers'
            ),
            array(
                'id' => 6,
                'toll_pass' => 'CA_US_AKWASASNE_SEAWAY_CORPORATE_CARD',
                'desc' => 'Canada to United States border crossing'
            ),
            array(
                'id' => 7,
                'toll_pass' => 'ID_E_TOLL',
                'desc' => 'Indonesia. E-card provided by multiple banks used to pay for tolls'
            ),
            array(
                'id' => 8,
                'toll_pass' => 'MX_TAG_IAVE',
                'desc' => 'Mexico toll pass'
            ),
        ); 
        \DB::table('toll_pass_origin')->insert($toll_pass);
    }
}
