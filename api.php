<?php

// This is designed to be able to handle external http calls to the API and to be used directly as a library

function gov_uk_data_api($action, $id ='', $url = '', $search_term = '', $limit = '', $distinct = '', $api_call = true) {

  // For database access
  $host = getenv("DB1_HOST");
  $user = getenv("DB1_USER");
  $pass = getenv("DB1_PASS");

  $mysqli = new mysqli($host, $user, $pass);
  $mysqli->select_db("gov_uk_data");

  $sql_string = "";
  if ($action == "getPageById") {
    $sql_string = "SELECT * FROM urls WHERE id='" . $id . "'";
  } elseif ($action == "getLinkById") {
    $sql_string = "SELECT * FROM links WHERE id='" . $id . "'";
  } elseif ($action == "getPageByUrl" {
    $sql_string = "SELECT * FROM urls WHERE url='" . $url . "'";
  } elseif ($action == "getLinksByFromId") {
    $sql_string = "SELECT " . $distinct . " * FROM links WHERE from_id='" . $id . "'";  
    if ($limit) {
      $sql_string .= " LIMIT " . $limit;
    }
  }
  } elseif ($action == "getLinksByToId") {
    $sql_string = "SELECT " . $distinct . " * FROM links WHERE from_id='" . $id . "'";  
    if ($limit) {
      $sql_string .= " LIMIT " . $limit;
    }
  }
  } elseif ($action == "searchPageTitles") {
    $sql_string = "SELECT " . $distinct . " * FROM urls WHERE page_title LIKE '%" . $search_term . "%'";  
    if ($limit) {
      $sql_string .= " LIMIT " . $limit;
    }
  } elseif ($action == "searchLinkText" {
    $sql_string = "SELECT " . $distinct . " * FROM links WHERE link_text LIKE '%" . $search_term . "%'";  
    if ($limit) {
      $sql_string .= " LIMIT " . $limit;
    }
  } else {
    if ($api_call) {
      print "{ \"error\": \"Unknown action\" }";
    } else {
      return ("error: Unknown action");
    }
  }

  $result = $mysqli->query($sql_string);
  $return_array = Array();
  while($row = $result->fetch_assoc()) {
    array_push($return_array, $row);
  }
  if ($api_call) {
    return $return_array;
  } else {
    print json_encode($return_array);
  }
  
}

// Main

// Handle both types of usage
if ($_GET['action']) {
  gov_uk_data_api($_GET['action'], $_GET['id'], $_GET['search_term'], $_GET['distinct'], $_GET['limit'], false);
}

?>