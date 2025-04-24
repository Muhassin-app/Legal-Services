<?php
namespace App\Http\Traits;
use Square\SquareClient;
use Square\Environment;
use Square\Exceptions\ApiException;
use Ramsey\Uuid\Uuid;
use App\Models\{Product, Variant, TaxCategory, Client, ProductVariantSet, ClientPreference, ProductTranslation, ClientLanguage, ProductVariant, ClientCurrency, TaxRate, AddonSet, AddonOption, SquareTimestamp};
use Auth, Log, DB;
use Carbon\Carbon;
trait SquareInventoryManager{

  private $application_id;
  private $access_token;
  private $location_id;
  private $square_enable_status;
  private $sandbox_enable_status;
  private $ClientPreference;

  public function init()
  {
    $this->ClientPreference      = ClientPreference::with(['primary'])->first();
    $getAdditionalPreference     = getAdditionalPreference(['square_enable_status', 'square_credentials', 'is_tax_price_inclusive']);
    $this->ClientPreference->is_tax_price_inclusive = $getAdditionalPreference['is_tax_price_inclusive'];
    $square_credentials          = json_decode($getAdditionalPreference['square_credentials'], true);
    $this->square_enable_status  = (int) $getAdditionalPreference['square_enable_status'];
    $this->sandbox_enable_status = (int) isset($square_credentials['sandbox_enable_status']) ? $square_credentials['sandbox_enable_status'] : 0;
    $this->application_id        = isset($square_credentials['application_id']) ? $square_credentials['application_id'] : '';
    $this->access_token          = isset($square_credentials['access_token']) ? $square_credentials['access_token'] : '';
    $this->location_id           = isset($square_credentials['location_id']) ? $square_credentials['location_id'] : '';
    if($this->square_enable_status == 1)
    {
      $config = [
          'accessToken' => $this->access_token,
          'environment' => ($this->sandbox_enable_status == 1) ? 'sandbox' : 'production',
      ];
      return new SquareClient($config);
    }
  }

  public function createOrUpdateProductInSquarePos($product_id)
  {
    //------init square client
    $client = $this->init();
    if($this->square_enable_status == 1)
    {
      DB::beginTransaction();
      try{
        $product = Product::with(['media.image', 'primary', 'category.cat', 'vendor','brand','variant', 'variant.set', 'variantSets', 'taxCategory.taxRate', 
        'sets.addOnName'])->select('id', 'sku', 'is_live', 'has_variant', 'tax_category_id', 'square_item_id', 'square_item_version')
        ->where('id', $product_id)->where('is_live', 1)->first();
        if(!empty($product))
        {
          
          //------https://developer.squareup.com/docs/catalog-api/build-with-catalog
          
          $square_item_id = !empty($product->square_item_id) ? $product->square_item_id : '#ITEM_'.$product->id;
          $object_ids[] = $square_item_id;
  
          $taxrate = $taxrateid = 0;
          $square_tax_id = '';
          if(!empty($product->taxCategory)){
            foreach($product->taxCategory->taxRate as $taxes){
              if($taxrate == 0){
                $taxrate   = $taxes->tax_rate;
                $taxrateid = $taxes->id;
                $square_tax_id = !empty($taxes->square_tax_id) ? $taxes->square_tax_id : '#TAX_'.$taxes->id;
                $object_ids[] = $square_tax_id;
              }
            }
          }
          
          //------get versions if items already exists
          $object_versions = $this->getItemVersionFromSquarePos($object_ids);
  
          if(!isset($object_versions[$square_item_id]))
          {
            $square_item_id = '#ITEM_'.$product->id;
          }
  
  
          //------item variant object creation starts here
          $variations = [];
          foreach($product->variant as $proVariant){
            
            $price_money = new \Square\Models\Money();
            $price_money->setAmount(($proVariant->price ?? 0.00) * 100);
            $price_money->setCurrency($this->ClientPreference->primary->currency->iso_code ?? 'USD');
  
            if(isset($proVariant->set[0])){
              $setVName = $proVariant->set[0]->title;
            }else{
              $setVName = $product->primary->title ?? $product->title;
            }
  
            $square_variant_id = (!empty($proVariant->square_variant_id) && isset($object_versions[$proVariant->square_variant_id])) ? $proVariant->square_variant_id : '#ITEM_VARIATION_'.$proVariant->id;
            
            $item_variation_data = new \Square\Models\CatalogItemVariation();
            $item_variation_data->setItemId($square_item_id);
            $item_variation_data->setName($setVName);
            $item_variation_data->setSku(!empty($proVariant->sku) ? $proVariant->sku : $product->sku);
            $item_variation_data->setPricingType('FIXED_PRICING');//------https://developer.squareup.com/reference/square/enums/CatalogPricingType
            $item_variation_data->setPriceMoney($price_money);//------https://developer.squareup.com/reference/square/objects/Money
            if($product->has_inventory == 0)
            {
              $item_variation_data->setTrackInventory(true);
            }
            $item_variation_data->setStockable(true);
            $item_variation_data->setSellable(true);
            
  
            $catalog_object = new \Square\Models\CatalogObject('ITEM_VARIATION', $square_variant_id);
            if(isset($object_versions[$square_variant_id]) && $object_versions[$square_variant_id] !='')
            {
              $catalog_object->setVersion($object_versions[$square_variant_id]);
            }
            $catalog_object->setItemVariationData($item_variation_data);
            $variations[] = $catalog_object;
          }
          //------item variant object creation ends here
  
          
          //------item object creation starts here
          $item_data = new \Square\Models\CatalogItem();
          $item_data->setName($product->primary->title ?? $product->title);
          $item_data->setVariations($variations);
          $item_data->setProductType('REGULAR');//------https://developer.squareup.com/reference/square/enums/CatalogItemProductType
  
          $catalog_object = new \Square\Models\CatalogObject('ITEM', $square_item_id);
          if(isset($object_versions[$square_item_id]) && $object_versions[$square_item_id]!=''){
            $catalog_object->setVersion($object_versions[$square_item_id]);
          }
          $catalog_object->setItemData($item_data);
          $objects[] = $catalog_object;
          //------item object creation ends here
  
  
          //------tax object creation starts here
          if($taxrate > 0){
              $tax_data = new \Square\Models\CatalogTax();//------https://developer.squareup.com/reference/square/objects/CatalogTax
              $tax_data->setName($product->taxCategory->title);
              $tax_data->setCalculationPhase('TAX_SUBTOTAL_PHASE');
              $tax_data->setInclusionType(($this->ClientPreference->is_tax_price_inclusive==1) ? 'INCLUSIVE' : 'ADDITIVE');
              $tax_data->setPercentage($taxrate);
      
              $catalog_object3 = new \Square\Models\CatalogObject('TAX', $square_tax_id);
              if(isset($object_versions[$square_tax_id]) && $object_versions[$square_tax_id]!=''){
                $catalog_object3->setVersion($object_versions[$square_tax_id]);
              }
              $catalog_object3->setTaxData($tax_data);
              $objects[] = $catalog_object3;
          }
          //------tax object creation ends here
        
  
          $catalog_object_batch = new \Square\Models\CatalogObjectBatch($objects);
          $batches = [$catalog_object_batch];
            
          $uniqueid = Uuid::uuid4();
          $body = new \Square\Models\BatchUpsertCatalogObjectsRequest($uniqueid, $batches);
          $api_response = $client->getCatalogApi()->batchUpsertCatalogObjects($body);
  
          if($api_response->isSuccess()) {
            $resultObject = $api_response->getResult()->getObjects();
            
            foreach($resultObject as $resultobjectdata){
              //------update squarepos item/version/tax id and version in respective table
              if($resultobjectdata->getType() == "ITEM"){
  
                Product::where('id', $product->id)->update(['square_item_id' => $resultobjectdata->getId(), 'square_item_version' => $resultobjectdata->getVersion()]);
                if(!empty($product->sets)){
                  $modifierids = [];
                  foreach($product->sets as $modifierset){
                    $modifierids[] = !empty($modifierset->addOnName->square_modifier_id) ? $modifierset->addOnName->square_modifier_id : '';
                  }
                  $modify = $this->applyModifierToItemSquare([$resultobjectdata->getId()], $modifierids);
                }
                
                foreach($resultobjectdata->getItemData()->getVariations() as $variantData){
                  if($variantData->getType() == "ITEM_VARIATION" && $variantData->getItemVariationData()->getSku()!=''){
                    $variant = ProductVariant::where('product_id', $product->id)->where('sku', '=', $variantData->getItemVariationData()->getSku())->first();
                    ProductVariant::where('product_id', $product->id)->where('sku', '=', $variantData->getItemVariationData()->getSku())->update(['square_variant_id' => $variantData->getId(), 'square_variant_version' => $variantData->getVersion()]);
                    if(!empty($variant)){
                      $inventoryupdate = $this->inventoryAdjustmentInSquarePos($variantData->getId(), $variant->quantity, "PHYSICAL_COUNT", "IN_STOCK");
                    }
                  }
                }
              }
  
              if($resultobjectdata->getType() == "TAX" && $taxrateid > 0){
                TaxRate::where('id', $taxrateid)->update(['square_tax_id' => $resultobjectdata->getId(), 'square_tax_version' => $resultobjectdata->getVersion()]);
              }
  
            }
            DB::commit();
            return response()->json([
                'status'  => 'success',
                'result'  => '',
                'message' => __('product created/updated in Square.')
            ]);
          } 
          else 
          {
            DB::rollback();
            $errors = $api_response->getErrors();
            return response()->json([
              'status'  => 'error',
              'result'  => '',
              'message' => __('There is some error while creating Item, tax and variant in Square.')
            ]);
          }
          
        }
        else
        {
          DB::rollback();
          return response()->json([
            'status'  => 'error',
            'result'  => [],
            'message' => __('product and its variants not found.')
          ]);
        }
      } 
      catch (ApiException $e) 
      {
        DB::rollback();
        return response()->json([
          'status'  => 'error',
          'result'  => [],
          'message' => $e->getMessage()
        ]);
      }
    } 
  }

  public function createOrUpdateModifiersSquare($addOnid)
  {
    //------init square client
    $client = $this->init();

    if($this->square_enable_status == 1)
    {
      DB::beginTransaction();
      try{
        $addOn = AddonSet::with(['primary', 'option.translation_one'])->where('id', $addOnid)->first();
        
        if(!empty($addOn)){
          
          //------item addon object creation starts here
          $square_modifier_id = !empty($addOn->square_modifier_id) ? $addOn->square_modifier_id : '#modifier_list';
          $object_versions = $this->getItemVersionFromSquarePos([$square_modifier_id]);
          
          $modifiers = [];
          
          foreach($addOn->option as $addonOptionData)
          {
            $price_money = new \Square\Models\Money();
            $price_money->setAmount(($addonOptionData->price ?? 0.00) * 100);
            $price_money->setCurrency($this->ClientPreference->primary->currency->iso_code ?? 'USD');

            $setVName = !empty($addonOptionData->translation_one) ? $addonOptionData->translation_one->title : $addonOptionData->title;
            
            $modifier_data = new \Square\Models\CatalogModifier();
            $modifier_data->setName($setVName);
            $modifier_data->setPriceMoney($price_money);
            $modifier_data->setModifierListId($square_modifier_id);

            $square_modifier_option_id = !empty($addonOptionData->square_modifier_option_id) ? $addonOptionData->square_modifier_option_id : '#MODIFIER_'.$addonOptionData->id;
            $catalog_object = new \Square\Models\CatalogObject('MODIFIER', $square_modifier_option_id);

            if(isset($object_versions[$square_modifier_option_id]) && $object_versions[$square_modifier_option_id] !='')
            {
              $catalog_object->setVersion($object_versions[$square_modifier_option_id]);
            }
            $catalog_object->setModifierData($modifier_data);
            $modifiers[] = $catalog_object;
          }
      
          $modifier_list_data = new \Square\Models\CatalogModifierList();
          $modifier_list_data->setName($addOn->primary->title);
          $modifier_list_data->setSelectionType(($addOn->max_select > 1) ? 'MULTIPLE' : 'SINGLE');
          $modifier_list_data->setModifiers($modifiers);

          $object       = new \Square\Models\CatalogObject('MODIFIER_LIST', $square_modifier_id);
          if(isset($object_versions[$square_modifier_id]) && $object_versions[$square_modifier_id] !='')
          {
            $object->setVersion($object_versions[$square_modifier_id]);
          }
          $object->setModifierListData($modifier_list_data);

          $uniqueid     = Uuid::uuid4();
          $body         = new \Square\Models\UpsertCatalogObjectRequest($uniqueid, $object);

          $api_response = $client->getCatalogApi()->upsertCatalogObject($body);

          if($api_response->isSuccess()) {
            $result = $api_response->getResult();
            
            $resultObject = $api_response->getResult()->getCatalogObject();
            
            if($resultObject->getType() == "MODIFIER_LIST"){

              AddonSet::where('id', $addOn->id)->update(['square_modifier_id' => $resultObject->getId()]);
              
              foreach($resultObject->getModifierListData()->getModifiers() as $modifierData){
                if($modifierData->getType() == "MODIFIER"){
                  $modifierName = $modifierData->getModifierData()->getName();
                  AddonOption::where('addon_id', $addOn->id)
                              ->where(function($q) use ($modifierName){
                                  $q->where('title', '=', $modifierName)
                                  ->orWhereHas('translation_one', function($query) use ($modifierName){
                                      $query->where('title', '=', $modifierName);
                                  });
                              })->update(['square_modifier_option_id' => $modifierData->getId()]);
                }
              }
            }
            DB::commit();
            return response()->json([
              'status'  => 'success',
              'result'  => '',
              'message' => __('Modifiers successfully created/updated in square.')
            ]);
          } else {
              DB::rollback();
              $errors = $api_response->getErrors();
              return response()->json([
                'status'  => 'error',
                'result'  => '',
                'message' => __('There is some error while adding/updating Modifier in square. Please check log for the error.')
              ]);
          }
        }else{
          DB::rollback();
          return response()->json([
            'status'  => 'error',
            'result'  => [],
            'message' => __("Addon does not exists")
          ]);
        }
        
      } 
      catch (ApiException $e) 
      {
        DB::rollback();
        return response()->json([
          'status'  => 'error',
          'result'  => [],
          'message' => $e->getMessage()
        ]);
      } 
    }
  }
  
  //------get square pos verionas objects ids (item, tax..... etc) starts here
  public function getItemVersionFromSquarePos($object_ids)
  {
    $client = $this->init();
    if($this->square_enable_status == 1)
    {
      $body = new \Square\Models\BatchRetrieveCatalogObjectsRequest($object_ids);
      $body->setIncludeRelatedObjects(true);

      $api_response = $client->getCatalogApi()->batchRetrieveCatalogObjects($body);
      
      $object_versions = array();
      if($api_response->isSuccess()) {
        $result = $api_response->getResult()->getObjects();
        $resultObject = $api_response->getResult()->getObjects();
        if($api_response->getResult()->getObjects()){
          $resultObject = $api_response->getResult()->getObjects();
          foreach($resultObject as $resultobjectdata){
        
            if($resultobjectdata->getType() == "ITEM"){
              $object_versions[$resultobjectdata->getId()] = $resultobjectdata->getVersion();
              foreach($resultobjectdata->getItemData()->getVariations() as $variantData){
                if($variantData->getType() == "ITEM_VARIATION" && $variantData->getItemVariationData()->getSku()!=''){
                  $object_versions[$variantData->getId()] = $variantData->getVersion();
                }
              }
            }
    
            if($resultobjectdata->getType() == "TAX"){
              $object_versions[$resultobjectdata->getId()] = $resultobjectdata->getVersion();
            }

            if($resultobjectdata->getType() == "MODIFIER_LIST"){
              $object_versions[$resultobjectdata->getId()] = $resultobjectdata->getVersion();
              foreach($resultobjectdata->getModifierListData()->getModifiers() as $modifierData){
                if($modifierData->getType() == "MODIFIER"){
                  $object_versions[$modifierData->getId()] = $modifierData->getVersion();
                }
              }
            }
          }
        }
      } else {
          $errors = $api_response->getErrors();
          return response()->json([
            'status'  => 'error',
            'result'  => '',
            'message' => __('There is some error while retriving versions related to squareids from square. Please check log for the error.')
          ]);
      }
      return $object_versions;
    }
  }//------get square pos verionas objects ids (item, tax..... etc) ends here


  //------get square pos verionas objects ids (item, tax..... etc) starts here
  public function applyModifierToItemSquare($itemids, $modifierids)
  {
    //-----init square client--------
    $client = $this->init();
    if($this->square_enable_status == 1)
    {
      $body = new \Square\Models\UpdateItemModifierListsRequest($itemids);
      $body->setModifierListsToEnable($modifierids);

      $api_response = $client->getCatalogApi()->updateItemModifierLists($body);

      if ($api_response->isSuccess()) {
          $result = $api_response->getResult();
          return response()->json([
            'status'  => 'success',
            'result'  => '',
            'message' => __('Modifier applied to items in Square.')
          ]);
      } else {
          $errors = $api_response->getErrors();
          return response()->json([
            'status'  => 'error',
            'result'  => '',
            'message' => __('There is some error while appling Modifier to Product in square. Please check log for the error.')
          ]);
      }
    }
  }//------get square pos verionas objects ids (item, tax..... etc) ends here


  //------update veriant quantity function starts here
  public function inventoryAdjustmentInSquarePos($square_variant_id, $quantity, $type, $state)
  {
    //-----init square client--------
    $client = $this->init();

    if($this->square_enable_status == 1)
    {
      $physical_count = new \Square\Models\InventoryPhysicalCount();
      $uniqueid     = Uuid::uuid4();
      $physical_count->setReferenceId($uniqueid);
      $physical_count->setCatalogObjectId($square_variant_id);
      $physical_count->setState($state);
      $physical_count->setLocationId($this->location_id);
      $physical_count->setQuantity($quantity);
      $physical_count->setOccurredAt(Carbon::now()->toIso8601ZuluString());//set timestamp as per square format

      $inventory_change = new \Square\Models\InventoryChange();
      $inventory_change->setType($type);
      $inventory_change->setPhysicalCount($physical_count);

      $changes = [$inventory_change];
      $uniqueid     = Uuid::uuid4();
      $body = new \Square\Models\BatchChangeInventoryRequest($uniqueid);
      $body->setChanges($changes);
      $body->setIgnoreUnchangedCounts(true);

      $api_response = $client->getInventoryApi()->batchChangeInventory($body);

      if ($api_response->isSuccess()) {
          $result = $api_response->getResult();
          return response()->json([
            'status'  => 'success',
            'result'  => '',
            'message' => __('Product variant stock updated in square.')
          ]);
      } else {
          $errors = $api_response->getErrors();
          return response()->json([
            'status'  => 'error',
            'result'  => '',
            'message' => __('There is some error while updating Product variant stock in square. Please check log for the error.')
          ]);
      }
    }
  }//------update veriant quantity function ends here

  public function deleteBatchInSquarePos($batch_square_ids)
  {
    //-----init square client--------
    $client = $this->init();

    if($this->square_enable_status == 1)
    {
      try{
        $object_ids = $batch_square_ids;
        $body = new \Square\Models\BatchDeleteCatalogObjectsRequest();
        $body->setObjectIds($object_ids);

        $api_response = $client->getCatalogApi()->batchDeleteCatalogObjects($body);

        if ($api_response->isSuccess()) {
            $result = $api_response->getResult();
            return response()->json([
              'status'  => 'success',
              'result'  => '',
              'message' => __('Batch deleted in Square.')
            ]);
        } else {
            $errors = $api_response->getErrors();
            return response()->json([
              'status'  => 'error',
              'result'  => [],
              'message' => __('Something went wrong, Please try again later.')
            ]);
        }
      }
      catch (ApiException $e) 
      {
        return response()->json([
          'status'  => 'error',
          'result'  => [],
          'message' => $e->getMessage()
        ]);
      } 
    }
  }

  public function searchCatalogObjects($timestamp_version_update ='')
  {
    //-----init square client--------
    $client = $this->init();
    try{
      $last_begin_timestamp = SquareTimestamp::orderBy('id', 'desc')->first();
      $object_types = ['ITEM', 'TAX', 'ITEM_VARIATION', 'MODIFIER', 'MODIFIER_LIST'];
      $body = new \Square\Models\SearchCatalogObjectsRequest();
      $body->setObjectTypes($object_types);
      $body->setIncludeDeletedObjects(true);
      $last_timestamp = (!empty($last_begin_timestamp) && isset($last_begin_timestamp->created_at)) ? Carbon::parse($last_begin_timestamp->created_at)->toIso8601ZuluString() : Carbon::now()->subDays(1)->toIso8601ZuluString();
      $body->setBeginTime($last_timestamp);

      $api_response = $client->getCatalogApi()->searchCatalogObjects($body);
      if ($api_response->isSuccess()) {
        $result = $api_response->getResult();
        if(@$result && @$result->getObjects()){
          foreach($result->getObjects() as $getobjects){
            if($getobjects->getType() == "ITEM"){
              $square_item_id = $getobjects->getId();
              $square_item_name = $getobjects->getItemData()->getName();
              $productdata = Product::where('square_item_id', '=', $square_item_id)->first();
              ProductTranslation::where('product_id', '=', $productdata->id)->update(['title' => $square_item_name]);
            }
            if(@$getobjects->getItemData() && @$getobjects->getItemData()->getVariations()){
              foreach($getobjects->getItemData()->getVariations() as $getvariation){
                if($getvariation->getType() == "ITEM_VARIATION"){
                  $square_variation_id = $getvariation->getId();
                  $square_variation_name = $getvariation->getItemVariationData()->getName();
                  $square_price = $getvariation->getItemVariationData()->getPriceMoney()->getAmount() / 100;
                  $variantdata = ProductVariant::where('square_variant_id', '=', $square_variation_id)->update(['price' => $square_price]);
                }
              }
            }
          }
        }
        if($timestamp_version_update != ''){
           $timestamp_version_update = Carbon::parse($timestamp_version_update)->toTimeString();
        }else{
          $timestamp_version_update = Carbon::now()->toIso8601ZuluString();
        }
        SquareTimestamp::create(array(
          'created_at' => $timestamp_version_update,
          'updated_at'  => $timestamp_version_update
        ));
      } else {
        $errors = $api_response->getErrors();
        return response()->json([
          'status' => 'error',
          'result' => [],
          'message' => __('Something went wrong, Please try again later.')
        ]);
      }
    }
    catch (ApiException $e)
    {
      return response()->json([
        'status' => 'error',
        'result' => [],
        'message' => $e->getMessage()
      ]);
    }
  }
}
