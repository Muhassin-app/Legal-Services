@include('frontend.ajax.product-card')
@if(count($listData))
<div class="pagination pagination-rounded justify-content-end mb-0 page-m-20">
    {{ $listData->links() }}
</div>
@endif

@section('script')
<script>
    $(document).ready(function(){
        let currentPage = '{{$_GET["page"]??"1"}}';
        if(currentPage){
            $('.page-link').each(function(){
                if($(this).text()==currentPage){
                    $(this).prev().addClass('active');
                    break;
                }
            })
        }
    })



</script>
@endsection
