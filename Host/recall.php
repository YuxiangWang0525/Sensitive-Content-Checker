<?php
// 版权所有 王钰翔@青岛电子学校 保留所有权利
$uid = $_GET['uid'];
$platform = $_GET['platform'];
$passed = $_GET['passed'];
$servername = "ServerHost";
$username = "DatabaseUsername";
$password = "DatabaseUserPassword";
$dbname = "DatabaseName";
$conn = new mysqli($servername, $username, $password, $dbname);
if ($conn->connect_error) {
    die("Error!!!: " . $conn->connect_error);
}
if($passed==0){
    $sql = "UPDATE scc_list SET status='nopass' WHERE uid='$uid' AND platform='$platform'";
    $conn->query($sql);
    exit();
}else if($passed==1){
    $sql = "UPDATE scc_list SET status='passed' WHERE uid='$uid' AND platform='$platform'";
    $conn->query($sql);
}
?>