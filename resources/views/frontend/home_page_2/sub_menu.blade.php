<ul id="main-menu" class="sm pixelstrap sm-horizontal menu-slider">
    @foreach ($navCategories as $cate)
        @if ($cate['name'])
            <li> <a href="{{ route('categoryDetail', $cate['slug']) }}">
                    @if ($client_preference_detail->show_icons == 1 && (\Request::route()->getName() == 'userHome' || \Request::route()->getName() == 'homeTest'))
                        <img class="blur-up lazyload"
                            data-icon_two="{{ !is_null($cate['icon_two']) ? $cate['icon_two']['image_fit'] . '200/200' . $cate['icon_two']['image_path'] : $cate['icon']['image_fit'] . '200/200' . $cate['icon']['image_path'] }}"
                            data-icon="{{ $cate['icon']['image_fit'] }}200/200{{ $cate['icon']['image_path'] }}"
                            data-src="{{ $cate['icon']['image_fit'] }}200/200{{ $cate['icon']['image_path'] }}"
                            alt="" onmouseover='changeImage(this,1)' onmouseout='changeImage(this,0)'>
                        @endif{{ $cate['name'] }}
                </a>
                @if (!empty($cate['children']))
                    <ul>
                        @foreach ($cate['children'] as $childs)
                            <li> <a href="{{ route('categoryDetail', $childs['slug']) }}"><span
                                        class="new-tag">{{ $childs['name'] }}</span></a>
                                @if (!empty($childs['children']))
                                    <ul>
                                        @foreach ($childs['children'] as $chld)
                                            <li><a
                                                    href="{{ route('categoryDetail', $chld['slug']) }}">{{ $chld['name'] }}</a>
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
