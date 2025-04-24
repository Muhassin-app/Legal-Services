<script>
    $(document).on('click', '.category-list', function() {
        $('.category-list').find('.select-category').removeClass('active');
        $(this).find('.select-category').addClass('active');
        $(this).show();
        $('.choose-category').show();
    });

    $('.dropify').dropify();
    $(document).on('click', '.select-category', function() {
        var category_id = $(this).data('id');
        $("#category_id").val(category_id);
        $(".selected-category").text($(this).data('name'));
        var type_id = $(this).data('type-id');
        $(".p2p-category-form").removeClass('d-none');
        if(type_id == '10'){
            $(".rental-cat-fields").removeClass('d-none');
            $(".p2p-cat-fields").addClass('d-none');
        }else{
            $(".rental-cat-fields").addClass('d-none');
            $(".p2p-cat-fields").removeClass('d-none');
        }
        $.ajaxSetup({
            headers: {
                'X-CSRF-TOKEN': $('input[name="_token"]').val()
            }
        });
        $.ajax({
            url: "{{ route('category.attributes') }}",
            type: "GET",
            data: {
                category_id: category_id
            },
            success: function(response) {
                if (response.success) {
                    $("#productAttributes").html(response.html);
                }
            },
        });
    });

    function checkAddressString(obj, name) {
        if ($(obj).val() == "") {
            document.getElementById('latitude').value = '';
            document.getElementById('longitude').value = '';
        }
    }

    //calender and day_price

    $(document).on('keyup', '#day_price', function() {
        var dayPrice = $('#day_price').val();
        var weekPrice = (dayPrice * 4) / 7;
        var monthPrice = (dayPrice * 4 * 3) / 30;
        $('#week_price').val(weekPrice.toFixed(2));
        $('#month_price').val(monthPrice.toFixed(2));
    });

    $(function() {
        var date = new Date();
        var currentMonth = date.getMonth();
        var currentDate = date.getDate();
        var currentYear = date.getFullYear();
        $('input[name="date_availability"]').daterangepicker({
            minDate: new Date(currentYear, currentMonth, currentDate),
            dateFormat: 'yy-mm-dd',
            //startDate: moment(date).add(1,'days'),
            // endDate: moment(date).add(2,'days'),
            locale: {
                format: 'DD.MM.YYYY'
            }
        });
    });

    var form = document.getElementById("product_form");
    document.getElementById("save-post").addEventListener("click", function (e) {
        e.preventDefault();
        const elements = document.querySelectorAll('.select-category.active');
        const hasElements = elements.length > 0;
        if (hasElements) {
            $('.cat-error').addClass('d-none');
            form.submit();
        } else {
            $('.cat-error').removeClass('d-none');
        }
    });

    $(document).on('change', '#category_filter', function(){
        var value = $(this).val();
        var $viewAllCats = $('.view-all_cats');
        var $viewP2PSellCats = $('.view-p2psell_cats');
        var $viewRentalCats = $('.view-rental_cats');
        var $categoryID = $("#category_id");
        var $selectedCategory = $(".selected-category");
        var $p2pCategoryForm = $(".p2p-category-form");

        $viewAllCats.addClass('d-none');
        $viewP2PSellCats.addClass('d-none');
        $viewRentalCats.addClass('d-none');
        switch (value) {
            case '10':
            $viewRentalCats.removeClass('d-none');
            break;
            case '13':
            $viewP2PSellCats.removeClass('d-none');
            break;
            default:
            $viewAllCats.removeClass('d-none');
            break;
        }
        $categoryID.val('');
        $selectedCategory.text('');
        $p2pCategoryForm.addClass('d-none');
    });

</script>