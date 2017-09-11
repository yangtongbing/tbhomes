<div class="row">
    @foreach ($img as $item)
        <div class="col-xs-6 col-md-3">
            <a href="{{ $item }}" class="thumbnail" target="_blank">
                <img src="{{ $item }}" style="width: 171px;height:180px">
            </a>
        </div>
    @endforeach
</div>