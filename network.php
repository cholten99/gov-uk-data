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

$data = "";
$nodes = "{\"id\": 1, \"title\": \"GOV.UK\", \"border.color\": \"#97C2FC\"},";
$edges = "";
$sql_string = "SELECT * FROM links WHERE from_id='1' LIMIT 21";
$result = $mysqli->query($sql_string);
while($row = $result->fetch_assoc()) {
  $url_sql_string = "SELECT * FROM urls WHERE id='" . $row['to_id'] . "'";
  $url_result = $mysqli->query($url_sql_string);
  $url_row = $url_result->fetch_assoc();
  $node_colour = "#97C2FC";
  if ($url_row['page_type'] == "normal") {
    $node_colour = "#FFA2A2";
  }

  $nodes .= "{\"id\": " . $url_row['id'] . ", \"title\": \"" . $url_row['page_title'] . "\", \"border.color\": \"" . $node_colour ."\"},";
  $edges .= "{\"from\": 1, \"to\": " . $row['to_id'] . ",\"title\": \"" . $row['link_text'] . "\"},";
}
$nodes = rtrim($nodes, ",");
$edges = rtrim($edges, ",");

$data = "{ \"nodes\": [ " . $nodes . " ], \"edges\": [ " . $edges . " ] }";

print $data;

?>
