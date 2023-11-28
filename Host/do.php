<?php
// 版权所有 王钰翔@青岛电子学校 保留所有权利
	header('Content-Type: text/html;charset=utf-8');
    header('Access-Control-Allow-Origin:*'); // *代表允许任何网址请求
    header('Access-Control-Allow-Methods:POST,GET,OPTIONS,DELETE'); // 允许请求的类型
    header('Access-Control-Allow-Credentials: true'); // 设置是否允许发送 cookies
    header('Access-Control-Allow-Headers: Content-Type,Content-Length,Accept-Encoding,X-Requested-with, Origin'); // 设置允许自定义请求头的字段

$uid = $_POST['uid'];
$platform = $_POST['platform'];
$servername = "ServerHost";
$username = "DatabaseUsername";
$password = "DatabaseUserPassword";
$dbname = "DatabaseName";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error!!!: " . $conn->connect_error);
}
//检查是否存在
$sql = "SELECT * FROM scc_list WHERE uid='$uid' AND platform='$platform'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
if ($result->num_rows != 0 && $row['status'] != "nopass" && $row['status'] != "waiting"){
    //查询是否存在结果
    $return = array(
        'code' => 120,
        'msg' => '数据库已有结果或正在进行中,请稍后再试'
    );
    echo json_encode($return);
    exit();
}else if($result->num_rows != 0 && $row['status'] == "nopass"){
    $sql = "DELETE FROM scc_list WHERE uid='$uid' AND platform='$platform'";
    $conn->query($sql);
}
//写入开始
    $sql = "INSERT scc_list(platform,uid,`status`) VALUES('$platform','$uid','doing')";
    $conn->query($sql);
    //请求开始
    $url = "https://sccslave.bmsg-oper.cn/checker?platform=$platform&uid=$uid";
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    if($response === false||$data['code']!=0){
        $sql = "UPDATE scc_list SET status='nopass' WHERE uid='$uid' AND platform='$platform'";
        $conn->query($sql);
        $return = array(
            'code' => 500,
            'msg' => '从机错误,请联系管理员'
        );
        echo json_encode($return);
        exit();
    }
    $return = array(
        'code' => 0,
        'msg' => '自动审核任务已提交'
    );
    echo json_encode($return);
?>