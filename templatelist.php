<?php

/*
Put this script into /usr/local/solusvm/www/, change USERNAME, PASSWORD, DATABASE
*/

$link = mysqli_connect("localhost", "USERNAME", "PASSWORD", "DATABASE") or die("Error while connecting to MySQL database");
$query = "SELECT * FROM templates ORDER BY friendlyname;";
$result = $link->query($query);

$archhuman = array(
    "x86_64" => "x86-64 (64-bit)",
    "x86"    => "x86 (32-bit)",
    "i386"   => "x86 (32-bit)",
    "i486"   => "x86 (32-bit)",
    "i586"   => "x86 (32-bit)",
    "i686"   => "x86 (32-bit)",
    "amd64"  => "x86-64 (64-bit)",
    "x64"    => "x86-64 (64-bit)"
);
    

$distlist = array();
while ($row = mysqli_fetch_array($result)) {
//    var_dump($row);
    //echo $row["friendlyname"], "\n";
    $exitcode = preg_match("/^([^0-9]+) ([0-9\.]+) (x86(_64)?|i.86|amd64|x64)( (devel|minimal)|)$/", $row["friendlyname"], $matches);
    if ($exitcode === 1) {
        $fileinfo = array(
            "dist" => $matches[1],
            "vers" => $matches[2],
            "arch" => $matches[3],
            "flag" => $matches[6]
        );
        if ($fileinfo["flag"] === NULL) {
            $fileinfo["flag"] = "standard";
        }
        //echo implode(" ", $fileinfo), "\n";
        if (!is_array($distlist[$fileinfo["dist"]])) {
            $distlist[$fileinfo["dist"]] = array();
            $distlist[$fileinfo["dist"]]["verslist"] = array();
            //echo "Created array: ", $fileinfo["dist"], "\n";
        }
        if (!is_array($distlist[$fileinfo["dist"]]["verslist"][$fileinfo["vers"]])) {
            $distlist[$fileinfo["dist"]]["verslist"][$fileinfo["vers"]] = array();
            $distlist[$fileinfo["dist"]]["verslist"][$fileinfo["vers"]]["archlist"] = array();
            $distlist[$fileinfo["dist"]]["verslist"][$fileinfo["vers"]]["flaglist"] = array();
            //echo "Created array: ", $fileinfo["dist"], " ", $fileinfo["vers"], "\n";
        }
        if (!is_array($distlist[$fileinfo["dist"]]["verslist"][$fileinfo["vers"]]["archlist"][$fileinfo["arch"]])) {
            $distlist[$fileinfo["dist"]]["verslist"][$fileinfo["vers"]]["archlist"][$fileinfo["arch"]] = array();
            $distlist[$fileinfo["dist"]]["verslist"][$fileinfo["vers"]]["archlist"][$fileinfo["arch"]]["flaglist"] = array();
            //echo "Created array: ", $fileinfo["dist"], " ", $fileinfo["vers"], " ", $fileinfo["arch"], "\n";
        }
        //echo "Adding flag: ", $fileinfo["dist"], " ", $fileinfo["vers"], " ", $fileinfo["arch"], " ", $fileinfo["flag"], "\n";
        $distlist[$fileinfo["dist"]]["verslist"][$fileinfo["vers"]]["archlist"][$fileinfo["arch"]]["flaglist"][] = $fileinfo["flag"];
        if (!in_array($fileinfo["flag"], $distlist[$fileinfo["dist"]]["verslist"][$fileinfo["vers"]]["flaglist"])) {
            $distlist[$fileinfo["dist"]]["verslist"][$fileinfo["vers"]]["flaglist"][] = $fileinfo["flag"];
        }
    } else {
        $unknown[] = $row["friendlyname"];
    }
}
?>
<!DOCTYPE html>
<html lang="en">
    <head>
        <meta charset="utf-8">
        <meta http-equiv="X-UA-Compatible" content="IE=edge">
        <meta name="viewport" content="width=device-width, initial-scale=1">
        <title>Available OpenVZ templates</title>
        <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap.min.css">
        <link rel="stylesheet" href="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/css/bootstrap-theme.min.css">
        <!--[if lt IE 9]>
            <script src="https://oss.maxcdn.com/libs/html5shiv/3.7.0/html5shiv.js"></script>
            <script src="https://oss.maxcdn.com/libs/respond.js/1.4.2/respond.min.js"></script>
        <![endif]-->
        <style type="text/css">
            * {
                font-family: "Helvetica Neue", Helvetica, Arial, sans-serif;
            }
        </style>
    </head>
    <body>
        <div class="container">
            <div class="page-header">
                <h1>List of available OpenVZ container templates</h1>
            </div>
            <div class="row">
<?php
foreach($distlist as $dist => $distarray) {
    echo "<div class=\"col-lg-4 col-sm-6\"><div class=\"panel panel-default\"><div class=\"panel-heading\">", $dist, "</div><div class=\"panel-body\"><div class=\"table-responsive\"><table class=\"table table-condensed table-hover table-striped sortable\"><thead><tr><th>Version</th><th>Architecture</th><th>Variant</th></tr></thead><tbody>";
    foreach ($distarray["verslist"] as $vers => $versarray) {
        foreach ($versarray["archlist"] as $arch => $archarray) {
            foreach ($archarray["flaglist"] as $flag) {
                echo "<tr><td>", $dist, " ", $vers, "</td><td>", (!empty($archhuman[$arch])?$archhuman[$arch]:$arch), "</td><td", ($flag === "standard" ? " style=\"color: green;\"" : ($flag === "minimal" ? " style=\"color: blue;\"" : ($flag === "devel" ? " style=\"color: red;\"" : ""))), ">", $flag, "</td></tr>";
            }
        }
    }
    echo "</tbody></table></div></div></div></div>";
}
?>
            </div>
            <div class="footer">
                <p style="float: left;"><a href="/login.php">SolusVM Control Panel</a></p>
                <p style="float: right;">&copy; <?php echo date('Y'); ?> <a href="http://example.com/">Your Company Name</a></p>
                <div style="clear: both;"></div>
            </div>
        </div>
        <script src="https://ajax.googleapis.com/ajax/libs/jquery/1.11.0/jquery.min.js"></script>
        <script src="https://netdna.bootstrapcdn.com/bootstrap/3.1.1/js/bootstrap.min.js"></script>
    </body>
</html>


<?php
/*
foreach($distlist as $dist => $distarray) {
    echo "<div style=\"float: left; margin-left: 25px; margin-right: 25px;\"><h2>", $dist, "</h2>";
    foreach ($distarray["verslist"] as $vers => $versarray) {
        echo "<h3>", $dist, " ", $vers, "</h3>Variants: <ul style=\"margin-top: 0;\"><li>", implode("</li><li>", $versarray["flaglist"]), "</li></ul>Architectures:<ul style=\"margin-top: 0;\">";
        foreach ($versarray["archlist"] as $arch => $archarray) {
            echo "<li>", (!empty($archhuman[$arch])?$archhuman[$arch]:$arch), "</li>";
        }
        echo "</ul>";
    }
    echo "</div>";
}
echo "<div style=\"clear: left;\"></div>";
*/
?>
