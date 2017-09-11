<?php

namespace App\Admin\Controllers;

use App\Models\AccountLog;
use App\Models\Company;
use App\Repositories\AccountLogRepository;
use App\Repositories\CipherECB;
use App\Repositories\CompanyRepository;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Illuminate\Http\Request;
use Encore\Admin\Layout\Row;
use Illuminate\Support\Facades\Input;
use Illuminate\Support\MessageBag;

class AccountController extends Controller
{
    use ModelForm;

    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('机构管理');
            $content->description('首页');
        });
    }

    /**
     * Create interface.
     *
     * @return Content
     */
    public function create(Request $request)
    {
        $cid = $request->input('cid');
        return Admin::content(function (Content $content) {
            $content->header('余额管理');
            $content->description('创建');
            $content->body($this->form());
        });
    }

    protected function form()
    {
        $input = Input::all();
        if (isset($input['cid'])) {
            $cid = $input['cid'];
        } else {
            $cid = $input['company_id'];
        }

        $companyMod = new Company();
        $field = ['id', 'amount'];
        $where = [
            ['id', '=', $cid]
        ];
        $account = $companyMod->select($field)->where($where)->first()->toArray();

        //推单积分
        $where = [
            ['company_id', '=', $cid],
            ['type', '=', 1],
        ];
        $accountMod = new AccountLogRepository();
        $account['push'] = $accountMod->getSum($where, 'amount');
        $account['push_number'] = $accountMod->getCount($where);

        //充值积分
        $where = [
            ['company_id', '=', $cid],
            ['type', '=', 2],
        ];
        $account['payment'] = $accountMod->getSum($where, 'amount');

        return Admin::form(AccountLog::class, function (Form $form) use ($account) {

            $form->tools(function (Form\Tools $tools) {
                // 去掉跳转列表按钮
                $tools->disableListButton();
            });

            $form->html($account['amount'], $label = '账户剩余积分');
            $form->html($account['push'], $label = '推单消耗积分');
            $form->html($account['payment'], $label = '充值积分');
            $form->html($account['push_number'], $label = '推送单数');
            $account_type = config('field.account_type');
            $form->select('type', '积分操作类型')->options($account_type);
            $form->number('amount', '积分变动值');
            $form->textarea('remark', '备注')->rows(3)->default();
            $form->hidden('company_id')->value(isset($account['id']) ? $account['id'] : 0);
            $form->saving(function ($form) {
                $companyMod = new CompanyRepository();

                $change_type = config('field.account_change_type');
                if ($change_type[$form->type] == 1) {
                    $re = $companyMod->increaseAmount($form->company_id, $form->amount, $form->type, $form->remark);
                } elseif ($change_type[$form->type] == 2) {
                    $re = $companyMod->deductAmount($form->company_id, $form->amount, $form->type, $form->remark);
                }

                if ($re) {
                    $success = new MessageBag([
                        'title' => 'success',
                        'message' => '操作成功',
                    ]);

                    return back()->with(compact('success'));
                } else {
                    $error = new MessageBag([
                        'title' => 'error',
                        'message' => $companyMod->getError(),
                    ]);

                    return back()->with(compact('error'));
                }
            });
        });
    }

}
