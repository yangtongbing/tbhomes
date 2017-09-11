<?php

/**
 * Created by PhpStorm.
 * User: zhaojipeng
 * Date: 17/9/4
 * Time: 11:14
 */

namespace App\Admin\Extensions\Layouts;

use Illuminate\Contracts\Support\Renderable;

class Image implements Renderable
{
    protected $view = 'admin.image';
    protected $image;

    public function addImg($imgPath)
    {
        $this->image[] = $imgPath;
    }

    private function variables()
    {
        return [
            'img' => $this->image
        ];
    }

    public function render()
    {
        return view($this->view, $this->variables())->render();
    }
}