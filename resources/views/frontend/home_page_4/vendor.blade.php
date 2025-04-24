@if($key < 7)
									<div class="col-lg-3 col-md-4 col-6">
										<div class="product-card-box position-relative text-center al_custom_vendors_sec_al p-3">
											<a class="suppliers-box d-block" href="{{route('vendorDetail')}}/{{ $vendor->slug }}">
												<div class="suppliers-img-outer position-relative " style="height:100px">
													@if($vendor->is_vendor_closed==1) 
														<img class="fluid-img mx-auto blur-up lazyload grayscale-image" data-src="{{ get_file_path($vendor->logo,'FILL_URL','200','200') }}" alt="" title="">
													@else
														<img  class="fluid-img mx-auto blur-up lazyload" data-src="{{ get_file_path($vendor->logo,'FILL_URL','200','200') }}" alt="" title="">
													@endif
						
												</div>
												<div class="supplier-rating">
													<h6 class="mb-1 ellips">{{ $vendor->name }}</h6>
														@if(@$vendor->timeofLineOfSightDistance != 0)
															<div class="pref-timing"> <span>{{$vendor->timeofLineOfSightDistance}}</span> </div>
														@endif
												</div>
												@if($client_preference_detail && $client_preference_detail->rating_check==1)
												@if($vendor->vendorRating >0)
												<span class="rating-number"> {{ $vendor->vendorRating }}</span>
												@endif @endif
											</a>
										</div>
									</div>
								@endif
								@if($key == 7)
									<div class="col-sm-3">
										<div class="al_boxSeeAll">
											<a class="al_boxSeeAllArea" href="{{route('vendor.all')}}">
												<span style="">
													<i class="fa fa-arrow-right"></i>
												</span>
												{{__("See all")}}
											</a>
										</div>
									</div>
								@endif
						