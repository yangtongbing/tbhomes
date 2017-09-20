<?php

namespace App\Admin\Controllers;

use App\Models\AtlasAdmin;
use App\Repositories\CipherECB;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;

class AtlasController extends Controller
{
    use ModelForm;

    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('家谱管理');
            $content->description('首页');
            $content->body($this->grid());
        });
    }

    /**
     * Edit interface.
     *
     * @param $id
     * @return Content
     */
    public function edit($id)
    {
        return Admin::content(function (Content $content) use ($id) {
            $content->header('家谱管理');
            $content->description('编辑');
            $content->body($this->form()->edit($id));
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create()
    {
        return Admin::content(function (Content $content) {

            $content->header('家谱管理');
            $content->description('创建');
            $content->body($this->form());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(AtlasAdmin::class, function (Grid $grid) {

            //禁用一些不要的功能
            $grid->disableBatchDeletion();
            $grid->disableRowSelector();
            $grid->disableExport();

            $grid->id('ID')->sortable();
            $grid->name('姓名');
            $grid->username('账号');
            $grid->mobile('手机号');
            $grid->created_at('创建时间');

            $grid->filter(function ($filter) {
                // 禁用id查询框
                $filter->disableIdFilter();
                $filter->like('name', '姓名');
                $filter->equal('mobile', '手机号');
                $filter->equal('username', '账号');
                $filter->between('created_at', '创建时间')->datetime();
            });
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(AtlasAdmin::class, function (Form $form) {
            $form->text('name', '姓名')->rules('required|max:6');
            $form->text('username', '账号');
            $form->mobile('mobile', '手机号')->options(['mask' => '99999999999'])->rules('required');
            $form->saving(function (Form $form) {
                if ($form->username == null) {
                    $form->username = $form->mobile;
                }
                $form->model()->password = md5(md5($form->username) . 'atlas');
            });
        });
    }
}
