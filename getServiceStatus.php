<!DOCTYPE html>
<html>
<head>
    <meta charset="UTF-8">
    <title>测试服务是否可用</title>
    <script src="https://enterbj.zhongchebaolian.com/enterbj/static_resources/enterbj/js/jquery-1.11.1.min.js"></script>
    <script type="text/javascript">
        <!-- 客户端用轮询查询的方式获取当前状态 -->
        function play() {
            var audio = document.getElementById("wav");
            audio.play();
        }
        function stop() {
            var audio = document.getElementById("wav");
            audio.pause();
            audio.currentTime = 0;
        }
        var local_status = 0;
        function checkStatus(status) {
            // 状态变为200则启动声音循环播放，若状态从200变为其他则停止播放
            if (status == 0) {
                // 服务器正在更新中
            } else if (status == 200) {
                // 提示
                if (local_status != 200) {
                    // play
                    play();
                }
            } else {
                // 失败
                if (local_status == 200) {
                    // stop
                    stop();
                }
            }
            // 更新本地状态
            local_status = status;
        }
        function getServerStatus() {
            $.ajax({
                type:"get",
                url:"checkService.php",
                dataType:"json",
                success:function(msg){
                    $("#lasttime").text(msg.timestamp);
                    $("#status").text(msg.status);

                    checkStatus(msg.status);
                }
            });
        }
        getServerStatus();
        setInterval(getServerStatus, 5 * 60 * 1000);
    </script>
</head>
<body>
<audio src="message.wav" id="wav" preload="auto" loop="loop"></audio>
<h1>最后一次查询时间：<p id="lasttime"></p></h1>
<h1>服务器状态：<p id="status"></p></h1>
</body>
</html>