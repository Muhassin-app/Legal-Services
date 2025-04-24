<?php echo '<?xml version="1.0" encoding="UTF-8"?>' ?>

<urlset xmlns="http://www.sitemaps.org/schemas/sitemap/0.9">
@foreach($categories as $category)
    <url>
        <loc>{{ URL::route("categoryDetail", [$category->slug]) }}</loc>
        <lastmod>{{ gmdate(DateTime::W3C, strtotime($category->updated_at)) }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
@endforeach

@foreach($vendors as $vendor)
    <url>
        <loc>{{ URL::route("vendorDetail", [$vendor->slug]) }}</loc>
        <lastmod>{{ gmdate(DateTime::W3C, strtotime($vendor->updated_at)) }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
@endforeach

@foreach($products as $product)
    <url>
        <loc>{{ URL::route("productDetail", [$product->vendor->slug,$product->url_slug]) }}</loc>
        <lastmod>{{ gmdate(DateTime::W3C, strtotime($product->updated_at)) }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
@endforeach

@foreach($brands as $brand)
    <url>
        <loc>{{ URL::route("brandDetail", [$brand->id]) }}</loc>
        <lastmod>{{ gmdate(DateTime::W3C, strtotime($brand->updated_at)) }}</lastmod> 
        <changefreq>daily</changefreq>
        <priority>1.0</priority>
    </url>
@endforeach

@foreach($pages as $page)
    <url>
        <loc>{{ URL::route("extrapage", [$page->slug]) }}</loc>
        <lastmod>{{ gmdate(DateTime::W3C, strtotime($page->updated_at)) }}</lastmod>
        <changefreq>daily</changefreq>
        <priority>0.8</priority>
    </url>
@endforeach
</urlset>