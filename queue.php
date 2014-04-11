<p>&nbsp;</p>
<p>&nbsp;</p>
<?php
session_start();
include("inc_connect.php");
include("inc_functions.php");
$login = false;
$user = array();
//GET MESSAGES
if(isset($_GET['e'])){
$errors[] = $_GET['e'];
}
if(isset($_GET['m'])){
$messages[] = $_GET['m'];
}
//Check login
function checklogin(){
global $user, $login,$_SESSION;
if(isset($_SESSION['user']) && $_SESSION['user'] > 0){
$user_query = mysql_query("SELECT * FROM users WHERE id = ".intval($_SESSION['user']));
if($user_query && mysql_num_rows($user_query) > 0){
$user = mysql_fetch_array($user_query);
$login = true;
}
}
}
checklogin();
//SUBMIT FORMS
$replacers = array();
include("inc_forms.php");
checklogin();
//
if(isset($user['username'])){
$replacers['#username#'] = $user['username'];
}
if(isset($user['email'])){
$replacers['#email#'] = $user['email'];
}
if(isset($user['fname'])){
$replacers['#fname#'] = $user['fname'];
}
if(isset($user['lname'])){
$replacers['#lname#'] = $user['lname'];
}
if(isset($user['zipcode'])){
$replacers['#zipcode#'] = $user['zipcode'];
}
if(isset($user['gender'])){
$replacers['#gender#'] = $user['gender'];
}
if(isset($user['age'])){
$replacers['#age#'] = $user['age'];
}
//GET QUEUE
$queue = array();
$queuect = 0;
$queue_query = mysql_query("SELECT queue.date as queuedate, users.* FROM queue INNER JOIN users ON queue.user = users.id ORDER BY queue.id") or die(mysql_error());
if(mysql_num_rows($queue_query) > 0){
while($queue_array = mysql_fetch_array($queue_query)){
$queue[] = $queue_array;
$queuect++;
if(isset($user['id']) && $queue_array['id'] == $user['id']){
$user_queue = $queue_array;
$user_queue_pos = $queuect;
}
}
}
//GET SETTINGS
$settings = array();
$settings_query = mysql_query("SELECT * FROM settings");
if(mysql_num_rows($settings_query)>0){
while($settings_array = mysql_fetch_array($settings_query)){
$settings[$settings_array['name']] = $settings_array;
}
}
//ADD TO QUEUE
if(isset($_GET['action']) && $_GET['action'] == "add"){
if($login){
//Make sure they are not already in the queue
if(!isset($user_queue)){
//Add user
mysql_query("INSERT INTO queue (user) VALUES (".intval($user['id']).")");
//Change FirstDate if no other users
if($queuect == 0){
mysql_query("UPDATE settings SET value = 'updated' WHERE name = 'firstdate'");
}
header("Location: index.php?m=You have been added to the queue");
}else{
header("Location: index.php?e=You are already in the queue");
}
}else{
header("Location: index.php?e=You must login to be added to the queue");
}
}
//DROP FROM QUEUE
if(isset($_GET['action']) && $_GET['action'] == "drop"){
if($login){
//Make sure are in the queue
if(isset($user_queue)){
//Remove user
mysql_query("DELETE FROM queue WHERE user = ".intval($user['id']));
//Change FirstDate if first user
if($user_queue_pos == 1){
mysql_query("UPDATE settings SET value = 'updated', date = NOW() WHERE name = 'firstdate'")or die(mysql_error());
//Check who the first user is and email them
//Send email about user
$firstpos_query = mysql_query("SELECT queue.date as queuedate, users.* FROM queue INNER JOIN users ON queue.user = users.id ORDER BY queue.id LIMIT 1");
if(mysql_num_rows($firstpos_query) > 0){
$firstpos = mysql_fetch_array($firstpos_query);
$emailmessage = "";
$emailmessage .= "<p>You are now in position 1!</p>\n\n";
sendemail($firstpos['email'],"Queue - First Position",$emailmessage);
}
}
header("Location: index.php?m=You have been removed from the queue");
}else{
header("Location: index.php?e=You are not in the queue");
}
}else{
header("Location: index.php?e=You must login");
}
}
?>
