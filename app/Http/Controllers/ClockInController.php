<?php
/**
 * Created by PhpStorm.
 * User: PC-Qiu
 * Date: 2018/9/25
 * Time: 17:50
 */

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use xmpush\Builder;
use xmpush\Constants;
use xmpush\Sender;

class ClockInController extends Controller
{
    public function clockIn(Request $request)
    {
        $mobile = $request->input('useraccount');
        $secret = 'cd8qCNP7tCtZsAqLQrg+eg==';
        $package = 'com.xiaomi.zyb56mipushdemo';
        Constants::setSecret($secret);
        Constants::setPackage($package);
        $sender = new Sender();
        $title = '你好';
        $content = '我是渣渣锐';
        $extras = '{"test":1,"ok":"It\'s a string"}';
        $message = new Builder();
        $message->title($title);
        $message->description($content);
        $message->passThrough(0);
        $message->payload($extras);
        $message->extra(Builder::notifyForeground, 1);
        $message->notifyId(5);
        $message->build();
        $result = $sender->sendToUserAccount($message,$mobile)->getRaw();
        Log::info('推送手机: ' . $mobile . '推送结果: ' . json_encode($result));

    }
}