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
      <h2>GOV.UK Data : Statistics</h2>
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
      <div id="outbound_link_count_area">
        <h4>Highest number of links on page</h4>
        <?php
          $sql_string = "SELECT * FROM urls WHERE external <> '1' ORDER BY outbound_link_count DESC LIMIT 25";
          $result = $mysqli->query($sql_string);
          print "<table>";
          while($row = $result->fetch_assoc()) {
            print "<tr><td><a href=\"index.php?location=" . $row['url'] . "\">" . $row['page_title'] . "</a></td><td>" . $row['outbound_link_count'] . "</td>";
          }
          print "</table>";
        ?>
      </div>
      <div id="inbound_link_count_area">
        <h4>Highest number of links to page</h4>
        <?php
          $sql_string = "SELECT * FROM urls WHERE external <> '1' ORDER BY inbound_link_count DESC LIMIT 25";
          $result = $mysqli->query($sql_string);
          print "<table>";
          while($row = $result->fetch_assoc()) {
            print "<tr><td><a href=\"index.php?location=" . $row['url'] . "\">" . $row['page_title'] . "</a></td><td>" . $row['inbound_link_count'] . "</td>";
          }
          print "</table>";
        ?>
      </div>
    </div>
  </body>
</html>
