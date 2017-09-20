@include('header')

<link rel="stylesheet" href="{{asset('packages/admin/bootstrap-fileinput/css/fileinput.min.css?v=4.3.7')}}">
<script src="{{asset("js/vue/vue.js")}}" type="text/javascript"></script>
<script src="{{asset("js/vue/axios.min.js")}}" type="text/javascript"></script>
<script src="{{asset("js/vue/qs.min.js")}}" type="text/javascript"></script>
<script src="{{asset("packages/admin/bootstrap-fileinput/js/fileinput.min.js?v=4.3.7")}}" type="text/javascript"></script>
<script src="{{asset("packages/admin/bootstrap-fileinput/js/fileinput_locale_zh_CN.js?v=4.3.7")}}" type="text/javascript"></script>
<script src="{{asset("packages/admin/bootstrap-fileinput/js/plugins/canvas-to-blob.min.js?v=4.3.7")}}" type="text/javascript"></script>
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
                        <form action="/web/mycustomer" id="s_form" style="margin-top:10px;">
                            <div class="row search-form-row">
                                <div class="col-sm-3">
                                    <div class="input-group">
                                        <div class="input-group-addon">申请日期</div>
                                        <input name="c_time_start" style="margin-bottom:1px;" type="text"
                                               class="form-control input-sm" value="{{$search['c_time_start']}}"
                                               onclick="WdatePicker({dateFmt: 'yyyy-MM-dd 00:00:00', qsEnabled: true, quickSel: ['%y-%M-{%d-1} 00:00:00', '%y-%M-%d 00:00:00']});">
                                        <input name="c_time_end" type="text" class="form-control input-sm"
                                               value="{{$search['c_time_end']}}"
                                               onclick="WdatePicker({dateFmt: 'yyyy-MM-dd 23:59:59', qsEnabled: true, quickSel: ['%y-%M-{%d-1} 00:00:00', '%y-%M-%d 00:00:00']});">
                                    </div>
                                </div>

                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <div class="input-group-addon">贷款期限<br>(月)</div>
                                        <input name="month_start" style="margin-bottom:1px;" type="text"
                                               class="form-control input-sm" value="{{$search['month_start']}}">
                                        <input name="month_end" type="text" class="form-control input-sm"
                                               value="{{$search['month_end']}}">
                                    </div>
                                </div>

                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <div class="input-group-addon">贷款额度<br>(元)</div>
                                        <input type="text" style="margin-bottom:1px;" name="money_min"
                                               value="{{$search['money_min']}}" class="form-control input-sm"/>
                                        <input type="text" name="money_max" value="{{$search['money_max']}}"
                                               class="form-control input-sm"/>
                                    </div>
                                </div>

                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <div class="input-group-addon">城市</div>
                                        <select name="zone_id" class="form-control input-sm">
                                            <option value="0">-- 全部 --</option>
                                            @foreach($city_list as $k => $v)
                                                <option value="{{$k}}"
                                                        @if ($search['zone_id'] == $k) selected="selected" @endif>
                                                     {{$v}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-2">
                                    <div class="input-group">
                                        <div class="input-group-addon">审核状态</div>
                                        <select name="status" class="form-control input-sm">
                                            <option value="">--全部--</option>
                                            @foreach($audit_status as $k => $v)
                                                <option value="{{$k}}"
                                                        @if ($search['status'] == $k) selected="selected" @endif>
                                                    {{$v}}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                </div>
                                <div class="col-sm-2" style="width:300px;">
                                    <button class="btn btn-primary input-sm" type="submit" style="margin: 2px 2px;">查询
                                    </button>
                                </div>
                            </div><!-- /.row -->
                        </form>
                    </div><!-- /.box-header -->
                    <div class="box-body">
                        <table id="example2" class="table table-bordered table-hover">
                            <thead>
                            <tr>
                                <th width="12%">订单号</th>
                                <th>姓名</th>
                                <th>城市</th>
                                <th>贷款额度</th>
                                <th>贷款期限</th>
                                <th>购买时间</th>
                                <th>用户详情</th>
                                <th>状态设置</th>
                                <th>操作</th>
                                <th>备注</th>
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
                                        <td>{{$value->order_id}}</td>
                                        <td>{{$value->username}}</td>
                                        <td>{{$city_list[$value->zone_id]}}</td>
                                        <td>{{$value->money}}元</td>
                                        <td>{{$value->month}}月</td>
                                        <td>{{$value->c_time}}</td>
                                        <td>
                                            <button class="btn btn-primary input-sm btn-check" style="margin: 2px 2px;" @click="edit({{$value->id}})">查看</button>
                                        </td>
                                        <td>
                                            <select @if($value['status'] == 11) disabled @endif class="show-tick form-control show-ticks-{{$value->id}}" onchange="show_ticks({{$value->id}})" data-live-search="true">
                                                @foreach($audit_status as $k=>$v)
                                                    <option value="{{$k}}"
                                                            @if($k == $value->status) selected @endif
                                                            @if($k == 11) @click="edit({{$value->id}})"  @endif
                                                    >{{$v}}</option>
                                                @endforeach
                                            </select>
                                        </td>
                                        <td>
                                            <button class="btn btn-primary input-sm btn-callout" style="margin: 2px 2px;" @click="edit({{$value->id}}, true)" >拨号</button>
                                        </td>
                                        <td>{{$value->remark}}</td>
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
                                        {!! str_replace('/?', '?', $lists->appends($search)->render()) !!}
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

<!-- Modal -->
<div class="modal fade" id="myModal1" tabindex="-1" role="dialog" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">上传凭证</h4>
            </div>
            <div class="modal-body">
                <h4>凭证包括：1 贷款客户与机构的收费协议；2 贷款客户与放款公司的借贷协议。</h4>

            </div>
        </div>
    </div>
</div>
<div class="modal fade" id="myModal2" tabindex="-1" role="dialog" style="overflow: auto" aria-labelledby="myModalLabel">
    <div class="modal-dialog modal-lg" role="document">
        <div class="modal-content">
            <div class="modal-header">
                <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span aria-hidden="true">&times;</span>
                </button>
                <h4 class="modal-title" id="myModalLabel">用户信息核对</h4>
            </div>
            <div class="modal-body">
                <div class="row" style="margin-bottom: 12px">
                    <div class="col-md-2 form-inline">
                        <label for="username">姓名</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <input disabled class="form-control" id="username" v-model="info.username"/>
                    </div>
                    <div class="col-md-2 form-inline">
                        <label for="zone_id">城市</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <select disabled id="zone_id" name="zone_id" class="form-control" data-live-search="true"
                        v-model="info.zone_id">
                            @foreach($city_list as $k=>$v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row" style="margin-bottom: 12px">
                    <div class="col-md-2 form-inline">
                        <label for="username">年龄</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <input disabled class="form-control" id="age" v-model="info.age"/>
                    </div>
                    <div class="col-md-2 form-inline">
                        <label for="use_company">贷款用途</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <select disabled id="use_company" name="use_company" class="form-control"
                                v-model="info.use_company">
                            @foreach(config('field.use') as $k=>$v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row" style="margin-bottom: 12px">
                    <div class="col-md-2 form-inline">
                        <label for="money">贷款金额</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <input disabled type="text" class="form-control" id="money" v-model="info.money">
                    </div>
                    <div class="col-md-2 form-inline">
                        <label for="city_id">贷款期限</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <input disabled type="text" class="form-control" id="month" v-model="info.month">
                    </div>
                </div>
                <div class="row" style="margin-bottom: 12px">
                    <div class="col-md-2 form-inline">
                        <label for="type">职业类型</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <select disabled name="type" id="type" class="form-control" v-model="info.type">
                            <option value="1">上班族</option>
                            <option value="3">无固定职业</option>
                        </select>
                    </div>
                    <div class="col-md-2 form-inline">
                        <label for="salary_bank_private">工资发放形式</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <select disabled name="type" id="salary_bank_private" class="form-control"
                                v-model="info.salary_bank_private">
                            @foreach (config('field.salary_type') as $k=>$v)
                            <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row" style="margin-bottom: 12px">
                    <div class="col-md-2 form-inline">
                        <label for="work_license">当前单位工龄（月）</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <input disabled type="text" class="form-control" id="work_license" v-model="info.work_license">
                    </div>
                    <div class="col-md-2 form-inline">
                        <label for="salary_bank_public">月收入（元）</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <input disabled type="text" class="form-control" id="salary_bank_public"
                               v-model="info.salary_bank_public">
                    </div>
                </div>
                <div class="row" style="margin-bottom: 12px">
                    <div class="col-md-2 form-inline">
                        <label for="house_type">名下房产情况</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <select disabled name="house_type" id="house_type" class="form-control"
                                v-model="info.house_type">
                            @foreach (config('field.house_type') as $k => $v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-inline">
                        <label for="car_type">名下车产情况</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <select disabled name="car_type" id="car_type" class="form-control" v-model="info.car_type">
                            @foreach (config('field.car_type') as $k => $v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row" style="margin-bottom: 12px">
                    <div class="col-md-2 form-inline">
                        <label for="is_fund">是否有公积金</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <select disabled name="is_fund" id="is_fund" class="form-control" v-model="info.is_fund">
                            @foreach (config('field.is_fund') as $k => $v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-inline">
                        <label for="is_security">是否有社保</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <select disabled name="is_security" id="is_security" class="form-control" v-model="info.is_security">
                            @foreach (config('field.is_security') as $k => $v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
                    </div>
                </div>
                <div class="row" style="margin-bottom: 12px">
                    <div class="col-md-2 form-inline">
                        <label for="credit_card">是否有信用卡</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <select disabled name="credit_card" id="credit_card" class="form-control"
                                v-model="info.credit_card">
                            @foreach (config('field.credit_card') as $k => $v)
                                <option value="{{$k}}">{{$v}}</option>
                            @endforeach
                        </select>
                    </div>
                    <div class="col-md-2 form-inline">
                        <label for="is_buy_insurance">是否有保单</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <select disabled name="is_buy_insurance" id="is_buy_insurance" class="form-control"
                                v-model="info.is_buy_insurance">
                            <option value="-1"></option>
                            <option value="1">没有</option>
                            <option value="2">有</option>
                        </select>
                    </div>
                </div>
                <div class="row" style="margin-bottom: 12px">
                    <div class="col-md-2 form-inline">
                        <label for="username">备注</label>
                    </div>
                    <div class="col-md-10">
                    <textarea  class="form-control textarea-input" style="width: 90%;" rows="3" v-model="info.remark"></textarea>
                    </div>
                </div>
                <div class="row" style="margin-bottom: 12px">
                    <div class="col-md-2">
                        <label>审核状态</label>
                    </div>
                    <div class="col-md-10">
                        <button class="btn btn-sm" style="margin-right: 12px;margin-bottom:6px " v-for="(item, index) in audit_status"
                                :class="item.is_active" type="button" @click="set_status(index, false)">@{{item.title}}
                        </button>
                    </div>
                </div>
                <div class="row real_money" style="margin-bottom: 12px;" >
                    <div class="col-md-2">
                        <label>结算方式</label>
                    </div>
                    <div class="col-md-10 form-inline" style="margin-bottom: 6px;">
                        <select id="settlement_type" class="form-control" v-model="info.settlement_type">
                            <option value=0>请选择结算方式</option>
                            <option value=1>按放款金额结算</option>
                            <option value=2>按服务费结算</option>
                            <option value=3>放款金额+服务费结算</option>
                        </select>
                    </div>
                    <div class="col-md-2">
                        <label>真实下款金额</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <input class="form-control" id="real_money" placeholder="真实下款金额" v-model="info.real_money"/>
                    </div>
                    <div class="col-md-2">
                        <label>服务费</label>
                    </div>
                    <div class="col-md-4 form-inline">
                        <input class="form-control" id="service_charge" placeholder="服务费" v-model="info.service_charge"/>
                    </div>
                    <div class="col-md-2">

                    </div>
                    <div class="col-md-10 form-inline">
                        <label style="color: red;">注意：结算信息确认后不可修改，好贷将自动从您账户扣除相应返佣金额。</label>
                    </div>
                </div>
                <div class="row real_money" style="margin-bottom: 12px;" >
                    <div class="col-md-2">
                        <label>上传凭证</label>
                    </div>
                    <div class="form-group">
                        <form enctype="multipart/form-data" action="/web/uploadFile" method="post">
                            <input type="hidden" id="file_id" v-model="info.id">
                            <div class="form-group col-md-10">
                                <input id="file-1" type="file" maxlength="50" multiple class="file" name="file[]">
                            </div>
                        </form>
                    </div>
                    <div class="col-md-2">

                    </div>
                    <div class="col-md-10 form-inline">
                        <label style="color: red;">注意：凭证包括：1、贷款客户与机构的收费协议；2、贷款客户与放款公司的借贷协议。</label>
                    </div>
                </div>
                <div class="row" style="margin-bottom: 12px">
                    <div class="col-md-2">
                        <label>状态变更</label>
                    </div>
                    <div class="col-md-10">
                        <table class="table table-bordered table-hover">
                            <tr>
                                <th>时间</th>
                                <th>状态</th>
                                <th>备注</th>
                            </tr>
                            <tr v-for="(item, index) in info.statusLog">
                                <td>@{{item.c_time}}</td>
                                <td>@{{item.order_status}}</td>
                                <td>@{{item.remark}}</td>
                            </tr>
                        </table>

                    </div>
                </div>
            </div>
            <div class="modal-footer">
                <button type="button" class="btn btn-default" data-dismiss="modal">取消</button>
                <button type="button" class="btn btn-primary" @click="edit()">确认信息，并保存</button>
            </div>
        </div>
    </div>
</div>
<script type="text/javascript">
    var app = new Vue({
        el: '#example2',
        data: {},
        methods: {
            edit: function (id, is_call, extra) {
                axios.post('/web/orderEdit', {
                            oid: id
                        })
                        .then(function (response) {
                            if (response.data.code == 0) {
                                modalApp.info = response.data.details.data;
                                modalApp.info.statusLog = response.data.details.statusLog;
                                modalApp.admin_id = response.data.msg;
                                //子账号信息
                                modalApp.sub_sign = response.data.details.data.sub_sign;
                                modalApp.sub_id = response.data.details.data.sub_id;

                                if(is_call == true){
                                    modalApp.call(modalApp.info.order_id);
                                }

                                //刷新fileinput
                                initialPreviewConfig = modalApp.info.initialPreviewConfig;
                                initialPreview = modalApp.info.initialPreview;
                                $('#file-1').fileinput('destroy');
                                fileinput(initialPreviewConfig, initialPreview);

                                //如果是进来就是完成放款状态
                                if (modalApp.info.status > 0) {
                                    modalApp.set_status(modalApp.info.status, true);
                                } else {
                                    modalApp.set_status(1, false);
                                }

                                if (extra) {
                                    modalApp.set_status(extra, false);
                                }

                                $('#myModal2').modal('show');
                            } else {
                                alert(response.data.msg);
                                return false;
                            }
                        })
                        .catch(function (error) {
                            console.log(error);
                        });
            }
        }
    });

    var modalApp = new Vue({
        el: '#myModal2',
        data: {
            audit_status: {
                1: {
                    title: '待审核',
                    is_active: 'btn-default',
                },
                2: {
                    title: '条件不符',
                    is_active: 'btn-default',
                },
                3: {
                    title: '电话未接通',
                    is_active: 'btn-default',
                },
                4: {
                    title: '用户考虑中',
                    is_active: 'btn-default',
                },
                5: {
                    title: '用户放弃',
                    is_active: 'btn-default',
                },
                6: {
                    title: '邀约到店中',
                    is_active: 'btn-default',
                },
                7: {
                    title: '提交资料',
                    is_active: 'btn-default',
                },
                8: {
                    title: '提交审批中',
                    is_active: 'btn-default',
                },
                9: {
                    title: '审批通过',
                    is_active: 'btn-default',
                },
                10: {
                    title: '审批未通过',
                    is_active: 'btn-default',
                },
                11: {
                    title: '完成放款',
                    is_active: 'btn-default',
                }
            },
            info: {},
            admin_id: 0
        },
        methods: {
            set_status: function (id, sign) {
                for (item in modalApp.audit_status) {
                    modalApp.audit_status[item].is_active = 'btn-default';
                }
                modalApp.audit_status[id].is_active = 'btn-primary';
                modalApp.info.audit_status = id;
                //初始进来就是完成放款状态，禁用标签
                if (id == 11) {
                    if (sign == true) {
                        $('.btn-sm').attr('disabled', true);
                        $('#real_money').attr('disabled', true);
                        $('#service_charge').attr('disabled', true);
                        $('#settlement_type').attr('disabled', true);
                    }
                    $('.real_money').show();
                } else {
                    $('.btn-sm').attr('disabled', false);
                    $('#real_money').attr('disabled', false);
                    $('#service_charge').attr('disabled', false);
                    $('#settlement_type').attr('disabled', false);
                    $('.real_money').hide();
                }
            },
            edit: function () {
                axios.post('/web/doEdit', modalApp.info)
                        .then(function (response) {
                            if (response.data.code == 0) {
                                $('#myModal2').modal('hide');
                                location.reload();
                            } else {
                                alert(response.data.msg);
                                return false;
                            }
                        })
                        .catch(function (error) {
                            console.log(error);
                        });
            },
            call: function (oid) {
                var options = {
                    "mobile":modalApp.info.mobile,
                    "id":modalApp.admin_id,
                    "type":1,
                    "source":oid,
                    "sub_sign":modalApp.sub_sign,
                    "sub_id":modalApp.sub_id
                };
                axios({
                    method: 'post',
                    url: '/api/callOut',
                    data: Qs.stringify(options),
                    headers:{'Content-Type':'application/x-www-form-urlencoded'}
                })
                .then(function(response){
                    if(response.data.code != 0){
                        alert(response.data.msg);
                    }
                    modalApp.info.record_id = response.data.details.id;
                });
            },
        }
    });

    function fileinput(initialPreviewConfig, initialPreview){
        //bootstrap上传
        $("#file-1").fileinput({
            language: 'zh',
            showCaption: true,//是否显示标题
            uploadUrl: "/web/uploadFile", //上传的地址(访问接口地址)
            allowedFileExtensions: ['jpg', 'png', 'jpeg'],//接收的文件后缀
            browseClass: "btn btn-primary", //按钮样式
            validateInitialCount:true,
            showRemove: false, //不展示一键清空按钮，逻辑比较复杂
            previewFileIcon: "<i class='glyphicon glyphicon-king'></i>",
            showUpload: false,
            initialPreviewConfig: initialPreviewConfig,
            initialPreview: initialPreview,
            //上传附件参数
            uploadExtraData: function (previewId, index) {
                var info = {"order_id": $('#file_id').val()};
                return info;
            },
            msgLoading: '加载中',
            maxFileCount: 10, //最大文件数
            overwriteInitial: false,
            uploadAsync: true, //上传同步
            //样式调节
            previewSettings:{
                image: {width: "auto", height: "auto", 'max-width': "100%",'max-height': "100%"},
                html: {width: "100%", height: "100%", 'min-height': "480px"},
                text: {width: "100%", height: "100%", 'min-height': "480px"},
                video: {width: "auto", height: "100%", 'max-width': "100%"},
                audio: {width: "100%", height: "30px"},
                flash: {width: "auto", height: "480px"},
                object: {width: "auto", height: "480px"},
                pdf: {width: "100%", height: "100%", 'min-height': "480px"},
                other: {width: "auto", height: "100%", 'min-height': "480px"}
            }
        });
    }


    //更改订单状态
    function show_ticks(id) {
        var status = $('.show-ticks-' + id).val();
        if (status == 11) {
            app.edit(id, false, status);
        } else {
            $.post('/web/doEdit', {id:id,audit_status:status}, function(response){
                if (response.code == 0) {
                    $('#myModal2').modal('hide');
                    location.reload();
                } else {
                    alert(response.data.msg);
                    return false;
                }
                location.reload();
            })
        }
    }
</script>
<script src="{{asset('js/bootstrap-select.min.js')}}"></script>
<link href="{{asset('css/bootstrap-select.min.css')}}" rel="stylesheet">
@include('footer')
