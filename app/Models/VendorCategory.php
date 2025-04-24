<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class VendorCategory extends Model
{
	use HasFactory;
	protected $table = 'vendor_categories';
	protected $fillable = ['status', 'vendor_id', 'category_id'];

	public function category()
	{
		return $this->hasOne('App\Models\Category', 'id', 'category_id');
	}

	public function categoryDetail()
	{
		return $this->hasOne('App\Models\Category', 'id', 'category_id')->select('id','slug');
	}

	public function vendor()
	{
		return $this->hasOne('App\Models\Vendor', 'id', 'vendor_id');
	}

	public function products(){
		return $this->hasMany('App\Models\Product', 'category_id', 'category_id');
	}

	public function addVendorCategory($to_vendor, $from_vendor)
	{
		/* Common categories replicate*/
		$records = self::select('categories.id as category_id', 'vendor_categories.vendor_id as vendor_id', 'vendor_categories.status as status')
			->join('categories', 'categories.id', '=', 'vendor_categories.category_id')
			->where('categories.vendor_id', null)
			->where('vendor_categories.vendor_id', $from_vendor)
			->get();

			foreach ($records as $row) {
				if (self::where(['vendor_id' => $to_vendor, 'category_id' => $row->category_id])->exists()) {
					self::where(['vendor_id' => $to_vendor, 'category_id' => $row->category_id])->update(['status' => $row->status]);
				} else {
					self::create(['vendor_id' => $to_vendor, 'category_id' => $row->category_id, 'status' => $row->status]);
				}
			}

		// return self::updateOrCreate([
		// 	'vendor_id' => $to_vendor,
		// 	'category_id' => $category_id
		// ],['status'=>$status]);
	}
}
