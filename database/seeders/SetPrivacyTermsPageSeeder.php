<?php

namespace Database\Seeders;
use DB;
use App\Models\Page;
use Illuminate\Support\Str;
use App\Models\ClientLanguage;
use Illuminate\Database\Seeder;
use App\Models\PageTranslation;

class SetPrivacyTermsPageSeeder extends Seeder{
    /**
     * Run the database seeds.
     *
     * @return void
     */
    public function run(){
       
        $all_pages = Page::whereIn('slug', ['privacy-policy','terms-conditions','vendor-registration'])->get();

        

        foreach ($all_pages as $key => $page) {
            switch($page->slug){
                case 'privacy-policy':
                $type_of_form =  4;
                break;
                case 'terms-conditions':
                $type_of_form =  5;
                break;
                case 'vendor-registration':
                $type_of_form =  1;
                break;
                default:
                $type_of_form =  0;
            }
            $page_detail = PageTranslation::where('page_id' , $page->id)->update(['type_of_form' => $type_of_form]);
           
        }
    }
}
