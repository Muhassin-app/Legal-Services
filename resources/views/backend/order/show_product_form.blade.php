
<div class="accordion custom-accordin1" id="accordionExample">
@foreach($product_faqs as $key => $value)


    <!-- <div class="royo-ques w-100">
        <h3>Question : {{$value->question}} </h3>
        <h6>Ans : {{$value->answer}} </h6>
    </div> -->

    <div class="card mb-2">
        <div class="card-header" id="heading{{$key}}">
        <h2 class="m-0 p-0">
            <button class="btn btn-link btn-block text-left" type="button" data-toggle="collapse" data-target="#collapse{{$key}}" aria-expanded="true" aria-controls="collapseOne">
           {{__("Ques:")}} {{$value->question}}
            </button>
        </h2>
        </div>

        <div id="collapse{{$key}}" class="collapse {{($key == 0 )? 'show' : ''}}" aria-labelledby="heading{{$key}}" data-parent="#accordionExample">
        <div class="card-body">
        {{__("Ans:")}} {{$value->answer}}
        </div>
        </div>
    </div>

@endforeach              


    
</div>