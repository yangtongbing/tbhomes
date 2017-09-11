<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;

class Record
{
    protected function script()
    {
        return <<<SCRIPT
SCRIPT;
    }

    protected function render()
    {
        Admin::script($this->script());

        return "<div class='modal fade' id='myModal' tabindex='-1' role='dialog' aria-labelledby='myModalLabel'>
    <div class='modal-dialog modal-lg' role='document'>
        <div class='modal-content'>
            <div class='modal-header'>
                <button type='button' class='close' data-dismiss='modal' aria-label='Close'><span aria-hidden='true'>&times;</span>
                </button>
                <h4 class='modal-title' id='myModalLabel'>外呼记录详情</h4>
            </div>
            <div class='modal-body'>
                <div class='row' style='margin-bottom: 12px'>
                    <div class='col-md-2 form-inline'>
                        <label for='username'>通话录音</label>
                    </div>
                    <div class='col-md-4 form-inline'>
                        <audio controls='controls' id='audio'>您的浏览器不支持播放</audio>
                    </div>
                    <div class='col-md-4 form-inline'>
                        <span id='audio_span'></span>
                    </div>
                </div>
                <div class='row' style='margin-bottom: 12px'>
                    <div class='col-md-2 form-inline'>
                        <label for='money'>备注</label>
                    </div>
                    <div class='col-md-4 form-inline'>
                        <span id='desc'></span>
                    </div>
                </div>
            </div>
            <div class='modal-footer'>
                <button type='button' class='btn btn-primary' data-dismiss='modal'>关闭</button>
            </div>
        </div>
    </div>
    </div>
    <script>
    $('#audio_span').hide();
    function getDetail(id){
        $.post('/admin/record/getDetail', {id:id}, function(data){
        console.log(data);
            $('#myModal').modal('show');
            $('#desc').html(data.details.desc);
            if (data.code == 0) {
                $('#audio').parent().show();
                $('#audio_span').hide();
                $('#audio').attr('src', data.details.src);
            } else {
                $('#audio').parent().hide();
                $('#audio_span').show();
                $('#audio_span').html(data.msg);
            }
        });
    }
    </script>";
    }

    public function __toString()
    {
         return $this->render();
    }
}