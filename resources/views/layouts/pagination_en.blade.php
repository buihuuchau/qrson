<div class="row">
    <div class="col-sm-12 col-md-12 text-right">
        <div class="dataTables_info pagination-info">Page {{$paginator->currentPage()}}/{{$paginator->lastPage()}} | Show {{$paginator->lastItem()}} Result &nbsp;</div>
        <div class="dataTables_paginate paging_simple_numbers pagination-number">
            <ul class="pagination">
                <!-- first Page Link -->
                @if ($paginator->onFirstPage())
                    <li class="paginate_button page-item disabled"><a class="page-link">&lt;&lt;</a></li>
                @else
                    <li class="paginate_button page-item"><a class="page-link" href="{{$paginator->url(1)}}" rel="prev">&lt;&lt;</a></li>
                @endif
            <!-- Previous Page Link -->
                @if ($paginator->onFirstPage())
                    <li class="paginate_button page-item disabled"><a class="page-link">Previous</a></li>
                @else
                    <li class="paginate_button page-item"><a class="page-link" href="{{ $paginator->previousPageUrl() }}" rel="prev">Before</a></li>
                @endif

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

            <!-- Next Page Link -->
                @if ($paginator->hasMorePages())
                    <li class="paginate_button page-item"><a class="page-link" href="{{ $paginator->nextPageUrl() }}" rel="next">Next</a></li>
                @else
                    <li class="paginate_button page-item disabled"><a class="page-link">Next</a></li>
                @endif

            <!-- last Page Link -->
                @if ($paginator->lastPage() === $paginator->currentPage())
                    <li class="paginate_button page-item disabled"><a class="page-link">&gt;&gt;</a></li>
                @else
                    <li class="paginate_button page-item"><a class="page-link" href="{{$paginator->url($paginator->lastPage())}}" rel="prev">&gt;&gt;</a></li>
                @endif
            </ul>
            &nbsp;
        </div>
    </div>
</div>
