<!DOCTYPE html>
<html>
<head>
    <title>Latest York's Comments</title>
    <!-- for-mobile-apps -->
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />
    <meta name="viewport" content="width=device-width, initial-scale=1, maximum-scale=1">
    <meta http-equiv="Content-Type" content="text/html; charset=utf-8" />

    <!-- //for-mobile-apps -->
    <link href="css/style.css" rel="stylesheet" type="text/css" media="all" />
</head>
<body>
<div class="main">
    <h1>Latest York's Comments</h1>
    <div class="content">
        <div class="content-top">
            <ul>
                <?php
$servername = "localhost";
$username = "#";
$password = "#";
$dbname = "#";

// Create connection
$conn = new mysqli($servername, $username, $password, $dbname);
// Check connection
if ($conn->connect_error) {
    die("Connection failed: " . $conn->connect_error);
}

if (!$conn->set_charset("utf8")) {
    printf("Ошибка при загрузке набора символов utf8: %s\n", $conn->error);
    exit();
}

$selectNews = "select `pa`.`link` AS `link`, `pa`.`type` AS `type`,`com`.`text` AS `text`,`com`.`created_at` AS `created_at` ,`com`.`plus` AS `plus` ,`com`.`minus` AS `minus` 
from (`comments` `com` join `pages` `pa` on((`com`.`page_id` = `pa`.`page_id`))) order by `com`.`created_at` desc";

$result = $conn->query($selectNews);
if ($result->num_rows > 0) {

    while($row = $result->fetch_assoc()) {
        $timestamp = strtotime($row['created_at']) + 3*60*60;
        $createdAtReply = date("Y-m-d H:i:s", $timestamp);
        echo '<li>';
        echo '<a href='.$row["link"].' target="_blank">'.nl2br($row['text']).'<i>'.$row['type'].'</i></a>';
        echo '<span>'.$createdAtReply.'<span>';
        echo '<span> Like '.$row['plus'].'<span>';
        echo '<span> Dis '.$row['minus'].'<span>';
        echo '</li>';
    }
}

?>
            </ul>
            <p><a href="https://profile.onliner.by/user/289815" target="blank">Follow me on Onliner.by</a></p>
        </div>
    </div>
</div>
</body>
</html>