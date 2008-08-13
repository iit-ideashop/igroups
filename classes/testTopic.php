<?php

include_once("topic.php");
include_once("thread.php");
include_once("post.php");
include_once("db.php");
include_once("globaltopic.php");


$db = new dbConnection();

$topic = new Topic(300, $db);

print "Thread count: {$topic->getThreadCount()}<br>Post count: {$topic->getPostCount()}<br>";

$thread = createThread("Test Thread 1", 3515, 300, $db);

print "<b>Create Thread 1</b><br>";
print "Thread count: {$topic->getThreadCount()}<br>Post count: {$topic->getPostCount()}<br>";
print "<b>Create Post 1</b><br>";

$post = createPost($thread->getID(), "Test Post 1", "This is only a test", 3515, $db);

print "Thread count: {$topic->getThreadCount()}<br>Post count: {$topic->getPostCount()}<br>";

print "<b>Create Thread 2</b><br>";

$thread2 = createThread("Test Thread 2", 3512, 300, $db);

print "Thread count: {$topic->getThreadCount()}<br>Post count: {$topic->getPostCount()}<br>";

$gTopic = createGlobalTopic("Global 1", "This is a test", $db);
$thread3 = createThread("Test Thread 3", 3514, $gTopic->getID(), $db);

print "<b>Create Global Topic</b><br>";
print "Thread count: {$topic->getThreadCount()}<br>Post count: {$topic->getPostCount()}<br>";
print "Global Thread count: {$gTopic->getThreadCount()}<br>Global Post count: {$gTopic->getPostCount()}<br>";

print "<b>Create Global Post</b><br>";
$post2 = createPost($thread3->getID(), "Test Post 2", "This is a test", 3512, $db);

print "Thread count: {$topic->getThreadCount()}<br>Post count: {$topic->getPostCount()}<br>";
print "Global Thread count: {$gTopic->getThreadCount()}<br>Global Post count: {$gTopic->getPostCount()}<br>";

$thread->delete();
$thread2->delete();
$thread3->delete();
$gTopic->delete();

?>
