<?php
/**
 * Created by PhpStorm.
 * User: lester
 * Date: 2018/5/31 20:20
 * Desc:
 */

namespace Server;

use Server\Model\Group;
use Server\Model\GroupMessage;
use Server\Model\MemoryTable;
use Server\Model\PrivateMessage;
use Swoole\WebSocket\Server;
use Server\Model\User;

class WebSocketServer
{
    public $server;

    public $user;

    public $privateMessage;

    public $groupMessage;

    public $group;

    public $MT;


    public function __construct()
    {
        $this->server = new Server('0.0.0.0', 9501);

        $this->server->set([
            'daemonize' => false,
        ]);

        $this->server->on('handshake', array($this, 'onHandshake'));
//        $this->server->on('open', array($this, 'onOpen'));
        $this->server->on('message', array($this, 'onMessage'));
        $this->server->on('close', array($this, 'onClose'));

        $this->user = new User();
        $this->privateMessage = new PrivateMessage();
        $this->groupMessage = new GroupMessage();
        $this->group = new Group();

        $this->MT = new MemoryTable();
        //The key: uid.
        $this->MT->createTable('user', 1024,
            [
                ['uid', 6, 1],
                ['fd', 6, 1],
                ['avatar', 120, 3],
                ['username', 20, 3],
                ['sign', 200, 3],
                ['is_online', 1, 1]
            ]);
        // The key: fd.
        $this->MT->createTable('lastSendTime', 1024,
            [
                ['fd', 6, 1],
                ['uid', 6, 1],
                ['last_send_time', 4, 1]
            ]);
        // The key: uid.
        $this->MT->createTable('friends', 1024,
            [
                ['uid', 6, 1],
                ['friends', 1024, 3]
            ]);
        // The key:group_id
        $this->MT->createTable('groupMembers', 1024,
            [
                ['gid', 5, 1],
                ['members', 1024, 3]
            ]);

        //初始化，把表中所有用户的fd置为0，防止因为上一次启动造成fd混乱
        $this->user->initFd();

        $this->server->start();
    }

    /**
     * @param \swoole_http_request $request
     * @param \swoole_http_response $response
     * @return bool
     */
    public function onHandshake(\swoole_http_request $request, \swoole_http_response $response)
    {
        // print_r( $request->header );
        // if (如果不满足我某些自定义的需求条件，那么返回end输出，返回false，握手失败) {
        //    $response->end();
        //     return false;
        // }

        /**
         * 获取 $uid 和 $_token 并验证，失败则拒绝握手 返回 false
         * 并验证参数有效性.
         */
        $uid = '';
        $_token = '';
        !empty($request->cookie['uid']) && $uid = $request->cookie['uid'];
        !empty($request->get['uid']) && $uid = $request->get['uid'];

        !empty($request->cookie['_token']) && $_token = $request->cookie['_token'];
        !empty($request->get['_token']) && $_token = $request->get['_token'];

        if (empty($uid) || empty($_token)) {
            $result = [
                'code' => 403,
                'message' => 'You should to login!',
                'next_url' => '/login'
            ];
            $response->end(json_encode($result));
            return false;
        }

        /**
         * 根据 $uid 和 $_token 验证用户身份
         */
        $obj = $this->user->checkToken($uid, $_token);
        if (!$obj) {
            $result = [
                'code' => 403,
                'message' => 'You should to login!',
                'next_url' => '/login'
            ];
            $response->end(json_encode($result));
            return false;
        }

        // websocket握手连接算法验证
        $secWebSocketKey = $request->header['sec-websocket-key'];
        $patten = '#^[+/0-9A-Za-z]{21}[AQgw]==$#';
        if (0 === preg_match($patten, $secWebSocketKey) || 16 !== strlen(base64_decode($secWebSocketKey))) {
            $response->end();
            return false;
        }
        echo $request->header['sec-websocket-key'];
        $key = base64_encode(sha1(
            $request->header['sec-websocket-key'] . '258EAFA5-E914-47DA-95CA-C5AB0DC85B11',
            true
        ));

        $headers = [
            'Upgrade' => 'websocket',
            'Connection' => 'Upgrade',
            'Sec-WebSocket-Accept' => $key,
            'Sec-WebSocket-Version' => '13',
        ];

        // WebSocket connection to 'ws://127.0.0.1:9502/'
        // failed: Error during WebSocket handshake:
        // Response must not include 'Sec-WebSocket-Protocol' header if not present in request: websocket
        if (isset($request->header['sec-websocket-protocol'])) {
            $headers['Sec-WebSocket-Protocol'] = $request->header['sec-websocket-protocol'];
        }

        foreach ($headers as $key => $val) {
            $response->header($key, $val);
        }

        // 绑定用户的fd,并将信息同步到内存表中
        $this->MT::table('user')->set($uid,
            ['uid' => $uid, 'fd' => $response->fd, 'username' => $obj->nickname,
                'avatar' => $obj->profile_photo, 'is_online' => $obj->online_status, 'sign' => $obj->sign
            ]
        );

        $this->MT::table('lastSendTime')->set($response->fd,
            ['fd' => $response->fd, 'uid' => $uid, 'last_send_time' => time()]
        );
        $friendIDArr = $this->user->getFriendUidArr($uid);
        $this->MT::table('friends')->set($uid, ['uid' => $uid, 'friends' => implode(',', $friendIDArr)]);

        // 设置当前用户所在的群成员
//        $this->user->updateFd($uid, $response->fd);
        $groupAndMembersList = $this->group->listGroupAndMembers($uid);
        foreach ($groupAndMembersList as $key => $value) {
            $this->MT::table('groupMembers')->set($key, ['gid' => $key, 'members' => implode(',', $value)]);
        }

        $response->status(101);
        $response->end();
        echo "connected!" . PHP_EOL;
        return true;
    }

    /**
     * @param Server $server
     * @param $request
     */
    public function onOpen(Server $server, $request)
    {
        echo "server:handshake success with fd{$request->fd}\n";
        $server->push($request->fd, $request->fd . ",Welcome!");
//        var_dump(json_encode($request));
    }

    /**
     * @param Server $server
     * @param $frame
     */
    public function onMessage(Server $server, $frame)
    {
        echo "receive from {$frame->fd}:\n{$frame->data} \nopcode:{$frame->opcode},fin:{$frame->finish}\n";

        // 对请求的参数解析，验证。
        if (empty($frame->data)) return;
        $data = json_decode($frame->data, true);
        if (json_last_error() != 0 || empty($data)) return;

        // 获取用户的相关信息 ($userData).
        $from_fd = $frame->fd;
        $lastSendTimeData = $this->MT::table('lastSendTime')->get($from_fd);
        $userData = $this->MT::table('user')->get($lastSendTimeData['uid']);
        $uid = $lastSendTimeData['uid'];

        // 记录上次发送消息的时候
        $time_time = time();
        $this->MT::table('lastSendTime')->set($from_fd, ['fd' => $from_fd, 'uid' => $userData['uid'], 'last_send_time' => (string)$time_time]);

        switch ($data['type']) {
            case 'chat':
                /**
                 * 根据 fd 获取的发送者的 uid ，并验证其身份。
                 * 如果，身份不相符(uid !== $data['data']['mine']['id']),则拒绝该请求。
                 */
                if (empty($userData) || $userData['uid'] != $data['data']['mine']['id']) {
                    $result = [
                        'type' => 'sendError',
                        'message' => '发送失败，当前用户非法'
                    ];
                    $server->push($from_fd, json_encode($result));
                    return;
                }
                $uid = $userData['uid'];

                $fromData = $data['data']['mine'];
                $toData = $data['data']['to'];
                $to_id = $toData['id'];

                $sendData = [
                    'id' => $uid,
                    'fromid' => $uid,
                    'username' => $fromData['username'],
                    'avatar' => $fromData['avatar'],
                    'type' => $toData['type'],
                    'content' => $fromData['content'],
                    'timestamp' => time() * 1000,
                    'mine' => false
                ];
                //私聊消息
                if ($toData['type'] == 'friend') {
                    $toMTData = $this->MT::table('user')->get($to_id);
                    if (empty($toMTData)) {
                        $to_obj = $this->user->getFd($to_id);
                        if (empty($to_obj) || $to_obj->is_deleted == 1) {
                            $result = [
                                'type' => 'sendError',
                                'message' => '消息接受者不存在'
                            ];
                            $server->push($from_fd, json_encode($result));
                            return;
                        }
                        $to_fd = $to_obj->fd;
                    } else {
                        $to_fd = $toMTData['fd'];
                    }
                    if ($this->checkClientIsActive($to_fd)) {   // online message
                        $message_id = $this->privateMessage->add($uid, $to_id, $fromData['content'], 0);
                        $sendData['cid'] = $message_id;
                        $server->push($to_fd, json_encode(['type' => 'chat', 'data' => $sendData]));
                    } else {    // offline message
                        $this->privateMessage->add($uid, $to_id, $fromData['content'], 1, 1);
                    }
                } else {
                    // 1.添加聊天记录
                    $message_id = $this->groupMessage->add($uid, $to_id, $fromData['content']);
                    // 2.获取组员ID.
                    $memberTMList = $this->MT::table('groupMembers')->get($to_id);
                    $memberIDArr = explode(',', $memberTMList['members']);
//                    $member_list = $this->group->listMembers($to_id);
                    $sendData['cid'] = $message_id;
                    $sendData['id'] = $toData['id'];
                    foreach ($memberIDArr as $memberUid) {
                        if ($memberUid == $uid || empty($memberUid)) {
                            continue;
                        }
                        $toMTData = $this->MT::table('user')->get($memberUid);
                        if ($toMTData && $this->checkClientIsActive($toMTData['fd'])) {
                            $this->groupMessage->addLink($message_id, $memberUid, $to_id, 0, 1);
                            $server->push($toMTData['fd'], json_encode(['type' => 'chat', 'data' => $sendData]));
                        } else {
                            $this->groupMessage->addLink($message_id, $memberUid, $to_id, 1, 0);
                        }
                    }
                }
                break;
            case 'sign':
                isset($userData['uid']) && $this->user->updateSign($userData['uid'], $data['data']['content']);
                break;
            case 'onlineStatus':
                //如果修改之后的状态和之前相同，则不做任何操作。
                $statusInt = onlineStringToInt($data['data']['status']);
                if ($userData['is_online'] == $statusInt) {
                    return;
                }
                $this->MT::table('user')->set($userData['uid'], ['is_online' => $statusInt]);
                $this->user->update($userData['uid'], $statusInt);
                // 取所有的好友，遍历。
                $friendsList = $this->MT::table('friends')->get($userData['uid']);
                $friendIDArr = explode(',', $friendsList['friends']);
                foreach ($friendIDArr as $value) {
                    $friendData = $this->MT::table('user')->get($value);
                    // 能在内存表里面获取到，并且状态不是离线，即发送
                    // 反之，则不发送。
                    if ($friendData && $friendData['is_online'] != 0) {
                        $statusString = $data['data']['status'] == 'online' ? 'online' : 'offline';
                        $sendData = [
                            'type' => 'onlineStatus',
                            'data' => ['uid' => $userData['uid'], 'status' => $statusString]
                        ];
                        $server->push($friendData['fd'], json_encode($sendData));
                    }
                }

                break;

            // 离线消息
            case 'offlineMessage':
                $obj_list = $this->privateMessage->listOfflineMessage($uid);
                $messageList = [];
                foreach ($obj_list as $key => $value) {
                    $messageList[] = [
                        'id' => $value->from_uid,
                        'fromid' => $value->from_uid,
                        'username' => $value->nickname,
                        'avatar' => $value->profile_photo,
                        'type' => 'friend',
                        'content' => $value->content,
                        'timestamp' => $value->create_time * 1000,
                        'mine' => false
                    ];
                }
                if (!empty($messageList)) {
                    $server->push($from_fd, json_encode(['type' => 'offlineMessage', 'data' => $messageList]));
                }
                break;


            default:
                break;

        }
//        $server->push($frame->fd, "this is server");
    }

    /**
     * Check the client active.
     *
     * @param int $fd
     * @return bool
     */
    public function checkClientIsActive($fd)
    {
        if (empty($fd)) {
            return false;
        }
        if (!$this->server->exist($fd)) {
            return false;
        }
        return true;
    }

    /**
     * @param Server $server
     * @param $fd
     */
    public function onClose(Server $server, $fd)
    {
        echo "client {$fd} closed\n";
    }

}