<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorOrderProductDispatcherStatus extends Model
{
    use HasFactory;
    protected $fillable = [
        'dispatcher_id', 'order_id', 'dispatcher_status_option_id', 'vendor_id','type','order_vendor_product_id','order_product_route_id','order_status_option_id','long_term_schedule_id'
    ];

    protected $appends = ['status_data'];
    public function getStatusDataAttribute()
    {
       $dispatcher_status_option = $this->attributes['dispatcher_status_option_id'];
       $type = $this->attributes['type'];
       $order_id = $this->attributes['order_id'];
       $vendor_id = $this->attributes['vendor_id'];
       
       $order = Order::with(['vendors.products.product.category.categoryDetail.type:id,title'])
       ->with('vendors', function ($query) use ($vendor_id) {
            if (!empty($vendor_id)) {
                $query->where('vendor_id', $vendor_id);
            }
       })->find($order_id);
       $productcategorytype =   '';
       $isLongTerm = 0;
       if($order){
           if(((isset($order)) && @$order->is_long_term ==1 )){
            $productcategorytype =   @$order->vendors[0]->products->first()->LongTermService->product->category->categoryDetail->type->title ;
            $isLongTerm = 1;
           }else{
               $productcategorytype =  @$order->vendors[0]->products[0]->product->category->categoryDetail->type->title ;
           }
       }

       $status_data = [];

       switch ($dispatcher_status_option) {
        case 1:
            if ($type == '1') {
                $status_data['icon'] = asset('assets/icons/driver_1_1.png');
                if($productcategorytype == "On Demand Service" || $productcategorytype == "Appointment" || ($isLongTerm==1) ):
                    $status_data['driver_status'] = __('Service Accepted');
                else:
                    $status_data['driver_status'] = __('Order Accepted');
                endif;
            } else {
                
            }
        break;
        case 2:
            
            if ($type == '1') {
                $status_data['icon'] = asset('assets/icons/driver_2_1.png');
                if($productcategorytype == "On Demand Service" || $productcategorytype == "Appointment" || ($isLongTerm==1) ):
                    $status_data['driver_status'] = __('Service Executive Assigned');
                elseif($productcategorytype == "Product" || $productcategorytype == "Vendor" || $productcategorytype == "Subcategory" || $productcategorytype == "Brand"):
                    $status_data['driver_status'] = __('Delivery Executive Assigned');
                elseif($productcategorytype == "Pickup/Parent" || $productcategorytype == "Pickup/Delivery"):
                    $status_data['driver_status'] = __('Driver Assigned');
                elseif($productcategorytype == "Laundry"):
                    $status_data['driver_status'] = __('Service Executive Assigned');
                else:
                    $status_data['driver_status'] = __('Delivery Executive Assigned');
                endif;
            } else {
                
            }
        
        break;
        case 3:
            
            if ($type == '1') {
                $status_data['icon'] = asset('assets/icons/driver_3_1.png');
                if($productcategorytype == "On Demand Service" || $productcategorytype == "Appointment" || ($isLongTerm==1)):
                    $status_data['driver_status'] = __('Service Executive heading to you');
                elseif($productcategorytype == "Product" || $productcategorytype == "Vendor" || $productcategorytype == "Subcategory" || $productcategorytype == "Brand"):
                    $status_data['driver_status'] = __('Delivery Executive heading to the store');
                elseif($productcategorytype == "Pickup/Parent" || $productcategorytype == "Pickup/Delivery"):
                    $status_data['driver_status'] = __('Driver heading to the pickup location');
                elseif($productcategorytype == "Laundry"):
                    $status_data['driver_status'] = __('Service Executive heading to you');
                else:
                    $status_data['driver_status'] = __('Delivery Executive heading to the store');
                endif;
            } else {
                $status_data['icon'] = asset('assets/icons/driver_3_2.png');
                if($productcategorytype == "On Demand Service" || $productcategorytype == "Appointment" || ($isLongTerm==1)):
                    $status_data['driver_status'] = __('Service Executive arrived at your location');
                elseif($productcategorytype == "Product" || $productcategorytype == "Vendor" || $productcategorytype == "Subcategory" || $productcategorytype == "Brand"):
                    $status_data['driver_status'] = __('Delivery Executive heading to you');
                elseif($productcategorytype == "Pickup/Parent" || $productcategorytype == "Pickup/Delivery"):
                    $status_data['driver_status'] = __('Driver heading to dropoff location');
                elseif($productcategorytype == "Laundry"):
                    $status_data['driver_status'] = __('Service Executive heading to the store');
                else:
                    $status_data['driver_status'] = __('Delivery Executive heading to you');                    
                endif;
            }
            
        break;
        case 4:
            if ($type == '1') {
                $status_data['icon'] = asset('assets/icons/driver_4_1.png');
                if($productcategorytype == "On Demand Service" || $productcategorytype == "Appointment" || ($isLongTerm==1)):
                    $status_data['driver_status'] = __('Service Executive reaching at your location soon');
                elseif($productcategorytype == "Product" || $productcategorytype == "Vendor" || $productcategorytype == "Subcategory" || $productcategorytype == "Brand"):
                    $status_data['driver_status'] = __('Delivery Executive arrived at store');
                elseif($productcategorytype == "Pickup/Parent" || $productcategorytype == "Pickup/Delivery"):
                    $status_data['driver_status'] = __('Driver arrived at pickup location');
                elseif($productcategorytype == "Laundry"):
                    $status_data['driver_status'] = __('Service Executive arrived at your location');
                else:
                    $status_data['driver_status'] = __('Delivery Executive arrived at your location');
                endif;

            }else{
                $status_data['icon'] = asset('assets/icons/driver_4_2.png');
                if($productcategorytype == "On Demand Service" || $productcategorytype == "Appointment" || ($isLongTerm==1)):
                    $status_data['driver_status'] = __('Service Under Process');
                elseif($productcategorytype == "Product" || $productcategorytype == "Vendor" || $productcategorytype == "Subcategory" || $productcategorytype == "Brand"):
                    $status_data['driver_status'] = __('Delivery Executive arrived at your location');
                elseif($productcategorytype == "Pickup/Parent" || $productcategorytype == "Pickup/Delivery"):
                    $status_data['driver_status'] = __('Driver arrived at dropoff location');
                elseif($productcategorytype == "Laundry"):
                    $status_data['driver_status'] = __('Service Executive arrived at the store');
                else:
                    $status_data['driver_status'] = __('Delivery Executive arrived at your location');
                endif;
            }
            
        break;
        case 5:
            if ($type == '1') {
                $status_data['icon'] = asset('assets/icons/driver_5_1.png');
                if($productcategorytype == "On Demand Service" || $productcategorytype == "Appointment" || ($isLongTerm==1)):
                    $status_data['driver_status'] = __('Service Executive is nearby your location');
                elseif($productcategorytype == "Product" || $productcategorytype == "Vendor" || $productcategorytype == "Subcategory" || $productcategorytype == "Brand"):
                    $status_data['driver_status'] = __('Order picked up');
                elseif($productcategorytype == "Pickup/Parent" || $productcategorytype == "Pickup/Delivery"):
                    $status_data['driver_status'] = __('Ride Started');
                elseif($productcategorytype == "Laundry"):
                    $status_data['driver_status'] = __('Order picked up');
                else:
                    $status_data['driver_status'] = __('Order picked up');
                endif;
            }else{
                $status_data['icon'] = asset('assets/icons/driver_5_2.png');
                if($productcategorytype == "On Demand Service" || $productcategorytype == "Appointment" || ($isLongTerm==1) ):
                    $status_data['driver_status'] = __('Service Completed');
                elseif($productcategorytype == "Product" || $productcategorytype == "Vendor" || $productcategorytype == "Subcategory" || $productcategorytype == "Brand"):
                    $status_data['driver_status'] = __('Order Delivered');
                elseif($productcategorytype == "Pickup/Parent" || $productcategorytype == "Pickup/Delivery"):
                    $status_data['driver_status'] = __('Ride Completed');
                elseif($productcategorytype == "Laundry"):
                    $status_data['driver_status'] = __('Service Completed');
                else:
                    $status_data['driver_status'] = __('Delivery Completed');
                endif;
            }
            
        break;
        default:
        $status_data['icon'] = '';
        $status_data['driver_status'] = '';
       }

       return $status_data;

    }

   
}
