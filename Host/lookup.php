<?php
// 版权所有 王钰翔@青岛电子学校 保留所有权利
$uid = $_GET['uid'];
$platform = $_GET['platform'];
$servername = "ServerHost";
$username = "DatabaseUsername";
$password = "DatabaseUserPassword";
$dbname = "DatabaseName";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error!!!: " . $conn->connect_error);
}
$sql = "SELECT * FROM scc_list WHERE uid='$uid' AND platform='$platform'";
$result = $conn->query($sql);
$row = $result->fetch_assoc();
switch($row['status']){
    case "passed":
    $return = array(
        'code' => 0,
        'msg' => '您已通过自动审核,恭喜,您可以入群了'
    );
    echo json_encode($return);
    break;
    case "nopass":
    $return = array(
        'code' => 150,
        'msg' => '审核失败,此账号含有敏感词或服务器出错,请检查或联系管理员'
    );
    echo json_encode($return);
    break;
    case "doing":
        $return = array(
            'code' => 180,
            'msg' => '正在审核中,请稍侯'
        );
    echo json_encode($return);
    break;
}
?>