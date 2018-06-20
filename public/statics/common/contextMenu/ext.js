layui.define(['jquery', 'contextMenu'], function (exports) {
    var contextMenu = layui.contextMenu;
    var $ = layui.jquery;
    var ext = {
        init: function () {//定义右键操作
            // 好友右键
            $(".layim-list-friend >li > ul > li").contextMenu({
                width: 140, // width
                itemHeight: 30, // 菜单项height
                bgColor: "#fff", // 背景颜色
                color: "#333", // 字体颜色
                fontSize: 15, // 字体大小
                hoverBgColor: "#009bdd", // hover背景颜色
                hoverColor: "#fff", // hover背景颜色
                target: function (ele) { // 当前元素
                    console.log(ele);
                    $(".ul-context-menu").attr("data-id", ele[0].className.replace(/layim-list-gray/, '').trim())
                        .attr("data-name", ele.find("span").html())
                        .attr("data-img", ele.find("img").attr('src'));
                },
                menu: [
                    { // 菜单项
                        text: "发送消息",
                        icon: "",
                        callback: function (ele) {
                            var othis = ele.parent(),
                                friend_id = othis[0].dataset.id.replace(/^layim-friend/g, ''),
                                friend_name = othis[0].dataset.name,
                                friend_avatar = othis[0].dataset.img;
                            console.log('fid:' + friend_id + ',fname:' + friend_name + ',favatar:' + friend_avatar);
                            console.log(othis[0].dataset);
                            // msg();
                            // layim.chat({
                            //     name: friend_name
                            //     , type: 'friend'
                            //     , avatar: friend_avatar
                            //     , id: friend_id
                            // });
                        }
                    },
                    {
                        text: "查看资料",
                        icon: "",
                        callback: function (ele) {
                            var othis = ele.parent(), friend_id = othis[0].dataset.id.replace(/^layim-friend/g, '');
                            im.getInformation({
                                id: friend_id,
                                type: 'friend'
                            });
                        }
                    },
                    {
                        text: "聊天记录",
                        icon: "",
                        callback: function (ele) {
                            var othis = ele.parent(),
                                friend_id = othis[0].dataset.id.replace(/^layim-friend/g, ''),
                                friend_name = othis[0].dataset.name;
                            im.getChatLog({
                                name: friend_name,
                                id: friend_id,
                                type: 'friend'
                            });
                        }
                    }
                ]
            });

            // 好友分组右键
            $(".layim-list-friend >li > h5").contextMenu({
                width: 140, // width
                itemHeight: 30, // 菜单项height
                bgColor: "#fff", // 背景颜色
                color: "#333", // 字体颜色
                fontSize: 15, // 字体大小
                hoverBgColor: "#009bdd", // hover背景颜色
                hoverColor: "#fff", // hover背景颜色
                target: function (ele) { // 当前元素
                    console.log(ele);
                    // 将数据存储在选项父级元素dataset内
                    $(".ul-context-menu").attr("data-id", ele[0].dataset.id)
                        .attr("data-name", ele.find("span").html())
                        .attr("data-count", ele.find(".layim-count").text());
                },
                menu: [
                    { // 菜单项
                        text: "重命名分组",
                        icon: "",
                        callback: function (ele) {
                            var othis = ele.parent(),
                                group_id = othis[0].dataset.id,
                                group_name = othis[0].dataset.name;
                            if (group_id === 0) {
                                layer.msg('默认分组名称无法修改');
                                return;
                            }
                            layer.prompt({title: '请输入新的分组名称', formType: 3}, function (text, index) {
                                if (group_name === text) {
                                    return;
                                }
                                postJSON(url_rename_friend_group, {id: group_id, name: text}, function (res) {
                                    if (parseInt(res.code) === 200) {
                                        $(".layim-list-friend >li > .layim-friendgroup" + group_id).find("span").text(text);
                                        // 更改cache里面分组的值.
                                        var j = 0, len = layim.cache().friend.length;
                                        for (; layim.cache().friend[j].id != group_id && j < len; j++) {}
                                        layim.cache().friend[j].groupname = text;
                                    } else {
                                        layer.msg(res.message);
                                    }
                                });
                                layer.close(index);
                            });
                        }
                    },
                    {
                        text: "添加新分组",
                        icon: "",
                        callback: function (ele) {
                            layer.prompt({title: '请输入新的分组名称', formType: 3}, function (text, index) {
                                if (text.trim() === '') {
                                    layer.msg('名称不能为空');
                                    return;
                                }
                                postJSON(url_add_friend_group, {name: text}, function (res) {
                                    if (parseInt(res.code) === 200) {
                                        layim.addFriendGroup({id: res.results.id, groupname: text, list: []});
                                    } else {
                                        layer.msg(res.message);
                                    }
                                });

                                layer.close(index);
                            });
                        }
                    },
                    {
                        text: "删除分组",
                        icon: "",
                        callback: function (ele) {
                            var othis = ele.parent(),
                                group_id = othis[0].dataset.id,
                                group_name = othis[0].dataset.name;
                                friend_count = othis[0].dataset.count;
                            var index=layer.confirm('确认删除【'+group_name+'】？', {
                                btn: ['确认','取消'] //按钮
                            }, function(){
                                if (parseInt(friend_count) !== 0) {
                                    layer.msg('该分组下还存在好友，无法删除');
                                    return;
                                }
                                postJSON(url_delete_friend_group, {id: group_id}, function (res) {
                                    if (parseInt(res.code) === 200) {
                                        layim.removeFriendGroup({id: res.results.id});
                                    } else {
                                        layer.msg('操作失败，请稍后再试');

                                    }
                                });
                                return false;
                            }, function(){
                                layer.close(index);
                                return false;
                            });

                        }
                    }
                ]
            });

            $(".layim-list-group >li").contextMenu({
                width: 140, // width
                itemHeight: 30, // 菜单项height
                bgColor: "#fff", // 背景颜色
                color: "#333", // 字体颜色
                fontSize: 15, // 字体大小
                hoverBgColor: "#009bdd", // hover背景颜色
                hoverColor: "#fff", // hover背景颜色
                target: function (ele) { // 当前元素
                    console.log(ele);
                    // 将数据存储在选项父级元素dataset内
                    $(".ul-context-menu").attr("data-id", ele[0].className)
                        .attr("data-name", ele.find("span").html())
                        .attr("data-img", ele.find("img").attr('src'));
                    console.log(ele.find(".layim-count"));
                    console.log(ele.find("span").html());
                },
                menu: [
                    { // 菜单项
                        text: "重命名分组",
                        icon: "",
                        callback: function (ele) {
                            var othis = ele.parent(),
                                friend_id = othis[0].dataset.id.replace(/^layim-group/g, ''),
                                friend_name = othis[0].dataset.name,
                                friend_avatar = othis[0].dataset.img;
                            msg();
                            layim.chat({
                                name: friend_name
                                , type: 'friend'
                                , avatar: friend_avatar
                                , id: friend_id
                            });
                        }
                    },
                    {
                        text: "添加分组",
                        icon: "",
                        callback: function (ele) {
                            var othis = ele.parent(), friend_id = othis[0].dataset.id.replace(/^layim-group/g, '');
                            im.getInformation({
                                id: friend_id,
                                type: 'friend'
                            });
                        }
                    },
                    {
                        text: "删除分组",
                        icon: "",
                        callback: function (ele) {
                            var othis = ele.parent(),
                                friend_id = othis[0].dataset.id.replace(/^layim-group/g, ''),
                                friend_name = othis[0].dataset.name;
                            im.getChatLog({
                                name: friend_name,
                                id: friend_id,
                                type: 'friend'
                            });
                        }
                    }
                ]
            });
        }
    };
    exports('ext', ext);
});