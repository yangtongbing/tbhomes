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
                    <div class="row">
                        <div class="col-md-2">机构ID</div>
                        <div class="col-md-3">{{$user['id']}} </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">机构名称</div>
                        <div class="col-md-3">{{$user['title']}} </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">负责人姓名</div>
                        <div class="col-md-3">{{$user['leader_name']}} </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">绑定手机</div>
                        <div class="col-md-3">{{$user['mobile']}} </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">注册日期</div>
                        <div class="col-md-3">{{$user['c_time']}} </div>
                    </div>
                    <hr style="color: #fff">
                    <div class="manage-head">
                        <span><strong>外呼系统</strong></span>
                    </div>
                    <div class="row">
                        <div class="col-md-2">安装包及说明文档</div>
                        <div class="col-md-3"><a href="{{asset("packages/callout/callout.zip")}}"> 点击下载 </a></div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">总机号</div>
                        <div class="col-md-3">{{env('PHONE')}} </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">坐席号</div>
                        <div class="col-md-3">{{$user['seat_no']}} </div>
                    </div>
                    <div class="row">
                        <div class="col-md-2">坐席密码</div>
                        <div class="col-md-3">{{$user['seat_password']}} </div>
                    </div>
                    @if(!$user['sub_sign'] && !empty($user['sub']))
                        <hr style="color: #fff">
                        <div class="manage-head">
                            <span><strong>子账号</strong></span>
                        </div>
                        @foreach($user['sub'] as $key => $value)
                        <div class="row">
                            <div class="col-md-2">账号{{$key+1}}：{{$value['username']}} </div>
                            <div class="col-md-2">坐席号：{{$value['seat_no']}} </div>
                        </div>
                        @endforeach
                    @endif
                </div>
            </div>

        </div>
    </section>
    <!-- /.content -->
</div>

@include('footer')