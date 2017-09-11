<?php

namespace App\Http\Controllers\Api;


use App\Http\Controllers\Controller;
use App\Models\Record;
use App\Repositories\OutBoundRepository;
use Symfony\Component\HttpFoundation\Request;

class CallOutController extends Controller
{
    private $suffix = 'credit_manager_';

    public function __construct(OutBoundRepository $outBoundRepository)
    {
        $this->repository = $outBoundRepository;
    }

    /**
     * 外呼接口
     * @param Request $request
     * @return array
     */
    public function callOut(Request $request)
    {
        $input = $request->input();
        $res = $this->repository->callOut($input);
        if ($res === false) {
            return $this->jsonError(1000, $this->repository->getError());
        } else {
            return $this->jsonSuccess(array('id'=>$res), '呼出成功');
        }
    }
}