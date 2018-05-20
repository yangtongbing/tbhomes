<?php

namespace App\Admin\Extensions;

use Encore\Admin\Admin;
use Encore\Admin\Grid\Tools\AbstractTool;

class ClickData extends AbstractTool
{
    protected function script()
    {
        return <<<SCRIPT
            
SCRIPT;
    }

    public function render()
    {
        Admin::script($this->script());
        return "
            <script>
            function clickData(){
                var Url2=document.getElementById('clickData');
                Url2.select(); // 选择对象
                document.execCommand('Copy'); // 执行浏览器复制命令
                alert('已复制好，可贴粘。');
            }            
            </script>
        ";
    }
}