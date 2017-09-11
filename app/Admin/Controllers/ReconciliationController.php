<?php

namespace App\Admin\Controllers;

use App\Admin\Extensions\Layouts\Image;
use App\Admin\Extensions\Layouts\Row;
use App\Models\Order;
use App\Repositories\CompanyRepository;
use App\Repositories\OrderExtRepository;
use App\Repositories\OrderRepository;
use App\Repositories\OrderStatusRepository;
use App\Repositories\ZoneRepository;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Encore\Admin\Widgets\Box;


class ReconciliationController extends Controller
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

            $content->header('返佣对账');

            $content->body($this->grid());
        });
    }

    /**
     * Make a grid builder.
     *
     * @return Grid
     */
    protected function grid()
    {
        return Admin::grid(Order::class, function (Grid $grid) {

            //禁用一些不要的功能
            $grid->disableCreation();
            $grid->disableBatchDeletion();
            $grid->disableRowSelector();
            $grid->disableExport();

            //获取所有机构列表
            $companyMod = new CompanyRepository();
            $companyInfo = $companyMod->getAll(['title', 'amount', 'unit_price', 'id']);
            $companyInfo = array_set_key($companyInfo, 'id');

            //计算每个企业成单率
            $orderMod = new OrderRepository();
            foreach ($companyInfo as $key => $item) {
                $where = [
                    ['company_id', '=', $item['id']],
                ];

                $allCount = $orderMod->count($where);
                $redayCount = $orderMod->count(array_merge($where, [['status', '=', 11]]));


                $companyInfo[$key]['allCount'] = $allCount;
                $companyInfo[$key]['redayCount'] = $redayCount;

                if ($redayCount > 0) {
                    $companyInfo[$key]['singleRate'] = bcdiv($redayCount, $allCount, 2) * 100;
                }else{
                    $companyInfo[$key]['singleRate'] = 0;
                }
            }


            //条件筛选
            $grid->model()->where('status', '=', 11)->whereIn('company_id', array_keys($companyInfo));


            $grid->order_id('订单号')->sortable();
            $grid->status('审核状态')->value(function ($default) {
                $status = config('field.audit_status');
                return $status[$default];
            });

            $grid->column('title', '推送机构（成单率）')->display(function () use ($companyInfo) {
                return $companyInfo[$this->company_id]['title'] . '(' . $companyInfo[$this->company_id]['singleRate'] . '%)';
            });

            $grid->column('unit_price', '单价/剩余(积分)')->display(function () use ($companyInfo) {
                return $companyInfo[$this->company_id]['unit_price'] . '/' . $companyInfo[$this->company_id]['amount'];
            });

            $grid->real_money('放款金额(元)');

            $grid->service_charge('服务费（元）');

//            $grid->settlement_type('结算方式')->value(function ($default) {
//                $settlement = config('field.settlement');
//                return $settlement[$default];
//            });

//            $grid->settlement_proportion('结算比例')->value(function ($default) {
//                return '放款金额*'.$default . '%服务费*3%';
//            });
            // TODO 返佣时间
            $grid->settlement_time('返佣时间')->display(function($time){
                return date('Y-m-d H:i:s', $time);
            });

            $grid->bonus('好贷分成（元）');


            $grid->actions(function ($actions) {
                //禁用修改和删除
                $actions->disableDelete();
                $actions->disableEdit();

                $actions->append('<a href="' . url('admin/order', ['id' => $actions->getKey()]) . '"><i class="fa fa-eye"></i></a>');
//                $actions->append('<a href="' . url('admin/reconciliation', ['id' => $actions->getKey()]) . '"><i class="fa fa-phone"></i></a>');

            });

            $grid->filter(function ($filter) {
                $filter->is('company_id', '机构ID');

                $filter->where(function ($query) {
                    $query->whereRaw(" `c_time` >= " . strtotime($this->input));
                }, '开始时间')->datetime();
                $filter->where(function ($query) {
                    $query->whereRaw(" `c_time` <= " . strtotime($this->input));
                }, '结束时间')->datetime();
            });
        });
    }

}
