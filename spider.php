<?php

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

// Before we start empty the DBs
$sql_string = "TRUNCATE TABLE urls";
$result = $mysqli->query($sql_string);
$sql_string = "TRUNCATE TABLE links";
$result = $mysqli->query($sql_string);

// Handle root page
$sql_string = "INSERT INTO urls (url,external,page_title,inbound_link_count,processed) VALUES ('http://gov.uk','0','','0','0')";
$result = $mysqli->query($sql_string);
$current_page_id = 1;
$current_page_url = "http://gov.uk";

while (true) {

  // Update number of pages we've processes
  $page_counter++;
  TestLog("Processing page " . $page_counter . " : " . $current_page_url);

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
  $offset = strpos($current_page_html, "<main id=") + 9;
  $link_start_pos = strpos($current_page_html, "<a href=\"", $offset);
  while ($link_start_pos != false) {
    $link_start_pos += 9; // Need this as is also used as end of loop check (needs to be false / zero)
    $link_end_pos = strpos($current_page_html, "\"", $link_start_pos);
    $text_start_pos = strpos($current_page_html, ">", $link_end_pos) + 1;
    $text_end_pos = strpos($current_page_html, "<", $text_start_pos);
    $link_url = substr($current_page_html, $link_start_pos, $link_end_pos - $link_start_pos);

    $external = '0';
    if ($link_url[0] == "/") {
      $link_url = "http://gov.uk" . $link_url;
    } else {
      $external = '1';
    }

    $link_text = substr($current_page_html, $text_start_pos, $text_end_pos - $text_start_pos);

    // Handle some weirdness in some link texts
    $link_text = preg_replace("/\n/", "", $link_text);
    $link_text = preg_replace("/[ ]{2,}/", " ", $link_text);
    if (($link_text == "") || ($link_text == " ")) {
      $link_text = "Failed to get link text";
    }

    // Set up for the next time round the link loop
    $link_start_pos = strpos($current_page_html, "<a href=\"", $text_end_pos);

    // Update number of links we've processed
    $link_counter++;
  
    // Check if linked-to is in urls. Add if not, update inbound_link_count if it is
    $sql_string = "SELECT id,inbound_link_count FROM urls WHERE url='" . $link_url . "'";
    $result = $mysqli->query($sql_string);
    if ($result->num_rows == 0) {
      $sql_string = "INSERT INTO urls (url,external,page_title,inbound_link_count,processed) VALUES ('" . $link_url . "','" . $external . "','','1','0')";
      $result = $mysqli->query($sql_string);
      $target_page_id = $mysqli->insert_id;
    } else {
      $row = $result->fetch_assoc();
      $target_page_id = $row['id'];
      $inbound_link_count = $row['inbound_link_count'];
      $inbound_link_count++;
      $sql_string = "UPDATE urls SET inbound_link_count='" . $inbound_link_count . "' WHERE id='" . $target_page_id . "'";
      $result = $mysqli->query($sql_string);
    }
  
    // Put the association into links
    $sql_string = "INSERT INTO links (from_id,to_id,link_text) VALUES ('" . $current_page_id . "','" . $target_page_id . "','" . $link_text . "')";
    $result = $mysqli->query($sql_string);
  }
  
  // Update outbound_link_count in urls and reset $link_counter for next time round the loop
  $sql_string = "UPDATE urls SET outbound_link_count='" . $link_counter . "' WHERE id='" . $current_page_id . "'";
  $result = $mysqli->query($sql_string);
  $link_counter = 0;

  // Get the first non-external URL in urls where last_run_id <> $current_run_id
  // If exsists set $current_page_url to it, else break
  $sql_string = "SELECT id, url FROM urls WHERE processed <> '1' AND external <> '1' LIMIT 1";
  $result = $mysqli->query($sql_string);
  if ($result->num_rows == 0) {
    break;
  } else {
    $row = $result->fetch_assoc();
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

// Log some stats
TestLog("Number of pages processed : " . $page_counter);
TestLog("Number of links processed : " . $link_counter);

TestLog("Completed");
?>
