<?php

namespace App\Admin\Controllers;

use App\Models\Zone;
use App\Models\Company;
use App\Repositories\OutBoundRepository;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;

class CompanyController extends Controller
{
    use ModelForm;

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index(Request $request)
    {
//        Input::offsetSet('c_time', ['start' => 1503849600, 'end' => 1503935999]);

        return Admin::content(function (Content $content) {

            $content->header('机构管理');
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
            $content->header('公司管理');
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

            $content->header('公司管理');
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
        return Admin::grid(Company::class, function (Grid $grid) {

            //禁用一些不要的功能
            $grid->disableBatchDeletion();
            $grid->disableRowSelector();
            $grid->disableExport();


            $grid->id('ID')->sortable();
            $grid->title('机构名称');
            $grid->leader_name('负责人姓名');
            $grid->amount('剩余积分');
            $grid->unit_price('单价（积分）');
            $grid->day_push_number('最大推单数（单）');
            $grid->push_type('推送方式')->value(function ($default) {
                $push_type_field = config('field.push_type');
                return $push_type_field[$default];
            });
            $grid->c_time('创建时间');
            $grid->push_status('推送状态')->value(function ($default) {
                $push_status_field = config('field.push_status');
                return $push_status_field[$default];
            });

            $grid->actions(function ($actions) {
                // append一个操作
                $actions->append('<a href="' . url('admin/account/create?cid=' . $actions->getKey()) . '"><i class="glyphicon glyphicon-jpy"></i></a>');
            });

            //TODO 找出日期搜索框转化成时间戳方法
            $grid->filter(function ($filter) {
                // 禁用id查询框
                $filter->like('title', '机构名称');
                $filter->is('push_status', '推送状态')->select(config('field.push_status'));

//                $filter->where(function ($query) {
//                    $query->whereRaw(" `c_time` >= ".strtotime($this->input));
//                }, '开始时间')->datetime();
//                $filter->where(function ($query) {
//                    $query->whereRaw(" `c_time` <= ".strtotime($this->input));
//                }, '结束时间')->datetime();
            });

//            Input::offsetSet('c_time', ['start' => '2017-08-02 00:00:00', 'end' => '2018-08-20 00:00:00']);
        });
    }

    /**
     * Make a form builder.
     *
     * @return Form
     */
    protected function form()
    {
        return Admin::form(Company::class, function (Form $form) {
            $form->tab('基本信息', function ($form) {
                $states = [
                    'on' => ['value' => 1, 'text' => '打开', 'color' => 'success'],
                    'off' => ['value' => 0, 'text' => '关闭', 'color' => 'danger'],
                ];

                $form->text('title', '机构名称')->rules('required|max:6');
                $form->text('leader_name', '负责人姓名')->rules('required');
                $form->mobile('mobile', '绑定手机号')->options(['mask' => '99999999999'])->rules('required');
                $form->number('unit_price', '单价')->rules('required');
                $form->number('day_push_number', '每日推单数')->rules('required');
                $form->select('push_type', '推送方式')->options(config('field.push_type'))->rules('required');
                $form->switch('week_push', '周未推送')->states($states)->rules('required');
                $form->switch('holiday_push', '法定假日推送')->states($states)->rules('required');
                $form->switch('push_status', '推送状态')->states($states)->rules('required');

                $form->embeds('settlement_proportion', '', function ($form) {

                    $form->rate('real_money_proportion', '结算比例')->default(0)->rules('required');
                    $form->rate('service_charge_proportion', '结算比例')->default(0)->rules('required');

                });

                $form->textarea('remark', '备注')->rows(3)->default();
            })->tab('推送条件', function ($form) {

                $form->embeds('push_config', '', function ($form) {

                    $zoneMod = new Zone();
                    $field = ['zone_id', 'zone_name'];
                    $where = [
                        ['Rank', '=', '1']
                    ];

                    $info = $zoneMod->select($field)->where($where)->get()->toArray();

                    $options = ['is_fund' => '有公积金', 'is_security' => '有社保', 'credit_card' => '有信用卡', 'house_type' => '有房', 'car_type' => '有车', 'is_buy_insurance' => '有保单'];

                    $profession = config('field.profession');
                    $profession[0] = '请选择';

                    $salary_type = config('field.salary_type');
                    $salary_type[0] = '请选择';

                    $form->number('money_min', '贷款金额(元)');
                    $form->number('money_max', '');

                    $form->number('age_min', '年龄（岁）');
                    $form->number('age_max', '');

                    $form->number('salary_bank_public_min', '月收入（元）');
                    $form->number('salary_bank_public_max', '');

                    $form->number('work_license_min', '当前单位工龄（月）');
                    $form->number('work_license_max', '');

                    $form->multipleSelect('zone_id', '城市')->options(array_pluck($info, 'zone_name', 'zone_id'));

                    $form->select('salary_bank_private', '工资发放形式')->options($salary_type);

                    $form->select('profession', '职业身份')->options($profession);

                    $form->checkbox('necessary', '必要条件')->options($options);

                    $form->checkbox('optional', '次要条件')->options($options);

                });
            })->tab('子账号', function ($form) {
                $form->hasMany('main_account', function (Form\NestedForm $form) {
                    $form->text('name', '姓名');
                    $form->mobile('username', '手机号')->options(['mask' => '99999999999']);
                    $form->text('password', '密码');
                });
            });

            $form->saving(function (Form $form) {
                $form->model()->username = $form->mobile;
                $form->model()->password = md5(md5('hd' . $form->mobile) . 'partner');
            });

            $form->saved(function (Form $form) {
                if (empty($form->model()->seat_no)) {
                    $calloutMod = new OutBoundRepository();
                    $calloutMod->createUser($form->model()->id, $form->model()->mobile, $form->model()->leader_name, false);
                }
            });
        });
    }

}
