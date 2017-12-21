<?php

/**
 * Created by PhpStorm.
 * User: yangtongbing
 * Date: 17/9/4
 * Time: 11:14
 */

namespace App\Admin\Extensions\Layouts;

use Illuminate\Contracts\Support\Renderable;

class Row implements Renderable
{
    protected $view = 'admin.row';
    protected $cols;

    public function addCol($width, $content)
    {
        $this->cols[] = ['width' => $width, 'content' => $content];
    }

    private function variables()
    {
        return [
            'row' => $this->cols
        ];
    }

    public function render()
    {
        return view($this->view, $this->variables())->render();
    }
}