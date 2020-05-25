<div class="col-lg-2">
    <div class="card">
        <div class="card-header">
            Sidebar
        </div>

        <div class="card-body sidemenu">
            <ul class="nav flex-column">
                <li class="nav-item" >
                    <a href="{{ url('/home') }}" class="nav-link {{ (request()->is('home')) ? 'active' : '' }}">
                        Dashboard
                    </a>
                </li>
                <li class="nav-item" >
                    <a href="{{ route('stores.index') }}" class="nav-link {{ (request()->is('stores') || request()->is('stores/*')) ? 'active' : '' }}">
                        Stores
                    </a>
                </li>
                <li class="nav-item" >
                    <a href="{{ route('orders.index') }}" class="nav-link {{ (request()->is('orders') || request()->is('orders/*')) ? 'active' : '' }}">
                        Orders
                    </a>
                </li>
                <li class="nav-item" >
                    <a href="{{ route('customers.index') }}" class="nav-link {{ (request()->is('customers') || request()->is('customers/*')) ? 'active' : '' }}">
                        Customers
                    </a>
                </li>
                <li class="nav-item" >
                    <a href="{{ route('products.index') }}" class="nav-link {{ (request()->is('products') || request()->is('products/*')) ? 'active' : '' }}">
                        Products
                    </a>
                </li>
                <li class="nav-item" >
                    <a href="{{ route('categories.index') }}" class="nav-link {{ (request()->is('categories') || request()->is('categories/*')) ? 'active' : '' }}">
                        Categories
                    </a>
                </li>
            </ul>
        </div>
    </div>
    <br>
    <div class="card">
        <div class="card-header">
            Business Cases
        </div>

        <div class="card-body sidemenu">
            <ul class="nav flex-column">
                <li class="nav-item" >
                    <a href="{{ route('businessCases.import_orders_automation') }}" class="nav-link {{ (request()->is('businessCases/import_orders_automation') || request()->is('businessCases/import_orders_automation/*')) ? 'active' : '' }}">
                        Import Orders Automation
                    </a>
                </li>
                <li class="nav-item" >
                    <a href="#" class="nav-link disabled">
                        Abandoned cart recovery
                    </a>
                </li>
                <li class="nav-item" >
                    <a href="#" class="nav-link disabled ">
                        Automatic Price Updating
                    </a>
                </li>
                <li class="nav-item" >
                    <a href="{{ route('businessCases.automatic_email_sending') }}" class="nav-link  {{ (request()->is('businessCases/automatic_email_sending') || request()->is('businessCases/automatic_email_sending/*')) ? 'active' : '' }}">
                        Automatic Email Sending
                    </a>
                </li>
                <li class="nav-item" >
                    <a href="#" class="nav-link disabled ">
                        Automatic Synchronization of Inventory
                    </a>
                </li>
            </ul>
        </div>
    </div>

</div>
