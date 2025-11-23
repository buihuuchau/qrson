<!-- Main Sidebar Container -->
<aside class="main-sidebar sidebar-dark-primary elevation-4">
    <!-- Brand Logo -->
    <a href="index3.html" class="brand-link">
        <img src="{{ asset('AdminLTE-3.2.0/dist/img/AdminLTELogo.png') }}" alt="AdminLTE Logo"
            class="brand-image img-circle elevation-3" style="opacity: .8">
        <span class="brand-text font-weight-light">AdminLTE 3</span>
    </a>

    <!-- Sidebar -->
    <div class="sidebar">
        <!-- Sidebar user panel (optional) -->
        <div class="user-panel mt-3 pb-3 mb-3 d-flex">
            <div class="info">
                <a href="#" class="d-block">{{ Auth::user()->name }} - {{ Auth::user()->phone }}</a>
            </div>
        </div>

        <!-- Sidebar Menu -->
        <nav class="mt-2">
            <ul class="nav nav-pills nav-sidebar flex-column" data-widget="treeview" role="menu"
                data-accordion="false">
                <li class="nav-item">
                    <a href="{{ route('web.shipment.list') }}"
                        class="nav-link @if (in_array(\Route::currentRouteName(), ['web.shipment.list'])) active @endif">
                        <p>
                            Shipment
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('web.document.list') }}"
                        class="nav-link @if (in_array(\Route::currentRouteName(), ['web.document.list'])) active @endif">
                        <p>
                            Số chứng từ
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('web.code-product.list') }}"
                        class="nav-link @if (in_array(\Route::currentRouteName(), ['web.code-product.list'])) active @endif">
                        <p>
                            Mã sản phẩm(đã xong)
                        </p>
                    </a>
                </li>
                <li class="nav-item">
                    <a href="{{ route('web.user.list') }}"
                        class="nav-link @if (in_array(\Route::currentRouteName(), ['web.user.list'])) active @endif">
                        <p>
                            Danh sách Nhân viên
                        </p>
                    </a>
                </li>
            </ul>
        </nav>
        <!-- /.sidebar-menu -->
    </div>
    <!-- /.sidebar -->
</aside>
