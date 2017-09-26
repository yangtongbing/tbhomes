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
                <div class="box-body" style="line-height: 40px">
                    <div class="row">
                        <div class="col-md-2">ID</div>
                        <div class="col-md-3">{{$user['id']}} </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">姓名</div>
                        <div class="col-md-3">{{$user['name']}} </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">手机号</div>
                        <div class="col-md-3">{{$user['mobile']}} </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">注册日期</div>
                        <div class="col-md-3">{{$user['created_at']}} </div>
                    </div>
                </div>
            </div>
        </div>
    </section>
    <!-- /.content -->
</div>
@include('footer')