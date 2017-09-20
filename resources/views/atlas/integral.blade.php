@include('header')

<script src="{{asset("js/vue/vue.js")}}" type="text/javascript"></script>
<script src="{{asset("js/vue/axios.min.js")}}" type="text/javascript"></script>
<script src="{{asset("js/vue/qs.min.js")}}"></script>
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
                        <form action="/web/integral" id="s_form" style="margin-top:10px;">
                            <div class="row search-form-row">
                                <div class="col-sm-3">
                                    <div class="input-group">
                                        <div class="input-group-addon">购买日期</div>
                                        <input name="startTime" style="margin-bottom:1px;" type="text"
                                               class="form-control input-sm" value="{{ $filter['startTime'] }}"
                                               onclick="WdatePicker({dateFmt: 'yyyy-MM-dd 00:00:00', qsEnabled: true, quickSel: ['%y-%M-{%d-1} 00:00:00', '%y-%M-%d 00:00:00']});">
                                        <input name="endTime" type="text" class="form-control input-sm"
                                               value="{{ $filter['endTime'] }}"
                                               onclick="WdatePicker({dateFmt: 'yyyy-MM-dd 23:59:59', qsEnabled: true, quickSel: ['%y-%M-{%d-1} 00:00:00', '%y-%M-%d 00:00:00']});">
                                    </div>
                                    <div class="input-group">
                                        <div class="input-group-addon">类型</div>
                                        <select class="form-control" name="change_type">
                                            <option value="">— 全部 —</option>
                                            <option value="1">收入</option>
                                            <option value="2">支出</option>
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-2" style="width:300px;">
                                    <a class="btn btn-primary input-sm" href="/web/integral"
                                       style="margin: 2px 2px;">重置</a>
                                    <button class="btn btn-primary input-sm" type="submit" style="margin: 2px 2px;">查询
                                    </button>
                                    <input type="hidden" id="showMode" name="showMode" value="show"/>
                                </div>
                            </div><!-- /.row -->
                        </form>
                    </div><!-- /.box-header -->

                    <div class="box-body">
                        <button type="button" class="btn btn-lg btn-primary" style="margin-bottom: 10px">
                            <span class="glyphicon glyphicon-user"></span> 账户总积分：{{$integral}}
                        </button>
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th>ID</th>
                                <th>交易流水</th>
                                <th>日期</th>
                                <th>变更金额</th>
                                <th>变更后金额</th>
                                <th>备注</th>
                            </tr>
                            </thead>
                            <tbody>
<?php if($lists){ ?>

                                    @foreach($lists as $list)
                                        <tr>
                                            <td>{{ $list->id }}</td>
                                            <td>{{ $list->serial_number }}</td>
                                            <td>{{ $list->c_time }}</td>
                                            <td><?php echo $list->change_type==1?'+':'-'?>{{ $list->amount }}</td>
                                            <td>{{ $list->remain }}</td>
                                            <td>{{ $list->remark }}</td>
                                        </tr>
                                    @endforeach
<?php }else{ ?>

                        
                            <tr>
                                <td colspan="11" style="text-align: center;">没有找到订单记录~</td>
                            </tr>
<?php }?>
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


@include('footer')