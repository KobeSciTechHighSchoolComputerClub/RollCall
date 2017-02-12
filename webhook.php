<?php
if($_POST["user_name"] == "slackbot") {
    exit;
}

require "account_info.php";
$db = new PDO("mysql:host=".$host.";dbname=".$dbname.";charset=utf8",$id,$pw);

$sql = "CREATE TABLE IF NOT EXISTS `rollcall` (
  `id` int(11) unsigned NOT NULL AUTO_INCREMENT,
  `user` varchar(255) NOT NULL,
  `domain` varchar(255) NOT NULL,
  `timestamp` int(11) unsigned NOT NULL,
  PRIMARY KEY (`id`),
  INDEX (`user`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8 AUTO_INCREMENT=1";
$result = $db->query($sql);

$sql = sprintf("SELECT user, domain, timestamp FROM `rollcall` WHERE user = '%s' ORDER BY timestamp DESC LIMIT 1", $_POST["user_name"]);
$result= $db->query($sql);
$row = $result->fetch(PDO::FETCH_ASSOC);

$cur_time = time();
if (!$row || date("Ymd", $cur_time) != date("Ymd", $row['timestamp'])) {
    $sql = sprintf("INSERT INTO `rollcall` (`user`, `domain`, `timestamp`) VALUES ('%s', '%s', %d)", $_POST["user_name"], $_POST["team_domain"], $cur_time);
    $result = $db->query($sql);
    
    $text = '@'.$_POST["user_name"].' attended on '.date("F jS", $cur_time).'.';
    $payload = array('text' => $text);
    echo json_encode($payload);
}
$db = null;
?>
