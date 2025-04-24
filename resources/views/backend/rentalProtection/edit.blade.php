 <div class="row">
     <div class="col-6 mb-2">
         {!! Form::label('title', __('Title'),['class' => 'control-label']) !!}
         {!! Form::text('title',$rentalProtection->title, ['class'=>'form-control', 'id' => 'title', 'required' => 'required']) !!}
     </div>
     <div class="col-6 mb-2">
         {!! Form::label('price', __('Price('.$clientCurrency->currency->symbol.')'),['class' => 'control-label']) !!}
         {!! Form::text('price', $rentalProtection->price, ['class'=>'form-control', 'id' => 'price']) !!}
     </div>
     <div class="col-12 mb-2">
         {!! Form::label('validity', __('Validity'),['class' => 'control-label']) !!}
         @php
         $validity = [
         1 => 'Day',
         2 => 'Week',
         3 => 'Month'
         ];
         @endphp
         {!! Form::select('validity', $validity,$rentalProtection->validity, ['class'=>'form-control ', 'id' => 'validity']) !!}

     </div>
     <div class="col-12 mb-2">
         {!! Form::label('description', __('Description'),['class' => 'control-label']) !!}
         {!! Form::textarea('description', $rentalProtection->description, ['class'=>'form-control', 'id' => 'body_html', 'rows' => '5']) !!}
     </div>
 </div>
