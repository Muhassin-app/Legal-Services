<?php

namespace App\Http\Controllers\Client;

use App\Http\Controllers\AhoyController;
use Auth;
use Session;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Http;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Storage;
use App\Http\Controllers\Client\BaseController;
use App\Http\Controllers\Client\BorzoeDeliveryController;
use App\Http\Controllers\D4BDunzoController;
use App\Http\Controllers\Front\LalaMovesController;
use App\Http\Controllers\ShiprocketController;
use App\Http\Controllers\DunzoController;
use App\Http\Controllers\RoadieController;
use Illuminate\Support\Facades\Validator;
use App\Http\Controllers\Front\QuickApiController;
use App\Models\RescheduleOrder;

use App\Models\{OrderStatusTime, OrderDocument, Tax, Order, User, VendorOrderDispatcherStatus, OrderStatusOption, Nomenclature, NomenclatureTranslation, DispatcherStatusOption, VendorOrderStatus, ClientPreference, NotificationTemplate, OrderProduct, OrderVendor, UserAddress, Vendor, OrderReturnRequest, UserDevice, UserVendor, LuxuryOption, ClientCurrency, UserDocs, UserRegistrationDocuments, OrderCancelRequest, CaregoryKycDoc, ThirdPartyAccounting, OrderVendorReport, OrderRefund, Wallet, OrderProductDispatchRoute, ProductVariant, Cart, ClientPreferenceAdditional, OrderLongTermServices, Currency, EmailTemplate, LawyerDocument, ProcessorProduct, OrderLongTermServiceSchedule, Product, OrderProductDispatchReturnRoute, OrderVendorProduct, VendorOrderProductStatus, ProductBooking};
use App\Models\Client as ClientData;

use GuzzleHttp\Client;
use App\Models\Client as CP;
use App\Models\Transaction;
use App\Models\AutoRejectOrderCron;
use App\Http\Traits\{ApiResponser, OrderTrait, MargTrait, TaxJarTrait, OrderBlockchain};
use Log;
use Carbon\Carbon;
use Carbon\CarbonPeriod;
use App\Models\{LoyaltyCard, Payment, VendorMargConfig, VendorOrderCancelReturnPayment};
use App\Http\Controllers\Front\ShipEngineController;
use App\Http\Traits\Borzoe;
use Illuminate\Support\Facades\DB;

class OrderController extends BaseController
{
    use Borzoe;

    private $folderName = '/order/reports';

    use ApiResponser, OrderTrait, MargTrait, OrderBlockchain, TaxJarTrait {
        TaxJarTrait::__construct as TaxJarTraitConstruct;
    }
    public $from_date;
    public $to_date;
    public $setWeekDate;
    function __construct()
    {
        $this->TaxJarTraitConstruct();
        $this->from_date = Carbon::now()->startOfDay()->subDays(7);
        $this->to_date = Carbon::now()->endOfDay();
        $this->setWeekDate =  $this->from_date->format('d M Y') . ' to ' . $this->to_date->format('d M Y');
    }


    /**
     * Display a listing of the resource.
     *
     * @return \Illuminate\Http\Response
     */
    public function index(Request $request)
    {
        $client_preferences = ClientPreference::first();
        $EnabledLuxuryOptions = $this->geteEnabledLuxuryOptions($client_preferences);
        $user = Auth::user();
        $return_requests = OrderReturnRequest::where('status', 'Pending')->where('type', 1);
        $rescheduleOrderCount = RescheduleOrder::count();
        if ($user->is_superadmin == 0) {
            $return_requests = $return_requests->whereHas('order.vendors.vendor.permissionToUser', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        $return_requests = $return_requests->count();

        $return_form_requests = OrderProductDispatchReturnRoute::where('dispatcher_status_option_id', 4);

        $returnFormRequestCount = $return_form_requests->count();

        // cancel order requests
        $cancel_order_requests = OrderCancelRequest::where('status', 0);
        if ($user->is_superadmin == 0) {
            $cancel_order_requests = $cancel_order_requests->whereHas('order.vendors.vendor.permissionToUser', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        $cancel_order_requests = $cancel_order_requests->count();

        // Pending counts
        $pending_order_count = Order::onlyEnabledLuxuryOptions($EnabledLuxuryOptions)->with('vendors')->whereHas('vendors', function ($query) use ($user) {
            $query->where('order_status_option_id', 1);
            if ($user->is_superadmin == 0) {
                $query->whereHas('vendor.permissionToUser', function ($query1) use ($user) {
                    $query1->where('user_id', $user->id);
                });
            }
        });
        if ($user->is_superadmin == 0) {
            $pending_order_count = $pending_order_count->whereHas('vendors.vendor.permissionToUser', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        $pending_order_count = $pending_order_count->where(function ($q1) {
            $q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1, 38]); // 1 for cod ,38 for offline manual by harbans
            $q1->orWhere(function ($q2) {
                $q2->whereIn('payment_option_id', [1, 38]) // 1 for cod ,38 for offline manual by harbans
                    ->orWhere(function ($q3) {


                        $q3->where('is_postpay', 1) // 1 for order is post pay.
                            ->whereNotIn('payment_option_id', [1, 38]);
                    });
            });
        });

        $pending_order_count = $pending_order_count->between($this->from_date, $this->to_date)->count();

        // past orders count
        $past_order_count = Order::onlyEnabledLuxuryOptions($EnabledLuxuryOptions)->with('vendors')->whereHas('vendors', function ($query) use ($user) {
            $query->whereIn('order_status_option_id', [6, 3]);
            if ($user->is_superadmin == 0) {
                $query->whereHas('vendor.permissionToUser', function ($query1) use ($user) {
                    $query1->where('user_id', $user->id);
                });
            }
        });
        if ($user->is_superadmin == 0) {
            $past_order_count = $past_order_count->whereHas('vendors.vendor.permissionToUser', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        $past_order_count = $past_order_count->where(function ($q1) {
            $q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1, 38]); // 1 for cod ,38 for offline manual by harbans
            $q1->orWhere(function ($q2) {
                $q2->whereIn('payment_option_id', [1, 38])
                    ->orWhere(function ($q3) {
                        $q3->where('is_postpay', 1) // 1 for order is post pay.
                            ->whereNotIn('payment_option_id', [1, 38]);
                    });
            });
        });
        $past_order_count = $past_order_count->between($this->from_date, $this->to_date)->count();

        // active orders count
        $active_order_count = Order::onlyEnabledLuxuryOptions($EnabledLuxuryOptions)->with('vendors')->whereHas('vendors', function ($query) use ($user) {
            $query->whereIn('order_status_option_id', [2, 4, 5, 9, 10]);
            if ($user->is_superadmin == 0) {
                $query->whereHas('vendor.permissionToUser', function ($query1) use ($user) {
                    $query1->where('user_id', $user->id);
                });
            }
        });
        if ($user->is_superadmin == 0) {
            $active_order_count = $active_order_count->whereHas('vendors.vendor.permissionToUser', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        $active_order_count = $active_order_count->where(function ($q1) {
            // 1 for cod ,38 for offline manual by harbans
            $q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1, 38]);
            $q1->orWhere(function ($q2) {
                $q2->whereIn('payment_option_id', [1, 38])
                    ->orWhere(function ($q3) {


                        $q3->where('is_postpay', 1) // 1 for order is post pay.
                            ->whereNotIn('payment_option_id', [1, 38]);
                    });
            });
        });
        $active_order_count = $active_order_count->between($this->from_date, $this->to_date)->count();
        //count management for lawyer
        
        if (Auth::user()->hasRole('Lawyer') && Auth::user()->is_superadmin == 0) {
            $pending_order_count = Order::where('lawyer_id', Auth::user()->id)->wherehas('ordervendor', function ($query) {
                $query->where('order_status_option_id', 9)->between($this->from_date, $this->to_date);
            })->count();
            // dd($pending_order_count);
            $past_order_count = Order::where('lawyer_id', Auth::user()->id)->wherehas('ordervendor', function ($query) {
                $query->whereIn('order_status_option_id', [6, 3])->between($this->from_date, $this->to_date);
            })->count();
            $active_order_count = Order::where('lawyer_id', Auth::user()->id)->wherehas('ordervendor', function ($query) {
                $query->whereIn('order_status_option_id', [2, 4, 5, 10])->between($this->from_date, $this->to_date);
            })->count();
        }
        // all vendors
        $vendors = Vendor::where('status', '!=', '2')->orderBy('id', 'desc');
        if ($user->is_superadmin == 0) {
            $vendors = $vendors->whereHas('permissionToUser', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        $vendors = $vendors->get();
        $clientCurrency = ClientCurrency::where('is_primary', 1)->first();

        $langId = Session::get('customerLanguage');
        $fixedFee = $this->fixedFee($langId);
        $accounting = ThirdPartyAccounting::where('status', 1)->get();
        $del_order_count = OrderVendor::has('accounting', '<', 1)->where('order_status_option_id', 6)->count();
        $request->merge(['response' => 2, 'filter_order_status' => 'pending_orders']);
        $OrderFilterData = $this->postOrderFilter($request);
        $setWeekDate = $this->setWeekDate;
        $role = Role::findById(7);
        $lawyers = $role->users()->where('status', 1)->get();
        return view('backend.order.index', compact('lawyers', 'return_requests', 'cancel_order_requests', 'pending_order_count', 'active_order_count', 'past_order_count', 'clientCurrency', 'vendors', 'fixedFee', 'accounting', 'del_order_count', 'rescheduleOrderCount', 'client_preferences', 'OrderFilterData', 'setWeekDate', 'returnFormRequestCount'));
    }
    public function geteEnabledLuxuryOptions($clientPreference)
    {
        $LuxuryOptions = [];
        // $enabled_vendor_types = [];
        foreach (config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value) {
            $clientVendorTypes = $vendor_typ_key . '_check';
            if ($clientPreference->$clientVendorTypes == 1) {
                $vendor_type_name = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key;
                // $enabled_vendor_types[] = $vendor_type_name;
                $LuxuryOptions[] = config('constants.VendorTypesLuxuryOptions.' . $vendor_typ_key);
            }
        }
        return $LuxuryOptions;
        // pr($LuxuryOptions);
    }

    public function postOrderFilter(Request $request, $domain = '')
    {

        $response = [];
        $user = Auth::user();
        $preferences = ClientPreference::first();
        $EnabledLuxuryOptions = $this->geteEnabledLuxuryOptions($preferences);
        $client_timezone = DB::table('clients')->first('timezone');
        $user->timezone = $client_timezone->timezone ?? $user->timezone;
        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;
        $filter_order_status = $request->filter_order_status;
        $HasGiftCard = 0;
        $ClassName = $request->has('className') ? $request->className : 'col-xl-6';

        $orders = Order::onlyEnabledLuxuryOptions($EnabledLuxuryOptions)->with(['vendors.products' => function ($q) {
            $q->withoutAppends();
        }, 'vendors.products.translation' => function ($q) use ($langId) {
            $q->where('language_id', $langId);
        }, 'vendors.status', 'orderStatusVendor', 'address', 'user']);


        $orders = $orders->with(['vendors.exchanged_of_order.orderDetail', 'vendors.exchanged_to_order.orderDetail']);


        if ($user->is_superadmin == 0) {
            if ($user->hasRole('Lawyer')) {
                $orders = $orders->where('lawyer_id', $user->id);
            } else {
                $orders = $orders->whereHas('vendors.vendor.permissionToUser', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            }
        }

        $order_count = Order::onlyEnabledLuxuryOptions($EnabledLuxuryOptions)->with('vendors')->where(function ($q1) {
            // 1 for cod ,38 for offline manual by harbans
            $q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1, 38]);
            $q1->orWhere(function ($q2) {
                $q2->whereIn('payment_option_id', [1, 38])
                    ->orWhere(function ($q3) {


                        $q3->where('is_postpay', 1) // 1 for order is post pay
                            ->whereNotIn('payment_option_id', [1, 38]);
                    });
            });
        })->orderBy('id', 'asc');
        if ($user->is_superadmin == 0) {
            $order_count = $order_count->whereHas('vendors.vendor.permissionToUser', function ($query) use ($user) {
                $query->where('user_id', $user->id);
            });
        }
        //filer bitween date
        if (!empty($request->get('date_filter'))) {
            $date_date_filter = explode(' to ', $request->get('date_filter'));
            $to_date = (!empty($date_date_filter[1])) ? $date_date_filter[1] : $date_date_filter[0];
            $from_date = date("Y-m-d", strtotime($date_date_filter[0]));
            $to_date = date("Y-m-d", strtotime($to_date));
            $orders->between($from_date . " 00:00:00", $to_date . " 23:59:59");


            //order_count
            $order_count->between($from_date . " 00:00:00", $to_date . " 23:59:59");
        } else {
            $orders->between($this->from_date, $this->to_date);
            //order_count
            $order_count->between($this->from_date, $this->to_date);
        }
        //get by vendor
        if (!empty($request->get('vendor_id'))) {
            $order_count->whereHas('vendors', function ($query)  use ($request) {
                $query->where('vendor_id', $request->get('vendor_id'));
            });
        }

        $HasGiftCard = 1;
        $orders  = $orders->with(['giftCard']);

        //Search by keyword
        if (!empty($request->search_keyword)) {
            $order_count->whereHas('address', function ($query) use ($request) {
                $query->where('house_number', 'like', '%' . $request->search_keyword . '%')
                    ->orWhere('address', 'like', '%' . $request->search_keyword . '%')
                    ->orWhere('street', 'like', '%' . $request->search_keyword . '%')
                    ->orWhere('city', 'like', '%' . $request->search_keyword . '%')
                    ->orWhere('state', 'like', '%' . $request->search_keyword . '%')
                    ->orWhere('pincode', 'like', '%' . $request->search_keyword . '%')
                    ->orWhere('country', 'like', '%' . $request->search_keyword . '%');
            })->orWhere('order_number', 'like', '%' . $request->search_keyword . '%');
            $orders->whereHas('address', function ($query) use ($request) {
                $query->where('house_number', 'like', '%' . $request->search_keyword . '%')
                    ->orWhere('address', 'like', '%' . $request->search_keyword . '%')
                    ->orWhere('street', 'like', '%' . $request->search_keyword . '%')
                    ->orWhere('city', 'like', '%' . $request->search_keyword . '%')
                    ->orWhere('state', 'like', '%' . $request->search_keyword . '%')
                    ->orWhere('pincode', 'like', '%' . $request->search_keyword . '%')
                    ->orWhere('country', 'like', '%' . $request->search_keyword . '%');
            })->orWhere('order_number', 'like', '%' . $request->search_keyword . '%');
        }
        // dd(count($order_count->get()));
        $pending_orders = clone $order_count;
        $active_orders = clone $order_count;
        $orders_history = clone $order_count;
        $auto_accept = 0;


        $lux_id = 0;
        if (isset($request->order_type)) {
            $lux_id = LuxuryOption::where('title', $request->order_type)->value('id');
        }

        if ($filter_order_status) {
            switch ($filter_order_status) {
                case 'pending_orders':
                    
                    $orders = $orders->with('vendors', function ($query) use ($user) {
                        if ($user->hasRole('Lawyer')) {
                            $query->whereIn('order_status_option_id', [9,10]);
                        } else {
                            $query->where('order_status_option_id', 1);
                        }
                        if ($user->is_superadmin == 0 && !$user->hasRole('Lawyer')) {
                            $query->whereHas('vendor.permissionToUser', function ($query1) use ($user) {
                                $query1->where('user_id', $user->id);
                            });
                        }
                    })->whereHas('vendors', function ($query) use ($request, $user) {
                        if ($user->hasRole('Lawyer')) {
                            $query->whereIn('order_status_option_id', [9,10]);
                        } else {
                            $query->where('order_status_option_id', 1);
                        }
                        if (!empty($request->get('vendor_id'))) {
                            $query->where('vendor_id', $request->get('vendor_id'));
                        }
                    });
                    break;


                case 'active_orders':
                    if ($user->hasRole('Lawyer')) {
                        $order_status_options = [2, 4, 5];
                    } else {
                        $order_status_options = [9, 10, 2, 4, 5];
                    }
                    $orders = $orders->with(['vendors' => function ($query) use ($order_status_options, $user) {
                        $query->whereRaw('ROUND(order_status_option_id, 1) IN (' . implode(',', $order_status_options) . ')');
                        if ($user->is_superadmin == 0 && !$user->hasRole('Lawyer')) {
                            $query->whereHas('vendor.permissionToUser', function ($query1) use ($user) {
                                $query1->where('user_id', $user->id);
                            });
                        }
                    }, 'vendors.acceptedBy'])
                        ->whereHas('vendors', function ($query) use ($order_status_options, $request) {
                            $query->whereRaw('ROUND(order_status_option_id, 1) IN (' . implode(',', $order_status_options) . ')');
                            if (!empty($request->get('vendor_id'))) {
                                $query->where('vendor_id', $request->get('vendor_id'));
                            }
                        });
                    break;

                case 'orders_history':
                    $order_status_options = [6, 3];
                    $orders = $orders->with(['vendors' => function ($query) use ($order_status_options, $user) {
                        $query->whereIn('order_status_option_id', $order_status_options);
                        if ($user->is_superadmin == 0 && !$user->hasRole('Lawyer')) {
                            $query->whereHas('vendor.permissionToUser', function ($query1) use ($user) {
                                $query1->where('user_id', $user->id);
                            });
                        }
                    }, 'vendors.cancelledBy', 'vendors.acceptedBy'])
                        ->whereHas('vendors', function ($query) use ($order_status_options, $request) {
                            $query->whereIn('order_status_option_id', $order_status_options);
                            if (!empty($request->get('vendor_id'))) {
                                $query->where('vendor_id', $request->get('vendor_id'));
                            }
                        });
                    break;

                case 'rental_pending_delivery':
                    $order_status_options = [1, 6];
                    $orders = $orders->with(['vendors' => function ($query) use ($order_status_options, $user) {

                        if ($user->is_superadmin == 0 && !$user->hasRole('Lawyer')) {
                            $query->whereHas('vendor.permissionToUser', function ($query1) use ($user) {
                                $query1->where('user_id', $user->id);
                            });
                        }
                    }])->whereHas('vendors.products.Routes', function ($query) {
                        $query->where('dispatcher_status_option_id', '!=', 5);
                    })
                        ->whereHas('vendors', function ($query) use ($order_status_options, $request) {
                            $query->whereNotIn('order_status_option_id', $order_status_options);
                            if (!empty($request->get('vendor_id'))) {
                                $query->where('vendor_id', $request->get('vendor_id'));
                            }
                        });
                    break;
                case 'rental_running_product':
                    $order_status_options = [6];
                    $orders = $orders->with(['vendors' => function ($query) use ($user) {

                        if ($user->is_superadmin == 0 && !$user->hasRole('Lawyer')) {
                            $query->whereHas('vendor.permissionToUser', function ($query1) use ($user) {
                                $query1->where('user_id', $user->id);
                            });
                        }
                    }])->whereHas('vendors.products.Routes', function ($query) {
                        $query->where('dispatcher_status_option_id', '=', 5);
                    })
                        ->whereHas('vendors', function ($query) use ($order_status_options, $request) {
                            // $query->whereIn('order_status_option_id', $order_status_options);
                            if (!empty($request->get('vendor_id'))) {
                                $query->where('vendor_id', $request->get('vendor_id'));
                            }
                        });
                    break;
                case 'rental_pending_return':
                    $order_status_options = [6];
                    $orders = $orders->with(['vendors' => function ($query) use ($order_status_options, $user) {

                        if ($user->is_superadmin == 0 && !$user->hasRole('Lawyer')) {
                            $query->whereHas('vendor.permissionToUser', function ($query1) use ($user) {
                                $query1->where('user_id', $user->id);
                            });
                        }
                    }])->whereHas('vendors.products.Routes', function ($query) {
                        $query->where('dispatcher_status_option_id', '=', 5);
                    })
                        ->whereHas('vendors', function ($query) use ($order_status_options, $request) {
                            // $query->whereIn('order_status_option_id', $order_status_options);
                            if (!empty($request->get('vendor_id'))) {
                                $query->where('vendor_id', $request->get('vendor_id'));
                            }
                        });
                    break;

                    // /* luxury option orders */
                    // case 'delivery_orders':
                    //     $orders = $filter_orders->where('luxury_option_id', 1);
                    //     break;

                    // case 'dine_in_orders':
                    //     $orders = $filter_orders->where('luxury_option_id', 2);
                    //     break;

                    // case 'takeaway_orders':
                    //     $orders = $filter_orders->where('luxury_option_id', 3);
                    //     break;

                    // case 'rental_orders':
                    //     $orders = $filter_orders->where('luxury_option_id', 4);
                    //     break;

                    // case 'pick_drop_orders':
                    //     $orders = $filter_orders->where('luxury_option_id', 5);
                    //     break;

                    // case 'on_demand_orders':
                    //     $orders = $filter_orders->where('luxury_option_id', 6);
                    //     break;

                    // case 'laundry_orders':
                    //     $orders = $filter_orders->where('luxury_option_id', 7);
                    //     break;
                    // /* luxury option orders */
            }
        }
        
        $orders = $orders->whereHas('vendors')->where(function ($q1) {
            // 1 for cod ,38 for offline manual by harbans
            $q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1, 38]);
            $q1->orWhere(function ($q2) {
                $q2->whereIn('payment_option_id', [1, 38])
                    ->orWhere(function ($q3) {


                        $q3->where('is_postpay', 1)
                            ->whereNotIn('payment_option_id', [1, 38]);
                    });
            });
        });
        //sort by distance
        if ($request->has('sort_order') && ($request->sort_order == 'distance')) {
            if (($preferences) && ($preferences->is_hyperlocal == 1)) {
                $latitude = ($preferences->Default_latitude) ? floatval($preferences->Default_latitude) : null;
                $longitude = ($preferences->Default_longitude) ? floatval($preferences->Default_longitude) : null;
                $distance_unit = (!empty($preferences->distance_unit_for_time)) ? $preferences->distance_unit_for_time : 'kilometer';
                //3961 for miles and 6371 for kilometers
                $calc_value = ($distance_unit == 'mile') ? 3961 : 6371;
                if (!empty($latitude) && !empty($longitude)) {
                    $orders = $orders->select('*', 'id as total_discount_calculate', DB::raw(' ( ' . $calc_value . ' * acos( cos( radians(' . $latitude . ') ) *
                            cos( radians( user_latitude ) ) * cos( radians( user_longitude ) - radians(' . $longitude . ') ) +
                            sin( radians(' . $latitude . ') ) *
                            sin( radians( user_latitude ) ) ) )  AS sortByUserDistance'));
                    $orders = $orders->orderBy(DB::raw('ISNULL(sortByUserDistance), sortByUserDistance'), 'ASC');
                }
            }
        } elseif ($request->has('sort_order') && ($request->sort_order == 'newest_slot')) {
            // $now = Carbon::now()->toDateTimeString();
            $orders = $orders->orderBy(DB::raw('ISNULL(scheduled_date_time), scheduled_date_time'), 'ASC')->orderBy('created_at', 'DESC');
        } else {
            $orders = $orders->select('*', 'id as total_discount_calculate')->orderBy('id', 'DESC');
        }
        // set order vendor type variables to get count

        foreach (config('constants.VendorTypes') as $vendor_typ_key => $vendor_typ_value) {
            $clientVendorTypes = $vendor_typ_key . '_check';
            $VendorTypesName = $vendor_typ_key == "dinein" ? 'dine_in' : $vendor_typ_key;

            if ($preferences->$clientVendorTypes == 1) {
                $vendorTypeOrders = $VendorTypesName . '_orders';

                $$vendorTypeOrders = clone $orders;
                $luxury_option_id = config('constants.VendorTypesLuxuryOptions.' . $vendor_typ_key);
                $$vendorTypeOrders = $$vendorTypeOrders->where('luxury_option_id', $luxury_option_id)->count();
                $response[$vendorTypeOrders] = $$vendorTypeOrders;
            }
        }
        if ($lux_id > 0) {
            $orders = $orders->where('luxury_option_id', $lux_id);
        }
        $orders = $orders->paginate(20);
        //  pr($orders->first()->vendors->toArray());
        // Pending orders count
        $pending_orders = $pending_orders->with('vendors', function ($query) use ($user) {
            $query->where('order_status_option_id', 1);
            if ($user->is_superadmin == 0) {
                $query->whereHas('vendor.permissionToUser', function ($query1) use ($user) {
                    $query1->where('user_id', $user->id);
                });
            }
        })->whereHas('vendors', function ($query) {
            $query->where('order_status_option_id', 1);
        })->count();

        // Active orders count
        if($user->hasRole('Lawyer')){
            $order_status_optionsa = [2, 4, 5];
        }else{
            $order_status_optionsa = [2, 4, 5, 9, 10];
        }
        $active_orders = $active_orders->with('vendors', function ($query) use ($order_status_optionsa, $user) {
            $query->whereIn('order_status_option_id', $order_status_optionsa);
            if ($user->is_superadmin == 0) {
                $query->whereHas('vendor.permissionToUser', function ($query1) use ($user) {
                    $query1->where('user_id', $user->id);
                });
            }
        })->whereHas('vendors', function ($query) use ($order_status_optionsa) {
            $query->whereIn('order_status_option_id', $order_status_optionsa);
        })->count();

        // Past orders count
        $order_status_optionsd = [6, 3];
        $orders_history = $orders_history->with('vendors', function ($query) use ($order_status_optionsd, $user) {
            $query->whereIn('order_status_option_id', $order_status_optionsd);
            if ($user->is_superadmin == 0) {
                $query->whereHas('vendor.permissionToUser', function ($query1) use ($user) {
                    $query1->where('user_id', $user->id);
                });
            }
        })->whereHas('vendors', function ($query) use ($order_status_optionsd) {
            $query->whereIn('order_status_option_id', $order_status_optionsd);
        })->count();

        //count management for lawyer
        if (Auth::user()->hasRole('Lawyer') && Auth::user()->is_superadmin == 0) {
            $pending_orders = Order::where('lawyer_id', Auth::user()->id)->wherehas('ordervendor', function ($query) {
                $query->where('order_status_option_id', 9,10)->between($this->from_date, $this->to_date);
            })->count();
            $orders_history = Order::where('lawyer_id', Auth::user()->id)->wherehas('ordervendor', function ($query) {
                $query->whereIn('order_status_option_id', [6, 3])->between($this->from_date, $this->to_date);
            })->count();
            $active_orders = Order::where('lawyer_id', Auth::user()->id)->wherehas('ordervendor', function ($query) {
                $query->whereIn('order_status_option_id', [2, 4, 5])->between($this->from_date, $this->to_date);
            })->count();
        }
        foreach ($orders as $key => $order) {

            $giftCardUsed = 0;
            $giftCardName = '';
            if ($HasGiftCard == 1) {
                if ($order->gift_card_id != '' && !empty($order->giftCard)) {
                    $giftCardUsed = 1;
                    $giftCardName = $order->giftCard ? $order->giftCard->name : 'NA';
                }
            }
            $order->giftCardUsed = $giftCardUsed;
            $order->giftCardName = $giftCardName;
            // $order->created_date = convertDateTimeInTimeZone($order->created_at, $user->timezone, 'd-m-Y, h:i A');
            $order->created_date = dateTimeInUserTimeZone($order->created_at, $user->timezone);
            $scheduled_date_time = !empty($order->scheduled_date_time) ? dateTimeInUserTimeZone($order->scheduled_date_time, $user->timezone) : '';

            $order->total_other_taxes_amount  = 0.00;
            // foreach (explode(":", $order->total_other_taxes) as $row) {
            //   $total_other_taxes += (float)$row;
            //}
            if (!empty($order->total_other_taxes)) {
                $order->total_other_taxes_amount  = (float) array_sum(explode(":", $order->total_other_taxes));
            } else {
                $order->total_other_taxes_amount = $order->taxable_amount;
            }

            foreach ($order->vendors as $vendor) {
                $vendor->isAlert = false;
                $vendor->alertMessage = "";
                $auto_accept = (isset($vendor) && !empty($vendor->auto_accept_order)) ? $vendor->auto_accept_order : 0;
                if (isset($vendor) && !empty($vendor->vendor_id))
                    $vendor->vendor_detail_url = route('order.show.detail', [$order->id, @$vendor->vendor_id]);
                else
                    $vendor->vendor_detail_url = '#';
                if (isset($vendor) && !empty($vendor->vendor_id) && @$vendor->exchanged_to_order) {
                    $vendor->exchanged_to_order->vendor_detail_url = route('order.show.detail', [$vendor->exchanged_to_order->order_id, @$vendor->exchanged_to_order->vendor_id]);
                }

                if (isset($vendor) && !empty($vendor->vendor_id && @$vendor->exchanged_of_order)) {
                    $vendor->exchanged_of_order->vendor_detail_url = route('order.show.detail', [$vendor->exchanged_of_order->order_id, @$vendor->exchanged_of_order->vendor_id]);
                }
                $vendor_order_status = VendorOrderStatus::with('OrderStatusOption')->where('order_id', $order->id)->where('vendor_id', $vendor->vendor_id)->orderBy('id', 'DESC')->first();
                $vendor->order_status = $vendor_order_status ? __($vendor_order_status->OrderStatusOption->title) : '';
                $vendor->order_vendor_id = $vendor_order_status ? $vendor_order_status->order_vendor_id : '';
                $vendor->vendor_name = $vendor->vendor->name ?? '';
                $product_total_count = 0;
                $security_amount = 0;
                if ($vendor->products->count() == 0) {
                    $orders->forget($key);
                }

                foreach ($vendor->products as $product) {
                    $product_total_count += $product->quantity * $product->price;
                    $security_amount += $product->security_amount;
                    $product->product_title = isset($product->translation) ? $product->translation->title : $product->product_name;
                    $product->image_path  = $product->media->first() &&  !is_null($product->media->first()->image) ? $product->media->first()->image->path : getDefaultImagePath();
                    if (!is_null($product->product) && ($product->has_inventory != 0) && ($product->quantity > ($product->product->variant->first() ? $product->product->variant[0]->quantity : 0))) {
                        $vendor->isAlert = true;
                        $vendor->alertMessage = __("You are low on stock");
                    }
                }

                if ($vendor->delivery_fee > 0) {
                    $order_pre_time = ($vendor->order_pre_time > 0) ? $vendor->order_pre_time : 0;
                    $user_to_vendor_time = ($vendor->user_to_vendor_time > 0) ? $vendor->user_to_vendor_time : 0;
                    $ETA = $order_pre_time + $user_to_vendor_time;
                    // $vendor->ETA = ($ETA > 0) ? $this->formattedOrderETA($ETA, $vendor->created_at, $order->scheduled_date_time) : convertDateTimeInTimeZone($vendor->created_at, $user->timezone, 'h:i A');
                    $vendor->ETA = ($ETA > 0) ? $this->formattedOrderETA($ETA, $vendor->created_at, $order->scheduled_date_time) : dateTimeInUserTimeZone($vendor->created_at, $user->timezone);
                    //$order->converted_scheduled_date_time = $order->scheduled_date_time;
                    $order->converted_scheduled_date_time = $scheduled_date_time;
                }

                $vendor->product_total_count = $product_total_count;
                $vendor->final_amount = $vendor->taxable_amount + $product_total_count;
            }

            $luxury_option_name = '';
            if ($order->luxury_option_id > 0) {
                $luxury_option = LuxuryOption::where('id', $order->luxury_option_id)->first();
                if ($luxury_option->title == 'takeaway') {
                    $luxury_option_name = getNomenclatureName('Takeaway', $langId, false);
                } elseif ($luxury_option->title == 'dine_in') {
                    $luxury_option_name = getNomenclatureName('Dine-In', $langId, false);
                } elseif ($luxury_option->title == 'on_demand') {
                    $luxury_option_name = getNomenclatureName('Services', $langId, false);
                } else {
                    $luxury_option_name = getNomenclatureName($luxury_option->title, $langId, false);
                    //$luxury_option_name = 'Delivery';
                }
                $luxury_option_name = ucwords(str_replace('_', ' ', $luxury_option_name));
            }
            $order->luxury_option_name = $luxury_option_name;
            if ($order->vendors->count() == 0) {
                $orders->forget($key);
            }
            $order->scheduled_date_time = $scheduled_date_time;
            $order->security_amount = $security_amount ?? '';
        }
        $admincurrency = ClientCurrency::getAdminCurrencySymbol();

        $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
        $langId = Session::get('customerLanguage');
        $fixedFee = $this->fixedFee($langId);
        $response['orders'] = $orders;
        $response['admin_currency'] = $admincurrency;
        $filter_order_status = $request->filter_order_status;
        $response2['next_page_url'] = @$orders->toArray()['next_page_url'];
        $response2['active_orders'] = $active_orders ?? 0;
        $response2['orders_history'] = $orders_history ?? 0;
        $response2['pending_orders'] = $pending_orders ?? 0;
        $response2['delivery_orders'] = $delivery_orders ?? 0;
        $response2['dine_in_orders'] = $dine_in_orders ?? 0;
        $response2['takeaway_orders'] = $takeaway_orders ?? 0;
        $response2['rental_orders'] = $rental_orders ?? 0;
        $response2['pick_drop_orders'] = $pick_drop_orders ?? 0;
        $response2['on_demand_orders'] = $on_demand_orders ?? 0;
        $response2['laundry_orders'] = $laundry_orders ?? 0;
        $response2['appointment_orders'] = $appointment_orders ?? 0;
        $response2['p2p_orders'] = $p2p_orders ?? 0;
        $response2['pagination'] = '';
        $response2['ClassName'] = $ClassName;
        $response2['auto_accept_status'] = $auto_accept;
        $clientData = ClientData::first();

        if (!empty($response2['next_page_url'])) {
            // $nextPageUrl = str_replace("/order","/orders/filter",$response2['next_page_url']);
            $url_components = parse_url($response2['next_page_url']);
            parse_str($url_components['query'], $params);
            $page_num = $params['page'];

            // $page = $request->has('page') ? $request->page : 1;
            $nextPageUrl = route('orders.filter') . '?page=' . $page_num;
            // pr( $nextPageUrl);
            $pagination = '<div class="col-md-4 offset-md-4 text-center">
                            <button class="ladda-button btn btn-primary load-more-btn" dir="ltr" data-style="expand-left" data-url="' . $nextPageUrl . '" data-rel="' . $filter_order_status . '">
                                <span class="ladda-label">' . __('Load More') . '</span>
                                <span class="ladda-spinner"></span>
                                <div class="ladda-progress" style="width: 0px;"></div>
                            </button>
                        </div>';
            $response2['pagination'] = $pagination;
        }
        $response2['count_resp'] = count($orders);
        $response2['html'] = \View::make('backend.order.order-parts.orderTable', array('ClassName' => $ClassName, 'orders' =>  $response, 'client_preferences' => $preferences, 'clientCurrency' => $clientCurrency, 'filter_order_status' => $filter_order_status, 'fixedFee' => $fixedFee, 'clientData' => $clientData))->render();
        if ($request->response == 2) {
            return $response2;
        }
        return $this->successResponse($response2, '', 201);
    }

    public function uploadReport(Request $request)
    {
        $checkpreviousrecord = OrderVendorReport::where(['order_id' => $request->order_id])->first();
        if ($checkpreviousrecord) {
            $vendorreport = OrderVendorReport::where('id', $checkpreviousrecord->id)->first();
        } else {
            $vendorreport = new OrderVendorReport();
        }
        if ($request->hasFile('file_name')) {    /* upload logo file */
            $file = $request->file('file_name');
            $vendorreport->report = Storage::disk('s3')->put($this->folderName, $file, 'public');
        }
        $vendorreport->order_id = $request->order_id;
        $vendorreport->vendor_id = $request->vendor_id;
        $vendorreport->save();
        return redirect()->back()->with('success', __("Report added successfully"));
    }


    public function deleteReport(Request $request, $domain = '', $reportId = 0)
    {
        $report = OrderVendorReport::findOrfail($reportId);
        $report->delete();
        return redirect()->back()->with('success', 'Report deleted successfully!');
    }
    /**
     * Display the order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */

    public function getOrderDetail($domain = '', $order_id, $vendor_id)
    {

        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;
        $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
        $vendor_order_status_option_ids = [];
        $vendor_order_status_created_dates = [];
        $order = Order::with(array(
            'vendors' => function ($query) use ($vendor_id, $order_id) {
                $query->join('vendors', 'vendors.id', '=', 'order_vendors.vendor_id')
                    ->where('vendors.id', $vendor_id)
                    ->where('order_vendors.order_id', $order_id);
            },
            'vendors.products.prescription' => function ($query) use ($vendor_id, $order_id) {
                $query->where('vendor_id', $vendor_id)->where('order_id', $order_id);
            },
            'vendors.orderDocument',
            'vendors.products' => function ($query) use ($vendor_id) {
                $query->where('vendor_id', $vendor_id);

                $query->with('order_product_status');
            },
            'vendors.products.translation' => function ($q) use ($langId) {
                $q->where('language_id', $langId);
            },
            'vendors.products.product',
            'vendors.products.addon',
            'vendors.products.addon.set',
            'vendors.products.addon.option',
            'vendors.products.addon.option.translation' => function ($q) use ($langId) {
                $q->select('addon_option_translations.id', 'addon_option_translations.addon_opt_id', 'addon_option_translations.title', 'addon_option_translations.language_id');
                $q->where('addon_option_translations.language_id', $langId);
                $q->groupBy('addon_option_translations.addon_opt_id', 'addon_option_translations.language_id');
            },
            'vendors.dineInTable.translations' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendors.dineInTable.category',
            'vendors.cancel_request',
            'reports',

        ));
        if (checkColumnExists('order_vendors', 'exchange_order_vendor_id')) {
            $order = $order->with(['vendors.exchanged_of_order.orderDetail', 'vendors.exchanged_to_order.orderDetail', 'order_exchange_request']);
        }
        $order = $order->findOrFail($order_id);
        //    return $order;
        // set payment option dynamic name
        if (@$order->paymentOption->code) {
            if ($order->paymentOption->code == 'stripe') {
                $order->paymentOption->title = __('Credit/Debit Card (Stripe)');
            } elseif ($order->paymentOption->code == 'kongapay') {
                $order->paymentOption->title = 'Pay Now';
            } elseif ($order->paymentOption->code == 'mvodafone') {
                $order->paymentOption->title = 'Vodafone M-PAiSA';
            } elseif ($order->paymentOption->code == 'mobbex') {
                $order->paymentOption->title = __('Mobbex');
            } elseif ($order->paymentOption->code == 'offline_manual') {
                $json = json_decode($order->paymentOption->credentials);
                $order->paymentOption->title = $json->manule_payment_title;
            }
            $order->paymentOption->title = __($order->paymentOption->title);
        }
        $product_schedule_type = '';


        if (!empty($order->total_other_taxes)) {
            $order->total_other_taxes_amount  =   (float) array_sum(explode(":", $order->total_other_taxes));
        } else {
            $order->total_other_taxes_amount = $order->taxable_amount;
        }

        $tax_amount  = round($order->total_other_taxes_amount, 2);

        foreach ($order->vendors as $key => $vendor) {

            if (isset($vendor) && !empty($vendor->vendor_id) && @$vendor->exchanged_to_order) {
                $vendor->exchanged_to_order->vendor_detail_url = route('order.show.detail', [$vendor->exchanged_to_order->order_id, @$vendor->exchanged_to_order->vendor_id]);
            }

            if (isset($vendor) && !empty($vendor->vendor_id && @$vendor->exchanged_of_order)) {
                $vendor->exchanged_of_order->vendor_detail_url = route('order.show.detail', [$vendor->exchanged_of_order->order_id, @$vendor->exchanged_of_order->vendor_id]);
            }

            // if(isset($vendor->recurring_booking_time) && !empty($vendor->recurring_booking_time)){

            //     $vendor->recurring_bookings =  OrderLongTermServiceSchedule::where(['order_vendor_product_id'=>$vendor->id,'type'=>2])->get();
            // }


            foreach ($vendor->products as $key => $product) {

                $product->longTermSchedule = array();
                if (@$product->product->is_long_term_service && $product->product->is_long_term_service == 1) {
                    $product->longTermSchedule =  OrderLongTermServices::with(['schedule', 'product.primary', 'addon.set', 'addon.option', 'addon.option.translation' => function ($q) use ($langId) {
                        $q->select('addon_option_translations.id', 'addon_option_translations.addon_opt_id', 'addon_option_translations.title', 'addon_option_translations.language_id');
                        $q->where('addon_option_translations.language_id', $langId);
                        $q->groupBy('addon_option_translations.addon_opt_id', 'addon_option_translations.language_id');
                    }])->where('order_product_id', $product->id)->first();
                    foreach ($product->longTermSchedule->addon as $ck => $addons) {
                        $opt_price_in_currency = $addons->option->price ?? 0;
                        $opt_price_in_doller_compare = $addons->option->price ?? 0;
                        if ($clientCurrency) {
                            $opt_price_in_currency = $addons->option->price ?? 0 / $divider;
                            $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                        }
                        $opt_quantity_price = decimal_format($opt_price_in_doller_compare * $product->quantity);
                        $addons->option->translation_title = ($addons->option->translation->isNotEmpty()) ? $addons->option->translation->first()->title : '';
                        $addons->option->price_in_cart = $addons->option->price;
                        $addons->option->price = decimal_format($opt_price_in_currency);
                        $addons->option->multiplier = ($clientCurrency) ? $clientCurrency->doller_compare : 1;
                    }
                }


                // Product Recurring Bookings
                $product_id             = @$product->product->id;
                $recurring_product      = Product::find($product_id);
                if (isset($recurring_product->is_recurring_booking) && $recurring_product->is_recurring_booking == 1) {
                    $product->recurring_bookings =  OrderLongTermServiceSchedule::where(['order_number' => $order->order_number, 'type' => 2])->get();
                }



                //pr($product->longTermSchedule->toArray());
                // check vendor product for schedule
                if ($product->schedule_type == 'schedule') {
                    $product_schedule_type = 'schedule';
                }
                $product->product_title = isset($product->translation) ? $product->translation->title : $product->product_name;
                $product->image_path  = $product->media->first() && !is_null($product->media->first()->image)  ? $product->media->first()->image->path : '';
                $divider = (empty($product->doller_compare) || $product->doller_compare < 0) ? 1 : $product->doller_compare;
                $total_amount = $product->quantity * $product->price;
                $product->routes = []; // routes for single product $product->Routes; //
                if (in_array($order->luxury_option_id, [6, 8, 4])) { // for on demand service and appointment service code by harbans :)
                    $product->routes =  $product->Routes; // OrderProductDispatchRoute::with('DispatchStatus')->where(['order_vendor_product_id'=>$product->id])->get()->toArray();
                }
                foreach ($product->addon as $ck => $addons) {
                    $opt_price_in_currency = $addons->option->price ?? 0;
                    $opt_price_in_doller_compare = $addons->option->price ?? 0;
                    if ($clientCurrency) {
                        $opt_price_in_currency = $addons->option->price ?? 0 / $divider;
                        $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                    }
                    $opt_quantity_price = decimal_format($opt_price_in_doller_compare * $product->quantity);
                    $addons->option->translation_title = ($addons->option->translation->isNotEmpty()) ? $addons->option->translation->first()->title : '';
                    $addons->option->price_in_cart = $addons->option->price;
                    $addons->option->price = decimal_format($opt_price_in_currency);
                    $addons->option->multiplier = ($clientCurrency) ? $clientCurrency->doller_compare : 1;
                    $addons->option->quantity_price = $opt_quantity_price;
                    $total_amount = $total_amount + $opt_quantity_price;
                }
                $product->total_amount = $total_amount;
            }
            if ($vendor->dineInTable) {
                $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                $vendor->dineInTableCategory = $vendor->dineInTable->category->title; //$vendor->dineInTable->category->first() ? $vendor->dineInTable->category->first()->title : '';
            }
        }
        // pr($order->vendors->toArray());
        $order->product_schedule_type = $product_schedule_type;
        $luxury_option_name = '';
        if ($order->luxury_option_id > 0) {
            $luxury_option = LuxuryOption::where('id', $order->luxury_option_id)->first();
            if ($luxury_option->title == 'takeaway') {
                $luxury_option_name = $this->getNomenclatureName('Takeaway', $langId, false);
            } elseif ($luxury_option->title == 'dine_in') {
                $luxury_option_name = $this->getNomenclatureName('Dine-In', $langId, false);
            } elseif ($luxury_option->title == 'on_demand') {
                $luxury_option_name = $this->getNomenclatureName('Services', $langId, false);
            } else {
                $luxury_option_name = $this->getNomenclatureName($luxury_option->title, $langId, false);
            }
        }
        $fixedFee = $this->fixedFee($langId);
        $order->luxury_option_name = $luxury_option_name;
        $order_status_options = OrderStatusOption::where('type', 1)->get();
        $dispatcher_status_options = DispatcherStatusOption::with(['vendorOrderDispatcherStatus' => function ($q) use ($order_id, $vendor_id) {
            $q->where(['order_id' => $order_id, 'vendor_id' => $vendor_id]);
        }])->get();

        $vendor_order_statuses = VendorOrderStatus::where('order_id', $order_id)->where('vendor_id', $vendor_id)->get();
        $prod = $order->vendors->first()->products->first();
        $processorProduct = [];
        if (checkTableExists('processor_products') && isset($prod)) {
            $processorProduct = ProcessorProduct::where(['product_id' => $prod->product_id])->first();
        }
        foreach ($vendor_order_statuses as $vendor_order_status) {
            $vendor_order_status_created_dates[$vendor_order_status->order_status_option_id] = $vendor_order_status->created_at;
            $vendor_order_status_option_ids[] = $vendor_order_status->order_status_option_id;
        }

        $user_docs = UserDocs::where('user_id', $order->user_id)->get();
        $user_registration_documents = UserRegistrationDocuments::get();
        $vendor_data = Vendor::where('id', $vendor_id)->first();

        $driver_data = '';
        if ($order->vendors[0]->shipping_delivery_type == 'L') {
            $lala = new LalaMovesController();
            $driver_data = $lala->getDeriverDetails($order->vendors[0]);
        }
        $category_KYC_document =  CaregoryKycDoc::where('ordre_id', $order->id)->with('category_document.primary')->groupBy('category_kyc_document_id')->get();

        $nomenclature = Nomenclature::where('label', 'Product Order Form')->first();
        $nomenclatureProductOrderForm = "Product Order Form";
        if (!empty($nomenclature)) {
            $nomenclatureTranslation = NomenclatureTranslation::where(['nomenclature_id' => $nomenclature->id, 'language_id' => $langId])->first();
            if ($nomenclatureTranslation) {
                $nomenclatureProductOrderForm = $nomenclatureTranslation->name ?? null;
            }
        }


        $recurring_booking = '';
        if (!empty($order->recurring_booking_time)) {
            $recurring_booking = OrderLongTermServiceSchedule::where(['order_number' => $order->order_number])->get();
        }
        if ($order->lawyer_id) {
            $lawyer =  User::where('id', $order->lawyer_id)->first();
        }
        
        $clientData = ClientData::first();
        return view('backend.order.view')->with([
            'vendor_id' => $vendor_id,
            'order' => $order,
            'lawyer' => @$lawyer,
            'processorProduct' => $processorProduct,
            'vendor_order_statuses' => $vendor_order_statuses,
            'vendor_order_status_option_ids' => $vendor_order_status_option_ids,
            'order_status_options' => $order_status_options,
            'dispatcher_status_options' => $dispatcher_status_options,
            'vendor_order_status_created_dates' => $vendor_order_status_created_dates,
            'user_registration_documents' => $user_registration_documents,
            'clientCurrency' => $clientCurrency,
            'user_docs' => $user_docs,
            'vendor_data' => $vendor_data,
            'fixedFee' => $fixedFee,
            "category_KYC_document" => $category_KYC_document,
            'driver_data' => (($driver_data) ? json_decode($driver_data) : ''),
            'nomenclatureProductOrderForm' => $nomenclatureProductOrderForm,
            'recurring_booking' => $recurring_booking,
            'tax_amount' => $tax_amount ?? 0,
            'clientData' => $clientData
        ]);
    }

    //Update order product price by vendor incase order is takeaway
    //mohit sir branch code added by sohail
    public function updateOrderProductPriceByVendor(Request $request, $domain = '')
    {

        try {
            $roles = [
                'or_vend_prod_id'   => 'required',
                'or_prod_old_price' => 'required',
                'product_price'   => 'required'
            ];
            $validator = Validator::make($request->all(), $roles);
            if ($validator->fails()) {
                return response()->json(['status' => 'error', 'message' => __('Price & Reason both fields are required')]);
            }
            DB::beginTransaction();
            $orderProduct = OrderProduct::find($request->or_vend_prod_id);
            $orderProduct->price = decimal_format(isset($request->product_price) ? $request->product_price : 0);
            $orderProduct->old_price = isset($request->or_prod_old_price) ? ($request->or_prod_old_price) : 0;
            $orderProduct->updated_price_reason = isset($request->update_price_reason) ? ($request->update_price_reason) : 0;
            $orderProduct->save();
            $orderData = Order::find($orderProduct->order_id);
            $newPayableAmount = 0;
            $newTotalAmount   = 0;
            if ($orderProduct->old_price < $orderProduct->price) {
                $newPayableAmount =   ($orderProduct->price - $orderProduct->old_price) + $orderData->payable_amount;
                $newTotalAmount   = ($orderProduct->price - $orderProduct->old_price) + $orderData->total_amount;
            } else if ($orderProduct->old_price > $orderProduct->price) {
                $newPayableAmount =   $orderData->payable_amount - ($request->or_prod_old_price - $request->product_price);
                $newTotalAmount   = $orderData->total_amount - ($request->or_prod_old_price - $request->product_price);
            }

            if (!empty($newPayableAmount) && !empty($newTotalAmount)) {
                $orderData->total_amount   = decimal_format($newTotalAmount);
                $orderData->payable_amount = decimal_format($newPayableAmount);
                $orderData->save();
                DB::commit();
                return response()->json(['status' => 'error', 'message' => __('Product price updated Successfully.')]);
            } else {
                DB::rollback();
            }
            return response()->json(['status' => 'error', 'message' => __('Product price are same.')]);
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }
    //till here

    /**
     * Change the status of order
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function uploadLawyerDocuments(Request $request)
    {
        $request->validate([
            'documents.*' => 'required|file',
            'order_id' => 'required'
        ]);

        foreach ($request->file('documents') as $file) {
            $path = Storage::disk('s3')->put('lawyerDocument', $file, 'public');
            LawyerDocument::create([
                'lawyer_id' => Auth()->user()->id,
                'file_path' => $path,
                'order_id' => $request->order_id,
                'status' => 0
            ]);
        }
        return back();
    }
    public function changeStatus(Request $request, $domain = '')
    {
        try {
            if ($request->status_option_id == 11) {
                $request->status_option_id = 2;
            } elseif ($request->status_option_id == 12) {
                $request->status_option_id = 4;
            } elseif ($request->status_option_id == 13) {
                $request->status_option_id = 5;
            } elseif ($request->status_option_id == 14) {
                $request->status_option_id = 6;
            }
            
            OrderStatusTime::updateOrCreate([
                'order_id' => $request->order_id,
                'order_status' => $request->status_option_id,
            ],  [
                'created_at' => now() 
            ]);
            if ($request->status_option_id == 5 || $request->status_option_id == 13) {
                $lawyerDocument = LawyerDocument::where('order_id', $request->order_id)->where('status', '!=', 2)->first();
                if (!$lawyerDocument) {
                    return response()->json([
                        'status' => 'error',
                        'message' => __('Please upload lawyer documents first')
                    ]);
                }
            }
            $orderPlaced = true;
            $orderPlacedNo = '';
            $productIds = $request->productIds ?? [];
            $orderVendorProductIds = $request->order_vendor_product_id ?? [];
            DB::beginTransaction();
            $client_preferences = ClientPreference::first();

            $timezone = Auth::user()->timezone;
            $vendor_order_status_check = VendorOrderStatus::where('order_id', $request->order_id)->where('vendor_id', $request->vendor_id)->where('order_status_option_id', $request->status_option_id)->first();
            $currentOrderStatus = OrderVendor::where(['vendor_id' => $request->vendor_id, 'order_id' => $request->order_id])->first();
            if ($currentOrderStatus->order_status_option_id == 2 && $request->status_option_id == 2) { //$request->status_option_id == 3){
                return response()->json(['status' => 'error', 'message' => __('Order has already been accepted!!!')]);
            }
            if ($currentOrderStatus->order_status_option_id == 3) { //$request->status_option_id == 2){
                return response()->json(['status' => 'error', 'message' => __('Order has already been rejected!!!')]);
            }
            // handle if lawyer reject the order then re-assign the order
            if($request->status_option_id == 3 && Auth::user()->hasRole('Lawyer')){
              $order_vendor_current_status = OrderVendor::where('id',$request->order_vendor_id)->first()->order_status_option_id;
              if($order_vendor_current_status == 10){
                Order::where('id',$request->order_id)->update(['lawyer_id' => null]);
                OrderVendor::where('id',$request->order_vendor_id)->update(['order_status_option_id' => 9,'reject_reason' => $request->reject_reason,'cancelled_by' => $request->cancelled_by]);
                VendorOrderStatus::where('order_id',$request->order_id)->where('vendor_id',$request->vendor_id)->where('order_status_option_id',10)->delete();
                OrderStatusTime::where('order_id',$request->order_id)->where('order_status',10)->delete();
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => __('Order Rejected successfully!!')
                ]);
              }
            }
          
            if (!$vendor_order_status_check) {
                if ($request->status_option_id == 2 || $request->status_option_id == 3) {
                    $clientDetail = CP::on('mysql')->where(['code' => $client_preferences->client_code])->first();
                    AutoRejectOrderCron::on('mysql')->where(['database_name' => $clientDetail->database_name, 'order_vendor_id' => $currentOrderStatus->id])->delete();
                    $this->sendRejectNotificationToVendor("", $request->vendor_id, $request->order_id);
                }


                $orderData = OrderVendor::with(['vendor', 'products.vendor', 'products.product.vendor', 'products.vendorProducts.product.media.image', 'products.vendorProducts.product.translation_one', 'products.vendorProducts.pvariant', 'orderDetail'])->where('vendor_id', $request->vendor_id)->where('order_id', $request->order_id)->first();
                //    dd($orderData->products->toArray()[0]['vendor_products'][0]['product']);
                if (@$orderData->exchanged_of_order) {
                    $return = OrderReturnRequest::where('order_id', $orderData->exchanged_of_order->order_id)->first();
                    if (@$return && $request->status_option_id == 2) { //accept exchange

                        $returns = OrderReturnRequest::where('order_id', $orderData->exchanged_of_order->order_id)->update(['status' => 'Accepted', 'reason_by_vendor' => $request->reject_reason ?? null]);
                    }

                    if (@$return && $request->status_option_id == 3) { //reject exchange
                        $returns = OrderReturnRequest::where('order_id', $orderData->exchanged_of_order->order_id)->update(['status' => 'Rejected', 'reason_by_vendor' => $request->reject_reason ?? null]);
                    }
                }

                if ($request->order_luxury_option_id == 4) {
                    foreach ($orderVendorProductIds as $key => $id) {
                        $vendor_order_product_status = new VendorOrderProductStatus();
                        $vendor_order_product_status->order_id = $request->order_id;
                        $vendor_order_product_status->order_vendor_id = $request->order_vendor_id;
                        $vendor_order_product_status->vendor_id = $request->vendor_id;
                        $vendor_order_product_status->product_id = $productIds[$key];
                        $vendor_order_product_status->order_status_option_id = $request->status_option_id;
                        $vendor_order_product_status->order_vendor_product_id = $id;
                        $vendor_order_product_status->save();
                    }
                }

                if ($request->status_option_id == 2) {
                    //Check Order delivery type
                    if ($orderData->shipping_delivery_type == 'D') {
                        //Create Shipping request for dispatcher
                        if ($orderData->orderDetail->is_long_term == 1) {
                            $order_dispatch = $this->checkIfanyServiceProductLastMileon($request);
                        } else if ($orderData->orderDetail->recurring_booking_type == 1) {
                            $order_dispatch = $this->checkIfIsProductRecurringLastMileon($request);
                        } else {

                            $order_dispatch = $this->checkIfanyProductLastMileon($request);
                        }
                        if ($order_dispatch && $order_dispatch == 1) {

                            $stats = $this->insertInVendorOrderDispatchStatus($request);
                            $orderPlaced = true;
                        }
                    } elseif ($orderData->shipping_delivery_type == 'L') {
                        //Create Shipping place order request for Lalamove
                        //$orderPlaced = $this->placeOrderRequestlalamove($request);
                    } elseif ($orderData->shipping_delivery_type == 'B') {
                        $orderPlaced = $this->placeOrderRequestBorzoeApi($request);
                    } elseif ($orderData->shipping_delivery_type == 'K') {
                        //Create Shipping place order request for Kwik
                        $orderPlaced = $this->placeOrderRequestKwikApi($request);
                    } elseif ($orderData->shipping_delivery_type == 'SR') {
                        //Create Shipping place order request for Shiprocket
                        $orderPlaced = $this->placeOrderRequestShiprocket($request);
                    } elseif ($orderData->shipping_delivery_type == 'DU') {
                        //Create Shipping place order request for Dunzo
                        $orderPlaced = $this->placeOrderRequestDunzo($request);
                    } elseif ($orderData->shipping_delivery_type == 'M') {
                        //Create Shipping place order request for Ahoy Masa
                        $orderPlaced = $this->placeOrderRequestAhoy($request);
                    } elseif ($orderData->shipping_delivery_type == 'SH') {
                        //Create Shipping place order request for Shippo Masa
                        $orderPlaced = $this->placeOrderRequestShippo($request);
                    } elseif ($orderData->shipping_delivery_type == 'SE') {
                        $orderPlaced = $this->placeOrderRequestShipEngine($request, $orderData);
                    } elseif ($orderData->shipping_delivery_type == 'RO') {
                        //Create Roadies place order request for Roadies
                        if ($orderData && ($orderData->delivery_fee > 0.00)) {
                            $orderPlaced = $this->placeOrderRequestRoadies($request, $orderData);
                        }
                    } elseif ($orderData->shipping_delivery_type == 'D4') {
                        if ($orderData && ($orderData->delivery_fee > 0.00)) {
                            $orderPlaced = $this->placeOrderRequestD4B($request);
                        }
                    }
                    $orderData->accepted_by = Auth::user()->id;
                    $orderData->save();
                }

                if ($request->status_option_id == 2 && taxJarEnable()) {
                    $this->createTaxJarOrder($orderData);
                }

                if ($request->status_option_id == 4  && $orderData->shipping_delivery_type == 'L') {
                    //Create Shipping place order request for Lalamove when order in processing state
                    $orderPlaced = $this->placeOrderRequestlalamove($request);
                    $orderPlacedNo = $orderPlaced;
                }


                if ($orderPlaced) {

                    $vendorOrderStatus = VendorOrderStatus::where('vendor_id', $request->vendor_id)->where('order_id', $request->order_id)->first();
                    if ($request->status_option_id != 9 || $request->status_option_id != 10) {
                        $vendor_order_status = new VendorOrderStatus();
                        $vendor_order_status->order_id = $request->order_id;
                        $vendor_order_status->vendor_id = $request->vendor_id;
                        // This below line is commented because the data in $request->order_vendor_id incorrect.
                        $vendor_order_status->order_vendor_id = $request->order_vendor_id;
                        //$vendor_order_status->order_vendor_id = $vendorOrderStatus->order_vendor_id;
                        $vendor_order_status->order_status_option_id = $request->status_option_id;
                        $vendor_order_status->save();
                    }
                    if ($request->status_option_id == 3) {
                        if ($orderData->shipping_delivery_type == 'D' && !empty($currentOrderStatus->dispatch_traking_url)) {
                            $dispatch_traking_url = str_replace('/order/', '/order-cancel/', $currentOrderStatus->dispatch_traking_url);
                            $response = Http::get($dispatch_traking_url);
                        } elseif ($orderData->shipping_delivery_type == 'L') {
                            //Cancel Shipping place order request for Lalamove
                            $lala = new LalaMovesController();
                            $order_lalamove = $lala->cancelOrderRequestlalamove($currentOrderStatus->web_hook_code);
                        } elseif ($orderData->shipping_delivery_type == 'B') {
                            //Cancel Shipping place order request for Borzoe
                            // $borzoe = new BorzoeDeliveryController();
                            $order_lalamove = $this->cancleOrderToBorzoApi($request->vendor_id, $request->order_id);
                        } elseif ($orderData->shipping_delivery_type == 'K') {
                            //Cancel Shipping place order request for KwikApi
                            $lala = new QuickApiController();
                            $order_lalamove = $lala->cancelOrderRequestKwikApi($request->order_id, $request->vendor_id);
                        } elseif ($orderData->shipping_delivery_type == 'SR') {
                            //Cancel Shipping place order request for Shiprocket
                            $ship = new ShiprocketController();
                            $order_ship = $ship->cancelOrderRequestShiprocket($currentOrderStatus->ship_order_id);
                        } elseif ($orderData->shipping_delivery_type == 'DU') {
                            //Cancel Dunzo place order request for Dunzo
                            $ship = new DunzoController();
                            $order_ship = $ship->cancelOrderRequestDunzo($currentOrderStatus->web_hook_code);
                        } elseif ($orderData->shipping_delivery_type == 'M') {
                            //Create Shipping place order request for Ahoy
                            $ship = new AhoyController();
                            $order_ship = $ship->cancelOrderRequestAhoy($currentOrderStatus->web_hook_code);
                        } elseif ($orderData->shipping_delivery_type == 'D4') {
                            //Cancel Dunzo place order request for Dunzo
                            $ship = new D4BDunzoController();
                            $order_ship = $ship->cancelOrderRequestD4BDunzo($currentOrderStatus->web_hook_code, $currentOrderStatus->reject_reason);
                        }

                        // return amount to user wallet worked by harbans
                        $vendor_id = $request->vendor_id;
                        $order = Order::with(array(
                            'vendors' => function ($query) use ($vendor_id) {
                                $query->where('vendor_id', $vendor_id);
                            }
                        ))->find($request->order_id);

                        // get vendor return amount from order
                        $return_response =  $this->GetVendorReturnAmount($request, $order);
                        // return amount to user wallet
                        if ($return_response['vendor_return_amount'] > 0) {
                            $user = User::find($currentOrderStatus->user_id);
                            $wallet = $user->wallet;
                            $credit_amount = $return_response['vendor_return_amount']; //$currentOrderStatus->payable_amount;
                            $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> for return #' . $currentOrderStatus->orderDetail->order_number . ' (' . $currentOrderStatus->vendor->name . ')']);
                            $this->sendWalletNotification($user->id, $currentOrderStatus->orderDetail->order_number);
                        }

                        // diarise loyalty in order table
                        $order->loyalty_points_used    =  $order->loyalty_points_used - $return_response['vendor_loyalty_points'];
                        $order->loyalty_amount_saved   =  $order->loyalty_amount_saved - $return_response['vendor_loyalty_amount'];
                        $order->loyalty_points_earned  =  $order->loyalty_points_earned - $return_response['vendor_loyalty_points_earned'];
                        $order->save();
                        // save payment in table
                        $vendor_return_payment                          = new VendorOrderCancelReturnPayment();
                        $vendor_return_payment->order_id                = $order->id;
                        $vendor_return_payment->order_vendor_id         = $currentOrderStatus->id;
                        $vendor_return_payment->wallet_amount           = $return_response['vendor_wallet_amount'];
                        $vendor_return_payment->online_payment_amount   = $return_response['vendor_online_payment_amount'];
                        $vendor_return_payment->loyalty_amount          = $return_response['vendor_loyalty_amount'];
                        $vendor_return_payment->loyalty_points          = $return_response['vendor_loyalty_points'];
                        $vendor_return_payment->loyalty_points_earned   = $return_response['vendor_loyalty_points_earned'];
                        $vendor_return_payment->total_return_amount     = $return_response['vendor_return_amount'];
                        $vendor_return_payment->save();
                        // end amount to user wallet worked by harbans
                    }
                }

                if ($request->status_option_id == 2) {
                    $this->ProductVariantStock($request->order_id, $request);

                    $hub_key = VendorMargConfig::where('vendor_id', $orderData->vendor_id ?? 0)->first();
                    if (isset($hub_key) && $hub_key->is_marg_enable == 1) {
                        $this->makeInsertOrderMargApi($orderData->orderDetail);
                    }
                }

                if ($currentOrderStatus->order_status_option_id == 2 && $request->status_option_id == 3) {
                    $this->ProductVariantStockIncreaseByOrderId($request->order_id);
                }

                if ($request->status_option_id == 3 && $request->order_luxury_option_id == 4) {
                    ProductBooking::where('order_id', $request->order_id)->update(['on_rent' => 0]);
                }

                $order_vendor = OrderVendor::where('vendor_id', $request->vendor_id)->where('order_id', $request->order_id)->first();
                $order_vendor->order_status_option_id = $request->status_option_id;
                $order_vendor->reject_reason = $request->reject_reason;
                $order_vendor->cancelled_by = $request->cancelled_by;

                $order_vendor->save();
                OrderProduct::where('vendor_id', $request->vendor_id)->where('order_id', $request->order_id)->update(['order_status_option_id' => $request->status_option_id]);
                DB::commit();
                $newOrder = Order::select('id', 'user_id')->with(
                    'ordervendor'
                )
                    ->where('id', $request->order_id)
                    ->first();

                $blockchain_route = ClientPreferenceAdditional::where('key_name', 'blockchain_route_formation')->first();

                if (isset($blockchain_route) && ($blockchain_route->key_value == 1)) {
                    @$this->updateBlockchainOrderDetail($newOrder);
                }
                // $this->sendSuccessNotification(Auth::user()->id, $request->vendor_id);

                $this->sendStatusChangePushNotificationCustomer([$currentOrderStatus->user_id], $orderData, $request->status_option_id);

                $customer = User::find($orderData->user_id);
                if (getAdditionalPreference(['is_tracking_url'])['is_tracking_url'] == 1) {
                    $this->sendTrackingUrlSMS($orderData, $request->order_id);
                }


                return response()->json([
                    'status' => 'success',
                    'created_date' => convertDateTimeInTimeZone($vendor_order_status->created_at, $timezone, 'l, F d, Y, H:i A'),
                    'message' => __('Order Status Updated Successfully.' . (($orderPlacedNo) ? ' Order No : ' . $orderPlacedNo : ''))
                ]);
            }
            DB::commit();
            $currentOrderStatus->order_status_option_id = $vendor_order_status_check->order_status_option_id;
            $currentOrderStatus->save();
            return response()->json([
                'status' => 'success',
                'message' => __('Order has updated successfully!!')
            ]);
        } catch (\Exception $e) {
            DB::rollback();

            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    public function changeVendorProductStatus(Request $request, $domain = '')
    {
        try {
            $timezone = Auth::user()->timezone;
            $vendor_order_product_status_check = VendorOrderProductStatus::where('order_id', $request->order_id)->where('vendor_id', $request->vendor_id)->where('order_vendor_product_id', $request->order_vendor_product_id)->where('order_status_option_id', $request->status_option_id)->first();

            if (@$vendor_order_product_status_check->order_status_option_id == 3) { //$request->status_option_id == 2){
                return response()->json(['status' => 'error', 'message' => __('Order has already been rejected!!!')]);
            }

            $create_vendor_order_product_status = VendorOrderProductStatus::create(array(
                'order_id' => $request->order_id,
                'order_vendor_id'  => $request->order_vendor_id,
                'vendor_id' => $request->vendor_id,
                'product_id' => $request->order_product_id,
                'order_status_option_id' => $request->status_option_id,
                'order_vendor_product_id' => $request->order_vendor_product_id
            ));

            if ($create_vendor_order_product_status) {
                return response()->json([
                    'status' => 'success',
                    'message' => __('Order Vendor Product Status Updated Successfully.')
                ]);
            }
        } catch (\Exception $e) {
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }

    /// ******************   insert In Vendor Order Dispatch Status   ************************ ///////////////
    public function insertInVendorOrderDispatchStatus($request)
    {
        $update = VendorOrderDispatcherStatus::updateOrCreate([
            'dispatcher_id' => null,
            'order_id' =>  $request->order_id,
            'dispatcher_status_option_id' => 1,
            'vendor_id' =>  $request->vendor_id
        ]);
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

    //     $notification_content = NotificationTemplate::where('id', 2)->first();

    //     if ($notification_content && !empty($token) && !empty($client_preferences->fcm_server_key)) {
    //         $data = [
    //             "registration_ids" => $token,
    //             "notification" => [
    //                 'title' => $notification_content->label,
    //                 'body'  => $notification_content->content,
    //             ]
    //         ];
    //         sendFcmCurlRequest($data);
    //     }
    // }
    /// ******************  check If any Product Last Mile on   ************************ ///////////////
    public function placeOrderRequestShippo($request)
    {
        $ship = new ShippoController();
        $is_place_order_delivery_zero = getAdditionalPreference(['is_place_order_delivery_zero'])['is_place_order_delivery_zero'];
        //Create Shipping place order request for Shiprocket
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);

        if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
            $order_ship = $ship->createOrderRequestShippo($checkdeliveryFeeAdded);
            ////\Log::info($order_ship);
        }
        if ($order_ship->object_id) {
            $up_web_hook_code = OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])->update([
                'ship_order_id' => $order_ship->object_id,
                'ship_shipment_id' => $order_ship->rate,
                'ship_awb_id' => $order_ship->parcel
            ]);
            return 1;
        }

        return 2;
    }

    public function placeOrderRequestRoadies($request, $orderData)
    {
        $roadie = new RoadieController();
        $checkOrderData = Order::with(['vendors.products.product', 'user', 'address'])->findOrFail($request->order_id);
        if (@$checkOrderData) {
            $order_ship_roadie = $roadie->createShipmentRequestRoadie($orderData, $checkOrderData);
            if ($order_ship_roadie) {
                $roadie_tracking_url = "https://www.roadie.com/tracking?tracking_number=" . $order_ship_roadie->tracking_number;
                $up_web_hook_code = OrderVendor::where(['order_id' => $checkOrderData->id, 'vendor_id' => $request->vendor_id])
                    ->update([
                        'roadie_tracking_url' => $roadie_tracking_url
                    ]);
                return 1;
            }
        }
        return false;
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

    public function placeOrderRequestShipEngine($request)
    {
        $shipEngine = new ShipEngineController();
        $checkOrderData = Order::with(['vendors.products.product', 'user', 'ordervendor.vendor'])->findOrFail($request->order_id);
        if (@$checkOrderData) {
            $createLabeleData = $shipEngine->placeOrderRequest($checkOrderData);
            if (isset($createLabeleData['label_id'])) {
                $tracking_url = $shipEngine->trackingUrl($createLabeleData['label_id']);
                OrderVendor::where(['order_id' => $checkOrderData->id, 'vendor_id' => $request->vendor_id])
                    ->update([
                        'dispatch_traking_url' => $tracking_url,
                        'courier_id' => $createLabeleData['carrier_code'],
                        'ship_shipment_id' => $createLabeleData['shipment_id'],
                        'label_id' => $createLabeleData['label_id'],
                        'label_pdf' => $createLabeleData['label_download']['pdf']
                    ]);
                return 1;
            }
        }
        return false;
    }

    public function placeOrderRequestBorzoeApi($request)
    {
        $borzoe = new BorzoeDeliveryController();
        //Create Shipping place order request for KwikApi
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);
        if ($checkdeliveryFeeAdded && $checkdeliveryFeeAdded->delivery_fee > 0.00) {
            $order_ship = $this->placeOrderToBorzoApi($checkdeliveryFeeAdded, $request->vendor_id, $request->order_id);
        }
        $orderDetails = json_decode($order_ship);
        if ($order_ship) {
            $up_web_hook_code = OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])
                ->update([
                    'borzoe_order_id' => $orderDetails->order->order_id,
                    'borzoe_order_name' => $orderDetails->order->order_name,
                    'dispatch_traking_url' => $orderDetails->order->points[1]->tracking_url,
                ]);
            return 1;
        }
        return false;
    }

    public function placeOrderRequestKwikApi($request)
    {
        $kwik = new QuickApiController();
        //Create Shipping place order request for KwikApi
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);
        if ($checkdeliveryFeeAdded && $checkdeliveryFeeAdded->delivery_fee > 0.00) {
            $order_ship = $kwik->placeOrderToKwikApi($request->vendor_id, $request->order_id);
        }
        if ($order_ship) {
            $up_web_hook_code = OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])
                ->update([
                    'delivery_response' => json_encode($order_ship),
                    'dispatch_traking_url' => $order_ship->pickups[0]->result_tracking_link,
                    'web_hook_code' => $order_ship->unique_order_id
                ]);
            return 1;
        }

        return false;
    }

    public function placeOrderRequestShiprocket($request)
    {
        $ship = new ShiprocketController();
        $is_place_order_delivery_zero = getAdditionalPreference(['is_place_order_delivery_zero'])['is_place_order_delivery_zero'];
        //Create Shipping place order request for Shiprocket
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);

        if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
            $order_ship = $ship->createOrderRequestShiprocket($checkOrder->user_id, $checkdeliveryFeeAdded);
        }
        if ($order_ship->order_id) {
            $up_web_hook_code = OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])
                ->update([
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
        $is_place_order_delivery_zero = getAdditionalPreference(['is_place_order_delivery_zero'])['is_place_order_delivery_zero'];
        //Create Ahoy place order request for Ahoy
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);

        if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
            $order_det = $data->createPreOrderRequestAhoy($checkOrder->user_id, $checkdeliveryFeeAdded);
        }

        if (isset($order_det->orderId)) {
            $up_web_hook_code = OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])
                ->update([
                    'web_hook_code' => $order_det->orderId
                ]);

            return 1;
        }

        return 2;
    }

    public function placeOrderRequestDunzo($request)
    {

        $data = new DunzoController();
        $is_place_order_delivery_zero = getAdditionalPreference(['is_place_order_delivery_zero'])['is_place_order_delivery_zero'];
        //Create Shipping place order request for Dunzo
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);

        if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
            $order_lalamove = $data->createOrderRequestDunzo($checkOrder->user_id, $checkdeliveryFeeAdded);
        }

        if ($order_lalamove->status) {
            $up_web_hook_code = OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])
                ->update([
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
        $is_place_order_delivery_zero = getAdditionalPreference(['is_place_order_delivery_zero'])['is_place_order_delivery_zero'];
        //Create Shipping place order request for Lalamove
        $checkdeliveryFeeAdded = OrderVendor::where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $checkOrder = Order::findOrFail($request->order_id);

        if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
            $order_lalamove = $lala->placeOrderToLalamoveDev($request->vendor_id, $checkOrder->user_id, $checkOrder->id);
        }
        if (isset($order_lalamove->orderRef)) {
            OrderVendor::where(['order_id' => $checkOrder->id, 'vendor_id' => $request->vendor_id])
                ->update(['web_hook_code' => $order_lalamove->orderRef]);
            return $order_lalamove->orderRef;
        }
        return false;
    }


    public function checkIfanyProductLastMileon($request)
    {
        $order_dispatchs = 2;
        $AdditionalPreference  =  getAdditionalPreference(['is_place_order_delivery_zero']);
        $checkdeliveryFeeAdded = OrderVendor::with('LuxuryOption', 'products')->where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $luxury_option_id = $checkdeliveryFeeAdded->LuxuryOption ? $checkdeliveryFeeAdded->LuxuryOption->luxury_option_id : 1;
        $is_place_order_delivery_zero = $AdditionalPreference['is_place_order_delivery_zero'];
        $is_restricted = $checkdeliveryFeeAdded->is_restricted;

        /// luxury option 8 ( static ) for appointment you can check it on luxuryOptionSeeder
        if ($luxury_option_id == 8) { // only for appointment type
            $dispatch_domain_Appointment = $this->checkIfAppointmentOnCommon();
            if ($dispatch_domain_Appointment && $dispatch_domain_Appointment != false) {
                $Appointment = 0;
                foreach ($checkdeliveryFeeAdded->products as $key => $prod) {


                    if (isset($prod->product_dispatcher_tag) && !empty($prod->product_dispatcher_tag) && $prod->product->category->categoryDetail->type_id == 12) {
                        $dispatch_domain_Appointment = $this->checkIfAppointmentOnCommon();
                        //echo $Appointment . 'app';
                        //echo $checkdeliveryFeeAdded->delivery_fee . '$checkdeliveryFeeAdded->delivery_fee';

                        if ($dispatch_domain_Appointment && $dispatch_domain_Appointment != false && $Appointment == 0  && $checkdeliveryFeeAdded->delivery_fee <= 0) {

                            $dispatch_domain = [
                                'service_key'      => $dispatch_domain_Appointment->appointment_service_key,
                                'service_key_code' => $dispatch_domain_Appointment->appointment_service_key_code,
                                'service_key_url'  => $dispatch_domain_Appointment->appointment_service_key_url,
                                'service_type'     => 'appointment'
                            ];
                            //pr($checkdeliveryFeeAdded);
                            $order_dispatchs = $this->placeRequestToDispatchSingleProduct($request->order_id, $request->vendor_id, $dispatch_domain, $request);
                            if ($order_dispatchs && $order_dispatchs == 1) {
                                $Appointment = 1;
                                return 1;
                            }
                        }
                    } else {
                    }

                    //pr('ad');
                }
            }
        }

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

        if ($luxury_option_id == 4) { // only for rental type
            $dispatch_domain = $this->getDispatchDomain();
            if ($dispatch_domain && $dispatch_domain != false) {
                foreach ($checkdeliveryFeeAdded->products as $key => $prod) {
                    if ($prod->product->category->categoryDetail->type_id == 10) {
                        $dispatch_domain = [
                            'service_key'      => $dispatch_domain->delivery_service_key,
                            'service_key_code' => $dispatch_domain->delivery_service_key_code,
                            'service_key_url'  => $dispatch_domain->delivery_service_key_url,
                            'service_type'     => 'rental'
                        ];

                        $order_dispatchs = $this->placeRequestToDispatchSingleProduct($request->order_id, $request->vendor_id, $dispatch_domain, $request);
                        if ($order_dispatchs && $order_dispatchs == 1) {
                            // $OnDemand = 1;
                            return 1;
                        }
                    }
                }
            }
        }

        $dispatch_domain = $this->getDispatchDomain();
        if ($dispatch_domain && $dispatch_domain != false) {

            if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {

                $order_dispatchs = $this->placeRequestToDispatch($request->order_id, $request->vendor_id, $dispatch_domain);
            }


            if ($order_dispatchs && $order_dispatchs == 1)
                return 1;
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
        // //\Log::info('getDispatchLaundryDomain');
        /////////////// **************** for laundry accept order *************** ////////////////
        $dispatch_domain_laundry = $this->getDispatchLaundryDomain();

        if ($dispatch_domain_laundry && $dispatch_domain_laundry != false) {
            $laundry = 0;

            foreach ($checkdeliveryFeeAdded->products as $key => $prod) {
                $isNotLongTerm = 1;

                if ($prod->product->is_long_term_service == 1) {
                    $isNotLongTerm = 0;
                }



                if (($isNotLongTerm == 1) && $prod->product->category->categoryDetail->type_id == 9) {    ///////// if product from laundry

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

    public function checkIfIsProductRecurringLastMileon($request)
    {

        $order_dispatchs        = 2;
        $checkdeliveryFeeAdded  = OrderVendor::with('LuxuryOption')->where(['order_id' => $request->order_id, 'vendor_id' => $request->vendor_id])->first();
        $luxury_option_id       = $checkdeliveryFeeAdded->LuxuryOption ? $checkdeliveryFeeAdded->LuxuryOption->luxury_option_id : 1;
        $is_place_order_delivery_zero = getAdditionalPreference(['is_place_order_delivery_zero'])['is_place_order_delivery_zero'];
        if ($luxury_option_id == 8) { // only for appointment type
            $dispatch_domain_Appointment = $this->checkIfAppointmentOnCommon();
            if ($dispatch_domain_Appointment && $dispatch_domain_Appointment != false) {
                $Appointment = 0;
                foreach ($checkdeliveryFeeAdded->products as $key => $prod) {


                    if (isset($prod->product_dispatcher_tag) && !empty($prod->product_dispatcher_tag) && $prod->product->category->categoryDetail->type_id == 12) {
                        $dispatch_domain_Appointment = $this->checkIfAppointmentOnCommon();
                        //echo $Appointment . 'app';
                        //echo $checkdeliveryFeeAdded->delivery_fee . '$checkdeliveryFeeAdded->delivery_fee';

                        if ($dispatch_domain_Appointment && $dispatch_domain_Appointment != false && $Appointment == 0  && $checkdeliveryFeeAdded->delivery_fee <= 0) {

                            $dispatch_domain = [
                                'service_key'      => $dispatch_domain_Appointment->appointment_service_key,
                                'service_key_code' => $dispatch_domain_Appointment->appointment_service_key_code,
                                'service_key_url'  => $dispatch_domain_Appointment->appointment_service_key_url,
                                'service_type'     => 'appointment'
                            ];
                            //pr($checkdeliveryFeeAdded);
                            $order_dispatchs = $this->placeRequestToDispatchSingleProduct($request->order_id, $request->vendor_id, $dispatch_domain, $request);
                            if ($order_dispatchs && $order_dispatchs == 1) {
                                $Appointment = 1;
                                return 1;
                            }
                        }
                    } else {
                    }

                    //pr('ad');
                }
            }
        }
        if ($luxury_option_id == 6) { // only for on_demand type
            $dispatch_domain_OnDemand = $this->getDispatchOnDemandDomain();

            if ($dispatch_domain_OnDemand && $dispatch_domain_OnDemand != false) {
                $OnDemand = 0;
                foreach ($checkdeliveryFeeAdded->products as $key => $prod) {


                    if (isset($prod->product_dispatcher_tag) && !empty($prod->product_dispatcher_tag) && $prod->product->category->categoryDetail->type_id == 8) {

                        //  $dispatch_domain_OnDemand = $this->getDispatchOnDemandDomain();
                        //echo $Appointment . 'app';

                        if ($dispatch_domain_OnDemand && $dispatch_domain_OnDemand != false && $OnDemand == 0  && $checkdeliveryFeeAdded->delivery_fee > 0) {



                            $dispatch_domain = [
                                'service_key'      => $dispatch_domain_OnDemand->dispacher_home_other_service_key,
                                'service_key_code' => $dispatch_domain_OnDemand->dispacher_home_other_service_key_code,
                                'service_key_url'  => $dispatch_domain_OnDemand->dispacher_home_other_service_key_url,
                                'service_type'     => 'on_demand'
                            ];


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

        if ($luxury_option_id == 4) { // only for rental type
            $dispatch_domain = $this->getDispatchDomain();
            if ($dispatch_domain && $dispatch_domain != false) {
                foreach ($checkdeliveryFeeAdded->products as $key => $prod) {
                    if ($prod->product->category->categoryDetail->type_id == 10) {
                        $dispatch_domain = [
                            'service_key'      => $dispatch_domain->delivery_service_key,
                            'service_key_code' => $dispatch_domain->delivery_service_key_code,
                            'service_key_url'  => $dispatch_domain->delivery_service_key_url,
                            'service_type'     => 'rental'
                        ];

                        $order_dispatchs = $this->placeRequestToDispatchSingleProduct($request->order_id, $request->vendor_id, $dispatch_domain, $request);
                        if ($order_dispatchs && $order_dispatchs == 1) {
                            // $OnDemand = 1;
                            return 1;
                        }
                    }
                }
            }
        }

        $dispatch_domain = $this->getDispatchDomain();

        if ($dispatch_domain && $dispatch_domain != false) {
            // dd($checkdeliveryFeeAdded->delivery_fee);
            if ($checkdeliveryFeeAdded && ($checkdeliveryFeeAdded->delivery_fee > 0.00 || $is_place_order_delivery_zero == 1)) {
                $order_dispatchs = $this->placeRequestToDispatch($request->order_id, $request->vendor_id, $dispatch_domain);
            }

            if ($order_dispatchs && $order_dispatchs == 1)
                return 1;
        }

        /////////////// **************** for laundry accept order *************** ////////////////
        $dispatch_domain_laundry = $this->getDispatchLaundryDomain();

        if ($dispatch_domain_laundry && $dispatch_domain_laundry != false) {
            $laundry = 0;

            foreach ($checkdeliveryFeeAdded->products as $key => $prod) {
                $isNotLongTerm = 1;

                if ($prod->product->is_long_term_service == 1) {
                    $isNotLongTerm = 0;
                }



                if (($isNotLongTerm == 1) && $prod->product->category->categoryDetail->type_id == 9) {    ///////// if product from laundry

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


                            ////\Log::info('placeRequestToDispatchLaundry');
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
            $getAdditionalPreference = getAdditionalPreference(['blockchain_route_formation', 'blockchain_api_domain', 'blockchain_address_id']);

            $dynamic = uniqid($order->id . $vendor);
            $vendor_details = Vendor::where('id', $vendor)->select('id', 'phone_no', 'email', 'name', 'latitude', 'longitude', 'address', 'order_pre_time')->first();
            $orderVendorDetails = OrderVendor::where('vendor_id', $vendor_details->id)->where('order_id', $order->id)->first();

            if ($order->payment_option_id == 1 && ($order->payable_amount > 0)) {
                $cash_to_be_collected = 'Yes';
                $payable_amount = $orderVendorDetails->payable_amount - $order->loyalty_amount_saved - $order->wallet_amount_used + $order->tip_amount;
            } else {
                if ($order->is_postpay == 1 && $order->payment_status == 0) {
                    $cash_to_be_collected = 'Yes';
                    $payable_amount = $orderVendorDetails->payable_amount - $order->loyalty_amount_saved - $order->wallet_amount_used + $order->tip_amount;
                } else {
                    $cash_to_be_collected = 'No';
                    $payable_amount = 0.00;
                }
            }
            if (!empty($orderVendorDetails->web_hook_code)) {
                $dynamic = $orderVendorDetails->web_hook_code;
            }
            $call_back_url = route('dispatch-order-update', $dynamic);

            $tasks = array();
            $meta_data = '';

            $unique = Auth::user()->code;
            $team_tag = $unique . "_" . $vendor;

            if (isset($order->scheduled_date_time) && !empty($order->scheduled_date_time)) {
                $task_type = 'schedule';
                $schedule_time = $order->scheduled_date_time ?? null;
            } else {
                $task_type = 'now';
            }
            $vendorProduct = OrderVendorProduct::where('order_id', $order->id)->first();
            $tags = isset($vendorProduct->product) ? $vendorProduct->product->tags : '';

            if (!empty($orderVendorDetails->scheduled_date_time) && $orderVendorDetails->scheduled_date_time > 0) {
                $task_type = 'schedule';
                $user = Auth::user();
                $selectedDate = dateTimeInUserTimeZone($orderVendorDetails->scheduled_date_time, $user->timezone);
                $slot = trim(explode("-", $orderVendorDetails->schedule_slot)[0]);

                $slotTime = date('H:i:s', strtotime("$slot"));
                $selectedDate = date('Y-m-d', strtotime($selectedDate));
                $scheduleDateTime = $selectedDate . ' ' . $slotTime;
                $schedule_time =  $scheduleDateTime ?? null;
            }

            if (checkColumnExists('orders', 'recurring_booking_type') && ($order->recurring_day_data != "")) {
                $date       = explode(",", $order->recurring_day_data);
                $start_date = $end_date = '';
                if (isset($date[0])) {
                    $start_date = $date[0];
                }
                if (isset($date[1])) {
                    $end_date   = $date[1];
                }
                $days_count     = 0;
                if (!empty($start_date) && !empty($end_date)) {
                    $days_count = Carbon::parse($start_date)->diffInDays($end_date);
                    $startDate  = Carbon::createFromFormat('Y-m-d', $start_date);
                    $endDate    = Carbon::createFromFormat('Y-m-d', $end_date);
                    $dateRange  = CarbonPeriod::create($startDate, $endDate);
                    $dates      = array_map(fn($date) => $date->format('Y-m-d'), iterator_to_array($dateRange));
                    $date      = $dates[0];
                }
                if ($days_count > 0) {
                    $recurring_data = OrderLongTermServiceSchedule::where(['order_number' => $order->order_number])->first();
                    $schedule_time = $recurring_data->schedule_date;
                    $tasks[] = array(
                        'task_type_id' => 1,
                        'latitude' => $vendor_details->latitude ?? '',
                        'longitude' => $vendor_details->longitude ?? '',
                        'short_name' => '',
                        'address' => $vendor_details->address ?? '',
                        'post_code' => '',
                        'barcode' => '',
                        'flat_no'     => null,
                        'email'       => $vendor_details->email ?? null,
                        'phone_number' => $vendor_details->phone_no ?? null,
                    );

                    $tasks[] = array(
                        'task_type_id' => 2,
                        'latitude' => $cus_address->latitude ?? '',
                        'longitude' => $cus_address->longitude ?? '',
                        'short_name' => '',
                        'address' => $cus_address->address ?? '',
                        'post_code' => $cus_address->pincode ?? '',
                        'barcode' => '',
                        'flat_no'     => $cus_address->house_number ?? null,
                        'email'       => $customer->email ?? null,
                        'phone_number' => ($customer->dial_code . $customer->phone_number)  ?? null,
                    );

                    if ($customer->dial_code == "971") {
                        // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                        $customerno = "0" . $customer->phone_number;
                    } else {
                        // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                        $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
                    }
                    $client = CP::orderBy('id', 'asc')->first();

                    $postdata =  [
                        'order_number' =>  $order->order_number,
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
                        'order_number' => $order->order_number,
                        'barcode' => '',
                        'order_team_tag' => $team_tag,
                        'order_agent_tag' => $tags,
                        'call_back_url' => $call_back_url ?? null,
                        'task' => $tasks,
                        'is_restricted' => $orderVendorDetails->is_restricted,
                        'vendor_id' => $vendor_details->id,
                        'order_vendor_id' => $orderVendorDetails->id,
                        'dbname' => $client->database_name,
                        'order_id' => $order->id,
                        'customer_id' => $order->user_id,
                        'user_icon' => $customer->image,
                        'vendor_name' => $vendor_details->name ?? null,
                        'tip_amount' => $order->tip_amount ?? 0,
                        'payment_method' => $order->payment_method,
                        'order_pre_time' => $vendor_details->order_pre_time,
                        'app_call' => 0,
                    ];
                    //pr($postdata);
                    if ($orderVendorDetails->is_restricted == 1) {
                        $postdata['user_verification_type'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? $customer->passbase_verification->resources->type : null;
                        $postdata['user_datapoints'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? json_decode($customer->passbase_verification->resources->datapoints) : null;
                    }

                    $client = new Client([
                        'headers' => [
                            'personaltoken' => $dispatch_domain->delivery_service_key,
                            'shortcode' => $dispatch_domain->delivery_service_key_code,
                            'content-type' => 'application/json'
                        ]
                    ]);

                    $url = $dispatch_domain->delivery_service_key_url;
                    $res = $client->post(
                        $url . '/api/task/create',
                        ['form_params' => ($postdata)]
                    );




                    $response = json_decode($res->getBody(), true);
                    if ($response && $response['task_id'] > 0) {
                        $dispatch_traking_url = $response['dispatch_traking_url'] ?? '';
                        $up_web_hook_code = OrderVendor::where(['order_id' => $order->id, 'vendor_id' => $vendor])
                            ->update(['web_hook_code' => $dynamic, 'dispatch_traking_url' => $dispatch_traking_url]);
                        $recurring_data->web_hook_code          = $dynamic;
                        $recurring_data->dispatch_traking_url   = $dispatch_traking_url;
                        $recurring_data->save();
                        return 1;
                    }
                }
            } else {


                $tasks[] = array(
                    'task_type_id' => 1,
                    'latitude' => $vendor_details->latitude ?? '',
                    'longitude' => $vendor_details->longitude ?? '',
                    'short_name' => '',
                    'address' => $vendor_details->address ?? '',
                    'post_code' => '',
                    'barcode' => '',
                    'flat_no'     => null,
                    'email'       => $vendor_details->email ?? null,
                    'phone_number' => $vendor_details->phone_no ?? null,
                );

                $tasks[] = array(
                    'task_type_id' => 2,
                    'latitude' => $cus_address->latitude ?? '',
                    'longitude' => $cus_address->longitude ?? '',
                    'short_name' => '',
                    'address' => $cus_address->address ?? '',
                    'post_code' => $cus_address->pincode ?? '',
                    'barcode' => '',
                    'flat_no'     => $cus_address->house_number ?? null,
                    'email'       => $customer->email ?? null,
                    'phone_number' => ($customer->dial_code . $customer->phone_number)  ?? null,
                );

                if ($customer->dial_code == "971") {
                    // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                    $customerno = "0" . $customer->phone_number;
                } else {
                    // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                    $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
                }
                $client = CP::orderBy('id', 'asc')->first();
                $postdata =  [
                    'order_number' =>  $order->order_number,
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
                    'order_number' => $order->order_number,
                    'barcode' => '',
                    'order_team_tag' => $team_tag,
                    'order_agent_tag' => $tags,
                    'call_back_url' => $call_back_url ?? null,
                    'task' => $tasks,
                    'is_restricted' => $orderVendorDetails->is_restricted,
                    'vendor_id' => $vendor_details->id,
                    'order_vendor_id' => $orderVendorDetails->id,
                    'dbname' => $client->database_name,
                    'order_id' => $order->id,
                    'customer_id' => $order->user_id,
                    'user_icon' => $customer->image,
                    'vendor_name' => $vendor_details->name ?? null,
                    'tip_amount' => $order->tip_amount,
                    'payment_method' => $order->payment_method,
                    'order_pre_time' => $vendor_details->order_pre_time
                ];

                if ($orderVendorDetails->is_restricted == 1) {
                    $postdata['user_verification_type'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? $customer->passbase_verification->resources->type : null;
                    $postdata['user_datapoints'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? json_decode($customer->passbase_verification->resources->datapoints) : null;
                }

                if ($getAdditionalPreference['blockchain_route_formation'] == 1) {
                    $client = new Client([
                        'headers' => [
                            'personaltoken' => $dispatch_domain->delivery_service_key,
                            'shortcode' => $dispatch_domain->delivery_service_key_code,
                            'content-type' => 'application/json',
                            'blockchain_route_formation' => $getAdditionalPreference['blockchain_route_formation'],
                            'blockchain_api_domain' => $getAdditionalPreference['blockchain_api_domain'],
                            'blockchain_address_id' => $getAdditionalPreference['blockchain_address_id']
                        ]
                    ]);
                } else {
                    $client = new Client([
                        'headers' => [
                            'personaltoken' => $dispatch_domain->delivery_service_key,
                            'shortcode' => $dispatch_domain->delivery_service_key_code,
                            'content-type' => 'application/json'
                        ]
                    ]);
                }
                $url = $dispatch_domain->delivery_service_key_url;
                // dd($url);
                $res = $client->post(
                    $url . '/api/task/create',
                    ['form_params' => ($postdata)]
                );

                $response = json_decode($res->getBody(), true);
                if ($response && $response['task_id'] > 0) {
                    $dispatch_traking_url = $response['dispatch_traking_url'] ?? '';
                    $up_web_hook_code = OrderVendor::where(['order_id' => $order->id, 'vendor_id' => $vendor])
                        ->update(['web_hook_code' => $dynamic, 'dispatch_traking_url' => $dispatch_traking_url]);

                    return 1;
                }
            }
            return 2;
        } catch (\Exception $e) {
            return 2;
            // return response()->json([
            //     'status' => 'error',
            //     'message' => $e->getMessage()
            // ]);
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
            $order_vendor = OrderVendor::where(['order_id' => $order->id, 'vendor_id' => $vendor])->first();
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
                'flat_no'     => null,
                'email'       => $vendor_details->email ?? null,
                'phone_number' => $vendor_details->phone_no ?? null,
            );

            $tasks[] = array(
                'task_type_id' => 2,
                'latitude' => $cus_address->latitude ?? '',
                'longitude' => $cus_address->longitude ?? '',
                'short_name' => '',
                'address' => $cus_address->address ?? '',
                'post_code' => $cus_address->pincode ?? '',
                'barcode' => '',
                'flat_no'     => $cus_address->house_number ?? null,
                'email'       => $customer->email ?? null,
                'phone_number' => ($customer->dial_code . $customer->phone_number)  ?? null,
            );

            if ($customer->dial_code == "971") {
                // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                $customerno = "0" . $customer->phone_number;
            } else {
                // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
            }
            $client = CP::orderBy('id', 'asc')->first();
            $postdata =  [
                'order_number' =>  $order->order_number,
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


            $client = new Client([
                'headers' => [
                    'personaltoken' => $dispatch_domain->dispacher_home_other_service_key,
                    'shortcode' => $dispatch_domain->dispacher_home_other_service_key_code,
                    'content-type' => 'application/json'
                ]
            ]);

            $url = $dispatch_domain->dispacher_home_other_service_key_url;
            $res = $client->post(
                $url . '/api/task/create',
                ['form_params' => ($postdata)]
            );
            $response = json_decode($res->getBody(), true);
            if ($response && $response['task_id'] > 0) {
                $dispatch_traking_url = $response['dispatch_traking_url'] ?? '';
                $up_web_hook_code = OrderVendor::where(['order_id' => $order->id, 'vendor_id' => $vendor])
                    ->update(['web_hook_code' => $dynamic, 'dispatch_traking_url' => $dispatch_traking_url]);

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
        // //\Log::info('placeRequestToDispatchLaundry -- 1');

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
            $vendor_details = Vendor::where('id', $vendor)->select('id', 'phone_no', 'email', 'name', 'latitude', 'longitude', 'address')->first();
            $order_vendor = OrderVendor::where(['order_id' => $order->id, 'vendor_id' => $vendor])->first();
            $tasks = array();
            $meta_data = '';
            $rtype = 'P';
            $unique = Auth::user()->code;
            if ($colm == 1) {     # 1 for pickup from customer drop to vendor --- Pickup Request
                $desc = $order->comment_for_pickup_driver ?? null;
                $tasks[] = array(
                    'task_type_id' => 1,
                    'latitude' => $cus_address->latitude ?? '',
                    'longitude' => $cus_address->longitude ?? '',
                    'short_name' => '',
                    'address' => $cus_address->address ?? '',
                    'post_code' => $cus_address->pincode ?? '',
                    'barcode' => '',
                );
                $tasks[] = array(
                    'task_type_id' => 2,
                    'latitude' => $vendor_details->latitude ?? '',
                    'longitude' => $vendor_details->longitude ?? '',
                    'short_name' => '',
                    'address' => $vendor_details->address ?? '',
                    'post_code' => '',
                    'barcode' => '',
                );

                if (isset($order->schedule_pickup) && !empty($order->schedule_pickup)) {
                    $task_type = 'schedule';
                    $schedule_time = $order->schedule_pickup ?? null;
                } else {
                    $task_type = 'now';
                }
            }


            if ($colm == 2) { # 1 for pickup from vendor drop to customer --- Delivery Request
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
                    'flat_no'     => null,
                    'email'       => $vendor_details->email ?? null,
                    'phone_number' => $vendor_details->phone_no ?? null,
                );

                $tasks[] = array(
                    'task_type_id' => 2,
                    'latitude' => $cus_address->latitude ?? '',
                    'longitude' => $cus_address->longitude ?? '',
                    'short_name' => '',
                    'address' => $cus_address->address ?? '',
                    'post_code' => $cus_address->pincode ?? '',
                    'barcode' => '',
                    'flat_no'     => $cus_address->house_number ?? null,
                    'email'       => $customer->email ?? null,
                    'phone_number' => ($customer->dial_code . $customer->phone_number)  ?? null,
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
            $postdata =  [
                'order_number' =>  $order->order_number,
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
                'request_type' => $rtype ?? 'P',
                'is_restricted' => $order_vendor->is_restricted ?? '0',
                'vendor_id' => $vendor_details->id ?? '',
                'order_vendor_id' => $order_vendor->id ?? '',
                'dbname' => $client->database_name,
                'order_id' => $order->id ?? '',
                'customer_id' => $order->user_id,
                'user_icon' => $customer->image,
                'tip_amount' => $order->tip_amount ?? 0

            ];
            ////\Log::info(json_encode($postdata));
            // if($order_vendor->is_restricted == 1)
            // {
            //     $postdata['user_verification_type'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? $customer->passbase_verification->resources->type : null;
            //     $postdata['user_datapoints'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? json_decode($customer->passbase_verification->resources->datapoints) : null;
            // }


            $client = new Client([
                'headers' => [
                    'personaltoken' => $dispatch_domain->laundry_service_key,
                    'shortcode' => $dispatch_domain->laundry_service_key_code,
                    'content-type' => 'application/json'
                ]
            ]);

            $url = $dispatch_domain->laundry_service_key_url;

            $res = $client->post(
                $url . '/api/task/create',
                ['form_params' => ($postdata)]
            );


            $response = json_decode($res->getBody(), true);

            if ($response && $response['task_id'] > 0) {
                $dispatch_traking_url = $response['dispatch_traking_url'] ?? '';
                $up_web_hook_code = OrderVendor::where(['order_id' => $order->id, 'vendor_id' => $vendor])
                    ->update(['web_hook_code' => $dynamic, 'dispatch_traking_url' => $dispatch_traking_url]);

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
        if ($preference->need_delivery_service == 1 && !empty($preference->delivery_service_key) && !empty($preference->delivery_service_key_code) && !empty($preference->delivery_service_key_url))
            return $preference;
        else
            return false;
    }


    # get prefereance if on demand on in config
    public function getDispatchOnDemandDomain()
    {
        $preference = ClientPreference::first();
        if ($preference->need_dispacher_home_other_service == 1 && !empty($preference->dispacher_home_other_service_key) && !empty($preference->dispacher_home_other_service_key_code) && !empty($preference->dispacher_home_other_service_key_url))
            return $preference;
        else
            return false;
    }

    # get prefereance if laundry in config
    public function getDispatchLaundryDomain()
    {
        $preference = ClientPreference::first();
        if ($preference->need_laundry_service == 1 && !empty($preference->laundry_service_key) && !empty($preference->laundry_service_key_code) && !empty($preference->laundry_service_key_url))
            return $preference;
        else
            return false;
    }



    /**
     * Display a listing of the order return request.
     *
     * @return \Illuminate\Http\Response
     */
    public function returnOrders(Request $request, $domain = '', $status)
    {
        try {
            $user = Auth::user();
            $orders_list = OrderReturnRequest::where('status', $status)->whereIn('type', [1, 3])->with('product', 'order')->orderBy('updated_at', 'DESC');
            if ($user->is_superadmin == 0) {
                $orders_list = $orders_list->whereHas('order.vendors.vendor.permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            }
            $orders[$status] = $orders_list->paginate(20);
            $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
            // all vendors
            $vendors = Vendor::where('status', '!=', '2')->orderBy('id', 'desc');
            if ($user->is_superadmin == 0) {
                $vendors = $vendors->whereHas('permissionToUser', function ($query) use ($user) {
                    $query->where('user_id', $user->id);
                });
            }
            $vendors = $vendors->get();
            // dd( $orders[$status]);
            return view(
                'backend.order.return',
                [
                    'orders' => $orders,
                    'status' => $status,
                    'clientCurrency' => $clientCurrency,
                    'vendors' => $vendors
                ]
            );
        } catch (\Throwable $th) {
            return redirect()->back();
        }
    }
    public function returnOrderFilter(Request $request)
    {
        try {
            $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
            $user = Auth::user();
            $timezone = $user->timezone;
            $orders_list = OrderReturnRequest::with('product', 'order')->orderBy('updated_at', 'DESC');
            if ($user->is_superadmin == 0) {
                $orders_list = $orders_list->whereHas('order.vendors.vendor.permissionToUser', function ($query) {
                    $query->where('user_id', Auth::user()->id);
                });
            }
            $orders_list = $orders_list->whereIn('type', [1, 3]); // 1 = return , 2 = exchange

            if (!empty($request->search_keyword)) {
                $orders_list->whereHas('order', function ($query)  use ($request) {
                    $query->whereHas('address', function ($q) use ($request) {
                        $q->where('house_number', 'like', '%' . $request->search_keyword . '%')
                            ->orWhere('address', 'like', '%' . $request->search_keyword . '%')
                            ->orWhere('street', 'like', '%' . $request->search_keyword . '%')
                            ->orWhere('city', 'like', '%' . $request->search_keyword . '%')
                            ->orWhere('state', 'like', '%' . $request->search_keyword . '%')
                            ->orWhere('pincode', 'like', '%' . $request->search_keyword . '%')
                            ->orWhere('country', 'like', '%' . $request->search_keyword . '%');
                    })->orWhere('order_number', 'like', '%' . $request->search_keyword . '%');
                });
            }
            //get by vendor
            if (!empty($request->get('vendor_id'))) {
                $orders_list->whereHas('product', function ($query)  use ($request) {
                    $query->where('vendor_id', $request->get('vendor_id'));
                });
            }
            //filer bitween date
            if (!empty($request->get('date_filter'))) {
                $date_date_filter = explode(' to ', $request->get('date_filter'));
                $to_date = (!empty($date_date_filter[1])) ? $date_date_filter[1] : $date_date_filter[0];
                $from_date = $date_date_filter[0];

                $orders_list->whereBetween('created_at', [$from_date . " 00:00:00", $to_date . " 23:59:59"]);
            }
            $Accepted = [];
            $Pending = [];
            $Rejected = [];
            $pending_orders = clone $orders_list;
            $accepted_orders = clone $orders_list;
            $rejected_orders = clone $orders_list;

            $pending_orders = $pending_orders->where('status', 'Pending')->paginate(20);
            $accepted_orders = $accepted_orders->where('status', 'Accepted')->paginate(20);
            $rejected_orders = $rejected_orders->where('status', 'Rejected')->paginate(20);

            $Pending = $pending_orders;
            $Accepted = $accepted_orders;
            $Rejected = $rejected_orders;
            $pending_html = view('backend.order.return-data')->with(['orders' => $Pending, 'status' => 'Pending', 'clientCurrency' => $clientCurrency, 'timezone' => $timezone])->render();
            $accepted_html = view('backend.order.return-data')->with(['orders' => $Accepted, 'status' => 'Accepted', 'clientCurrency' => $clientCurrency, 'timezone' => $timezone])->render();
            $rejected_html = view('backend.order.return-data')->with(['orders' => $Rejected, 'status' => 'Rejected', 'clientCurrency' => $clientCurrency, 'timezone' => $timezone])->render();

            return $this->successResponse(['pending_html' => $pending_html, 'accepted_html' => $accepted_html, 'rejected_html' => $rejected_html], '', 201);
        } catch (\Throwable $e) {
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * View Rescheduled Orders
     * Get Route
     * Added by Ovi
     */
    public function rescheduledOrders(Request $request)
    {
        try {
            $rescheduleOrders = RescheduleOrder::all();
            return view('backend.order.reschedule', ['rescheduleOrders' => $rescheduleOrders]);
        } catch (\Throwable $th) {
            return redirect()->back();
        }
    }


    /**
     * return orders details
     */
    public function getReturnProductModal(Request $request, $domain = '')
    {
        try {
            $return_details = OrderReturnRequest::where('id', $request->id)->whereIn('type', [1, 3])->with('returnFiles')->first();
            if (isset($return_details)) {

                if ($request->ajax()) {
                    return \Response::json(\View::make('frontend.modals.update-return-product-client', array('return_details' => $return_details))->render());
                }
            }
            return $this->errorResponse('Invalid order', 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }


    /**
     * return rental orders details
     */
    public function getRentalReturnProductModal(Request $request, $domain = '')
    {
        // dd($request->all());
        try {
            $return_details = OrderProductDispatchReturnRoute::with(['order', 'orderProduct', 'orderProduct.pvariant', 'orderProduct.product'])->where('id', $request->id)->first();
            if (isset($return_details)) {

                if ($request->ajax()) {
                    return \Response::json(\View::make('frontend.modals.update-rental-return-product-client', array('return_details' => $return_details))->render());
                }
            }
            return $this->errorResponse('Invalid order', 404);
        } catch (Exception $e) {
            return $this->errorResponse($e->getMessage(), $e->getCode());
        }
    }

    /**
     * return  order product
     */
    public function updateProductReturn(Request $request)
    {
        DB::beginTransaction();
        try {
            $return = OrderReturnRequest::with('order')->find($request->id);
            // dd($return->created_at);
            $returns = OrderReturnRequest::where('id', $request->id)->update(['status' => $request->status ?? null, 'reason_by_vendor' => $request->reason_by_vendor ?? null]);
            if (isset($returns) && $return->type == 1) {
                if ($request->status == 'Accepted' && $return->status != 'Accepted') {
                    $user = User::find($return->return_by);
                    $wallet = $user->wallet;
                    $order_product = OrderProduct::find($return->order_vendor_product_id);
                    if (!empty($return->order) && $return->order->luxury_option_id != 4) {
                        $credit_amount = $order_product->price + $order_product->taxable_amount;
                        $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> for return ' . $order_product->product_name]);
                    }
                    $dispatch_domain = $this->getDispatchDomain();
                    $order_details = OrderProduct::where('id', $return->order_vendor_product_id)->whereHas('order', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })->first();
                    $this->ProductVariantStockIncreaseByOrderId($order_product->order_id);
                    $this->placeReturnRequestToDispatch($order_details->order_id, $order_details->vendor_id, $dispatch_domain, $order_details);
                }
                DB::commit();
                return $this->successResponse($returns, 'Updated.');
            } elseif (isset($returns) && $return->type == 3) {
                if ($request->status == 'Accepted' && $return->status != 'Accepted') {
                    $user = User::find($return->return_by);
                    $wallet = $user->wallet;
                    $order_product = OrderProduct::find($return->order_vendor_product_id);
                    $product_detail = Product::with('variant')->find($order_product->product_id);

                    // Get Remaining Hours
                    $to = Carbon::createFromFormat('Y-m-d H:s:i', $return->created_at); //request created date
                    $from = Carbon::createFromFormat('Y-m-d H:s:i', $order_product->end_date_time); //product end date
                    $diff_in_hours = $to->diffInHours($from);

                    // Get Total Hours
                    $start_hour = Carbon::createFromFormat('Y-m-d H:s:i', $order_product->start_date_time);
                    $end_hour = Carbon::createFromFormat('Y-m-d H:s:i', $order_product->end_date_time);
                    $total_hours = $start_hour->diffInHours($end_hour);

                    $hours_used = floor($total_hours - $diff_in_hours);
                    if ($hours_used <= $product_detail->minimum_duration) {
                        $credit_amount = $order_product->incremental_price;
                    } else {
                        $remaining_hours = floor(($total_hours - $hours_used) / $product_detail->additional_increments);
                        $credit_amount = ($remaining_hours * $product_detail->variant[0]['incremental_price']);
                    }
                    $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> for return ' . $order_product->product_name]);
                    $dispatch_domain = $this->getDispatchDomain();
                    $order_details = OrderProduct::where('id', $return->order_vendor_product_id)->whereHas('order', function ($q) use ($user) {
                        $q->where('user_id', $user->id);
                    })->first();
                    $this->ProductVariantStockIncreaseByOrderId($order_product->order_id);
                    $this->placeReturnRequestToDispatch($order_details->order_id, $order_details->vendor_id, $dispatch_domain, $order_details);
                    $this->ProductVariantStockIncreaseByOrderId($order_product->order_id);
                    DB::commit();
                    return $this->successResponse($returns, 'Updated.');
                }
            }
            return $this->errorResponse('Invalid order', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    /**
     * return  order product
     */
    public function updateProductRentalReturn(Request $request)
    {
        DB::beginTransaction();
        try {
            $return = OrderProductDispatchReturnRoute::with(['order', 'orderProduct', 'orderProduct.pvariant', 'orderProduct.product'])->where('id', $request->id)->first();
            if (@$request->status && $request->status == 'Accepted') {
                $returns = OrderProductDispatchReturnRoute::where('id', $request->id)->update(['dispatcher_status_option_id' => 6]);
                $update_return_status = OrderReturnRequest::where('order_vendor_product_id', $request->order_vendor_product_id)->update(['status' => 'Completed']);
            }

            if (isset($returns)) {
                $security_amount = $return->orderProduct->security_amount;
                if ($request->damage > 0) {
                    $security_amount = $security_amount - $request->damage;
                }

                $user = User::find($return->order->user_id);
                $wallet = $user->wallet;
                $order_product = OrderProduct::find($return->order_vendor_product_id);
                $credit_amount = $security_amount;
                $wallet->depositFloat($credit_amount, ['Wallet has been <b>Credited</b> for secuirity return ' . $order_product->product_name]);
                $this->ProductVariantStockIncreaseByOrderId($order_product->order_id, 'rental');
                $product_bookings = ProductBooking::where('order_vendor_product_id', $request->order_vendor_product_id)->update(['on_rent' => 0]);
                DB::commit();
                return $this->successResponse($returns, 'Updated.');
            }
            return $this->errorResponse('Invalid order', 200);
        } catch (Exception $e) {
            DB::rollback();
            return $this->errorResponse($e->getMessage(), 400);
        }
    }

    // place Request To Dispatch
    public function placeReturnRequestToDispatch($order, $vendor, $dispatch_domain, $order_product)
    {
        try {

            $order = Order::find($order);
            $customer = User::find($order->user_id);
            $cus_address = UserAddress::find($order->address_id);
            $tasks = array();
            if ($order->payment_option_id == 1) {
                $cash_to_be_collected = 'Yes';
                $payable_amount = $order->payable_amount;
            } else {

                if ($order->is_postpay == 1 && $order->payment_status == 0) {
                    $cash_to_be_collected = 'Yes';
                    $payable_amount = $order->payable_amount;
                } else {
                    $cash_to_be_collected = 'No';
                    $payable_amount = 0.00;
                }
            }
            $dynamic = uniqid($order->id . $vendor);
            $call_back_url = route('dispatch-order-update', $dynamic);
            $vendor_details = Vendor::where('id', $vendor)->select('id', 'phone_no', 'email', 'name', 'latitude', 'longitude', 'address')->first();
            $tasks = array();
            $meta_data = '';

            $unique = Auth::user()->code;
            $team_tag = $unique . "_" . $vendor;

            if (isset($order->scheduled_date_time) && !empty($order->scheduled_date_time)) {
                $task_type = 'schedule';
                $schedule_time = $order->scheduled_date_time ?? null;
            } else {
                $task_type = 'now';
            }

            $orderVendorDetails = OrderVendor::where('vendor_id', $vendor_details->id)->where('order_id', $order->id)->get()->first();
            if (!empty($orderVendorDetails->scheduled_date_time) && $orderVendorDetails->scheduled_date_time > 0) {
                $task_type = 'schedule';
                $user = Auth::user();
                $selectedDate = dateTimeInUserTimeZone($orderVendorDetails->scheduled_date_time, $user->timezone);
                $slot = trim(explode("-", $orderVendorDetails->schedule_slot)[0]);

                $slotTime = date('H:i:s', strtotime("$slot"));
                $selectedDate = date('Y-m-d', strtotime($selectedDate));
                $scheduleDateTime = $selectedDate . ' ' . $slotTime;
                $schedule_time =  $scheduleDateTime ?? null;
            }

            $tasks[] = array(
                'task_type_id' => 1,
                'latitude' => $cus_address->latitude ?? '',
                'longitude' => $cus_address->longitude ?? '',
                'short_name' => '',
                'address' => $cus_address->address ?? '',
                'post_code' => $cus_address->pincode ?? '',
                'barcode' => '',
                'flat_no'     => $cus_address->house_number ?? null,
                'email'       => $customer->email ?? null,
                'phone_number' => ($customer->dial_code . $customer->phone_number)  ?? null,
            );
            $tasks[] = array(
                'task_type_id' => 2,
                'latitude' => $vendor_details->latitude ?? '',
                'longitude' => $vendor_details->longitude ?? '',
                'short_name' => '',
                'address' => $vendor_details->address ?? '',
                'post_code' => '',
                'barcode' => '',
                'flat_no'     => null,
                'email'       => $vendor_details->email ?? null,
                'phone_number' => $vendor_details->phone_no ?? null,
            );

            if ($customer->dial_code == "971") {
                // $customerno = '+' . $customer->dial_code . "0" . $customer->phone_number;
                $customerno = "0" . $customer->phone_number;
            } else {
                // $customerno = ($customer->phone_number) ? '+' . $customer->dial_code . $customer->phone_number : rand(111111, 11111) ;
                $customerno = ($customer->phone_number) ? $customer->phone_number : rand(111111, 11111);
            }
            $client = CP::orderBy('id', 'asc')->first();
            $postdata =  [
                'order_number' =>  $order->order_number,
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
                'royo_order_number' => $order->order_number,
                'barcode' => '',
                'order_team_tag' => $team_tag,
                'call_back_url' => $call_back_url ?? null,
                'task' => $tasks,
                'is_restricted' => $orderVendorDetails->is_restricted,
                'vendor_id' => $vendor_details->id,
                'order_vendor_id' => $orderVendorDetails->id,
                'dbname' => $client->database_name,
                'order_id' => $order->id,
                'customer_id' => $order->user_id,
                'user_icon' => $customer->image
            ];
            //pr($postdata);
            if ($orderVendorDetails->is_restricted == 1) {
                $postdata['user_verification_type'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? $customer->passbase_verification->resources->type : null;
                $postdata['user_datapoints'] = isset($customer->passbase_verification) && !is_null($customer->passbase_verification) ? json_decode($customer->passbase_verification->resources->datapoints) : null;
            }

            $client = new Client([
                'headers' => [
                    'personaltoken' => $dispatch_domain->delivery_service_key,
                    'shortcode' => $dispatch_domain->delivery_service_key_code,
                    'content-type' => 'application/json'
                ]
            ]);
            //\Log::info("header", $postdata);
            $url = $dispatch_domain->delivery_service_key_url;

            $res = $client->post(
                $url . '/api/return-to-warehouse-task',
                ['form_params' => ($postdata)]
            );
            $response = json_decode($res->getBody(), true);
            if ($response && $response['task_id'] > 0) {
                $dispatch_traking_url = $response['dispatch_traking_url'] ?? '';
                $up_web_hook_code = OrderVendor::where(['order_id' => $order->id, 'vendor_id' => $vendor])
                    ->update(['web_hook_code' => $dynamic, 'dispatch_traking_url' => $dispatch_traking_url]);

                //track url
                // OrderReturnRequest

                $dispatch_route                                 = new OrderProductDispatchReturnRoute();
                $dispatch_route->order_id                       = $order->id;
                $dispatch_route->order_vendor_id                = $order_product->order_vendor_id;
                $dispatch_route->order_vendor_product_id        = $order_product->id;
                $dispatch_route->web_hook_code                  = $dynamic;
                $dispatch_route->dispatch_traking_url           = $dispatch_traking_url;
                $dispatch_route->dispatcher_status_option_id    = 4;
                $dispatch_route->order_status_option_id         = 1;
                $dispatch_route->save();

                return 1;
            }
            return 2;
        } catch (\Exception $e) {
            // Log::info($e->getMessage());
            return 2;
            // return response()->json([
            //     'status' => 'error',
            //     'message' => $e->getMessage()
            // ]);
        }
    }

    public function sendStatusChangePushNotificationCustomer($user_ids, $orderData, $order_status_id)
    {
        $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $user_ids)->pluck('device_token')->toArray();

        $client_preferences = ClientPreference::select('fcm_server_key', 'favicon')->first();
        // if ($order_status_id == 3) {
        //     $this->sendCancelledEmail($user_ids, $orderData);
        // }
        if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
            if ($order_status_id == 2 || $order_status_id == 9) {
                $notification_content = NotificationTemplate::where('id', 5)->first();
            } elseif ($order_status_id == 10) {
                $notification_content = NotificationTemplate::where('id', 20)->first();
            } elseif ($order_status_id == 3) {
                $notification_content = NotificationTemplate::where('id', 6)->first();
            } elseif ($order_status_id == 4) {
                $notification_content = NotificationTemplate::where('id', 7)->first();
            } elseif ($order_status_id == 5) {
                //Check for order is takeaway
                if (@$orderData->luxury_option_id == 3) {
                    $notification_content = NotificationTemplate::where('slug', 'order-out-for-takeaway-delivery')->first();
                } else {
                    $notification_content = NotificationTemplate::where('id', 21)->first();
                }
            } elseif ($order_status_id == 6) {
                $notification_content = NotificationTemplate::where('id', 24)->first();
            }
            if ($notification_content) {
                $body_content = str_ireplace("{order_id}", "#" . $orderData->orderDetail->order_number, $notification_content->content);
                $redirect_URL['type'] = 4;
                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $notification_content->subject,
                        'body'  => $body_content,
                        'sound' => "default",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                        'click_action' => '',
                        "android_channel_id" => "default-channel-id",
                        "redirect_type" => $redirect_URL['type']
                    ],
                    "data" => [
                        'title' => $notification_content->subject,
                        'body'  => $body_content,
                        "type" => "order_status_change",
                        "order_id" => $orderData->id,
                        "vendor_id" => $orderData->ordervendor->vendor_id ?? '',
                        "order_status" => $order_status_id,
                        "redirect_type" => $redirect_URL['type']
                    ],
                    "priority" => "high"
                ];
                sendFcmCurlRequest($data);
            }
        }
    }

    protected function sendCancelledEmail($user_ids, $orderData)
    {

        $data = ClientPreference::select(
            'sms_key',
            'sms_secret',
            'sms_from',
            'mail_type',
            'mail_driver',
            'mail_host',
            'mail_port',
            'mail_username',
            'sms_provider',
            'mail_password',
            'mail_encryption',
            'mail_from'
        )->where('id', '>', 0)->first();
        $confirured = $this->setMailDetail($data->mail_driver, $data->mail_host, $data->mail_port, $data->mail_username, $data->mail_password, $data->mail_encryption);

        $client = ClientData::select('id', 'name', 'email', 'phone_number', 'logo', 'sub_domain', 'custom_domain')->where('id', '>', 0)->first();
        $mail_from = $data->mail_from;

        $email_template_content = '';
        $email_template = EmailTemplate::where('id', 12)->first();
        $email_template_content = $email_template->content;
        //     if ($vendor_id == "") {
        $returnHTML = view('email.orderCancelEmail')->with(['products' => $orderData->products->toArray()])->render();
        //     } else {
        //$returnHTML = view('email.newOrderVendorProducts')->with(['cartData' => $cartDetails,'order' => $order, 'id' => $vendor_id, 'currencySymbol' => $currSymbol, 'luxuryOptionTitle' => $luxuryOptionTitle])->render();
        // }
        $user = User::where('id', $user_ids[0])->first();
        $email_template_content = str_ireplace("{name}", ucwords($user->name), $email_template_content);
        $email_template_content = str_ireplace("{order_id}", "#" . $orderData->orderDetail->order_number, $email_template_content);
        // $email_template_content = str_ireplace("{description}", '', $email_template_content);
        $email_template_content = str_ireplace("{products}", $returnHTML, $email_template_content);
        $body_content =
            $client_name = $client->name;
        $email_data = [
            'link' => "link",
            'mail_from' => $mail_from,
            'client_name' => $client_name,
            'logo' => $client->logo['original'],
            'subject' => $email_template->subject,
            'customer_name' => ucwords($user->name),
            'email_template_content' => $email_template_content
        ];

        if (!empty($data['admin_email'])) {
            $email_data['admin_email'] = $data['admin_email'];
        }
        $vendor_id = $orderData->vendor_id;

        /* -- Sending email to vendor -- */
        $vendor = Vendor::where('id', $vendor_id)->first();
        if (!empty($vendor)) {
            $email_data['email'] = $vendor->email;
            dispatch(new \App\Jobs\SendOrderSuccessEmailJob($email_data))->onQueue('verify_email');
        }

        /* -- Sending email to customer -- */
        $email_data['email'] = $user->email;
        // $email_data['email'] = 'bamitcodebrew@gmail.com';
        dispatch(new \App\Jobs\SendOrderSuccessEmailJob($email_data))->onQueue('verify_email');
    }


    /**
     * Change the status of order
     *
     * @param  int  $id
     * @return \Illuminate\Http\Response
     */
    public function createDispatchRequest(Request $request, $domain = '')
    {

        DB::beginTransaction();
        $client_preferences = ClientPreference::first();
        try {
            $timezone = Auth::user()->timezone;
            $currentOrderStatus = OrderVendor::where(['vendor_id' => $request->vendor_id, 'order_id' => $request->order_id])->first();
            $vendor_dispatch_status = VendorOrderDispatcherStatus::where(['vendor_id' => $request->vendor_id, 'order_id' => $request->order_id])->first();

            if ($currentOrderStatus->order_status_option_id == 3) { //if order rejected
                return response()->json(['status' => 'error', 'message' => __('Order has already been rejected!!!')]);
            }

            if (isset($vendor_dispatch_status) && !empty($vendor_dispatch_status)) { //if alredery dispatch request done
                return response()->json(['status' => 'error', 'message' => __('Order has already been generated in dispatcher')]);
            }


            if (!$vendor_dispatch_status) {
                $order_dispatch = $this->checkIfanyProductLastMileon($request);
                if ($order_dispatch && $order_dispatch == 1)
                    $stats = $this->insertInVendorOrderDispatchStatus($request);
                DB::commit();
                return response()->json([
                    'status' => 'success',
                    'message' => __('Dispatch Request Created.')
                ]);
            } else {
                return response()->json([
                    'status' => 'error',
                    'message' => __('Try again later.')
                ]);
            }
        } catch (\Exception $e) {
            DB::rollback();
            return response()->json([
                'status' => 'error',
                'message' => $e->getMessage()
            ]);
        }
    }



    public function formattedOrderETA($minutes, $order_vendor_created_at, $scheduleTime = '', $user = '')
    {
        $d = floor($minutes / 1440);
        $h = floor(($minutes - $d * 1440) / 60);
        $m = $minutes - ($d * 1440) - ($h * 60);

        if (isset($user) && !empty($user))
            $user =  $user;
        else
            $user = Auth::user();

        $timezone = $user->timezone;
        $preferences = ClientPreference::select('date_format', 'time_format')->where('id', '>', 0)->first();
        $date_format = $preferences->date_format;
        $time_format = $preferences->time_format;

        if ($scheduleTime != '') {
            $datetime = Carbon::parse($scheduleTime)->addMinutes($minutes);
        } else {
            $datetime = Carbon::parse($order_vendor_created_at)->addMinutes($minutes);
        }
        if (Carbon::parse($datetime)->isToday()) {
            if ($time_format == '12') {
                $time_format = 'hh:mm A';
            } else {
                $time_format = 'HH:mm';
            }
        }
        $datetime = dateTimeInUserTimeZone($datetime, $timezone);
        return $datetime;
    }


    /**
     * edit the order.
     *
     * @param  \App\Models\Order  $order
     * @return \Illuminate\Http\Response
     */

    public function getOrderDetailEdit($domain = '', $order_id, $vendor_id)
    {
        $langId = Session::has('adminLanguage') ? Session::get('adminLanguage') : 1;
        $clientCurrency = ClientCurrency::where('is_primary', 1)->first();
        $vendor_order_status_option_ids = [];
        $vendor_order_status_created_dates = [];
        $order = Order::with(array(
            'vendors' => function ($query) use ($vendor_id) {
                $query->where('vendor_id', $vendor_id);
            },
            'vendors.products.prescription' => function ($query) use ($vendor_id, $order_id) {
                $query->where('vendor_id', $vendor_id)->where('order_id', $order_id);
            },
            'vendors.products' => function ($query) use ($vendor_id) {
                $query->where('vendor_id', $vendor_id);
            },
            'vendors.products.addon',
            'vendors.products.addon.set',
            'vendors.products.addon.option',
            'vendors.products.addon.option.translation' => function ($q) use ($langId) {
                $q->select('addon_option_translations.id', 'addon_option_translations.addon_opt_id', 'addon_option_translations.title', 'addon_option_translations.language_id');
                $q->where('addon_option_translations.language_id', $langId);
                $q->groupBy('addon_option_translations.addon_opt_id', 'addon_option_translations.language_id');
            },
            'vendors.dineInTable.translations' => function ($qry) use ($langId) {
                $qry->where('language_id', $langId);
            },
            'vendors.dineInTable.category'
        ))->findOrFail($order_id);
        foreach ($order->vendors as $key => $vendor) {
            foreach ($vendor->products as $key => $product) {
                $product->image_path  = $product->media->first() ? $product->media->first()->image->path : '';
                $divider = (empty($product->doller_compare) || $product->doller_compare < 0) ? 1 : $product->doller_compare;
                $total_amount = $product->quantity * $product->price;
                foreach ($product->addon as $ck => $addons) {
                    $opt_price_in_currency = $addons->option->price;
                    $opt_price_in_doller_compare = $addons->option->price;
                    if ($clientCurrency) {
                        $opt_price_in_currency = $addons->option->price / $divider;
                        $opt_price_in_doller_compare = $opt_price_in_currency * $clientCurrency->doller_compare;
                    }
                    $opt_quantity_price = decimal_format($opt_price_in_doller_compare * $product->quantity);
                    $addons->option->translation_title = ($addons->option->translation->isNotEmpty()) ? $addons->option->translation->first()->title : '';
                    $addons->option->price_in_cart = $addons->option->price;
                    $addons->option->price = decimal_format($opt_price_in_currency);
                    $addons->option->multiplier = ($clientCurrency) ? $clientCurrency->doller_compare : 1;
                    $addons->option->quantity_price = $opt_quantity_price;
                    $total_amount = $total_amount + $opt_quantity_price;
                }
                $product->total_amount = $total_amount;
            }
            if ($vendor->dineInTable) {
                $vendor->dineInTableName = $vendor->dineInTable->translations->first() ? $vendor->dineInTable->translations->first()->name : '';
                $vendor->dineInTableCapacity = $vendor->dineInTable->seating_number;
                $vendor->dineInTableCategory = $vendor->dineInTable->category->title; //$vendor->dineInTable->category->first() ? $vendor->dineInTable->category->first()->title : '';
            }
        }
        // dd($order->toArray());
        $luxury_option_name = '';
        if ($order->luxury_option_id > 0) {
            $luxury_option = LuxuryOption::where('id', $order->luxury_option_id)->first();
            if ($luxury_option->title == 'takeaway') {
                $luxury_option_name = $this->getNomenclatureName('Takeaway', $langId, false);
            } elseif ($luxury_option->title == 'dine_in') {
                $luxury_option_name = $this->getNomenclatureName('Dine-In', $langId, false);
            } elseif ($luxury_option->title == 'on_demand') {
                $luxury_option_name = $this->getNomenclatureName('Services', $langId, false);
            } else {
                //$luxury_option_name = 'Delivery';
                $luxury_option_name = $this->getNomenclatureName($luxury_option->title, $langId, false);
            }
        }
        $order->luxury_option_name = $luxury_option_name;
        $order_status_options = OrderStatusOption::where('type', 1)->get();
        $dispatcher_status_options = DispatcherStatusOption::with(['vendorOrderDispatcherStatus' => function ($q) use ($order_id, $vendor_id) {
            $q->where(['order_id' => $order_id, 'vendor_id' => $vendor_id]);
        }])->get();
        $vendor_order_statuses = VendorOrderStatus::where('order_id', $order_id)->where('vendor_id', $vendor_id)->get();
        foreach ($vendor_order_statuses as $vendor_order_status) {
            $vendor_order_status_created_dates[$vendor_order_status->order_status_option_id] = $vendor_order_status->created_at;
            $vendor_order_status_option_ids[] = $vendor_order_status->order_status_option_id;
        }

        $vendor_data = Vendor::where('id', $vendor_id)->first();
        return view('backend.order.edit')->with([
            'vendor_id' => $vendor_id,
            'order' => $order,
            'vendor_order_statuses' => $vendor_order_statuses,
            'vendor_order_status_option_ids' => $vendor_order_status_option_ids,
            'order_status_options' => $order_status_options,
            'dispatcher_status_options' => $dispatcher_status_options,
            'vendor_order_status_created_dates' => $vendor_order_status_created_dates,
            'clientCurrency' => $clientCurrency,
            'vendor_data' => $vendor_data
        ]);
    }
    # get product faq
    public function viewProductForm(Request $request, $domain = '', $product_id)
    {

        $faq_data =  OrderProduct::where('id', $product_id)->select('id', 'user_product_order_form')->first();
        //pr($faq_data->user_product_order_form);
        if (isset($faq_data)) {
            $Product_faq = json_decode($faq_data->user_product_order_form);
            //pr( $Product_faq );
            if ($request->ajax()) {
                return \Response::json(\View::make('backend.order.show_product_form', array('product_faqs' =>  $Product_faq))->render());
            }
        }
        return $this->errorResponse('Invalid product form ', 404);
    }

    function apiTest()
    {

        $client_preferences = ClientPreference::first();
        $orders = Order::with(['vendors.products' => function ($q) {
            $q->withoutAppends();
        }, 'vendors.status', 'orderStatusVendor', 'address', 'user', 'vendors.products.product', 'vendors.vendor', 'vendors.products.pvariant'])
            // ->where('user_id', 2)
            ->where('id', 11)
            ->get();
        $cart_details = Cart::with('cartProducts')->where('user_id', 2)->first();
        // dd($orders->toArray());
        $product_details = [];
        foreach ($orders as $key => $val) {
            // order table data
            $product_details[$key]['order_id'] = $val->id;
            $product_details[$key]['order_number'] = $val->order_number;
            $product_details[$key]['date_time'] = \Carbon\Carbon::parse($val->created_at)->toDateString();

            $product_details[$key]['customer_name'] = optional($val->user)->name;
            $product_details[$key]['customer_phone_number'] = optional($val->user)->phone_number;

            $product_details[$key]['payment_methods'] = $val->payment_option_id ?? '';
            // $product_details[$key]['profit_loss'] = '';
            if (!empty($val->vendors)) {

                foreach ($val->vendors as $inn_key => $inn_val) {
                    // order vendor table data
                    $product_details[$key]['subtotal_amount']   = $inn_val->subtotal_amount;
                    $product_details[$key]['payable_amount']    = $inn_val->payable_amount;
                    $product_details[$key]['discount_amount']   = $inn_val->discount_amount;
                    $product_details[$key]['taxable_amount']   = $inn_val->taxable_amount;
                    $product_details[$key]['order_status_option_id'] = $inn_val->order_status_option_id;
                    $product_details[$key]['order_side_vendor_id'] = $inn_val->vendor_id;

                    if (!empty($inn_val->products)) {

                        foreach ($inn_val->products as $product_key => $product_val) {
                            if (!empty($product_val->product) && !empty($product_val->product->sku)) {
                                // product table data
                                $product_details[$key]['products_list'][$product_key]['product_id'] = $product_val->product_id ?? null;
                                $product_details[$key]['products_list'][$product_key]['product_quantity'] = $product_val->quantity ?? null;
                                $product_details[$key]['products_list'][$product_key]['sku'] = $product_val->product->sku;

                                // we use model inside the loop because one product had multiple variant to fetach exact variant used sku code
                                $product_varaint = ProductVariant::where('sku', $product_val->product->sku)->first();
                                $product_details[$key]['products_list'][$product_key]['product_amount'] = $product_varaint->price ?? '0.00';
                            }
                        }
                    }
                }
            }
            // vendor or warehouse name
            if (!empty($val->vendors[$key]) && !empty($val->vendors[$key]->vendor)) {
                $product_details[$key]['warehouse'] = $val->vendors[$key]->vendor->name;
            } else {
                $product_details[$key]['warehouse'] = '';
            }
        }

        $client = new \GuzzleHttp\Client([
            'headers' => [
                'shortcode' => $client_preferences->inventory_service_key_code,
                'content-type' => 'application/json'
            ]
        ]);
        $request = $client->get('127.0.0.1:9001/api/v1/log-order', [
            'json' => ['product_details' => $product_details]
        ]);

        // Product decrement successfully
        if ($request->getStatusCode() == 200) {
        } else {
        }
    }
    function testObserver()
    {
        $order_vendor = OrderVendor::where('order_id', 47)->first();
        $order_vendor->order_status_option_id = rand();
        $order_vendor->save();
    }

    public function addExtraPrepTimeToOrder(Request $request)
    {

        $response    = $this->addBufferTime($request);
        if ($response['status'] == 'success') {
            $order = Order::find($response['order_id']);
            $this->sendDelayPushNotification($order->user_id, $order, $request);
            return response()->json([
                'status' => 'success',
                'message' => 'Time Added Success Fully.',
            ], 200);
        } else {
            return response()->json([
                'status' => 'error',
                'message' => 'Some Thing Went Wrong.',

            ], 200);
        }
    }

    public function getBlockchainOrderDetail(Request $request)
    {




        $api_domain = ClientPreferenceAdditional::where('key_name', 'blockchain_api_domain')->first();
        $from_id = ClientPreferenceAdditional::where('key_name', 'blockchain_address_id')->first();
        $client = ClientData::first();
        $data = [
            "orderID" => $request->order_id,
            "address_f" => $from_id->key_value ?? '',
            "address_short_code" => $client->code,
        ];

        $headers = [
            'Content-Type' => 'application/json',
        ];

        $order_detail = [];
        if (isset($api_domain)) {
            $apiUrl = $api_domain->key_value . '/fetchOrderDetails';
            $response = '';


            $response = Http::withHeaders($headers)->post($apiUrl, $data);

            $responseData = $response->json();
            $data = $responseData['orderData'][0];
            $order_detail = json_decode($data['orderDetail'], true);
        }

        $orderDetail = array_values($order_detail); // Get the values with numeric keys

        if (array_key_exists('24', $orderDetail)) {
            $newValue = $orderDetail['24'];
            $orderDetail = $newValue['ordervendor'];
        }



        return view('backend.order.blockchain-order-data', compact(['data', 'orderDetail']));
    }

    public function orderDocument(Request $request, $domain, $id, $vendor_id)
    {
        $orderVendor = OrderVendor::where('order_id', $id)->where('vendor_id', $vendor_id)->first();
        foreach ($request->document as $document) {
            if ($document->isValid()) {
                $filename = 'prods/' . uniqid() . '_' . $document->getClientOriginalName();
                Storage::disk('s3')->put($filename, file_get_contents($document->path()), 'public');
                if (Storage::disk('s3')->exists($filename)) {
                    $data['order_vendor_product_id'] = $orderVendor->id;
                    $data['document'] = $filename;
                    $data['file_name'] = $document->getClientOriginalName();
                    OrderDocument::create($data);
                }
            }
        }
        return redirect()->back()->with('success', 'Document Uploaded Successfully');
    }

    public function deleteDocument($domain, $id)
    {
        OrderDocument::where('id', $id)->delete();
        return redirect()->back()->with('success', 'Document Deleted Successfully');
    }

    public function sendRejectNotificationToVendor($domain, $vendorId, $orderNo)
    {
        $userIds = UserVendor::where('vendor_id', $vendorId)->pluck('user_id');
        if (count($userIds) > 0) {
            $orderNumber = Order::find($orderNo)->value('order_number');
            $devices = UserDevice::whereNotNull('device_token')->whereIn('user_id', $userIds)->pluck('device_token')->toArray();
            // push notification
            $client_preferences = ClientPreference::select('fcm_server_key', 'favicon', 'sms_provider', 'sms_key', 'sms_secret', 'sms_from')->first();
            if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                $notification_content = NotificationTemplate::where('id', 19)->first();
                if ($notification_content) {
                    $body_content = str_ireplace("{order_id}", "#" . $orderNumber, $notification_content->content);
                    $redirect_URL['type'] = 4;
                    $data = [
                        "registration_ids" => $devices,
                        "notification" => [
                            'title' => $notification_content->subject,
                            'body'  => $body_content,
                            'sound' => "default",
                            "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                            'click_action' => '',
                            "android_channel_id" => "default-channel-id",
                            "redirect_type" => $redirect_URL['type']
                        ],
                        "data" => [
                            'title' => $notification_content->subject,
                            'body'  => $body_content,
                            "type" => "order_status_change",
                            "order_id" => $orderNo,
                            "vendor_id" => $vendorId ?? '',
                            "order_status" => 3,
                            "redirect_type" => $redirect_URL['type']
                        ],
                        "priority" => "high"
                    ];
                    sendFcmCurlRequest($data);
                }
            }
            // for sms
            $users = User::whereIN('id', $userIds)->select('id', 'phone_number', 'dial_code');
            foreach ($users as $user) {
                $keyData = ['{order_number}' => $orderNumberr ?? ''];
                $body = sendSmsTemplate('order-canceled-vendor', $keyData);
                if (!empty($client_preferences->sms_provider)) {
                    if ($user->dial_code == "971") {
                        $to = '+' . $user->dial_code . "0" . $user->phone_number;
                    } else {
                        $to = '+' . $user->dial_code . $user->phone_number;
                    }
                    $send = $this->sendSmsNew($client_preferences, $client_preferences->sms_key, $client_preferences->sms_secret, $client_preferences->sms_from, $to, $body);
                }
            }
        }
    }
}
