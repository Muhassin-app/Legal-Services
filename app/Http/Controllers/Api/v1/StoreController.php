<?php

namespace App\Http\Controllers\Api\v1;

use Carbon\Carbon;
use Illuminate\Http\Request;
use Validator;
use GuzzleHttp\Client as GCLIENT;
use App\Http\Traits\ApiResponser;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Str;
use App\Http\Controllers\Api\v1\BaseController;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Storage;
use Illuminate\Pagination\Paginator;
use App\Models\{User, Vendor, Order, UserVendor, ProductAvailability, PaymentOption, VendorCategory, Product, VendorOrderStatus, OrderStatusOption, ClientCurrency, Category_translation, OrderVendor, LuxuryOption, ClientLanguage, ProductCategory, ProductVariant, ProductTranslation, Variant, Brand, AddonSet, TaxCategory, ClientPreference, Celebrity, ProductImage, ProductAddon, ProductUpSell, ProductCrossSell, ProductRelated, ProductCelebrity, ProductTag, VendorMedia, ProductVariantSet, CartProduct, Category, OrderQrcodeLinks, ProductVariantImage, RescheduleOrder, UserWishlist, ProductAttribute, Attribute, Client, ClientDocument, Notification, NotificationTemplate, OrderProduct, Type, UserDevice, VendorFacilty, VendorMinAmount};
use Carbon\CarbonPeriod;
use Log;
use PhpParser\JsonDecoder;

class StoreController extends BaseController
{
	use ApiResponser;
	private $folderName = 'prods';

	public function getMyStoreProductList(Request $request)
	{
		try {
			$category_list = [];
			$user = Auth::user();
			$langId = $user->language;
			$is_selected_vendor_id = 0;
			$paginate = $request->has('limit') ? $request->limit : 12;
			$client_currency_detail = ClientCurrency::where('currency_id', $user->currency)->first();
			$selected_vendor_id = $request->has('selected_vendor_id') ? $request->selected_vendor_id : '';
			$selected_category_id = $request->has('selected_category_id') ? $request->selected_category_id : '';
			$user_vendor_ids = UserVendor::where('user_id', $user->id)->pluck('vendor_id');
			if ($user_vendor_ids) {
				$is_selected_vendor_id = $selected_vendor_id ? $selected_vendor_id : $user_vendor_ids->first();
			}
			$vendor_list = Vendor::whereIn('id', $user_vendor_ids)->get(['id', 'name', 'logo']);
			foreach ($vendor_list as $vendor) {
				$vendor->is_selected = ($is_selected_vendor_id == $vendor->id) ? true : false;
			}
			$vendor_categories = VendorCategory::where('vendor_id', $is_selected_vendor_id)
				->whereHas('category', function ($query) {
					$query->whereIn('type_id', [1]);
				})->where('status', 1)->get('category_id');
			$vendor_category_id = 0;
			if ($vendor_categories->count()) {
				$vendor_category_id = $vendor_categories->first()->category_id;
			}
			$is_selected_category_id = $selected_category_id ? $selected_category_id : $vendor_category_id;
			foreach ($vendor_categories as $vendor_category) {
				$Category_translation = Category_translation::where('category_id', $vendor_category->category->id)->where('language_id', $langId)->first();
				if (!$Category_translation) {
					$Category_translation = Category_translation::where('category_id', $vendor_category->category->id)->first();
				}
				$category_list[] = array(
					'id' => $vendor_category->category->id,
					'name' => $Category_translation ? $Category_translation->name : $vendor_category->category->slug,
					'type_id' => $vendor_category->category->type_id,
					'is_selected' => $is_selected_category_id == $vendor_category->category_id ? true : false
				);
			}
			$products = Product::select('id', 'sku', 'url_slug','is_live','category_id','calories')->has('vendor')
						->with(['media.image', 'translation' => function($q) use($langId){
                        	$q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                    	},'variant' => function($q) use($langId){
                            $q->select('sku', 'product_id', 'quantity', 'price','markup_price', 'barcode');
                            $q->groupBy('product_id');
                    	},
                    ])->where('category_id', $is_selected_category_id);
			if($selected_vendor_id > 0){
				$products = $products->where('vendor_id', $selected_vendor_id);
			}
			$products = $products->where('is_live', 1)->paginate($paginate);
			//$products = $products->paginate($paginate);
			foreach ($products as $product) {
				foreach ($product->variant as $k => $v) {
					$product->variant[$k]->multiplier = $client_currency_detail->doller_compare;
				}
			}
			$data = ['vendor_list' => $vendor_list, 'category_list' => $category_list, 'products' => $products];
			return $this->successResponse($data, '', 200);
		} catch (Exception $e) {

		}
	}
	public function getMyStoreDetails(Request $request)
	{
		try {
			$user = Auth::user();
			$is_selected_vendor_id = 0;
			$paginate = $request->has('limit') ? $request->limit : 12;
			$selected_vendor_id = $request->has('selected_vendor_id') ? $request->selected_vendor_id : '';
			$user_vendor_ids = UserVendor::where('user_id', $user->id)->pluck('vendor_id');
			if ($user_vendor_ids) {
				$is_selected_vendor_id = $selected_vendor_id ? $selected_vendor_id : $user_vendor_ids->first();
			}
			$order_list = Order::with('orderStatusVendor')
				->whereHas('vendors', function ($query) use ($is_selected_vendor_id) {
					$query->where('vendor_id', $is_selected_vendor_id);
				})
				->where(function ($q1) {
					$q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1, 38]);
					$q1->orWhere(function ($q2) {
						$q2->whereIn('payment_option_id', [1, 38]);
					});
				})
				->orderBy('id', 'DESC')->paginate($paginate);
			foreach ($order_list as $order) {
				$order_status = [];
				$product_details = [];
				$order_item_count = 0;
				$order->user_name = $order->user->name;
				$order->user_image = $order->user->image;
				$order->date_time = dateTimeInUserTimeZone($order->created_at, $user->timezone);
				$order->date_time = date("d-M-Y h:i A", strtotime($order->date_time));
				$order->payment_option_title = __($order->paymentOption->title);
				foreach ($order->vendors as $vendor) {
					$vendor_order_status = VendorOrderStatus::where('order_id', $order->id)->where('vendor_id', $is_selected_vendor_id)->orderBy('id', 'DESC')->first();
					if ($vendor_order_status) {
						$order_status_option_id = $vendor_order_status->order_status_option_id;
						$current_status = OrderStatusOption::select('id', 'title')->find($order_status_option_id);
						if ($order_status_option_id == 2) {
							$upcoming_status = OrderStatusOption::select('id', 'title')->where('id', '>', 3)->first();
						} elseif ($order_status_option_id == 3) {
							$upcoming_status = null;
						} elseif ($order_status_option_id == 6) {
							$upcoming_status = null;
						} else {
							$upcoming_status = OrderStatusOption::select('id', 'title')->where('id', '>', $order_status_option_id)->first();
						}
						$order->order_status = [
							'current_status' => $current_status,
							'upcoming_status' => $upcoming_status,
						];
					}
				}
				foreach ($order->products as $product) {
					$order_item_count += $product->quantity;
					if ($is_selected_vendor_id == $product->vendor_id) {
						$product_details[] = array(
							'image_path' => $product->media->first() ? $product->media->first()->image->path : $product->image,
							'price' => $product->price,
							'qty' => $product->quantity,
							'category_type' => $product->product->category->categoryDetail->type->title ?? '',
							'product_id' => $product->product_id,
	    				    'title' => isset($product->translation)?$product->translation->title:$product->product_name
	    				,
	    				);
    				}
				}
				if (!empty($order->scheduled_date_time)) {
					$order->scheduled_date_time = dateTimeInUserTimeZone($order->scheduled_date_time, $user->timezone);
				}
				$luxury_option_name = '';
				if ($order->luxury_option_id > 0) {
					$luxury_option = LuxuryOption::where('id', $order->luxury_option_id)->first();
					if ($luxury_option->title == 'takeaway') {
						$luxury_option_name = $this->getNomenclatureName('Takeaway', $user->language, false);
					} elseif ($luxury_option->title == 'dine_in') {
						$luxury_option_name = $this->getNomenclatureName('Dine-In', $user->language, false);
					} elseif ($luxury_option->title == 'on_demand') {
						$luxury_option_name = $this->getNomenclatureName('Services', $user->language, false);
					} else {
						//$luxury_option_name = 'Delivery';
						$luxury_option_name = getNomenclatureName($luxury_option->title);
					}
				}
				$order->luxury_option_name = $luxury_option_name;
				$order->product_details = $product_details;
				$order->item_count = $order_item_count;
				unset($order->user);
				unset($order->products);
				unset($order->paymentOption);
				unset($order->payment_option_id);
			}
			$vendor_list = Vendor::where('status', 1)->whereIn('id', $user_vendor_ids)->get(['id', 'name', 'logo']);
			foreach ($vendor_list as $vendor) {
				$vendor->is_selected = ($is_selected_vendor_id == $vendor->id) ? true : false;
			}
			$data = ['order_list' => $order_list, 'vendor_list' => $vendor_list];
			return $this->successResponse($data, '', 200);
		} catch (Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	public function getMyStoreVendors(Request $request)
	{
		try {
			$user = Auth::user();
			$limit = $request->has('limit') ? $request->limit : 12;
			$page = $request->has('page') ? $request->page : 1;
			$user_vendor_ids = UserVendor::where('user_id', $user->id)->pluck('vendor_id')->toArray();
			$vendors_list = Vendor::where('status', 1)->whereIn('id', $user_vendor_ids)->select('id', 'name', 'logo')->paginate($limit, $page);
			return $this->successResponse($vendors_list, '', 200);
		} catch (Exception $e) {
			return $this->errorResponse('Server Error', $e->getCode());
		}
	}

	public function getMyStoreVendorDashboard(Request $request, $vendor_id)
	{
		try {
			$user = Auth::user();
			$orders = Order::where(function ($q1) {
				$q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1, 38]);
				$q1->orWhere(function ($q2) {
					$q2->whereIn('payment_option_id', [1, 38]);
				});
			});

			$pending_orders = clone $orders;
			$active_orders = clone $orders;
			$completed_orders = clone $orders;
			$cancelled_orders = clone $orders;

			$pending_orders = $pending_orders->whereHas('vendors', function ($query) use ($vendor_id) {
				$query->where('vendor_id', $vendor_id)->where('order_status_option_id', 1);
			})->count();

			$active_orders = $active_orders->whereHas('vendors', function ($query) use ($vendor_id) {
				$query->where('vendor_id', $vendor_id)->whereIn('order_status_option_id', [2, 4, 5]);
			})->count();

			$completed_orders = $completed_orders->whereHas('vendors', function ($query) use ($vendor_id) {
				$query->where('vendor_id', $vendor_id)->where('order_status_option_id', 6);
			})->count();

			$cancelled_orders = $cancelled_orders->whereHas('vendors', function ($query) use ($vendor_id) {
				$query->where('vendor_id', $vendor_id)->where('order_status_option_id', 3);
			})->count();

			$data = ['pending_orders' => $pending_orders, 'active_orders' => $active_orders, 'completed_orders' => $completed_orders, 'cancelled_orders' => $cancelled_orders];
			return $this->successResponse($data, '', 200);
		} catch (Exception $e) {
			return $this->errorResponse('Server Error', $e->getCode());
		}
	}

	public function clearBagOrders(Request $request, $qrcode, $order_number = '')
	{
		try {
			$orderIds = OrderQrcodeLinks::where('code', $qrcode);
			if ($order_number) {
				$orderIds = $orderIds->where('order_id', $order_number);
			}
			if (empty($orderIds->get()->toArray())) {
				return $this->errorResponse(__('No order is found.'), 400);
			}
			$orderIds->delete();
			return $this->successResponse(__('Order is removed.'));
		}catch(\Exception $e)
		{
		}
	}

	public function getMyStoreVendorBagOrders(Request $request, $qrcode)
	{
		try {
			$orderIds = OrderQrcodeLinks::where('code', $request->qr_code ?? $qrcode)->pluck('order_id')->toArray();
			if (empty($orderIds)) {
				return $this->successResponse([], '', 200);
			}
			//dd($orderIds);
			$user = Auth::user();
			$langId = $user->language;
			$limit = $request->has('limit') ? $request->limit : 12;
			$page = $request->has('page') ? $request->page : 1;
			$type = $request->has('type') ? $request->type : '';
			if ($type == '') {
				$this->errorResponse(__('Missing Required parameters'), 400);
			}
			$status_ids = [];
			if ($type == 'pending') {
				$status_ids = [1];
			} elseif ($type == 'active') {
				$status_ids = [2, 4, 5];
			} elseif ($type == 'cancelled') {
				$status_ids = [3];
			} elseif ($type == 'completed') {
				$status_ids = [6];
			}
			$order_list = Order::select('*')->with([
				'vendors',
				'user',
				'orderStatusVendor',
				'products',
				'products.product.categoryName' => function ($q) use ($langId) {
					$q->select('category_id', 'name');
					$q->where('language_id', $langId);
				}
			])
				->whereHas('vendors', function ($query) use ($status_ids) {
					$query->whereIn('order_status_option_id', $status_ids);
				})
				->where(function ($q1) {
					$q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1, 38]);
					$q1->orWhere(function ($q2) {
						$q2->whereIn('payment_option_id', [1, 38]);
					});
				})->whereIn('id', $orderIds)
				->orderBy('id', 'DESC')->paginate($limit, $page);
			//dd($order_list);
			foreach ($order_list as $order) {
				$order_status = [];
				$product_details = [];
				$order_item_count = 0;
				$order->user_name = $order->user->name;
				$order->user_image = $order->user->image;
				$order->date_time = dateTimeInUserTimeZone($order->created_at, $user->timezone);
				$order->date_time = date("d-M-Y h:i A", strtotime($order->date_time));
				// set payment option dynamic name
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

				$order->payment_option_title = __($order->paymentOption->title);
				foreach ($order->vendors as $vendor) {
					$vendor_order_status = VendorOrderStatus::where('order_id', $order->id)->orderBy('id', 'DESC')->first();
					if ($vendor_order_status) {
						$order_status_option_id = $vendor_order_status->order_status_option_id;
						$current_status = OrderStatusOption::select('id', 'title')->find($order_status_option_id);
						if ($order_status_option_id == 2) {
							$upcoming_status = OrderStatusOption::select('id', 'title')->where('id', '>', 3)->first();
						} elseif ($order_status_option_id == 3) {
							$upcoming_status = null;
						} elseif ($order_status_option_id == 6) {
							$upcoming_status = null;
						} else {
							$upcoming_status = OrderStatusOption::select('id', 'title')->where('id', '>', $order_status_option_id)->first();
						}
						$order->order_status = [
							'current_status' => $current_status,
							'upcoming_status' => $upcoming_status,
						];
					}
				}
				foreach ($order->products as $product) {
					$order_item_count += $product->quantity;
					if ($product->vendor_id) {
						$product_details[] = array(
							'image_path' => $product->media->first() ? $product->media->first()->image->path : $product->image,
							'price' => $product->price,
							'qty' => $product->quantity,
							'category_type' => $product->product->category->categoryDetail->type->title ?? '',
							'product_id' => $product->product_id,
	    				    'title' => isset($product->translation)?$product->translation->title:$product->product_name,
							'category_name' => (!empty($product->product->categoryName->name))?$product->product->categoryName->name:''
	    				);
    				}
				}
				if (!empty($order->scheduled_date_time)) {
					$order->scheduled_date_time = dateTimeInUserTimeZone($order->scheduled_date_time, $user->timezone);
				}
				$luxury_option_name = '';
				if ($order->luxury_option_id > 0) {
					$luxury_option = LuxuryOption::where('id', $order->luxury_option_id)->first();
					if ($luxury_option->title == 'takeaway') {
						$luxury_option_name = $this->getNomenclatureName('Takeaway', $user->language, false);
					} elseif ($luxury_option->title == 'dine_in') {
						$luxury_option_name = $this->getNomenclatureName('Dine-In', $user->language, false);
					} elseif ($luxury_option->title == 'on_demand') {
						$luxury_option_name = $this->getNomenclatureName('Services', $user->language, false);
					} else {
						//$luxury_option_name = 'Delivery';
						$luxury_option_name = getNomenclatureName($luxury_option->title);
					}
				}
				$order->luxury_option_name = $luxury_option_name;
				$order->product_details = $product_details;
				$order->item_count = $order_item_count;


				unset($order->user);
				unset($order->products);
				unset($order->paymentOption);
				unset($order->payment_option_id);
			}
			return $this->successResponse($order_list, '', 200);
		} catch (Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	public function getMyStoreVendorOrders(Request $request, $vendor_id)
	{
		try {
			$user = Auth::user();
			$langId = $user->language;
			$limit = $request->has('limit') ? $request->limit : 12;
			$page = $request->has('page') ? $request->page : 1;
			$type = $request->has('type') ? $request->type : '';
			if ($type == '') {
				$this->errorResponse(__('Missing Required parameters'), 400);
			}
			$status_ids = [];
			if ($type == 'pending') {
				$status_ids = [9,10];
			} elseif ($type == 'active') {
				$status_ids = [2, 4, 5];
			} elseif ($type == 'cancelled') {
				$status_ids = [3];
			} elseif ($type == 'completed') {
				$status_ids = [6];
			}
			$order_list = Order::select('*')->with([
				'vendors',
				'user',
				'orderStatusVendor',
				'products',
				'products.product.categoryName' => function ($q) use ($langId) {
					$q->select('category_id', 'name');
					$q->where('language_id', $langId);
				}
			])
				->whereHas('vendors', function ($query) use ($vendor_id, $status_ids) {
					$query->where('vendor_id', $vendor_id)->whereIn('order_status_option_id', $status_ids);
				})
				->where(function ($q1) {
					$q1->where('payment_status', 1)->whereNotIn('payment_option_id', [1, 38]);
					$q1->orWhere(function ($q2) {
						$q2->whereIn('payment_option_id', [1, 38]);
					});
				})
				->where('lawyer_id',$user->id) //lawyer's order filter out 
				->orderBy('id', 'DESC')->paginate($limit, $page);

				$order_list->getCollection()->transform(function ($order) {
					$order->formatted_schedule_date = $order->order_schedule_date;
					$order->is_appointment = ($order->formatted_schedule_date && $order->scheduled_slot) ? 1 : 0;
					$order->formatted_schedule_date =  $order->is_appointment ? $order->formatted_schedule_date : null;
					return $order;
				});
			foreach ($order_list as $order) {
				// fetch order client documents
				$order->client_documents = ClientDocument::where('order_id',$order->id)->get();
				$order_status = [];
				$product_details = [];
				$order_item_count = 0;
				$order->user_name = $order->user->name;
				$order->user_image = $order->user->image;
				$order->date_time = dateTimeInUserTimeZone($order->created_at, $user->timezone);
				$order->date_time = date("d-M-Y h:i A", strtotime($order->date_time));
				// set payment option dynamic name

				if ($order->paymentOption->code == 'stripe') {
					$order->paymentOption->title = __('Credit/Debit Card (Stripe)');
				} elseif ($order->paymentOption->code == 'kongapay') {
					$order->paymentOption->title = 'Pay Now';
				} elseif (@$order->paymentOption->code == 'mvodafone') {
					$order->paymentOption->title = 'Vodafone M-PAiSA';
				} elseif ($order->paymentOption->code == 'mobbex') {
					$order->paymentOption->title = __('Mobbex');
				} elseif ($order->paymentOption->code == 'offline_manual') {
					$json = json_decode($order->paymentOption->credentials);
					$order->paymentOption->title = $json->manule_payment_title;
				}
				$order->paymentOption->title = __($order->paymentOption->title);

				$order->payment_option_title = __($order->paymentOption->title);
				$total_markup_Price = 0;
				foreach ($order->vendors as $vendor) {
					$vendor_order_status = VendorOrderStatus::where('order_id', $order->id)->where('vendor_id', $vendor_id)->orderBy('id', 'DESC')->first();
					if ($vendor_order_status) {
						$order_status_option_id = $vendor_order_status->order_status_option_id;
						$current_status = OrderStatusOption::select('id', 'title','arabic_name')->find($order_status_option_id);
						if ($order_status_option_id == 2) {
							$upcoming_status = OrderStatusOption::select('id', 'title','arabic_name')->where('id', '>', 3)->first();
						} elseif ($order_status_option_id == 3) {
							$upcoming_status = null;
						} elseif ($order_status_option_id == 6) {
							$upcoming_status = null;
						} else {
							$upcoming_status = OrderStatusOption::select('id', 'title','arabic_name')->where('id', '>', $order_status_option_id)->first();
						}
						if ($request->type == 'cancelled') {
							if (!empty($current_status)) {
								$current_status->title = 'Rejected';
							}
						
							if (!empty($upcoming_status)) {
								$upcoming_status->title = 'Rejected';
							}
						}
						if($request->header('language') == 8){
							if ($request->header('language') == 8) {
								$current_status = is_object($current_status) ? $current_status : (object)[];
								$upcoming_status = is_object($upcoming_status) ? $upcoming_status : (object)[];
							
								$current_status->title = isset($current_status->arabic_name) ? $current_status->arabic_name : '';
								$upcoming_status->title = isset($upcoming_status->arabic_name) ? $upcoming_status->arabic_name : '';
								
								if ($request->type == 'cancelled') {
									if (!empty($current_status)) {
										$current_status->title = 'مرفوض';
									}
								
									if (!empty($upcoming_status)) {
										$upcoming_status->title = 'مرفوض';
									}
								}
							}
						}
						$order->order_status = [
							'current_status' => $current_status,
							'upcoming_status' => $upcoming_status,
						];
					}
					if (auth()->user()->is_admin) {
						$vendor->subtotal_amount = $vendor->subtotal_amount - $vendor->total_markup_price;
					} else {
						$vendor->subtotal_amount = $vendor->subtotal_amount - $vendor->total_markup_price;
					}
				}
				foreach ($order->products as $product) {
					$order_item_count += $product->quantity;
					if ($vendor_id == $product->vendor_id) {
						$product_details[] = array(
							'image_path' => $product->media->first() ? $product->media->first()->image->path : $product->image,
							'price' => $product->price,
							'qty' => $product->quantity,
							'category_type' => $product->product->category->categoryDetail->type->title ?? '',
							'product_id' => $product->product_id,
	    				    'title' => isset($product->translation)?$product->translation->title:$product->product_name,
							'category_name' => (!empty($product->product->categoryName->name))?$product->product->categoryName->name:''
	    				);

						$total_markup_Price += $product->markup_price;
					}
				}
				if (!empty($order->scheduled_date_time)) {
					$order->scheduled_date_time = dateTimeInUserTimeZone($order->scheduled_date_time, $user->timezone);
				}
				$luxury_option_name = '';
				if ($order->luxury_option_id > 0) {
					$luxury_option = LuxuryOption::where('id', $order->luxury_option_id)->first();
					if ($luxury_option->title == 'takeaway') {
						$luxury_option_name = $this->getNomenclatureName('Takeaway', $user->language, false);
					} elseif ($luxury_option->title == 'dine_in') {
						$luxury_option_name = $this->getNomenclatureName('Dine-In', $user->language, false);
					} elseif ($luxury_option->title == 'on_demand') {
						$luxury_option_name = $this->getNomenclatureName('Services', $user->language, false);
					} else {
						//$luxury_option_name = 'Delivery';
						$luxury_option_name = $this->getNomenclatureName($luxury_option->title, $user->language, false);
					}
				}

				if($langId == 8){
					$order->luxury_option_name = 'موعد';
				}else{
					$order->luxury_option_name = $luxury_option_name;
				}
				$order->product_details = $product_details;
				$order->item_count = $order_item_count;

				if (auth()->user()->is_admin) {
					$order->total_amount = $order->total_amount - $total_markup_Price;
					$order->payable_amount = $order->payable_amount - $total_markup_Price;
				} else {
					$order->total_amount = $order->total_amount;
					$order->payable_amount = $order->payable_amount;
				}
				unset($order->user);
				unset($order->products);
				unset($order->paymentOption);
				unset($order->payment_option_id);
			}
			return $this->successResponse($order_list, '', 200);
		} catch (\Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	public function getMyStoreRevenueDetails(Request $request)
	{
		$dates = [];
		$sales = [];
		$revenue = [];
		$type = $request->type;
		$vendor_id = $request->vendor_id;
		$monthly_sales_query = OrderVendor::select(\DB::raw('sum(payable_amount) as y'), \DB::raw('count(*) as z'), \DB::raw('date(created_at) as x'));
		switch ($type) {
			case 'monthly':
				$created_at = $monthly_sales_query->whereRaw('MONTH(created_at) = ?', [date('m')]);
				break;
			case 'weekly':
				Carbon::setWeekStartsAt(Carbon::SUNDAY);
				$created_at = $monthly_sales_query->whereBetween('created_at', [Carbon::now()->startOfWeek(), Carbon::now()->endOfWeek()]);
				break;
			case 'yearly':
				$created_at = $monthly_sales_query->whereRaw('YEAR(created_at) = ?', [date('Y')]);
				break;
			default:
				$created_at = $monthly_sales_query->whereRaw('MONTH(created_at) = ?', [date('m')]);
				break;
		}
		$monthlysales = $monthly_sales_query->where('vendor_id', $vendor_id)->groupBy('x')->get();
		foreach ($monthlysales as $monthly) {
			$dates[] = $monthly->x;
			$sales[] = $monthly->z;
			$revenue[] = $monthly->y;
		}
		$data = ['dates' => $dates, 'revenue' => $revenue, 'sales' => $sales];
		return $this->successResponse($data, '', 200);
	}

	public function my_pending_orders(Request $request)
	{
		try {
			$user = Auth::user();
			$paginate = $request->has('limit') ? $request->limit : 12;
			$order_list = Order::with(['orderStatusVendor', 'vendors.products', 'vendors.status'])->select('id', 'order_number', 'payable_amount', 'payment_option_id', 'user_id');
			if ($user->is_superadmin == 1) {
				$order_list = $order_list->whereHas('vendors', function ($query) {
					$query->where('order_status_option_id', 1);
				})->with('vendors', function ($query) {
					$query->where('order_status_option_id', 1);
				});
			} else {
				$user_vendor_ids = UserVendor::where('user_id', $user->id)->pluck('vendor_id');
				$order_list = $order_list->whereHas('vendors', function ($query) use ($user_vendor_ids) {
					$query->where('order_status_option_id', 1);
					$query->whereIn('vendor_id', $user_vendor_ids);
				})->with('vendors', function ($query) {
					$query->where('order_status_option_id', 1);
				});
			}
			$order_list = $order_list->orderBy('id', 'DESC')->paginate($paginate);
			foreach ($order_list as $order) {
				$order_status = [];
				$product_details = [];
				$order_item_count = 0;
				$order->user_name = $order->user->name;
				$order->user_image = $order->user->image;
				$order->date_time = dateTimeInUserTimeZone($order->created_at, $user->timezone);
				$order->payment_option_title = $order->paymentOption->title??'';
				if (!empty($order->scheduled_date_time)) {
					$order->scheduled_date_time = date('d-m-Y h:i A',strtotime(dateTimeInUserTimeZone($order->scheduled_date_time, $user->timezone)));
				}
				if(!empty($order->scheduled_slot) ){
					$slot_time = explode("-",$order->scheduled_slot);
					$start_time = $slot_time[0];
					$end_time = !empty($slot_time[1]) ? $slot_time[1]: $slot_time[0];
					$order->schedule_slot =date('d-m-Y h:i A',strtotime( date('Y-m-d',strtotime($order->scheduled_date_time)). " " . $start_time)) . ' - ' . date('h:i A',strtotime($end_time));
				}
				foreach ($order->vendors as $vendor) {
					$vendor_order_status = VendorOrderStatus::where('order_id', $order->id)->where('vendor_id', $vendor->vendor_id)->orderBy('id', 'DESC')->first();
					if ($vendor_order_status) {
						$order_status_option_id = $vendor_order_status->order_status_option_id;
						$current_status = OrderStatusOption::select('id', 'title')->find($order_status_option_id);
						$upcoming_status = OrderStatusOption::select('id', 'title')->where('id', '>', $order_status_option_id)->first();
						$order->order_status = [
							'current_status' => $current_status,
							'upcoming_status' => $upcoming_status,
						];
					}
				}
				foreach ($order->products as $product) {
					$order_item_count += $product->quantity;
					$product_details[] = array(
						'name' => $product->product_name,
						'image_path' => $product->media->first() ? $product->media->first()->image->path : $product->image,
						'price' => $product->price,
						'qty' => $product->quantity,
					);
				}
				$order->product_details = $product_details;
				$order->item_count = $order_item_count;
				unset($order->user);
				unset($order->products);
				unset($order->paymentOption);
				unset($order->payment_option_id);
			}
			$data = ['order_list' => $order_list];
			return $this->successResponse($data, '', 200);
		} catch (Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	public function VendorCategory(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'vendor_id' => 'required'
			]);

			if ($validator->fails()) {
				return $this->errorResponse($validator->errors()->first(), 422);
			}
			$user = Auth::user();
			$vendorid = $request->vendor_id;
			$product_categories = VendorCategory::with([
				'category',
				'category.translation' => function ($q) {
					$q->select('category_translations.name', 'category_translations.meta_title', 'category_translations.meta_description', 'category_translations.meta_keywords', 'category_translations.category_id')
					;
				}
			])->where('status', 1)->where('vendor_id', $vendorid)->groupBy('category_id')->get();
			if (count($product_categories) == 0) {
				return $this->errorResponse('No category found', 422);
			}
			$p_categories = collect();
			$product_categories_hierarchy = '';
			if ($product_categories) {
				foreach ($product_categories as $pc) {
					$p_categories->push($pc->category);
				}
				$product_categories_build = $this->buildTree(array_filter($p_categories->toArray()));

				$product_categories_hierarchy = $this->printCategoryOptionsHeirarchy($product_categories_build);
				foreach ($product_categories_hierarchy as $k => $cat) {
					$myArr = array(1, 3, 7, 8, 9);
					if (isset($cat['type_id']) && !in_array($cat['type_id'], $myArr)) {
						unset($product_categories_hierarchy[$k]);
					}
				}
			}
			$output = [];
			foreach ($product_categories_hierarchy as $singlecat) {
				$output[] = $singlecat;
			}
			$data = $output;
			return $this->successResponse($data, '', 200);
		} catch (Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	public function addProduct(Request $request)
	{



		//return $product = Product::with('brand', 'variant.set', 'variant.vimage.pimage.image', 'primary', 'category.cat', 'variantSet', 'vatoptions', 'addOn', 'media.image', 'related', 'upSell', 'crossSell', 'celebrities')->where('id', 232)->firstOrFail();
		try {
			$validator = Validator::make($request->all(), [
				'sku' => 'required|unique:products',
				'url_slug' => 'required|unique:products',
				'category_id' => 'required',
				'product_name' => 'required',
				'vendor_id' => 'required'
			]);

			if ($validator->fails()) {

				return $this->errorResponse($validator->errors()->first(), 422);
			}

			$user = Auth::user();

			$product = new Product();
			$product->sku = $request->sku;
			$product->url_slug = $request->url_slug;
			$product->title = $request->product_name;
			$product->category_id = $request->category_id;
			$product->type_id = 1;
			$product->vendor_id = $request->vendor_id;
			$client_lang = ClientLanguage::where('is_primary', 1)->first();
			if (!$client_lang) {
				$client_lang = ClientLanguage::where('is_active', 1)->first();
			}
			$product->save();
			if ($product->id > 0) {
				$datatrans[] = [
					'title' => $request->product_name ?? null,
					'body_html' => '',
					'meta_title' => '',
					'meta_keyword' => '',
					'meta_description' => '',
					'product_id' => $product->id,
					'language_id' => $client_lang->language_id
				];
				$product_category = new ProductCategory();
				$product_category->product_id = $product->id;
				$product_category->category_id = $request->category_id;
				$product_category->save();
				$proVariant = new ProductVariant();
				$proVariant->sku = $request->sku;
				$proVariant->product_id = $product->id;
				$proVariant->barcode = $this->generateBarcodeNumber();
				$proVariant->save();
				ProductTranslation::insert($datatrans);
				//$product_detail = Product::with('brand', 'variant.set', 'variant.vimage.pimage.image', 'primary', 'category.cat', 'variantSet', 'vatoptions', 'addOn', 'media.image', 'related', 'upSell', 'crossSell', 'celebrities')->where('id', $product->id)->firstOrFail();
				$product_detail = Product::where('id', $product->id)->firstOrFail();
				//$data = $this->preProductDetail($product->id);
				$data = ['product_detail' => $product_detail];
				return $this->successResponse($data, 'Product added successfully!', 200);
			}
		} catch (Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}

	}

	public function productDetail(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'product_id' => 'required',
			]);

			if ($validator->fails()) {
				return $this->errorResponse($validator->errors()->first(), 422);
			}
			$user = Auth::user();
			$productid = $request->product_id;

			$data = $this->preProductDetail($productid);


			// product attributes
			if (clientPrefrenceModuleStatus('p2p_check')) {

				$product = Product::findOrFail($request->product_id);

				// All attribute list
				$productAttributes = Attribute::with('option', 'varcategory.cate.primary', 'productAttribute')
					->select('attributes.*')
					->join('attribute_categories', 'attribute_categories.attribute_id', 'attributes.id')
					->where('attribute_categories.category_id', $product->category_id)
					->where('attributes.status', '!=', 2)
					->orderBy('position', 'asc')->get();

				$data['attributes'] = $productAttributes;
				$data['p2p_active'] = true;

			} else {
				$data['attributes'] = [];
				$data['p2p_active'] = false;

			}

			return $this->successResponse($data, 'Product detail!', 200);

		} catch (Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	public function makeVariantRows(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'product_id' => 'required',
				'optionIds' => 'required',
				'variantIds' => 'required'
			]);

			if ($validator->fails()) {
				return $this->errorResponse($validator->errors()->first(), 422);
			}
			//return $request->all();
			$multiArray = array();
			$variantNames = array();
			$product = Product::where('id', $request->product_id)->firstOrFail();
			$msgRes = 'Please check variants to create variant set.';
			if (!$request->has('optionIds') || !$request->has('variantIds')) {
				return $this->errorResponse($msgRes, 422);
			}
			foreach ($request->optionIds as $key => $value) {
				$name = explode(';', $request->variantIds[$key]);
				if (!in_array($name[1], $variantNames)) {
					$variantNames[] = $name[1];
				}
				$multiArray[$request->variantIds[$key]][] = $value;
			}

			$combination = $this->array_combinations($multiArray);
			$new_combination = array();
			$edit = 0;

			if ($request->has('existing') && !empty($request->existing)) {
				$existingComb = $request->existing;
				$edit = 1;
				foreach ($combination as $key => $value) {
					$comb = $arrayVal = '';
					foreach ($value as $k => $v) {
						$arrayVal = explode(';', $v);
						$comb .= $arrayVal[0] . '*';
					}

					$comb = rtrim($comb, '*');

					if (!in_array($comb, $existingComb)) {
						$new_combination[$key] = $value;
					}
				}
				$combination = $new_combination;
				$msgRes = 'No new variant set found.';
			}

			if (count($combination) < 1) {
				return $this->errorResponse($msgRes, 422);
			}

			$data = $this->combinationVariants($combination, $multiArray, $variantNames, $product->id, $request->sku, $edit);
			return $this->successResponse($data, 'Variant detail!', 200);
		} catch (Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	function combinationVariants($combination, $multiArray, $variantNames, $product_id, $sku = '', $edit = 0)
	{
		$arrVal = array();
		foreach ($multiArray as $key => $value) {
			$varStr = $optStr = array();
			$vv = explode(';', $key);

			foreach ($value as $k => $v) {
				$ov = explode(';', $v);
				$optStr[] = $ov[0];
			}

			$arrVal[$vv[0]] = $optStr;
		}
		$name1 = '';

		$all_variant_sets = array();
		$output_data = [];
		$inc = 0;
		foreach ($combination as $key => $value) {
			$names = array();
			$ids = array();
			foreach ($value as $k => $v) {
				$variant = explode(';', $v);
				$ids[] = $variant[0];
				$names[] = $variant[1];
			}
			$proSku = $sku . '-' . implode('*', $ids);
			$proVariant = ProductVariant::where('sku', $proSku)->first();
			if (!$proVariant) {
				$proVariant = new ProductVariant();
				$proVariant->sku = $proSku;
				$proVariant->title = $sku . '-' . implode('-', $names);
				$proVariant->product_id = $product_id;
				$proVariant->barcode = $this->generateBarcodeNumber();
				$proVariant->save();

				foreach ($ids as $id1) {
					$all_variant_sets[$inc] = [
						'product_id' => $product_id,
						'product_variant_id' => $proVariant->id,
						'variant_option_id' => $id1,
					];

					foreach ($arrVal as $key => $value) {

						if (in_array($id1, $value)) {
							$all_variant_sets[$inc]['variant_type_id'] = $key;
						}
					}
					$inc++;
				}
			}
			$varient = array('id' => $proVariant->id, 'product_id' => $product_id, 'title' => $proVariant->title, 'names' => implode(", ", $names));
			$output_data[] = $varient;

		}
		ProductVariantSet::insert($all_variant_sets);

		return $output_data;
	}

	// public function updateProduct(Request $request)
	// {
	// 	$product = Product::where('id', $request->product_id)->firstOrFail();
	// 	try {
	// 		$validator = Validator::make($request->all(), [
	// 			'product_id' => 'required',
	// 				// 'product_name' => 'required|string',
	// 				// 'sku' => 'required|unique:products,sku,' . $product->id,
	// 				// 'url_slug' => 'required|unique:products,url_slug,' . $product->id,
	// 		]);

	// 		if ($validator->fails()) {
	// 			return $this->errorResponse($validator->errors()->first(), 422);
	// 		}

	// 		// Save Product Attribute
	// 		if (checkTableExists('product_attributes')) {
	// 			if (!empty($request->attribute)) {
	// 				$attribute = json_decode($request->attribute, true);

	// 				if (!empty($attribute)) {
	// 					$insert_arr = [];
	// 					$insert_count = 0;

	// 					foreach ($attribute as $key => $value) {
	// 						if (!empty($value) && !empty($value['option'] && is_array($value))) {

	// 							if (!empty($value['type']) && $value['type'] == 1) { // dropdown
	// 								$value_arr = @$value['value'];

    //                                 foreach($value['option'] as $option_key => $option) {
    //                                     if(!empty($value['type']) && $value['type'] == 4 ) { // textbox
	// 										$insert_arr[$insert_count]['product_id'] = $request->product_id;
	// 										$insert_arr[$insert_count]['attribute_id'] = $value['id'];
	// 										$insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
	// 										$insert_arr[$insert_count]['attribute_option_id'] = $option['option_id'];
	// 										$insert_arr[$insert_count]['key_value'] = (!empty($value['value']) && !empty($value['value'][0]) ? $value['value'][0] : '');
	// 										$insert_arr[$insert_count]['is_active'] = 1;
	// 									} elseif (@in_array($option['option_id'], $value_arr)) {

	// 										$insert_arr[$insert_count]['product_id'] = $request->product_id;
	// 										$insert_arr[$insert_count]['attribute_id'] = $value['id'];
	// 										$insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
	// 										$insert_arr[$insert_count]['attribute_option_id'] = $option['option_id'];
	// 										$insert_arr[$insert_count]['key_value'] = $option['option_id'];
	// 										$insert_arr[$insert_count]['is_active'] = 1;
	// 									}

	// 									$insert_count++;
	// 								}
	// 							} else {
	// 								$value_arr = @$value['value'];

	// 								// //\Log::info($option['option_id']);
	// 								foreach ($value['option'] as $option_key => $option) {
	// 									if (!empty($value['type']) && $value['type'] == 4) { // textbox
	// 										$insert_arr[$insert_count]['product_id'] = $request->product_id;
	// 										$insert_arr[$insert_count]['attribute_id'] = $value['id'];
	// 										$insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
	// 										$insert_arr[$insert_count]['attribute_option_id'] = $option['option_id'];
	// 										$insert_arr[$insert_count]['key_value'] = (!empty($value['value']) && !empty($value['value'][0]) ? $value['value'][0] : '');
	// 										$insert_arr[$insert_count]['is_active'] = 1;
	// 									} elseif (@in_array($option['option_id'], $value_arr)) {

	// 										$insert_arr[$insert_count]['product_id'] = $request->product_id;
	// 										$insert_arr[$insert_count]['attribute_id'] = $value['id'];
	// 										$insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
	// 										$insert_arr[$insert_count]['attribute_option_id'] = $option['option_id'];
	// 										$insert_arr[$insert_count]['key_value'] = $option['option_id'];
	// 										$insert_arr[$insert_count]['is_active'] = 1;
	// 									}

	// 									$insert_count++;
	// 								}
	// 							}
	// 						}


	// 					}
	// 					if (!empty($insert_arr)) {
	// 						ProductAttribute::where('product_id', $request->product_id)->delete();
	// 						ProductAttribute::insert($insert_arr);
	// 					}
	// 				}
	// 			}
	// 		}


	// 		$client = Client::orderBy('id', 'asc')->first();
	// 		if (isset($client->custom_domain) && !empty($client->custom_domain) && $client->custom_domain != $client->sub_domain) {
	// 			$sku_url =  ($client->custom_domain);
	// 		} else {
	// 			$sku_url =  ($client->sub_domain . env('SUBMAINDOMAIN'));
	// 		}
	// 		$slug = str_replace(' ', '-', $request->product_name);
	// 		$generated_slug = $sku_url . '.' . $slug;
	// 		$slug = generateSlug($generated_slug);
	// 		$slug = str_replace(' ', '-', $slug);
	// 		$generated_slug = $sku_url . '.' . $slug;

	// 		$user = Auth::user();
	// 		$productid = $product->id;
	// 		$user_vendor = UserVendor::where('user_id', $user->id)->first();




	// 		if (@$user_vendor->vendor_id) {

	// 			$product->sku = $slug;
	// 			$product->url_slug = $generated_slug;
	// 			$product->title = $request->product_name;
	// 			$product->category_id = $request->category_id;
	// 			$product->description = $request->body_html ?? '';
	// 			$product->type_id = 1;
	// 			$product->is_live = 1;
	// 			$product->publish_at = date('Y-m-d H:i:s');
	// 			$product->vendor_id = $user_vendor->vendor_id;

	// 			if (@$request->longitude) {
	// 				$product->longitude = $request->longitude;
	// 			}

	// 			if (@$request->address) {
	// 				$product->address = $request->address;
	// 			}
	// 			if (@$request->latitude) {
	// 				$product->latitude = $request->latitude;
	// 			}
	// 			$product->save();
	// 			$client_lang = ClientLanguage::where('is_primary', 1)->first();
	// 			if (!$client_lang) {
	// 				$client_lang = ClientLanguage::where('is_active', 1)->first();
	// 			}


	// 			if ($product->id > 0) {
	// 				$datatrans[] = [
	// 					'title' => $request->product_name ?? null,
	// 					'body_html' => $request->body_html ?? null,
	// 					'meta_title' => '',
	// 					'meta_keyword' => '',
	// 					'meta_description' => '',
	// 					'product_id' => $product->id,
	// 					'language_id' => $client_lang->language_id
	// 				];
	// 				$product_category =  ProductCategory::where('product_id',$request->product_id)->first();

	// 				// if(@$request->category_id){
	// 				// $product_category->product_id = $product->id;
	// 				// $product_category->category_id = $request->category_id;
	// 				// $product_category->save();
	// 				// }
	// 				$proVariant =  ProductVariant::where('product_id',$request->product_id)->first();

	// 				$proVariant->price = $request->price ?? 0;

	// 				$proVariant->week_price = $request->week_price ?? 0;
	// 				$proVariant->month_price = $request->month_price ?? 0;

	// 				if (@$request->emirate) {
	// 					$proVariant->emirate = $request->emirate;
	// 				}
	// 				if (@$request->compare_at_price) {
	// 					$proVariant->compare_at_price = $request->compare_at_price;
	// 				}

	// 				if (@$request->minimum_duration) {
	// 					$proVariant->minimum_duration = $request->minimum_duration * 24;
	// 				}
	// 				$proVariant->sku = $slug;
	// 				$proVariant->title = $slug . '-' .  empty($request->product_name) ? $slug : $request->product_name;
	// 				$proVariant->product_id = $product->id;
	// 				$proVariant->quantity = 1;
	// 				$proVariant->status = 1;
	// 				$proVariant->barcode = $this->generateBarcodeNumber();
	// 				$proVariant->save();
	// 				ProductTranslation::insert($datatrans);

	// 				$product_detail = Product::where('id', $product->id)->firstOrFail();

	// 				$data = ['product_detail' => $product_detail];

	// 			}

	// 		}

	// 		if ($request->has('file')) {
	// 			ProductImage::where('product_id',$product->id)->delete();
	// 			$imageId = '';
	// 			$files = $request->file('file');
	// 			if (is_array($files)) {
	// 				foreach ($files as $file) {
	// 					$img = new VendorMedia();
	// 					$img->media_type = 1;
	// 					$img->vendor_id = $product->vendor_id;
	// 					$img->path = Storage::disk('s3')->put($this->folderName, $file, 'public');
	// 					$img->save();
	// 					$path1 = $img->path['proxy_url'] . '40/40' . $img->path['image_path'];
	// 					if ($img->id > 0) {
	// 						$imageId = $img->id;
	// 						$image = new ProductImage();
	// 						$image->product_id = $product->id;
	// 						$image->is_default = 1;
	// 						$image->media_id = $imageId;
	// 						$image->save();
	// 						// if($image->id > 0 && $variant_id!="")
	// 						// {
	// 						// 	$varientimage = new ProductVariantImage();
	// 						// 	$varientimage->product_variant_id = $variant_id;
	// 						// 	$varientimage->product_image_id = $image->id;
	// 						// 	$varientimage->save();
	// 						// }
	// 					}
	// 				}
	// 				//return response()->json(['htmlData' => $resp]);
	// 			} else {
	// 				$img = new VendorMedia();
	// 				$img->media_type = 1;
	// 				$img->vendor_id = $product->vendor_id;
	// 				$img->path = Storage::disk('s3')->put($this->folderName, $files, 'public');
	// 				$img->save();
	// 				if ($img->id > 0) {
	// 					$imageId = $img->id;
	// 					$image = new ProductImage();
	// 					$image->product_id = $product->id;
	// 					$image->is_default = 1;
	// 					$image->media_id = $img->id;
	// 					$image->save();
	// 					// if($image->id > 0 && $variant_id!="")
	// 					// {
	// 					// 	$varientimage = new ProductVariantImage();
	// 					// 	$varientimage->product_variant_id = $variant_id;
	// 					// 	$varientimage->product_image_id = $image->id;
	// 					// 	$varientimage->save();
	// 					// }
	// 				}
	// 			}
	// 		}


	// 		if ($request->has('file_360')) {
	// 			$imageId = '';
	// 			$files = $request->file('file_360');
	// 			if (is_array($files)) {
	// 				foreach ($files as $file) {
	// 					$img = new VendorMedia();
	// 					$img->media_type = 4;
	// 					$img->vendor_id = $product->vendor_id;
	// 					$img->path = Storage::disk('s3')->put($this->folderName, $file, 'public');
	// 					$img->save();
	// 					$path1 = $img->path['proxy_url'] . '40/40' . $img->path['image_path'];
	// 					if ($img->id > 0) {
	// 						$imageId = $img->id;
	// 						$image = new ProductImage();
	// 						$image->product_id = $product->id;
	// 						$image->is_default = 1;
	// 						$image->media_id = $imageId;
	// 						$image->save();
	// 						// if($image->id > 0 && $variant_id!="")
	// 						// {
	// 						// 	$varientimage = new ProductVariantImage();
	// 						// 	$varientimage->product_variant_id = $variant_id;
	// 						// 	$varientimage->product_image_id = $image->id;
	// 						// 	$varientimage->save();
	// 						// }
	// 					}
	// 				}
	// 				//return response()->json(['htmlData' => $resp]);
	// 			} else {
	// 				$img = new VendorMedia();
	// 				$img->media_type = 4;
	// 				$img->vendor_id = $product->vendor_id;
	// 				$img->path = Storage::disk('s3')->put($this->folderName, $files, 'public');
	// 				$img->save();
	// 				if ($img->id > 0) {
	// 					$imageId = $img->id;
	// 					$image = new ProductImage();
	// 					$image->product_id = $product->id;
	// 					$image->is_default = 1;
	// 					$image->media_id = $img->id;
	// 					$image->save();
	// 					// if($image->id > 0 && $variant_id!="")
	// 					// {
	// 					// 	$varientimage = new ProductVariantImage();
	// 					// 	$varientimage->product_variant_id = $variant_id;
	// 					// 	$varientimage->product_image_id = $image->id;
	// 					// 	$varientimage->save();
	// 					// }
	// 				}
	// 			}
	// 		}
	// 			if(@$request->date_availability){
	// 			// $dates = array_column($request->date_availability, 'date_time');
	// 			ProductAvailability::where('product_id', $product->id)->where('not_available', 0)->delete();
	// 			if (@$request->date_availability && is_array($request->date_availability)) {
	// 				$date_availability_data = [];
	// 				foreach ($request->date_availability as $date_availability) {
	// 					$productAvailability = ProductAvailability::where('product_id', $product->id)->whereDate('date_time', $date_availability['date_time'])->first();
	// 					if ($productAvailability)
	// 						continue;
	// 					$date_availability_data[] = [
	// 						'product_id' => $product->id,
	// 						'date_time' => $date_availability['date_time'],
	// 						'not_available' => $date_availability['not_available'],
	// 						'created_at' => Carbon::now(),
	// 						'updated_at' => Carbon::now()
	// 					];
	// 				}
	// 				if (@$date_availability_data) {
	// 					ProductAvailability::insert($date_availability_data);
	// 				}
	// 			}
	// 		}


	// 		$data = Product::with('brand', 'variant.set', 'variant.vimage.pimage.image', 'primary', 'category.cat', 'variantSet', 'vatoptions', 'addOn', 'media.image', 'related', 'upSell', 'crossSell', 'celebrities')->where('id', $product->id)->firstOrFail();
	// 		return $this->successResponse($data, 'Product Updated successfully!', 200);
	// 	} catch (Exception $e) {
	// 		return $this->errorResponse($e->getMessage(), $e->getCode());
	// 	}
	// }

	public function updateProduct(Request $request)
	{
		$product = Product::where('id', $request->product_id)->firstOrFail();
		try {
			$validator = Validator::make($request->all(), [
				'product_id' => 'required',
			]);

			if ($validator->fails()) {
				return $this->errorResponse($validator->errors()->first(), 422);
			}

			// Save Product Attribute
			if (checkTableExists('product_attributes')) {
				if (!empty($request->attribute)) {
					$attribute = json_decode($request->attribute, true);

					if (!empty($attribute)) {
						$insert_arr = [];
						$insert_count = 0;

						foreach ($attribute as $key => $value) {
							if (!empty($value) && !empty($value['option'] && is_array($value))) {

								if (!empty($value['type']) && $value['type'] == 1) { // dropdown
									$value_arr = @$value['value'];

									foreach ($value['option'] as $key1 => $val1) {
										if (@in_array($val1['option_id'], $value_arr)) {

											$insert_arr[$insert_count]['product_id'] = $request->product_id;
											$insert_arr[$insert_count]['attribute_id'] = $value['id'];
											$insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
											$insert_arr[$insert_count]['attribute_option_id'] = $val1['option_id'];
											$insert_arr[$insert_count]['key_value'] = $val1['option_id'];
											$insert_arr[$insert_count]['is_active'] = 1;
										}
										$insert_count++;
									}
								} else {
									$value_arr = @$value['value'];

									// //\Log::info($option['option_id']);
									foreach ($value['option'] as $option_key => $option) {
										if (!empty($value['type']) && $value['type'] == 4) { // textbox
											$insert_arr[$insert_count]['product_id'] = $request->product_id;
											$insert_arr[$insert_count]['attribute_id'] = $value['id'];
											$insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
											$insert_arr[$insert_count]['attribute_option_id'] = $option['option_id'];
											$insert_arr[$insert_count]['key_value'] = (!empty($value['value']) && !empty($value['value'][0]) ? $value['value'][0] : '');
											$insert_arr[$insert_count]['is_active'] = 1;
										} elseif (@in_array($option['option_id'], $value_arr)) {

											$insert_arr[$insert_count]['product_id'] = $request->product_id;
											$insert_arr[$insert_count]['attribute_id'] = $value['id'];
											$insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
											$insert_arr[$insert_count]['attribute_option_id'] = $option['option_id'];
											$insert_arr[$insert_count]['key_value'] = $option['option_id'];
											$insert_arr[$insert_count]['is_active'] = 1;
										}

										$insert_count++;
									}
								}
							}
						}
						if (!empty($insert_arr)) {
							ProductAttribute::where('product_id', $request->product_id)->delete();
							ProductAttribute::insert($insert_arr);
						}
					}
				}
			}
			$client = Client::orderBy('id', 'asc')->first();
			if (isset($client->custom_domain) && !empty($client->custom_domain) && $client->custom_domain != $client->sub_domain) {
				$sku_url =  ($client->custom_domain);
			} else {
				$sku_url =  ($client->sub_domain . env('SUBMAINDOMAIN'));
			}
			$slug = str_replace(' ', '-', $request->product_name);
			$generated_slug = $sku_url . '.' . $slug;
			$slug = generateSlug($generated_slug);
			$slug = str_replace(' ', '-', $slug);
			$generated_slug = $sku_url . '.' . $slug;

			$user = Auth::user();
			$user_vendor = UserVendor::where('user_id', $user->id)->first();

			if (@$user_vendor->vendor_id) {

				$product->sku = $slug;
				$product->url_slug = $generated_slug;
				$product->title = $request->product_name;
				$product->category_id = $request->category_id;
				$product->description = $request->body_html ?? '';
				$product->type_id = 1;
				$product->is_live = 1;
				$product->publish_at = date('Y-m-d H:i:s');
				$product->vendor_id = $user_vendor->vendor_id;

				if (@$request->longitude) {
					$product->longitude = $request->longitude;
				}

				if (@$request->address) {
					$product->address = $request->address;
				}
				if (@$request->latitude) {
					$product->latitude = $request->latitude;
				}
				$product->save();
				$client_lang = ClientLanguage::where('is_primary', 1)->first();
				if (!$client_lang) {
					$client_lang = ClientLanguage::where('is_active', 1)->first();
				}


				if ($product->id > 0) {
					$datatrans[] = [
						'title' => $request->product_name ?? null,
						'body_html' => $request->body_html ?? null,
						'meta_title' => '',
						'meta_keyword' => '',
						'meta_description' => '',
						'product_id' => $product->id,
						'language_id' => $client_lang->language_id
					];
					$product_category =  ProductCategory::where('product_id',$request->product_id)->first();

					// if(@$request->category_id){
					// $product_category->product_id = $product->id;
					// $product_category->category_id = $request->category_id;
					// $product_category->save();
					// }
					$proVariant =  ProductVariant::where('product_id',$request->product_id)->first();

					$proVariant->price = $request->price ?? 0;

					$proVariant->week_price = $request->week_price ?? 0;
					$proVariant->month_price = $request->month_price ?? 0;

					if (@$request->emirate) {
						$proVariant->emirate = $request->emirate;
					}
					if (@$request->compare_at_price) {
						$proVariant->compare_at_price = $request->compare_at_price;
					}

					if (@$request->minimum_duration) {
						$proVariant->minimum_duration = $request->minimum_duration * 24;
					}
					$proVariant->sku = $slug;
					$proVariant->title = $slug . '-' .  empty($request->product_name) ? $slug : $request->product_name;
					$proVariant->product_id = $product->id;
					$proVariant->quantity = 1;
					$proVariant->status = 1;
					$proVariant->barcode = $this->generateBarcodeNumber();
					$proVariant->save();
					ProductTranslation::insert($datatrans);

					$product_detail = Product::where('id', $product->id)->firstOrFail();

					$data = ['product_detail' => $product_detail];

				}

			}

			if ($request->has('file')) {
				ProductImage::where('product_id',$product->id)->delete();
				$imageId = '';
				$files = $request->file('file');
				if (is_array($files)) {
					foreach ($files as $file) {
						$img = new VendorMedia();
						$img->media_type = 1;
						$img->vendor_id = $product->vendor_id;
						$img->path = Storage::disk('s3')->put($this->folderName, $file, 'public');
						$img->save();
						$path1 = $img->path['proxy_url'] . '40/40' . $img->path['image_path'];
						if ($img->id > 0) {
							$imageId = $img->id;
							$image = new ProductImage();
							$image->product_id = $product->id;
							$image->is_default = 1;
							$image->media_id = $imageId;
							$image->save();
							// if($image->id > 0 && $variant_id!="")
							// {
							// 	$varientimage = new ProductVariantImage();
							// 	$varientimage->product_variant_id = $variant_id;
							// 	$varientimage->product_image_id = $image->id;
							// 	$varientimage->save();
							// }
						}
					}
					//return response()->json(['htmlData' => $resp]);
				} else {
					$img = new VendorMedia();
					$img->media_type = 1;
					$img->vendor_id = $product->vendor_id;
					$img->path = Storage::disk('s3')->put($this->folderName, $files, 'public');
					$img->save();
					if ($img->id > 0) {
						$imageId = $img->id;
						$image = new ProductImage();
						$image->product_id = $product->id;
						$image->is_default = 1;
						$image->media_id = $img->id;
						$image->save();
						// if($image->id > 0 && $variant_id!="")
						// {
						// 	$varientimage = new ProductVariantImage();
						// 	$varientimage->product_variant_id = $variant_id;
						// 	$varientimage->product_image_id = $image->id;
						// 	$varientimage->save();
						// }
					}
				}
			}


			if ($request->has('file_360')) {
				$imageId = '';
				$files = $request->file('file_360');
				if (is_array($files)) {
					foreach ($files as $file) {
						$img = new VendorMedia();
						$img->media_type = 4;
						$img->vendor_id = $product->vendor_id;
						$img->path = Storage::disk('s3')->put($this->folderName, $file, 'public');
						$img->save();
						$path1 = $img->path['proxy_url'] . '40/40' . $img->path['image_path'];
						if ($img->id > 0) {
							$imageId = $img->id;
							$image = new ProductImage();
							$image->product_id = $product->id;
							$image->is_default = 1;
							$image->media_id = $imageId;
							$image->save();
							// if($image->id > 0 && $variant_id!="")
							// {
							// 	$varientimage = new ProductVariantImage();
							// 	$varientimage->product_variant_id = $variant_id;
							// 	$varientimage->product_image_id = $image->id;
							// 	$varientimage->save();
							// }
						}
					}
					//return response()->json(['htmlData' => $resp]);
				} else {
					$img = new VendorMedia();
					$img->media_type = 4;
					$img->vendor_id = $product->vendor_id;
					$img->path = Storage::disk('s3')->put($this->folderName, $files, 'public');
					$img->save();
					if ($img->id > 0) {
						$imageId = $img->id;
						$image = new ProductImage();
						$image->product_id = $product->id;
						$image->is_default = 1;
						$image->media_id = $img->id;
						$image->save();
						// if($image->id > 0 && $variant_id!="")
						// {
						// 	$varientimage = new ProductVariantImage();
						// 	$varientimage->product_variant_id = $variant_id;
						// 	$varientimage->product_image_id = $image->id;
						// 	$varientimage->save();
						// }
					}
				}
			}
				if(@$request->date_availability){
				// $dates = array_column($request->date_availability, 'date_time');
				ProductAvailability::where('product_id', $product->id)->where('not_available', 0)->delete();
				if (@$request->date_availability && is_array($request->date_availability)) {
					$date_availability_data = [];
					foreach ($request->date_availability as $date_availability) {
						$productAvailability = ProductAvailability::where('product_id', $product->id)->whereDate('date_time', $date_availability['date_time'])->first();
						if ($productAvailability)
							continue;
						$date_availability_data[] = [
							'product_id' => $product->id,
							'date_time' => $date_availability['date_time'],
							'not_available' => $date_availability['not_available'],
							'created_at' => Carbon::now(),
							'updated_at' => Carbon::now()
						];
					}
					if (@$date_availability_data) {
						ProductAvailability::insert($date_availability_data);
					}
				}
			}


			$data = Product::with('brand', 'variant.set', 'variant.vimage.pimage.image', 'primary', 'category.cat', 'variantSet', 'vatoptions', 'addOn', 'media.image', 'related', 'upSell', 'crossSell', 'celebrities')->where('id', $product->id)->firstOrFail();
			return $this->successResponse($data, 'Product Updated successfully!', 200);
		} catch (\Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}
	public function deleteProduct(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'product_id' => 'required',
			]);

			if ($validator->fails()) {
				return $this->errorResponse($validator->errors()->first(), 422);
			}
			$id = $request->product_id;
			DB::beginTransaction();
			$product = Product::find($id);
			if (!$product) {
				return $this->errorResponse('Product not found', 422);
			}
			$dynamic = time();

			Product::where('id', $id)->update(['sku' => $product->sku . $dynamic, 'url_slug' => $product->url_slug . $dynamic]);

			$tot_var = ProductVariant::where('product_id', $id)->get();
			foreach ($tot_var as $varr) {
				$dynamic = time() . substr(md5(mt_rand()), 0, 7);
				ProductVariant::where('id', $varr->id)->update(['sku' => $product->sku . $dynamic]);
			}

			Product::where('id', $id)->delete();

			CartProduct::where('product_id', $id)->delete();
			UserWishlist::where('product_id', $id)->delete();

			DB::commit();
			return $this->successResponse('', 'Product deleted successfully!', 200);
		} catch (Exception $e) {
			DB::rollback();
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	public function deleteProductVariant(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'product_id' => 'required',
				'variant_id' => 'required',
			]);

			if ($validator->fails()) {
				return $this->errorResponse($validator->errors()->first(), 422);
			}
			$product_id = $request->product_id;
			$variant_id = $request->variant_id;

			$product_variant = ProductVariant::where('id', $variant_id)->where('product_id', $product_id)->first();
			if (!$product_variant) {
				return $this->errorResponse('Product variant not found', 422);
			}
			$product_variant->status = 0;
			$product_variant->save();
			// if ($request->is_product_delete > 0) {
			// 	Product::where('id', $request->product_id)->delete();
			// }
			return $this->successResponse('', 'Product variant deleted successfully!', 200);
		} catch (Exception $e) {
			DB::rollback();
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	public function productImages(Request $request)
	{

		try {
			$validator = Validator::make($request->all(), [
				'product_id' => 'required',
			]);

			if ($validator->fails()) {
				return $this->errorResponse($validator->errors()->first(), 422);
			}
			$product_id = $request->product_id;
			$variant_id = $request->variant_id;
			$imageid = $request->image_id;

			$resp = '';
			$product = Product::findOrFail($product_id);
			if (!$product) {
				return $this->errorResponse('Product not found', 422);
			}

			//delete prev variant images
			if ($variant_id && $variant_id != "") {
				$deleteVarImg = ProductVariantImage::where('product_variant_id', $variant_id)->delete();
			}
			if ($request->has('image_id')) {
				foreach ($imageid as $key => $value) {
					$saveImage[] = [
						'product_variant_id' => $variant_id,
						'product_image_id' => $value
					];
				}
				ProductVariantImage::insert($saveImage);
			}

			if ($request->has('file')) {
				$imageId = '';
				$files = $request->file('file');
				if (is_array($files)) {
					foreach ($files as $file) {
						$img = new VendorMedia();
						$img->media_type = 1;
						$img->vendor_id = $product->vendor_id;
						$img->path = Storage::disk('s3')->put($this->folderName, $file, 'public');
						$img->save();
						$path1 = $img->path['proxy_url'] . '40/40' . $img->path['image_path'];
						if ($img->id > 0) {
							$imageId = $img->id;
							$image = new ProductImage();
							$image->product_id = $product->id;
							$image->is_default = 1;
							$image->media_id = $imageId;
							$image->save();
							if ($image->id > 0 && $variant_id != "") {
								$varientimage = new ProductVariantImage();
								$varientimage->product_variant_id = $variant_id;
								$varientimage->product_image_id = $image->id;
								$varientimage->save();
							}
						}
					}
					//return response()->json(['htmlData' => $resp]);
				} else {
					$img = new VendorMedia();
					$img->media_type = 1;
					$img->vendor_id = $product->vendor_id;
					$img->path = Storage::disk('s3')->put($this->folderName, $files, 'public');
					$img->save();
					if ($img->id > 0) {
						$imageId = $img->id;
						$image = new ProductImage();
						$image->product_id = $product->id;
						$image->is_default = 1;
						$image->media_id = $img->id;
						$image->save();
						if ($image->id > 0 && $variant_id != "") {
							$varientimage = new ProductVariantImage();
							$varientimage->product_variant_id = $variant_id;
							$varientimage->product_image_id = $image->id;
							$varientimage->save();
						}
					}
				}
			}
			$images = ProductImage::with('image')->where('product_images.product_id', $product->id)->get();

			return $this->successResponse($images, 'Product image added successfully!', 200);

		} catch (Exception $e) {
			DB::rollback();
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	public function deleteProductImage(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'product_id' => 'required',
				'media_id' => 'required',
			]);

			if ($validator->fails()) {
				return $this->errorResponse($validator->errors()->first(), 422);
			}
			$product_id = $request->product_id;
			$image_id = $request->media_id;
			$product = Product::findOrfail($product_id);
			$img = VendorMedia::findOrfail($image_id);
			$img->delete();

			$images = ProductImage::with('image')->where('product_images.product_id', $product_id)->get();
			return $this->successResponse($images, 'Product image deleted successfully!', 200);
		} catch (Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	public function getProductImages(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'product_id' => 'required',
				'variant_id' => 'required',
			]);

			if ($validator->fails()) {
				return $this->errorResponse($validator->errors()->first(), 422);
			}
			$product_id = $request->product_id;
			$variant_id = $request->variant_id;

			$product = Product::where('id', $product_id)->first();
			if (!$product) {
				return $this->errorResponse('Product not found', 422);
			}
			$variantImages = array();
			if ($variant_id > 0) {
				$varImages = ProductVariantImage::where('product_variant_id', $variant_id)->get();
				if ($varImages) {
					foreach ($varImages as $key => $value) {
						$variantImages[] = $value->product_image_id;
					}
				}
			}
			//$variId = ($request->has('variant_id') && $request->variant_id > 0) ? $request->variant_id : 0;
			$images = ProductImage::with('image')->where('product_images.product_id', $product->id)->get();
			$k = 0;
			foreach ($images as $singleimage) {
				if (in_array($singleimage->id, $variantImages)) {
					$images[$k]->is_selected = 1;
				} else {
					$images[$k]->is_selected = 0;
				}
				$k++;
			}
			return $this->successResponse($images, 'Product images details!', 200);
		} catch (Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	public function getVendorProductList(Request $request)
	{
		try {
			$category_list = [];
			$allcategories = [];
			$user = Auth::user();
			$langId = $user->language;
			$is_selected_vendor_id = 0;
			$paginate = $request->has('limit') ? $request->limit : 12;
			$client_currency_detail = ClientCurrency::where('currency_id', $user->currency)->first();
			$selected_vendor_id = $request->has('selected_vendor_id') ? $request->selected_vendor_id : '';
			$selected_category_id = $request->has('selected_category_id') ? $request->selected_category_id : '';
			$user_vendor_ids = UserVendor::where('user_id', $user->id)->pluck('vendor_id');
			if ($user_vendor_ids) {
				$is_selected_vendor_id = $selected_vendor_id ? $selected_vendor_id : $user_vendor_ids->first();
			}
			$vendor_list = Vendor::whereIn('id', $user_vendor_ids)->get(['id', 'name', 'logo']);
			foreach ($vendor_list as $vendor) {
				$vendor->is_selected = ($is_selected_vendor_id == $vendor->id) ? true : false;
			}
			$vendor_categories = VendorCategory::where('vendor_id', $is_selected_vendor_id)
				->whereHas('category', function ($query) {
					$query->whereIn('type_id', [1]);
				})->where('status', 1)->get('category_id');
			$vendor_category_id = 0;
			if ($vendor_categories->count()) {
				$vendor_category_id = $vendor_categories->first()->category_id;
			}



			//  $is_selected_category_id = $selected_category_id ? $selected_category_id : $vendor_category_id;
			// if($selected_category_id)
			// {
			// 	$allcategories[] = $selected_category_id;
			// }else{
			// 	foreach ($vendor_categories as $vendor_category) {
			// 		$allcategories[] = $vendor_category->category->id;
			// 	}
			// }
			$is_selected_category_id = $selected_category_id;
			foreach ($vendor_categories as $vendor_category) {
				$Category_translation = Category_translation::where('category_id', $vendor_category->category->id)->where('language_id', $langId)->first();
				if (!$Category_translation) {
					$Category_translation = Category_translation::where('category_id', $vendor_category->category->id)->first();
				}
				$category_detail = Category::where('id', $vendor_category->category->id)->first();
				$category_list[] = array(
					'id' => $vendor_category->category->id,
					'name' => $Category_translation ? $Category_translation->name : $vendor_category->category->slug,
					'cat_image' => $category_detail->image ?? "",
					'type_id' => $vendor_category->category->type_id,
					'is_selected' => $is_selected_category_id == $vendor_category->category_id ? true : false
				);
			}
			$products = Product::select('id', 'sku', 'url_slug','is_live','category_id','calories')->has('vendor')
						->with(['media.image', 'categoryName', 'translation' => function($q) use($langId){
                        	$q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
                    	},'variant' => function($q) use($langId){
                            $q->select('sku', 'product_id', 'quantity', 'price','markup_price', 'barcode');
                            $q->groupBy('product_id');
                    	},
                    ])->orderBy('id', 'DESC');

			if ($selected_category_id) {
				$products = $products->where('category_id', $selected_category_id);
			}
			// ->where('category_id', $is_selected_category_id);
			if ($selected_vendor_id > 0) {
				$products = $products->where('vendor_id', $selected_vendor_id);
			}
			$publishtype = $request->has('type') ? $request->type : '';

			if ($publishtype == "all") {
				$products = $products->paginate($paginate);
			} else {
				$products = $products->where('is_live', 1)->paginate($paginate);
			}

			foreach ($products as $product) {
				foreach ($product->variant as $k => $v) {
					$product->variant[$k]->multiplier = $client_currency_detail->doller_compare;
				}
			}
			$data = ['vendor_list' => $vendor_list, 'category_list' => $category_list, 'products' => $products];
			return $this->successResponse($data, '', 200);
		} catch (Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	public function getVendorProductCategoryList(Request $request, $vendor_id)
	{
		try {
			$user = Auth::user();
			$langId = $user->language;
			$limit = $request->has('limit') ? $request->limit : 12;
			$page = $request->has('page') ? $request->page : 1;

			$vendor_categories = VendorCategory::with([
				'category.translation' => function ($q) use ($langId) {
					$q->where('category_translations.language_id', $langId)->groupBy('category_translations.category_id');
				}
			])
				->whereHas('category', function ($q) use ($langId) {
					$q->whereNull('deleted_at')->orWhere('deleted_at', '');
				})
				->select('category_id')->where('vendor_id', $vendor_id)->where('status', 1)->paginate($limit, $page);

			$p_categories = collect();
			$product_categories_hierarchy = '';

			foreach ($vendor_categories as $vendor_category) {
				$p_categories->push($vendor_category->category);
				// $category_name = '';
				// if($vendor_category->category){
				// 	$category_name = $vendor_category->category->translation->first() ? $vendor_category->category->translation->first()->name : $vendor_category->category->slug;
				// }
				// $vendor_category->id = $vendor_category->category_id;
				// $vendor_category->name = $category_name;
				// $vendor_category->cat_image = $vendor_category->category->image ?? '';
				// $vendor_category->type_id = $vendor_category->category->type_id;
				// unset($vendor_category->category);
				// unset($vendor_category->category_id);
			}
			$product_categories_build = $this->buildTree(array_filter($p_categories->toArray()));
			$product_categories_hierarchy = $this->getCategoryOptionsHeirarchy($product_categories_build, $langId);
			foreach ($product_categories_hierarchy as $k => $cat) {
				$myArr = array(1, 3, 7, 8, 9);
				if (getClientPreferenceDetail()->p2p_check) {
					$myArr[] = 13;
				}
				if (isset($cat['type_id']) && !in_array($cat['type_id'], $myArr)) {
					unset($product_categories_hierarchy[$k]);
				}
			}
			$data = new Paginator(array_values($product_categories_hierarchy), $limit, $page);
			return $this->successResponse($data, '', 200);
		} catch (Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	public function getVendorProductsWithCategoryList(Request $request, $vendor_id)
	{

		try {
			$user = Auth::user();
			$langId = $user->language;
			$limit = $request->has('limit') ? $request->limit : 12;
			$page = $request->has('page') ? $request->page : 1;
			$publishtype = $request->has('type') ? $request->type : '';

			$client_currency_detail = ClientCurrency::where('currency_id', $user->currency)->first();
			$selected_category_id = $request->has('selected_category_id') ? $request->selected_category_id : '';

			$products = Product::select('id', 'sku', 'url_slug', 'is_live', 'category_id','latitude','longitude','address')->has('vendor')
				->with([
					'media.image',
					'categoryName',
					'productcategory',
					'product_availability',
					'translation' => function ($q) use ($langId) {
						$q->select('product_id', 'title', 'body_html', 'meta_title', 'meta_keyword', 'meta_description')->where('language_id', $langId);
					},
					'variant' => function ($q) use ($langId) {
						$q->select('sku', 'product_id', 'quantity', 'price', 'markup_price', 'barcode','week_price','month_price','emirate','price','cost_price','compare_at_price');
						$q->groupBy('product_id');
					},
				])->orderBy('id', 'DESC');

			if ($selected_category_id) {
				$products = $products->where('category_id', $selected_category_id);
			}

			$products = $products->where('vendor_id', $vendor_id);
			if ($publishtype == "all") {
				$products = $products->paginate($limit, $page);
			} else {
				$products = $products->paginate($limit, $page);
			}

			// foreach ($products as $product) {
			// 	foreach ($product->variant as $k => $v) {
			// 		$product->variant[$k]->multiplier = $client_currency_detail->doller_compare;
			// 	}
			// }

			foreach ($products as $product) {
				foreach ($product->orderProduct as $OrderProducts) {
					$dates = [];

					if (@$OrderProducts->start_date_time && @$OrderProducts->end_date_time) {
						$period = CarbonPeriod::create(date('Y-m-d', strtotime($OrderProducts->start_date_time)), date('Y-m-d', strtotime($OrderProducts->end_date_time)));

						foreach ($period as $date) {
							$dates[] =  $date->format('Y-m-d');
						}

						if (@$dates) {
							foreach ($product->product_availability as $k => $product_availability) {
								foreach ($dates as $date) {
									if (date('Y-m-d', strtotime($product_availability->date_time)) == $date) {
										@$product->product_availability[$date]['selected'] = true;
									}
								}
							}
						}
					}
				}
				foreach ($product->variant as $k => $v) {
					$product->variant[$k]->multiplier = $client_currency_detail->doller_compare;
				}
			}
			return $this->successResponse($products, '', 200);
		} catch (Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	public function updateProductStatus(Request $request)
	{
		try {
			$validator = Validator::make($request->all(), [
				'product_id' => 'required',
				'is_live' => 'required'
			]);

			if ($validator->fails()) {
				return $this->errorResponse($validator->errors()->first(), 422);
			}
			$product_id = $request->product_id;
			$product = Product::where('id', $request->product_id)->first();
			if ($product) {
				$product->is_live = $request->is_live;
				$product->save();
				return $this->successResponse('', 'Status updated successfully!', 200);
			} else {
				return $this->errorResponse('Product not found', 422);
			}
		} catch (Exception $e) {
			return $this->errorResponse($e->getMessage(), $e->getCode());
		}
	}

	private function preProductDetail($productid)
	{
		$user = Auth::user();
		$product = Product::with('brand', 'variant.set', 'vendor', 'variant.vimage.pimage.image', 'primary', 'category.cat', 'variantSet', 'vatoptions', 'addOn', 'media.image', 'related', 'upSell', 'crossSell', 'celebrities')->where('id', $productid)->firstOrFail();
		$productVariants = Variant::with('option', 'varcategory.cate.primary')
			->select('variants.*')
			->join('variant_categories', 'variant_categories.variant_id', 'variants.id')
			->where('variant_categories.category_id', $product->category_id)
			->where('variants.status', '!=', 2)
			->orderBy('position', 'asc')->get();

		$brands = Brand::join('brand_categories as bc', 'bc.brand_id', 'brands.id')
			->select('brands.id', 'brands.title', 'brands.image')
			->where('bc.category_id', $product->category_id)->where('status', 1)->get();

		$clientLanguages = ClientLanguage::join('languages as lang', 'lang.id', 'client_languages.language_id')
			->select('lang.id as langId', 'lang.name as langTitle', 'lang.sort_code', 'client_languages.is_primary')
			->where('client_languages.client_code', Auth::user()->code)
			->where('client_languages.is_active', 1)
			->orderBy('client_languages.is_primary', 'desc')->get();

		$addons = AddonSet::with('option')->select('id', 'title')
			->where('status', '!=', 2)
			->where('vendor_id', $product->vendor_id)
			->orderBy('position', 'asc')->get();

		$taxCate = TaxCategory::all();

		$otherProducts = Product::with('primary')->select('id', 'sku')->where('is_live', 1)->where('id', '!=', $product->id)->where('vendor_id', $product->vendor_id)->get();
		$configData = ClientPreference::select('celebrity_check', 'pharmacy_check', 'need_dispacher_ride', 'need_delivery_service', 'enquire_mode', 'need_dispacher_home_other_service', 'delay_order', 'product_order_form', 'business_type', 'minimum_order_batch')->first();
		$celebrities = Celebrity::select('id', 'name')->where('status', '!=', 3)->get();
		$data = ['product_detail' => $product, 'product_variants' => $productVariants, 'brands' => $brands, 'client_languages' => $clientLanguages, 'addons' => $addons, 'tax_category' => $taxCate, 'other_products' => $otherProducts, 'config_data' => $configData, 'celebrities' => $celebrities];
		return $data;
	}

	public function generateBarcodeNumber()
	{
		$random_string = substr(md5(microtime()), 0, 14);
		while (ProductVariant::where('barcode', $random_string)->exists()) {
			$random_string = substr(md5(microtime()), 0, 14);
		}
		return $random_string;
	}

	private function array_combinations($arrays)
	{
		$result = array();
		$arrays = array_values($arrays);
		$sizeIn = sizeof($arrays);
		$size = $sizeIn > 0 ? 1 : 0;
		foreach ($arrays as $array)
			$size = $size * sizeof($array);
		for ($i = 0; $i < $size; $i++) {
			$result[$i] = array();
			for ($j = 0; $j < $sizeIn; $j++)
				array_push($result[$i], current($arrays[$j]));
			for ($j = ($sizeIn - 1); $j >= 0; $j--) {
				if (next($arrays[$j]))
					break;
				elseif (isset($arrays[$j]))
					reset($arrays[$j]);
			}
		}
		return $result;
	}


	/**
	 * Post Route
	 * Save Rescheduled Order
	 */
	public function rescheduleOrder(Request $request, $domain = '')
	{
		try {
			$request->schedule_pickup_slot = $request->pickup_reschdule_slot ?? '';
			$request->pickup_schedule_datetime = $request->pickup_reschdule_date ?? '';

			$request->schedule_dropoff_slot = $request->drop_reschdule_slot ?? '';
			$request->dropoff_schedule_datetime = $request->drop_reschdule_date ?? '';

			$order_id = $request->order_id;
			$order = Order::find($order_id);
			$vendor_id = $request->vendor_id;
			$vendor = Vendor::where('id', $vendor_id)->first();
			$user = Auth::user();
			$currency_id = $request->currency_id;
			$clientCurrency = ClientCurrency::where('currency_id', $currency_id)->first();
			$schedule_pickup_compare = Carbon::parse($order->schedule_pickup ?? '');
			$schedule_dropoff_compare = Carbon::parse($order->schedule_dropoff ?? '');
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
					return $this->errorResponse('Insufficient wallet balance, required rescheduling charges are ' . $clientCurrency->currency->symbol . $totalCharges . '. Please recharge your wallet.', '400');
				}

			}
			// If the rescheduling is done on the day of pickup, then a rescheduling fee will apply.
			elseif ($pickup_schedule_datetime_compare == $schedule_pickup_compare->format('Y-m-d')) {
				if ($vendor->pickup_cancelling_charges > 0) {
					$result = $this->chargeForPickupRescheduling($user, $vendor, $order);
					if ($result == false) {
						return $this->errorResponse('Insufficient wallet balance, required rescheduling charges are ' . $clientCurrency->currency->symbol . $vendor->pickup_cancelling_charges . '. Please recharge your wallet.', '400');
					}
				}

			}
			// If the rescheduling is done on the day of delivery, then a rescheduling fee will apply.
			elseif ($dropoff_schedule_datetime_compare == $schedule_dropoff_compare->format('Y-m-d')) {
				if ($vendor->pickup_cancelling_charges > 0) {
					$result = $this->chargeForDropoffRescheduling($user, $vendor, $order);
					if ($result == false) {
						return $this->errorResponse('Insufficient wallet balance, required rescheduling charges are ' . $clientCurrency->currency->symbol . $vendor->rescheduling_charges . '. Please recharge your wallet.', '400');
					}
				}
			}

			$pickup_schedule_datetime = null;
			if ($request->schedule_pickup_slot) {
				$schedule_pickup_slot = explode(" - ", $request->schedule_pickup_slot);
				$pickup_schedule_datetime = Carbon::createFromFormat('Y-m-d H:i:s', $request->pickup_schedule_datetime . ' ' . $schedule_pickup_slot[0] . ':00');
			}

			$dropoff_schedule_datetime = null;
			if ($request->schedule_dropoff_slot) {
				$schedule_dropoff_slot = explode(" - ", $request->schedule_dropoff_slot);
				$dropoff_schedule_datetime = Carbon::createFromFormat('Y-m-d H:i:s', $request->dropoff_schedule_datetime . ' ' . $schedule_dropoff_slot[0] . ':00');
			}

			$rescheduleOrder = new RescheduleOrder();
			$rescheduleOrder->reschedule_by = $user->id;
			$rescheduleOrder->order_id = $order_id;
			$rescheduleOrder->vendor_id = $vendor_id;
			$rescheduleOrder->prev_schedule_pickup = $order->schedule_pickup;
			$rescheduleOrder->prev_schedule_dropoff = $order->schedule_dropoff;
			$rescheduleOrder->prev_scheduled_slot = $order->scheduled_slot;
			$rescheduleOrder->prev_dropoff_scheduled_slot = $order->dropoff_scheduled_slot;
			$rescheduleOrder->new_schedule_pickup = (($pickup_schedule_datetime) ? Carbon::parse($pickup_schedule_datetime, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s') : Null);
			$rescheduleOrder->new_schedule_dropoff = (($dropoff_schedule_datetime) ? Carbon::parse($dropoff_schedule_datetime, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s') : NULL);
			$rescheduleOrder->new_scheduled_slot = $request->schedule_pickup_slot ?? NUll;
			$rescheduleOrder->new_dropoff_scheduled_slot = $request->schedule_dropoff_slot ?? Null;
			$rescheduleOrder->save();

			if ($request->schedule_pickup_slot) {
				$order->schedule_pickup = Carbon::parse($pickup_schedule_datetime, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
				$order->scheduled_slot = $request->schedule_pickup_slot;
			}
			if ($request->schedule_dropoff_slot) {
				$order->dropoff_scheduled_slot = $request->schedule_dropoff_slot;
				$order->schedule_dropoff = Carbon::parse($dropoff_schedule_datetime, $user->timezone)->setTimezone('UTC')->format('Y-m-d H:i:s');
			}
			$order->save();

			// Send Rescheduling Request to Dispatcher if order is already accepted
			$orderVendor = OrderVendor::where('order_id', $order->id)->first();
			if (!empty($orderVendor->dispatch_traking_url)) {
				$postdata = [
					'order_unique_id' => substr($orderVendor->dispatch_traking_url, strrpos($orderVendor->dispatch_traking_url, '/') + 1),
					// To get order unique id after slash (/).
					'order_number' => $orderVendor->orderDetail->order_number,
					'schedule_pickup' => (($pickup_schedule_datetime) ? $orderVendor->orderDetail->schedule_pickup : null),
					'schedule_dropoff' => (($dropoff_schedule_datetime) ? $orderVendor->orderDetail->schedule_dropoff : null),
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
				$res = $client->post(
					$url . '/api/order/reschedule',
					['form_params' => ($postdata)]
				);
				$response = json_decode($res->getBody(), true);

			} else {
				$response['status'] = 'success';
			}

			if ($response['status'] == 'success') {
				return $this->successResponse([], 'Order reschedule is done', 200);
			} else {
				return $this->errorResponse('Something went wrong!', '400');
			}

		} catch (\Exception $e) {
			return $this->errorResponse($e->getMessage(), '400');
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

	public function chargeForPickupRescheduling($user, $vendor, $order)
	{
		if ($user) {
			if ($user->balanceFloat > 0) {
				$wallet = $user->wallet;
				$wallet_amount_used = $user->balanceFloat;
				$payable_amount_for_pickup = $vendor->pickup_cancelling_charges;
				if ($wallet_amount_used >= $payable_amount_for_pickup) {
					if ($wallet_amount_used > 0) {
						$wallet->withdrawFloat($payable_amount_for_pickup, ['Wallet has been <b>debited</b> for rescheduling the order on pickup day under order number <b>' . $order->order_number . '</b>']);
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
						$wallet->withdrawFloat($payable_amount, ['Wallet has been <b>debited</b> for rescheduling the order on dropoff day under order number <b>' . $order->order_number . '</b>']);
						return true;
					}
				}
			} else {
				return false;
			}
		}
	}

	/**
	 * Add product Attribute
	 */
	public function addProductAttribute(Request $request)
	{
		try {
			if (clientPrefrenceModuleStatus('p2p_check')) {

				$product = Product::findOrFail($request->product_id);
				if (!$product) {
					return $this->errorResponse('Product not found', 422);
				}

				if (!empty($request->attribute)) {
					$insert_arr = [];
					$insert_count = 0;
					foreach ($request->attribute as $key => $value) {
						if (!empty($value) && !empty($value['option'] && is_array($value))) {

							if (!empty($value['type']) && $value['type'] == 1) { // dropdown
								$value_arr = @$value['value'];

								foreach ($value['option'] as $key1 => $val1) {

									if (@in_array($val1['option_id'], $value_arr)) {

										$insert_arr[$insert_count]['product_id'] = $request->product_id;
										$insert_arr[$insert_count]['attribute_id'] = $value['id'];
										$insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
										$insert_arr[$insert_count]['attribute_option_id'] = $val1['option_id'];
										$insert_arr[$insert_count]['key_value'] = $val1['option_id'];
										$insert_arr[$insert_count]['is_active'] = 1;
									}
									$insert_count++;
								}
							} else {
								foreach ($value['option'] as $option_key => $option) {
									if (@$option['value']) {
										$insert_arr[$insert_count]['product_id'] = $request->product_id;
										$insert_arr[$insert_count]['attribute_id'] = $value['id'];
										$insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
										$insert_arr[$insert_count]['attribute_option_id'] = $option['option_id'];
										$insert_arr[$insert_count]['key_value'] = $option['value'] ?? $option['option_title'];
										$insert_arr[$insert_count]['is_active'] = 1;

									}
									$insert_count++;
								}
							}
						}


					}
					if (!empty($insert_arr)) {
						ProductAttribute::where('product_id', $request->product_id)->delete();
						ProductAttribute::insert($insert_arr);
					}
				}


				return $this->successResponse([], 'Attribute Added Successfully', 200);
			} else {
				return $this->errorResponse('Attribute option is not enabled', 500);
			}
		} catch (\Exception $ex) {
			return $this->errorResponse('Exception occured', 500);
		}
	}

	/**
	 * Product Attribute list which is save on db of particular product
	 */
	public function getProductAttribute(Request $request)
	{

		try {
			if (clientPrefrenceModuleStatus('p2p_check')) {

				$product = Product::findOrFail($request->product_id);
				if (!$product) {
					return $this->errorResponse('Product not found', 422);
				}

				// Fetch product attribute
				$product_attr = ProductAttribute::where('product_id', $request->product_id)->get();
				return $this->successResponse($product_attr, 'Product Attribute List', 200);
			} else {
				return $this->errorResponse('Attribute option is not enabled', 500);
			}
		} catch (\Exception $e) {
			return $this->errorResponse('Exception occured', 500);
		}

	}

	/**
	 * Attribute List
	 */
	public function availableListOfAttribute(Request $request)
	{
		try {
			if (clientPrefrenceModuleStatus('p2p_check')) {

				if (empty($request->category_id)) {
					return $this->errorResponse('Category not found', 422);
				}

				$productAttributes = Attribute::with('option', 'varcategory.cate.primary')
					->select('attributes.*')
					->join('attribute_categories', 'attribute_categories.attribute_id', 'attributes.id')
					->where('attribute_categories.category_id', $request->category_id)
					->where('attributes.status', '!=', 2)
					->orderBy('position', 'asc')->get();

				return $this->successResponse($productAttributes, 'Attribute List', 200);
			} else {
				return $this->errorResponse('Attribute option is not enabled', 500);
			}
		}
		catch(\Exception $e) {
			return $this->errorResponse('Exception occured', 500);
		}
	}

	/**
	 * Save Product for p2p
	 */

	function addProductWithAttribute(Request $request)
	{
	    
		try {

			$validator = Validator::make($request->all(), [
				// 'sku' => 'required|unique:products',
				// 'url_slug' => 'required|unique:products',
				'image' => 'array|max:10',
				'category_id' => 'required',
				'product_name' => 'required',
				// 'vendor_id'	=>	'required'
			]);

			if ($validator->fails()) {
		

				return $this->errorResponse($validator->errors()->first(), 422);
			}

			$client = Client::orderBy('id', 'asc')->first();
			if (isset($client->custom_domain) && !empty($client->custom_domain) && $client->custom_domain != $client->sub_domain) {
				$sku_url = ($client->custom_domain);
			} else {
				$sku_url = ($client->sub_domain . env('SUBMAINDOMAIN'));
			}

			$slug = str_replace(' ', '-', $request->product_name);
			$generated_slug = $sku_url . '.' . $slug;
			$slug = generateSlug($generated_slug);
			$slug = str_replace(' ', '-', $slug);
			$generated_slug = $sku_url . '.' . $slug;
			$users = Auth::user();
	
			$user = User::where('id',$users->id)->first();
			
			$user_vendor = UserVendor::where('user_id', $users->id)->first();
			
			if(empty($user_vendor)){
                

				$user->assignRole(4); // by default make this user as vendor
				
				$user->is_admin = 1;
				$user->save();

				// Create vendor with default images
				$vendor = new Vendor();
				$vendor->logo = 'default/default_logo.png';
				$vendor->banner = 'default/default_image.png';

				$vendor->status = 1;
				$vendor->show_slot = 0;
				$vendor->name = $user->name;
				$vendor->p2p = 1;
				$vendor->email = $user->email ?? '';
				$vendor->phone_no = $user->phone_number ?? '';
				$vendor->slug = Str::slug($user->name, "-");
				$vendor->save();
				$user_vendor =  UserVendor::create(['user_id' => $user->id, 'vendor_id' => $vendor->id]);
				$user = new User ;
				// $user->createPermissionsUser();
				$p2p_type = Type::where('service_type', 'p2p')->first();
				if( !empty($p2p_type) ) {
					$category_id = Category::where('type_id', $p2p_type->id)->get();
					$categories_ids = [];
					
					if( !empty($category_id) ) {
						foreach($category_id as $key => $val) {
							$categories_ids[] = $val->id;
						}
					}
					$request->request->add(['selectedCategories'=> $categories_ids]);
					
				}
				
				$this->addDataSaveVendor($request, $vendor->id);
				$user_vendor = UserVendor::where('user_id', $users->id)->first();
			}
			if (@$user_vendor->vendor_id) {
				$product = new Product();
				$product->sku = $slug;
				$product->url_slug = $generated_slug;
				$product->title = $request->product_name;
				$product->category_id = $request->category_id;
				$product->description = $request->description ?? '';
				$product->type_id = 1;
				$product->is_live = 1;
				$product->publish_at = date('Y-m-d H:i:s');
				$product->vendor_id = $user_vendor->vendor_id;
				if (@$request->address) {
					$product->address = $request->address;
				  }
					if (@$request->longitude) {
						$product->longitude = $request->longitude;
					}
					if (@$request->latitude) {
						$product->latitude = $request->latitude;
					}


					$client_lang = ClientLanguage::where('is_primary', 1)->first();
					if (!$client_lang) {
						$client_lang = ClientLanguage::where('is_active', 1)->first();
					}

					$client_lang = ClientLanguage::where('is_primary', 1)->first();
					if (!$client_lang) {
						$client_lang = ClientLanguage::where('is_active', 1)->first();
					}
				
					$product->save();
					if ($product->id > 0) {
						$datatrans[] = [
							'title' => $request->product_name ?? null,
							'body_html' => $request->body_html ?? null,
							'meta_title' => '',
							'meta_keyword' => '',
							'meta_description' => $request->meta_description,
							'product_id' => $product->id,
							'language_id' => $client_lang->language_id
						];
						$product_category = new ProductCategory();
						$product_category->product_id = $product->id;
						$product_category->category_id = $request->category_id;
						$product_category->save();
						$proVariant = new ProductVariant();
						$proVariant->price = $request->price ?? 0;
						if (@$request->week_price) {
							$proVariant->week_price = $request->week_price ?? 0;
						}
						if (@$request->month_price) {
							$proVariant->month_price = $request->month_price ?? 0;
						}
						// if (@$request->emirate) {
						// 	$proVariant->emirate = $request->emirate;
						// }
						if (@$request->compare_at_price) {
							$proVariant->compare_at_price = $request->compare_at_price;
						}

						if (@$request->minimum_duration) {
							$proVariant->minimum_duration = $request->minimum_duration * 24;
						}

						$proVariant->sku = $slug;
						$proVariant->title = $slug . '-' . empty($request->product_name) ? $slug : $request->product_name;
						$proVariant->product_id = $product->id;
						$proVariant->quantity = 1;
						$proVariant->status = 1;
						$proVariant->barcode = $this->generateBarcodeNumber();
						$proVariant->save();
						ProductTranslation::insert($datatrans);
						
			         

						$product_detail = Product::where('id', $product->id)->firstOrFail();

						$data = ['product_detail' => $product_detail];


						// Upload Image
						if ($request->has('file')) {
							$imageId = '';
							$files = $request->file('file');
							if (is_array($files)) {
								foreach ($files as $file) {
									$img = new VendorMedia();
									$img->media_type = 1;
									$img->vendor_id = $product->vendor_id;
									$img->path = Storage::disk('s3')->put($this->folderName, $file, 'public');
									$img->save();
									$path1 = $img->path['proxy_url'] . '40/40' . $img->path['image_path'];
									if ($img->id > 0) {
										$imageId = $img->id;
										$image = new ProductImage();
										$image->product_id = $product->id;
										$image->is_default = 1;
										$image->media_id = $imageId;
										$image->save();
										// if($image->id > 0 && $variant_id!="")
										// {
										// 	$varientimage = new ProductVariantImage();
										// 	$varientimage->product_variant_id = $variant_id;
										// 	$varientimage->product_image_id = $image->id;
										// 	$varientimage->save();
										// }
									}
								}
								//return response()->json(['htmlData' => $resp]);
							} else {
								$img = new VendorMedia();
								$img->media_type = 1;
								$img->vendor_id = $product->vendor_id;
								$img->path = Storage::disk('s3')->put($this->folderName, $files, 'public');
								$img->save();
								if ($img->id > 0) {
									$imageId = $img->id;
									$image = new ProductImage();
									$image->product_id = $product->id;
									$image->is_default = 1;
									$image->media_id = $img->id;
									$image->save();
									// if($image->id > 0 && $variant_id!="")
									// {
									// 	$varientimage = new ProductVariantImage();
									// 	$varientimage->product_variant_id = $variant_id;
									// 	$varientimage->product_image_id = $image->id;
									// 	$varientimage->save();
									// }
								}
							}
						}


						if ($request->has('file_360')) {
							$imageId = '';
							$files = $request->file('file_360');
							if (is_array($files)) {
								foreach ($files as $file) {
									$img = new VendorMedia();
									$img->media_type = 4;
									$img->vendor_id = $product->vendor_id;
									$img->path = Storage::disk('s3')->put($this->folderName, $file, 'public');
									$img->save();
									$path1 = $img->path['proxy_url'] . '40/40' . $img->path['image_path'];
									if ($img->id > 0) {
										$imageId = $img->id;
										$image = new ProductImage();
										$image->product_id = $product->id;
										$image->is_default = 1;
										$image->media_id = $imageId;
										$image->save();
										// if($image->id > 0 && $variant_id!="")
										// {
										// 	$varientimage = new ProductVariantImage();
										// 	$varientimage->product_variant_id = $variant_id;
										// 	$varientimage->product_image_id = $image->id;
										// 	$varientimage->save();
										// }
									}
								}
								//return response()->json(['htmlData' => $resp]);
							} else {
								$img = new VendorMedia();
								$img->media_type = 4;
								$img->vendor_id = $product->vendor_id;
								$img->path = Storage::disk('s3')->put($this->folderName, $files, 'public');
								$img->save();
								if ($img->id > 0) {
									$imageId = $img->id;
									$image = new ProductImage();
									$image->product_id = $product->id;
									$image->is_default = 1;
									$image->media_id = $img->id;
									$image->save();
									// if($image->id > 0 && $variant_id!="")
									// {
									// 	$varientimage = new ProductVariantImage();
									// 	$varientimage->product_variant_id = $variant_id;
									// 	$varientimage->product_image_id = $image->id;
									// 	$varientimage->save();
									// }
								}
							}
						}

					// Add Attributes
					if( checkTableExists('product_attributes') ) {
						if( !empty($request->attribute) ) {
							$attribute = json_decode($request->attribute, true);
                         
							if( !empty($attribute) ) {

								$insert_arr = [];
								$insert_count = 0;

								foreach($attribute as $key => $value) {

									if( !empty($value) && !empty($value['option'] && is_array($value) )) {

										if(!empty($value['type']) && $value['type'] == 1 ) { // dropdown
											$value_arr = @$value['value'];

											foreach( $value['option'] as $key1 => $val1 ) {
												if( @in_array($val1['option_id'], $value_arr) ) {
													$insert_arr[$insert_count]['product_id'] = $product->id;
													$insert_arr[$insert_count]['attribute_id'] = $value['id'];
													$insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
													$insert_arr[$insert_count]['attribute_option_id'] = $val1['option_id'];
													$insert_arr[$insert_count]['key_value'] = $val1['option_id'];
													$insert_arr[$insert_count]['latitude'] = null;
													$insert_arr[$insert_count]['longitude'] = null;
													$insert_arr[$insert_count]['is_active'] = 1;
												}
												$insert_count++;
											}
										}
										else {
											$value_arr = @$value['value'];

											foreach($value['option'] as $option_key => $option) {
												if(!empty($value['type']) && $value['type'] == 4 ) { // textbox
													$insert_arr[$insert_count]['product_id'] = $product->id;
													$insert_arr[$insert_count]['attribute_id'] = $value['id'];
													$insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
													$insert_arr[$insert_count]['attribute_option_id'] = $option['option_id'];
													$insert_arr[$insert_count]['key_value'] = (!empty($value['value']) && !empty($value['value'][0]) ? $value['value'][0] : '');
													$insert_arr[$insert_count]['latitude'] = null;
													$insert_arr[$insert_count]['longitude'] = null;
													$insert_arr[$insert_count]['is_active'] = 1;
												}
												elseif(!empty($value['type']) && $value['type'] == 6) {

													$insert_arr[$insert_count]['product_id'] = $product->id;
													$insert_arr[$insert_count]['attribute_id'] = $value['id'];
													$insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
													$insert_arr[$insert_count]['attribute_option_id'] = $option['option_id'];
													$insert_arr[$insert_count]['key_value'] = $value['address'];
													$insert_arr[$insert_count]['latitude'] = $value['latitude'] ?? null;
													$insert_arr[$insert_count]['longitude'] = $value['longitude'] ?? null;
													$insert_arr[$insert_count]['is_active'] = 1;
												}
												elseif( @in_array($option['option_id'], $value_arr) ) {
													$insert_arr[$insert_count]['product_id'] = $product->id;
													$insert_arr[$insert_count]['attribute_id'] = $value['id'];
													$insert_arr[$insert_count]['key_name'] = $value['attribute_title'];
													$insert_arr[$insert_count]['attribute_option_id'] = $option['option_id'];
													$insert_arr[$insert_count]['key_value'] = $option['option_id'];
													$insert_arr[$insert_count]['latitude'] = $value['latitude'] ?? null;
													$insert_arr[$insert_count]['longitude'] = $value['longitude'] ?? null;
													$insert_arr[$insert_count]['is_active'] = 1;
												}
											}
										}


									}
									// if (!empty($insert_arr)) {
									// 	ProductAttribute::where('product_id', $request->product_id)->delete();
									// 	ProductAttribute::insert($insert_arr);
									// }


								}

								
								if( !empty($insert_arr) ) {
									ProductAttribute::where('product_id',$request->product_id)->delete();
									ProductAttribute::insert($insert_arr);
								}
							}
						}
						if (@$request->date_availability) {	
							$date = [];
							$date = json_decode($request->date_availability);	
							foreach ($date as $date_availability) {
								$date_availability_data[] = [
									'product_id' => $product->id,
									'date_time' => $date_availability,
									'not_available' => 0,
									'created_at' => Carbon::now(),
									'updated_at' => Carbon::now()
								];
							}
							if (@$date_availability_data) {
								ProductAvailability::insert($date_availability_data);
							}

						}

						$this->sendNotification($user->id);

						return $this->successResponse($data, 'Product added successfully!', 200);
					} else {
						return $this->errorResponse('Sorry, You are not a vendor.', 500);
					}
				} else {
					return $this->errorResponse('Sorry, You are not a vendor.', 500);
				}
			
			
		} 
		
		} catch (\Exception $e) {
			return $this->errorResponse('Exception occured', 500);
		}

}

public function addDataSaveVendor(Request $request, $vendor_id){

	$vendor = Vendor::where('id', $vendor_id)->firstOrFail();
	$VendorController = new VendorController();

	$request->merge(["return_json"=>1]);
	
	$VendorConfigrespons = $VendorController->updateConfig($request,'',$vendor_id)->getData();//$this->updateConfig($vendor_id);
   // pr($VendorConfigrespons);
	if($request->has('can_add_category')){
		$vendor->add_category = $request->can_add_category == 'on' ? 1 : 0;
	}
	if ($request->has('assignTo')) {
		$vendor->vendor_templete_id = $request->assignTo;
	}

	$vendor->save();
	if($request->has('category_ids')){
		foreach($request->category_ids as $category_id){
			VendorCategory::create(['vendor_id' => $vendor_id, 'category_id' => $category_id, 'status' => '1']);
		}
	}
	if($request->has('selectedCategories')){
		foreach($request->selectedCategories as $category_id){
			VendorCategory::create(['vendor_id' => $vendor_id, 'category_id' => $category_id, 'status' => '1']);
		}
	}
	return response()->json([
		'status' => 'success',
		'message' => 'Vendor created Successfully!',
		'data' => $VendorConfigrespons
	]);
	// pr($VendorConfigrespons);
}



	public function destroy(Request $request)
		{
			$product = Product::find($request->id);
			$current_date = date('Y-m-d');
			if (!empty($product)) {
				$order_vendor_product = OrderProduct::where('product_id',$request->id)->get();
				if(!empty($order_vendor_product)){
					foreach($order_vendor_product as $orders){
					if($orders->end_date_time > $current_date){
						return $this->errorResponse('You Cannot Delete this Product Becuase it is Booked For Future Dates.',500);
					}
					}
				}
				$delete_product = Product::where('id', $request->id)->delete();
				return $this->successResponse($delete_product, 'Product Has been Deleted Sucessfully!', 200);
			} else {
				return $this->errorResponse('Product Not Found', 500);
			}
		}
public function sendNotification($user_id){
	$devices = UserDevice::whereNotNull('device_token')
	->where('user_id', $user_id)
	->pluck('device_token')
	->toArray();

	if (empty($devices)) {
		return true;
	}
	$client_preferences = ClientPreference::select('fcm_server_key', 'favicon', 'vendor_fcm_server_key')->first();
	$from = (!empty($client_preferences->fcm_server_key)) ? $client_preferences->fcm_server_key : '';

	$notification_content = NotificationTemplate::where('id', 16)->first();
	$title = $notification_content ? $notification_content->subject : "New Listing Created";
	$body_content = $notification_content ? $notification_content->content : "Yay! Your product has been successfully listed. View your product under Account->My Posts";

	$data = [
		"registration_ids" => $devices,
		"notification" => [
			'title' => $title,
			'body'  => $body_content,
			'sound' => "notification.wav",
			"icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
			"android_channel_id" => "sound-channel-id"
		],
		"data" => [
			'title' => $title,
			'body'  => $body_content,
			'type' => "order_status_change"
		],
		"priority" => "high"
	];
	if (!empty($from)) {
		// helper function
		sendFcmCurlRequest($data);
		$notification = new Notification();
		$notification->order_id = 0;
		$notification->title = $title;
		$notification->message = $body_content;
		$notification->user_id = $user_id;
		$notification->notification_template_id = 16;
		$notification->save();
	}
}

public function send_notification(Request $request)
    {


        if(empty($request->user_id)){
            $user = User::where('id',$request->user_id)->first();
        }

        $devices = UserDevice::where('user_id', 2)->pluck('device_token')->toArray();

        if (!empty($devices))
        {
            $from = '';
            $client_preferences = ClientPreference::select('fcm_server_key', 'favicon', 'vendor_fcm_server_key')->first();

            if (!empty($devices) && !empty($client_preferences->fcm_server_key)) {
                $from = $client_preferences->fcm_server_key;
            }

            $notification_content = "User raise a issue in chat";
            if ($notification_content && empty($vendorIds)) {
                $body_content =  $notification_content ;
				$title = "User raise a issue";
            }else{
                $title = "User raise a issue";
                $body_content = "Raise a Issue";
            }
                $data = [
                    "registration_ids" => $devices,
                    "notification" => [
                        'title' => $title,
                        'body'  => $body_content,
                        'sound' => "notification.wav",
                        "icon" => (!empty($client_preferences->favicon)) ? $client_preferences->favicon['proxy_url'] . '200/200' . $client_preferences->favicon['image_path'] : '',
                        //'click_action' => route('order.index'),
                        "android_channel_id" => "sound-channel-id"
                    ],
                    "data" => [
                        'title' => $title,
                        'body'  => $notification_content,
                        'type' => "raise_issue"
                    ],
                    "priority" => "high"
                ];

                if(!empty($from)){

                    // helper function
                    sendFcmCurlRequest($data);
					return true;
                }
        }else{
            return false;
        }
    }

}
