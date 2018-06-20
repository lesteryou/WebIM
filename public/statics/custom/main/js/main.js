/**
 * The WebSocket instance.
 */
var ws;

/**
 * Last sending time.
 *
 * @type {number}
 */
var last_send_time = 0;

/**
 * Heartbeat & reconnect.
 *
 * @type {{timeout: number, timeoutObj: null, serverTimeoutObj: null, reset: heartCheck.reset, start: heartCheck.start}}
 */
var heartCheck = {
    timeout: 60000,//60ms
    timeoutObj: null,
    serverTimeoutObj: null,
    reset: function () {
        clearTimeout(this.timeoutObj);
        clearTimeout(this.serverTimeoutObj);
        this.start();
    },
    start: function () {
        var self = this;
        this.timeoutObj = setTimeout(function () {
            var hear_beat = {type: "heartbeat", time: new Date().getTime()};
            send(hear_beat);
            self.serverTimeoutObj = setTimeout(function () {
                ws.close();//如果onclose会执行reconnect，我们执行ws.close()就行了.如果直接执行reconnect 会触发onclose导致重连两次
            }, self.timeout)
        }, this.timeout)
    }
};

/**
 * LayIM instance.
 * @var object
 */
var layim;

/**
 * LayUI
 */
layui.config({
    base: '/statics/common/contextMenu/' //扩展 JS 所在目录
}).use('ext');
layui.use(['layim', 'jquery','contextMenu'], function () {
    layim = layui.layim;
    $ = jquery = layui.jquery;

    //Base config
    layim.config({
        //Initialization interface.
        init: {
            url: url_init
            , type: 'get'
            , data: {}
        }
        //The group members.
        , members: {
            url: url_list_group_members
            , data: {}
        }

        , uploadImage: {
            url: url_upload_image
            , type: 'post'
        }
        , uploadFile: {
            url: url_upload_file
            , type: 'post'
        }

        , isAudio: true
        , isVideo: true

        //扩展工具栏
        , tool: [{
            alias: 'code'
            , title: '代码'
            , icon: '&#xe64e;'
        }]

        , title: 'WebIM'
        , right: '10px'
        //,minRight: '90px' //聊天面板最小化时相对浏览器右侧距离
        , initSkin: '3.jpg' //1-5 设置初始背景
        //,skin: ['aaa.jpg'] //新增皮肤
        , notice: true //是否开启桌面消息提醒，默认false

        , msgbox: '/layim/demo/msgbox.html' //消息盒子页面地址，若不开启，剔除该项即可
        , find: '/front/main/find' //发现页面地址，若不开启，剔除该项即可
        , chatLog: '/front/main/chatLog' //聊天记录页面地址，若不开启，剔除该项即可

    });

    //Listen for ready.
    layim.on('ready', function (res) {
        ws = new WebSocket('ws://localhost:9501?uid=' + uid + '&_token=' + _token);

        layui.ext.init();

        ws.onopen = function (ev) {
            heartCheck.start();
            send({type: 'offlineMessage'});
        };

        ws.onmessage = function (res) {
            heartCheck.reset();
            // console.log(res);
            var messageData = JSON.parse(res.data);
            if (messageData.type === 'chat') {
                layim.getMessage(messageData.data);
            } else if (messageData.type === 'onlineStatus') {
                layim.setFriendStatus(messageData.data.uid, messageData.data.status);
            } else if (messageData.type === 'offlineMessage') {
                for (var j = 0, len = messageData.data.length; j < len; j++) {
                    layim.getMessage(messageData.data[j]);
                }
            }
        };

        ws.onclose = function (ev) {
            reconnect();
        };

        ws.onerror = function (ev) {
            reconnect();
            console.log(ev);
        };

        //console.log(res.mine);
        // layim.msgbox(5); //模拟消息盒子有新消息，实际使用时，一般是动态获得
        layui.ext.init(); //更新右键点击事件
    });


    // Listen for online status.
    layim.on('online', function (status) {
        var send_data = {
            type: 'onlineStatus',
            data: {
                status: status
            }
        };
        send(send_data);
        layer.msg('在线状态已更改为 [ ' + ((status === 'online') ? '我在线上' : '隐身') + ' ] ');
    });

    // Listen for signature changes.
    layim.on('sign', function (value) {
        var send_data = {
            type: 'sign',
            data: {
                content: value
            }
        };
        send(send_data);
        layer.msg('签名 已更改为 [' + value + '] ');
    });

    // Listen for custom toolbars.
    // Take adding code
    layim.on('tool(code)', function (insert) {
        layer.prompt({
            title: '插入代码 - 工具栏扩展示例'
            , formType: 2
            , shade: 0
        }, function (text, index) {
            layer.close(index);
            insert('[pre class=layui-code]' + text + '[/pre]'); //将内容插入到编辑器
        });
    });


    // Listen for sending message.
    layim.on('sendMessage', function (data) {
        send({
            type: 'chat' //消息类型
            , data: data
        });
        // layim.setChatStatus('<span style="color:#FF5722;">对方正在输入。。。</span>');
    });

    // Listen for viewing group members.
    layim.on('members', function (data) {
        console.log(data);
    });

    //Listening for the chat window switch.
    layim.on('chatChange', function (res) {
        var type = res.data.type;
        console.log(res.data.id);
        var chat_text = $(".layim-chat-textarea textarea");
        var is_typing = 0;
        var time_handle;
        chat_text.focus(function () {
            // clearTimeout(time_handle);
            // is_typing++;
            // console.log('如果持续1.5秒，则表示正在打字');
            // //layim 切换后，直接令输入框获取到焦点
            // // 在这里，我们延迟1.5秒，
            // //      判断如果仍获取到焦点，则表明为真正的正在输入
            // // 获取到焦点之后在
            // time_handle = setTimeout(function () {
            //     if (chat_text.is(":focus") === true) {
            //         clearTimeout(time_handle);
            //         console.log('正在输入');
            //         // 正在输入
            //         ws.send(JSON.stringify({
            //             type: 'chatStatus',  //消息类型
            //             data: res.data,
            //             status: 1
            //         }));
            //         chat_text.blur(function () {
            //             //不输入了
            //             console.log('我不打字了');
            //             ws.send(JSON.stringify({
            //                 type: 'chatStatus',  //消息类型
            //                 data: res.data,
            //                 status: 0
            //             }));
            //         })
            //     }
            // }, 1500);

        });

        if (type === 'friend') {
            //模拟标注好友状态
            //layim.setChatStatus('<span style="color:#FF5722;">在线</span>');
        } else if (type === 'group') {
            //模拟系统消息
            // layim.getMessage({
            //     system: true
            //     , id: res.data.id
            //     , type: "group"
            //     , content: '模拟群员' + (Math.random() * 100 | 0) + '加入群聊'
            // });
        }
    });


    //面板外的操作
    var active = {
        chat: function () {
            //自定义会话
            layim.chat({
                name: '小闲'
                , type: 'friend'
                , avatar: '//tva3.sinaimg.cn/crop.0.0.180.180.180/7f5f6861jw1e8qgp5bmzyj2050050aa8.jpg'
                , id: 1008612
            });
            layer.msg('也就是说，此人可以不在好友面板里');
        }
        , message: function () {
            //制造好友消息
            layim.getMessage({
                username: "贤心"
                , avatar: "//tp1.sinaimg.cn/1571889140/180/40030060651/1"
                , id: "100001"
                , type: "friend"
                , content: "嗨，你好！欢迎体验LayIM。演示标记：" + new Date().getTime()
                , timestamp: new Date().getTime()
            });
        }
        , messageAudio: function () {
            //接受音频消息
            layim.getMessage({
                username: "林心如"
                , avatar: "//tp3.sinaimg.cn/1223762662/180/5741707953/0"
                , id: "76543"
                , type: "friend"
                , content: "audio[http://gddx.sc.chinaz.com/Files/DownLoad/sound1/201510/6473.mp3]"
                , timestamp: new Date().getTime()
            });
        }
        , messageVideo: function () {
            //接受视频消息
            layim.getMessage({
                username: "林心如"
                , avatar: "//tp3.sinaimg.cn/1223762662/180/5741707953/0"
                , id: "76543"
                , type: "friend"
                , content: "video[http://www.w3school.com.cn//i/movie.ogg]"
                , timestamp: new Date().getTime()
            });
        }
        , messageTemp: function () {
            //接受临时会话消息
            layim.getMessage({
                username: "小酱"
                , avatar: "//tva1.sinaimg.cn/crop.7.0.736.736.50/bd986d61jw8f5x8bqtp00j20ku0kgabx.jpg"
                , id: "198909151014"
                , type: "friend"
                , content: "临时：" + new Date().getTime()
            });
        }
        , add: function () {
            //实际使用时数据由动态获得
            layim.add({
                type: 'friend'
                , username: '麻花疼'
                , avatar: '//tva1.sinaimg.cn/crop.0.0.720.720.180/005JKVuPjw8ers4osyzhaj30k00k075e.jpg'
                , submit: function (group, remark, index) {
                    layer.msg('好友申请已发送，请等待对方确认', {
                        icon: 1
                        , shade: 0.5
                    }, function () {
                        layer.close(index);
                    });

                    //通知对方
                    /*
                    $.post('/im-applyFriend/', {
                      uid: info.uid
                      ,from_group: group
                      ,remark: remark
                    }, function(res){
                      if(res.status != 0){
                        return layer.msg(res.msg);
                      }
                      layer.msg('好友申请已发送，请等待对方确认', {
                        icon: 1
                        ,shade: 0.5
                      }, function(){
                        layer.close(index);
                      });
                    });
                    */
                }
            });
        }
        , addqun: function () {
            layim.add({
                type: 'group'
                , username: 'LayIM会员群'
                , avatar: '//tva2.sinaimg.cn/crop.0.0.180.180.50/6ddfa27bjw1e8qgp5bmzyj2050050aa8.jpg'
                , submit: function (group, remark, index) {
                    layer.msg('申请已发送，请等待管理员确认', {
                        icon: 1
                        , shade: 0.5
                    }, function () {
                        layer.close(index);
                    });

                    //通知对方
                    /*
                    $.post('/im-applyGroup/', {
                      uid: info.uid
                      ,from_group: group
                      ,remark: remark
                    }, function(res){

                    });
                    */
                }
            });
        }
        , addFriend: function () {
            var user = {
                type: 'friend'
                , id: 1234560
                , username: '李彦宏' //好友昵称，若申请加群，参数为：groupname
                , avatar: '//tva4.sinaimg.cn/crop.0.0.996.996.180/8b2b4e23jw8f14vkwwrmjj20ro0rpjsq.jpg' //头像
                , sign: '全球最大的中文搜索引擎'
            };
            layim.setFriendGroup({
                type: user.type
                , username: user.username
                , avatar: user.avatar
                , group: layim.cache().friend //获取好友列表数据
                , submit: function (group, index) {
                    //一般在此执行Ajax和WS，以通知对方已经同意申请
                    //……

                    //同意后，将好友追加到主面板
                    layim.addList({
                        type: user.type
                        , username: user.username
                        , avatar: user.avatar
                        , groupid: group //所在的分组id
                        , id: user.id //好友ID
                        , sign: user.sign //好友签名
                    });

                    layer.close(index);
                }
            });
        }
        , addGroup: function () {
            layer.msg('已成功把[Angular开发]添加到群组里', {
                icon: 1
            });
            //增加一个群组
            layim.addList({
                type: 'group'
                , avatar: "//tva3.sinaimg.cn/crop.64.106.361.361.50/7181dbb3jw8evfbtem8edj20ci0dpq3a.jpg"
                , groupname: 'Angular开发'
                , id: "12333333"
                , members: 0
            });
        }
        , removeFriend: function () {
            layer.msg('已成功删除[凤姐]', {
                icon: 1
            });
            //删除一个好友
            layim.removeList({
                id: 121286
                , type: 'friend'
            });
        }
        , removeGroup: function () {
            layer.msg('已成功删除[前端群]', {
                icon: 1
            });
            //删除一个群组
            layim.removeList({
                id: 101
                , type: 'group'
            });
        }
        //置灰离线好友
        , setGray: function () {
            layim.setFriendStatus(168168, 'offline');

            layer.msg('已成功将好友[马小云]置灰', {
                icon: 1
            });
        }
        //取消好友置灰
        , unGray: function () {
            layim.setFriendStatus(168168, 'online');

            layer.msg('成功取消好友[马小云]置灰状态', {
                icon: 1
            });
        }
        //移动端版本
        , mobile: function () {
            var device = layui.device();
            var mobileHome = '/layim/demo/mobile.html';
            if (device.android || device.ios) {
                return location.href = mobileHome;
            }
            var index = layer.open({
                type: 2
                , title: '移动版演示 （或手机扫右侧二维码预览）'
                , content: mobileHome
                , area: ['375px', '667px']
                , shadeClose: true
                , shade: 0.8
                , end: function () {
                    layer.close(index + 2);
                }
            });
            layer.photos({
                photos: {
                    "data": [{
                        "src": "http://cdn.layui.com/upload/2016_12/168_1481056358469_50288.png",
                    }]
                }
                , anim: 0
                , shade: false
                , success: function (layero) {
                    layero.css('margin-left', '350px');
                }
            });
        }
    };
    // $('.site-demo-layim').on('click', function () {
    //     var type = $(this).data('type');
    //     active[type] ? active[type].call(this) : '';
    // });
});


function reconnect() {
    ws = new WebSocket('ws://localhost:9501?uid=' + uid + '&_token=' + _token);
}

/**
 *
 * @param data
 * @param is_obj
 */
function send(data, is_obj) {
    is_obj = arguments[1] || true;

    var this_time = new Date().getTime();
    if (this_time - last_send_time < heartbeat_time && data.type === 'heartbeat') {
        return;
    }

    var timeout = 0;
    var readyState = parseInt(ws.readyState);
    if (readyState === 2 || readyState === 3) {
        reconnect();
        timeout = 1000;
    }
    setTimeout(function () {
        ws.send(is_obj ? JSON.stringify(data) : data);
    }, timeout);
}

function msg() {
    alert('124');
}