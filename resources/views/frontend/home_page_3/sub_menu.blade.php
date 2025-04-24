<ul id="main-menu" class="sm pixelstrap sm-horizontal menu-slider d-flex justify-content-center" >
    @foreach($navCategories as $cate)
    @if($cate['name'])
    <li class="al_main_category"  >
       <a href="{{route('categoryDetail', $cate['slug'])}}" class="{{isset($category) && $category->slug == $cate['slug'] ? 'current_category' : ''}}">
        {{dd($client_preference_detail->show_icons)}}
          @if($client_preference_detail->show_icons==1 && (\Request::route()->getName()=='homeTest' || \Request::route()->getName()=='userHome' || \Request::route()->getName()=='categoryDetail'))
          <div class="nav-cate-img {{ \Request::route()->getName()=='userHome' ? '' : 'activ_nav'}} " > <img style="height:100px;width:100px;" class="blur-up lazyload" data-icon_two="{{!is_null($cate['icon_two']) ? $cate['icon_two']['image_fit'].'200/200'.$cate['icon_two']['image_path'] : $cate['icon']['image_fit'].'200/200'.$cate['icon']['image_path']}}" data-icon="{{$cate['icon']['image_fit']}}200/200{{$cate['icon']['image_path']}}" data-src="{{$cate['icon']['image_fit']}}150/150{{$cate['icon']['image_path']}}" alt="" onmouseover='changeImage(this,1)' onmouseout='changeImage(this,0)'> </div>
          @endif
          {{$cate['name']}}
       </a>
      @if(!empty($cate['children']))
      <ul class="al_main_category_list">
          @foreach($cate['children'] as $childs)
          <li>
             <a href="{{route('categoryDetail', $childs['slug'])}}"><span class="new-tag">{{$childs['name']}}</span></a>
              @if(!empty($childs['children']))
             <ul class="al_main_category_sub_list">
                @foreach($childs['children'] as $chld)
                <li><a href="{{route('categoryDetail', $chld['slug'])}}">{{$chld['name']}}</a></li>
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