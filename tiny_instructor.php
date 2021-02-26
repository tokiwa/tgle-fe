<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LTI1.3 tiny example tool</title>
    <!--    Bootstrap begin-->
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
            integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
            crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
            integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
            crossorigin="anonymous"></script>
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
            integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
            crossorigin="anonymous"></script>
    <!--    Bootstrap end-->
</head>

<body>
<div class="container">
    <div class="jumbotron">
        <h1 class="text-center">Tiny LTI1.3 Example</h1>
        <p class="text-center">for Instructor</p>
    </div>

    <?php
    require_once __DIR__ . '/vendor/autoload.php';
    require_once __DIR__ . '/db/example_database.php';

    use \IMSGlobal\LTI;
    $launch = LTI\LTI_Message_Launch::from_cache($_REQUEST['launch_id'], new Example_Database());
    ?>
    <div class="alert alert-info" role="alert">Data by LTI 1.3 Core</div>
    roles: <?= explode('#',$launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/roles'][0])[1]; ?><br>
    sub(=user_id): <?= $launch->get_launch_data()['sub']; ?><br>
    name: <?= $launch->get_launch_data()['name']; ?><br>
    email: <?= $launch->get_launch_data()['email']; ?><br>
    version: <?= $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/version']; ?><br>
    context/id: <?= $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/context']['id']; ?><br>
    context/title: <?= $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/context']['title']; ?><br>
    context/label: <?= $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/context']['label']; ?><br>
    <hr>
    <div class="alert alert-info" role="alert">Data by LTI 1.3 Core - Moodle extension</div>
    loginid: <?= $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/ext']['user_username']; ?><br>
    lms: <?= $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/ext']['lms']; ?><br>
    <hr>
    <div class="alert alert-info" role="alert">Roster by LTI Advantage Name Role Provisioning Service / [user_id/roles/name/email]</div>

    <?php
    //NRPS
    $members = $launch->get_nrps()->get_members();
    foreach ($members as $member) {
        echo $member['user_id'] . " / " . $member['roles'][0] . " / " . $member['name'] . " / " . $member['email'] . "<br>";
    }
    ?>

</div>
</body>
</html>