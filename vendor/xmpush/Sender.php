<?php
/**
 * MiPush消息发送类.
 * @author wangkuiwei
 * @name Sender
 * @desc MiPush消息发送
 *
 */
namespace xmpush;

class Sender extends HttpBase {

    public function __construct() {
        parent::__construct();
    }

    //指定regId单发消息
    public function send(Message $message, $regId, $retries = 1) {
        $fields = $message->getFields();
        $fields['registration_id'] = $regId;
        return $this->postResult(PushRequestPath::V3_REGID_MESSAGE(), $fields, $retries);
    }

    //指定regId列表群发
    public function sendToIds(Message $message, $regIdList, $retries = 1) {
        $fields = $message->getFields();
        $jointRegIds = '';
        foreach ($regIdList as $regId) {
            if (isset($regId)) {
                $jointRegIds .= $regId . Constants::$comma;
            }
        }
        $fields['registration_id'] = $jointRegIds;
        return $this->postResult(PushRequestPath::V3_REGID_MESSAGE(), $fields, $retries);
    }

    //多条发送
    public function multiSend($targetMessages, $type, $retries = 1) {
        $requestPath = $this->multiSendRequestPath($type);
        $data = array();
        foreach ($targetMessages as $targetMsg) {
            array_push($data, $targetMsg->getFields());
        }
        $fields = array('messages' => json_encode($data));
        return $this->postResult($requestPath, $fields, $retries);
    }

    //多条发送
    public function multiSendAtTime($targetMessages, $type, $timeToSend, $retries = 1) {
        $requestPath = $this->multiSendRequestPath($type);
        $data = array();
        foreach ($targetMessages as $targetMsg) {
            array_push($data, $targetMsg->getFields());
        }
        $fields = array('messages' => json_encode($data), 'time_to_send' => $timeToSend);
        return $this->postResult($requestPath, $fields, $retries);
    }

    //指定别名单发
    public function sendToAlias(Message $message, $alias, $retries = 1) {
        $fields = $message->getFields();
        $fields['alias'] = $alias;
        return $this->postResult(PushRequestPath::V3_ALIAS_MESSAGE(), $fields, $retries);
    }

    //指定别名列表群发
    public function sendToAliases(Message $message, $aliasList, $retries = 1) {
        $fields = $message->getFields();
        $jointAliases = '';
        foreach ($aliasList as $alias) {
            if (strlen($jointAliases) > 0) {
                $jointAliases = $jointAliases . Constants::$comma;
            }
            $jointAliases = $jointAliases . $alias;
        }
        $fields['alias'] = $jointAliases;
        return $this->postResult(PushRequestPath::V3_ALIAS_MESSAGE(), $fields, $retries);
    }

    //指定userAccount群发
    public function sendToUserAccount(Message $message, $userAccount, $retries = 1) {
        $fields = $message->getFields();
        $fields['user_account'] = $userAccount;
        return $this->postResult(PushRequestPath::V2_USER_ACCOUNT_MESSAGE(), $fields, $retries);
    }

    //指定userAccount列表群发
    public function sendToUserAccounts(Message $message, $userAccountList, $retries = 1) {
        $fields = $message->getFields();
        $jointUserAccounts = '';
        foreach ($userAccountList as $userAccount) {
            if (strlen($jointUserAccounts) > 0) {
                $jointUserAccounts = $jointUserAccounts . Constants::$comma;
            }
            $jointUserAccounts = $jointUserAccounts . $userAccount;
        }
        $fields['user_account'] = $jointUserAccounts;
        return $this->postResult(PushRequestPath::V2_USER_ACCOUNT_MESSAGE(), $fields, $retries);
    }

    //指定topic群发
    public function broadcast(Message $message, $topic, $retries = 1) {
        $fields = $message->getFields();
        $fields['topic'] = $topic;
        return $this->postResult(PushRequestPath::V3_BROADCAST(), $fields, $retries);
    }

    //向所有设备发送消息
    public function broadcastAll(Message $message, $retries = 1) {
        $fields = $message->getFields();
        return $this->postResult(PushRequestPath::V3_BROADCAST_TO_ALL(), $fields, $retries);
    }

    //广播消息，多个topic，支持topic间的交集、并集或差集
    public function multiTopicBroadcast(Message $message, $topicList, $topicOp, $retries = 1) {
        if (count($topicList) == 1) {
            return $this->broadcast($message, $topicList[0], $retries);
        }
        $fields = $message->getFields();
        $jointTopics = '';
        foreach ($topicList as $topic) {
            if (strlen($jointTopics) > 0) {
                $jointTopics = $jointTopics . Constants::$multi_topic_split;
            }
            $jointTopics = $jointTopics . $topic;
        }
        $fields['topics'] = $jointTopics;
        $fields['topic_op'] = $topicOp;
        return $this->postResult(PushRequestPath::V3_MILTI_TOPIC_BROADCAST(), $fields, $retries);
    }

    // 检测定时任务是否存在
    public function checkScheduleJobExist($msgId, $retries = 1) {
        $fields = array('job_id' => $msgId);
        return $this->postResult(PushRequestPath::V2_CHECK_SCHEDULE_JOB_EXIST(), $fields, $retries);
    }

    // 删除定时任务
    public function deleteScheduleJob($msgId, $retries = 1) {
        $fields = array('job_id' => $msgId);
        return $this->postResult(PushRequestPath::V2_DELETE_SCHEDULE_JOB(), $fields, $retries);
    }

    /**
     * @param $type
     * @return PushRequestPath
     */
    private function multiSendRequestPath($type) {
        if ($type == TargetedMessage::TARGET_TYPE_ALIAS) {
            $requestPath = PushRequestPath::V2_SEND_MULTI_MESSAGE_WITH_ALIAS();
            return $requestPath;
        } else if ($type == TargetedMessage::TARGET_TYPE_USER_ACCOUNT) {
            $requestPath = PushRequestPath::V2_SEND_MULTI_MESSAGE_WITH_ACCOUNT();
            return $requestPath;
        } else {
            $requestPath = PushRequestPath::V2_SEND_MULTI_MESSAGE_WITH_REGID();
            return $requestPath;
        }
    }

    public function callToPush($userAccount,$title,$content,$extras,$push_type)
    {
        $sender = new Sender();
        // message1 演示自定义的点击行为
        $message = new Builder();
        $message->title($title);  // 通知栏的title
        $message->description($content); // 通知栏的description
        $message->passThrough(0);  // 这是一条通知栏消息，如果需要透传，把这个参数设置成1,同时去掉title和descption两个参数
        $message->payload($extras); // 携带的数据，点击后将会通过客户端的receiver中的onReceiveMessage方法传入。
        $message->extra(Builder::notifyForeground, 1); // 应用在前台是否展示通知，如果不希望应用在前台时候弹出通知，则设置这个参数为0
//        $message->extra(Builder::notifyEffect,2);
//        $message->extra(Builder::intentUri,'zyb56push://car.com#Intent;launchFlags=0x10000000;end');
        if ($extras)
        {
            foreach ($extras as $key => $value)
            {
                $message->extra($key,$value);
            }
        }
        $message->notifyId(5); // 通知类型。最多支持0-4 5个取值范围，同样的类型的通知会互相覆盖，不同类型可以在通知栏并存
        $message->build();
        if ($push_type == 1)
        {
            if (!is_array($userAccount))
            {
                $userAccount = array($userAccount);
            }
            $result = $sender->sendToUserAccounts($message,$userAccount)->getRaw();
        }else{
            $result = $sender->broadcastAll($message)->getRaw();
        }
        return $result;
    }
}


?>
