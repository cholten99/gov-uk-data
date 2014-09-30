<html>

<?php
// For database access
$host = getenv("DB1_HOST");
$user = getenv("DB1_USER");
$pass = getenv("DB1_PASS");

$mysqli = new mysqli($host, $user, $pass);
$mysqli->select_db("gov_uk_data");

if ($mysqli->connect_errno) {
  print "Failed to connect to MySQL: " . $mysqli->connect_error;
}
?>

  <head>
    <title>GOV.UK Data</title>
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>
    <link href='http://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>
    <link href='my.css' rel='stylesheet' type='text/css'>

    <script>
    $(function() {
      href = window.location.href;
      location_start = href.indexOf("=") + 1;
      location_url = href.slice(location_start, href.length);
      if (location_url == "") {
        location_url = "http://gov.uk";
      }
      $("#jump_select option[value='" + location_url + "']").prop("selected", true);

      $("#jump_select").mouseup(function () {
        window.location.href = "index.php?location=" + $("#jump_select").val();
      });
    });
    </script>

  </head>
  <body>
    <div id="header">
      <h2>GOV.UK Data : Search results</h2>
      Jump to :
      <select id="jump_select">
        <option value="http://gov.uk">GOV.UK</option>
        <option value="https://www.gov.uk/browse/benefits">Benefits</option>
        <option value="https://www.gov.uk/browse/justice">Justice</option>
        <option value="https://www.gov.uk/browse/education">Education</option>
        <option value="https://www.gov.uk/browse/environment-countryside">Environment</option>
        <option value="https://www.gov.uk/browse/visas-immigration">Visas</option>
      </select>
      | <a href="stats.php">Statistics</a>
      | Search : <form action="search.php" method="get"><input name="search_input" id="search_input"></form>
      <hr/>
    </div>
    <div id="tables">
      <div id="page_title_search_results">
      <h4>Page title results</h4>
      <?php
        $search_term = $_GET['search_input'];
        $sql_string = "SELECT * FROM urls WHERE page_title LIKE '%" . $search_term . "%' ORDER BY inbound_link_count DESC LIMIT 25";
        $result = $mysqli->query($sql_string);
        if ($result->num_rows == 0) {
          print "No page titles found with that search term.";
        } else {
          print "<table>";
          while($row = $result->fetch_assoc()) {
            if ($row['external'] == "1") {
              print "<tr><td><a href=\"" . $row['id'] . "\">External page</a></td></tr>";
            } else {
              print "<tr><td><a href=\"index.php?id=" . $row['id'] . "\">" . $row['page_title'] . "</a></td></tr>";
            }
          }
          print "</table>";
        }
      ?>
      </div>
      <div id="link_text_search_results">
      <h4>Link text results</h4>
      <?php
        $search_term = $_GET['search_input'];
        $sql_string = "SELECT DISTINCT * FROM links WHERE link_text LIKE '%" . $search_term . "%' LIMIT 25";
        $result = $mysqli->query($sql_string);
        if ($result->num_rows == 0) {
          print "No link texts found with that search term.";
        } else {
          print "<table>";
          while($row = $result->fetch_assoc()) {
            $url_id = $row['from_id'];
            $sql_string = "SELECT * FROM urls WHERE id='" . $url_id . "'";
            $url_result = $mysqli->query($sql_string);
            $url_row = $url_result->fetch_assoc();
            if ($url_row['external'] == "1") {
              print "<tr><td><a href=\"" . $url_row['id'] . "\">External link</a></td></tr>";
            } else {
              print "<tr><td><a href=\"index.php?id=" . $url_row['id'] . "\">" . $row['link_text'] . "</a></td></tr>";
            }
          }
          print "</table";
	}
      ?>
      </div>

    </div>
  </body>
</html>



