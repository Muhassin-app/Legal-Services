<?php
namespace App\Http\Traits;
use Auth, Log, Config;
use Carbon\Carbon;
use Illuminate\Support\Facades\DB;

use App\Models\{AddonOption, AddonOptionTranslation, AddonSet, AddonSetTranslation, OrderVendorProduct, Banner, MobileBanner, Brand, BrandCategory, BrandTranslation, Cart, CartAddon, CartCoupon, CartProduct, CartProductPrescription, Category, CategoryHistory, CategoryTranslation, Celebrity, CsvProductImport, CsvVendorImport, LoyaltyCard, Order, OrderProductAddon, OrderProductPrescription, OrderProductRating, OrderProductRatingFile, OrderReturnRequest, OrderReturnRequestFile, OrderTax, OrderVendor, Payment, PaymentOption, Product, ProductAddon, ProductCategory, ProductCelebrity, ProductCrossSell, ProductImage, ProductInquiry, ProductRelated, ProductTranslation, ProductUpSell, ProductVariant, ProductVariantImage, ProductVariantSet, Promocode, PromoCodeDetail, PromocodeRestriction, ServiceArea, SlotDay, SocialMedia, Transaction, User, UserAddress, UserDevice, UserLoyaltyPoint, UserPermissions, UserRefferal, UserVendor, UserWishlist, Variant, VariantCategory, VariantOption, VariantOptionTranslation, VariantTranslation, Vendor, VendorCategory, VendorMedia, VendorOrderStatus, VendorSlot, VendorSlotDate, Wallet,CabBookingLayout,CabBookingLayoutCategory,CabBookingLayoutTranslation,ClientPreference,AppStyling,AppStylingOption,Tag,TagTranslation,ProductTag,Page,FaqTranslations,PageTranslation};
use Exception;
use Spatie\DbDumper\Databases\MySql;

trait SetDemoDBdata{
    // $res= $this->setDemoDbData('homeric'); // use like that

    // we can pass update demo DB data fron \connected dbb
    public function setDemoDbData($demoDbName)
    {
        $domain_array = ['grub','gusto','punnet','suel','voltaic','elixir','homeric','gokab','zest','ace'];
        if(in_array($demoDbName,$domain_array)){
            try {
                    DB::beginTransaction();
                    DB::statement("SET foreign_key_checks=0");
                    Cart::truncate();
                    Brand::truncate();
                    Order::truncate();
                    Banner::truncate();
                    MobileBanner::truncate();
                    Vendor::truncate();
                    SlotDay::truncate();
                    Payment::truncate();
                    Variant::truncate();
                    Product::truncate();
                    AddonSet::truncate();
                    Category::truncate();
                    OrderTax::truncate();
                    Promocode::truncate();
                    CartAddon::truncate();
                    Celebrity::truncate();
                    VendorSlot::truncate();
                    CartCoupon::truncate();
                    AddonOption::truncate();
                    LoyaltyCard::truncate();
                    ServiceArea::truncate();
                    VendorMedia::truncate();
                    CartProduct::truncate();
                    SocialMedia::truncate();
                    Transaction::truncate();
                    OrderVendor::truncate();
                    ProductAddon::truncate();
                    ProductImage::truncate();
                    ProductUpSell::truncate();
                    VariantOption::truncate();
                    BrandCategory::truncate();
                    VendorSlotDate::truncate();
                    VendorCategory::truncate();
                    ProductRelated::truncate();
                    ProductVariant::truncate();
                    ProductInquiry::truncate();
                    ProductCategory::truncate();
                    CsvVendorImport::truncate();
                    VariantCategory::truncate();
                    PromoCodeDetail::truncate();
                    CategoryHistory::truncate();
                    CsvProductImport::truncate();
                    BrandTranslation::truncate();
                    ProductCelebrity::truncate();
                    ProductCrossSell::truncate();
                    ProductVariantSet::truncate();
                    VendorOrderStatus::truncate();
                    OrderProductAddon::truncate();
                    OrderProductRating::truncate();
                    ProductTranslation::truncate();
                    VariantTranslation::truncate();
                    OrderVendorProduct::truncate();
                    OrderReturnRequest::truncate();
                    AddonSetTranslation::truncate();
                    CategoryTranslation::truncate();
                    ProductVariantImage::truncate();
                    PromocodeRestriction::truncate();
                    AddonOptionTranslation::truncate();
                    OrderProductRatingFile::truncate();
                    OrderReturnRequestFile::truncate();
                    CartProductPrescription::truncate();
                    CartProductPrescription::truncate();
                    VariantOptionTranslation::truncate();
                    OrderProductPrescription::truncate();
                    CabBookingLayout::truncate();
                    CabBookingLayoutCategory::truncate();
                    CabBookingLayoutTranslation::truncate();
                    AppStyling::truncate();
                    AppStylingOption::truncate();
                    Tag::truncate();
                    TagTranslation::truncate();
                    ProductTag::truncate();
                
                    $sql_file = $demoDbName.".sql";
                    //  DB::connectiunprepared(file_get_contents((asset('sql_files/'.$sql_file))));
                    DB::unprepared(file_get_contents((public_path('sql_files/'.$sql_file))));
                    DB::commit();
                    DB::statement("SET foreign_key_checks=1");
                
                    ClientPreference::where('id', 1)->update(['is_hyperlocal' => 0]);
            }
                catch (\PDOException $e) {
                DB::rollBack();
               Log::info("import dummy data: !{$e->getMessage()}");
                
            }
            return 1;
        }
        return 0;
    }
    
}
