@include('header')
<link rel="stylesheet" href="{{asset('css/zui.treemap.min.css')}}">
<script src="{{asset('js/zui.treemap.min.js')}}"></script>
<script src="{{asset('js/zui.min.js')}}"></script>

<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{$title}}
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="#"><i class="fa fa-dashboard"></i> 主页</a></li>
            <li class="active">{{$title}}</li>
        </ol>
    </section>

    <!-- Main content -->
    <section class="content">
        <div class="col-sm-12">
            <div class="box">
                <input type="hidden" id="hidden" value="{{$id}}">
                <div id="treemapExample2" class="treemap">
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>
<script>
    //解决zui导致右上角user-menu不出来
    $('.user-menu').click(function () {
        var sign = $('.user-menu a').attr('aria-expanded');
        if (typeof (sign) == 'undefined' || sign == 'true') {
            $('.user-menu').addClass('open');
        } else {
            $('.user-menu a').attr('aria-expanded', false)
            $('.user-menu').removeClass('open');
        }
    })

    $.post('/atlas/treemap', {id:$('#hidden').val()}, function(data){
        treemapdata = data.details;
        $('#treemapExample2').treemap(
            treemapdata
        );
    });
</script>
@include('footer')