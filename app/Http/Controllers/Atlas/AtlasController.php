<?php
/**
 * Created by PhpStorm.
 * User: tongbing
 * Date: 17/8/18
 * Time: 14:17
 */

namespace App\Http\Controllers\Atlas;

use App\Http\Controllers\Controller;
use App\Http\Requests\CreateUserPost;
use App\Http\Requests\IndexPost;
use App\Models\AtlasUser;
use App\Repositories\AtlasAdminRepository;
use App\Repositories\AtlasReleationRepository;
use App\Repositories\AtlasUserRepository;
use App\Repositories\TreeMapRepository;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;

class AtlasController extends Controller
{
    protected $user;

    private $session_key = 'atlas';

    private $path = './upload/';

    public function __construct()
    {
        $this->middleware(function ($request, $next) {
            $this->user = json_decode(session($this->session_key), true);
            return $next($request);
        });
        $this->repository = new AtlasAdminRepository();
    }

    //登录页面
    public function login()
    {
        Session::put($this->session_key, '');
        return view('atlas.login', ['title' => '登录']);
    }

    //处理登录
    public function doLogin(IndexPost $request)
    {
        $input = $request->input();
        if (captcha_check($input['captcha']) === false) {
            return back()->withErrors('验证码错误');
        }

        //判断密码
        $user = $this->repository->getOne('*', ['username' => $input['account']]);

        if ($user === false) {
            return back()->withErrors('请确认账号是否存在？');
        } else {
            if ($user['password'] != md5(md5($input['password']) . 'atlas')) {
                return back()->withErrors('密码错误');
            }
        }

        //登录成功
        if ($user) {
            unset($user['password']);
            Session::put($this->session_key, json_encode($user));
        } else {
            return back()->withErrors('请确认账号是否存在？');
        }
        return redirect('/atlas/account');
    }

    //图形验证码
    public function imgCode()
    {
        //简单防刷
        $pathInfo = pathinfo(env('APP_URL'));
        if ($_SERVER['HTTP_HOST'] != $pathInfo['basename']) {
            return $this->jsonError(9999, '禁止使用图形验证码');
        }
        return $this->jsonSuccess(array('code' => captcha_src()));
    }

    /**
     * 上传文件
     * @param Request $request
     * @return array
     */
    public function uploadFile(Request $request)
    {
        //参数校验
        $orderId = $request->input('order_id');
        $file = $request->file('file');

        if (count($file) == 0) {
            return array('error' => '没有图片，请检查');
        }

        if (empty($orderId)) {
            return array('error' => '没有找到对应订单');
        }

        $orderExt = new OrderExtRepository();
        $orderExists = $orderExt->getOne('*', ['oid' => $orderId]);

        $path = 'certificate/' . $orderId . '/';
        //判断图片是不是已经上传5张了
        if (is_dir($this->path . $path)) {
            $dir = scandir($this->path . $path);
            if (count($dir) - 2 >= 5) {
                return array('error' => '最多只能上传5张图片');
            }
        }

        //循环上传图片
        foreach ($file as $value) {
            // 文件是否上传成功
            if ($value->isValid()) {
                // 获取文件相关信息
                $ext = $value->getClientOriginalExtension();     // 扩展名
                $realPath = $value->getRealPath();   //临时文件的绝对路径
                // 上传文件 指定上二级目录
                $filename = $path . time() . '-' . rand(1000, 9999) . '.' . $ext;
                // 使用我们新建的uploads本地存储空间（目录）
                $bool = Storage::disk('uploads')->put($filename, file_get_contents($realPath));
                if ($bool) {
                    //更新数据库
                    if (empty($orderExists)) {
                        $res = $orderExt->create(['oid' => $orderId, 'c_time' => time(), 'img_path' => json_encode([$filename])]);
                    } else {
                        $imgPath = json_decode($orderExists['img_path'], true);
                        $imgPath[] = $filename;
                        $res = $orderExt->update(['oid' => $orderId], ['img_path' => json_encode($imgPath)]);
                    }
                    //获取预览图
                    $return = $this->getPreview($orderId, true);
                    if ($res) {
                        return $return;
                    } else {
                        return array('error' => '上传失败');
                    }
                } else {
                    return array('error' => '上传失败');
                }
            }
        }
    }

    //统一组装bootstrap fileinput预览图方法
    public function getPreview($oid, $single = false)
    {
        //取出对应的订单拓展信息
        $orderExt = new OrderExtRepository();
        $data = $orderExt->getOne('*', ['oid' => $oid]);
        if (empty($data)) {
            return false;
        }

        $imgPath = $data['img_path'];
        if (empty($imgPath)) {
            return false;
        }

        $imgPath = json_decode($imgPath, true);
        $initialPreview = [];
        $initialPreviewConfig = [];
        foreach ($imgPath as $value) {
            $file = $this->path . $value;
            $handle = fopen($file, "r");
            //获取文件的统计信息
            $fstat = fstat($handle);
            $initialPreview[] = "<img width='100%' src='" . ltrim($file, '.') . "'/>";
            $initialPreviewConfig[] = (object)[
                'caption' => basename($file),
                'size' => $fstat['size'],
                'url' => '/atlas/deleteImg',
                'key' => $oid . '&&' . $value
            ];
        }

        if ($single) {
            $initialPreview = [end($initialPreview)];
            $initialPreviewConfig = [end($initialPreviewConfig)];
        }

        return [
            'initialPreview' => $initialPreview,
            'initialPreviewConfig' => $initialPreviewConfig
        ];
    }

    //删除图片
    public function deleteImg(Request $request)
    {
        $post = $request->input();
        $post = explode('&&', $post['key']);

        //取出对应的订单拓展信息
        $orderExt = new OrderExtRepository();
        $data = $orderExt->getOne('*', ['oid' => $post[0]]);
        if (empty($data) || empty($data['img_path'])) {
            return array('error' => '请确认该订单下是否存在图片？');
        }

        $imgPath = json_decode($data['img_path'], true);
        $key = array_search($post[1], $imgPath);
        unset($imgPath[$key]);
        $imgPath = array_values($imgPath);
        $res = $orderExt->update(['oid' => $post[0]], ['img_path' => json_encode($imgPath)]);
        if ($res) {
            unlink($this->path . $post[1]);
            return array('success' => '删除成功');
        }
    }

    //账户信息
    public function account()
    {
        return view('atlas.account', ['title' => '我的信息', 'user' => $this->user]);
    }

    //我的家谱
    public function myTreeMap(Request $request)
    {
        $id = $request->input('id');
        return view('atlas.myTreeMap', ['title' => '我的家谱', 'user' => $this->user,  'id' => $id]);
    }

    /**
     * 家谱列表
     * @param Request $request
     */
    public function treeMapList(Request $request)
    {
        $search = [];
        $search['name'] = $request->input('name');
        $query = AtlasUser::query();

        if (isset($search['name'])) {
            $query->where('name', '=', $search['name']);
        }

        //添加规则，只查自己的并且创建时间大于账号创建时间
        $query->where('admin_id', '=', $this->user['id']);

        $lists = $query->orderBy('created_at','desc')->paginate(20);
        return view('atlas.treeMapList', ['user'=>$this->user, 'title' => '家谱成员','lists'=>$lists,'filter'=>$search]);
    }

    /**
     * 查询关联用户
     * @param Request $request
     * @return array
     */
    public function treemap(Request $request)
    {
        $data = DB::select('SELECT id,pid FROM atlas_releation WHERE FIND_IN_SET(id, treemap(?));', (array)$request->input('id'));

        $treemapRepository = new TreeMapRepository();
        $atlasUser = new AtlasUserRepository();
        $atlasUserData = $atlasUser->getOne(['name'],['id' => $request->input('id')]);
        //组装对应的结构，用于zui显示成树形结构
        $returnData['data']['text'] = $atlasUserData['name'];

        if (!empty($data)) {
            $data = json_decode(json_encode($data), true);
            $treemapRepository->load($data);
            $data = $treemapRepository->DeepTree($request->input('id'));
            $returnData['data']['children'] = $data;
            return $this->jsonSuccess($returnData);
        } else {
            return $this->jsonError(1000, '没有');
        }
    }

    public function addUser(Request $request)
    {
        if (!empty($request->input('id'))) {
            $pid = $request->input('id');
        } else {
            $pid = 0;
        }
        return view('atlas.addUser', ['user' => $this->user, 'title' => '添加成员', 'pid' => $pid]);
    }

    /**
     * 执行添加用户
     */
    public function doAddUser(CreateUserPost $request)
    {
        //参数验证
        $this->validate($request,
            [
                'name' => 'required',
                'mobile' => [
                    'regex:/^(1(3|4|5|7|8)[0-9])\d{8}$/'
                ],
                'sex' => 'required',
//                'birthday' => 'required',
            ],
            [
                'name.required' => '姓名不能为空',
                'mobile.regex' => '手机号格式不正确',
                'sex.required' => '性别不能为空',
//                'birthday.required' => '出生年月不能为空',
            ]
        );
        $atlasUser = new AtlasUserRepository();
        $atlasReleation = new AtlasReleationRepository();

        //添加到用户表中
        $post = $request->input();
        $pid = $post['pid'] ?: 0;
        unset($post['pid']);
        $post['admin_id'] = $this->user['id'];
        $id = $atlasUser->create($post);

        //添加到关联表中
        $atlasReleationData = [
            'id' => $id,
            'pid' => $pid
        ];
        $atlasReleation->create($atlasReleationData);
        return redirect('/atlas/treeMapList');
    }

    /**
     * 删除用户
     * @param Request $request
     * @return bool|null
     */
    public function delUser(Request $request)
    {
        $id = $request->input('id');
        $atlasUser = new AtlasUserRepository();
        return $atlasUser->delete($id);
    }

    /**
     * 获取用户信息
     */
    public function editUser(Request $request)
    {
        $atlasUser = new AtlasUserRepository();
        $id = $request->input('id');
        $atlasUserData = $atlasUser->getOne('*', ['id' => $id]);
        return view('atlas.editUser', ['user'=>$this->user, 'title' => '成员编辑','lists'=>$atlasUserData]);
    }

    /**
     * 编辑用户
     * @param Request $request
     */
    public function doEditUser(Request $request)
    {
        $this->validate($request,
            [
                'id' => 'required',
                'name' => 'required',
                'mobile' => [
                    'regex:/^(1(3|4|5|7|8)[0-9])\d{8}$/'
                ],
                'sex' => 'required',
//                'birthday' => 'required',
            ],
            [
                'id.required' => 'id不能为空',
                'name.required' => '姓名不能为空',
                'mobile.regex' => '手机号格式不正确',
                'sex.required' => '性别不能为空',
//                'birthday.required' => '出生年月不能为空',
            ]
        );
        $postData = $request->input();
        $atlasUser = new AtlasUserRepository();
        $res = $atlasUser->update(['id' => $postData['id']], $postData);
        if ($res) {
            return redirect('/atlas/treeMapList');
        } else {
            return back()->withErrors('更新失败');
        }
    }

    /**
     * 重置密码
     * @param Request $request
     */
    public function resetPass(Request $request)
    {

    }
}
