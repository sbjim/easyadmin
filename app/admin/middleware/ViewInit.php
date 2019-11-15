<?php

// +----------------------------------------------------------------------
// | EasyAdmin
// +----------------------------------------------------------------------
// | PHP交流群: 763822524
// +----------------------------------------------------------------------
// | 开源协议  https://mit-license.org 
// +----------------------------------------------------------------------
// | github开源项目：https://github.com/zhongshaofa/EasyAdmin
// +----------------------------------------------------------------------

namespace app\admin\middleware;


use app\common\service\AuthService;
use think\App;
use think\facade\Cache;
use think\facade\Env;
use think\facade\Request;
use think\facade\View;
use think\route\dispatch\Controller;

class ViewInit
{

    public function handle($request, \Closure $next)
    {
        list($thisModule, $thisController, $thisAction) = [app('http')->getName(), Request::controller(), $request->action()];
        list($thisControllerArr, $jsPath) = [explode('.', $thisController), null];
        foreach ($thisControllerArr as $vo) {
            empty($jsPath) ? $jsPath = parse_name($vo) : $jsPath .= '/' . parse_name($vo);
        }
        $autoloadJs           = file_exists("static/{$thisModule}/js/{$jsPath}.js") ? true : false;
        $thisControllerJsPath = "{$thisModule}/js/{$jsPath}.js";
        $adminModuleName      = Env::get('easyadmin.admin', 'admin');
        // 获取所有授权的节点
        $allAuthNode = Cache::get('allAuthNode_' . session('admin.id'));
        if (empty($allAuthNode)) {
            $allAuthNode = (new AuthService(session('admin.id')))->getAdminNode();
            Cache::tag('initAdmin')->set('allAuthNode_' . session('admin.id'), $allAuthNode);
        }
        $data                 = [
            'admin_module_name'    => $adminModuleName,
            'thisController'       => parse_name($thisController),
            'thisAction'           => parse_name($thisAction),
            'thisRequest'          => parse_name("{$thisModule}/{$thisController}/{$thisAction}"),
            'thisControllerJsPath' => "{$thisControllerJsPath}",
            'autoloadJs'           => $autoloadJs,
            'allAuthNode'          => $allAuthNode,
        ];
        View::assign($data);
        $request->adminModuleName = $adminModuleName;
        return $next($request);
    }


}