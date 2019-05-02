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

include('simple_html_dom.php');

$userId = 289815;
$url = "https://www.onliner.by/feed";
$xml = simplexml_load_file($url);

for($i = 0; $i < 9999; $i++){
    if (!isset($xml->channel->item[$i])) {
        break;
    }
    $title = $xml->channel->item[$i]->title;
    $link = $xml->channel->item[$i]->link;
    $description = $xml->channel->item[$i]->description;
    $pubDate = $xml->channel->item[$i]->pubDate;
    $html = file_get_html($link);
    $newDate = date("Y-m-d H:i:s", strtotime($pubDate));
    $newsType = getStringBetween((string)$link, '://', '.onliner');
    foreach($html->find('span.news_view_count') as $e) {
        $newsId = $e->attr['news_id'];
        $sql = "INSERT IGNORE INTO `pages` (`page_id`, `type`, `link`, `date`) VALUES (".$newsId.", '".$newsType."','".$link."', '".$newDate."')";
        if ($conn->query($sql) === TRUE) {
            // do nothing
        } else {
            echo "Error: " . $sql . "<br>" . $conn->error;
        }
    }
}

$selectNews = "SELECT * FROM `pages` WHERE date > adddate(now(),-5)";
$result = $conn->query($selectNews);
if ($result->num_rows > 0) {
    // output data of each row
    while($row = $result->fetch_assoc()) {
        $newsId = $row["page_id"];
        $type = $row["type"];
        $apiUrl = 'https://comments.api.onliner.by/news/'.$type.'.post/'.$newsId.'/comments?limit=9999';
        $apiData = file_get_contents($apiUrl);
        $data  = json_decode($apiData);

        for($j = 0; $j < 9999; $j++) {
            if (!isset($data->comments[$j])) {
                break;
            }

            $commentUserId = $data->comments[$j]->author->id;

            if ($commentUserId == $userId) {
                $text = $data->comments[$j]->text;
                $id = $data->comments[$j]->id;
                $like = $data->comments[$j]->marks->likes;
                $dislike = $data->comments[$j]->marks->dislikes;
                $createdAt = $data->comments[$j]->created_at;
                $createdAt = date("Y-m-d H:i:s", strtotime($createdAt));
                $sqlComment = "INSERT INTO `comments` (`id`, `page_id`, `text`, `created_at`,`plus`, `minus`) 
                    VALUES ('".$id."', ".$newsId.", '".$text."', '".$createdAt."',".$like.", ".$dislike.")
                    ON DUPLICATE KEY UPDATE `plus`=".$like.", `minus`=".$dislike;
                if ($conn->query($sqlComment) === TRUE) {
                    // do nothing
                } else {
                    echo "Error: " . $sqlComment . "<br>" . $conn->error;
                }
            }

            $replies = $data->comments[$j]->replies;
            if (isset ($replies)) {
                foreach ($replies as $reply) {
                    $commentReplyUserId = $reply->author->id;
                    if ($commentReplyUserId == $userId) {
                        $textReply = $reply->text;
                        $idReply = $reply->id;
                        $like = $reply->marks->likes;
                        $dislike = $reply->marks->dislikes;
                        $createdAtReply = date("Y-m-d H:i:s", strtotime($reply->created_at));
                        $sqlCommentReply = "INSERT INTO `comments` (`id`, `page_id`, `text`, `created_at`,`plus`, `minus`) 
                            VALUES ('".$idReply."', ".$newsId.", '".$textReply."', '".$createdAtReply."',".$like.", ".$dislike.") 
                            ON DUPLICATE KEY UPDATE `plus`=".$like.", `minus`=".$dislike;
                        if ($conn->query($sqlCommentReply) === TRUE) {
                            // do nothing
                        } else {
                            echo "Error: " . $sqlCommentReply . "<br>" . $conn->error;
                        }
                    }
                }

            }
        }
    }
} else {
    echo "0 results";
}
$conn->close();


function getStringBetween($string, $start, $end)
{
    $string = ' ' . $string;
    $ini = strpos($string, $start);
    if ($ini == 0) return '';
    $ini += strlen($start);
    $len = strpos($string, $end, $ini) - $ini;
    return substr($string, $ini, $len);
}