<!DOCTYPE html>
<html>
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <title>{{$title}}</title>
    <!-- Tell the browser to be responsive to screen width -->
    <meta content="width=device-width, initial-scale=1, maximum-scale=1, user-scalable=no" name="viewport">
    <!-- Bootstrap 3.3.5 -->
    <link rel="stylesheet" href="{{ asset("/packages/admin/AdminLTE/bootstrap/css/bootstrap.min.css") }}">
    <!-- Font Awesome -->
    <link rel="stylesheet" href="{{ asset("/packages/admin/font-awesome/css/font-awesome.min.css") }}">
    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset("/packages/admin/AdminLTE/dist/css/AdminLTE.min.css") }}">
    <!-- iCheck -->
    <link rel="stylesheet" href="{{ asset("/packages/admin/AdminLTE/plugins/iCheck/square/blue.css") }}">

    <!-- Theme style -->
    <link rel="stylesheet" href="{{ asset("/packages/admin/AdminLTE/dist/css/skins/" . config('admin.skin') .".min.css") }}">
    <!-- HTML5 Shim and Respond.js IE8 support of HTML5 elements and media queries -->
    <!-- WARNING: Respond.js doesn't work if you view the page via file:// -->
    <!--[if lt IE 9]>
    <script src="https://oss.maxcdn.com/html5shiv/3.7.3/html5shiv.min.js"></script>
    <script src="https://oss.maxcdn.com/respond/1.4.2/respond.min.js"></script>
    <![endif]-->
</head>
<body class="hold-transition">
<div class="login-box container">
    <div class="login-logo">
        <b>随手一写</b>
    </div>
    @if (count($errors) > 0)
        <div class="alert alert-danger">
            <ul>
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form class="form-signin" action="/atlas/doLogin" method="post">
        {{--<input type="hidden" name="_token" value="{{ csrf_token() }}">--}}
        <div class="row">
            <div class="col-md-12">
                <input type="text" name="account" class="form-control" placeholder="账号" required autofocus>
            </div>
        </div>
        <div class="clear" style="margin: 3px"></div>
        <div class="row">
            <div class="col-md-12">
                <input type="password" name="password" class="form-control" placeholder="密码" required>
            </div>
        </div>
        <div class="clear" style="margin: 3px"></div>
        <div class="row" style="margin-bottom: 10px">
            <div class="col-xs-7">
                <input class="form-control" name="captcha" type="text" id="verify" maxlength="5" placeholder="验证码" required>
            </div>
            <div class="col-xs-5">
                <img border="0" width="80%" height="34px" style="cursor:pointer"  onclick="getimgcode()" id="verifyImg">
            </div>
        </div>
        {{--<div class="checkbox">--}}
            {{--<label>--}}
                {{--<input type="checkbox" name="check" value="1"> Remember me--}}
            {{--</label>--}}
        {{--</div>--}}
        <button class="btn btn-lg btn-primary btn-block" type="submit">Sign in</button>
    </form>


    <!-- /.login-box-body -->
</div>
<!-- jQuery 2.1.4 -->
<script src="{{ asset("/packages/admin/AdminLTE/plugins/jQuery/jQuery-2.1.4.min.js")}} "></script>
<!-- Bootstrap 3.3.5 -->
<script src="{{ asset("/packages/admin/AdminLTE/bootstrap/js/bootstrap.min.js")}}"></script>
<!-- iCheck -->
<script src="{{ asset("/packages/admin/AdminLTE/plugins/iCheck/icheck.min.js")}}"></script>
<!-- /.login-box -->
<script type="application/javascript">
    $(function(){
        getimgcode();
    });

    function getimgcode()
    {
        var token = $("input[name='_token']").val();
        $.post('/atlas/imgCode', {_token:token}, function(data){
            $('#verifyImg').attr('src', data.details.code);
        });
    }

    $(function () {
        $('input').iCheck({
            checkboxClass: 'icheckbox_square-blue',
            radioClass: 'iradio_square-blue',
            increaseArea: '20%' // optional
        });
    });
</script>
</body>
</html>

