<?php

// TODO
// 1) Fix 'external' when adding URL into urls
// 2) Add page title
// 3) Create array of links from current page
// 3) index.php
// 4) stats.php

// For logging
include "logging.php";
ClearLog();

// For database access
$host = getenv("DB1_HOST");
$user = getenv("DB1_USER");
$pass = getenv("DB1_PASS");

$mysqli = new mysqli($host, $user, $pass);
$mysqli->select_db("gov_uk_data");

if ($mysqli->connect_errno) {
  print "Failed to connect to MySQL: " . $mysqli->connect_error;
}

// URL and link processing counts
$page_counter = 0;
$link_counter = 0;

// Handle root page
$current_page_id = 1;
$current_page_url = "http://gov.uk";

/*
while (true) {
*/

  // Update number of pages we've processes
  $page_counter++;

  // Get the current page's contents
  $current_page_html = file_get_contents($current_page_url);

  // Update the title text and set processed to true
  $title_start_pos = strpos($current_page_html, "<title>") + 7;
  $end_marker = '';
  if ($current_page_id == 1) {
    $end_marker = "</title>";
  } else {
    $end_marker = " -";
  }
  $title_end_pos = strpos($current_page_html, $end_marker, $title_start_pos);
  $current_page_title = substr($current_page_html, $title_start_pos, $title_end_pos - $title_start_pos);
  $sql_string = "UPDATE urls SET page_title='" . $current_page_title . "', processed='1' WHERE id='" . $current_page_id . "'";
  $result = $mysqli->query($sql_string);
  
  // Get all the links in $current_page_url and their link text
  $link_array = Array();
  $offset = strpos($current_page_html, "<main id=") + 9;
  $link_start_pos = strpos($current_page_html, "<a href=\"") + offset + 9 ;
  while ($link_start_pos != false) {
    $link_end_pos = strpos($current_page_html, "\"", $link_start_pos);
    $text_start_pos = strpos($current_page_html, ">", $link_end_pos) + 1;
    $text_end_pos = strpos($current_page_html, "<", $text_start_pos);
    $link_url = substr($current_page_html, $link_start_pos, $link_end_pos - $link_start_pos);
    $link_text = substr($current_page_html, $text_start_pos, $text_end_pos - $text_start_pos);
    $link_array[$link_url] = $link_text;
    
TestLogArray($link_array);
exit(0);
    
  }


  // For each link
  foreach ($link_array as $loop_url => $link_text) {
  
    // Update number of links we've processed
    $link_counter++;
  
    // Check if linked-to is in urls and add if not with last_run_id set to -1
    $sql_string = "SELECT id, last_run_id from urls WHERE url='" . $loop_url . "'";
    $result = $mysqli->query($sql_string);
    if ($result->num_rows == 0) {
      $sql_string = "INSERT INTO urls (url,external,page_title,inbound_link_count,processes) VALUES ('" . $loop_url . "','0','','0','0')";
      $result = $mysqli->query($sql_string);
      $target_page_id = $mysqli->insert_id;
    } else {
      $row = $result->fetch_assoc();
      $target_page_id = $row['id'];
    }
  
    // Check if this association is in links and add / update with the $current_run_id
    $sql_string = "SELECT id FROM links WHERE from_id='" . $current_page_id . "' AND to_id ='" . $target_page_id . "'";
    $result = $mysqli->query($sql_string);
    if ($result->num_rows == 0) {
      $sql_string = "INSERT INTO links (from_id,to_id,link_text) VALUES ('" . $current_page_id . "','" . $target_page_id . "','" . $link_text . "')";
      $result = $mysqli->query($sql_string);
    } else {
      $row = $result->fetch_assoc();
      $link_db_id = $row['id'];
      $sql_string = "UPDATE links SET last_run_id='" . $current_run_id . "' WHERE id='" . $link_db_id . "'";
      $result = $mysqli->query($sql_string);    
    }

    // Increase inbound_link_count for the target page
    $sql_string = "SELECT inbound_link_count FROM urls WHERE id='" . $target_page_id . "'";
    $result = $mysqli->query($sql_string);
    $row = $result->fetch_assoc();
    $count = $row['inbound_link_count'] + 1;
    $sql_string = "UPDATE urls SET inbound_link_count='" . $count . "' WHERE id='" . $target_page_id . "'";
    $result = $mysqli->query($sql_string);
  }
  
  // Get the first non-external URL in urls where last_run_id <> $current_run_id
  // If exsists set $current_page_url to it, else break
  $sql_string = "SELECT id, url FROM urls WHERE processed <> '1' AND external <> '1' LIMIT 1";
  $result = $mysqli->query($sql_string);
  if ($result->num_rows == 0) {
    break;
  } else {
    $current_page_id = $row['id'];
    $current_page_url = $row['url'];
  }
  
}

// Clean up DBs
$sql_string = "DELETE FROM urls WHERE last_run_id <> '" . $current_run_id . "'";
$result = $mysqli->query($sql_string);

$sql_string = "DELETE FROM links WHERE last_run_id <> '" . $current_run_id . "'";
$result = $mysqli->query($sql_string);

// Close DB
$mysqli->close();
*/

// Log some stats
TestLog("Number of pages processed : " . $page_counter);
TestLog("Number of links processed : " . $link_counter);

TestLog("Completed");
?>