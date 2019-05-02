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

$selectNews = "select `pa`.`link` AS `link`,`com`.`text` AS `text`,`com`.`created_at` AS `created_at` ,`com`.`plus` AS `plus` ,`com`.`minus` AS `minus` 
from (`comments` `com` join `pages` `pa` on((`com`.`page_id` = `pa`.`page_id`))) order by `com`.`created_at` desc";
echo "<table border='1' cellpadding='2' cellspacing='2'>";
echo "<tr>";
echo "<th>Статья</th>";
echo "<th>Коммент</th>";
echo "<th>Дата</th>";
echo "<th>Лайк</th>";
echo "<th>Диз</th>";
echo "</tr>";
$result = $conn->query($selectNews);
if ($result->num_rows > 0) {

    while($row = $result->fetch_assoc()) {
        $timestamp = strtotime($row['created_at']) + 3*60*60;
        $createdAtReply = date("Y-m-d H:i:s", $timestamp);
        echo "<tr>";
        echo "<td><a href='".$row['link']."'>".$row['link']."</a></td>";
        echo "<td>".nl2br($row['text'])."</td>";
        echo "<td>".$createdAtReply."</td>";
        echo "<td>".$row['plus']."</td>";
        echo "<td>".$row['minus']."</td>";
        echo "</tr>";
    }
}
echo "</table>";