@extends('web.layouts.main')
@section('title')
    <title>Danh sách Nhân viên</title>
@endsection
@section('custom_css')
    {{-- custom-style --}}
@endsection
@section('content')
    <div class="content-wrapper">
        <!-- Content Header (Page header) -->
        <div class="content-header">
            <div class="container-fluid">
                <div class="row mb-2">
                    <div class="col-sm-6">
                        <h1 class="m-0">Nhân viên</h1>
                    </div><!-- /.col -->
                    <div class="col-sm-6">
                        <ol class="breadcrumb float-sm-right">
                            <li class="breadcrumb-item"><a href="">Nhân viên</a></li>
                        </ol>
                    </div><!-- /.col -->
                </div><!-- /.row -->
            </div><!-- /.container-fluid -->
        </div>
        <!-- /.content-header -->

        <!-- Main content -->
        <section class="content">
            <div class="container-fluid">
                <div class="row">
                    <div class="col-12">
                        <div class="card">
                            <div class="card-header">
                                <h3 class="card-title mb-3">Danh sách các Nhân viên</h3>
                                <div class="text-right">
                                    <button type="button" class="btn btn-primary" data-toggle="modal"
                                        data-target="#exampleModalAdd">
                                        Tạo mới
                                    </button>
                                </div>
                                <form class="mb-3 col-md-12 col-sm-12 d-flex row" action="{{ route('web.user.list') }}"
                                    method="get">
                                    <div class="col-md-3 col-sm-6">
                                        <label for="name" class="form-label">Họ và tên</label>
                                        <input id="name" type="text" class="form-control" name="name"
                                            maxlength="255" value="{{ request()->query('name') }}">
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label for="phone" class="form-label">Số điện thoại</label>
                                        <input id="phone" type="text" class="form-control" name="phone"
                                            minlength="10" maxlength="10" value="{{ request()->query('phone') }}">
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label for="role" class="form-label">Quyền hạn</label>
                                        <select class="form-control" name="role">
                                            <option value="" disabled selected>-- Chọn --</option>
                                            <option value="admin"
                                                {{ request()->query('role') == 'admin' ? 'selected' : '' }}>
                                                Quản lý
                                            </option>
                                            <option value="user"
                                                {{ request()->query('role') == 'user' ? 'selected' : '' }}>
                                                Nhân viên
                                            </option>
                                        </select>
                                    </div>
                                    <div class="col-md-3 col-sm-6">
                                        <label>&nbsp;</label><br>
                                        <button type="submit" class="btn btn-primary">Tìm kiếm</button>
                                    </div>
                                </form>
                                @if (session('success'))
                                    <div class="alert alert-success">
                                        {{ session('success') }}
                                    </div>
                                @endif
                                @if (session('error'))
                                    <div class="alert alert-danger">
                                        {{ session('error') }}
                                    </div>
                                @endif
                                @if ($errors->any())
                                    <div class="alert alert-danger">
                                        <ul style="margin: 0">
                                            @foreach ($errors->all() as $error)
                                                <li>{{ $error }}</li>
                                            @endforeach
                                        </ul>
                                    </div>
                                @endif
                            </div>
                            <!-- /.card-header -->
                            <div class="card-body">
                                <table id="example1" class="table table-bordered table-striped">
                                    <thead>
                                        <tr>
                                            <th>Số thứ tự</th>
                                            <th>Họ tên</th>
                                            <th>Số điện thoại</th>
                                            <th>Quyền</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        @foreach ($users as $key => $user)
                                            <tr>
                                                <td>{{ $key + 1 }}</td>
                                                <td>{{ $user->name }}</td>
                                                <td>{{ $user->phone }}</td>
                                                <td>
                                                    @if ($user->role == 'admin')
                                                        Quản lý
                                                    @else
                                                        Nhân viên
                                                    @endif
                                                </td>
                                                <td class="d-flex">
                                                    <button class="btn btn-primary mr-5 editUser" title="Chỉnh sửa"
                                                        data-user-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                                        data-phone="{{ $user->phone }}" data-role="{{ $user->role }}">
                                                        <i class="fas fa-pencil-alt"></i>
                                                    </button>
                                                    <button class="btn btn-danger clearUser" title="Xóa"
                                                        data-user-id="{{ $user->id }}" data-name="{{ $user->name }}"
                                                        data-phone="{{ $user->phone }}">
                                                        <i class="fas fa-trash"></i>
                                                    </button>
                                                </td>
                                            </tr>
                                        @endforeach
                                    </tbody>
                                    <tfoot>
                                        <tr>
                                            <th>Số thứ tự</th>
                                            <th>Họ tên</th>
                                            <th>Số điện thoại</th>
                                            <th>Quyền</th>
                                            <th>Thao tác</th>
                                        </tr>
                                    </tfoot>
                                </table>
                                <div class="d-flex justify-content-end">
                                    {{ $users->appends($_GET)->links('web.layouts.pagination_vi') }}
                                </div>
                            </div>
                            <!-- /.card-body -->
                        </div>
                        <!-- /.card -->
                    </div>
                    <!-- /.col -->
                </div>
                <!-- /.row (main row) -->
            </div><!-- /.container-fluid -->
        </section>
        <!-- /.content -->
    </div>

    <!-- Modal Add -->
    <div class="modal fade" id="exampleModalAdd" tabindex="-1" aria-labelledby="exampleModalLabel" aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Thêm nhân viên</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formAdd" action="{{ route('web.user.add') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <div class="mb-3">
                            <label for="name" class="form-label">Họ và tên</label>
                            <input id="name" type="text" class="form-control" name="name" maxlength="255"
                                value="{{ old('name') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="phone" class="form-label">Số điện thoại</label>
                            <input id="phone" type="text" class="form-control" name="phone" minlength="10"
                                maxlength="10" value="{{ old('phone') }}" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Quyền hạn</label>
                            <select class="form-control" name="role" required>
                                <option value="admin" {{ old('role', 'user') == 'admin' ? 'selected' : '' }}>Quản lý
                                </option>
                                <option value="user" {{ old('role', 'user') == 'user' ? 'selected' : '' }}>Nhân viên
                                </option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu</label>
                            <input id="password" type="password" class="form-control" name="password" minlength="6"
                                required>
                        </div>
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Xác nhận mật khẩu</label>
                            <input id="password_confirm" type="password" class="form-control"
                                name="password_confirmation" minlength="6" required>
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button id="btnAddSubmit" type="button" class="btn btn-primary">Thêm</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Modal Update -->
    <div class="modal fade" id="exampleModalUpdate" tabindex="-1" aria-labelledby="exampleModalLabel"
        aria-hidden="true">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <h5 class="modal-title" id="exampleModalLabel">Chỉnh sửa nhân viên</h5>
                    <button type="button" class="close" data-dismiss="modal" aria-label="Close">
                        <span aria-hidden="true">&times;</span>
                    </button>
                </div>
                <form id="formUpdate" action="{{ route('web.user.update') }}" method="post">
                    @csrf
                    <div class="modal-body">
                        <input type="hidden" name="user_id" id="inputUpdateId">
                        <div class="mb-3">
                            <label for="inputUpdatePhone" class="form-label">Số điện thoại</label>
                            <input id="inputUpdatePhone" type="text" class="form-control" name="phone"
                                minlength="10" maxlength="10" disabled>
                        </div>
                        <div class="mb-3">
                            <label for="inputUpdateName" class="form-label">Họ và tên</label>
                            <input id="inputUpdateName" type="text" class="form-control" name="name"
                                maxlength="255" required>
                        </div>
                        <div class="mb-3">
                            <label for="role" class="form-label">Quyền</label>
                            <select id="inputUpdateRole" class="form-control" name="role">
                                <option value="admin">Quản lý
                                </option>
                                <option value="user">Nhân viên
                                </option>
                            </select>
                        </div>
                        <div class="mb-3">
                            <label for="password" class="form-label">Mật khẩu <small class="text-danger">* Để trống nếu
                                    không muốn đổi mật khẩu</small></label>
                            <input id="password" type="password" class="form-control" name="password" minlength="6">
                        </div>
                        <div class="mb-3">
                            <label for="password_confirm" class="form-label">Xác nhận mật khẩu <small
                                    class="text-danger">* Để trống nếu
                                    không muốn đổi mật khẩu</small></label>
                            <input id="password_confirm" type="password" class="form-control"
                                name="password_confirmation" minlength="6">
                        </div>
                    </div>
                    <div class="modal-footer">
                        <button type="button" class="btn btn-secondary" data-dismiss="modal">Đóng</button>
                        <button id="btnUpdateSubmit" type="button" class="btn btn-primary">Chỉnh sửa</button>
                    </div>
                </form>
            </div>
        </div>
    </div>
@endsection
@section('custom_script')
    <script>
        $('#btnAddSubmit').click(function(e) {
            e.preventDefault();
            let formAdd = $('#formAdd')[0];
            Swal.fire({
                title: "Thêm mới",
                text: "Xác nhận tạo mới nhân viên?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Thêm",
                cancelButtonText: "Hủy"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#loadingOverlay').css('display', 'flex');
                    setTimeout(function() {
                        if (!formAdd.checkValidity()) {
                            $('#loadingOverlay').hide();
                            formAdd.reportValidity();
                            return;
                        }
                        formAdd.submit();
                    }, 300);
                }
            });
        });

        $('.editUser').click(function(e) {
            e.preventDefault();
            let button = $(this);
            let user_id = button.data('user-id');
            let name = button.data('name');
            let phone = button.data('phone');
            let role = button.data('role');
            $('#inputUpdateId').val(user_id);
            $('#inputUpdateName').val(name);
            $('#inputUpdatePhone').val(phone);
            $('#inputUpdateRole').val(role);
            $('#exampleModalUpdate').modal('show');
        });

        $('#btnUpdateSubmit').click(function(e) {
            e.preventDefault();
            let formUpdate = $('#formUpdate')[0];
            Swal.fire({
                title: "Chỉnh sửa",
                text: "Xác nhận chỉnh sửa nhân viên?",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Sửa",
                cancelButtonText: "Hủy"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#loadingOverlay').css('display', 'flex');
                    setTimeout(function() {
                        if (!formUpdate.checkValidity()) {
                            $('#loadingOverlay').hide();
                            formUpdate.reportValidity();
                            return;
                        }
                        formUpdate.submit();
                    }, 300);
                }
            });
        });

        $('.clearUser').click(function(e) {
            e.preventDefault();
            let button = $(this);
            let user_id = button.data('user-id');
            let name = button.data('name');
            let phone = button.data('phone');
            Swal.fire({
                title: "Xác nhận xóa?",
                html: "Nhân viên: " + name + "<br>" +
                    "Số điện thoại: " + phone + "<br>" +
                    "sẽ bị xóa và không thể khôi phục!",
                icon: "warning",
                showCancelButton: true,
                confirmButtonColor: "#3085d6",
                cancelButtonColor: "#d33",
                confirmButtonText: "Xóa",
                cancelButtonText: "Hủy"
            }).then((result) => {
                if (result.isConfirmed) {
                    $('#loadingOverlay').css('display', 'flex');
                    $.ajax({
                        type: "POST",
                        url: "{{ route('web.user.delete') }}",
                        data: {
                            user_id: user_id,
                            _token: "{{ csrf_token() }}"
                        },
                        dataType: "json",
                        success: function(response) {
                            let message = response && response.message ? response
                                .message :
                                'Xóa Nhân viên thành công';
                            Swal.fire({
                                icon: "success",
                                title: "Thành công",
                                text: message,
                                showConfirmButton: false,
                                timer: 1500
                            });
                            button.closest('tr').remove();
                            $("#example1 tbody tr").each(function(index) {
                                $(this).find("td:first").text(index + 1);
                            });
                            $('#loadingOverlay').hide();
                        },
                        error: function(xhr, status, error) {
                            let message = xhr.responseJSON && xhr.responseJSON
                                .message ?
                                xhr.responseJSON.message :
                                'Đã có lỗi xảy ra';
                            Swal.fire({
                                icon: "error",
                                title: "Lỗi",
                                text: message,
                            });
                            $('#loadingOverlay').hide();
                        }
                    });
                }
            });
        });
    </script>
@endsection
