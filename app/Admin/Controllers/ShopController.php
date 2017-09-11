<?php

namespace App\Admin\Controllers;

use App\Models\DayPushLog;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class ShopController extends Controller
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

            $content->header('商家管理');
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

            $content->header('商家管理');
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

            $content->header('商家管理');
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
        return Admin::grid(DayPushLog::class, function (Grid $grid) {

            //禁用一些不要的功能
            $grid->disableActions();
            $grid->disableCreation();
            $grid->disableBatchDeletion();
            $grid->disableRowSelector();
            $grid->disableExport();

            //列表展示
            $grid->id('ID')->sortable();
            $grid->company_name('公司名称');
            $grid->company_num('纳税人识别号');
            $grid->mobile('手机号');
            $grid->c_time('创建时间');
            $grid->u_time('修改时间');

            //搜索框
            $grid->filter(function($filter){
                // 禁用id查询框
                $filter->disableIdFilter();

                $filter->like('company_name','公司名称');
                $filter->like('mobile','手机号');
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
        return Admin::form(DayPushLog::class, function (Form $form) {

        });
    }
}
