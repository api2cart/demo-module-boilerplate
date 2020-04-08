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
            </ul>
        </div>
    </div>
</div>
