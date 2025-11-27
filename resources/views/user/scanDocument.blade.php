@extends('user.main')

@section('title', 'Nhập số chứng từ')

@section('content')
    <h4 class="mb-3 text-center">Nhập số chứng từ</h4>
    <div style="text-align: end">
        <a href="{{ route('web.logout') }}">Đăng xuất</a>
    </div>
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

    <form id="formAdd" action="{{ route('user.document.add') }}" method="post">
        @csrf
        <div class="mb-3">
            <label for="shipment_id" class="form-label">Shipment No</label>
            <input type="text" class="form-control" id="shipment_id" name="shipment_id" value="{{ $shipment_id }}"
                readonly required>
        </div>
        <div class="mb-3">
            <label for="document_id" class="form-label">Số chứng từ</label>
            <input type="text" class="form-control" id="document_id" name="document_id" value="{{ old('document_id') }}"
                required>
        </div>
        <div class="mb-3">
            <label for="total" class="form-label">Số lượng mã</label>
            <input type="number" class="form-control" id="total" name="total" min="1"
                value="{{ old('total') }}" required>
        </div>
        <button id="btnAddSubmit" type="submit" class="btn btn-primary">Tạo Số chứng từ</button>
    </form>

    <h5>Danh sách các Số chứng từ mà bạn đã tạo nhưng chưa quét đủ mã sản phẩm hoặc chưa xác nhận hoàn thành.</h5>
    <div class="card-body">
        <table id="example1" class="table table-bordered table-striped">
            <thead>
                <tr>
                    <th>Số thứ tự</th>
                    <th>Shipment ID</th>
                    <th>Số chứng từ</th>
                    <th>Số mã đã nhập</th>
                    <th>Số mã tất cả</th>
                    <th>Thời gian nhập</th>
                    <th>Thao tác</th>
                </tr>
            </thead>
            <tbody>
                @foreach ($documents as $key => $document)
                    <tr>
                        <td>{{ $key + 1 }}</td>
                        <td>{{ $document->shipment_id }}</td>
                        <td>{{ $document->id }}</td>
                        <td>{{ $document->total_current }}</td>
                        <td>{{ $document->total }}</td>
                        <td>{{ $document->created_at }}</td>
                        <td>
                            <a class="btn btn-primary" title="Chi tiết"
                                href="{{ route('user.scan.codeProduct', ['shipment_id' => $document->shipment_id, 'document_id' => $document->id]) }}">
                                <i class="fas fa-eye"></i>
                            </a>
                            <button class="btn btn-danger clearDocument" title="Xóa"
                                data-document-id="{{ $document->id }}"><i class="fas fa-trash"></i></button>
                        </td>
                    </tr>
                @endforeach
            </tbody>
            <tfoot>
                <tr>
                    <th>Số thứ tự</th>
                    <th>Shipment ID</th>
                    <th>Số chứng từ</th>
                    <th>Số mã đã nhập</th>
                    <th>Số mã tất cả</th>
                    <th>Thời gian nhập</th>
                    <th>Thao tác</th>
                </tr>
            </tfoot>
        </table>
        <div class="d-flex justify-content-end">
            {{ $documents->appends($_GET)->links('web.layouts.pagination_vi') }}
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
                text: "Xác nhận tạo mới Số chứng từ?",
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

        $('.clearDocument').click(function(e) {
            e.preventDefault();
            let button = $(this);
            let document_id = button.data('document-id');
            Swal.fire({
                title: "Xác nhận xóa?",
                text: "Số chứng từ:  " + document_id +
                    " và các mã sản phẩm đi kèm sẽ bị xóa và không thể khôi phục!",
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
                        url: "{{ route('user.document.delete') }}",
                        data: {
                            document_id: document_id,
                            _token: "{{ csrf_token() }}"
                        },
                        dataType: "json",
                        success: function(response) {
                            let message = response && response.message ? response.message :
                                'Xóa Số chứng từ thành công';
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
                            let message = xhr.responseJSON && xhr.responseJSON.message ?
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
