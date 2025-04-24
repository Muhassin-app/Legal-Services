<?php

namespace App\Http\Controllers\Front;

use App\Http\Controllers\AhoyController;
use App\Http\Controllers\D4BDunzoController;
use App\Http\Controllers\DunzoController;
use DB;
use Log;
use Auth;
use Crypt;
use Redirect;
use Carbon\{
    Carbon,
    CarbonPeriod
};
use Omnipay\Omnipay;
use App\Models\Cart;
use App\Models\User;
use App\Models\Page;
use App\Models\Order;
use App\Models\RescheduleOrder;
use GuzzleHttp\Client;
use App\Models\Payment;
use App\Models\Vendor;
use App\Models\Product;
use App\Models\OrderTax;
use App\Models\CartAddon;
use App\Models\UserVendor;
use App\Models\UserDevice;
use App\Models\CartCoupon;
use App\Models\OrderVendor;
use App\Models\LoyaltyCard;
use App\Models\UserAddress;
use App\Models\CartProduct;
use App\Models\Client as CP;
use App\Models\OrderProduct;
use App\Models\EmailTemplate;
use App\Models\ClientCurrency;
use App\Models\CaregoryKycDoc;
use App\Models\VendorOrderStatus;
use App\Models\OrderProductAddon;
use App\Models\NotificationTemplate;
use App\Models\CartProductPrescription;
use App\Models\OrderProductPrescription;
use App\Models\SubscriptionInvoicesUser;
use App\Models\UserRegistrationDocuments;
use App\Models\DriverRegistrationDocument;
use App\Models\LawyerDocument;
use App\Models\JitsiMeeting;
use Illuminate\Support\Facades\Session as FacadesSession;

use App\Models\{
    VendorOrderDispatcherStatus,
    VerificationOption,
    DispatcherStatusOption,
    ReturnReason,
    OrderDeliveryStatusIcon,
    UserGiftCard,
    OrderFiles,
    SubscriptionInvoicesVendor
};
use Illuminate\Http\Request;
use App\Models\LuxuryOption;
use App\Models\PaymentOption;
use App\Models\CartDeliveryFee;
use App\Models\ClientPreference;
use App\Http\Traits\{
    ApiResponser,
    CartManager,
    OrderBlockchain,
    SquareInventoryManager,
    VendorTrait,
    OrderTrait,
    MargTrait,
    CartManagerV2
};
use App\Models\AddonOption;
use App\Models\{
    OrderLongTermServices,
    OrderLongTermServicesAddon,
    OrderLongTermServiceSchedule,
    Bid,
    VendorMargConfig,
    CartBookingOption,
    CartRentalProtection,
    ClientDocument,
    OrderNotificationsLogs,
    ProductAvailability,
    ClientPreferenceAdditional,
    OrderVendorProduct
};
use App\Models\ProductVariantSet;
use GuzzleHttp\Client as GCLIENT;
use App\Models\AutoRejectOrderCron;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Validator;
use Illuminate\Foundation\Bus\DispatchesJobs;
use App\Http\Controllers\Front\FrontController;
use App\Http\Controllers\Front\LalaMovesController;
use App\Http\Controllers\ShiprocketController;
use Illuminate\Support\Facades\Auth as FacadesAuth;
use Illuminate\Support\Facades\Http;

class OrderController extends FrontController
{
    use ApiResponser, CartManager, SquareInventoryManager, VendorTrait, OrderTrait, OrderBlockchain, CartManagerV2;

    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */

    public function lenderOrders(Request $request, $domain = '')
    {
        $iconsArray = [];
        $user = Auth::user();
        if (empty($user->timezone)) {
            $client_timezone = DB::table('clients')->first('timezone');
            $user->timezone = $client_timezone->timezone ?? $user->timezone;
        }
        $currency_id = Session::get('customerCurrency');

        $langId = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($langId);
        $additionalPreference = getAdditionalPreference(['is_long_term_service', 'is_token_currency_enable', 'token_currency', 'is_postpay_enable', 'is_order_edit_enable', 'order_edit_before_hours', 'is_service_product_price_from_dispatch', 'is_service_price_selection']);
        $getOnDemandPricingRule = getOnDemandPricingRule(Session::get('vendorType'), (@Session::get('onDemandPricingSelected') ?? ''), $additionalPreference);
        $is_service_product_price_from_dispatch_forOnDemand = $getOnDemandPricingRule['is_price_from_freelancer'] ?? 0;


        $dispatcher_icons = OrderDeliveryStatusIcon::select('image', 'image_url')->get();
        foreach ($dispatcher_icons as $icon) {
            $imgUrl = asset($icon->image);
            if (!empty($icon->image_url['proxy_url'])) {
                $imgUrl = $icon->image_url['proxy_url'] . '40/40' . $icon->image_url['image_path'];
            }

            $iconsArray[] = $imgUrl;
        }
        $vendorUser =  UserVendor::select('vendor_id')->where('user_id', $user->id)->first();
        $allOrders = $upcomingOrders = $ongoingOrders = [];
        if ($vendorUser) {
            //Past Orders Start Query
            $allOrders = Order::with([
                'vendors',
                'vendors.vendor',
                'vendors.dineInTable.translations' => function ($qry) use ($langId) {
                    $qry->where('language_id', $langId);
                },
                'vendors.dineInTable.category',
                'vendors.products',
                'vendors.products.product',
                'vendors.products.media.image',
                'vendors.products.pvariant.media.pimage.image',
                'vendors.products.Routes',
                'vendors.products.order_product_status',
                'products.productRating',
                'user',
                'address',
                'driver_rating',
                'reports'
            ]);
            $allOrders = $allOrders->with('vendors.exchanged_of_order.orderDetail', 'vendors.exchanged_to_order.orderDetail');

            $allOrders = $allOrders->whereHas('vendors', function ($q) use ($vendorUser) {
                $q->whereHas('products');
                //lender
                $q->where('vendor_id',  $vendorUser->vendor_id);
            });
            $allOrders = $allOrders->where(function ($q1) {
                $q1->where('payment_status', 1)
                    ->whereNotIn('payment_option_id', [
                        1
                    ]);
                $q1->orWhere(function ($q2) {
                    $q2->where('payment_option_id', 1);
                });
            });
            $allOrders->where('orders.is_long_term', 0);
            $allOrders = $allOrders->orderBy('orders.id', 'DESC')->select('*', 'id as total_discount_calculate')->paginate(10);

            foreach ($allOrders as $order) {
                $is_order_days_for_return = 0;
                $replaceable = 0;
                foreach ($order->vendors as $vendor) {
                    $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)->where('vendor_id', $vendor->vendor_id)->orderBy('id', 'DESC')->first();
                    $vendor->order_status = $vendor_order_status ? strtolower($vendor_order_status->OrderStatusOption->title) : '';
                    foreach ($vendor->products as $product) {
                        $product = $this->gettimeSlotName($product);
                        // dd($product->product->return_days);
                        // $vendor->is_order_days_for_return = 1;
                        if ((@$product->product->return_days && $this->checkOrderDaysForReturn($vendor, $product->product->return_days)) && $is_order_days_for_return == 0) {
                            $this->checkOrderDaysForReturn($vendor, $product->product->return_days);
                            $vendor->is_order_days_for_return = 1;
                        }

                        if (isset($product->pvariant) && isset($product->pvariant->media) && $product->pvariant->media->isNotEmpty()) {
                            $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'] . '74/100' . $product->pvariant->media->first()->pimage->image->path['image_path'];
                        } elseif ($product->media->isNotEmpty()) {
                            $product->image_url = $product->media->first()->image->path['image_fit'] . '74/100' . $product->media->first()->image->path['image_path'];
                        } else {
                            $product->image_url = ($product->image) ? $product->image['image_fit'] . '74/100' . $product->image['image_path'] : '';
                        }
                    }
                    if ($vendor->dineInTable) {
                        $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                        $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                        $vendor->dineInTableCategory = $vendor->dineInTable->category ? $vendor->dineInTable->category->title : '';
                    }
                }
            }


            //Upcoming Lender Orders Start Query
            $upcomingOrders = Order::with([
                'vendors',
                'vendors.vendor',
                'vendors.dineInTable.translations' => function ($qry) use ($langId) {
                    $qry->where('language_id', $langId);
                },
                'vendors.dineInTable.category',
                'vendors.products',
                'vendors.products.product',
                'vendors.products.media.image',
                'vendors.products.pvariant.media.pimage.image',
                'vendors.products.Routes',
                'vendors.products.order_product_status',
                'products.productRating',
                'user',
                'address',
                'driver_rating',
                'reports'
            ]);
            $upcomingOrders = $upcomingOrders->with('vendors.exchanged_of_order.orderDetail', 'vendors.exchanged_to_order.orderDetail');
            $upcomingOrders = $upcomingOrders->whereHas('vendors', function ($q) use ($vendorUser) {
                $q->whereHas('products');
                //lender
                $q->where('vendor_id',  $vendorUser->vendor_id);
                // upcoming
                $q->whereIn('order_status_option_id', [1, 2]);
            });
            $upcomingOrders = $upcomingOrders->where(function ($q1) {
                $q1->where('payment_status', 1)
                    ->whereNotIn('payment_option_id', [
                        1
                    ]);
                $q1->orWhere(function ($q2) {
                    $q2->where('payment_option_id', 1);
                });
            });

            $upcomingOrders->where('orders.is_long_term', 0);

            $upcomingOrders = $upcomingOrders->orderBy('orders.id', 'DESC')->select('*', 'id as total_discount_calculate')->paginate(10);

            foreach ($upcomingOrders as $order) {
                $is_order_days_for_return = 0;
                $replaceable = 0;
                foreach ($order->vendors as $vendor) {
                    $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)->where('vendor_id', $vendor->vendor_id)->orderBy('id', 'DESC')->first();
                    $vendor->order_status = $vendor_order_status ? strtolower($vendor_order_status->OrderStatusOption->title) : '';
                    foreach ($vendor->products as $product) {
                        $product = $this->gettimeSlotName($product);
                        // dd($product->product->return_days);
                        // $vendor->is_order_days_for_return = 1;
                        if ((@$product->product->return_days && $this->checkOrderDaysForReturn($vendor, $product->product->return_days)) && $is_order_days_for_return == 0) {
                            $this->checkOrderDaysForReturn($vendor, $product->product->return_days);
                            $vendor->is_order_days_for_return = 1;
                        }

                        if (isset($product->pvariant) && isset($product->pvariant->media) && $product->pvariant->media->isNotEmpty()) {
                            $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'] . '74/100' . $product->pvariant->media->first()->pimage->image->path['image_path'];
                        } elseif ($product->media->isNotEmpty()) {
                            $product->image_url = $product->media->first()->image->path['image_fit'] . '74/100' . $product->media->first()->image->path['image_path'];
                        } else {
                            $product->image_url = ($product->image) ? $product->image['image_fit'] . '74/100' . $product->image['image_path'] : '';
                        }
                    }
                    if ($vendor->dineInTable) {
                        $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                        $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                        $vendor->dineInTableCategory = $vendor->dineInTable->category ? $vendor->dineInTable->category->title : '';
                    }
                }
            }


            //Ongoing Lender Orders Start Query
            $ongoingOrders = Order::with([
                'vendors',
                'vendors.vendor',
                'vendors.dineInTable.translations' => function ($qry) use ($langId) {
                    $qry->where('language_id', $langId);
                },
                'vendors.dineInTable.category',
                'vendors.products',
                'vendors.products.product',
                'vendors.products.media.image',
                'vendors.products.pvariant.media.pimage.image',
                'vendors.products.Routes',
                'vendors.products.order_product_status',
                'products.productRating',
                'user',
                'address',
                'driver_rating',
                'reports'
            ]);
            $ongoingOrders = $ongoingOrders->with('vendors.exchanged_of_order.orderDetail', 'vendors.exchanged_to_order.orderDetail');
            $ongoingOrders = $ongoingOrders->whereHas('vendors', function ($q) use ($vendorUser) {
                $q->whereHas('products');
                //lender
                $q->where('vendor_id',  $vendorUser->vendor_id);
                //ongoing
                $q->whereIn('order_status_option_id', [4]);
            });
            $ongoingOrders = $ongoingOrders->where(function ($q1) {
                $q1->where('payment_status', 1)
                    ->whereNotIn('payment_option_id', [
                        1
                    ]);
                $q1->orWhere(function ($q2) {
                    $q2->where('payment_option_id', 1);
                });
            });

            $ongoingOrders->where('orders.is_long_term', 0);

            $ongoingOrders = $ongoingOrders->orderBy('orders.id', 'DESC')
                ->select('*', 'id as total_discount_calculate')
                ->paginate(10);

            foreach ($ongoingOrders as $order) {
                $is_order_days_for_return = 0;
                $replaceable = 0;
                foreach ($order->vendors as $vendor) {
                    $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)
                        ->where('vendor_id', $vendor->vendor_id)
                        ->orderBy('id', 'DESC')
                        ->first();
                    $vendor->order_status = $vendor_order_status ? strtolower($vendor_order_status->OrderStatusOption->title) : '';
                    foreach ($vendor->products as $product) {
                        $product = $this->gettimeSlotName($product);
                        // dd($product->product->return_days);
                        // $vendor->is_order_days_for_return = 1;
                        if ((@$product->product->return_days && $this->checkOrderDaysForReturn($vendor, $product->product->return_days)) && $is_order_days_for_return == 0) {
                            $this->checkOrderDaysForReturn($vendor, $product->product->return_days);
                            $vendor->is_order_days_for_return = 1;
                        }

                        if (isset($product->pvariant) && isset($product->pvariant->media) && $product->pvariant->media->isNotEmpty()) {
                            $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'] . '74/100' . $product->pvariant->media->first()->pimage->image->path['image_path'];
                        } elseif ($product->media->isNotEmpty()) {
                            $product->image_url = $product->media->first()->image->path['image_fit'] . '74/100' . $product->media->first()->image->path['image_path'];
                        } else {
                            $product->image_url = ($product->image) ? $product->image['image_fit'] . '74/100' . $product->image['image_path'] : '';
                        }
                    }
                    if ($vendor->dineInTable) {
                        $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                        $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                        $vendor->dineInTableCategory = $vendor->dineInTable->category ? $vendor->dineInTable->category->title : '';
                    }
                }
            }
        }


        $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();

        if (empty($clientCurrency)) {
            $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
        }

        $client_preferences = ClientPreference::select('*')->where('id', '>', 0)->first();
        if (!empty($client_preferences)) {
            $client_preferences->is_postpay_enable =  $additionalPreference['is_postpay_enable'];
            $client_preferences->is_order_edit_enable = $additionalPreference['is_order_edit_enable'];
            $client_preferences->order_edit_before_hours = $additionalPreference['order_edit_before_hours'];
            $client_preferences->editlimit_datetime = Carbon::now()->addHours($client_preferences->order_edit_before_hours)->toDateTimeString();
        }
        $payments = PaymentOption::where('credentials', '!=', '')->where('status', 1)->count();

        $cancellation_reason = ReturnReason::where([
            'status' => 'Active',
            'type' => 3
        ])->get();


        $longTermOrder = [];
        /**
         * get user long term orders
         */
        $show_long_term = 0;
        if ($additionalPreference['is_long_term_service'] == 1) {
            $show_long_term = 1;

            $longTermOrder = $this->getUserLongTermService($user, $langId, $currency_id);
        }


        $langId = Session::get('customerLanguage');
        $fixedFee = $this->fixedFee($langId);
        $pendingOrder = [];
        if ($is_service_product_price_from_dispatch_forOnDemand == 1) {
            $pendingOrder = $this->pendingOrder($request, $user, $langId, $iconsArray);
        }

        return view('frontend.account.lender-orders')->with([
            'payments' => $payments,
            'navCategories' => $navCategories,
            'cancellation_reason' => $cancellation_reason,
            'allOrders' => $allOrders,
            'ongoingOrders' => $ongoingOrders,
            'upcomingOrders' => $upcomingOrders,
            'clientCurrency' => $clientCurrency,
            'clientPreference' => $client_preferences,
            'fixedFee' => $fixedFee,
            'longTermOrder' => $longTermOrder,
            'additionalPreference' => $additionalPreference,
            'pendingOrder' => $pendingOrder,
            'show_long_term' => $show_long_term,
            'is_service_product_price_from_dispatch_forOnDemand' => $is_service_product_price_from_dispatch_forOnDemand
        ]);
    }

    public function borrowerOrders(Request $request, $domain = '')
    {
        $iconsArray = [];
        $user = Auth::user();
        if (empty($user->timezone)) {
            $client_timezone = DB::table('clients')->first('timezone');
            $user->timezone = $client_timezone->timezone ?? $user->timezone;
        }
        $currency_id = Session::get('customerCurrency');

        $langId = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($langId);
        $additionalPreference = getAdditionalPreference(['is_long_term_service', 'is_token_currency_enable', 'token_currency', 'is_postpay_enable', 'is_order_edit_enable', 'order_edit_before_hours', 'is_service_product_price_from_dispatch', 'is_service_price_selection']);
        $getOnDemandPricingRule = getOnDemandPricingRule(Session::get('vendorType'), (@Session::get('onDemandPricingSelected') ?? ''), $additionalPreference);
        $is_service_product_price_from_dispatch_forOnDemand = $getOnDemandPricingRule['is_price_from_freelancer'] ?? 0;

        $dispatcher_icons = OrderDeliveryStatusIcon::select('image', 'image_url')->get();
        foreach ($dispatcher_icons as $icon) {
            $imgUrl = asset($icon->image);
            if (!empty($icon->image_url['proxy_url'])) {
                $imgUrl = $icon->image_url['proxy_url'] . '40/40' . $icon->image_url['image_path'];
            }
            $iconsArray[] = $imgUrl;
        }

        $vendorUser =  UserVendor::select('vendor_id')->where('user_id', $user->id)->first();
        $allOrders = $upcomingOrders = $ongoingOrders = [];
        if ($vendorUser) {
            //All Borrower Orders Start Query
            $allOrders = Order::with([
                'vendors',
                'vendors.vendor',
                'vendors.dineInTable.translations' => function ($qry) use ($langId) {
                    $qry->where('language_id', $langId);
                },
                'vendors.dineInTable.category',
                'vendors.products',
                'vendors.products.product',
                'vendors.products.media.image',
                'vendors.products.pvariant.media.pimage.image',
                'vendors.products.Routes',
                'vendors.products.order_product_status',
                'products.productRating',
                'user',
                'address',
                'driver_rating',
                'reports'
            ]);
            $allOrders = $allOrders->with('vendors.exchanged_of_order.orderDetail', 'vendors.exchanged_to_order.orderDetail');
            $allOrders = $allOrders->whereHas('vendors', function ($q) use ($user) {
                $q->whereHas('products');
                //lender
                $q->where('user_id', $user->id);
            });
            $allOrders = $allOrders->where(function ($q1) {
                $q1->where('payment_status', 1)
                    ->whereNotIn('payment_option_id', [
                        1
                    ]);
                $q1->orWhere(function ($q2) {
                    $q2->where('payment_option_id', 1);
                });
            })->where('orders.user_id', $user->id);
            $allOrders->where('orders.is_long_term', 0);
            $allOrders = $allOrders->orderBy('orders.id', 'DESC')->select('*', 'id as total_discount_calculate')->paginate(10);

            foreach ($allOrders as $order) {
                $is_order_days_for_return = 0;
                $replaceable = 0;
                foreach ($order->vendors as $vendor) {
                    $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)
                        ->where('vendor_id', $vendor->vendor_id)
                        ->orderBy('id', 'DESC')
                        ->first();
                    $vendor->order_status = $vendor_order_status ? strtolower($vendor_order_status->OrderStatusOption->title) : '';
                    foreach ($vendor->products as $product) {
                        $product = $this->gettimeSlotName($product);
                        // dd($product->product->return_days);
                        // $vendor->is_order_days_for_return = 1;
                        if ((@$product->product->return_days && $this->checkOrderDaysForReturn($vendor, $product->product->return_days)) && $is_order_days_for_return == 0) {
                            $this->checkOrderDaysForReturn($vendor, $product->product->return_days);
                            $vendor->is_order_days_for_return = 1;
                        }

                        if (isset($product->pvariant) && isset($product->pvariant->media) && $product->pvariant->media->isNotEmpty()) {
                            $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'] . '74/100' . $product->pvariant->media->first()->pimage->image->path['image_path'];
                        } elseif ($product->media->isNotEmpty()) {
                            $product->image_url = $product->media->first()->image->path['image_fit'] . '74/100' . $product->media->first()->image->path['image_path'];
                        } else {
                            $product->image_url = ($product->image) ? $product->image['image_fit'] . '74/100' . $product->image['image_path'] : '';
                        }
                    }
                    if ($vendor->dineInTable) {
                        $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                        $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                        $vendor->dineInTableCategory = $vendor->dineInTable->category ? $vendor->dineInTable->category->title : '';
                    }
                }
            }


            //Upcoming Borrower Orders Start Query
            $upcomingOrders = Order::with([
                'vendors',
                'vendors.vendor',
                'vendors.dineInTable.translations' => function ($qry) use ($langId) {
                    $qry->where('language_id', $langId);
                },
                'vendors.dineInTable.category',
                'vendors.products',
                'vendors.products.product',
                'vendors.products.media.image',
                'vendors.products.pvariant.media.pimage.image',
                'vendors.products.Routes',
                'vendors.products.order_product_status',
                'products.productRating',
                'user',
                'address',
                'driver_rating',
                'reports'
            ]);
            $upcomingOrders = $upcomingOrders->with('vendors.exchanged_of_order.orderDetail', 'vendors.exchanged_to_order.orderDetail');
            $upcomingOrders = $upcomingOrders->whereHas('vendors', function ($q) use ($user) {
                $q->whereHas('products');
                //borrower
                $q->where('user_id', $user->id);
                // upcoming
                $q->whereIn('order_status_option_id', [1, 2]);
            });
            $upcomingOrders = $upcomingOrders->where(function ($q1) {
                $q1->where('payment_status', 1)
                    ->whereNotIn('payment_option_id', [
                        1
                    ]);
                $q1->orWhere(function ($q2) {
                    $q2->where('payment_option_id', 1);
                });
            })->where('orders.user_id', $user->id);

            $upcomingOrders->where('orders.is_long_term', 0);

            $upcomingOrders = $upcomingOrders->orderBy('orders.id', 'DESC')->select('*', 'id as total_discount_calculate')->paginate(10);

            foreach ($upcomingOrders as $order) {
                $is_order_days_for_return = 0;
                $replaceable = 0;
                foreach ($order->vendors as $vendor) {
                    $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)
                        ->where('vendor_id', $vendor->vendor_id)
                        ->orderBy('id', 'DESC')
                        ->first();
                    $vendor->order_status = $vendor_order_status ? strtolower($vendor_order_status->OrderStatusOption->title) : '';
                    foreach ($vendor->products as $product) {
                        $product = $this->gettimeSlotName($product);
                        // dd($product->product->return_days);
                        // $vendor->is_order_days_for_return = 1;
                        if ((@$product->product->return_days && $this->checkOrderDaysForReturn($vendor, $product->product->return_days)) && $is_order_days_for_return == 0) {
                            $this->checkOrderDaysForReturn($vendor, $product->product->return_days);
                            $vendor->is_order_days_for_return = 1;
                        }

                        if (isset($product->pvariant) && isset($product->pvariant->media) && $product->pvariant->media->isNotEmpty()) {
                            $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'] . '74/100' . $product->pvariant->media->first()->pimage->image->path['image_path'];
                        } elseif ($product->media->isNotEmpty()) {
                            $product->image_url = $product->media->first()->image->path['image_fit'] . '74/100' . $product->media->first()->image->path['image_path'];
                        } else {
                            $product->image_url = ($product->image) ? $product->image['image_fit'] . '74/100' . $product->image['image_path'] : '';
                        }
                    }
                    if ($vendor->dineInTable) {
                        $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                        $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                        $vendor->dineInTableCategory = $vendor->dineInTable->category ? $vendor->dineInTable->category->title : '';
                    }
                }
            }


            //Ongoing Borrower Orders Start Query
            $ongoingOrders = Order::with([
                'vendors',
                'vendors.vendor',
                'vendors.dineInTable.translations' => function ($qry) use ($langId) {
                    $qry->where('language_id', $langId);
                },
                'vendors.dineInTable.category',
                'vendors.products',
                'vendors.products.product',
                'vendors.products.media.image',
                'vendors.products.pvariant.media.pimage.image',
                'vendors.products.Routes',
                'vendors.products.order_product_status',
                'products.productRating',
                'user',
                'address',
                'driver_rating',
                'reports'
            ]);
            $ongoingOrders = $ongoingOrders->with('vendors.exchanged_of_order.orderDetail', 'vendors.exchanged_to_order.orderDetail');
            $ongoingOrders = $ongoingOrders->whereHas('vendors', function ($q) use ($user) {
                $q->whereHas('products');
                //borrower
                $q->where('user_id', $user->id);
                //ongoing
                $q->whereIn('order_status_option_id', [4]);
            });
            $ongoingOrders = $ongoingOrders->where(function ($q1) {
                $q1->where('payment_status', 1)
                    ->whereNotIn('payment_option_id', [
                        1
                    ]);
                $q1->orWhere(function ($q2) {
                    $q2->where('payment_option_id', 1);
                });
            })->where('orders.user_id', $user->id);

            $ongoingOrders->where('orders.is_long_term', 0);

            $ongoingOrders = $ongoingOrders->orderBy('orders.id', 'DESC')->select('*', 'id as total_discount_calculate')->paginate(10);

            foreach ($ongoingOrders as $order) {
                $is_order_days_for_return = 0;
                $replaceable = 0;
                foreach ($order->vendors as $vendor) {
                    $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)
                        ->where('vendor_id', $vendor->vendor_id)
                        ->orderBy('id', 'DESC')
                        ->first();
                    $vendor->order_status = $vendor_order_status ? strtolower($vendor_order_status->OrderStatusOption->title) : '';
                    foreach ($vendor->products as $product) {
                        $product = $this->gettimeSlotName($product);
                        // dd($product->product->return_days);
                        // $vendor->is_order_days_for_return = 1;
                        if ((@$product->product->return_days && $this->checkOrderDaysForReturn($vendor, $product->product->return_days)) && $is_order_days_for_return == 0) {
                            $this->checkOrderDaysForReturn($vendor, $product->product->return_days);
                            $vendor->is_order_days_for_return = 1;
                        }

                        if (isset($product->pvariant) && isset($product->pvariant->media) && $product->pvariant->media->isNotEmpty()) {
                            $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'] . '74/100' . $product->pvariant->media->first()->pimage->image->path['image_path'];
                        } elseif ($product->media->isNotEmpty()) {
                            $product->image_url = $product->media->first()->image->path['image_fit'] . '74/100' . $product->media->first()->image->path['image_path'];
                        } else {
                            $product->image_url = ($product->image) ? $product->image['image_fit'] . '74/100' . $product->image['image_path'] : '';
                        }
                    }
                    if ($vendor->dineInTable) {
                        $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                        $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                        $vendor->dineInTableCategory = $vendor->dineInTable->category ? $vendor->dineInTable->category->title : '';
                    }
                }
            }
        }


        $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();

        if (empty($clientCurrency)) {
            $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
        }

        $client_preferences = ClientPreference::select('*')->where('id', '>', 0)->first();
        if (!empty($client_preferences)) {
            $client_preferences->is_postpay_enable =  $additionalPreference['is_postpay_enable'];
            $client_preferences->is_order_edit_enable = $additionalPreference['is_order_edit_enable'];
            $client_preferences->order_edit_before_hours = $additionalPreference['order_edit_before_hours'];
            $client_preferences->editlimit_datetime = Carbon::now()->addHours($client_preferences->order_edit_before_hours)->toDateTimeString();
        }
        $payments = PaymentOption::where('credentials', '!=', '')->where('status', 1)->count();

        $cancellation_reason = ReturnReason::where([
            'status' => 'Active',
            'type' => 3
        ])->get();


        $longTermOrder = [];
        /**
         * get user long term orders
         */
        $show_long_term = 0;
        if ($additionalPreference['is_long_term_service'] == 1) {
            $show_long_term = 1;
            $longTermOrder = $this->getUserLongTermService($user, $langId, $currency_id);
        }

        $langId = Session::get('customerLanguage');
        $fixedFee = $this->fixedFee($langId);
        $pendingOrder = [];
        if ($is_service_product_price_from_dispatch_forOnDemand == 1) {
            $pendingOrder = $this->pendingOrder($request, $user, $langId, $iconsArray);
        }
        return view('frontend.account.borrower-orders')->with([
            'payments' => $payments,
            'navCategories' => $navCategories,
            'cancellation_reason' => $cancellation_reason,
            'allOrders' => $allOrders,
            'ongoingOrders' => $ongoingOrders,
            'upcomingOrders' => $upcomingOrders,
            'clientCurrency' => $clientCurrency,
            'clientPreference' => $client_preferences,
            'fixedFee' => $fixedFee,
            'longTermOrder' => $longTermOrder,
            'additionalPreference' => $additionalPreference,
            'pendingOrder' => $pendingOrder,
            'show_long_term' => $show_long_term,
            'is_service_product_price_from_dispatch_forOnDemand' => $is_service_product_price_from_dispatch_forOnDemand
        ]);
    }

    public function orderVenderStatusUpdate(Request $request)
    {
        try {
            $orderVendor = OrderVendor::find($request->order_vendor_id);
            if (!$orderVendor) {
                return $this->errorResponse(__('Order vendor not found.'), 404);
            }

            $orderVendor->order_status_option_id = $request->order_vendor_status;
            $orderVendor->save();
            $response = [
                'success' => true,
                'message' => 'Status updated successfully'
            ];
            return response()->json($response);
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return $this->errorResponse(__('Something went wrong, Please try again.'), 400);
        }
    }


    public function orders(Request $request, $domain = '')
    {
        $iconsArray = [];
        $user = Auth::user();
        if (empty($user->timezone)) {
            $client_timezone = DB::table('clients')->first('timezone');
            $user->timezone = $client_timezone->timezone ?? $user->timezone;
        }
        $currency_id = Session::get('customerCurrency');

        $langId = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($langId);
        $additionalPreference = getAdditionalPreference(['is_long_term_service', 'is_token_currency_enable', 'token_currency', 'is_postpay_enable', 'is_order_edit_enable', 'order_edit_before_hours', 'is_service_product_price_from_dispatch', 'is_service_price_selection']);
        $getOnDemandPricingRule = getOnDemandPricingRule(Session::get('vendorType'), (@Session::get('onDemandPricingSelected') ?? ''), $additionalPreference);
        $is_service_product_price_from_dispatch_forOnDemand = $getOnDemandPricingRule['is_price_from_freelancer'] ?? 0;


        $dispatcher_icons = OrderDeliveryStatusIcon::select('image', 'image_url')->get();
        foreach ($dispatcher_icons as $icon) {
            $imgUrl = asset($icon->image);
            if (!empty($icon->image_url['proxy_url'])) {
                $imgUrl = $icon->image_url['proxy_url'] . '40/40' . $icon->image_url['image_path'];
            }

            $iconsArray[] = $imgUrl;
        }


        //Past Orders Start Query
        $pastOrders = Order::with([
            'vendors' => function ($q) {

                $q->whereHas('products', function ($Pq) use ($is_service_product_price_from_dispatch_forOnDemand) {
                    if ($is_service_product_price_from_dispatch_forOnDemand == 1) {
                        $Pq->where('dispatcher_status_option_id', 5);
                    }
                });

                if ($is_service_product_price_from_dispatch_forOnDemand == 0) {
                    $q->whereIn('order_status_option_id', [
                        6,
                        9
                    ]);
                }
            },
            'vendors.vendor',
            'vendors.dineInTable.translations' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendors.orderDocument',
            'vendors.dineInTable.category',
            'vendors.products',
            'vendors.products.product',
            'vendors.products.media.image',
            'vendors.products.pvariant.media.pimage.image',
            'vendors.products.Routes',
            'vendors.products.order_product_status',
            'products.productRating',
            'user',
            'address',
            'driver_rating',
            'reports'
        ]);
        $pastOrders = $pastOrders->with('vendors.exchanged_of_order.orderDetail', 'vendors.exchanged_to_order.orderDetail');

        if ($is_service_product_price_from_dispatch_forOnDemand == 1) {
            $pastOrders->whereHas('vendors.products', function ($q) {
                $q->where('dispatcher_status_option_id', 5); //1=pending,5= complete,6 reject
            });
        } else {
            $pastOrders = $pastOrders->whereHas('vendors', function ($q) {
                $q->whereIn('order_status_option_id', [
                    6,
                ]);
            });
        }
        $pastOrders = $pastOrders->where(function ($q1) {
            $q1->where('payment_status', 1)
                ->whereNotIn('payment_option_id', [
                    1
                ]);
            $q1->orWhere(function ($q2) {
                $q2->where('payment_option_id', 1);
            });
        })->where('orders.user_id', $user->id);
        $pastOrders->where('orders.is_long_term', 0);

        $pastOrders = $pastOrders->orderBy('orders.id', 'DESC')
            ->select('*', 'id as total_discount_calculate')
            ->paginate(10);

        //End Past Orders Query


        //Active Orders Start Query
        $activeOrders = Order::with([
            'vendors' => function ($q) use ($is_service_product_price_from_dispatch_forOnDemand) {
                $q->whereHas('products', function ($Pq) use ($is_service_product_price_from_dispatch_forOnDemand) {
                    if ($is_service_product_price_from_dispatch_forOnDemand == 1) {
                        // $Pq->where('dispatcher_status_option_id',2);
                        $Pq->whereNotIn('dispatcher_status_option_id', [1, 5, 6]);
                    }
                    $Pq->with(['products.media.image', 'products.pvariant.media.pimage.image', 'products.Routes', 'products.order_product_status']);
                });

                $q->with('exchanged_of_order.orderDetail');
                $q->whereNotIn('order_status_option_id', [
                    3,
                    6,
                ]);
            },
            'vendors.dineInTable.translations' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendors.dineInTable.category',
            'user',
            'address',
            'reqCancelOrder'
        ]);


        $activeOrders->where(function ($q1) {
            $q1->where('payment_status', 1)
                ->whereNotIn('payment_option_id', [
                    1,
                    38
                ]);
            $q1->orWhere(function ($q2) {
                $q2->whereIn('payment_option_id', [
                    1,
                    38
                ])
                    ->orWhere(function ($q3) {
                        $q3->where('is_postpay', 1)
                            -> // 1 for order is post paid
                            whereNotIn('payment_option_id', [
                                1,
                                38
                            ]);
                    });
            });
        })
        ->where('orders.user_id', $user->id);
        
        $activeOrders->where('orders.is_long_term', 0);

        if ($is_service_product_price_from_dispatch_forOnDemand == 1) {
            $activeOrders =  $activeOrders->whereHas('vendors.products', function ($q) {
                $q->whereNotIn('dispatcher_status_option_id', [1, 5, 6]); //1=pending,5= complete,6 reject
            })->whereHas('vendors.products');
        } else {
            $activeOrders =   $activeOrders->whereHas('vendors', function ($q) {
                $q->whereNotIn('order_status_option_id', [
                    3,
                    6,
                ]);
            });
        }
        $activeOrders = $activeOrders->orderBy('orders.id', 'DESC')
            ->select('*', 'id as total_discount_calculate')
            // ->where('id','102')
            ->paginate(10);
        //End Orders Active Query
        // dd($activeOrders->get());
        foreach ($activeOrders as $order) {
            foreach ($order->vendors as $vendor) {       
                // $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)
                //     ->where('vendor_id', $vendor->vendor_id)
                //     ->orderBy('id', 'DESC')
                //     ->first();
                // dd($vendor->OrderStatusOption->getStatusName($order->luxury_option_id));
                if (!empty($vendor->OrderStatusOption)) {
                    $vendor->order_status = strtolower(@$vendor->OrderStatusOption->getStatusName($order->luxury_option_id)) ?? '';
                }
                foreach ($vendor->products as $product) {
                    $product = $this->gettimeSlotName($product);
                    if (isset($product->pvariant) && isset($product->pvariant->media) && $product->pvariant->media->isNotEmpty()) {
                        $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'] . '74/100' . $product->pvariant->media->first()->pimage->image->path['image_path'];
                    } elseif ($product->media->isNotEmpty() && !is_null($product->media->first()->image)) {
                        $product->image_url = $product->media->first()->image->path['image_fit'] . '74/100' . $product->media->first()->image->path['image_path'];
                    } else {
                        $product->image_url = ($product->image) ? $product->image['image_fit'] . '74/100' . $product->image['image_path'] : '';
                    }
                    $product->product_title = isset($product->translation) ? $product->translation->title : $product->product_name;
                }
                if ($vendor->delivery_fee > 0) {
                    $order_pre_time = ($vendor->order_pre_time > 0) ? $vendor->order_pre_time : 0;
                    $user_to_vendor_time = ($vendor->user_to_vendor_time > 0) ? $vendor->user_to_vendor_time : 0;
                    $ETA = $order_pre_time + $user_to_vendor_time;
                    // $vendor->ETA = ($ETA > 0) ? $this->formattedOrderETA($ETA, $vendor->created_at, $order->scheduled_date_time) : convertDateTimeInTimeZone($vendor->created_at, $user->timezone, 'h:i A');
                    $vendor->ETA = ($ETA > 0) ? $this->formattedOrderETA($ETA, $vendor->created_at, $order->scheduled_date_time) : dateTimeInUserTimeZone($vendor->created_at, $user->timezone);
                }
                if ($vendor->dineInTable) {
                    $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                    $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                    $vendor->dineInTableCategory = $vendor->dineInTable->category ? $vendor->dineInTable->category->title : '';
                }

                $vendor->vendor_dispatcher_status = VendorOrderDispatcherStatus::whereNotIn('dispatcher_status_option_id', [
                    2
                ])->select('*', 'dispatcher_status_option_id as status_data')->where('order_id', $order->id);
                if (isset($vendor->vendor->id))
                    $vendor->vendor_dispatcher_status = $vendor->vendor_dispatcher_status->where('vendor_id', $vendor->vendor->id);

                $vendor->vendor_dispatcher_status = $vendor->vendor_dispatcher_status->get();
                $vendor->vendor_dispatcher_status_count = 6;
                $vendor->dispatcher_status_icons = $iconsArray;
                // $vendor->dispatcher_status_icons = [asset('assets/icons/driver_1_1.png'),asset('assets/icons/driver_2_1.png'),asset('assets/icons/driver_4_1.png'),asset('assets/icons/driver_3_1.png'),asset('assets/icons/driver_4_2.png'),asset('assets/icons/driver_5_1.png')];
                // $dispatcher_status_options =VendorOrderDispatcherStatus::where(['order_id'=> $order->id,'vendor_id'=>$vendor->vendor->id,'dispatcher_status_option_id'=>'2'])->first();
                // $vendor->driver_chat = $dispatcher_status_options ? 1 : 0 ;
            }
        }
        // pr($activeOrders->toArray());exit();

        foreach ($pastOrders as $order) {

            $is_order_days_for_return = 0;
            $replaceable = 0;
            foreach ($order->vendors as $vendor) {
                // $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)
                //     ->where('vendor_id', $vendor->vendor_id)
                //     ->orderBy('id', 'DESC')
                //     ->first();
                // $vendor->order_status = $vendor_order_status ? strtolower($vendor_order_status->OrderStatusOption->title) : '';
                if (!empty($vendor->OrderStatusOption)) {
                    $vendor->order_status = strtolower(@$vendor->OrderStatusOption->getStatusName($order->luxury_option_id)) ?? 'n/a';
                }

                foreach ($vendor->products as $product) {
                    $product = $this->gettimeSlotName($product);
                    // dd($product->product->return_days);
                    // $vendor->is_order_days_for_return = 1;
                    if ((@$product->product->return_days && $this->checkOrderDaysForReturn($vendor, $product->product->return_days)) && $is_order_days_for_return == 0) {
                        $this->checkOrderDaysForReturn($vendor, $product->product->return_days);
                        $vendor->is_order_days_for_return = 1;
                    }

                    if (isset($product->pvariant) && isset($product->pvariant->media) && $product->pvariant->media->isNotEmpty()) {
                        $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'] . '74/100' . $product->pvariant->media->first()->pimage->image->path['image_path'];
                    } elseif ($product->media->isNotEmpty()) {
                        $product->image_url = $product->media->first()->image->path['image_fit'] . '74/100' . $product->media->first()->image->path['image_path'];
                    } else {
                        $product->image_url = ($product->image) ? $product->image['image_fit'] . '74/100' . $product->image['image_path'] : '';
                    }
                    $product->product_title = isset($product->translation) ? $product->translation->title : $product->product_name;
                }
                if ($vendor->dineInTable) {
                    $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                    $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                    $vendor->dineInTableCategory = $vendor->dineInTable->category ? $vendor->dineInTable->category->title : '';
                }
            }
        }
        $returnOrders = Order::with([
            'vendors.products.productReturn',
            'products.productRating',
            'user',
            'address',
            'products' => function ($q) {
                $q->whereHas('productReturn');
            },
            'vendors.dineInTable.translations' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendors.dineInTable.category',
            'vendors.products' => function ($q) {
                $q->whereHas('productReturn');
            },
            'vendors.products.media.image',
            'vendors.products.pvariant.media.pimage.image',
            'vendors' => function ($q) {
                $q->whereHas('products.productReturn');
            }

            ])->whereHas('vendors.products.productReturn')->where('orders.user_id', $user->id);
        $returnOrders->where('orders.is_long_term', 0);

        $returnOrders = $returnOrders->orderBy('orders.id', 'DESC')->paginate(20);

        foreach ($returnOrders as $order) {
            foreach ($order->vendors as $vendor) {
                foreach ($vendor->products as $product) {
                    $product = $this->gettimeSlotName($product);
                    if (isset($product->pvariant) && isset($product->pvariant->media) && $product->pvariant->media->isNotEmpty()) {
                        $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'] . '74/100' . $product->pvariant->media->first()->pimage->image->path['image_path'];
                    } elseif ($product->media->isNotEmpty()) {
                        $product->image_url = $product->media->first()->image->path['image_fit'] . '74/100' . $product->media->first()->image->path['image_path'];
                    } else {
                        $product->image_url = ($product->image) ? $product->image['image_fit'] . '74/100' . $product->image['image_path'] : '';
                    }
                    $product->product_title = isset($product->translation) ? $product->translation->title : $product->product_name;
                }
                if ($vendor->dineInTable) {
                    $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                    $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                    $vendor->dineInTableCategory = $vendor->dineInTable->category ? $vendor->dineInTable->category->title : '';
                }
            }
        }

        $rejectedOrders = Order::with([
            'vendors' => function ($q) {
                $q->where('order_status_option_id', 3);
            },
            'vendors.dineInTable.translations' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendors.dineInTable.category',
            'vendors.products',
            'vendors.products.media.image',
            'vendors.products.pvariant.media.pimage.image',
            'products.productRating',
            'user',
            'address'
        ]);
        if ($is_service_product_price_from_dispatch_forOnDemand == 1) {
            $rejectedOrders->whereHas('vendors.products', function ($q) {
                $q->where('dispatcher_status_option_id', 6); //1=pending,5= complete,6 reject
            });
        } else {

            $rejectedOrders = $rejectedOrders->whereHas('vendors', function ($q) {
                $q->where('order_status_option_id', 3);
            });
        }
        $rejectedOrders = $rejectedOrders->where(function ($q1) {
            $q1->where('payment_status', 1)
                ->whereNotIn('payment_option_id', [
                    1
                ]);
            $q1->orWhere(function ($q2) {
                $q2->where('payment_option_id', 1);
            });
        })
        ->where('orders.user_id', $user->id);
            

        $rejectedOrders->where('orders.is_long_term', 0);
        $rejectedOrders = $rejectedOrders->orderBy('orders.id', 'DESC')->select('*', 'id as total_discount_calculate')->paginate(10);


        foreach ($rejectedOrders as $order) {
            foreach ($order->vendors as $vendor) {
                // $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)
                //     ->where('vendor_id', $vendor->vendor_id)
                //     ->orderBy('id', 'DESC')
                //     ->first();
                // $vendor->order_status = $vendor_order_status ? strtolower($vendor_order_status->OrderStatusOption->title) : '';

                if (!empty($vendor->OrderStatusOption)) {
                    $vendor->order_status = strtolower(@$vendor->OrderStatusOption->getStatusName($order->luxury_option_id)) ?? '';
                }

                if ($vendor->cancelled_by == $user->id) {
                    $vendor->order_status = OrderVendor::CANCEL_STATUS;
                }
                foreach ($vendor->products as $product) {
                    if (isset($product->pvariant->media)) {
                        if ($product->pvariant->media->isNotEmpty()) {
                            $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'] . '74/100' . $product->pvariant->media->first()->pimage->image->path['image_path'];
                        } elseif ($product->media->isNotEmpty()) {
                            $product->image_url = $product->media->first()->image->path['image_fit'] . '74/100' . $product->media->first()->image->path['image_path'];
                        } else {
                            $product->image_url = ($product->image) ? $product->image['image_fit'] . '74/100' . $product->image['image_path'] : '';
                        }
                    }
                    $product->product_title = isset($product->translation) ? $product->translation->title : $product->product_name;
                }
                if ($vendor->dineInTable) {
                    $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                    $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                    $vendor->dineInTableCategory = $vendor->dineInTable->category ? $vendor->dineInTable->category->title : '';
                }
            }
        }

        $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();

        if (empty($clientCurrency)) {
            $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
        }

        $client_preferences = ClientPreference::select('*')->where('id', '>', 0)->first();
        if (!empty($client_preferences)) {
            $client_preferences->is_postpay_enable =  $additionalPreference['is_postpay_enable'];
            $client_preferences->is_order_edit_enable = $additionalPreference['is_order_edit_enable'];
            $client_preferences->order_edit_before_hours = $additionalPreference['order_edit_before_hours'];
            $client_preferences->editlimit_datetime = Carbon::now()->addHours($client_preferences->order_edit_before_hours)->toDateTimeString();
        }
        $payments = PaymentOption::where('credentials', '!=', '')->where('status', 1)->count();

        $cancellation_reason = ReturnReason::where([
            'status' => 'Active',
            'type' => 3
        ])->get();


        $longTermOrder = [];
        /**
         * get user long term orders
         */
        $show_long_term = 0;
        if ($additionalPreference['is_long_term_service'] == 1) {
            $show_long_term = 1;

            $longTermOrder = $this->getUserLongTermService($user, $langId, $currency_id);
        }


        $langId = Session::get('customerLanguage');
        $fixedFee = $this->fixedFee($langId);
        $pendingOrder = [];
        if ($is_service_product_price_from_dispatch_forOnDemand == 1) {
            $pendingOrder = $this->pendingOrder($request, $user, $langId, $iconsArray);
        }

        return view('frontend.account.orders')->with([
            'payments' => $payments,
            'rejectedOrders' => $rejectedOrders,
            'navCategories' => $navCategories,
            'cancellation_reason' => $cancellation_reason,
            'activeOrders' => $activeOrders,
            'pastOrders' => $pastOrders,
            'returnOrders' => $returnOrders,
            'clientCurrency' => $clientCurrency,
            'clientPreference' => $client_preferences,
            'fixedFee' => $fixedFee,
            'longTermOrder' => $longTermOrder,
            'additionalPreference' => $additionalPreference,
            'pendingOrder' => $pendingOrder,
            'show_long_term' => $show_long_term,
            'is_service_product_price_from_dispatch_forOnDemand' => $is_service_product_price_from_dispatch_forOnDemand
        ]);
    }

    /**
     * pendingOrder which dont have driver assign yet
     *
     * @param  mixed $request
     * @return void
     */
    public function pendingOrder(Request $request, $user, $langId, $iconsArray)
    {
        $Orders = Order::with([
            'vendors' => function ($q) {
                $q->whereHas('products', function ($Pq) {
                    $Pq->whereIn('dispatcher_status_option_id', [
                        1
                    ]);
                    // $Pq->orwhereNull('dispatcher_status_option_id');
                    $Pq->with(['products.media.image', 'products.pvariant.media.pimage.image', 'products.Routes', 'products.order_product_status']);
                });
            },
            'vendors.dineInTable.translations' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendors.dineInTable.category',
            'user',
            'address',
            'reqCancelOrder'
        ]);

        $Orders->where(function ($q1) {
            $q1->where('payment_status', 1)
                ->whereNotIn('payment_option_id', [
                    1,
                    38
                ]);
            $q1->orWhere(function ($q2) {
                $q2->whereIn('payment_option_id', [
                    1,
                    38
                ])
                    ->orWhere(function ($q3) {
                        $q3->where('is_postpay', 1)
                            -> // 1 for order is post paid
                            whereNotIn('payment_option_id', [
                                1,
                                38
                            ]);
                    });
            });
        })
            ->where('orders.user_id', $user->id);

        $Orders = $Orders->orderBy('orders.id', 'DESC')
            ->select('*', 'id as total_discount_calculate')
            ->paginate(10);
        foreach ($Orders as $order) {

            foreach ($order->vendors as $vendor) {

                $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)
                    ->where('vendor_id', $vendor->vendor_id)
                    ->orderBy('id', 'DESC')
                    ->first();

                $vendor->order_status = $vendor_order_status ? strtolower($vendor_order_status->OrderStatusOption->title) : '';

                foreach ($vendor->products as $product) {
                    //   pr($product->toArray());
                    $product = $this->gettimeSlotName($product);
                    if (isset($product->pvariant) && isset($product->pvariant->media) && $product->pvariant->media->isNotEmpty()) {
                        $product->image_url = $product->pvariant->media->first()->pimage->image->path['image_fit'] . '74/100' . $product->pvariant->media->first()->pimage->image->path['image_path'];
                    } elseif ($product->media->isNotEmpty() && !is_null($product->media->first()->image)) {
                        $product->image_url = $product->media->first()->image->path['image_fit'] . '74/100' . $product->media->first()->image->path['image_path'];
                    } else {
                        $product->image_url = ($product->image) ? $product->image['image_fit'] . '74/100' . $product->image['image_path'] : '';
                    }
                }
                if ($vendor->delivery_fee > 0) {
                    $order_pre_time = ($vendor->order_pre_time > 0) ? $vendor->order_pre_time : 0;
                    $user_to_vendor_time = ($vendor->user_to_vendor_time > 0) ? $vendor->user_to_vendor_time : 0;
                    $ETA = $order_pre_time + $user_to_vendor_time;

                    $vendor->ETA = ($ETA > 0) ? $this->formattedOrderETA($ETA, $vendor->created_at, $order->scheduled_date_time) : dateTimeInUserTimeZone($vendor->created_at, $user->timezone);
                }
                if ($vendor->dineInTable) {
                    $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                    $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                    $vendor->dineInTableCategory = $vendor->dineInTable->category ? $vendor->dineInTable->category->title : '';
                }

                $vendor->vendor_dispatcher_status = VendorOrderDispatcherStatus::whereNotIn('dispatcher_status_option_id', [
                    2
                ])->select('*', 'dispatcher_status_option_id as status_data')->where('order_id', $order->id);
                if (isset($vendor->vendor->id))
                    $vendor->vendor_dispatcher_status = $vendor->vendor_dispatcher_status->where('vendor_id', $vendor->vendor->id);

                $vendor->vendor_dispatcher_status = $vendor->vendor_dispatcher_status->get();
                $vendor->vendor_dispatcher_status_count = 6;
                $vendor->dispatcher_status_icons = $iconsArray;
            }
        }
        return $Orders;
    }
    public function gettimeSlotName($product)
    {
        $product->schedule_slot_name = '';
        if ($product->schedule_slot != '') {
            $nowDate     = Carbon::now()->format('Y-m-d');
            $D_slot     = explode('-', $product->schedule_slot);
            $start_time = $nowDate . ' ' . (@$D_slot[0] ?? '00:00');
            $end_time   = $nowDate . ' ' . (@$D_slot[1] ?? '01:00');

            $product->schedule_slot_name =  date('h:i A', strtotime($start_time)) . ' - ' . date('h:i A', strtotime($end_time));
        }
        return $product;
    }

    public function getOrderSuccessPage(Request $request)
    {
        $currency_id = Session::get('customerCurrency');
        $langId = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($langId);

        $order = Order::with([
            'products.vendor',
            'products.pvariant.vset.option2',
            'products.product.productcategory',
            'products.pvariant.translation' => function ($q) use ($langId) {
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                $q->where('language_id', $langId);
            },
            'address'
        ]);
        if (checkTableExists('order_long_term_services')) {
            $order = $order->with([
                'products.LongTermService.product',
                'products.LongTermService.product.translation_one' => function ($q) use ($langId) {
                    $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                    $q->where('language_id', $langId);
                }
            ]);
        }
        $order = $order->findOrfail($request->order_id);

        $fixedFeeNomenclatures = $this->fixedFee($langId);
        $order_vendors = OrderVendor::where('order_id', $request->order_id)->whereNotNull('dispatch_traking_url')->get();
        if (count($order_vendors)) {
            $home_service = ClientPreference::where('business_type', 'home_service')->where('id', '>', 0)->first();
            if ($home_service) {
                return Redirect::route('front.booking.details', $order->order_number);
            }
        }
        $total_other_taxes = 0.00;
        foreach (explode(":", $order->total_other_taxes) as $row) {
            $total_other_taxes += (float) $row;
        }

        $order->total_other_taxes_amount = $total_other_taxes;

        $slot_delivery_fees = 0;
        foreach ($order->products as $product) {
            $slot_delivery_fees += $product->slot_price;
        }

        $order->slot_delivery_fees = $slot_delivery_fees;

        $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();

        return view('frontend.order.success', compact('order', 'navCategories', 'clientCurrency', 'fixedFeeNomenclatures'));
    }

    // public function getOrderToyyibPaySuccessPage(Request $request)
    // {
    // $currency_id = Session::get('customerCurrency');
    // $langId = Session::get('customerLanguage');
    // $navCategories = $this->categoryNav($langId);
    // $order = Order::with(['products.pvariant.vset', 'products.pvariant.translation_one', 'address'])->findOrfail($request->order_id);
    // // dd($order->toArray());

    // $order_vendors = OrderVendor::where('order_id', $request->order_id)->whereNotNull('dispatch_traking_url')->get();
    // if (count($order_vendors)) {
    // $home_service = ClientPreference::where('business_type', 'home_service')->where('id', '>', 0)->first();
    // if ($home_service) {
    // return Redirect::route('front.booking.details', $order->order_number);
    // }
    // }

    // $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
    // return view('frontend.order.success', compact('order', 'navCategories', 'clientCurrency'));
    // }

    /**
     * getOrderSuccessReturnPage
     *
     * @param mixed $request
     * @return void
     */
    public function getOrderSuccessReturnPage(Request $request)
    {
        $currency_id = Session::get('customerCurrency');
        $langId = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($langId);
        // $order = Order::with(['products.pvariant.vset', 'products.pvariant.translation_one', 'address'])->findOrfail($request->order_id);
        $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
        return view('frontend.order.success-return', compact('navCategories', 'clientCurrency'));
    }

    /**
     * sendSuccessEmail
     *
     * @param mixed $request
     * @param mixed $order
     * @param mixed $vendor_id
     * @return void
     */
    public function sendSuccessEmail($request, $order, $vendor_id = '')
    {
        if ((isset($request->user_id)) && (!empty($request->user_id))) {
            $user = User::find($request->user_id);
        } elseif ((isset($request->auth_token)) && (!empty($request->auth_token))) {
            $user = User::where('auth_token', $request->auth_token)->first();
        } elseif (Auth::user()) {
            $user = Auth::user();
        } else {
            $user_id = $order->user_id;
            $user = User::find($user_id);
        }

        $client = CP::select('id', 'name', 'email', 'phone_number', 'logo')->where('id', '>', 0)->first();
        $data = ClientPreference::select('sms_key', 'sms_secret', 'sms_from', 'mail_type', 'mail_driver', 'mail_host', 'mail_port', 'mail_username', 'sms_provider', 'mail_password', 'mail_encryption', 'mail_from', 'admin_email')->where('id', '>', 0)->first();
        if (!empty($data->mail_driver) && !empty($data->mail_host) && !empty($data->mail_port) && !empty($data->mail_port) && !empty($data->mail_password) && !empty($data->mail_encryption)) {
            $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);
            $currSymbol = Session::has('currencySymbol') ? Session::get('currencySymbol') : '$';
            $client_name = 'Sales';
            $mail_from = $data->mail_from;
            try {
                $email_template_content = '';
                $address = '';
                $cartDetails = '';
                $email_template = EmailTemplate::where('id', 5)->first();
                if (!empty($email_template)) {
                    if ($user) {
                        $cart = Cart::select('id', 'is_gift', 'item_count')->with('coupon.promo')
                            ->where('status', '0')
                            ->where('user_id', $user->id)
                            ->first();
                    } else {
                        $cart = Cart::select('id', 'is_gift', 'item_count')->with('coupon.promo')
                            ->where('status', '0')
                            ->where('unique_identifier', session()->get('_token'))
                            ->first();
                    }

                    if ($cart) {
                        $cartDetails = $this->getCart($cart, 0, $user);
                    }

                    $luxuryOptionTitle = ($request->has('type')) ? $request->type : 'delivery';
                    if ($luxuryOptionTitle == 'payment_intent.succeeded') {
                        $luxuryOptionTitle = 'Success';
                    } elseif ($luxuryOptionTitle == 'payment_intent.payment_failed') {
                        $luxuryOptionTitle = 'Failed';
                    } else {
                        $luxuryOptionTitle = !empty($order->luxury_option) ? $order->luxury_option->title : 'delivery';
                    }

                    $email_template_content = $email_template->content;
                    //     if ($vendor_id == "") {
                    $returnHTML = view('email.newOrderProducts')->with(['user' => $user, 'cartData' => $cartDetails, 'order' => $order, 'currencySymbol' => $currSymbol, 'luxuryOptionTitle' => $luxuryOptionTitle])->render();
                    //     } else {
                    //$returnHTML = view('email.newOrderVendorProducts')->with(['cartData' => $cartDetails,'order' => $order, 'id' => $vendor_id, 'currencySymbol' => $currSymbol, 'luxuryOptionTitle' => $luxuryOptionTitle])->render();
                    // }
                    $email_template_content = str_ireplace("{customer_name}", ucwords($user->name), $email_template_content);
                    $email_template_content = str_ireplace("{order_id}", $order->order_number, $email_template_content);
                    $email_template_content = str_ireplace("{description}", '', $email_template_content);
                    $email_template_content = str_ireplace("{products}", $returnHTML, $email_template_content);

                    if (UserAddress::where('id', $request->address_id)->exists()) {
                        $address_arr = UserAddress::where('id', $request->address_id)->first();
                        $email_template_content = str_ireplace("{address}", $address_arr->address . ', ' . $address_arr->state . ', ' . $address_arr->country . ', ' . $address_arr->pincode, $email_template_content);
                        $address = str_ireplace("{address}", $address_arr->address . ', ' . $address_arr->state . ', ' . $address_arr->country . ', ' . $address_arr->pincode, $email_template_content);
                    }

                    $email_data = [
                        'link' => "link",
                        'mail_from' => $mail_from,
                        'client_name' => $client_name,
                        'logo' => $client->logo['original'],
                        'subject' => $email_template->subject,
                        'customer_name' => ucwords($user->name),
                        'email_template_content' => $email_template_content,
                        'cartData' => $cartDetails,
                        'user_address' => $address
                    ];

                    if (!empty($data['admin_email'])) {
                        $email_data['admin_email'] = $data['admin_email'];
                    }
                    $vendor_id == "" ? $email_data['send_to_cc'] = 1 : $email_data['send_to_cc'] = 0;

                    /* -- Sending email to vendor -- */
                    $vendor = Vendor::where('id', $vendor_id)->first();
                    if (!empty($vendor)) {
                        $email_data['email'] = $vendor->email;
                        dispatch(new \App\Jobs\SendOrderSuccessEmailJob($email_data))->onQueue('verify_email');
                    }

                    /* -- Sending email to customer -- */
                    $email_data['email'] = $user->email;
                    dispatch(new \App\Jobs\SendOrderSuccessEmailJob($email_data))->onQueue('verify_email');
                }
            } catch (\Exception $e) {
                \Log::error($e->getMessage());
            }
        }
    }

    /**
     * sendSuccessSMS
     *
     * @param mixed $request
     * @param mixed $order
     * @param mixed $vendor_id
     * @return void
     */
    public function sendSuccessSMS($request, $order, $vendor_id = '')
    {
        try {
            $prefer = ClientPreference::select('sms_provider', 'sms_key', 'sms_secret', 'sms_from', 'digit_after_decimal')->first();

            $currId = Session::get('customerCurrency');
            $currSymbol = Session::get('currencySymbol');
            $customerCurrency = ClientCurrency::where('currency_id', $currId)->first();
            $user = User::where('id', $order->user_id)->first();
            if ($user) {
                if ($user->dial_code == "971") {
                    $to = '+' . $user->dial_code . "0" . $user->phone_number;
                } else {
                    $to = '+' . $user->dial_code . $user->phone_number;
                }
                $provider = $prefer->sms_provider;
                $order->payable_amount = number_format((float) $order->payable_amount, $prefer->digit_after_decimal, '.', '');

                $keyData = [
                    '{user_name}' => $user->name ?? '',
                    '{amount}' => $currSymbol . $order->payable_amount,
                    '{order_number}' => $order->order_number ?? ''
                ];
                $body = sendSmsTemplate('order-place-Successfully', $keyData);

                if (!empty($prefer->sms_provider)) {
                    $send = $this->sendSmsNew($provider, $prefer->sms_key, $prefer->sms_secret, $prefer->sms_from, $to, $body);
                }
            }
        } catch (\Exception $ex) {
        }
    }

    /**
     * Get Cart Items
     */
    public function getCart($cart, $address_id = 0, $user = array())
    {
        $cart_id = $cart->id;
        if (!empty($user)) {
            $user = $user;
        } else {
            $user = Auth::user();
        }
        $langId = Session::get('customerLanguage');
        $curId = Session::get('customerCurrency');
        $pharmacy = ClientPreference::first();
        $cart->pharmacy_check = $pharmacy->pharmacy_check;
        $customerCurrency = ClientCurrency::where('currency_id', $curId)->first();
        $latitude = '';
        $longitude = '';
        if ($address_id > 0) {
            $address = UserAddress::where('user_id', $user->id)->where('id', $address_id)->first();
        } else {
            $address = UserAddress::where('user_id', $user->id)->where('is_primary', 1)->first();
            $address_id = ($address) ? $address->id : 0;
        }
        $latitude = ($address) ? $address->latitude : '';
        $longitude = ($address) ? $address->longitude : '';

        $cartData = CartProduct::with([
            'vendor',
            'coupon' => function ($qry) use ($cart_id) {
                $qry->where('cart_id', $cart_id);
            },
            'vendorProducts.pvariant.media.image',
            'vendorProducts.product.media.image',
            'vendorProducts.pvariant.vset.variantDetail.trans' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendorProducts.pvariant.vset.optionData.trans' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendorProducts.product.translation_one' => function ($q) use ($langId) {
                $q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description');
                $q->where('language_id', $langId);
            },
            'vendorProducts' => function ($qry) use ($cart_id) {
                $qry->where('cart_id', $cart_id);
            },
            'vendorProducts.addon.set' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendorProducts.product.categoryName' => function ($q) use ($langId) {
                $q->select('category_id', 'name');
                $q->where('language_id', $langId);
            },
            'vendorProducts.addon.option' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendorProducts.product.taxCategory.taxRate'
        ])->select('vendor_id', 'luxury_option_id')
            ->where('status', [
                0,
                1
            ])
            ->where('cart_id', $cart_id)
            ->groupBy('vendor_id')
            ->orderBy('created_at', 'asc')
            ->get();
        $loyalty_amount_saved = 0;
        $redeem_points_per_primary_currency = '';
        $loyalty_card = LoyaltyCard::where('status', '0')->first();
        if ($loyalty_card) {
            $redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
        }
        $subscription_features = array();
        if ($user) {
            //Get earn and used loyalty amount
            $loyaltyCheck = $this->getOrderLoyalityAmount($user);
            $loyalty_amount_saved = $loyaltyCheck->loyalty_amount_saved;

            $now = Carbon::now()->toDateTimeString();
            $user_subscription = SubscriptionInvoicesUser::with('features')->select('id', 'user_id', 'subscription_id')
                ->where('user_id', $user->id)
                ->where('end_date', '>', $now)
                ->orderBy('end_date', 'desc')
                ->first();
            if ($user_subscription) {
                foreach ($user_subscription->features as $feature) {
                    $subscription_features[] = $feature->feature_id;
                }
            }
        }
        $total_payable_amount = $total_subscription_discount = $total_discount_amount = $total_discount_percent = $total_taxable_amount = 0.00;
        if ($cartData) {
            $delivery_status = 1;
            foreach ($cartData as $ven_key => $vendorData) {
                $payable_amount = $taxable_amount = $subscription_discount = $discount_amount = $discount_percent = $deliver_charge = $delivery_fee_charges = 0.00;
                $delivery_count = 0;
                foreach ($vendorData->vendorProducts as $ven_key => $prod) {
                    $quantity_price = 0;
                    $divider = (empty($prod->doller_compare) || $prod->doller_compare < 0) ? 1 : $prod->doller_compare;
                    $price_in_currency = $prod->pvariant->price / $divider;
                    $price_in_doller_compare = $price_in_currency * $customerCurrency->doller_compare;
                    $quantity_price = $price_in_doller_compare * $prod->quantity;
                    $prod->pvariant->price_in_cart = $prod->pvariant->price;
                    $prod->pvariant->price = $price_in_currency;
                    $prod->pvariant->media_one = $prod->pvariant->media ? $prod->pvariant->media->first() : [];
                    $prod->pvariant->media_second = $prod->product->media ? $prod->product->media->first() : [];
                    $prod->pvariant->multiplier = $customerCurrency->doller_compare;
                    $prod->pvariant->quantity_price = number_format($quantity_price, 2);
                    $payable_amount = $payable_amount + $quantity_price;
                    $taxData = array();
                    $is_tax_price_inclusive = (object) getAdditionalPreference([
                        'is_tax_price_inclusive'
                    ]);
                    if (!empty($prod->product->taxCategory) && count($prod->product->taxCategory->taxRate) > 0) {
                        foreach ($prod->product->taxCategory->taxRate as $tckey => $tax_value) {
                            $rate = round($tax_value->tax_rate);
                            $tax_amount = ($price_in_doller_compare * $rate) / 100;
                            if (!$is_tax_price_inclusive->is_tax_price_inclusive) {
                                $product_tax = $quantity_price * $rate / 100;
                            } else {
                                $product_tax = ($quantity_price * $rate) / (100 + $rate);
                            }
                            $taxData[$tckey]['identifier'] = $tax_value->identifier;
                            $taxData[$tckey]['rate'] = $rate;
                            $taxData[$tckey]['tax_amount'] = number_format($tax_amount, 2);
                            $taxData[$tckey]['product_tax'] = number_format($product_tax, 2);
                            $taxable_amount = $taxable_amount + $product_tax;
                            $payable_amount = $payable_amount + $product_tax;
                        }
                        unset($prod->product->taxCategory);
                    }
                    $prod->taxdata = $taxData;
                    foreach ($prod->addon as $ck => $addons) {
                        $opt_price_in_currency = $addons->option->price / $divider;
                        $opt_price_in_doller_compare = $opt_price_in_currency * $customerCurrency->doller_compare;
                        $opt_quantity_price = number_format($opt_price_in_doller_compare * $prod->quantity, 2);
                        $addons->option->price_in_cart = $addons->option->price;
                        $addons->option->price = $opt_price_in_currency;
                        $addons->option->multiplier = $customerCurrency->doller_compare;
                        $addons->option->quantity_price = $opt_quantity_price;
                        $payable_amount = $payable_amount + $opt_quantity_price;
                    }
                    if (isset($prod->pvariant->image->imagedata) && !empty($prod->pvariant->image->imagedata)) {
                        $prod->cartImg = $prod->pvariant->image->imagedata;
                    } else {
                        $prod->cartImg = (isset($prod->product->media[0]) && !empty($prod->product->media[0])) ? $prod->product->media[0]->image : '';
                    }
                    if (!empty($prod->product->Requires_last_mile) && ($prod->product->Requires_last_mile == 1)) {
                        $deliver_charge = $this->getDeliveryFeeDispatcher($vendorData->vendor_id, $user->id);
                        if (!empty($deliver_charge) && $delivery_count == 0) {
                            $delivery_count = 1;
                            $prod->deliver_charge = number_format($deliver_charge, 2);
                            $payable_amount = $payable_amount + $deliver_charge;
                            $delivery_fee_charges = $deliver_charge;
                        }
                    }
                }
                if ($vendorData->coupon) {
                    if ($vendorData->coupon->promo->promo_type_id == 2) {
                        $total_discount_percent = $vendorData->coupon->promo->amount;
                        $payable_amount -= $total_discount_percent;
                    } else {
                        $gross_amount = number_format(($payable_amount - $taxable_amount), 2);
                        $percentage_amount = ($gross_amount * $vendorData->coupon->promo->amount / 100);
                        $payable_amount -= $percentage_amount;
                    }
                }
                if (in_array(1, $subscription_features)) {
                    $subscription_discount = $subscription_discount + $delivery_fee_charges;
                }
                if (isset($serviceArea)) {
                    if ($serviceArea->isEmpty()) {
                        $vendorData->isDeliverable = 0;
                        $delivery_status = 0;
                    } else {
                        $vendorData->isDeliverable = 1;
                    }
                }
                $vendorData->delivery_fee_charges = number_format($delivery_fee_charges, 2);
                $vendorData->payable_amount = number_format($payable_amount, 2);
                $vendorData->discount_amount = number_format($discount_amount, 2);
                $vendorData->discount_percent = number_format($discount_percent, 2);
                $vendorData->taxable_amount = number_format($taxable_amount, 2);
                $vendorData->product_total_amount = number_format(($payable_amount - $taxable_amount), 2);
                if (!empty($subscription_features)) {
                    $vendorData->product_total_amount = number_format(($payable_amount - $taxable_amount - $subscription_discount), 2);
                }
                $total_payable_amount = $total_payable_amount + $payable_amount;
                $total_taxable_amount = $total_taxable_amount + $taxable_amount;
                $total_discount_amount = $total_discount_amount + $discount_amount;
                $total_discount_percent = $total_discount_percent + $discount_percent;
                $total_subscription_discount = $total_subscription_discount + $subscription_discount;
            }
            $is_percent = 0;
            $amount_value = 0;
            if ($cart->coupon) {
                foreach ($cart->coupon as $ck => $coupon) {
                    if (isset($coupon->promo)) {
                        if ($coupon->promo->promo_type_id == 1) {
                            $is_percent = 1;
                            $total_discount_percent = $total_discount_percent + round($coupon->promo->amount);
                        }
                    }
                }
            }
            if ($is_percent == 1) {
                $total_discount_percent = ($total_discount_percent > 100) ? 100 : $total_discount_percent;
                $total_discount_amount = $total_discount_amount + ($total_payable_amount * $total_discount_percent) / 100;
            }
            if ($amount_value > 0) {
                $amount_value = $amount_value * $customerCurrency->doller_compare;
                $total_discount_amount = $total_discount_amount + $amount_value;
            }
            if (!empty($subscription_features)) {
                $total_discount_amount = $total_discount_amount + $total_subscription_discount;
                $cart->total_subscription_discount = number_format($total_subscription_discount, 2);
            }
            $total_payable_amount = $total_payable_amount - $total_discount_amount;
            if ($loyalty_amount_saved > 0) {
                if ($loyalty_amount_saved > $total_payable_amount) {
                    $loyalty_amount_saved = $total_payable_amount;
                }
                $total_payable_amount = $total_payable_amount - $loyalty_amount_saved;
            }

            $cart->loyalty_amount = number_format($loyalty_amount_saved, 2);
            $cart->gross_amount = number_format(($total_payable_amount + $total_discount_amount + $loyalty_amount_saved - $total_taxable_amount), 2);
            $cart->new_gross_amount = number_format(($total_payable_amount + $total_discount_amount), 2);
            $cart->total_payable_amount = number_format($total_payable_amount, 2);
            $cart->total_discount_amount = number_format($total_discount_amount, 2);
            $cart->total_taxable_amount = number_format($total_taxable_amount, 2);
            $cart->tip_5_percent = number_format((0.05 * $total_payable_amount), 2);
            $cart->tip_10_percent = number_format((0.1 * $total_payable_amount), 2);
            $cart->tip_15_percent = number_format((0.15 * $total_payable_amount), 2);
            $cart->deliver_status = $delivery_status;
            $cart->products = $cartData->toArray();
        }
        return $cart;
    }

    public function placeOrder(Request $request, $domain = '')
    {
        $primaryCurrency = ClientCurrency::where('is_primary', '=', 1)->first();
        if ($request->payment_option_id == '52') {
            if ($primaryCurrency->currency->iso_code != 'QAR') {
                return $this->errorResponse("Currency does not Match", 400);
            }
        }


        $order_response = $this->orderSave($request, "1");


        $response = $order_response->getData();
        if ($response->status == 'Success') {
            # if payment type cash on delivery or payment status is 'Paid'
            if (($response->data->payment_option_id == 1 || ($response->data->payment_option_id != 1 && $response->data->is_postpay == 1)) || (($response->data->payment_option_id != 1) && ($response->data->payment_status == 1))) {
                # if vendor selected auto accept
                $autoaccept = $this->autoAcceptOrderIfOn($response->data->id);
            }
            // dd($request->all());
            $productIds = collect($response->data->products)->pluck('product_id')->toArray();
            $order_id= $response->data->vendors[0]->order_id ?? null;
            if($request->payment_option_id == 1){
            ClientDocument::where('client_id', FacadesAuth::user()->id)
                ->whereNull('order_id')
                ->whereIn('product_id', $productIds)
                ->update(['order_id' => $order_id]);
            }
            return $this->successResponse($response->data, __('Order placed successfully.'), 201);
        } else {

            return $this->errorResponse($response->message, $response->code ?? 400);
        }
    }



    public function orderSave($request, $paymentStatus)
    {
        try {
            $latitude = '';
            $longitude = '';
            $Order_bid_discount = 0;
            $UserGiftCardId = '';
            $giftCardTotalAmount = 0;
            $giftCardUsedAmount = 0;
            $userGiftCardCode = null;
            $nowDate = Carbon::now()->toDateTimeString();

            $action = (Session::has('vendorType')) ? Session::get('vendorType') : 'delivery';
            if ($action == 'takeaway' || $action == 'dine_in' || $action == 'appointment') {
                $latitude = Session::get('latitude') ?? '';
                $longitude = Session::get('longitude') ?? '';
            }

            $fixed_fee_amount = $request->total_fixed_fee_amount ?? 0.00;
            DB::beginTransaction();

            $preferences = ClientPreference::select('is_hyperlocal', 'Default_latitude', 'Default_longitude', 'distance_unit_for_time', 'distance_to_time_multiplier', 'client_code', 'slots_with_service_area', 'stop_order_acceptance_for_users', 'subscription_mode')->first();
            $editlimit_datetime = Carbon::now()->toDateTimeString();
            $order_edit_before_hours = 0;
            $is_service_product_price_from_dispatch = 0;
            $additionalPreferences = getAdditionalPreference(['is_tax_price_inclusive', 'is_gift_card', 'is_service_product_price_from_dispatch', 'order_edit_before_hours', 'is_show_vendor_on_subcription', 'is_service_price_selection', 'stock_notification_before', 'stock_notification_qunatity']);

            if (($action == 'on_demand') && ($additionalPreferences['is_service_product_price_from_dispatch'] == 1)) {
                $getOnDemandPricingRule = getOnDemandPricingRule($action, Session::get('onDemandPricingSelected'), $additionalPreferences);
                $is_service_product_price_from_dispatch = $getOnDemandPricingRule['is_price_from_freelancer'];
            }
            $additionalPreferences = (object) $additionalPreferences;


            $order_edit_before_hours = $additionalPreferences->order_edit_before_hours;

            $editlimit_datetime = Carbon::now()->addHours($order_edit_before_hours)->toDateTimeString();

            $luxury_option = LuxuryOption::where('title', $action)->first();

            $delivery_on_vendors = array();
            if ((isset($request->user_id)) && (!empty($request->user_id))) {
                $user = User::find($request->user_id);
            } elseif ((isset($request->auth_token)) && (!empty($request->auth_token))) {
                $user = User::whereHas('device', function ($qu) use ($request) {
                    $qu->where('access_token', $request->auth_token);
                })->first();
            } else {
                $user = Auth::user();
            }
            if (($request->payment_option_id != 1) && ($request->payment_option_id != 2) && ($request->has('transaction_id')) && (!empty($request->transaction_id))) {
                $saved_transaction = Payment::where('transaction_id', $request->transaction_id)->first();
                if ($saved_transaction) {
                    return $this->errorResponse('Transaction has already been done', 400);
                }
            }

            if (isset($preferences->stop_order_acceptance_for_users) && ($preferences->stop_order_acceptance_for_users == 1)) {
                return $this->errorResponse(__('Sorry! We are not accepting orders right now.'), 400);
            }

            $currency_id = Session::get('customerCurrency');
            $language_id = Session::get('customerLanguage');
            $cart = Cart::where('user_id', $user->id)->with([
                'editingOrder.orderStatusVendor',
                'cartvendor'
            ])->first();
            if (!isset($cart)) {
                return $this->errorResponse(__('Product is removed as it is no longer available.'), 404);
            }
            $cart_product_removed =    CartProduct::where('cart_id', $cart->id)->whereHas('product', function ($q) {
                $q->whereIn('is_live', [0, 2]);
            })->pluck('id');

            if (count($cart_product_removed)) {
                CartProduct::whereIn('id', $cart_product_removed)->delete();
                if (CartProduct::where('cart_id', $cart->id)->count() == 0) {
                    Cart::find($cart->id)->delete();
                }
                DB::commit();
                return $this->errorResponse(__('Product is removed as it is no longer available.'), 404);
            }
            /* Get Currencies of client and customer */
            $customerCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
            $clientCurrency = ClientCurrency::where('is_primary', '=', 1)->first();

            $loyalty_amount_saved = 0;
            $redeem_points_per_primary_currency = '';
            $loyalty_card = LoyaltyCard::where('status', '0')->first();
            if ($loyalty_card) {
                $redeem_points_per_primary_currency = $loyalty_card->redeem_points_per_primary_currency;
            }

            $order_loyalty_points_earned_detail = Order::where('user_id', $user->id)->select(DB::raw('sum(loyalty_points_earned) AS sum_of_loyalty_points_earned'), DB::raw('sum(loyalty_points_used) AS sum_of_loyalty_points_used'))->first();

            if ($order_loyalty_points_earned_detail) {
                $loyalty_points_used = $order_loyalty_points_earned_detail->sum_of_loyalty_points_earned - $order_loyalty_points_earned_detail->sum_of_loyalty_points_used;
                if ($loyalty_points_used > 0 && $redeem_points_per_primary_currency > 0) {
                    $loyalty_amount_saved = $loyalty_points_used / $redeem_points_per_primary_currency;
                    if (($customerCurrency) && ($customerCurrency->is_primary != 1)) {
                        $loyalty_amount_saved = $loyalty_amount_saved * $customerCurrency->doller_compare;
                    }
                }
            }
            // Get earn and used loyalty amount
            $loyaltyCheck = $this->getOrderLoyalityAmount($user, $customerCurrency);
            $loyalty_amount_saved = $loyaltyCheck->loyalty_amount_saved;
            $loyalty_points_used = $loyaltyCheck->loyalty_points_used ?? 0;

            // check gift card

            if ($additionalPreferences->is_gift_card == 1) {


                if (isset($cart->giftCard) && !empty($cart->giftCard)) {

                    $giftcard = UserGiftCard::with('giftCard')->whereHas('giftCard', function ($query) use ($nowDate) {
                        return  $query->whereDate('expiry_date', '>=', $nowDate);
                    })->where(['is_used' => '0', 'gift_card_code' => $cart->user_gift_code])->first();


                    if ($giftcard) {
                        $UserGiftCardId = $giftcard->id;
                        $giftCardTotalAmount = $cart->giftCard->amount;
                        $userGiftCardCode = $cart->user_gift_code;
                    }
                }
            }

            /* Generate order object */
            $order = new Order();

            /* Generate order object based on conditions is cart is created by editing any order or not */

            if (isset($cart->editingOrder) && !empty($cart->editingOrder)) {
                $order = Order::where('id', $cart->editingOrder->id)->first();
                if ((strtotime($order->scheduled_date_time) - strtotime($editlimit_datetime)) < 0) {
                    return $this->errorResponse(__("Order can only be edited before Time limit of " . $order_edit_before_hours . " Hours from Scheduled date. Please discard order editing."), 400);
                }
                $VendorOrderStatus = VendorOrderStatus::where('order_id', $order->id)->whereNotIn('order_status_option_id', [1, 2])->count();
                $order_vendor_status_error = 0;
                foreach ($cart->editingOrder->orderStatusVendor as $key => $status) {
                    if ($status->order_status_option_id > 2) {
                        $order_vendor_status_error = 1;
                    }
                }

                if ($VendorOrderStatus > 0 || $order_vendor_status_error == 1) {
                    return $this->errorResponse(__("You can not edit this order. Either order is in processed or in processing. Please discard order editing."), 400);
                }

                OrderProduct::where('order_id', $order->id)->delete();
                OrderProductPrescription::where('order_id', $order->id)->delete();
                OrderTax::where('order_id', $order->id)->delete();
                VendorOrderStatus::where('order_id', $order->id)->delete();

                if (!empty($cart->cartvendor)) {
                    $array_cart_vendors = array();
                    foreach ($cart->cartvendor as $cartvendor) {
                        $array_cart_vendors[] = $cartvendor->vendor_id;
                    }
                    if (count($array_cart_vendors) > 0) {
                        $noincartVendors = OrderVendor::where('order_id', $cart->editingOrder->id)->whereNotIn('vendor_id', $array_cart_vendors)->get();
                        foreach ($noincartVendors as $noincartVendor) {
                            OrderVendor::where('order_id', $cart->editingOrder->id)->where('vendor_id', $noincartVendor->vendor_id)->delete();
                            if ($noincartVendor->dispatch_traking_url != '' && $noincartVendor->dispatch_traking_url != NULL) {
                                $dispatch_traking_url = str_replace('/order/', '/order-cancel/', $noincartVendor->dispatch_traking_url);
                                $response = Http::get($dispatch_traking_url);
                            }
                        }
                    }
                }
                $order->is_edited = 1;
            } else {
                $order = new Order();
                $order->order_number = generateOrderNo();
            }
            // $order = new Order;
            $order->user_id = $user->id;

            /* Get Client Address */
            if (($request->has('address_id')) && ($request->address_id > 0)) {
                $order->address_id = $request->address_id;
                $cus_address = UserAddress::find($request->address_id);
                $latitude = $cus_address->latitude ?? Session::get('latitude');
                $longitude = $cus_address->longitude ?? Session::get('longitude');
            } else {
                $order->address_id = $cart->address_id ?? null;
                $latitude = Session::get('latitude');
                $longitude = Session::get('longitude');
            }

            if ($action == 'appointment') { // no need to check serviceArea in appointment
                $latitude = '';
                $longitude = '';
            }
            /* Uodating client other details in order object */
            $order->payment_option_id = $request->payment_option_id;
            $order->total_other_taxes = $request->other_taxes_string;
            $order->comment_for_pickup_driver = $cart->comment_for_pickup_driver ?? null;
            $order->comment_for_dropoff_driver = $cart->comment_for_dropoff_driver ?? null;
            $order->comment_for_vendor = $cart->comment_for_vendor ?? null;
            $order->schedule_pickup = $cart->schedule_pickup ?? null;
            $order->schedule_dropoff = $cart->schedule_dropoff ?? null;
            $order->fixed_fee_amount = $fixed_fee_amount;
            $order->specific_instructions = $cart->specific_instructions ?? null;
            $order->is_gift = $request->is_gift ?? 0;
            $order->user_latitude = $latitude ? $latitude : null;
            $order->user_longitude = $longitude ? $longitude : null;
            $order->is_postpay = (isset($request->is_postpay)) ? $request->is_postpay : 0;
            $order->pick_drop_order_number = $request->pick_drop_order_number ?? null;
            /* Save initial details of order */
            $order->payable_amount = $request->total_amount;
            $order->total_amount = $request->total_amount;

            $order->save();


            /* Updating order prescription if any */
            $cart_prescriptions = CartProductPrescription::where('cart_id', $cart->id)->get();
            foreach ($cart_prescriptions as $cart_prescription) {
                $order_prescription = new OrderProductPrescription();
                $order_prescription->order_id = $order->id;
                $order_prescription->vendor_id = $cart_prescription->vendor_id;
                $order_prescription->product_id = $cart_prescription->product_id;
                $order_prescription->prescription = $cart_prescription->getRawOriginal('prescription');
                $order_prescription->save();
            }

            /* Getting subscripton details */
            $subscription_features = array();
            if ($user) {

                $user_subscription = SubscriptionInvoicesUser::with('features')->select('id', 'user_id', 'subscription_id')
                    ->where('user_id', $user->id)
                    ->where('end_date', '>', $nowDate)
                    ->orderBy('end_date', 'desc')
                    ->first();
                // if ($user_subscription) {
                // foreach ($user_subscription->features as $feature) {
                // $subscription_features[] = $feature->feature_id;
                // }
                // }
            }

            /* Get all products blongs to cart */

            $cart_products = CartProduct::select('*')->with([
                'vendor',
                'vendor.slot.geos.serviceArea',
                'vendor.slotDate.geos.serviceArea',
                'product.pimage',
                'product.variants',
                'product.taxCategory.taxRate',
                'vendorProducts.productVariantByRoles',
                'coupon' => function ($query) use ($cart) {
                    $query->where('cart_id', $cart->id);
                },
                'coupon.promo',
                'product.addon',
                'LongTermProducts.addons'
            ])
                ->where('cart_id', $cart->id)
                ->where('is_cart_checked', 1)
                ->where('status', [
                    0,
                    1
                ])
                ->orderBy('created_at', 'asc')
                ->get();

            /* Initialize empty data */
            $total_amount = 0;
            $total_discount = 0;
            $taxable_amount = 0;
            $total_taxable_amount = 0;
            $payable_amount = 0;
            $tax_rate = 0;
            $tax_category_ids = [];
            $vendor_ids = [];
            $total_service_fee = 0;
            $total_delivery_fee = 0;
            $fixed_fee_amount = 0.00;
            $total_subscription_discount = 0;
            $total_container_charges = 0;
            $vendor_total_container_charges = 0;
            $new_vendor_taxable_amount = 0;
            $rate = 0;
            $addon_amount = 0;
            $total_other_taxes = 0.00;
            $additionalPrice = 0.00;
            $totalAdditionalPrice = 0.00;
            $security_amount = 0.00;
            $is_long_term_order = 0;
            $deliveryfeeOnCoupon = 0;
            $rentalProtectionPrice = 0;
            $bookingOptionPrice = 0;

            /* Check if other taxes available like: Tax on service fee, container charges, delivery fee and fixed fee .etc */
            if (!empty($request->other_taxes_string)) {
                $total_other_taxes = array_sum(explode(":", $request->other_taxes_string));
                $total_other_taxes = decimal_format($total_other_taxes);
            }
            /* Loop through evey cart product to get desired data for order */
            foreach ($cart_products->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {


                $vendor_ids[] = $vendor_id;
                $delivery_fee = 0;
                $delivery_duration = 0;
                $delivery_distance = 0;
                $deliver_charge = $ptaxable_amount = $delivery_fee_charges = 0.00;
                $delivery_count = 0;
                $rate = 0;
                $vendor_amount = 0;
                $vendor_payable_amount = 0;
                $vendor_markup_amount = 0;
                $vendor_discount_amount = 0;
                $product_taxable_amount = 0;
                $vendor_products_total_amount = 0;
                $vendor_total_container_charges = 0;
                $vendor_taxable_amount = 0;
                $is_restricted = 0;
                $additionalPrice = 0.00;
                $quantity_container_charges = 0;
                $deliveryfeeOnCoupon = 0;
                $slot_based_price = 0;
                $passbase_check = VerificationOption::where([
                    'code' => 'passbase',
                    'status' => 1
                ])->first();

                $client_timezone = DB::table('clients')->first('timezone');

                if ($user) {
                    $timezone = $user->timezone ??  $client_timezone->timezone;
                } else {
                    $timezone = $client_timezone->timezone ?? ($user ? $user->timezone : 'Asia/Kolkata');
                }
                $vendor_subcription_lnvoices_id = '';
                if ($preferences->subscription_mode == '1' && $additionalPreferences->is_show_vendor_on_subcription == 1) {
                    $vendor_on_subcription = $this->getVendorActiveSubscription($vendor_id);
                    if ($vendor_on_subcription) {
                        $vendor_subcription_lnvoices_id =   $vendor_on_subcription->id;
                    }
                }

                /* Update details related to order vendor */
                if (isset($cart->editingOrder) && !empty($cart->editingOrder)) {
                    $OrderVendor = OrderVendor::where('order_id', $cart->editingOrder->id)->where('vendor_id', $vendor_id)->first();
                    if (!empty($OrderVendor)) {
                        $OrderVendor->web_hook_code = $OrderVendor->web_hook_code;
                    } else {
                        $OrderVendor = new OrderVendor();
                    }
                } else {
                    $OrderVendor = new OrderVendor();
                }

                if (!empty($order->total_other_taxes)) {
                    $tax_amount  =   (float) array_sum(explode(":", $order->total_other_taxes));
                } else {
                    $tax_amount = $order->taxable_amount;
                }

                $tax_amount  = round($order->total_other_taxes_amount, 2);
                $OrderVendor->status = 0;
                $OrderVendor->user_id = $user->id;
                $OrderVendor->order_id = $order->id;
                $OrderVendor->vendor_id = $vendor_id;
                $OrderVendor->subscription_invoices_vendor_id = $vendor_subcription_lnvoices_id;
                $OrderVendor->vendor_dinein_table_id = $vendor_cart_products->unique('vendor_dinein_table_id')->first()->vendor_dinein_table_id;
                $OrderVendor->save();

                //

                $vendorProductIds = array();
                $bid_vendor_discount = 0;
                $vendor_service_fee_percentage_amount = 0;
                // $addonArray = [];
                $datesInRange = [];
                foreach ($vendor_cart_products as $vendor_cart_product) {

                    if (!empty($vendor_cart_product->slot_price)) {
                        $slot_based_price += $vendor_cart_product->slot_price;
                    }

                    $start_date_time  = new Carbon($vendor_cart_product->start_date_time);
                    $end_date_time  = new Carbon($vendor_cart_product->end_date_time);
                    $vendor_cart_product->days = $start_date_time->diff($end_date_time)->days + 1;
                    $rental_price = $vendor_cart_product->pvariant ? $vendor_cart_product->pvariant->price : 0;
                    if (@$vendor_cart_product->pvariant->month_price && @$vendor_cart_product->pvariant->week_price) {
                        if ($vendor_cart_product->days >= 7 && $vendor_cart_product->days < 30) {
                            $rental_price = $vendor_cart_product->pvariant->week_price;
                        } elseif ($vendor_cart_product->days >= 30) {
                            $rental_price = $vendor_cart_product->pvariant->month_price;
                        }
                    }
                    $vendor_cart_product->rental_price = $rental_price;
                    $order['payable_amount'] = $vendor_cart_product->price;


                    if ((isset($preferences->is_hyperlocal)) && ($preferences->is_hyperlocal == 1) && !empty($latitude) && !empty($longitude)) {
                        $serviceArea =  $OrderVendor->vendor->where('id', $OrderVendor->vendor_id)->whereHas('serviceArea', function ($query) use ($latitude, $longitude) {
                            $query->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))");
                        })->first();
                        if (!isset($serviceArea)) {
                            DB::rollback();
                            return $this->errorResponse(__('Products for this vendor are not deliverable at your area. Please change address or remove product.'), 400);
                        }

                        if (($preferences->slots_with_service_area == 1) && ($vendor_cart_product->vendor->show_slot == 0)) {
                            $serviceArea = $vendor_cart_product->vendor->where(function ($query) use ($latitude, $longitude) {
                                $query->whereHas('slot.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                                    $q->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                                })
                                    ->orWhereHas('slotDate.geos.serviceArea', function ($q) use ($latitude, $longitude) {
                                        $q->select('vendor_id')->whereRaw("ST_Contains(POLYGON, ST_GEOMFROMTEXT('POINT(" . $latitude . " " . $longitude . ")'))")->where('is_active_for_vendor_slot', 1);
                                    });
                            })->where('id', $vendor_id)->get();

                            if ($serviceArea->isEmpty()) {
                                DB::rollback();
                                return $this->errorResponse(__('Products for this vendor are not deliverable at your area. Please change address or remove product.'), 400);
                            }
                        }
                    }

                    if (@$luxury_option->id == 4) {
                        $security_amount += $vendor_cart_product->product->security_amount;
                    }

                    if ($is_restricted == 0 && $passbase_check && isset($vendor_cart_product->product) && $vendor_cart_product->product->age_restriction == 1) {
                        $is_restricted = 1;
                    }
                    $rate = 0;
                    $variant = $vendor_cart_product->product->variants->where('id', $vendor_cart_product->variant_id)->first();
                    $quantity_price = 0;
                    $divider = (empty($vendor_cart_product->doller_compare) || $vendor_cart_product->doller_compare < 0) ? 1 : $vendor_cart_product->doller_compare;
                    $price_in_currency = $variant->price / $divider;

                    $variant_price = $variant->price;
                    if ($luxury_option->id == 9 && @$variant->month_price) {
                        $schedule_days = $vendor_cart_product->additional_increments_hrs_min / 24;
                        if ($schedule_days >= 7 && $schedule_days < 30) {
                            $variant_price = $variant->week_price * ($vendor_cart_product->additional_increments_hrs_min / (60 * 24));
                        } elseif ($schedule_days >= 30) {
                            $variant_price = $variant->month_price * ($vendor_cart_product->additional_increments_hrs_min / (60 * 24));
                        } else {
                            $variant_price = $variant->price * ($vendor_cart_product->additional_increments_hrs_min / (60 * 24));
                        }
                    }


                    // change product price when is_service_product_price_from_dispatch on
                    if (($action == 'on_demand') && $is_service_product_price_from_dispatch == 1) {
                        $price_in_currency = $vendor_cart_product->dispatch_agent_price / $divider;
                    }
                    //Find item price here  ==  + $variant->price;
                    $container_charges_in_currency = $variant->container_charges / $divider;
                    $price_container_charges = $variant->container_charges;
                    $price_in_dollar_compare = $price_in_currency * $clientCurrency->doller_compare;
                    $container_charges_in_dollar_compare = $container_charges_in_currency * $clientCurrency->doller_compare;

                    if (getAdditionalPreference([
                        'is_corporate_user'
                    ])['is_corporate_user'] == 1) {
                        $quantity_role_price = $this->calculatePriceV2($vendor_cart_product->productVariantByRoles, $vendor_cart_product->quantity);
                    }
                    if (@$quantity_role_price['quantity_price'] != 0 && (getAdditionalPreference([
                        'is_corporate_user'
                    ])['is_corporate_user'] == 1)) {

                        $quantity_price = $quantity_role_price['quantity_price'];
                    } else {

                        $quantity_price = $price_in_dollar_compare * $vendor_cart_product->quantity;
                    }

                    $daysCountRecurring       = 1;
                    if ($vendor_cart_product->recurring_day_data && !empty($vendor_cart_product->recurring_day_data)) {
                        $date       = count(explode(",", $vendor_cart_product->recurring_day_data));
                        $daysCountRecurring =  $date;
                    }
                    $quantity_price = $quantity_price * $daysCountRecurring;

                    if ($action == 'p2p') {
                        $quantity_price = $request->total_amount ?? 0;
                    }
                    $quantity_container_charges = $container_charges_in_dollar_compare * $vendor_cart_product->quantity;
                    $total_container_charges = $total_container_charges + $quantity_container_charges;

                    $vendor_products_total_amount = $vendor_products_total_amount + $quantity_price + $price_container_charges;
                    // $vendor_payable_amount = $vendor_payable_amount + $quantity_price + $quantity_container_charges;
                    $vendor_markup_amount = $vendor_markup_amount + $variant->markup_price;
                    $vendor_payable_amount = $vendor_payable_amount + $quantity_price;
                    $vendor_amount = $vendor_amount + $quantity_price;
                    $vendor_total_container_charges = $vendor_total_container_charges + $quantity_container_charges;
                    // $vendor_total_container_charges = $quantity_container_charges;
                    // echo "<br>payable_amount: ".$payable_amount."+ quantity_price: ".$quantity_price ;

                    $payable_amount = $payable_amount + $quantity_price;

                    // $payable_amount = $payable_amount + $quantity_price;
                    // $vendor_products_total_amount = $vendor_products_total_amount + $quantity_price;
                    // $vendor_payable_amount = $vendor_payable_amount + $quantity_price;

                    $OrderVendor->schedule_slot = !empty($vendor_cart_product->schedule_slot) ? $vendor_cart_product->schedule_slot : '';

                    $OrderVendor->scheduled_date_time = !empty($vendor_cart_product->scheduled_date_time) ? $vendor_cart_product->scheduled_date_time : '';
                    $deliver_Vendor_type = [
                        'delivery',
                        'appointment',
                        'on_demand'
                    ]; // pass vendor type for delivery option
                    if (in_array($action, $deliver_Vendor_type)) {
                        $deliver_fee_data = CartDeliveryFee::where('cart_id', $vendor_cart_product->cart_id)->where('vendor_id', $vendor_cart_product->vendor_id)->first();
                        if (((!empty($vendor_cart_product->product->Requires_last_mile)) && ($vendor_cart_product->product->Requires_last_mile == 1)) || isset($deliver_fee_data)) {
                            $OrderVendor->shipping_delivery_type = $deliver_fee_data->shipping_delivery_type ?? 'D';
                            $OrderVendor->courier_id = $deliver_fee_data->courier_id ?? 0;

                            if ($deliver_fee_data) :
                                $delivery_fee = $deliver_fee_data->delivery_fee ?? 0.00;
                                $delivery_duration = $deliver_fee_data->delivery_duration ?? 0;
                                $delivery_distance = $deliver_fee_data->delivery_distance ?? 0.00;
                            endif;

                            if (!empty($delivery_fee) && $delivery_count == 0) {
                                $total_delivery_fee += $delivery_fee;
                                $delivery_count = 1;
                                $vendor_cart_product->delivery_fee = number_format($delivery_fee, 2);
                                // $payable_amount = $payable_amount + $delivery_fee;
                                $delivery_fee_charges = $delivery_fee;

                                if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                                    if ($order->address_id)
                                        $vendor_cart_product->vendor = $this->getVendorDistanceWithTime($latitude, $longitude, $vendor_cart_product->vendor, $preferences);
                                    $OrderVendor->order_pre_time = ($vendor_cart_product->vendor->order_pre_time > 0) ? $vendor_cart_product->vendor->order_pre_time : 0;
                                    $timeofLineOfSightDistance = ($vendor_cart_product->vendor->timeofLineOfSightDistance > 0) ? $vendor_cart_product->vendor->timeofLineOfSightDistance : 0;
                                    if ($delivery_duration > 0) {
                                        $OrderVendor->user_to_vendor_time = intval($delivery_duration);
                                    } else if ($vendor_cart_product->vendor->timeofLineOfSightDistance > 0) {
                                        $OrderVendor->user_to_vendor_time = intval($timeofLineOfSightDistance) - intval($OrderVendor->order_pre_time);
                                    }
                                } else {
                                    $OrderVendor->order_pre_time = ($vendor_cart_product->vendor->order_pre_time > 0) ? $vendor_cart_product->vendor->order_pre_time : 0;
                                    if ($delivery_duration > 0) {
                                        $OrderVendor->user_to_vendor_time = intval($delivery_duration);
                                    }
                                }
                            }
                        }
                    }


                    $taxable_amount = $product_taxable_amount;
                    $vendor_taxable_amount = $taxable_amount;
                    if ($action == 'p2p') {
                        $variant_price = $request->total_amount ?? 0;
                    } else {
                        $variant_price = $variant->price * $daysCountRecurring;
                    }
                    // change variant_price price when is_service_product_price_from_dispatch on
                    $is_price_buy_driver = 0;
                    if (($action == 'on_demand') && ($is_service_product_price_from_dispatch == 1)) {
                        $variant_price = $vendor_cart_product->dispatch_agent_price;
                        $is_price_buy_driver = 1;
                    }
                    $total_amount += $vendor_cart_product->quantity * $variant_price;

                    if (@$quantity_role_price['quantity_price'] != 0 && (getAdditionalPreference(['is_corporate_user'])['is_corporate_user'] == 1)) {

                        $quantity_price = $quantity_role_price['quantity_price'];
                        $total_amount += $vendor_cart_product->quantity * $quantity_role_price['amount'];
                        $variant_price = $quantity_role_price['amount'];
                    }
                    $getAdditionalPreference = getAdditionalPreference(['is_rental_weekly_monthly_price']);
                    if (@$getAdditionalPreference['is_rental_weekly_monthly_price']) {
                        $quantity_price = $request->total_amount;
                        $total_amount = $request->total_amount;
                        $variant_price = $request->total_amount;
                    }
                    $order_product = new OrderProduct;

                    if ($action == 'p2p') {
                        $order_product->price = $request->total_amount ?? 0;
                    } else {
                        $order_product->price = $variant_price;
                    }
                    $order_product->order_id = $order->id;
                    $order_product->price = $variant_price;
                    $order_product->bid_number = @$vendor_cart_product->bid_number ?? null;
                    $order_product->bid_discount = @$vendor_cart_product->bid_discount ?? null;
                    $order_product->markup_price = $variant->markup_price;
                    $order_product->additional_increments_hrs_min = @$vendor_cart_product->additional_increments_hrs_min;
                    $order_product->start_date_time = $vendor_cart_product->start_date_time;
                    $order_product->end_date_time = $vendor_cart_product->end_date_time;
                    $order_product->dispatcher_status_option_id = 1;
                    $order_product->order_status_option_id = 1;
                    $order_product->product_delivery_fee = isset($vendor_cart_product->product_delivery_fee) ? $vendor_cart_product->product_delivery_fee : 0;
                    $order_product->is_price_buy_driver = $is_price_buy_driver;
                    $order_product->specific_instruction = $vendor_cart_product->specific_instruction;


                    if ($action == 'p2p') {
                        $order_product->price = $request->total_amount ?? 0;
                    } else {
                        $order_product->price = $variant->price * $daysCountRecurring;
                    }

                    /**
                     * for rental case total_booking_time as a total time
                     * for on_demand and appointment total booking time as single service duration time as per service for get totel service time multiply by quantity
                     */

                    if (@$vendor_cart_product->bid_number) {
                        Bid::where('id', $vendor_cart_product->bid_number)->update([
                            'status' => 1
                        ]);
                        $bid_vendor_discount += ((($order_product->price * $vendor_cart_product->quantity) * $vendor_cart_product->bid_discount) / 100);
                    }

                    $order_product->total_booking_time = @$vendor_cart_product->total_booking_time;

                    $order_product->container_charges = $variant->container_charges;
                    $order_product->order_vendor_id = $OrderVendor->id;
                    $order_product->taxable_amount = $product_taxable_amount;
                    if ($variant->incremental_price_per_min != '' && $variant->incremental_price_per_min > 0) {
                        $additionalPrice += ($vendor_cart_product->additional_increments_hrs_min / $variant->incremental_price_per_min);

                        $order_product->incremental_price = ($vendor_cart_product->additional_increments_hrs_min / $variant->incremental_price_per_min);
                    }

                    $order_product->quantity = $vendor_cart_product->quantity;
                    $order_product->vendor_id = $vendor_cart_product->vendor_id;
                    $order_product->product_id = $vendor_cart_product->product_id;
                    $order_product->user_product_order_form = $vendor_cart_product->user_product_order_form;
                    $product_category = Product::where('id', $vendor_cart_product->product_id)->first();
                    if ($product_category) {
                        $order_product->category_id = $product_category->category_id;
                    }
                    $order_product->created_by = $vendor_cart_product->created_by;
                    $order_product->variant_id = $vendor_cart_product->variant_id;
                    $product_variant_sets = '';
                    if (isset($vendor_cart_product->variant_id) && !empty($vendor_cart_product->variant_id)) {
                        $var_sets = ProductVariantSet::where('product_variant_id', $vendor_cart_product->variant_id)->where('product_id', $vendor_cart_product->product->id)
                            ->with([
                                'variantDetail.trans' => function ($qry) use ($language_id) {
                                    $qry->where('language_id', $language_id);
                                },
                                'optionData.trans' => function ($qry) use ($language_id) {
                                    $qry->where('language_id', $language_id);
                                }
                            ])
                            ->get();
                        if (count($var_sets)) {
                            foreach ($var_sets as $set) {
                                if (isset($set->variantDetail) && !empty($set->variantDetail)) {
                                    $product_variant_set = @$set->variantDetail->trans->title . ":" . @$set->optionData->trans->title . ", ";
                                    $product_variant_sets .= $product_variant_set;
                                }
                            }
                        }
                    }

                    $order_product->product_variant_sets = $product_variant_sets;
                    if (!empty($vendor_cart_product->product->title)) {
                        $vendor_cart_product->product->title = $vendor_cart_product->product->title;
                    } elseif (empty($vendor_cart_product->product->title)  && !empty($vendor_cart_product->product->translation)) {
                        $vendor_cart_product->product->title = @$vendor_cart_product->product->translation[0]->title;
                    } else {
                        $vendor_cart_product->product->title = $vendor_cart_product->product->sku;
                    }

                    $order_product->product_name = $vendor_cart_product->product->title ?? $vendor_cart_product->product->sku;

                    $product_dispatcher_tag = $vendor_cart_product->product->tags;

                    $order_product->product_dispatcher_tag = $vendor_cart_product->product->tags;

                    $order_product->schedule_type = $vendor_cart_product->schedule_type ?? null;
                    $order_product->scheduled_date_time = $vendor_cart_product->schedule_type == 'schedule' ? $vendor_cart_product->scheduled_date_time : null;

                    $order_product->schedule_slot = !empty($vendor_cart_product->schedule_slot) ? $vendor_cart_product->schedule_slot : '';
                    $order_product->dispatch_agent_id = !empty($vendor_cart_product->dispatch_agent_id) ? $vendor_cart_product->dispatch_agent_id : null;


                    $order_product->slot_id = !empty($vendor_cart_product->slot_id) ? $vendor_cart_product->slot_id : null;

                    $order_product->delivery_date = !empty($vendor_cart_product->delivery_date) ? $vendor_cart_product->delivery_date : null;

                    $order_product->slot_price = !empty($vendor_cart_product->slot_price) ? $vendor_cart_product->slot_price : null;

                    $order_product->dispatch_agent_id = !empty($vendor_cart_product->dispatch_agent_id) ? $vendor_cart_product->dispatch_agent_id : null;

                    if ($vendor_cart_product->product->pimage) {
                        $order_product->image = $vendor_cart_product->product->pimage->first() ? $vendor_cart_product->product->pimage->first()->path : '';
                    }
                    // added some columen for rental case
                    $order_product->start_date_time = $vendor_cart_product->start_date_time;
                    $order_product->end_date_time = $vendor_cart_product->end_date_time;
                    $order_product->additional_increments_hrs_min = $vendor_cart_product->additional_increments_hrs_min;

                    if (@$luxury_option->id == 4) {
                        $order_product->security_amount = $vendor_cart_product->product->security_amount;
                    }

                    $order_product->save();

                    /** for Recurring Service */
                    if (!empty($vendor_cart_product->recurring_booking_time)) {
                        $user_timezone          =   $timezone;
                        $recurring_booking_time =   convertDateTimeInTimeZone($vendor_cart_product->recurring_booking_time, $user_timezone, 'H:i');

                        $RecurringServiceSchedule = array();

                        // No Nee other action
                        if (@$vendor_cart_product->recurring_booking_type) {
                            $Recurring_quantity     = $vendor_cart_product->quantity;
                            $recurring_day_data     = $vendor_cart_product->recurring_day_data;
                            $recurring_day_data     = explode(",", $recurring_day_data);

                            $ndate                  = convertDateTimeInClientTimeZone(Carbon::now());
                            $recurring_booking_time = convertDateTimeInTimeZone($vendor_cart_product->recurring_booking_time, $user_timezone, 'H:i');
                            for ($x = 0; $x < count($recurring_day_data); $x++) {
                                $date           = $recurring_day_data[$x];
                                $newDate        = $date . ' ' . $recurring_booking_time;
                                $RecurringServiceSchedule[] = [
                                    'order_vendor_product_id' => $order_product->id,
                                    'schedule_date'           => $newDate,
                                    'type'                    => 2,
                                    'order_number'            => $order->order_number
                                ];
                            }
                        }

                        if (!empty($RecurringServiceSchedule)) {
                            OrderLongTermServiceSchedule::insert($RecurringServiceSchedule);
                        }
                    }

                    /** for long Term Service */
                    if ($vendor_cart_product->product->is_long_term_service && $vendor_cart_product->LongTermProducts) {
                        $is_long_term_order = 1;
                        $service_start_date = $vendor_cart_product->service_start_date ?? Carbon::now()->format('Y-m-d H:i:s');
                        $service_end_date = Carbon::parse($service_start_date)->addMonths($vendor_cart_product->product->service_duration)
                            ->setTimezone('UTC')
                            ->format('Y-m-d H:i:s');
                        $LongTermSericeData = [
                            'order_product_id' => $order_product->id,
                            'user_id' => $user->id,
                            'service_quentity' => $vendor_cart_product->LongTermProducts->quantity ?? 1,
                            'service_day' => $vendor_cart_product->service_day,
                            'service_date' => $vendor_cart_product->service_date,
                            'service_start_date' => $service_start_date,
                            'service_period' => $vendor_cart_product->service_period,
                            'service_end_date' => $service_end_date,
                            'service_product_id' => $vendor_cart_product->LongTermProducts->product_id,
                            'service_product_variant_id' => $vendor_cart_product->LongTermProducts->product_variant,
                            'status' => 0
                        ];
                        $OrderLongTermServices = OrderLongTermServices::create($LongTermSericeData);
                        if ($vendor_cart_product->LongTermProducts->addons->isNotEmpty()) {
                            foreach ($vendor_cart_product->LongTermProducts->addons as $SAddon) {
                                $LongTermSericeAddonData = [
                                    'order_long_term_services_id' => $OrderLongTermServices->id,
                                    'addon_id' => $SAddon->addon_id,
                                    'option_id' => $SAddon->option_id
                                ];
                                OrderLongTermServicesAddon::create($LongTermSericeAddonData);
                            }
                        }
                        /**
                         * save long term service schedule
                         */
                        $OrderLongTermServiceSchedule = array();;
                        $Service_quantity = $vendor_cart_product->LongTermProducts->quantity;
                        $start_service_date = Carbon::parse($vendor_cart_product->service_start_date)->format('Y-m-d');
                        $end_service_date = Carbon::parse($vendor_cart_product->service_start_date)->addMonths($vendor_cart_product->product->service_duration);
                        $ndate = convertDateTimeInClientTimeZone(Carbon::now());

                        if ($vendor_cart_product->service_period == 'days') {

                            $end_service_date = Carbon::parse($vendor_cart_product->service_start_date)->addDays(($vendor_cart_product->LongTermProducts->quantity + 1));
                            $period = CarbonPeriod::create($start_service_date, $end_service_date);
                            $entery = 1;
                            foreach ($period as $key => $date) {

                                $newDate = $date->format('Y-m-d') . ' ' . Carbon::parse($vendor_cart_product->start_date_time)->format('H:i:s');
                                $UserutcTime = convertDateTimeInClientTimeZone($newDate);

                                if (strtotime($ndate) < strtotime($UserutcTime)) {
                                    if ($entery <= $Service_quantity) {
                                        $OrderLongTermServiceSchedule[] = [
                                            'order_long_term_services_id' => $OrderLongTermServices->id,
                                            'schedule_date' => $UserutcTime //
                                        ];
                                        $entery++;
                                    }
                                }
                            }
                        } elseif ($vendor_cart_product->service_period == 'week') {
                            $end_service_date = Carbon::parse($vendor_cart_product->service_start_date)->addWeeks(($vendor_cart_product->LongTermProducts->quantity + 1));
                            $period = CarbonPeriod::create($start_service_date, $end_service_date);
                            $entery = 1;
                            foreach ($period as $key => $date) {
                                $dayNumber = $date->dayOfWeek + 1; // get day number
                                if ($vendor_cart_product->service_day == $dayNumber) {
                                    if ($entery <= $Service_quantity) {
                                        $OrderLongTermServiceSchedule[] = [
                                            'order_long_term_services_id' => $OrderLongTermServices->id,
                                            'schedule_date' => $date->format('Y-m-d') . ' ' . Carbon::parse($vendor_cart_product->start_date_time)->format('H:i:s') //
                                        ];
                                        $entery++;
                                    }
                                }
                            }
                        } elseif ($vendor_cart_product->service_period == 'months') {

                            $end_service_date = Carbon::parse($vendor_cart_product->service_start_date)->addMonths(($vendor_cart_product->LongTermProducts->quantity + 1));

                            if ($vendor_cart_product->service_date == 0) {

                                $startdate = Carbon::now()->endOfMonth()->format('Y-m-d');
                                // echo $startdate . ' ';
                                if (strtotime($startdate) < strtotime($start_service_date))
                                    $startdate = Carbon::now()->addMonths(1);

                                $arrayDate = explode("-", $startdate);
                                $newDate = $arrayDate[0] . '-' . $arrayDate[1] . '-01';

                                for ($i = 0; $i < $Service_quantity; $i++) {

                                    $OrderLongTermServiceSchedule[] = [
                                        'order_long_term_services_id' => $OrderLongTermServices->id,
                                        'schedule_date' => Carbon::parse($newDate)->addMonths($i)
                                            ->endOfMonth()
                                            ->format('Y-m-d') . ' ' . Carbon::parse($vendor_cart_product->start_date_time)->format('H:i:s') //
                                    ];
                                }
                            } else {

                                $todayDate = Carbon::now()->format('Y-m-d');
                                $arrayDate = explode("-", $todayDate);
                                $newDate = $arrayDate[0] . '-' . $arrayDate[1] . '-' . $vendor_cart_product->service_date;
                                $startdate = Carbon::parse($newDate)->format('Y-m-d');
                                if (strtotime($startdate) < strtotime($start_service_date))
                                    $startdate = Carbon::parse($startdate)->addMonth();

                                // $selected_date = $startdate->subMonth();

                                for ($i = 0; $i < $Service_quantity; $i++) {

                                    $OrderLongTermServiceSchedule[] = [
                                        'order_long_term_services_id' => $OrderLongTermServices->id,
                                        'schedule_date' => Carbon::parse($startdate)->addMonths($i)->format('Y-m-d') . ' ' . Carbon::parse($vendor_cart_product->start_date_time)->format('H:i:s') //
                                    ];
                                }
                            }
                        }

                        if (!empty($OrderLongTermServiceSchedule)) {
                            OrderLongTermServiceSchedule::insert($OrderLongTermServiceSchedule);
                        }
                    }



                    // book for rental
                    if (@$luxury_option->id == 4) {

                        $data = [
                            'memo' => __('Booked for order #') . $order->order_number,
                            'variant_id' => $order_product->variant_id,
                            'product_id' => $order_product->product_id,
                            'start_date' => $order_product->start_date_time,
                            'order_user_id' => $order->user_id,
                            'order_vendor_id' => $order_product->vendor_id,
                            'end_date' => $order_product->end_date_time
                        ];
                        //pr($data);
                        $res =   $this->bookingSlot($data, $order_product->id, $order->id);
                        //pr($res);
                    }
                    // pr($order_product);

                    if (!empty($vendor_cart_product->addon)) {

                        foreach ($vendor_cart_product->addon as $ck => $addon) {
                            $opt_quantity_price = 0;
                            $opt_price_in_currency = $addon->option->price ?? 0;
                            $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                            $opt_quantity_price = $opt_price_in_doller_compare * $order_product->quantity;
                            $total_amount = $total_amount + $opt_quantity_price;
                            // echo "opt_quantity_price: ".$opt_quantity_price;
                            $payable_amount = $payable_amount + $opt_quantity_price;
                            $vendor_payable_amount = $vendor_payable_amount + $opt_quantity_price;
                            // if(!in_array($vendor_cart_product->vendor_id, $addonArray)){
                            // $vendor_payable_amount_for_service = $vendor_payable_amount;
                            // }
                            $vendor_amount = $vendor_amount + $opt_quantity_price;
                            $quantity_price = $quantity_price + $opt_quantity_price;
                        }
                    }


                    if (!empty($cart->rentalProtection)) {
                        foreach ($cart->rentalProtection as $protection) {
                            $protection_price_in_currency = $protection->rentalProtection->price ?? 0;
                            $rentalProtectionPrice = $protection_price_in_currency * $clientCurrency->doller_compare;
                            $payable_amount += $rentalProtectionPrice;
                            $quantity_price += $rentalProtectionPrice;
                            $vendor_amount += $rentalProtectionPrice;
                        }
                    }
                    if (!empty($cart->bookingOption)) {
                        foreach ($cart->bookingOption as $option) {
                            $option_price_in_currency = $option->bookingOption->price ?? 0;
                            $bookingOptionPrice = $option_price_in_currency * $clientCurrency->doller_compare;
                            $payable_amount += $bookingOptionPrice;
                            $quantity_price += $bookingOptionPrice;
                            $vendor_amount += $bookingOptionPrice;
                        }
                    }

                    if ($vendor_cart_product->vendor->service_fee_percent > 0) {
                        // $vendor_service_fee_percentage_amount = ($vendor_payable_amount * $vendor_cart_product->vendor->service_fee_percent) / 100; // wrong percentage_amount
                        $service_fee_percentage_amount = ($quantity_price * $vendor_cart_product->vendor->service_fee_percent) / 100;
                        $vendor_service_fee_percentage_amount = $vendor_service_fee_percentage_amount + $service_fee_percentage_amount;
                        $payable_amount += $service_fee_percentage_amount;
                        $total_service_fee = $total_service_fee + $service_fee_percentage_amount;
                        $vendor_payable_amount += $service_fee_percentage_amount;
                    }

                    if ($vendor_cart_product->vendor->fixed_service_charge > 0) {
                        // $vendor_service_fee_percentage_amount = ($vendor_payable_amount * $vendor_cart_product->vendor->service_fee_percent) / 100; // wrong percentage_amount
                        $service_fee_percentage_amount        = $vendor_cart_product->vendor->service_charge_amount;
                        $vendor_service_fee_percentage_amount = $vendor_service_fee_percentage_amount + $service_fee_percentage_amount;
                        $payable_amount += $service_fee_percentage_amount;
                        $total_service_fee = $total_service_fee + $service_fee_percentage_amount;
                        $vendor_payable_amount += $service_fee_percentage_amount;
                    }


                    if (@$getAdditionalPreference['is_rental_weekly_monthly_price']) {
                        $service_fee_percentage_amount        = 0;
                        $vendor_service_fee_percentage_amount = 0;
                        $total_amount = $payable_amount = $request->total_amount;
                        $total_service_fee = 0;
                        $vendor_payable_amount = 0;
                    }

                    $cart_addons = CartAddon::where('cart_product_id', $vendor_cart_product->id)->get();
                    if ($cart_addons) {
                        foreach ($cart_addons as $cart_addon) {
                            $orderAddon = new OrderProductAddon();
                            $orderAddon->addon_id = $cart_addon->addon_id;
                            $orderAddon->option_id = $cart_addon->option_id;
                            $addon_amount = AddonOption::find($cart_addon->option_id)->price ?? 0;
                            $orderAddon->order_product_id = $order_product->id;
                            $orderAddon->save();
                        }

                        // $addon_amount CartAddon::where('cart_product_id', $vendor_cart_product->id)->delete();
                    }
                    // array_push($addonArray, $vendor_cart_product->vendor_id);
                    if (isset($vendor_cart_product->product->taxCategory)) {
                        foreach ($vendor_cart_product->product->taxCategory->taxRate as $tax_rate_detail) {
                            $rate = $tax_rate_detail->tax_rate;
                        }
                    }
                } //End products loop

                $payable_amount += $vendor_total_container_charges;
                if (@$getAdditionalPreference['is_rental_weekly_monthly_price']) {
                    $payable_amount = $request->total_amount;
                }

                $coupon_id = null;
                $coupon_name = null;
                $actual_amount = $vendor_amount;
                if ($vendor_cart_product->coupon) {
                    $coupon_id = $vendor_cart_product->coupon->promo->id;
                    if ($vendor_cart_product->coupon->promo->paid_by_vendor_admin == 0) {
                        $coupon_paid_by = 0;
                    }

                    $coupon_name = $vendor_cart_product->coupon->promo->name;
                    // -------------Coupon Related discount calculations start here----------------------
                    // ----fixed amount----------
                    if ($vendor_cart_product->coupon->promo->promo_type_id == 2) {
                        $amount = round($vendor_cart_product->coupon->promo->amount);
                        $total_discount += $amount;
                        $vendor_payable_amount -= $amount;
                        $vendor_discount_amount += $amount;
                    } else {
                        // ----Percent amount----------
                        $percentage_amount = ($actual_amount * $vendor_cart_product->coupon->promo->amount / 100);
                        $total_discount += $percentage_amount;
                        $vendor_payable_amount -= $percentage_amount;
                        $vendor_discount_amount += $percentage_amount;
                    }
                    if ($vendor_cart_product->coupon->promo->allow_free_delivery == 1) {
                        $vendor_discount_amount = $vendor_discount_amount + $delivery_fee;
                        $vendor_payable_amount = $vendor_payable_amount - $delivery_fee;
                        $total_discount += $delivery_fee;
                        $deliveryfeeOnCoupon = 1;
                    }

                    // -------------Coupon Related discount calculations Ends here----------------------
                }
                // End applying service fee on vendor products total
                // $total_service_fee = $total_service_fee + $vendor_service_fee_percentage_amount;
                $OrderVendor->service_fee_percentage_amount = $vendor_service_fee_percentage_amount;

                // $total_delivery_fee += $delivery_fee;
                $vendor_payable_amount += $additionalPrice;
                $vendor_payable_amount += $delivery_fee;
                $vendor_payable_amount += $vendor_taxable_amount;

                $payable_amount += $additionalPrice;
                $getAdditionalPreference = getAdditionalPreference(['is_rental_weekly_monthly_price']);
                if (@$getAdditionalPreference['is_rental_weekly_monthly_price']) {
                    // $payable_amount = $request->total_amount;
                    $payable_amount = !empty($request->total_amount) ? $request->total_amount : 0;
                    $vendor_payable_amount = $request->total_amount;
                }
                // dump("+AdditionalPrice ".$additionalPrice."/- ----".$payable_amount);
                $totalAdditionalPrice += $additionalPrice;

                $OrderVendor->coupon_id = $coupon_id;
                $OrderVendor->coupon_paid_by = $coupon_paid_by ?? 1;
                $OrderVendor->coupon_code = $coupon_name;
                $OrderVendor->order_status_option_id = 1;
                $OrderVendor->delivery_fee = $delivery_fee;
                $OrderVendor->subtotal_amount = $actual_amount;
                $OrderVendor->discount_amount = $vendor_discount_amount;
                if($deliveryfeeOnCoupon)
                    $vendor_discount_amount =  $vendor_discount_amount - $delivery_fee;

                // check if is_tax_price_inclusive is on than no tax
                if (!$additionalPreferences->is_tax_price_inclusive) {
                    $new_vendor_taxable_amount = number_format((($actual_amount - $vendor_discount_amount) * $rate) / 100, 2);
                } else {
                    $new_vendor_taxable_amount = number_format((($actual_amount - $vendor_discount_amount) * $rate) / (100 + $rate), 2);
                }

                $new_vendor_taxable_amount = str_replace(',', '', $new_vendor_taxable_amount);
                $new_vendor_taxable_amount = floatval($new_vendor_taxable_amount);
                $total_taxable_amount += $new_vendor_taxable_amount;
                // $OrderVendor->taxable_amount = $vendor_taxable_amount;

                $fixedFeeAmount = 0.00;
                if (isset($vendor_cart_product->vendor->fixed_fee_amount)) {
                    $fixedFeeAmount = $vendor_cart_product->vendor->fixed_fee_amount;
                }
                $OrderVendor->fixed_fee = $fixedFeeAmount;
                $OrderVendor->additional_price = $additionalPrice;
                $OrderVendor->taxable_amount = $new_vendor_taxable_amount + $tax_amount;
                $OrderVendor->payment_option_id = $request->payment_option_id;
                $OrderVendor->subtotal_amount = $OrderVendor->subtotal_amount - $bid_vendor_discount ?? 0;



                if ($action == 'p2p') {
                    $OrderVendor->payable_amount = $request->$total_amount;
                } else {
                    $OrderVendor->payable_amount = $vendor_payable_amount + $fixedFeeAmount + $new_vendor_taxable_amount;
                }
                if (@$getAdditionalPreference['is_rental_weekly_monthly_price']) {
                    $OrderVendor->subtotal_amount = $OrderVendor->payable_amount = $request->total_amount;
                }
                $OrderVendor->total_markup_price = $vendor_markup_amount;
                $OrderVendor->total_container_charges = $vendor_total_container_charges;

                $vendor_subs_disc_percent = isset($vendor_cart_product->vendor->subscription_discount_percent) ? $vendor_cart_product->vendor->subscription_discount_percent : 0;
                $deliveryfee_ifnot_discounted = ($deliveryfeeOnCoupon == 0) ? $delivery_fee : 0;
                $subs_discount_arr = $this->calCulateSubscriptionDiscount($user->id, $deliveryfee_ifnot_discounted, $OrderVendor->payable_amount, $vendor_subs_disc_percent);
                $subs_discount_admin = $subs_discount_arr['admin'] + $subs_discount_arr['delivery_discount'];
                $subs_discount_vendor = $subs_discount_arr['vendor'];

                $OrderVendor->subscription_discount_admin  = $subs_discount_admin;
                $OrderVendor->subscription_discount_vendor = $subs_discount_vendor;

                $total_subscription_discount = $total_subscription_discount + $subs_discount_admin + $subs_discount_vendor;
                $OrderVendor->is_restricted = $is_restricted;
                $OrderVendor->bid_discount = $bid_vendor_discount ?? 0;
                $Order_bid_discount += $bid_vendor_discount ?? 0;
                $vendor_info = Vendor::where('id', $vendor_id)->first();
                if ($vendor_info) {
                    if (isset($coupon_paid_by)) {
                        $actual_amount = $actual_amount - $vendor_discount_amount;
                    }
                    if (($vendor_info->commission_percent) != null && $actual_amount > 0) {
                        $actual_amountComm = $actual_amount - $vendor_markup_amount;

                        $OrderVendor->admin_commission_percentage_amount = round($vendor_info->commission_percent * ($actual_amountComm / 100), 2);
                    }
                    if (($vendor_info->commission_fixed_per_order) != null && $actual_amount > 0) {
                        $OrderVendor->admin_commission_fixed_amount = $vendor_info->commission_fixed_per_order;
                    }
                    if ($vendor_info->fixed_fee_amount > 0) {
                        $fixed_fee_amount = $fixed_fee_amount + $vendor_info->fixed_fee_amount;
                    }
                }
                $OrderVendor->save();
                // pr($OrderVendor->toArray());
                $order_status = new VendorOrderStatus();
                $order_status->order_id = $order->id;
                $order_status->vendor_id = $vendor_id;
                $order_status->order_vendor_id = $OrderVendor->id;
                $order_status->order_status_option_id = 1;
                $order_status->save();
            } // End cart product loop
            // echo "loop end";
            // $loyalty_points_earned = LoyaltyCard::getLoyaltyPoint('', $payable_amount);

            // Total Discount
            $total_discount = $total_discount + $total_subscription_discount;

            $order->total_amount = $total_amount - $Order_bid_discount ?? 0;
            if (!empty($vendor_cart_product->recurring_booking_time)) {
                $order->total_amount =  $request->total_amount;
            }
            if (@$getAdditionalPreference['is_rental_weekly_monthly_price']) {
                $order->total_amount = $request->total_amount;
            }

            if (isset($vendor_cart_product)) {

                if ($vendor_cart_product->recurring_day_data && !empty($vendor_cart_product->recurring_day_data)) {
                    $date       = explode(",", $vendor_cart_product->recurring_day_data);
                    if ($vendor_cart_product->recurring_booking_type == 1 || $vendor_cart_product->recurring_booking_type == 2 || $vendor_cart_product->recurring_booking_type == 3 || $vendor_cart_product->recurring_booking_type == 4) {
                        $days_count                         =  count($date);
                        $pvariant_new_price                 =   $order->total_amount * $days_count;
                        //$order->total_amount                =  decimal_format($pvariant_new_price);
                    }
                }
            }



            $order->total_discount = $total_discount;
            // $order->taxable_amount = $taxable_amount;
            // $new_taxable_amount = number_format(($actual_amount * $rate) / 100, 2);
            $order->taxable_amount = $total_taxable_amount;

            $payable_amount = $payable_amount + $total_delivery_fee - $total_discount;

            // ------------ move up
            $tip_amount = 0;
            if (isset($request->tip)) {
                $request->tip = str_replace(',', '', $request->tip);
                $tip_amount = floatval($request->tip);
                if (($tip_amount != '') && ($tip_amount > 0)) {
                    $tip_amount = ($tip_amount / $customerCurrency->doller_compare) * $clientCurrency->doller_compare;
                    $order->tip_amount = $tip_amount;
                }
            }
            $payable_amount = $payable_amount + $tip_amount + $total_other_taxes + $security_amount;
            if($total_other_taxes < 0)
              $payable_amount += $order->taxable_amount;

              // ---------------------------------------
            $payable_amount = ($payable_amount + $fixed_fee_amount) - $loyalty_amount_saved ;

            // if(!empty($vendor_cart_product->recurring_booking_time)){
            //     $payable_amount = ($request->total_amount + $fixed_fee_amount) - $loyalty_amount_saved ;
            // }

            $ex_gateways_wallet = [4,5,36, 40, 41, 22]; // stripe,mycash,userede,openpay,paystack

            // $tip_amount = 0;
            // if (isset($request->tip)) {
            // $request->tip = str_replace(',', '', $request->tip);
            // $tip_amount = floatval($request->tip);
            // if( ($tip_amount != '') && ($tip_amount > 0) ){
            // $tip_amount = ($tip_amount / $customerCurrency->doller_compare) * $clientCurrency->doller_compare;
            // $order->tip_amount = $tip_amount;
            // }

            // }
            // echo " Total payable_amount1=".$payable_amount."; <br>";
            // echo " tip_amount=".$tip_amount." fixed_fee_amount=".$fixed_fee_amount." total_taxable_amount=".$total_taxable_amount."; <br>";

            // $payable_amount = $payable_amount + $tip_amount + $total_taxable_amount+$total_other_taxes;
            // $payable_amount = $payable_amount + $tip_amount + $total_other_taxes;

            $wallet_amount_used = 0;
            if ($user) {
                if ($user->balanceFloat > 0) {
                    $wallet = $user->wallet;
                    $wallet_amount_used = $user->balanceFloat;
                    if ($wallet_amount_used > $payable_amount) {
                        $wallet_amount_used = $payable_amount;
                    }
                    $order->wallet_amount_used = $wallet_amount_used;
                    // Deduct wallet amount if payable amount is successfully done on gateway
                    if (($wallet_amount_used > 0) && (!in_array($request->payment_option_id, $ex_gateways_wallet))) {
                        $wallet->withdrawFloat($order->wallet_amount_used, [
                            'Wallet has been <b>debited</b> for order number <b>' . $order->order_number . '</b>'
                        ]);
                    }
                }
            }

            $payable_amount = $payable_amount - $wallet_amount_used;
            if (!empty($vendor_cart_product->recurring_booking_time)) {
                $payable_amount =  $request->total_amount - $wallet_amount_used;
            }
            //echo  " Total payable_amount2=".$payable_amount."; <br>";
            $order->total_service_fee = $total_service_fee;
            $order->total_delivery_fee = $total_delivery_fee;
            $order->loyalty_points_used = $loyalty_points_used ?? 0;
            $order->loyalty_amount_saved = $loyalty_amount_saved ?? 0;
            $order->subscription_discount = $total_subscription_discount;
            // echo " total_subscription_discount=".$total_subscription_discount."; <br>";
            $order->loyalty_points_earned = $loyalty_points_earned['per_order_points'] ?? 0;
            $order->loyalty_membership_id = $loyalty_points_earned['loyalty_card_id'] ?? 0;
            $order->rental_protection_amount = $rentalProtectionPrice;
            $order->booking_option_price = $bookingOptionPrice;
            // echo " total_service_fee=".$total_service_fee." total_delivery_fee=".$total_delivery_fee;
            // echo " Total payable_amount 3=".$payable_amount."; <br>";
            $order->scheduled_date_time = $OrderVendor->scheduled_date_time ? $OrderVendor->scheduled_date_time : null;

            $order->scheduled_slot = (($OrderVendor->schedule_slot) ? $OrderVendor->schedule_slot : null);
          
            if ($order->scheduled_date_time) {
                $date =    explode(" ",$order->scheduled_date_time);
                $order->scheduled_date_time = $date[0];
            }
            $order->dropoff_scheduled_slot = (($cart->dropoff_scheduled_slot) ? $cart->dropoff_scheduled_slot : null);
            $order->luxury_option_id = $luxury_option->id ?? '';
            $payable_amount = $payable_amount - $Order_bid_discount ?? 0;
            if (!$additionalPreferences->is_tax_price_inclusive) {
                $slot_based_price =   isset($slot_based_price) ? $slot_based_price : 0;
                $orderTotalPay = decimal_format($payable_amount + $slot_based_price);
                // gift card calculation
                if ($giftCardTotalAmount > 0 && $orderTotalPay > 0) {
                    $calCulateGiftCard = $this->calCulateGiftCard($orderTotalPay, $giftCardTotalAmount);
                    $orderTotalPay = @$calCulateGiftCard['totalPaybel'];
                    $giftCardUsedAmount = @$calCulateGiftCard['used_GiftCardAmount'];
                }
                $order->payable_amount = $orderTotalPay;
            } else {
                $slot_based_price =   isset($slot_based_price) ? $slot_based_price : 0;

                // Slot based price added to payable amount column
                $order->payable_amount = decimal_format($payable_amount + $slot_based_price);

                $orderTotalPay = decimal_format($payable_amount - $total_other_taxes);

                // gift card calculation
                if ($giftCardTotalAmount > 0 && $orderTotalPay > 0) {
                    $calCulateGiftCard = $this->calCulateGiftCard($orderTotalPay, $giftCardTotalAmount);
                    $orderTotalPay = @$calCulateGiftCard['totalPaybel'];
                    $giftCardUsedAmount = @$calCulateGiftCard['used_GiftCardAmount'];
                }
                $order->payable_amount = $orderTotalPay;
            }

            if (@$getAdditionalPreference['is_rental_weekly_monthly_price']) {
                $order->payable_amount = $request->total_amount;
            }
            if (getAdditionalPreference([
                'is_gift_card'
            ])['is_gift_card'] == 1) {
                $order->gift_card_id = $cart->gift_card_id;

                $order->gift_card_amount = decimal_format($giftCardUsedAmount);
                $order->gift_card_code = $userGiftCardCode;
                Cart::where('id', $cart->id)->update([
                    'gift_card_id' => null
                ]);
                if ($UserGiftCardId && ($giftCardUsedAmount > 0)) {
                    $giftcard = UserGiftCard::where([
                        'id' => $UserGiftCardId
                    ])->update([
                        'is_used' => 1
                    ]);
                }
            }

            // Advance Book Token Amount
            // mohit sir branch code added by sohail
            $getAdditionalPreference = getAdditionalPreference([
                'advance_booking_amount',
                'advance_booking_amount_percentage'
            ]);
            if (!empty($getAdditionalPreference['advance_booking_amount']) && !empty($getAdditionalPreference['advance_booking_amount_percentage']) && ($getAdditionalPreference['advance_booking_amount_percentage'] > 0) && ($getAdditionalPreference['advance_booking_amount_percentage'] < 101)) {
                $advanceAmount = $payable_amount * $getAdditionalPreference['advance_booking_amount_percentage'] / 100;
                $order->advance_amount = number_format($advanceAmount, 2);
            }
            // till here

            $order->fixed_fee_amount        = $fixed_fee_amount;
            $order->additional_price        = $totalAdditionalPrice;
            $order->total_container_charges = $total_container_charges;
            $order->is_long_term            = $is_long_term_order;
            if (($payable_amount == 0) || (($request->has('transaction_id')) && (!empty($request->transaction_id)))) {
                $order->payment_status  = 1;
            }
            $order->bid_discount        = $Order_bid_discount ?? 0;

            if (!empty($vendor_cart_product->recurring_booking_time)) {
                $user_timezone          =   $timezone;
                $recurring_booking_time =   convertDateTimeInTimeZone($vendor_cart_product->recurring_booking_time, $user_timezone, 'H:i');
                $order->recurring_booking_type  = $vendor_cart_product->recurring_booking_type;
                $order->recurring_week_day      = json_encode($vendor_cart_product->recurring_week_day);
                $order->recurring_week_type     = $vendor_cart_product->recurring_week_type;
                $order->recurring_day_data      = $vendor_cart_product->recurring_day_data;
                $order->recurring_booking_time  = $recurring_booking_time;
            }

            if ((FacadesSession::get('vendorType') == "rental")) {
                $order->payable_amount = $request->total_amount;
                $order->total_amount = $request->total_amount;
            }
            $order->save();
            if (isset($datesInRange)) {

                ProductAvailability::where('product_id', @$order_product->product_id)->whereIn('date_time', $datesInRange)->update(['not_available' => 1]);
            }
            OrderFiles::where('cart_id', $cart->id)->update(['order_id' => $order->id, 'cart_id' => '']);

            // $this->sendOrderNotification($user->id, $vendor_ids);

            $ex_gateways = [
                4,
                5,
                7,
                8,
                9,
                10,
                12,
                13,
                15,
                17,
                18,
                19,
                20,
                21,
                23,
                24,
                25,
                26,
                28,
                29,
                30,
                31,
                32,
                34,
                35,
                36,
                37,
                39,
                40,
                41,
                42,
                43,
                44,
                45,
                46,
                47,
                52,
                53,
                54,
                56,
                22
            ]; // stripe, mobbex,yoco,pointcheckout,razorpay,simplified,square,pagarme, checkout,Authourize, stripe_fpx,KongaPay, cashfree,easubuzz,vnpay, payu,mycash,Stipre_oxxo,stripe_ideal, obo

            if (!in_array($request->payment_option_id, $ex_gateways) || (isset($request->is_postpay) && $request->is_postpay == 1)) {

                // Send Email to customer
                //Send Email to customer
                $request->request->add(['type' => $action]);
                // $this->sendSuccessEmail($request, $order);
                // Send Email to Vendor
                foreach ($cart_products->groupBy('vendor_id') as $vendor_id => $vendor_cart_products) {
                    $this->sendSuccessEmail($request, $order, $vendor_id);
                }


                Cart::where('id', $cart->id)->update([
                    'schedule_type' => null,
                    'scheduled_date_time' => null,
                    'comment_for_pickup_driver' => null,
                    'comment_for_dropoff_driver' => null,
                    'comment_for_vendor' => null,
                    'schedule_pickup' => null,
                    'schedule_dropoff' => null,
                    'specific_instructions' => null,
                    'order_id' => NULL
                ]);

                CaregoryKycDoc::where('cart_id', $cart->id)->update([
                    'ordre_id' => $order->id,
                    'cart_id' => ''
                ]);

                CartAddon::where('cart_id', $cart->id)->delete();
                CartCoupon::where('cart_id', $cart->id)->delete();
                // CartProduct::where('cart_id', $cart->id)->delete();
                $cart_product_ids = $cart_products->pluck('id');
                CartProduct::query()->whereIn('id', $cart_product_ids)->delete();
                CartProductPrescription::where('cart_id', $cart->id)->delete();
                CartDeliveryFee::where('cart_id', $cart->id)->delete();
                CartRentalProtection::where('cart_id', $cart->id)->delete();
                CartBookingOption::where('cart_id', $cart->id)->delete();
                // send sms
                // $this->sendSuccessSMS($request, $order);
            }

            if (count($tax_category_ids)) {
                foreach ($tax_category_ids as $tax_category_id) {
                    $order_tax = new OrderTax();
                    $order_tax->order_id = $order->id;
                    $order_tax->tax_category_id = $tax_category_id;
                    $order_tax->save();
                }
            }
            if (($request->payment_option_id != 1 && (!isset($request->is_postpay) || $request->is_postpay == 0)) && ($request->payment_option_id != 2 && (!isset($request->is_postpay) || $request->is_postpay == 0)) && ($request->has('transaction_id')) && (!empty($request->transaction_id))) {
                Payment::insert([
                    'date' => date('Y-m-d'),
                    'order_id' => $order->id,
                    'transaction_id' => $request->transaction_id,
                    'balance_transaction' => $order->payable_amount,
                    'type' => 'cart'
                ]);
            }
            $order = $order->with([
                'paymentOption',
                'user_vendor',
                'vendors:id,order_id,vendor_id',
                'vendors.vendor',
                'products'
            ])
                ->where('order_number', $order->order_number)
                ->first();
            if (!in_array($request->payment_option_id, $ex_gateways) || (isset($request->is_postpay) && $request->is_postpay == 1)) {
                if (!empty($order->vendors)) {
                    foreach ($order->vendors as $vendor_value) {
                        $vendorDetail = $vendor_value->vendor;
                        if ($vendorDetail->auto_accept_order == 0 && $vendorDetail->auto_reject_time > 0) {
                            $clientDetail = CP::on('mysql')->where([
                                'code' => $preferences->client_code
                            ])->first();
                            AutoRejectOrderCron::on('mysql')->create([
                                'database_host' => $clientDetail->database_path,
                                'database_name' => $clientDetail->database_name,
                                'database_username' => $clientDetail->database_username,
                                'database_password' => $clientDetail->database_password,
                                'order_vendor_id' => $vendor_value->id,
                                'auto_reject_time' => Carbon::now()->addMinute($vendorDetail->auto_reject_time)
                            ]);
                        }
                        $vendor_order_detail = $this->minimize_orderDetails_for_notification($order->id, $vendor_value->vendor_id);
                        $user_vendors = UserVendor::where([
                            'vendor_id' => $vendor_value->vendor_id
                        ])->pluck('user_id');
                        if ($request->payment_option_id == 1 || $order->is_postpay == 1 || $order->payment_status == 1) {

                            $this->sendOrderPushNotificationVendors($user_vendors, $vendor_order_detail);
                        }



                        if (!empty($additionalPreferences->stock_notification_before) && $additionalPreferences->stock_notification_before == 1) {
                            $vendor_id = $this->CheckProductStockLimit($order->id, $additionalPreferences->stock_notification_qunatity);
                            if (!empty($vendor_id)) {
                                $this->sendProductStockOutPushNotificationVendors($vendor_id, $vendor_order_detail);
                            }
                        }
                    }
                    $vendor_order_detail = $this->minimize_orderDetails_for_notification($order->id);

                    $super_admin = User::where('is_superadmin', 1)->pluck('id');
                    if ($request->payment_option_id == 1 || $order->is_postpay == 1 || $order->payment_status == 1) {
                        $this->sendOrderPushNotificationVendors($super_admin, $vendor_order_detail);
                    }
                } else {
                    $vendor_order_detail = $this->minimize_orderDetails_for_notification($order->id);

                    $getAllVendorAdmin = Order::join('order_vendors as ov', 'ov.order_id', 'orders.id')->leftjoin('user_vendors as uv', 'uv.vendor_id', 'ov.vendor_id')
                        ->where('order_number', $order->order_number)
                        ->pluck('uv.user_id');

                    $super_admin = User::where('is_superadmin', 1)->pluck('id');

                    if (!empty($getAllVendorAdmin)) {
                        $admins = $super_admin->merge($getAllVendorAdmin);
                        $super_admin = $admins->all();
                    }

                    $this->sendOrderPushNotificationVendors($super_admin, $vendor_order_detail);

                    // $user_admins = User::where(function ($query) {
                    // $query->where(['is_superadmin' => 1]);
                    // })->pluck('id')->toArray();
                    // $user_vendors = [];
                    // if (!empty($order->user_vendor) && count($order->user_vendor) > 0) {
                    // $user_vendors = $order->user_vendor->pluck('user_id')->toArray();
                    // }
                    // $order->admins = array_unique(array_merge($user_admins, $user_vendors));
                    // $this->sendOrderPushNotificationVendors($order->admins, ['id' => $order->id]);
                }
            }

            // if(){
            // $this->bookingSlot($vendor_cart_products);
            // }

            DB::commit();
            $blockchain_route = ClientPreferenceAdditional::where('key_name', 'blockchain_route_formation')->first();
            $order_data = Order::select('id', 'user_id')->with([
                'ordervendor'
            ])
                ->where('order_number', $order->order_number)
                ->first();
            if (isset($blockchain_route) && ($blockchain_route->key_value == 1)) {
                @$this->saveBlockchainOrderDetail($order_data);
            }
            $this->sendSuccessSMS($request, $order);
            // $hub_key = @getAdditionalPreference(['is_marg_enable']);

            return $this->successResponse($order);
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage, 402);
        }
    }

    public function sendOrderNotification($id, $vendorIds)
    {
        $super_admin = User::where('is_superadmin', 1)->pluck('id');
        $user_vendors = UserVendor::whereIn('vendor_id', $vendorIds)->pluck('user_id');
        $devices = UserDevice::whereNotNull('device_token')->where('user_id', $id)->pluck('device_token');
        foreach ($devices as $device) {
            $token[] = $device;
        }
        $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $user_vendors)->pluck('device_token');
        foreach ($devices as $device) {
            $token[] = $device;
        }
        $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $super_admin)->pluck('device_token');
        foreach ($devices as $device) {
            $token[] = $device;
        }
        // $token[] = "d4SQZU1QTMyMaENeZXL3r6:APA91bHoHsQ-rnxsFaidTq5fPse0k78qOTo7ZiPTASiH69eodqxGoMnRu2x5xnX44WfRhrVJSQg2FIjdfhwCyfpnZKL2bHb5doCiIxxpaduAUp4MUVIj8Q43SB3dvvvBkM1Qc1ThGtEM";
        // $from = env('FIREBASE_SERVER_KEY');
        $notification_content = NotificationTemplate::where('id', 1)->first();
        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
        if ($notification_content && !empty($token) && !empty($client_preferences->fcm_server_key)) {

            $data = [
                "registration_ids" => $token,
                "notification" => [
                    'title' => $notification_content->label,
                    'body' => $notification_content->content
                ]
            ];

            sendFcmCurlRequest($data);
        }
    }

    public function sendOrderPushNotificationVendors($user_ids, $orderData)
    {
        $devices = UserDevice::where('is_vendor_app', 0)->whereNotNull('device_token')
            ->whereIn('user_id', $user_ids)
            ->pluck('device_token')
            ->toArray();

        $from = '';
        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon', 'vendor_fcm_server_key')->first();
        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
            $from = $client_preferences->fcm_server_key;
        }
        $notification_content = NotificationTemplate::where('id', 4)->first();
        if ($notification_content) {
            $body_content = str_ireplace("{order_id}", "#" . $orderData->order_number, $notification_content->content);
            // dd($body_content);
            $data = [
                "registration_ids" => $devices,
                "notification" => [
                    'title' => $notification_content->subject,
                    'body' => $body_content,
                    'sound' => "notification.wav",
                    "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                    // 'click_action' => route('order.index'),
                    "android_channel_id" => "sound-channel-id"
                ],
                "data" => [
                    'title' => $notification_content->subject,
                    'body' => $notification_content->content,
                    'data' => $orderData,
                    'order_id' => $orderData->id,
                    'type' => "order_created"
                ],
                "priority" => "high"
            ];
            if (!empty($from)) {
                // helper function
                sendFcmCurlRequest($data);
            }

            // Individual Vendor App User Token
            $vendorAppUserDevices = UserDevice::where('is_vendor_app', 1)->whereNotNull('device_token')
                ->whereIn('user_id', $user_ids)
                ->pluck('device_token')
                ->toArray();

            if (!empty($vendorAppUserDevices) && !empty($client_preferences->vendor_fcm_server_key)) {

                $from = $client_preferences->vendor_fcm_server_key;
                $data['registration_ids'] = $vendorAppUserDevices;

                $result = sendFcmCurlRequest($data, $from,1);
                //// Log::info($result);
            }
        }
    }



    public function makePayment(Request $request)
    {
        $token = $request->stripeToken;
        $gateway = Omnipay::create('Stripe');
        $gateway->setApiKey('sk_test_51IhpwhSFHEA938FwRPiQSAH5xF6DcjO5GCASiud9cGMJ0v8UJyRfCb7IQAMbXbuPMe7JphA1izxZOsIclvmOgqUV00Zpk85xfl');
        $formData = [
            'number' => $request->card_num,
            'description' => $request->first_name,
            'expiryMonth' => $request->exp_month,
            'expiryYear' => $request->exp_year,
            'cvv' => $request->cvc
        ];
        $response = $gateway->purchase([
            'amount' => $request->amount,
            'currency' => 'INR',
            'card' => $formData,
            'token' => $token
        ])->send();
        if ($response->isSuccessful()) {
            $cart = Cart::where('user_id', Auth::user()->id)->first();
            $payment = new Payment();
            $payment->amount = $request->amount;
            $payment->transaction_id = $response->getData()['id'];
            $payment->balance_transaction = $response->getData()['balance_transaction'];
            $payment->type = "card";
            $payment->cart_id = $cart->id;
            $payment->save();
            $this->orderSave($request, "2", "1");
        } elseif ($response->isRedirect()) {
            $response->redirect();
        } else {
            exit($response->getMessage());
        }
    }

    public function getDeliveryFeeDispatcher($vendor_id, $user_id)
    {
        try {
            $dispatch_domain = $this->checkIfLastMileOn();
            if ($dispatch_domain && $dispatch_domain != false) {
                $customer = User::find($user_id);
                $cus_address = UserAddress::where('user_id', $user_id)->orderBy('is_primary', 'desc')->first();
                if ($cus_address) {
                    $tasks = array();
                    $vendor_details = Vendor::find($vendor_id);
                    $location[] = array(
                        'latitude' => $vendor_details->latitude ?? 30.71728880,
                        'longitude' => $vendor_details->longitude ?? 76.80350870
                    );
                    $location[] = array(
                        'latitude' => $cus_address->latitude ?? 30.717288800000,
                        'longitude' => $cus_address->longitude ?? 76.803508700000
                    );
                    $postdata = [
                        'locations' => $location
                    ];
                    $client = new GCLIENT([
                        'headers' => [
                            'personaltoken' => $dispatch_domain->delivery_service_key,
                            'shortcode' => $dispatch_domain->delivery_service_key_code,
                            'content-type' => 'application/json'
                        ]
                    ]);
                    $url = $dispatch_domain->delivery_service_key_url;
                    $res = $client->post($url . '/api/get-delivery-fee', [
                        'form_params' => ($postdata)
                    ]);
                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['message'] == 'success') {
                        return $response['total'];
                    }
                }
            }
        } catch (\Exception $e) {
            // print_r($e->getMessage());
            // die;
        }
    }

    # check if last mile delivery on
    public function checkIfLastMileOn()
    {
        $preference = ClientPreference::first();
        if ($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url)) {
            return $preference;
        } else {
            return false;
        }
    }

    public function postPaymentPlaceOrder(Request $request, $domain = '')
    {
        if ((isset($request->auth_token)) && (!empty($request->auth_token))) {
            return $this->placeOrder($request);
        } else {
            return $this->errorResponse('Invalid User', 402);
        }
    }

    # if vendor selected auto accepted order
    public function autoAcceptOrderIfOn($order_id)
    {
        $order_vendors = OrderVendor::where('order_id', $order_id)->whereHas('vendor', function ($q) {
            $q->where('auto_accept_order', 1);
        })
            ->get();
        $orderData = Order::find($order_id);
        $user = Auth::user();
        if (!$user) {
            $user_id = $orderData->user_id;
            $user = User::find($user_id);
        }
        foreach ($order_vendors as $ov) {
            //// Log::info($ov->order_id);
            $request = $ov;

            DB::beginTransaction();
            // try {

            $request->order_id = $ov->order_id;
            //// Log::info($ov->order_id);
            //// Log::info($request->order_id);
            $request->vendor_id = $ov->vendor_id;
            $request->order_vendor_id = $ov->id;
            $request->status_option_id = 2;
            // $timezone = Auth::user()->timezone;
            //// Log::info(Auth::user());
            $vendor_order_status_check = VendorOrderStatus::where('order_id', $request->order_id)->where('vendor_id', $request->vendor_id)
                ->where('order_status_option_id', $request->status_option_id)
                ->first();
            //// Log::info($vendor_order_status_check);
            if (!$vendor_order_status_check) {
                $vendor_order_status = new VendorOrderStatus();
                $vendor_order_status->order_id = $request->order_id;
                $vendor_order_status->vendor_id = $request->vendor_id;
                $vendor_order_status->order_vendor_id = $request->order_vendor_id;
                $vendor_order_status->order_status_option_id = $request->status_option_id;
                $vendor_order_status->save();

                if ($request->status_option_id == 2) {
                    if ($request->shipping_delivery_type == 'D') {
                        $order_dispatch = $this->checkIfanyProductLastMileon($request);
                        if ($order_dispatch && $order_dispatch == 1) {
                            $stats = $this->insertInVendorOrderDispatchStatus($request);
                        }
                    } elseif ($request->shipping_delivery_type == 'L') {
                        // Create Shipping place order request for Lalamove
                        $order_lalamove = $this->placeOrderRequestlalamove($request);
                    } elseif ($request->shipping_delivery_type == 'SR') {
                        // Create Shipping place order request for Shiprocket
                        $order_ship = $this->placeOrderRequestShiprocket($request);
                    } elseif ($request->shipping_delivery_type == 'DU') {
                        // Create Shipping place order request for Shiprocket
                        $order_ship = $this->placeOrderRequestDunzo($request);
                    } elseif ($request->shipping_delivery_type == 'M') {
                        // Create Shipping place order request for Shiprocket
                        $order_ship = $this->placeOrderRequestAhoy($request);
                    } elseif ($request->shipping_delivery_type == 'D4') {
                        $order_ship = $this->placeOrderRequestD4B($request);
                    }
                }
                OrderVendor::where('vendor_id', $request->vendor_id)->where('order_id', $request->order_id)->update([
                    'order_status_option_id' => $request->status_option_id
                ]);
                $this->ProductVariantStock($order_id);
                DB::commit();
                $this->sendSuccessNotification($user->id, $request->vendor_id);

                $customer = User::find($user->id);
                if (getAdditionalPreference([
                    'is_tracking_url'
                ])['is_tracking_url'] == 1) {
                    $this->sendTrackingUrlSMS($orderData, $request->order_id);
                }
            }
        }
    }
    /// ******************  check If any D4b Mile on   ************************ ///////////////
    public function placeOrderRequestD4B($request)
    {
        $ship = new D4BDunzoController();
        //Create Shipping place order request for Shiprocket
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);
        if ($checkdeliveryFeeAdded && $checkdeliveryFeeAdded->delivery_fee > 0.00) {
            $order_d4dunzo = $ship->createOrderRequestD4BDunzo($checkOrder->user_id, $checkdeliveryFeeAdded);
        }
        if ($order_d4dunzo['state'] == 'created') {
            $up_web_hook_code = OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])
                ->update([
                    'web_hook_code' => $order_d4dunzo['task_id'],
                ]);
            return 1;
        }
        return 2;
    }
    /// ******************  check If any Product Last Mile on   ************************ ///////////////
    // / ****************** check If any Product Last Mile on ************************ ///////////////
    public function placeOrderRequestShiprocket($request)
    {
        $ship = new ShiprocketController();
        $is_place_order_delivery_zero = getAdditionalPreference([
            'is_place_order_delivery_zero'
        ])['is_place_order_delivery_zero'];
        // Create Shipping place order request for Shiprocket
        $checkdeliveryFeeAdded = OrderVendor::where([
            'order_id' => $request->order_id,
            'vendor_id' => $request->vendor_id
        ])->first();
        $checkOrder = Order::findOrFail($request->order_id);
        if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
            $order_ship = $ship->createOrderRequestShiprocket($checkOrder->user_id, $checkdeliveryFeeAdded);
        }
        if ($order_ship->order_id) {
            $up_web_hook_code = OrderVendor::where([
                'order_id' => $checkOrder->id,
                'vendor_id' => $request->vendor_id
            ])->update([
                'ship_order_id' => $order_ship->order_id,
                'ship_shipment_id' => $order_ship->shipment_id,
                'ship_awb_id' => $order_ship->awb_code
            ]);
            return 1;
        }

        return 2;
    }

    public function placeOrderRequestAhoy($request)
    {
        $data = new AhoyController();
        $is_place_order_delivery_zero = getAdditionalPreference([
            'is_place_order_delivery_zero'
        ])['is_place_order_delivery_zero'];
        // Create Shipping place order request for Dunzo
        $checkdeliveryFeeAdded = OrderVendor::where([
            'order_id' => $request->order_id,
            'vendor_id' => $request->vendor_id
        ])->first();
        $checkOrder = Order::findOrFail($request->order_id);
        if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
            $orderDetails = $data->createPreOrderRequestAhoy($checkOrder->user_id, $checkdeliveryFeeAdded);
        }

        if (isset($orderDetails->orderId)) {
            $up_web_hook_code = OrderVendor::where([
                'order_id' => $checkOrder->id,
                'vendor_id' => $request->vendor_id
            ])->update([
                'web_hook_code' => $orderDetails->orderId
            ]);
            return 1;
        }

        return 2;
    }

    public function placeOrderRequestDunzo($request)
    {
        $data = new DunzoController();
        $is_place_order_delivery_zero = getAdditionalPreference([
            'is_place_order_delivery_zero'
        ])['is_place_order_delivery_zero'];
        $checkdeliveryFeeAdded = OrderVendor::where([
            'order_id' => $request->order_id,
            'vendor_id' => $request->vendor_id
        ])->first();
        $checkOrder = Order::findOrFail($request->order_id);
        if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
            $order_lalamove = $data->createOrderRequestDunzo($checkOrder->user_id, $checkdeliveryFeeAdded);
        }

        if ($order_lalamove->status) {
            $up_web_hook_code = OrderVendor::where([
                'order_id' => $checkOrder->id,
                'vendor_id' => $request->vendor_id
            ])->update([
                'web_hook_code' => $order_lalamove->data->order_uuid,
                'lalamove_tracking_url' => $order_lalamove->data->trackUrl
            ]);

            return 1;
        }

        return 2;
    }

    public function placeOrderRequestlalamove($request)
    {
        $lala = new LalaMovesController();
        $is_place_order_delivery_zero = getAdditionalPreference([
            'is_place_order_delivery_zero'
        ])['is_place_order_delivery_zero'];
        // Create Shipping place order request for Lalamove
        $checkdeliveryFeeAdded = OrderVendor::where([
            'order_id' => $request->order_id,
            'vendor_id' => $request->vendor_id
        ])->first();
        $checkOrder = Order::findOrFail($request->order_id);
        if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
            $order_lalamove = $lala->placeOrderToLalamoveDev($request->vendor_id, $checkOrder->user_id, $checkOrder->id);
        }

        if ($order_lalamove->totalFee > 0) {
            $up_web_hook_code = OrderVendor::where([
                'order_id' => $checkOrder->id,
                'vendor_id' => $request->vendor_id
            ])->update([
                'web_hook_code' => $order_lalamove->orderRef
            ]);

            return 1;
        }

        return 2;
    }

    public function checkIfanyProductLastMileon($request)
    {
        $order_dispatchs = 2;
        $AdditionalPreference = getAdditionalPreference(['is_place_order_delivery_zero']);
        $is_place_order_delivery_zero =  $AdditionalPreference['is_place_order_delivery_zero'];
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->with('products', 'LuxuryOption')->first();

        $luxury_option_id      = $checkdeliveryFeeAdded->LuxuryOption ? $checkdeliveryFeeAdded->LuxuryOption->luxury_option_id : 1;
        $is_restricted = $checkdeliveryFeeAdded->is_restricted;
        if ($luxury_option_id == 6) { // only for on_demand type

            $dispatch_domain_OnDemand = $this->getDispatchOnDemandDomain();

            if ($dispatch_domain_OnDemand && $dispatch_domain_OnDemand != false) {
                $OnDemand = 0;
                foreach ($checkdeliveryFeeAdded->products as $key => $prod) {
                    $dispatch_domain = [
                        'service_key'      => $dispatch_domain_OnDemand->dispacher_home_other_service_key,
                        'service_key_code' => $dispatch_domain_OnDemand->dispacher_home_other_service_key_code,
                        'service_key_url'  => $dispatch_domain_OnDemand->dispacher_home_other_service_key_url,
                        'service_type'     => 'on_demand'
                    ];

                    if (($prod->is_price_buy_driver == 1)  && ($prod->product->category->categoryDetail->type_id == 8)) {

                        $dispatch_domain['rejectable_order'] = 1;

                        $order_dispatchs = $this->placeRequestToDispatchSingleProduct($request->order_id, $request->vendor_id, $dispatch_domain, $request);
                        if ($order_dispatchs && $order_dispatchs == 1) {
                            $OnDemand = 1;
                            return 1;
                        }
                    } else if (isset($prod->product_dispatcher_tag) && !empty($prod->product_dispatcher_tag) && $prod->product->category->categoryDetail->type_id == 8) {



                        if ($dispatch_domain_OnDemand && $dispatch_domain_OnDemand != false && $OnDemand == 0  && $checkdeliveryFeeAdded->delivery_fee > 0) {


                            $order_dispatchs = $this->placeRequestToDispatchSingleProduct($request->order_id, $request->vendor_id, $dispatch_domain, $request);
                            if ($order_dispatchs && $order_dispatchs == 1) {
                                $OnDemand = 1;
                                return 1;
                            }
                        }
                    } else { //for long term service

                    }
                }
            }
        }

        $dispatch_domain = $this->getDispatchDomain();
        if ($dispatch_domain && $dispatch_domain != false) {
            if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
                $order_dispatchs = $this->placeRequestToDispatch($request->order_id, $request->vendor_id, $dispatch_domain);
            }

            if ($order_dispatchs && $order_dispatchs == 1) {
                return 1;
            }
        }


        // $dispatch_domain_ondemand = $this->getDispatchOnDemandDomain();
        // if ($dispatch_domain_ondemand && $dispatch_domain_ondemand != false) {
        //     $ondemand = 0;
        //     foreach ($checkdeliveryFeeAdded->products as $key => $prod) {
        //         if (isset($prod->product_dispatcher_tag) && !empty($prod->product_dispatcher_tag) && $prod->product->category->categoryDetail->type_id == 8) {
        //             $dispatch_domain_ondemand = $this->getDispatchOnDemandDomain();
        //             if ($dispatch_domain_ondemand && $dispatch_domain_ondemand != false && $ondemand == 0  && $checkdeliveryFeeAdded->delivery_fee <= 0.00) {
        //                 $order_dispatchs = $this->placeRequestToDispatchOnDemand($request->order_id, $request->vendor_id, $dispatch_domain_ondemand);
        //                 if ($order_dispatchs && $order_dispatchs == 1) {
        //                     $ondemand = 1;
        //                     return 1;
        //                 }
        //             }
        //         }
        //     }
        // }

        // ///////////// **************** for laundry accept order *************** ////////////////
        $dispatch_domain_laundry = $this->getDispatchLaundryDomain();

        if ($dispatch_domain_laundry && $dispatch_domain_laundry != false) {
            $laundry = 0;

            foreach ($checkdeliveryFeeAdded->products as $key => $prod) {
                if ($prod->product->category->categoryDetail->type_id == 9) { // /////// if product from laundry
                    $dispatch_domain_laundry = $this->getDispatchLaundryDomain();
                    if ($dispatch_domain_laundry && $dispatch_domain_laundry != false && $laundry == 0) {
                        for ($x = 1; $x <= 2; $x++) {
                            if ($x == 1) {
                                $team_tag = $dispatch_domain_laundry->laundry_pickup_team ?? null;
                                $colm = $x;
                            }

                            if ($x == 2) {
                                $team_tag = $dispatch_domain_laundry->laundry_dropoff_team ?? null;
                                $colm = $x;
                            }

                            $order_dispatchs = $this->placeRequestToDispatchLaundry($request->order_id, $request->vendor_id, $dispatch_domain_laundry, $team_tag, $colm);
                        }

                        if ($order_dispatchs && $order_dispatchs == 1) {
                            $laundry = 1;
                            return 1;
                        }
                    }
                }
            }
        }

        return 2;
    }

    // place Request To Dispatch
    public function placeRequestToDispatch($order, $vendor, $dispatch_domain)
    {
        try {
            $order = Order::find($order);
            $customer = User::find($order->user_id);
            $cus_address = UserAddress::find($order->address_id);
            $tasks = array();

            $dynamic = uniqid($order->id . $vendor);
            $call_back_url = route('dispatch-order-update', $dynamic);
            $vendor_details = Vendor::where('id', $vendor)->select('id', 'name', 'phone_no', 'email', 'latitude', 'longitude', 'address', 'order_pre_time')->first();
            $order_vendor = OrderVendor::where([
                'order_id' => $order->id,
                'vendor_id' => $vendor
            ])->first();
            if (!empty($order_vendor->web_hook_code)) {
                $dynamic = $order_vendor->web_hook_code;
            }
            $tasks = array();
            $meta_data = '';

            if ($order->payment_option_id == 1) {
                $cash_to_be_collected = 'Yes';
                $payable_amount = $order_vendor->payable_amount -  $order->loyalty_amount_saved - $order->wallet_amount_used + $order->tip_amount;
            } else {
                if ($order->is_postpay == 1 && $order->payment_status == 0) {
                    $cash_to_be_collected = 'Yes';
                    $payable_amount = $order_vendor->payable_amount -  $order->loyalty_amount_saved - $order->wallet_amount_used + $order->tip_amount;
                } else {
                    $cash_to_be_collected = 'No';
                    $payable_amount = 0.00;
                }
            }

            $team_tag = null;
            if (!empty($dispatch_domain->last_mile_team)) {
                $team_tag = $dispatch_domain->last_mile_team;
            }
            $vendorProduct=OrderVendorProduct::where('order_id',$order->id)->first();
            $tags = isset($vendorProduct->product)?$vendorProduct->product->tags:'';

            if (isset($order->scheduled_date_time) && !empty($order->scheduled_date_time)) {
                $task_type = 'schedule';
                $schedule_time = $order->scheduled_date_time ?? null;
            } else {
                $task_type = 'now';
            }

            $tasks[] = array(
                'task_type_id' => 1,
                'latitude' => $vendor_details->latitude ?? '',
                'longitude' => $vendor_details->longitude ?? '',
                'short_name' => '',
                'address' => $vendor_details->address ?? '',
                'post_code' => '',
                'barcode' => '',
                'flat_no' => null,
                'email' => $vendor_details->email ?? null,
                'phone_number' => $vendor_details->phone_no ?? null
            );

            $tasks[] = array(
                'task_type_id' => 2,
                'latitude' => $cus_address->latitude ?? '',
                'longitude' => $cus_address->longitude ?? '',
                'short_name' => '',
                'address' => $cus_address->address ?? '',
                'post_code' => $cus_address->pincode ?? '',
                'barcode' => '',
                'flat_no' => $cus_address->house_number ?? null,
                'email' => $customer->email ?? null,
                'phone_number' => ($customer->dial_code . $customer->phone_number) ?? null
            );
            if ($customer->dial_code == "971") {
                // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                $customerno = "0" . $customer->phone_number;
            } else {
                // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
            }
            //Log::info("order Pre Time is ".$vendor_details->order_pre_time);
            $client = CP::orderBy('id', 'asc')->first();
            $postdata = [
                'order_number' => $order->order_number,
                'customer_name' => $customer->name ?? 'Dummy Customer',
                'customer_phone_number' => $customerno ?? rand(111111, 11111),
                'customer_dial_code' => $customer->dial_code ?? null,
                'customer_email' => $customer->email ?? null,
                'recipient_phone' => $customerno ?? rand(111111, 11111),
                'recipient_email' => $customer->email ?? null,
                'task_description' => "Order From :" . $vendor_details->name,
                'allocation_type' => 'a',
                'task_type' => $task_type,
                'schedule_time' => $schedule_time ?? null,
                'cash_to_be_collected' => $payable_amount ?? 0.00,
                'barcode' => '',
                'order_team_tag' => $team_tag,
                'order_agent_tag' => $tags,
                'call_back_url' => $call_back_url ?? null,
                'task' => $tasks,
                'is_restricted' => $order_vendor->is_restricted,
                'vendor_id' => $vendor_details->id,
                'order_vendor_id' => $order_vendor->id,
                'dbname' => $client->database_name,
                'order_id' => $order->id,
                'customer_id' => $order->user_id,
                'user_icon' => $customer->image,
                'order_pre_time' => $vendor_details->order_pre_time,
                'app_call' => 0,
                'tip_amount' => $order->tip_amount ?? 0
            ];
            if ($order_vendor->is_restricted == 1) {
                $postdata['user_verification_type'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? $customer->passbase_verification->resources->type : null;
                $postdata['user_datapoints'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? json_decode($customer->passbase_verification->resources->datapoints) : null;
            }

            $client = new GCLIENT([
                'headers' => [
                    'personaltoken' => $dispatch_domain->delivery_service_key,
                    'shortcode' => $dispatch_domain->delivery_service_key_code,
                    'content-type' => 'application/json'
                ]
            ]);

            $url = $dispatch_domain->delivery_service_key_url;
            $res = $client->post($url . '/api/task/create', [
                'form_params' => ($postdata)
            ]);
            $response = json_decode($res->getBody(), true);
            if ($response && $response['task_id'] > 0) {
                $dispatch_traking_url = $response['dispatch_traking_url'] ?? '';
                $up_web_hook_code = OrderVendor::where([
                    'order_id' => $order->id,
                    'vendor_id' => $vendor
                ])->update([
                    'web_hook_code' => $dynamic,
                    'dispatch_traking_url' => $dispatch_traking_url
                ]);
                return 1;
            }
            return 2;
        } catch (\Exception $e) {
            return 2;
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    // place Request To Dispatch for On Demand
    public function placeRequestToDispatchOnDemand($order, $vendor, $dispatch_domain)
    {
        try {
            $order = Order::find($order);
            $customer = User::find($order->user_id);
            $cus_address = UserAddress::find($order->address_id);
            $tasks = array();
            if ($order->payment_option_id == 1) {
                $cash_to_be_collected = 'Yes';
                $payable_amount = $order->payable_amount -  $order->loyalty_amount_saved - $order->wallet_amount_used;
            } else {
                if ($order->is_postpay == 1 && $order->payment_status == 0) {
                    $cash_to_be_collected = 'Yes';
                    $payable_amount = $order->payable_amount -  $order->loyalty_amount_saved - $order->wallet_amount_used;
                } else {
                    $cash_to_be_collected = 'No';
                    $payable_amount = 0.00;
                }
            }
            $dynamic = uniqid($order->id . $vendor);
            $call_back_url = route('dispatch-order-update', $dynamic);
            $vendor_details = Vendor::where('id', $vendor)->select('id', 'name', 'phone_no', 'email', 'latitude', 'longitude', 'address')->first();
            $order_vendor = OrderVendor::where([
                'order_id' => $order->id,
                'vendor_id' => $vendor
            ])->first();
            $tasks = array();
            $meta_data = '';

            $unique = Auth::user()->code;
            $team_tag = $unique . "_" . $vendor;

            $tasks[] = array(
                'task_type_id' => 1,
                'latitude' => $vendor_details->latitude ?? '',
                'longitude' => $vendor_details->longitude ?? '',
                'short_name' => '',
                'address' => $vendor_details->address ?? '',
                'post_code' => '',
                'barcode' => '',
                'flat_no' => null,
                'email' => $vendor_details->email ?? null,
                'phone_number' => $vendor_details->phone_no ?? null
            );

            $tasks[] = array(
                'task_type_id' => 2,
                'latitude' => $cus_address->latitude ?? '',
                'longitude' => $cus_address->longitude ?? '',
                'short_name' => '',
                'address' => $cus_address->address ?? '',
                'post_code' => $cus_address->pincode ?? '',
                'barcode' => '',
                'flat_no' => $cus_address->house_number ?? null,
                'email' => $customer->email ?? null,
                'phone_number' => ($customer->dial_code . $customer->phone_number) ?? null
            );

            if ($customer->dial_code == "971") {
                // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                $customerno = "0" . $customer->phone_number;
            } else {
                // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
            }
            $client = CP::orderBy('id', 'asc')->first();
            $postdata = [
                'order_number' => $order->order_number,
                'customer_name' => $customer->name ?? 'Dummy Customer',
                'customer_phone_number' => $customerno ?? rand(111111, 11111),
                'customer_dial_code' => $customer->dial_code ?? null,
                'customer_email' => $customer->email ?? null,
                'recipient_phone' => $customerno ?? rand(111111, 11111),
                'recipient_email' => $customer->email ?? null,
                'task_description' => "Order From :" . $vendor_details->name,
                'allocation_type' => 'a',
                'task_type' => 'now',
                'cash_to_be_collected' => $payable_amount ?? 0.00,
                'barcode' => '',
                'order_team_tag' => $team_tag,
                'call_back_url' => $call_back_url ?? null,
                'task' => $tasks,
                'is_restricted' => $order_vendor->is_restricted,
                'vendor_id' => $vendor_details->id,
                'order_vendor_id' => $order_vendor->id,
                'dbname' => $client->database_name,
                'order_id' => $order->id,
                'customer_id' => $order->user_id,
                'user_icon' => $customer->image,
                'tip_amount' => $order->tip_amount ?? 0

            ];
            if ($order_vendor->is_restricted == 1) {
                $postdata['user_verification_type'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? $customer->passbase_verification->resources->type : null;
                $postdata['user_datapoints'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? json_decode($customer->passbase_verification->resources->datapoints) : null;
            }

            $client = new GCLIENT([
                'headers' => [
                    'personaltoken' => $dispatch_domain->dispacher_home_other_service_key,
                    'shortcode' => $dispatch_domain->dispacher_home_other_service_key_code,
                    'content-type' => 'application/json'
                ]
            ]);

            $url = $dispatch_domain->dispacher_home_other_service_key_url;
            $res = $client->post($url . '/api/task/create', [
                'form_params' => ($postdata)
            ]);
            $response = json_decode($res->getBody(), true);
            if ($response && $response['task_id'] > 0) {
                $dispatch_traking_url = $response['dispatch_traking_url'] ?? '';
                $up_web_hook_code = OrderVendor::where([
                    'order_id' => $order->id,
                    'vendor_id' => $vendor
                ])->update([
                    'web_hook_code' => $dynamic,
                    'dispatch_traking_url' => $dispatch_traking_url
                ]);

                return 1;
            }
            return 2;
        } catch (\Exception $e) {
            return 2;
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    // place Request To Dispatch for Laundry
    public function placeRequestToDispatchLaundry($order, $vendor, $dispatch_domain, $team_tag, $colm)
    {
        try {
            $order = Order::find($order);
            $customer = User::find($order->user_id);
            $cus_address = UserAddress::find($order->address_id);
            $tasks = array();
            if ($order->payment_option_id == 1) {
                $cash_to_be_collected = 'Yes';
                $payable_amount = $order->payable_amount -  $order->loyalty_amount_saved - $order->wallet_amount_used;
            } else {
                if ($order->is_postpay == 1 && $order->payment_status == 0) {
                    $cash_to_be_collected = 'Yes';
                    $payable_amount = $order->payable_amount -  $order->loyalty_amount_saved - $order->wallet_amount_used;
                } else {
                    $cash_to_be_collected = 'No';
                    $payable_amount = 0.00;
                }
            }

            $dynamic = uniqid($order->id . $vendor);
            $call_back_url = route('dispatch-order-update', $dynamic);
            $vendor_details = Vendor::where('id', $vendor)->select('id', 'name', 'latitude', 'phone_no', 'email', 'longitude', 'address')->first();
            $order_vendor = OrderVendor::where([
                'order_id' => $order->id,
                'vendor_id' => $vendor
            ])->first();
            $tasks = array();
            $meta_data = '';
            $rtype = 'P';
            $unique = Auth::user()->code;
            if ($colm == 1) { # 1 for pickup from customer drop to vendor
                $rtype = 'P';
                $desc = $order->comment_for_pickup_driver ?? null;
                $tasks[] = array(
                    'task_type_id' => 1,
                    'latitude' => $cus_address->latitude ?? '',
                    'longitude' => $cus_address->longitude ?? '',
                    'short_name' => '',
                    'address' => $cus_address->address ?? '',
                    'post_code' => $cus_address->pincode ?? '',
                    'barcode' => '',
                    'flat_no' => $cus_address->house_number ?? '',
                    'email' => $customer->email ?? '',
                    'phone_number' => $customer->dial_code . $customer->phone_number ?? ''
                );
                $tasks[] = array(
                    'task_type_id' => 2,
                    'latitude' => $vendor_details->latitude ?? '',
                    'longitude' => $vendor_details->longitude ?? '',
                    'short_name' => '',
                    'address' => $vendor_details->address ?? '',
                    'post_code' => '',
                    'barcode' => '',
                    'flat_no' => null,
                    'email' => $vendor_details->email ?? null,
                    'phone_number' => $vendor_details->phone_no ?? null
                );

                if (isset($order->schedule_pickup) && !empty($order->schedule_pickup)) {
                    $task_type = 'schedule';
                    $schedule_time = $order->schedule_pickup ?? null;
                } else {
                    $task_type = 'now';
                }
            }

            if ($colm == 2) { # 1 for pickup from vendor drop to customer
                $rtype = 'D';
                $desc = $order->comment_for_dropoff_driver ?? null;
                $tasks[] = array(
                    'task_type_id' => 1,
                    'latitude' => $vendor_details->latitude ?? '',
                    'longitude' => $vendor_details->longitude ?? '',
                    'short_name' => '',
                    'address' => $vendor_details->address ?? '',
                    'post_code' => '',
                    'barcode' => '',
                    'flat_no' => null,
                    'email' => $vendor_details->email ?? null,
                    'phone_number' => $vendor_details->phone_no ?? null
                );

                $tasks[] = array(
                    'task_type_id' => 2,
                    'latitude' => $cus_address->latitude ?? '',
                    'longitude' => $cus_address->longitude ?? '',
                    'short_name' => '',
                    'address' => $cus_address->address ?? '',
                    'post_code' => $cus_address->pincode ?? '',
                    'barcode' => '',
                    'flat_no' => $cus_address->house_number ?? null,
                    'email' => $customer->email ?? null,
                    'phone_number' => ($customer->dial_code . $customer->phone_number) ?? null
                );

                if (isset($order->schedule_dropoff) && !empty($order->schedule_dropoff)) {
                    $task_type = 'schedule';
                    $schedule_time = $order->schedule_dropoff ?? null;
                } else {
                    $task_type = 'now';
                }
            }

            if ($customer->dial_code == "971") {
                // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                $customerno = "0" . $customer->phone_number;
            } else {
                // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
            }

            $client = CP::orderBy('id', 'asc')->first();
            $postdata = [
                'order_number' => $order->order_number,
                'customer_name' => $customer->name ?? 'Dummy Customer',
                'customer_phone_number' => $customerno ?? rand(111111, 11111),
                'customer_dial_code' => $customer->dial_code ?? null,
                'customer_email' => $customer->email ?? null,
                'recipient_phone' => $customerno ?? rand(111111, 11111),
                'recipient_email' => $customer->email ?? null,
                'task_description' => $desc ?? null,
                'allocation_type' => 'a',
                'task_type' => $task_type,
                'cash_to_be_collected' => $payable_amount ?? 0.00,
                'schedule_time' => $schedule_time ?? null,
                'barcode' => '',
                'order_team_tag' => $team_tag,
                'call_back_url' => $call_back_url ?? null,
                'task' => $tasks,
                'request_type' => $rtype,
                'is_restricted' => $order_vendor->is_restricted,
                'vendor_id' => $vendor_details->id,
                'dbname' => $client->database_name,
                'order_vendor_id' => $order_vendor->id,
                'order_id' => $order->id,
                'customer_id' => $order->user_id,
                'user_icon' => $customer->image,
                'tip_amount' => $order->tip_amount ?? 0

            ];
            if ($order_vendor->is_restricted == 1) {
                $postdata['user_verification_type'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? $customer->passbase_verification->resources->type : null;
                $postdata['user_datapoints'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? json_decode($customer->passbase_verification->resources->datapoints) : null;
            }

            $client = new Client([
                'headers' => [
                    'personaltoken' => $dispatch_domain->laundry_service_key,
                    'shortcode' => $dispatch_domain->laundry_service_key_code,
                    'content-type' => 'application/json'
                ]
            ]);

            $url = $dispatch_domain->laundry_service_key_url;
            $res = $client->post($url . '/api/task/create', [
                'form_params' => ($postdata)
            ]);
            $response = json_decode($res->getBody(), true);

            if ($response && $response['task_id'] > 0) {
                $dispatch_traking_url = $response['dispatch_traking_url'] ?? '';
                $up_web_hook_code = OrderVendor::where([
                    'order_id' => $order->id,
                    'vendor_id' => $vendor
                ])->update([
                    'web_hook_code' => $dynamic,
                    'dispatch_traking_url' => $dispatch_traking_url
                ]);

                return 1;
            }
            return 2;
        } catch (\Exception $e) {
            return 2;
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    # get prefereance if last mile on or off and all details updated in config
    public function getDispatchDomain()
    {
        $preference = ClientPreference::first();
        if ($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url)) {
            return $preference;
        } else {
            return false;
        }
    }

    # get prefereance if on demand on in config
    public function getDispatchOnDemandDomain()
    {
        $preference = ClientPreference::first();
        if ($preference->need_dispacher_home_other_service == 1 && !empty($preference->dispacher_home_other_service_key) && !empty($preference->dispacher_home_other_service_key_code) && !empty($preference->dispacher_home_other_service_key_url)) {
            return $preference;
        } else {
            return false;
        }
    }

    # get prefereance if laundry in config
    public function getDispatchLaundryDomain()
    {
        $preference = ClientPreference::first();
        if ($preference->need_laundry_service == 1 && !empty($preference->laundry_service_key) && !empty($preference->laundry_service_key_code) && !empty($preference->laundry_service_key_url)) {
            return $preference;
        } else {
            return false;
        }
    }



    // public function sendSuccessNotification($id, $vendorId)
    // {
    //     $super_admin = User::where('is_superadmin', 1)->pluck('id');
    //     $user_vendors = UserVendor::where('vendor_id', $vendorId)->pluck('user_id');
    //     $devices = UserDevice::whereNotNull('device_token')->where('user_id', $id)->pluck('device_token');
    //     foreach ($devices as $device) {
    //         $token[] = $device;
    //     }
    //     $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $user_vendors)->pluck('device_token');
    //     foreach ($devices as $device) {
    //         $token[] = $device;
    //     }
    //     $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $super_admin)->pluck('device_token');
    //     foreach ($devices as $device) {
    //         $token[] = $device;
    //     }
    //     //$token[] = "d4SQZU1QTMyMaENeZXL3r6:APA91bHoHsQ-rnxsFaidTq5fPse0k78qOTo7ZiPTASiH69eodqxGoMnRu2x5xnX44WfRhrVJSQg2FIjdfhwCyfpnZKL2bHb5doCiIxxpaduAUp4MUVIj8Q43SB3dvvvBkM1Qc1ThGtEM";
    //     // dd($token);

    //     //$from = env('FIREBASE_SERVER_KEY');

    //     $notification_content = NotificationTemplate::where('id', 2)->first();
    //     $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
    //     if ($notification_content && !empty($token) && !empty($client_preferences->fcm_server_key)) {

    //         $data = [
    //             "registration_ids" => $token,
    //             "notification" => [
    //                 'title' => $notification_content->label,
    //                 'body'  => $notification_content->content,
    //             ]
    //         ];
    //         $dataString = $data;

    //         sendFcmCurlRequest($data);
    //     }
    // }

    // / ****************** insert In Vendor Order Dispatch Status ************************ ///////////////
    public function insertInVendorOrderDispatchStatus($request)
    {
        $update = VendorOrderDispatcherStatus::updateOrCreate([
            'dispatcher_id' => null,
            'order_id' => $request->order_id,
            'dispatcher_status_option_id' => 1,
            'vendor_id' => $request->vendor_id
        ]);
    }

    public function checkIfLastMileDeliveryOn()
    {
        $preference = ClientPreference::first();

        if ($preference->business_type == 'taxi') {
            if ($preference->need_dispacher_ride == 1 && !empty($preference->pickup_delivery_service_key) && !empty($preference->pickup_delivery_service_key_code) && !empty($preference->pickup_delivery_service_key_url))
                return $preference;
            else
                return false;
        } elseif ($preference->business_type == 'laundry') {
            if ($preference->need_laundry_service == 1 && !empty($preference->laundry_service_key) && !empty($preference->laundry_service_key_code) && !empty($preference->laundry_service_key_url))
                return $preference;
            else
                return false;
        } else {
            if ($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
                return $preference;
            else
                return false;
        }
    }

    public function driverDocuments()
    {
        try {
            $dispatch_domain = $this->checkIfLastMileDeliveryOn();
            if ($dispatch_domain->business_type == 'taxi') {
                $url = $dispatch_domain->pickup_delivery_service_key_url;
                $endpoint = $url . "/api/send-documents";
                $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->pickup_delivery_service_key, 'shortcode' => $dispatch_domain->pickup_delivery_service_key_code]]);

                $response = $client->post($endpoint);
                $response = json_decode($response->getBody(), true);
                $response['api_data'] = [
                    'url' => $url,
                    'token' => $dispatch_domain->pickup_delivery_service_key,
                    'code' => $dispatch_domain->pickup_delivery_service_key_code
                ];
            } elseif ($dispatch_domain->business_type == 'laundry') {
                $url = $dispatch_domain->laundry_service_key_url;
                $endpoint = $url . "/api/send-documents";
                $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->laundry_service_key, 'shortcode' => $dispatch_domain->laundry_service_key_code]]);

                $response = $client->post($endpoint);
                $response = json_decode($response->getBody(), true);
                $response['api_data'] = [
                    'url' => $url,
                    'token' => $dispatch_domain->laundry_service_key,
                    'code' => $dispatch_domain->laundry_service_key_code
                ];
            } else {

                $url = $dispatch_domain->delivery_service_key_url;
                $endpoint = $url . "/api/send-documents";
                $client = new GCLIENT(['headers' => ['personaltoken' => $dispatch_domain->delivery_service_key, 'shortcode' => $dispatch_domain->delivery_service_key_code]]);

                $response = $client->post($endpoint);
                $response = json_decode($response->getBody(), true);
                $response['api_data'] = [
                    'url' => $url,
                    'token' => $dispatch_domain->delivery_service_key,
                    'code' => $dispatch_domain->delivery_service_key_code
                ];
            }
            return json_encode($response);
        } catch (\Exception $e) {
            $data = [];
            $data['status'] = 400;
            $data['message'] = $e->getMessage();
            return $data;
        }
    }

    public function driverSignup(Request $request)
    {

        try {
            $validator = Validator::make($request->all(), [
                'name' => 'required',
                'phone_number' => 'required',
                'type' => 'required',
                'team' => 'required'
            ], [
                "name.required" => __('The name field is required.'),
                "phone_number.required" => __('The phone number field is required.'),
                "type.required" => __('The type field is required.'),
                "team.required" => __('The team field is required.')
            ]);
            if ($validator->fails()) {
                return $this->errorResponse($validator->errors(), 422);
            }
            $dispatch_domain = $this->checkIfLastMileDeliveryOn();
            if ($dispatch_domain && $dispatch_domain != false) {

                $driver_documents = json_decode($this->driverDocuments());
                $data = $driver_documents->data;
                $api = $driver_documents->api_data;
                $driver_registration_documents = $data->documents;
                $rules_array = [
                    'name' => 'required',
                    'phonenumber' => 'required',
                    'type' => 'required',
                    'team' => 'required'
                ];
                foreach ($driver_registration_documents as $driver_registration_document) {
                    if ($driver_registration_document->is_required == 1) {
                        $name = str_replace(" ", "_", $driver_registration_document->name);
                        $name = preg_replace('/[^A-Za-z0-9\-]/', '', $name);
                        $rules_array[$name] = 'required';
                    }
                }
                $requestAllData = [];
                foreach ($request->all() as $key => $requestData) {
                    $newKey = preg_replace('/[^A-Za-z0-9\-]/', '', $key);
                    $requestAllData[$newKey] = $requestData;
                }
                $validator = Validator::make($requestAllData, $rules_array, [
                    "name.required" => __('The name field is required.'),
                    "phonenumber.required" => __('The phone number field is requiredfff.'),
                    "type.required" => __('The type field is required.'),
                    "vehicle_type_id.required" => __('The transport type is required.'),
                    "make_model.required" => __('The transport details field is required.'),
                    "uid.required" => __('The UID field is required.'),
                    "platenumber.required" => __('The licence plate field is required.'),
                    "color.required" => __('The color field is required.'),
                    "team.required" => __('The team field is required.')
                ]);
                if ($validator->fails()) {
                    return $this->errorResponse($validator->errors(), 422);
                }

                $files = [];
                if ($driver_registration_documents != null) {
                    foreach ($driver_registration_documents as $key => $driver_registration_document) {
                        $driver_registration_document_file_type[$key] = $driver_registration_document->file_type;
                        $files[$key]['file_type'] = $driver_registration_document_file_type[$key];
                        $driver_registration_document_id[$key] = $driver_registration_document->id;
                        $files[$key]['id'] = $driver_registration_document_id[$key];
                        $driver_registration_document_name[$key] = $driver_registration_document->name;
                        $files[$key]['name'] = $driver_registration_document_name[$key];
                        $name = $driver_registration_document->name;
                        $arr = explode(' ', $name);
                        $name = implode('_', $arr);
                        $driver_registration_document_file_name[$key] = $request[$name];
                        $files[$key]['file_name'] = $driver_registration_document_file_name[$key];
                    }
                }
                // $dispatch_domain->delivery_service_key_code = '649a9a';
                //  $dispatch_domain->delivery_service_key = 'icDerSAVT4Fd795DgPsPfONXahhTOA';
                $client = new GCLIENT(['headers' => ['personaltoken' => $api->token, 'shortcode' => $api->code]]);
                $url = $api->url;
                $key1 = 0;
                $key2 = 0;
                $filedata = [];
                $other = [];
                $abc = [];

                foreach ($files as $file) {
                    if ($file['file_name'] != null) {
                        if ($file['file_type'] != "Text" && $file['file_type'] != "selector" && $file['file_type'] != "Date") {
                            $file_path = $file['file_name']->getPathname();
                            $file_mime = $file['file_name']->getMimeType('image');
                            $file_uploaded_name = $file['file_name']->getClientOriginalName();
                            $filedata[$key2] = [
                                'Content-type' => 'multipart/form-data',
                                'name' => 'uploaded_file[]',
                                'file_type' => $file['file_type'],
                                'id' => $file['id'],
                                'filename' => $file_uploaded_name,
                                'contents' => fopen($file_path, 'r')
                            ];
                            $other[$key2] = [
                                'filename1' => $file['name'],
                                'file_type' => $file['file_type'],
                                'id' => $file['id']
                            ];
                            $key2++;
                        } else {
                            $abc[$key1] = [
                                'file_type' => $file['file_type'],
                                'id' => $file['id'],
                                'contents' => $file['file_name'],
                                'label_name' => $file['name']
                            ];
                            $key1++;
                        }
                    }
                }
                $profile_photo = [];
                if ($request->hasFile('upload_photo')) {
                    $profile_photo = [
                        'Content-type' => 'multipart/form-data',
                        'name' => 'upload_photo',
                        'filename' => $request->upload_photo->getClientOriginalName(),
                        'Mime-Type' => $request->upload_photo->getMimeType('image'),
                        'contents' => fopen($request->upload_photo, 'r')
                    ];
                }
                if ($profile_photo == null) {
                    $profile_photo = [
                        'name' => 'profile_photo[]',
                        'contents' => 'abc'
                    ];
                }
                if (!array_key_exists(0, $filedata)) {
                    $filedata[0] = [
                        'name' => 'uploaded_file[]',
                        'contents' => 'abc'
                    ];
                }
                if (!array_key_exists(1, $filedata)) {
                    $filedata[1] = [
                        'name' => 'uploaded_file[]',
                        'contents' => 'abc'
                    ];
                }
                if (!array_key_exists(2, $filedata)) {
                    $filedata[2] = [
                        'name' => 'uploaded_file[]',
                        'contents' => 'abc'
                    ];
                }
                if (!array_key_exists(3, $filedata)) {
                    $filedata[3] = [
                        'name' => 'uploaded_file[]',
                        'contents' => 'abc'
                    ];
                }
                if (!array_key_exists(4, $filedata)) {
                    $filedata[4] = [
                        'name' => 'uploaded_file[]',
                        'contents' => 'abc'
                    ];
                }
                if (!array_key_exists(5, $filedata)) {
                    $filedata[5] = [
                        'name' => 'uploaded_file[]',
                        'contents' => 'abc'
                    ];
                }
                if (!array_key_exists(6, $filedata)) {
                    $filedata[6] = [
                        'name' => 'uploaded_file[]',
                        'contents' => 'abc'
                    ];
                }
                if (!array_key_exists(7, $filedata)) {
                    $filedata[7] = [
                        'name' => 'uploaded_file[]',
                        'contents' => 'abc'
                    ];
                }
                if (!array_key_exists(8, $filedata)) {
                    $filedata[8] = [
                        'name' => 'uploaded_file[]',
                        'contents' => 'abc'
                    ];
                }
                if (!array_key_exists(9, $filedata)) {
                    $filedata[9] = [
                        'name' => 'uploaded_file[]',
                        'contents' => 'abc'
                    ];
                }

                $tags = '';
                if ($request->has('tags') && !empty($request->get('tags'))) {
                    $tagsArray = $request->get('tags');
                    $tags = implode(',', $tagsArray);
                }

                $res = $client->post($url . '/api/agent/create', [

                    'multipart' => [
                        $filedata[0],
                        $profile_photo,
                        $filedata[1],
                        $filedata[2],
                        $filedata[3],
                        $filedata[4],
                        $filedata[5],
                        $filedata[6],
                        $filedata[7],
                        $filedata[8],
                        $filedata[9],
                        [
                            'name' => 'other',
                            'contents' => json_encode($other)
                        ],
                        [
                            'name' => 'files_text',
                            'contents' => json_encode($abc)
                        ],

                        [
                            'name' => 'count',
                            'contents' => count($files)
                        ],
                        [
                            'name' => 'name',
                            'contents' => $request->name
                        ],
                        [
                            'name' => 'phone_number',
                            'contents' => $request->phone_number
                        ],
                        [
                            'name' => 'country_code',
                            'contents' => $request->country_code
                        ],
                        [
                            'name' => 'type',
                            'contents' => $request->type
                        ],
                        [
                            'name' => 'vehicle_type_id',
                            'contents' => $request->vehicle_type_id ?? null
                        ],
                        [
                            'name' => 'make_model',
                            'contents' => $request->make_model ?? null
                        ],
                        [
                            'name' => 'uid',
                            'contents' => $request->uid ?? null
                        ],
                        [
                            'name' => 'plate_number',
                            'contents' => $request->plate_number ?? null
                        ],
                        [
                            'name' => 'color',
                            'contents' => $request->color ?? null
                        ],
                        [
                            'name' => 'team_id',
                            'contents' => $request->team
                        ],
                        [
                            'name' => 'tags',
                            'contents' => $tags
                        ]
                    ]
                ]);
                $response = json_decode($res->getBody(), true);
                return $response;
            }
        } catch (\Exception $e) {
            $data = [];
            $data['status'] = 400;
            $data['message'] = $e->getMessage();
            return $data;
        }
    }

    public function minimize_orderDetails_for_notification($order_id, $vendor_id = "")
    {
        $user = Auth::user();
        $order = Order::with([
            'vendors.vendor:id,name,auto_accept_order,logo'
        ])->select('id', 'order_number', 'payable_amount', 'payment_option_id', 'user_id', 'address_id', 'loyalty_amount_saved', 'total_discount', 'total_delivery_fee', 'total_amount', 'taxable_amount', 'created_at');
        $order = $order->whereHas('vendors', function ($query) use ($vendor_id) {
            if (!empty($vendor_id)) {
                $query->where('vendor_id', $vendor_id);
            }
        })
            ->with('vendors', function ($query) use ($vendor_id) {
                $query->select('id', 'order_id', 'vendor_id');
                if (!empty($vendor_id)) {
                    $query->where('vendor_id', $vendor_id);
                }
            });
        $order = $order->find($order_id);

        return $order;
    }

    public function orderDetails_for_notification($order_id, $vendor_id = "")
    {
        $user = Auth::user();
        $language_id = (!empty(Session::get('customerLanguage'))) ? Session::get('customerLanguage') : 1;
        $order = Order::with([
            'vendors.products:id,product_name,product_id,order_id,order_vendor_id,variant_id,quantity,price',
            'vendors.vendor:id,name,auto_accept_order,logo',
            'vendors.products.addon:id,order_product_id,addon_id,option_id',
            'vendors.products.pvariant:id,sku,product_id,title,quantity',
            'user:id,name,timezone,dial_code,phone_number',
            'address:id,user_id,address',
            'vendors.products.addon.option:addon_options.id,addon_options.title,addon_id,price',
            'vendors.products.addon.set:addon_sets.id,addon_sets.title',
            'vendors.products.translation' => function ($q) use ($language_id) {
                $q->select('id', 'product_id', 'title');
                $q->where('language_id', $language_id);
            },
            'vendors.products.addon.option.translation_one' => function ($q) use ($language_id) {
                $q->select('id', 'addon_opt_id', 'title');
                $q->where('language_id', $language_id);
            },
            'vendors.products.addon.set.translation_one' => function ($q) use ($language_id) {
                $q->select('id', 'addon_id', 'title');
                $q->where('language_id', $language_id);
            }
        ])->select('id', 'order_number', 'payable_amount', 'payment_option_id', 'user_id', 'address_id', 'loyalty_amount_saved', 'total_discount', 'total_delivery_fee', 'total_amount', 'taxable_amount', 'wallet_amount_used', 'created_at');
        $order = $order->whereHas('vendors', function ($query) use ($vendor_id) {
            if (!empty($vendor_id)) {
                $query->where('vendor_id', $vendor_id);
            }
        })
            ->with('vendors', function ($query) use ($vendor_id) {
                $query->select('id', 'order_id', 'vendor_id');
                if (!empty($vendor_id)) {
                    $query->where('vendor_id', $vendor_id);
                }
            });
        $order = $order->find($order_id);
        $order_item_count = 0;
        $order->payment_option_title = $order->paymentOption->title;
        $order->item_count = $order_item_count;
        foreach ($order->products as $product) {
            $order_item_count += $product->quantity;
        }
        $order->item_count = $order_item_count;
        unset($order->products);
        unset($order->paymentOption);
        return $order;
    }

    /**
     * Credit Money Into order tip
     *
     * @return \Illuminate\Http\Response
     */
    public function tipAfterOrder(Request $request, $domain = '')
    {
        if ((isset($request->user_id)) && (!empty($request->user_id))) {
            $user = User::find($request->user_id);
        } else {
            $user = Auth::user();
        }

        if ($user) {
            $order_number = $request->order_number;
            if ($order_number > 0) {
                $order = Order::select('id', 'tip_amount')->where('order_number', $order_number)->first();
                if (($order->tip_amount == 0) || empty($order->tip_amount)) {
                    $tip = Order::where('order_number', $order_number)->update([
                        'tip_amount' => $request->tip_amount
                    ]);
                    $payment = Payment::where('transaction_id', $request->transaction_id)->first();
                    if (!$payment) {
                        $payment = new Payment();
                    }
                    $payment->date = date('Y-m-d');
                    $payment->order_id = $order->id;
                    $payment->transaction_id = $request->transaction_id;
                    $payment->balance_transaction = $request->tip_amount;
                    $payment->type = 'tip';
                    $payment->save();
                }
                $message = 'Tip has been submitted successfully';
                $response['tip_amount'] = $request->tip_amount;
                Session::put('success', $message);
                return $this->successResponse($response, $message, 200);
            } else {
                return $this->errorResponse('Amount is not sufficient', 400);
            }
        } else {
            return $this->errorResponse('Invalid User', 400);
        }
    }

    /**
     * Post Route
     * Save Rescheduled Order
     * Added by Ovi
     */
    public function rescheduleOrder(Request $request)
    {
        $order_id = Crypt::decrypt($request->order_id);
        $order = Order::find($order_id);
        $vendor_id = $request->vendor_id;
        $vendor = Vendor::where('id', $vendor_id)->first();
        $user = Auth::user();
        $currency_id = Session::get('customerCurrency');
        $clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
        $schedule_pickup_compare = Carbon::parse($order->schedule_pickup);
        $schedule_dropoff_compare = Carbon::parse($order->schedule_dropoff);
        // $pickup_schedule_datetime_compare = Carbon::parse($request->pickup_schedule_datetime);
        // $dropoff_schedule_datetime_compare = Carbon::parse($request->dropoff_schedule_datetime);
        $pickup_schedule_datetime_compare = date('Y-m-d');
        $dropoff_schedule_datetime_compare = date('Y-m-d');

        // If the rescheduling of pickup and dropoff is done on current dates.
        if ($dropoff_schedule_datetime_compare == $schedule_dropoff_compare->format('Y-m-d') && $pickup_schedule_datetime_compare == $schedule_pickup_compare->format('Y-m-d')) {
            $totalCharges = $vendor->pickup_cancelling_charges + $vendor->rescheduling_charges;
            if ($user->balanceFloat >= $totalCharges) {
                $this->chargeForPickupRescheduling($user, $vendor, $order);
                $this->chargeForDropoffRescheduling($user, $vendor, $order);
            } else {
                Session::flash('error', 'Insufficient wallet balance, required rescheduling charges are ' . $clientCurrency->currency->symbol . $totalCharges . '. Please recharge your wallet.');
                return redirect()->back();
            }
        } // If the rescheduling is done on the day of pickup, then a rescheduling fee will apply.
        elseif ($pickup_schedule_datetime_compare == $schedule_pickup_compare->format('Y-m-d')) {
            if ($vendor->pickup_cancelling_charges > 0) {
                $result = $this->chargeForPickupRescheduling($user, $vendor, $order);
                if ($result == false) {
                    Session::flash('error', 'Insufficient wallet balance, required rescheduling charges are ' . $clientCurrency->currency->symbol . $vendor->pickup_cancelling_charges . '. Please recharge your wallet.');
                    return redirect()->back();
                }
            }
        } // If the rescheduling is done on the day of delivery, then a rescheduling fee will apply.
        elseif ($dropoff_schedule_datetime_compare == $schedule_dropoff_compare->format('Y-m-d')) {
            if ($vendor->pickup_cancelling_charges > 0) {
                $result = $this->chargeForDropoffRescheduling($user, $vendor, $order);
                if ($result == false) {
                    Session::flash('error', 'Insufficient wallet balance, required rescheduling charges are ' . $clientCurrency->currency->symbol . $vendor->rescheduling_charges . '. Please recharge your wallet.');
                    return redirect()->back();
                }
            }
        }

        $schedule_pickup_slot = explode(" - ", $request->schedule_pickup_slot);
        $pickup_schedule_datetime = Carbon::createFromFormat('Y-m-d H:i:s', $request->pickup_schedule_datetime . ' ' . $schedule_pickup_slot[0] . ':00');

        $schedule_dropoff_slot = explode(" - ", $request->schedule_dropoff_slot);
        $dropoff_schedule_datetime = Carbon::createFromFormat('Y-m-d H:i:s', $request->dropoff_schedule_datetime . ' ' . $schedule_dropoff_slot[0] . ':00');

        $rescheduleOrder = new RescheduleOrder();
        $rescheduleOrder->reschedule_by = $user->id;
        $rescheduleOrder->order_id = $order_id;
        $rescheduleOrder->vendor_id = $vendor_id;
        $rescheduleOrder->prev_schedule_pickup = $order->schedule_pickup;
        $rescheduleOrder->prev_schedule_dropoff = $order->schedule_dropoff;
        $rescheduleOrder->prev_scheduled_slot = $order->scheduled_slot;
        $rescheduleOrder->prev_dropoff_scheduled_slot = $order->dropoff_scheduled_slot;
        $rescheduleOrder->new_schedule_pickup = Carbon::parse($pickup_schedule_datetime, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
        $rescheduleOrder->new_schedule_dropoff = Carbon::parse($dropoff_schedule_datetime, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
        $rescheduleOrder->new_scheduled_slot = $request->schedule_pickup_slot;
        $rescheduleOrder->new_dropoff_scheduled_slot = $request->schedule_dropoff_slot;
        $rescheduleOrder->save();

        $order->schedule_pickup = Carbon::parse($pickup_schedule_datetime, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
        $order->schedule_dropoff = Carbon::parse($dropoff_schedule_datetime, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
        $order->scheduled_slot = $request->schedule_pickup_slot;
        $order->dropoff_scheduled_slot = $request->schedule_dropoff_slot;
        $order->save();

        // Send Rescheduling Request to Dispatcher - Added by Ovi
        $orderVendor = OrderVendor::where('order_id', $order->id)->first();

        $postdata = [
            'order_unique_id' => substr($orderVendor->dispatch_traking_url, strrpos($orderVendor->dispatch_traking_url, '/') + 1), // To get order unique id after slash (/).
            'order_number' => $orderVendor->orderDetail->order_number,
            'schedule_pickup' => $orderVendor->orderDetail->schedule_pickup,
            'schedule_dropoff' => $orderVendor->orderDetail->schedule_dropoff
        ];

        // Call API Here
        $dispatch_domain_laundry = $this->getDispatchLaundryDomain();

        $client = new GCLIENT([
            'headers' => [
                'personaltoken' => $dispatch_domain_laundry->laundry_service_key,
                'shortcode' => $dispatch_domain_laundry->laundry_service_key_code,
                'content-type' => 'application/json'
            ]
        ]);

        $url = $dispatch_domain_laundry->laundry_service_key_url;
        $res = $client->post($url . '/api/order/reschedule', [
            'form_params' => ($postdata)
        ]);

        $response = json_decode($res->getBody(), true);
        if ($response['status'] == 'success') {
            Session::flash('success', 'Your order has been rescheduled successfully.');
        } else {
            Session::flash('error', 'Something went wrong!');
        }
        return redirect()->back();
    }

    public function chargeForPickupRescheduling($user, $vendor, $order)
    {
        if ($user) {
            if ($user->balanceFloat > 0) {
                $wallet = $user->wallet;
                $wallet_amount_used = $user->balanceFloat;
                $payable_amount_for_pickup = $vendor->pickup_cancelling_charges;
                if ($wallet_amount_used >= $payable_amount_for_pickup) {
                    if ($wallet_amount_used > 0) {
                        $wallet->withdrawFloat($payable_amount_for_pickup, [
                            'Wallet has been <b>debited</b> for rescheduling the order on pickup day under order number <b>' . $order->order_number . '</b>'
                        ]);
                        return true;
                    }
                }
            } else {
                return false;
            }
        }
    }

    public function chargeForDropoffRescheduling($user, $vendor, $order)
    {
        if ($user) {
            if ($user->balanceFloat > 0) {
                $wallet = $user->wallet;
                $wallet_amount_used = $user->balanceFloat;
                $payable_amount = $vendor->rescheduling_charges;
                if ($wallet_amount_used >= $payable_amount) {
                    if ($wallet_amount_used > 0) {
                        $wallet->withdrawFloat($payable_amount, [
                            'Wallet has been <b>debited</b> for rescheduling the order on dropoff day under order number <b>' . $order->order_number . '</b>'
                        ]);
                        return true;
                    }
                }
            } else {
                return false;
            }
        }
    }

    public function editOrderByUser(Request $request)
    {
        try {
            $orderid = $request->orderid;
            $response = $this->editOrderInCart($orderid);
            return $response;
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return $this->errorResponse(__('Something went wrong, Please try again.'), 400);
        }
    }

    public function TrackOrder(Request $request)
    {
        $order_id = $request->order_id;
        $user_id = $request->id;
        $user = User::find($user_id);
        $order = Order::where([
            'user_id' => $user_id,
            'order_number' => $order_id
        ])->with('orderStatusVendor', 'ordervendor')->first();
        $language_id = Session::get('customerLanguage');
        $navCategories = $this->categoryNav($language_id);
        $showPage = 'd-block';
        $verifyPage = 'd-none';
        if (getAdditionalPreference([
            'is_tracking_url'
        ])['is_tracking_url'] == 1 && getAdditionalPreference([
            'is_tracking_sms_url'
        ])['is_tracking_sms_url'] == 0) {
            $showPage = 'd-block';
            $verifyPage = 'd-none';
        } else {
            if (getAdditionalPreference([
                'is_tracking_sms_url'
            ])['is_tracking_sms_url'] == 1) {

                if (isset($_COOKIE['tracking_url_' . $order_id]) || $request->verified == 1) {
                    if ($_COOKIE['tracking_url_' . $order_id] == $request->ip()) {
                        $showPage = 'd-block';
                        $verifyPage = 'd-none';
                    }
                } else {
                    if (empty($user->track_order_phone_token) && empty($user->track_order_phone_token_valid_till)) {
                        $this->sendAccessTrackingUrlSMS($user, $order);
                        $showPage = 'd-none';
                        $verifyPage = 'd-block';
                    } else {
                        $showPage = 'd-none';
                        $verifyPage = 'd-block';
                    }
                }
            }
        }

        return view('frontend.order.trackOrderDeatil')->with([
            'order' => $order,
            'navCategories' => $navCategories,
            'showPage' => $showPage,
            'verifyPage' => $verifyPage
        ]);
    }

    public function TrackOrderTokenVerify(Request $request)
    {
        $user = User::where('id', $request->data)->first();
        $order_number = $request->order_number;
        if (!$request->verifyToken) {
            return response()->json([
                'error' => __('OTP required!')
            ], 404);
        }
        $currentTime = \Carbon\Carbon::now()->toDateTimeString();

        if ($user->track_order_phone_token != $request->verifyToken) {
            return response()->json([
                'error' => __('OTP is not valid')
            ], 404);
        }
        if ($currentTime > $user->track_order_phone_token_valid_till) {
            return response()->json([
                'error' => __('OTP has been expired.')
            ], 404);
        }
        $user->track_order_phone_token = NULL;
        $user->track_order_phone_token_valid_till = NULL;
        $user->save();
        $ip = $request->ip();
        setcookie('tracking_url_' . $order_number, $ip, time() + (86400), "/"); // 86400 = 1 day
        return response()->json([
            'success' => __('OTP verified')
        ], 202);
    }

    public function ResendOtpForTrackingUrl(Request $request)
    {
        $order_id = $request->order_id;
        $user_id = $request->data;
        $user = User::find($user_id);
        $order = Order::where([
            'user_id' => $user_id,
            'order_number' => $order_id
        ])->with('orderStatusVendor', 'ordervendor')->first();
        if (!$user) {
            return response()->json([
                'error' => __('User is not valid')
            ], 404);
        }
        if (!$order) {
            return response()->json([
                'error' => __('Order is not valid')
            ], 404);
        }
        $this->sendAccessTrackingUrlSMS($user, $order);
        return response()->json([
            'success' => __('OTP send')
        ], 202);
    }

    public function discardEditOrderByUser(Request $request)
    {
        try {
            $orderid = $request->orderid;
            $response = $this->discardEditOrder($orderid);
            return $response;
        } catch (\Exception $e) {
            \Log::error($e->getMessage());
            return $this->errorResponse(__('Something went wrong, Please try again.'), 400);
        }
    }

    function calculatePrice($productVariantByRoles, $prodQuantity)
    {
        $quantity_price = 0;
        $current_price = 0;
        if (getAdditionalPreference([
            'is_corporate_user'
        ])['is_corporate_user'] == 1 && !empty($productVariantByRoles)) {
            $amount = 0;
            $quantity = 0;
            foreach ($productVariantByRoles->reverse() as $inn_key => $inn_val) {
                // if ($inn_val->role_id == Auth::user()->role_id) {
                if ($quantity < $inn_val->quantity && $inn_val->quantity <= $prodQuantity) {
                    $quantity = $inn_val->quantity;
                    $amount = $inn_val->amount;
                }
                // }
                // break;
            }
            $quantity_price = $amount * $prodQuantity;
        }
        return [
            'quantity_price' => $quantity_price,
            'amount' => $amount
        ];
    }


    function squareProductCreate()
    {
        $this->createNewProductInSquareTest();
    }

    function squareProductUpdate()
    {
        $this->updateNewProductInSquareTest();
    }

    public function orderStatusDetail($domain,$order_id)
    {
        // $is_arabic = ($request->hasHeader('language')) ? $request->header('language') : 1;
        $is_arabic = Session::get('customerLanguage');
        $is_arabic = $is_arabic == 8 ? 1 : 0;

        $order = Order::with('products')->findOrFail($order_id);
        if($order->lawyer_id){
            $lawyer_details = User::where('id',$order->lawyer_id)->first();
        }
        $product_form = json_decode($order->orderVendorProduct->user_product_order_form, true);
        if($order->ordervendor->order_status_option_id == 5 || $order->ordervendor->order_status_option_id == 6){
            $lawyer_document = LawyerDocument::where('order_id',$order->id)->where('status','!=',2)->get();
        }
        $pending_lawyer_document = LawyerDocument::where('order_id',$order->id)
        ->where('status',0)
        ->first();
        $data = [];
        $cancelOrder = 0;

        // Define labels in both English and Arabic
        $labels = [
            'request_sent' => $is_arabic ? 'تم إرسال الطلب' : 'Request Sent',
            'request_received' => $is_arabic ? 'تم استلام الطلب' : 'Request Received',
            'document_reviewed' => $is_arabic ? 'تمت مراجعة المستندات' : 'Document Reviewed',
            'approved_request' => $is_arabic ? 'تمت الموافقة على الطلب' : 'Approved Request',
            'meeting_scheduled' => $is_arabic ? 'تم تحديد موعد الاجتماع' : 'Meeting Scheduled',
            'document_submitted' => $is_arabic ? 'تم تقديم المستندات' : 'Document Submitted',
            'session_completed' => $is_arabic ? 'اكتملت الجلسة' : 'Session Completed',
            'request_new' => $is_arabic ? 'طلب جديد' : 'New Request'
        ];
  // Define status translations
        $statuses = [
            'Completed' =>  'Completed',
            'Active' =>  'Active',
            'pending' =>  'Pending',
            'Ongoing' =>  'Ongoing'
        ];

        // Define tag translations
        $tags = [
            'Completed' => 'Completed',
            'Reviewed' =>  'Reviewed'
        ];
        if ($order->ordervendor->order_status_option_id == 9 || $order->ordervendor->order_status_option_id == 1) {
            $cancelOrder = 1;
            $data[] = [
                'id' => 1,
                'title' => $labels['request_sent'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 2,
                'title' => $is_arabic ? 'مراجعة المستندات' : 'Document Review',
                'status' => $statuses['Active'],
                'description' => $is_arabic ? 'المحامي يتحقق من تفاصيلك' : 'Lawyer is verifying your details',
                'tag' => '',
            ];
            $data[] = [
                'id' => 3,
                'title' => $labels['approved_request'],
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];

            $data[] = [
                'id' => 4,
                'title' => $labels['meeting_scheduled'],
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];
            $data[] = [
                'id' => 5,
                'title' => $is_arabic ? 'مستندات المحامي' : 'Lawyer Document',
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];
            $data[] = [
                'id' => 6,
                'title' => $is_arabic ? 'تم الشراء بنجاح' : 'Successfully Purchased',
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => null,
            ];
        }

        if ($order->ordervendor->order_status_option_id == 10) {
            $cancelOrder = 1;
            $data[] = [
                'id' => 1,
                'title' => $labels['request_sent'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 2,
                'title' => $labels['document_reviewed'],
                'status' => $statuses['Completed'],
                'description' => $is_arabic ? 'المحامي يتحقق من تفاصيلك' : 'Lawyer is verifying your details',
                'tag' => $tags['Reviewed'],
            ];
            $data[] = [
                'id' => 3,
                'title' => $labels['approved_request'],
                'status' => $statuses['Active'],
                'description' => null,
                'tag' => null,
            ];

            $data[] = [
                'id' => 4,
                'title' => $labels['meeting_scheduled'],
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];
            $data[] = [
                'id' => 5,
                'title' => $is_arabic ? 'مستندات المحامي' : 'Lawyer Document',
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];
            $data[] = [
                'id' => 6,
                'title' => $is_arabic ? 'تم الشراء بنجاح' : 'Successfully Purchased',
                'status' => $statuses['Active'],
                'description' => null,
                'tag' => null,
            ];
        }
        if ($order->ordervendor->order_status_option_id == 2) {
            $data[] = [
                'id' => 1,
                'title' => $labels['request_sent'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 2,
                'title' => $labels['document_reviewed'],
                'status' => $statuses['Completed'],
                'description' => $is_arabic ? 'المحامي يتحقق من تفاصيلك' : 'Lawyer is verifying your details',
                'tag' => $tags['Reviewed'],
            ];
            $data[] = [
                'id' => 3,
                'title' => $labels['approved_request'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];

            $data[] = [
                'id' => 4,
                'title' => $labels['meeting_scheduled'],
                'status' => 'Ongoing',
                'description' => null,
                'tag' => '',
            ];
            $data[] = [
                'id' => 5,
                'title' => $is_arabic ? 'مستندات المحامي' : 'Lawyer Document',
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];
            $data[] = [
                'id' => 6,
                'title' => $is_arabic ? 'تم الشراء بنجاح' : 'Successfully Purchased',
                'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];
        }
        if ($order->ordervendor->order_status_option_id == 4) {
            $data[] = [
                'id' => 1,
                'title' => $labels['request_sent'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 2,
                'title' => $labels['document_reviewed'],
                'status' => $statuses['Completed'],
                'description' => $is_arabic ? 'المحامي يتحقق من تفاصيلك' : 'Lawyer is verifying your details',
                'tag' => $tags['Reviewed'],
            ];
            $data[] = [
                'id' => 3,
                'title' => $labels['approved_request'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];

            $data[] = [
                'id' => 4,
                'title' => $labels['meeting_scheduled'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 5,
                'title' => $is_arabic ? 'مستندات المحامي' : 'Lawyer Document',
                'status' => $statuses['Active'],
                'description' => null,
                'tag' => '',
            ];
            $data[] = [
                'id' => 6,
                'title' => $is_arabic ? 'تم الشراء بنجاح' : 'Successfully Purchased',
                 'status' => $statuses['pending'],
                'description' => null,
                'tag' => '',
            ];
        }
        if ($order->ordervendor->order_status_option_id == 5) {
            $data[] = [
                'id' => 1,
                'title' => $labels['request_sent'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 2,
                'title' => $labels['document_reviewed'],
                'status' => $statuses['Completed'],
                'description' => $is_arabic ? 'المحامي يتحقق من تفاصيلك' : 'Lawyer is verifying your details',
                'tag' => $tags['Reviewed'],
            ];
            $data[] = [
                'id' => 3,
                'title' => $labels['approved_request'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];

            $data[] = [
                'id' => 4,
                'title' => $labels['meeting_scheduled'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 5,
                'title' => $is_arabic ? 'مستندات المحامي' : 'Lawyer Document',
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 6,
                'title' => $is_arabic ? 'تم الشراء بنجاح' : 'Successfully Purchased',
                'status' => $statuses['Active'],
                'description' => null,
                'tag' => '',
            ];
        }
        if ($order->ordervendor->order_status_option_id == 6) {
            $data[] = [
                'id' => 1,
                'title' => $labels['request_sent'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 3,
                'title' => $labels['approved_request'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 2,
                'title' => $labels['document_reviewed'],
                'status' => $statuses['Completed'],
                'description' => $is_arabic ? 'المحامي يتحقق من تفاصيلك' : 'Lawyer is verifying your details',
                'tag' => $tags['Reviewed'],
            ];
            $data[] = [
                'id' => 4,
                'title' => $labels['meeting_scheduled'],
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 5,
                'title' => $is_arabic ? 'مستندات المحامي' : 'Lawyer Document',
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
            $data[] = [
                'id' => 6,
                'title' => $is_arabic ? 'تم الشراء بنجاح' : 'Successfully Purchased',
                'status' => $statuses['Completed'],
                'description' => null,
                'tag' => $tags['Completed']
            ];
        }
        $pending_document = 0;
        if ($pending_lawyer_document) {
            $pending_document = 1;
        }
        $client_documents = '';
        if (Auth::check() && $order_id) {
            $client_documents = ClientDocument::where('client_id', Auth::id())
                ->where('order_id', $order_id)
                ->get();
        }
        if ($client_documents) {
            $clientDocuments = $client_documents;
        }
        if ($order->ordervendor->order_status_option_id == 3) {
            $isRejected = 1;
        }
        
        $recentLawyerDocument = LawyerDocument::where('order_id', $order_id)
        ->orderBy('created_at', 'desc')
        ->first();
        if (isset($recentLawyerDocument) && $recentLawyerDocument->status == 2) {
            $rejected_lawyer_doc_reason = $recentLawyerDocument->reject_reason;
        }

        $response = [
            'status' => 200,
            'data' => $data,
            'product' => $order->products,
            'pending_document' => $pending_document,
            'lawyer_document' => $lawyer_document ?? null,
            'rejected_lawyer_doc_reason' => $rejected_lawyer_doc_reason ?? null,
            'client_documents' => $clientDocuments,
            'reorder' => $order->ordervendor->order_status_option_id == 6 ? 1 : 0,
            'product_form' => $product_form,
            'cancelOrder' => $cancelOrder,
            'has_ongoing_meeting' => (int) JitsiMeeting::query()->where('order_id', $order_id)
                ->whereRelation('invitation', 'user_id', $order->lawyer_id)
                ->exists(),
                'lawyer_details' => $lawyer_details ?? null
        ];

        // Add `isRejected` only if it is true
        if ($order->ordervendor->order_status_option_id == 3) {
            $response['isRejected'] = 1;
        }
        return view('frontend.account.order-status-detail',compact('response'));
    }

}
