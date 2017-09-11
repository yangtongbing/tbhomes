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
use function foo\func;


class OrderController extends Controller
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

            $content->header('订单列表');

            $content->body($this->grid());
        });
    }

    public function show($id, OrderRepository $orderRepository, ZoneRepository $zoneRepository, OrderStatusRepository $orderStatusRepository, OrderExtRepository $orderExtRepository)
    {
        $zoneInfo = $zoneRepository->getZoneList();

        $where = [
            ['id', '=', $id]
        ];
        $orderInfo = $orderRepository->getOne('*', $where);

        $where = [
            ['oid', '=', $id]
        ];
        $orderStatusInfo = $orderStatusRepository->getAll($where);

        $orderExtInfo = $orderExtRepository->getOne('img_path', $where);


        $rederData = [
            'orderInfo' => $orderInfo,
            'zoneInfo' => $zoneInfo,
            'orderStatusInfo' => $orderStatusInfo,
            'orderExtInfo' => json_decode($orderExtInfo['img_path'], true),
        ];

        return Admin::content(function (Content $content) use ($rederData) {

            $content->header('返佣对账');

            $userInfo = new Row();


            if (!empty($rederData['orderInfo']['username'])) {
                $userInfo->addCol(3, '姓名：' . $rederData['orderInfo']['username']);
            }
            if (!empty($rederData['orderInfo']['age'])) {
                $userInfo->addCol(3, '年龄：' . $rederData['orderInfo']['age'] . '岁');
            }
            if (!empty($rederData['orderInfo']['money'])) {
                $userInfo->addCol(3, '贷款金额：' . $rederData['orderInfo']['money'] . '元');
            }
            if (!empty($rederData['orderInfo']['month'])) {
                $userInfo->addCol(3, '贷款期限：' . $rederData['orderInfo']['month'] . '月');
            }
            if (!empty($rederData['orderInfo']['salary_bank_private'])) {
                $userInfo->addCol(3, '工资发放形式：' . array_get(config('field.salary_type'), $rederData['orderInfo']['salary_bank_private']));
            }
            if (!empty($rederData['orderInfo']['zone_id'])) {
                $userInfo->addCol(3, '工作所在地：' . array_get($rederData['zoneInfo'], $rederData['orderInfo']['zone_id']));
            }
            if (!empty($rederData['orderInfo']['work_license'])) {
                $userInfo->addCol(3, '当前单位工龄：' . $rederData['orderInfo']['work_license'] . '月');
            }
            if (!empty($rederData['orderInfo']['salary_bank_public'])) {
                $userInfo->addCol(3, '月收入：' . $rederData['orderInfo']['salary_bank_public'] . '元');
            }
            if (!empty($rederData['orderInfo']['use_company'])) {
                $userInfo->addCol(3, '贷款用途：' . array_get(config('field.use'), $rederData['orderInfo']['use_company']));
            }
            if (!empty($rederData['orderInfo']['profession'])) {
                $userInfo->addCol(3, '职业身份：' . array_get(config('field.profession'), $rederData['orderInfo']['profession']));
            }
            if (!empty($rederData['orderInfo']['credit_card'])) {
                $userInfo->addCol(3, '信用情况：' . array_get(config('field.credit_card'), $rederData['orderInfo']['credit_card']));
            }
            if (!empty($rederData['orderInfo']['is_buy_insurance'])) {
                $userInfo->addCol(3, '保单情况：' . array_get(config('field.is_buy_insurance'), $rederData['orderInfo']['is_buy_insurance']));
            }
            if (!empty($rederData['orderInfo']['house_type'])) {
                $userInfo->addCol(3, '名下房产情况：' . array_get(config('field.house_type'), $rederData['orderInfo']['house_type']));
            }
            if (!empty($rederData['orderInfo']['car_type'])) {
                $userInfo->addCol(3, '名下车产情况：' . array_get(config('field.car_type'), $rederData['orderInfo']['car_type']));
            }
            if (!empty($rederData['orderInfo']['is_fund'])) {
                $userInfo->addCol(3, '是否有本地公积金：' . array_get(config('field.is_fund'), $rederData['orderInfo']['is_fund']));
            }
            if (!empty($rederData['orderInfo']['is_security'])) {
                $userInfo->addCol(3, '是否有本地社保：' . array_get(config('field.is_security'), $rederData['orderInfo']['is_security']));
            }

            $content->row(new Box('用户信息', $userInfo));


            //状态变更
            if (empty($rederData['orderStatusInfo']) === false && is_array($rederData['orderStatusInfo'])) {
                $statusLog = new Row();
                foreach ($rederData['orderStatusInfo'] as $item) {
                    $statusLog->addCol(4, $item['c_time']);
                    $statusLog->addCol(4, '状态变更为：' . array_get(config('field.audit_status'), $item['order_status']));
                    $statusLog->addCol(4, empty($item['remark']) ? '备注信息：无' : '备注信息：' . $item['remark']);
                }
                $content->row(new Box('状态变更', $statusLog));
            }

            //结算信息
            if ($rederData['orderInfo']['real_money'] > 0 && $rederData['orderInfo']['service_charge'] > 0 && $rederData['orderInfo']['bonus'] > 0) {
                $settlement = new Row();
                $settlement->addCol(3, '实际放款金额：' . $rederData['orderInfo']['real_money'] . '元');
                $settlement->addCol(3, '服务费金额：' . $rederData['orderInfo']['service_charge'] . '元');

                $str = '好贷分成：' . $rederData['orderInfo']['bonus'] . '元';
                
                switch($rederData['orderInfo']['settlement_type']){
                    case 1:
                        $str .= '（放款金额 *' . $rederData['orderInfo']['settlement_proportion']['real_money_proportion'] . '%）';
                        break;
                    case 2:
                        $str .= '（服务费 *' . $rederData['orderInfo']['settlement_proportion']['service_charge_proportion'] . '%）';
                        break;
                    case 3:
                        $str .= '(放款金额 * ' . $rederData['orderInfo']['settlement_proportion']['real_money_proportion'] . '% + 服务费 * '
                            . $rederData['orderInfo']['settlement_proportion']['service_charge_proportion'] . '%)';
                        break;
                }

                $settlement->addCol(3, $str);

                $content->row(new Box('结算信息', $settlement));
            }

            //上传凭证
            if (is_array($rederData['orderExtInfo']) && empty($rederData['orderExtInfo']) === false) {
                $img = new Image();
                foreach ($rederData['orderExtInfo'] as $item) {
                    $img->addImg('/upload/' . $item);
                }

                $content->row(new Box('上传凭证（凭证包括：1 贷款客户与机构的收费协议；2 贷款客户与放款公司的借贷协议）', $img));
            }
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

            //获取城市列表
            $zoneRepository = new ZoneRepository();
            $zoneInfo = $zoneRepository->getZoneList();

            //获取所有机构列表
            $companyMod = new CompanyRepository();
            $companyInfo = $companyMod->getAll(['title', 'amount', 'unit_price', 'id'], true);
            $companyInfo = array_set_key($companyInfo, 'id');
            $grid->id('ID')->sortable();

            $grid->company_id('推送机构（ID）')->display(function ($company_id) use ($companyInfo) {
                return $companyInfo[$company_id]['title'] . '(' . $companyInfo[$company_id]['id'] . ')';
            });
            $grid->order_id('订单号');
            $grid->username('姓名');
            $grid->zone_id('城市')->display(function ($zone_id) use ($zoneInfo) {
                return $zoneInfo[$zone_id];
            });
            $grid->money('贷款金额')->display(function ($money) {
                if ($money > 10000) {
                    $money = bcdiv($money, 10000, 2);
                    return $money . '万元';
                } else {
                    return $money . '元';
                }
            });
            $grid->month('贷款期限')->display(function ($month) {
                return $month . '月';
            });
            $grid->c_time('推送时间');
            $grid->status('状态')->value(function ($default) {
                $status = config('field.audit_status');
                return $status[$default];
            });

            $grid->actions(function ($actions) {
                //禁用修改和删除
                $actions->disableDelete();
                $actions->disableEdit();

                $actions->append('<a href="' . url('admin/order', ['id' => $actions->getKey()]) . '" style="margin-right: 5px;"><i class="fa fa-eye"></i></a>');
                $actions->append('<a href="' . url('admin/record?source_id='.$actions->row->order_id) . '" style="margin-right: 5px;" alt="外呼记录"><i class="fa fa-phone"></i></a>');
                $actions->append('<a href="' . url('admin/order', ['id' => $actions->getKey()]) . '" style="margin-right: 5px;"  alt="审核记录"><i class="fa fa-phone-square"></i></a>');

            });

            $grid->filter(function ($filter) use($zoneInfo, $companyInfo) {
                $filter->disableIdFilter();

                $filter->is('company_id', '机构ID')->select(array_pluck($companyInfo, 'title', 'id'));
                $filter->is('order_id', '订单号');

                $filter->is('status', '审核状态')->select(config('field.audit_status'));
                $filter->is('zone_id', '推送城市')->select($zoneInfo);

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
