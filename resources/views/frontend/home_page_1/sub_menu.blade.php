<ul id="main-menu" class="sm pixelstrap sm-horizontal menu-slider">
    @foreach($navCategories as $cate)
    @if($cate['name'])
    <li class="al_main_category">

        @if ($client_preference_detail->view_get_estimation_in_category == 1 && $client_preference_detail->business_type == "laundry")
            <a href="/get-estimation#{{$cate['slug']}}">
                @if($client_preference_detail->show_icons==1 && (\Request::route()->getName()=='userHome' || \Request::route()->getName()=='homeTest'))
                <div class="nav-cate-img" > <img class="blur blurload" data-src="{{$cate['icon']['image_fit']}}200/200{{$cate['icon']['image_path']}}" src="{{$cate['icon']['image_fit']}}20/20{{$cate['icon']['image_path']}}" alt=""> </div>
                @endif{{$cate['name']}}
            </a>
        @else
            <a href="{{route('categoryDetail', $cate['slug'])}}">
                @if($client_preference_detail->show_icons==1 && (\Request::route()->getName()=='userHome' || \Request::route()->getName()=='homeTest'))
                <div class="nav-cate-img" > <img class="blur blurload" data-src="{{$cate['icon']['image_fit']}}200/200{{$cate['icon']['image_path']}}" src="{{$cate['icon']['image_fit']}}20/20{{$cate['icon']['image_path']}}" alt=""> </div>
                @endif{{$cate['name']}}
            </a>
        @endif

        @if(!empty($cate['children']))
        <ul class="al_main_category_list">
            @foreach($cate['children'] as $childs)
            <li>
                <a href="{{route('categoryDetail', $childs['slug'])}}"><span
                        class="new-tag">{{$childs['name']}}</span></a>
                @if(!empty($childs['children']))
                <ul class="al_main_category_sub_list">
                    @foreach($childs['children'] as $chld)
                    <li><a
                            href="{{route('categoryDetail', $chld['slug'])}}">{{$chld['name']}}</a>
                    </li>
                    @endforeach
                </ul>
                @endif
            </li>
            @endforeach
        </ul>
        @endif
    </li>
    @endif
    @endforeach
</ul>