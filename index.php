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

// Okay, this is a hack to handle the select
$url = $_GET['url'];
if ($url) {
  $sql_string = "SELECT id FROM urls WHERE url='" . $url . "'";
  $result = $mysqli->query($sql_string);
  $row = $result->fetch_assoc();
  header("Location: index.php?id=" . $row['id']);
}

// Get the ID and info of the target page
$current_page_id = $_GET['id'];
if (!($current_page_id)) {
  $current_page_id = 1;
}
$sql_string = "SELECT * FROM urls WHERE id='" . $current_page_id . "'";
$result = $mysqli->query($sql_string);
$row = $result->fetch_assoc();
$current_page_url = $row['url'];
$current_page_title = $row['page_title'];
?>

  <head>
    <title>GOV.UK Data</title>

    <!-- jQuery -->
    <script src="//ajax.googleapis.com/ajax/libs/jquery/2.1.1/jquery.min.js"></script>

    <!-- Google fonts -->
    <link href='http://fonts.googleapis.com/css?family=Lato' rel='stylesheet' type='text/css'>

    <!-- Vis DOT mapper -->
    <script type="text/javascript" src="vis.js"></script>
    <link href="vis.css" rel="stylesheet" type="text/css" />

    <!-- My CSS -->
    <link href='my.css' rel='stylesheet' type='text/css'>

    <script>
    $(function() {
      // Set select to match page being examined if it's in the list
      location_url = "<?php print $current_page_url; ?>";
      $("#jump_select option[value='" + location_url + "']").prop("selected", true);

      // Select listener
      $("#jump_select").change(function () {
        window.location.assign("index.php?url=" + $("#jump_select").val());	
      });

      // Draw the network
      $.post("network.php", { url: location_url }, function(data_json, status) {
        data = JSON.parse(data_json);
        try {
          var options = {
            height:"400px",
          };
          theCanvas = $("#map")[0];
          network = new vis.Network(theCanvas, data, options);
          network.redraw();

          // Network node listener
          network.on('doubleClick', function(properties) {
            window.location.assign("index.php?id=" + properties.nodes)
          });

        }
        catch (err) {
          console.log(err.toString());
        }
      });

    });
    </script>

  </head>
  <body>
    <div id="header">
      <h2>GOV.UK Data : Main page</h2>
      Jump to :
      <select id="jump_select">
        <option value="https://www.gov.uk">GOV.UK</option>
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
        print "<div id=\"mini_title\">Page title : " . $current_page_title . "</div>";
      ?>
      <hr/>
      <div id="links_out">
        <div id="links_out_table">
          <?php
            $links_sql_string = "SELECT DISTINCT * from links WHERE from_id='" . $current_page_id . "'";
            $links_result = $mysqli->query($links_sql_string);
            print "<table><tr><th>Outbound links</th></tr>";
            while($links_row = $links_result->fetch_assoc()) {
              $outbound_sql_string = "SELECT * FROM urls WHERE id='" . $links_row['to_id'] . "'";
              $outbound_result = $mysqli->query($outbound_sql_string);
              $outbound_row = $outbound_result->fetch_assoc();
              print "<tr><td><a href=\"index.php?id=" . $outbound_row['id'] . "\">" . $links_row['link_text'] . "</a></td><td>";
            }
            print "</table>";
          ?>
        </div>
      </div>
      <div id="links_in">
        <div id="links_in_table">
          <?php
            $links_sql_string = "SELECT DISTINCT * from links WHERE to_id='" . $current_page_id . "'";
            $links_result = $mysqli->query($links_sql_string);
            print "<table><tr><th>Inbound links</th></tr>";
            while($links_row = $links_result->fetch_assoc()) {
              $inbound_sql_string = "SELECT * FROM urls WHERE id='" . $links_row['from_id'] . "'";
              $inbound_result = $mysqli->query($inbound_sql_string);
              $inbound_row = $inbound_result->fetch_assoc();
              print "<tr><td><a href=\"index.php?id=" . $inbound_row['id'] . "\">" . $links_row['link_text'] . "</a></td><td>";
            }
            print "</table>";
          ?>
        </div>
      </div>
    </div>
    <hr/>
    <div id="map_area">
      <div id="mini_title">Network of pages</div>
      <div id="map"></div>
      <hr/>
    </div>
  </body>
</html>
