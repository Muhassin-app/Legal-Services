 @php $client_cur = App\Models\ClientCurrency::where('is_primary',1)->first(); @endphp
        @if(isset($client_cur) && !empty($client_cur->currency))
            ({{$client_cur->currency->symbol ?? ''}})
@endif
  