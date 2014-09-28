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
      <h2>GOV.UK Data : Main page</h2>
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
      <?php
        $location = $_GET['location'];
        $sql_string = "SELECT * FROM urls WHERE url='" . $location . "'";
        $result = $mysqli->query($sql_string);
        $row = $result->fetch_assoc();
        print "<div id=\"mini_title\">Page title : " . $row['page_title'] . "</div>";
      ?>
      <hr/>
      <div id="links_out">
        <div id="mini_title">Outbound links</div>
        <div id="links_out_table">
          <?php
            $links_sql_string = "SELECT DISTINCT * from links WHERE from_id='" . $row['id'] . "'";
            $links_result = $mysqli->query($links_sql_string);
            print "<table>";
            while($links_row = $links_result->fetch_assoc()) {
              $outbound_sql_string = "SELECT url FROM urls WHERE id='" . $links_row['to_id'] . "'";
              $outbound_result = $mysqli->query($outbound_sql_string);
              $outbound_row = $outbound_result->fetch_assoc();
              print "<tr><td><a href=\"index.php?location=" . $outbound_row['url'] . "\">" . $links_row['link_text'] . "</a></td><td>";
            }
            print "</table>";
          ?>
        </div>
      </div>
      <div id="links_in">
        <div id="mini_title">Inbound links</div>
        <div id="links_in_table">
          <?php
            $links_sql_string = "SELECT DISTINCT * from links WHERE to_id='" . $row['id'] . "'";
            $links_result = $mysqli->query($links_sql_string);
            print "<table>";
            while($links_row = $links_result->fetch_assoc()) {
              $inbound_sql_string = "SELECT url FROM urls WHERE id='" . $links_row['from_id'] . "'";
              $inbound_result = $mysqli->query($inbound_sql_string);
              $inbound_row = $inbound_result->fetch_assoc();
              print "<tr><td><a href=\"index.php?location=" . $inbound_row['url'] . "\">" . $links_row['link_text'] . "</a></td><td>";
            }
            print "</table>";
          ?>
        </div>
      </div>
    </div>
    <hr/>
    <div id="network">
      <div id="mini_title">Network of pages</div>
      Remember clustering if returned number of nodes is too big : http://goo.gl/nRF4MO
    </div>
  </body>
</html>
