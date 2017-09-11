<?php
/**
 * 录音管理
 */
namespace App\Admin\Controllers;

use App\Models\Company;
use App\Models\Record;
use App\Repositories\RecordRepository;
use Encore\Admin\Form;
use Encore\Admin\Grid;
use Encore\Admin\Facades\Admin;
use Encore\Admin\Layout\Content;
use App\Http\Controllers\Controller;
use Encore\Admin\Controllers\ModelForm;
use Symfony\Component\HttpFoundation\Request;

class RecordController extends Controller
{
    use ModelForm;

    public function __construct(RecordRepository $recordRepository)
    {
        $this->repository = $recordRepository;
    }

    /**
     * Index interface.
     *
     * @return Content
     */
    public function index()
    {
        return Admin::content(function (Content $content) {

            $content->header('录音管理');
            $content->description('列表');

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
        return Admin::grid(Record::class, function (Grid $grid) {

            //禁用一些不要的功能
            $grid->disableCreation();
            $grid->disableRowSelector();
            $grid->disableExport();

            //展示字段
            $grid->id('ID')->sortable();
            $grid->company_id('机构名称')->value(function($company_id){
                $company = new Company();
                $data = $company->where('id', '=', $company_id)->select('title')->get()->toArray();
                if (!empty($data)) {
                    return $data[0]['title'];
                } else {
                    return '/';
                }
            });
            $grid->seat_no('坐席号');
            $grid->source_id('来源标识');
            $grid->status('状态')->value(function($status){
                switch ($status) {
                    case 0:
                        return '呼叫中';
                        break;
                    case 1:
                        return '未接通';
                        break;
                    case 2:
                        return '正在通话';
                        break;
                    case 3:
                        return '已挂断';
                        break;
                    default:
                        return '异常话单';
                        break;
                }
            });
            $grid->duration('通话时长')->value(function($duration){
                return $duration.'s';
            });

            $grid->actions(function ($actions) {
                $actions->disableDelete();
                $actions->disableEdit();
                $actions->append(new \App\Admin\Extensions\Record());
                $actions->append('<button class="btn btn-primary input-sm" style="margin: 2px 2px;" onclick="getDetail('. $actions->getKey().')">查看详情</button>');
            });

            $grid->c_time('创建时间');
            $grid->model()->orderBy('id', 'desc');

            //搜索框
            $grid->filter(function($filter) {
                // 禁用id查询框
                $filter->disableIdFilter();
                $filter->equal('seat_no','坐席号');
                $filter->equal('source_id','来源标识');
            });
        });
    }

    //获取通话详情
    public function getDetail(Request $request)
    {
        $id = $request->input('id');
        $path = '/upload/callout/';
        $where = [
            'id' => $id
        ];
        $data = $this->repository->getList($where);
        $data = $data['list'][0];
        if (empty($data['record_file'])) {
            return $this->jsonError(
                1000, '暂无', ['desc'=>$data['desc']]
            );
        } else {
            return $this->jsonSuccess(
                ['src'=>$path.$data['record_file'],'desc'=>$data['desc']]
            );
        }
    }
}