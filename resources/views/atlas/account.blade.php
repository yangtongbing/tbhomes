@include('header')
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
                <div class="box-body" style="line-height: 30px">

                </div>
            </div>

        </div>
    </section>
    <!-- /.content -->
</div>

@include('footer')