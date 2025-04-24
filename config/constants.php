<?php
return [
  'MONTHS' => [
    1 => 'Jan',
    2 => 'Feb',
    3 => 'Mar',
    4 => 'Apr',
    5 => 'May',
    6 => 'Jun',
    7 => 'Jul',
    8 => 'Aug',
    9 => 'Sep',
    10 => 'Oct',
    11 => 'Nov',
    12 => 'Dec'
  ],
  'VendorTypes' => [
    'delivery'     => 'Delivery',           // Delivery of the order will be sent to the ccustomer.
    'dinein'       => 'Dine-In',            // Customer can order and dine in the restaurant.
    'takeaway'     => 'Takeaway',           // Customer can order and take there meal along with them.
    'rental'       => 'Rentals',            // Products which are available for rents will be mentioned in this flow.
    'pick_drop'    => 'Pick & Drop',        // Rides or pickup delivery products will be shown in this flow.
    'on_demand'    => 'Services',           // Services that are available any time you want to use it. 
    'laundry'      => 'Laundry',            // Laundry related products are mentioned in this flow.
    'appointment'  => 'Appointment',        // appointment related products are mentioned in this flow.
    'p2p'          => 'P2P',
    'car_rental'   => 'Car-Rental'
 
  ],
  // VendorTypes database
  // add these fields in table (client_preferences) rental_check,pick_check,on_demand_check,laundry_check
  // add these fields in table (vendors) rental,pick_drop,on_demand,laundry
  // add these fields in table (vendor_slots) rental,on_demand,on_demand,laundry,appointment

  'VendorTypesIcon' => [
    'delivery'     => 'deliveryicon',           // Delivery of the order will be sent to the ccustomer.
    'dinein'       => 'dineinicon',             // Customer can order and dine in the restaurant.
    'takeaway'     => 'takewayicon',               // Customer can order and take there meal along with them.
    'pick_drop'    => 'pick_dropicon',          // Rides or pickup delivery products will be shown in this flow.
    'rental'       => 'rentalicon',             // Products which are available for rents will be mentioned in this flow.
    'on_demand'    => 'on_demandicon',         // Services that are available any time you want to use it. 
    'laundry'      => 'laundryicon',            // Laundry related products are mentioned in this flow.
    'appointment'  => 'appointmenticon',        // appointment related products are mentioned in this flow.
    'p2p'          => 'p2picon',
    'car_rental'   => 'car_rentalicon'

  ],
  /**  
   * these service types for category type 
   * 
   * */
  'ServiceTypes' => [
    'products_service'   => "Products Service", // 
    'rental_service'     => "Rental Service",
    'pick_drop_service'  => "Pick and Drop Service",
    'on_demand_service'  => "Services",
    'laundry_service'    => "Laundry Service",
    'appointment_service'=> "Appointment Service",
  ],
  'VendorTypesLuxuryOptions' => [
    'delivery'     => '1',           // Delivery of the order will be sent to the ccustomer.
    'dinein'       => '2',            // Customer can order and dine in the restaurant.
    'takeaway'     => '3',           // Customer can order and take there meal along with them.
    'rental'       => '4',            // Products which are available for rents will be mentioned in this flow.
    'pick_drop'    => '5',        // Rides or pickup delivery products will be shown in this flow.
    'on_demand'    => '6', // Services that are available any time you want to use it. 
    'laundry'      => '7',            // Laundry related products are mentioned in this flow.
    'appointment'  => '8',  
    'p2p'  => '9',
    'car_rental' => '10',
  ],
  'Period' =>[
    'days'     => 'Daily',
    'week'     => 'Weekly',
    'months'   => 'Monthly',
  ],
  'weekDay' => [
    '1' => "Sunday",
    '2' => "Monday",
    '3' => "Tuesday",
    '4' => "Wednesday",
    '5' => "Thursday",
    '6' => "Friday",
    '7' => "Saturday",
  ],
  'onDemandPricingType' => [
    'vendor'     => 'Vendor Service',           // Delivery of the order will be sent to the ccustomer.
    'freelancer' => 'Freelancer Service',             // Customer can order and dine in the restaurant.
    

  ],
];
