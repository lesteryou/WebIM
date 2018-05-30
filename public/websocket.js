var ws = new WebSocket(url);
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
            ws.send("HeartBeat");
            self.serverTimeoutObj = setTimeout(function () {
                //如果onclose会执行reconnect，我们执行ws.close()就行了.如果直接执行reconnect 会触发onclose导致重连两次
                ws.close();
            }, self.timeout)
        }, this.timeout)
    }
};

function reconnect() {


}
ws.onopen = function () {
    heartCheck.start();
};
ws.onmessage = function (event) {
    heartCheck.reset();
};

ws.onclose = function () {
    reconnect();
};
ws.onerror = function () {
    reconnect();
};