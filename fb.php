<?php
require("db.class.php");

$db=new Database("host","user","pass","dbname");
$db->connect();
$db->conn->set_charset("utf8");

$re->ch = curl_init();
curl_setopt($re->ch, CURLOPT_HEADER, 0);
curl_setopt($re->ch, CURLOPT_RETURNTRANSFER,1);

$csv=1; // do not show a shit

function fetchpageposts($pageID)
   {
   global $token, $re;
   $success=0; $retry=0; // fetch until success
   while ($success==0)
      {
      $url="https://graph.facebook.com/".$pageID."/feed?fields=id,message,type,picture,link,likes.summary%28true%29,comments.summary%28true%29,shares&access_token=".$token;
      curl_setopt($re->ch, CURLOPT_URL, $url);
      $content=curl_exec($re->ch);
      $json=json_decode($content);
      if (isset($json->data[0])) $success=1; // success, continue
      else sleep(2); // else failed - sleep 2s & repeat
      $retry++;
      if ($retry>3) break;
      }
   return $json->data;
   }

?>
<!DOCTYPE html>
<html lang="en">
<head>
<meta charset="UTF-8" />
</head>
<body>
<?php

$limit=5;
$appID=208479938375;
$appsecret="81b251e23fdbd955ccea89ad5a76a973";
$tokenurl="https://graph.facebook.com/oauth/access_token?client_id=".$appID."&client_secret=".$appsecret."&grant_type=client_credentials";
curl_setopt($re->ch, CURLOPT_URL, $tokenurl);
$content=curl_exec($re->ch);
$json=json_decode($content);
parse_str($content, $params);
if (!isset($params["access_token"])) // use existing token
   {
   $token=file_get_contents("token.txt");
   }
else // fetch new token and save it for later use
   {
   $token=$params["access_token"];
   file_put_contents("token.txt",$token);
   }

$result=$db->query("SELECT * FROM pages");

while ($row=$result->fetch_assoc())
   {
   $pageID=$row["fbname"];
   $pages[]=$row["name"];
   $url="https://graph.facebook.com/".$pageID."?access_token=".$token;
   curl_setopt($re->ch, CURLOPT_URL, $url);
   $content=curl_exec($re->ch);
   $json=json_decode($content);
   $pagename=$json->name;
   if (!$csv)
      {
      echo '<h1>',$pagename,'</h1>';
      }

   if (!$csv)
      {
      echo '<table>';
      echo '<tr><td>Time</td><td>Status length</td><td>Type</td><td>Likes</td><td>Shares</td><td>Comments</td><td>Link?</td><td>Image?</td></tr>';
      }
   $json->data=fetchpageposts($pageID);
   foreach ($json->data as $post)
      {
      if (isset($post->to) AND !isset($post->from->category)) continue; // user posting to page, skip
      $postid=$post->id;
      $message=$post->message;
      $type=$post->type; //link, photo, video
      $picture=$post->picture;
      $link=$post->link;
      $likes=$post->likes->summary->total_count;
      $shares=$post->shares->count;
      $comments=$post->comments->summary->total_count;
      $time=str_replace("+0000","",$post->created_time);
      $message=preg_replace('~[\r\n]+~', ' ', $message);
      $messagelength=strlen($message);
      $message=$db->conn->real_escape_string($message);
      $picture=$db->conn->real_escape_string($post->picture);
      $link=$db->conn->real_escape_string($post->link);
      if (array_search($post->from->name,$pages)) // post by page, not by user
      {
          $result2=$db->query("SELECT postid FROM posts WHERE postid='".$postid."'");
          if (!$result2->num_rows AND $post->from->name==$pagename) // post does not exist and post is by page
             {
             $db->query("INSERT INTO posts SET fbname='".$pageID."',postid='".$postid."',type='".$type."',time='".$time."',length='".$messagelength."',picture='".$picture."',link='".$link."',message='".$message."',pagepost='1'");
             $db->query("INSERT INTO poststats SET postid='".$postid."',time='".gmdate("Y-m-d H:i:s")."',shares='".$shares."',comments='".$comments."',likes='".$likes."'");
             }
          elseif (time()-strtotime($time)<=18000 AND $post->from->name==$pagename) // <5 hours and post by page
             {
             $db->query("INSERT INTO poststats SET postid='".$postid."',time='".gmdate("Y-m-d H:i:s")."',shares='".$shares."',comments='".$comments."',likes='".$likes."'");
             }
            $db->conn->commit();
       }

      if (!$csv)
         {
         echo '<tr><td>',$time,'</td><td>',$messagelength,'</td><td>',$type,'</td><td>',$likes,'</td><td>',$shares,'</td><td>',$comments,'</td><td>',$link,'</td><td>',$picture,'</td></tr>';
         $csvexport.=$pagename.'|'.$time.'|'.$messagelength.'|'.$type.'|'.$likes.'|'.$shares.'|'.$comments.'|'.$link.'|'.$picture."\n";
         flush(); ob_flush();
         }
      }
   if (!$csv)
      {
      echo '</table></body></html>';
      }
   }
?>