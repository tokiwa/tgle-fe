<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TGLE</title>
    <!--    Table Sorter begin-->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script type="text/javascript" src="https://ajax.googleapis.com/ajax/libs/jquery/1.12.4/jquery.min.js"></script>
    <script type="text/javascript"
            src="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.0/js/jquery.tablesorter.min.js"></script>
    <link rel="stylesheet"
          href="https://cdnjs.cloudflare.com/ajax/libs/jquery.tablesorter/2.31.0/css/theme.default.min.css">
    <!--    Table Sorter end-->
    <style>
        #fav-table th {
            background-color: lightyellow;
        }
    </style>
    <script>
        $(document).ready(function () {
            $('#fav-table').tablesorter();
        });
    </script>
</head>

<body>
<div class="container">
    <div class="jumbotron">
        <h1 class="text-center">TGLE</h1>
        <p class="text-center">Tools for Group Learning Environment</p>
        <p class="text-center">for Instructor</p>
    </div>

    <?php
    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/db/example_database.php';

    use \IMSGlobal\LTI;

    $launch = LTI\LTI_Message_Launch::from_cache($_REQUEST['launch_id'], new Example_Database());

    $user_id = $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/ext']['user_username'];
    $course_id = $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/context']['label'];

    echo "<div class='text-center'>" . $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/context']['title'] . "</div>";
    echo "<div class='text-center'>Course ID: " . $course_id . "</div>";
    echo "<div class='text-center'>User ID: " . $user_id . "</div>";
    echo "<div class='text-center'>Name: " . $launch->get_launch_data()['name'] . "</div>";
    echo "<div class='text-center'>Mail: " . $launch->get_launch_data()['email'] . "</div>";

    // Database search for latest group and seat position
    $mysqli = new mysqli('localhost', 'tgleuser', 'tglepass', 'tgle');

    if ($mysqli->connect_error) {
        die("connect_error - " . $mysqli->connect_error);
    } else {
        $mysqli->set_charset("utf8");
        //suppose lesson_id = 1
        $sql = 'select user_id,seat,grp from seats where lesson_id = "1" AND course_id = "' . $course_id . '" order by updated_at desc limit 100 ';
        $result = $mysqli->query($sql) or die("*tgle error* " . $sql);
        while ($row = $result->fetch_array(MYSQLI_ASSOC)) {
            $rows[] = $row;
        }
        // Release Database
        $result->free();
    }
    $mysqli->close();

    //NRPS
    $members = $launch->get_nrps()->get_members();
    ?>
    <hr>
    <div>座席位置およびグループ</div>
    <table id="fav-table" class="table table-bordered">
        <thead>
        <tr>
            <th>Moodle id</th>
            <th>姓</th>
            <th>名</th>
            <th>Mail</th>
            <th>座席</th>
            <th>Group</th>
        </tr>
        </thead>
        <?php
        foreach ($members as $member) {
            foreach ($rows as $row) {
                if ($row['user_id'] === $member['user_id']) {
                    echo "<tr><td>" . $member['user_id'] . "</td><td>" . $member['family_name'] . "</td><td>" . $member['given_name'] . "</td><td>" . $member['email'] . "</td><td>" . $row['seat'] . "</td><td>" . $row['grp'] . "</td><tr>";
                }
            }
        }
        ?>
    </table>
</div>

</body>
</html>