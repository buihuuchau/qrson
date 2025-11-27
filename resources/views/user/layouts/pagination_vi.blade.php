<div class="row">
    <div class="col-sm-12 col-md-12 text-center">
        <div class="dataTables_info pagination-info">Trang {{$paginator->currentPage()}}/{{$paginator->lastPage()}}</div>
        <div class="dataTables_paginate paging_simple_numbers pagination-number">
            <ul class="pagination">
            <!-- Pagination Elements -->
                @foreach ($elements as $element)
                <!-- "Three Dots" Separator -->
                    @if (is_string($element))
                        <li class="paginate_button page-item disabled"><a class="page-link">{{ $element }}</a></li>
                    @endif

                <!-- Array Of Links -->
                    @if (is_array($element))
                        @foreach ($element as $page => $url)
                            @if ($page == $paginator->currentPage())
                                <li class="paginate_button page-item active"><a class="page-link">{{ $page }}</a></li>
                            @else
                                <li class="paginate_button page-item"><a class="page-link" href="{{ $url }}">{{ $page }}</a></li>
                            @endif
                        @endforeach
                    @endif
                @endforeach
            </ul>
            &nbsp;
        </div>
    </div>
</div>
