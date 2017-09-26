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
                    <div class="box-body">
                        @if (count($errors) > 0)
                            <div class="alert alert-danger">
                                <ul>
                                    @foreach ($errors->all() as $error)
                                        <li>{{ $error }}</li>
                                    @endforeach
                                </ul>
                            </div>
                        @endif
                        <form action="/atlas/doEditUser" method="post">
                            <table class="table table-bordered table-hover">

                                <input name="id" value="{{$lists['id']}}" type="hidden"/>
                                <tr>
                                    <td style="width:100px;">姓名：</td>
                                    <td><input name="name" value="{{$lists['name']}}" type="text" /></td>
                                </tr>
                                <tr>
                                    <td>手机：</td>
                                    <td>
                                        <input name="mobile" value="{{$lists['mobile']}}" type="text" />
                                    </td>
                                </tr>
                                <tr>
                                    <td>性别：</td>
                                    <td>
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="sex" @if($lists['sex'] == 0) checked @endif value="0">
                                                男
                                            </label>
                                        </div>
                                        <div class="radio disabled">
                                            <label>
                                                <input type="radio" name="sex" @if($lists['sex'] == 1) checked @endif  value="1">
                                                女
                                            </label>
                                        </div>
                                    </td>
                                </tr>
                                <tr>
                                    <td>出生年月：</td>
                                    <td>
                                        <input name="birthday" style="margin-bottom:1px;" type="text"
                                               class="form-control input-sm" value="{{$lists['birthday']}}"
                                               onclick="WdatePicker({dateFmt: 'yyyy-MM-dd', qsEnabled: true, quickSel: ['%y-%M-{%d-1} 00:00:00', '%y-%M-%d 00:00:00']});">
                                    </td>
                                </tr>
                                <tr>
                                    <td colspan="2">
                                        <input type="reset" value="重置" class="btn btn-primary"/>
                                        <input type="submit" value="提交" class="btn btn-primary"/>
                                    </td>
                                </tr>
                            </table>
                        </form>
                    </div><!-- /.box-body -->
                </div><!-- /.box -->
            </div><!-- /.col -->
        </div><!-- /.row -->


    </section><!-- /.content -->
</div><!-- /.content-wrapper -->

<script src="{{asset('js/bootstrap-select.min.js')}}"></script>
<link href="{{asset('css/bootstrap-select.min.css')}}" rel="stylesheet">
@include('footer')
