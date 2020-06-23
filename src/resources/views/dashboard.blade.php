@extends('layouts.app')

@section('content')
    <div class="container-fluid">
        <div class="row">
            @include('parts.sidebar')

            <div class="col-lg-10">
                <div class="card">
                    <div class="card-header">Dashboard</div>

                    <div class="card-body">
                        <p><strong>This demo module</strong> shows the main possibilities of our API and enables you to see API2Cart in action. It is specially designed for B2B SaaS software vendors and tech specialists so that they can test how they can implement their business features and functionality via APICart API methods.</p>

                        <p>You will be able to check how you can work with orders, products, customers, categories, and other store data by using test stores of the most popular shopping platforms. You can also create an API2Cart account and use your own stores for a demo module.</p>

                        <p>With the help of <a href="https://api2cart.com/e-commerce-api-integration-benefits-for-development/" target="_blank">unified API</a> provided by <strong>APICart</strong> you can integrate with more than 40 eCommerce platforms at once. Instead of developing separate integration with each shopping platform, you will be able to <a href="https://api2cart.com/e-commerce-api-integration-benefits-for-business/" target="_blank">save time, money, and resources</a>. API2Cart has already established integration with the most popular eCommerce platforms and developed over 100 API methods to work with needed store data.</p>

                        <p>All you have to do is <a href="https://app.api2cart.com/#register" target="_blank">register an API2Cart account</a> and write integration between your software and API2Cart.</p>

                        <p>Watch this video that shows the basic logic of how API2Cart works.</p>

                        <br>
                        <div class="row">
                            <div class="col-3"></div>
                            <div class="col-6">
                                <div class="embed-responsive embed-responsive-16by9 text-center">
                                    <iframe class="embed-responsive-item" src="https://www.youtube.com/embed/r8Ailt2D6cw" frameborder="0" allow="accelerometer; autoplay; encrypted-media; gyroscope; picture-in-picture" allowfullscreen></iframe>
                                </div>
                            </div>
                        </div>

                        <br>

                        <p>API2Cart lets you easily implement your <a href="https://api2cart.com/business-cases/" target="_blank">business cases</a> of different types:</p>
                        <ul>
                            <li>order import automation;</li>
                            <li>automatic emails sending;</li>
                            <li>automatic synchronization of inventory;</li>
                            <li>abandoned cart recovery;</li>
                            <li>automatic price updating;</li>
                            <li>sending notifications on order statuses;</li>
                            <li>management of product listings;</li>
                            <li>automatic creation of shipments;</li>
                            <li>report automation;</li>
                            <li>automatic coupon creation</li>
                        </ul>

                        <p>It is an amazing option for those businesses that work in the fields of order and inventory management, warehouse management, shipping software, marketing automation, and <a href="https://api2cart.com/use-cases/" target="_blank">much more</a>.</p>

                    </div>
                </div>
            </div>
        </div>
    </div>
@endsection
