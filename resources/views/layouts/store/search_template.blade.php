<script type="text/template" id="search_box_main_div_template">
    <a class="text-right d-block mr-2 mb-1" id="search_viewall" href="#">{{ __('View All') }}</a> 
    <div class="row mx-0">
        <% _.each(results, function(data, k){%>
        <% if(data.title !=''){ %>

            <div class="result-item-name product_heading">
                <h4><%=data.title %></h4>
            </div>
        
        <%} %>
        <% _.each(data.result, function(result, k){%>
        <a class="col-12 text-center list-items pt-2" href="<%=result.redirect_url %>">
            <img class="blur-up lazyload" data-src="<%=result.image_url%>" alt="">
            <div class="result-item-name">
                <b><%=result.name %></b>
            </div>
        </a>
        <%}); %>
        <%}); %>
    </div>
</script>
