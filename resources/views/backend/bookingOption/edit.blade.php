 <div class="row">
     <div class="col-6 mb-2">
         {!! Form::label('title', __('Title'),['class' => 'control-label']) !!}
         {!! Form::text('title',$bookingOption->title, ['class'=>'form-control', 'id' => 'title', 'required' => 'required']) !!}
     </div>
     <div class="col-6 mb-2">
         {!! Form::label('price', __('Price('.$clientCurrency->currency->symbol.')'),['class' => 'control-label']) !!}
         {!! Form::text('price', $bookingOption->price, ['class'=>'form-control', 'id' => 'price']) !!}
     </div>
     <div class="col-12 mb-2">
         {!! Form::label('description', __('Description'),['class' => 'control-label']) !!}
         {!! Form::textarea('description', $bookingOption->description, ['class'=>'form-control', 'id' => 'body_html', 'rows' => '5']) !!}
     </div>
 </div>
