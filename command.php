<?php
function keywordSplit($data)
{
	$data = mb_convert_kana($data, 's');
	return explode(" ", $data);
}

function replyEphemeral($msg)
{
	$res = array(
		"text" => $msg,
		"response_type" => "ephemeral"
	);
	header('Content-Type: application/json');
	echo json_encode($res);
}

function replyInChannel($msg)
{
	$res = array(
		"text" => $msg,
		"response_type" => "in_channel"
	);
	header('Content-Type: application/json');
	echo json_encode($res);
}

$ary_text = keywordSplit($_POST['text']);

if ($ary_text[0]) {
	$db = new PDO("mysql:host=<your_host>;dbname=<your_db_name>;charset=utf8","<id>","<pw>");

	if ($ary_text[0] == "list" && $ary_text[1] == "on") {
		$str_date = substr($_POST['text'], strlen($ary_text[0])+strlen($ary_text[1])+2);
		if (!strtotime($str_date))
			replyEphemeral("date format error : ".$str_date );
		
		$begin_time = strtotime(date("Y-m-d", strtotime($str_date)));
		$end_time = strtotime("+1 day", $begin_time);
		
		$sql = sprintf("SELECT * FROM `rollcall` WHERE timestamp BETWEEN %d AND %d ORDER BY timestamp ASC",  $begin_time, $end_time);
		$array = $db->query($sql);
		$length = $array->rowCount();
		$msg = "I saw ".$length." attendees on ".date("F jS", $begin_time).".\n```";
		if ($length > 0) {
			$cnt = 1;
			foreach ($array as $row) {
				$msg .= $row['user'];
				if ($cnt++ < $length) {
					$msg .= ", ";
				}
			}
		} else {
			$msg .= "nobody there";
		}
		$msg .= "```";
		replyInChannel($msg);
	} else if ($ary_text[0] == "list" && $ary_text[1] == "from") {
		$str_date = substr($_POST['text'], strlen($ary_text[0])+strlen($ary_text[1])+2);
		if (!strtotime($str_date))
			replyEphemeral("date format error : ".$str_date );
		
		$begin_time = strtotime(date("Y-m-d", strtotime($str_date)));
		$end_time = time();
		
		$msg = "I give you the attendance summary from ".date("F jS", $begin_time)." to now.\n>>>";
		$sql = sprintf("SELECT user, COUNT(*) AS freq FROM `rollcall` WHERE timestamp BETWEEN %d AND %d GROUP BY user", $begin_time, $end_time);
		$array = $db->query($sql);
		$length = $array->rowCount();
		if ($length > 0) {
			$cnt = 1;
			foreach ($array as $row) {
				$msg .= $row['user']." ".$row['freq'];
				if ($cnt++ < $length) {
					$msg .= "\n";
				}
			}
		} else {
			$msg .= "nobody there";
		}
		replyInChannel($msg);
	} else if ($ary_text[0] == "clear") {
		replyInChannel("you can't use this option.");
	} else {
		replyEphemeral("Syntax Error");
		exit;
	}
	$db = null;
} else {
	replyEphemeral('Usage : /rollcall ["list on" or "list from"] [date]');
	exit;
}
?>
