<?php

namespace App\Admin\Controllers;

use App\Models\Invoice;

use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;

class InvoiceController extends Controller
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

            $content->header('发票管理');
            $content->description('发票列表');

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

            $content->header('发票管理');
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

            $content->header('发票管理');
            $content->descridescriptionion('创建');
            $form = $this->form();

            $content->body($form);
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Invoice::class, function (Grid $grid) {

            //禁用一些不要的功能
            $grid->disableActions();
            $grid->disableCreation();
            $grid->disableBatchDeletion();
            $grid->disableRowSelector();
            $grid->disableExport();

            //展示字段
            $grid->id('ID')->sortable();
            $grid->c_company_title('公司名称');
            $grid->c_company_num('纳税人识别号');
            $grid->status('开票状态')->value(function($status){
                return $status == 1 ? '已开' : '未开';
            });
            $grid->c_time('创建时间');
            $grid->u_time('修改时间');

            //搜索框
            $grid->filter(function($filter) {
                // 禁用id查询框
                $filter->disableIdFilter();

                $filter->like('c_company_title','公司名称');
                $filter->equal('c_company_num','纳税人识别号');
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
        return Admin::form(Invoice::class, function (Form $form) {

            $form->display('id', 'ID');
            $form->text('c_company_title', '公司名称')->rules('required');
            $form->text('c_company_num', '纳税人识别号')->rules('required|min:15|max:20');

        });
    }
}
