<?php

// TODO
// 0) Extract URLs from current page into an array with their link text (turn into full links)
// 1) Fix 'external' when adding URL into urls (bin both places)
// 2) Remember to switch off logging

// For logging
include "logging.php";
ClearLog();
TestLogArray($_POST);

// For database access
$host = getenv("DB1_HOST");
$user = getenv("DB1_USER");
$pass = getenv("DB1_PASS");

$mysqli = new mysqli($host, $user, $pass);
$mysqli->select_db("gov_uk_data");

if ($mysqli->connect_errno) {
  print "Failed to connect to MySQL: " . $mysqli->connect_error;
}

// Record starting run
$seconds_since_epoch = time();
$sql_string = "INSERT INTO run (started) VALUES ('" . $seconds_since_epoch . "')";
$result = $mysqli->query($sql_string);
$current_run_id = $mysqli->insert_id;

if ($mysqli->error) {
  print "Error description: " . $mysqli->error . "\n";
}

// Get root page
$current_page = file_get_contents("http://gov.uk");

$this_page_id = 0;
while (true) {

  // See if current page is already stored and either add or update run number
  $sql_string = "SELECT id FROM urls WHERE url='" . $current_page . "'";
  $result = $mysqli->query($sql_string);
  if ($result->num_rows == 0) {
    $sql_string = "INSERT INTO urls (url,external,last_run_id) VALUES ('" . $current_page . "','0','" . $current_run_id . "')";
    $result = $mysqli->query($sql_string);
    $this_page_id = $mysqli->insert_id;
  } else {
    $row = $result->fetch_assoc();
    $this_page_id = $row['id'];
    $sql_string = "UPDATE urls SET last_run_id='" . $current_run_id . "' WHERE id='" . $this_page_id . "'";
    $result = $mysqli->query($sql_string);    
  }

  // Get all the links in $current_page and their link text

MISSING BIT HERE!


  // For each link
  foreach ($link_array as $loop_url => $link_text) {
  
    // Check if linked-to is in urls and add if not with last_run_id set to -1
    $sql_string = "SELECT id, last_run_id from urls WHERE url='" . $loop_url . "'";
    $result = $mysqli->query($sql_string);
    $link_page_id = 0;
    if ($result->num_rows == 0) {
      $sql_string = "INSERT INTO urls (url,external,last_run_id) VALUES ('" . $loop_url . "','0','-1')";
      $result = $mysqli->query($sql_string);
      $link_page_id = $mysqli->insert_id;
    } else {
      $row = $result->fetch_assoc();
      $link_page_id = $row['id'];
    }
  
    // Check if this association is in links and add / update with the $current_run_id
    $sql_string = "SELECT id FROM links WHERE from_id='" . $this_page_id . "' AND to_id ='" . $link_page_id . "'";
    $result = $mysqli->query($sql_string);
    if ($result->num_rows == 0) {
      $sql_string = "INSERT INTO links (from_id,to_id,link_text,last_run_id) VALUES ('" . $this_page_id . "','" . $link_page_id . "','" . $link_text . "','" . $current_run_id . "')";
      $result = $mysqli->query($sql_string);
    } else {
      $row = $result->fetch_assoc();
      $link_db_id = $row['id'];
      $sql_string = "UPDATE links SET last_run_id='" . $current_run_id . "' WHERE id='" . $link_db_id . "'";
      $result = $mysqli->query($sql_string);    
    }

  }
  
  // Get the first non-external URL in urls where last_run_id <> $current_run_id
  // If exsists set $current_page to it, else break
  $sql_string = "SELECT url FROM urls WHERE last_run_id <> '" . $current_run_id . "' LIMIT 1";
  $result = $mysqli->query($sql_string);
  if ($result->num_rows == 0) {
    break;
  } else {
    $current_page = $row['url'];
  }
  
}

// Clean up DBs
$sql_string = "DELETE FROM urls WHERE last_run_id <> '" . $current_run_id . "'";
$result = $mysqli->query($sql_string);

$sql_string = "DELETE FROM links WHERE last_run_id <> '" . $current_run_id . "'";
$result = $mysqli->query($sql_string);

// Close DB
$mysqli->close();

?>