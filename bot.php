<html>
<?php
$servername = "localhost";
// database information
$username   = "";
$password   = "";
$db         = "";

// Create connection to DB
$conn = new mysqli($servername, $username, $password, $db);
// Check connection to DB
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}
echo "Connected successfully.</br>";

$botToken = ""; // Bot token goes here
$website  = "https://api.telegram.org/bot" . $botToken;


$conn->query("SET SESSION time_zone = '+8:00'");
$timezone = date_default_timezone_get();
echo "The current server timezone is: " . $timezone;
// Set TimeZone to Hong Kong
date_default_timezone_set('Asia/Hong_Kong');
echo "<br/>The current server timezone after default_set is: " . $timezone;
$date    = date('m/d/Y h:i:s a', time());
$type_in = date("Y-m-d H:i:s", time());

$content    = file_get_contents("php://input");
$update     = json_decode($content, true);
$chatId     = $update["message"]["chat"]["id"];
$message    = $update["message"]["text"];
$msg_update = $conn->prepare("INSERT INTO `message` (chat_id, message, get_time) VALUES (?,?,NOW())");
$msg_update->bind_param("ss", $chatId, $message);
$msg_update->execute();
$msg_update->close();


if ($message == "/time") {
    sendMessage($chatId, "而家香港嘅時間係： " . "\nThe time now is in HongKong: \n" . "*" . $date . "*", "markdown");
    //eq. "<b>".$date."</b>","HTML");
}

//$conn->query($message_update);
/*
if ($conn->query($message_update) === TRUE) {
echo "</br>Successful message cache.</br>";
} else {
echo "</br></br>Fail to cache.</br>";
echo $conn->error."</br>";
}
*/

$check_mem = "SELECT chat_id, user_name, reg_time , last_use_time FROM user WHERE chat_id = $chatId";
$result    = $conn->query($check_mem);
//$row_cnt = $result->num_rows;
if ($result) {
    /* output data of each row
    while($row = $result->fetch_assoc()) {
    echo "id: " . $row["id"]. " - Name: " . $row["firstname"]. " " . $row["lastname"]. "<br>";
    } 
    */
    sendMessage($chatId, "你未登記喎。\nYou have to /register.", "");
} else {
    while ($result) {
        $row = mysqli_fetch_assoc($result);
        sendMessage($chatId, "你好，" . "`" . $row['user_name'] . "`。" . "\nHello, " . "`" . $row['user_name'] . "`.", "markdown");
    }
}

//sendMessage($chatId,"The time now is in HongKong: ".$date);
//sendMessage($chatId,"試下用 /wea。\nTry /wea.");
//sendMessage($chatId,$date);



if ($message == "/hkoweather") {
    // Parsing HKO's XML
    $xml_str = file_get_contents('http://rss.weather.gov.hk/rss/LocalWeatherForecast_uc.xml');
    $xml     = simplexml_load_string($xml_str);
    $title   = $xml->channel->title;
    $report  = $xml->channel->item->title;
    $otime   = $xml->channel->item->pubDate;
    
    $doc = new DOMDocument();
    $doc->load('http://rss.weather.gov.hk/rss/LocalWeatherForecast_uc.xml');
    $des = $doc->getElementsByTagName("description");
    foreach ($des as $description) {
        foreach ($description->childNodes as $child) {
            if ($child->nodeType == XML_CDATA_SECTION_NODE) {
                //echo $child->textContent . "<br/>";
                $cc   = preg_replace('/\s+/', '', $child->textContent);
                $cc   = str_ireplace("<br/>", "\n", $cc);
                $cc   = str_ireplace("<p/>", "\n\n", $cc);
                $cc   = str_ireplace(":", "：", $cc);
                $body = $title . "\n\n" . $report . "\n" . $otime . "\n\n" . $cc;
                sendMessage($chatId, $body, "");
            }
        }
    }
    
}

$keyboard = array(
    array(
        "/time",
        "/hkoweather",
        "/comment"
    )
);

$resp  = array(
    "keyboard" => $keyboard,
    "resize_keyboard" => true,
    "one_time_keyboard" => false
);
$reply = json_encode($resp);
$urll  = $website . "/sendmessage?chat_id=" . $chatId . "&text=" . urlencode("你想點？\nChoose your command.") . "&reply_markup=" . $reply;
$chh   = curl_init();
curl_setopt($chh, CURLOPT_URL, $urll);
curl_setopt($chh, CURLOPT_RETURNTRANSFER, 1);
$filee = curl_exec($chh);
curl_close($chh);

//$min = date('i');
//echo end($oneA) != "18:40:00";

function sendMessage($chatId, $message, $parse)
{
    $botToken = ""; // Token goes here
    $website  = "https://api.telegram.org/bot" . $botToken . "/";
    $url      = $website . "sendMessage?chat_id=" . $chatId . "&text=" . urlencode($message) . "&parse_mode=" . $parse;
    $ch       = curl_init();
    curl_setopt($ch, CURLOPT_URL, $url);
    curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
    $file = curl_exec($ch);
    curl_close($ch);
}
?>
</html>
