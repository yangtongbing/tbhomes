@include('header')

<link rel="stylesheet" href="{{asset('packages/admin/bootstrap-fileinput/css/fileinput.min.css?v=4.3.7')}}">
<!-- Content Wrapper. Contains page content -->
<div class="content-wrapper">
    <!-- Content Header (Page header) -->
    <section class="content-header">
        <h1>
            {{$title}}
            <small></small>
        </h1>
        <ol class="breadcrumb">
            <li><a href="/"><i class="fa fa-dashboard"></i> 首页</a></li>
            <li class="active">{{$title}}</li>
        </ol>
    </section>
    <!-- Main content -->
    <section class="content">
        <div class="row">
            <div class="col-sm-12">
                <div class="box">
                    <div class="box-header">
                        <form action="/atlas/treeMapList" id="s_form" style="margin-top:10px;">
                            <div class="row search-form-row">
                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <div class="input-group-addon">姓名</div>
                                        <input type="text" value="{{$filter['name']}}" class="form-control input-sm" name="name">
                                    </div>
                                </div>
                                <div class="col-sm-2" style="width:300px;">
                                    <button class="btn btn-primary input-sm" type="submit" style="margin: 2px 2px;">查询
                                    </button>
                                    <a class="btn btn-primary" href="/atlas/addUser">添加家谱成员</a>
                                </div>
                            </div><!-- /.row -->
                        </form>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>姓名</th>
                                <th>性别</th>
                                <th>出生年月</th>
                                <th>创建时间</th>
                                <th>操作</th>
                            </tr>
                            </thead>
                            <tbody>
                            @if (count($lists) === 0)
                                <tr>
                                    <td colspan="11" style="text-align: center;">没有找到订单记录~</td>
                                </tr>
                            @else
                                @foreach ($lists as $value)
                                    <tr>
                                        <td>{{$value->id}}</td>
                                        <td>{{$value->name}}</td>
                                        <td>{{$value->sex == 0 ? '男' : '女'}}</td>
                                        <td>{{$value->birthday}}</td>
                                        <td>{{$value->created_at}}</td>
                                        <td style="cursor: pointer">
                                            <a href="/atlas/myTreeMap?id={{$value->id}}">查看家谱</a> |
                                            <a href="/atlas/editUser?id={{$value->id}}">编辑</a> |
                                            <a onclick="delUser({{$value->id}})">删除</a> |
                                            <a href="/atlas/addUser?id={{$value->id}}">添加下一代</a>
                                        </td>
                                    </tr>
                                @endforeach
                            @endif
                            </tbody>
                        </table>
                        <!-- page -->
                        <div class="box-footer clearfix">
                            <div class="row">
                                <div class="col-sm-3">
                                </div>
                                <div class="col-sm-9">
                                    <div class="text-right">
                                        <span class="total-num">共 {{$lists->total()}} 条数据</span>
                                        {!! str_replace('/?', '?', $lists->appends($filter)->render()) !!}
                                    </div>
                                </div>
                            </div>

                        </div>
                        <!-- /.page-->
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row -->


    </section><!-- /.content -->
</div><!-- /.content-wrapper -->
<script>
    function delUser(id) {
        $.post('/atlas/delUser', {id:id}, function(){
            alert('删除成功');
            location.reload(true);
        });
    }
</script>
<script src="{{asset('js/bootstrap-select.min.js')}}"></script>
<link href="{{asset('css/bootstrap-select.min.css')}}" rel="stylesheet">
@include('footer')
