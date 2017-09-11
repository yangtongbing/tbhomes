<?php

namespace App\Admin\Controllers;

use App\Models\User;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class UserController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('用户管理');
            $content->description('列表');

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

            $content->header('用户管理');
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

            $content->header('用户管理');
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
        return Admin::grid(User::class, function (Grid $grid) {

            //禁用一些不要的功能
            $grid->disableActions();
            $grid->disableCreation();
            $grid->disableBatchDeletion();
            $grid->disableRowSelector();
            $grid->disableExport();

            //列表展示数据
            $grid->id('ID')->sortable();
            $grid->username('用户名');
            $grid->mobile('手机号');
            $grid->c_time('创建时间');
            $grid->u_time('修改时间');

            //搜索框
            $grid->filter(function ($filter) {
                // 禁用id查询框
                $filter->disableIdFilter();

                $filter->like('username','用户名');
                $filter->equal('mobile','手机号');
//                $filter->between('c_time','创建时间')->datetime();
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
        return Admin::form(User::class, function (Form $form) {
            //form表单
//            $form->display('id', 'ID');
            $form->text('username', '用户名');
            $form->mobile('mobile', '手机号');
        });
    }
}
