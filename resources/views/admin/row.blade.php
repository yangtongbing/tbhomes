<div class="row">
    @foreach ($row as $item)
        <div class="col-md-{{ $item['width'] }}">
            {{ $item['content'] }}
        </div>
    @endforeach
</div>