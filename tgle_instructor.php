<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>LTI1.3 tiny example tool</title>
    <!--    Bootstrap begin-->

    <script src="https://code.jquery.com/jquery-3.3.1.slim.min.js"
            integrity="sha384-q8i/X+965DzO0rT7abK41JStQIAqVgRVzpbzo5smXKp4YfRvH+8abtTE1Pi6jizo"
            crossorigin="anonymous"></script>
    <script src="https://cdnjs.cloudflare.com/ajax/libs/popper.js/1.14.7/umd/popper.min.js"
            integrity="sha384-UO2eT0CpHqdSJQ6hJty5KVphtPhzWj9WO1clHTMGa3JDZwrnQq4sF86dIHNDz0W1"
            crossorigin="anonymous"></script>
    <link rel="stylesheet" href="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/css/bootstrap.min.css"
          integrity="sha384-ggOyR0iXCbMQv3Xipma34MD+dH/1fQ784/j6cY/iJTQUOhcWr7x9JvoRxT2MZw1T" crossorigin="anonymous">
    <script src="https://stackpath.bootstrapcdn.com/bootstrap/4.3.1/js/bootstrap.min.js"
            integrity="sha384-JjSmVgyd0p3pXB1rRibZUAYoIIy6OrQ6VrjIEaFf/nJGzIxFDsf4x0xIM+B07jRM"
            crossorigin="anonymous"></script>
    <!--    Bootstrap end-->

    <script src="https://unpkg.com/vuejs-datepicker"></script>
</head>

<body>

<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/db/example_database.php';

use \IMSGlobal\LTI;
$launch = LTI\LTI_Message_Launch::from_cache($_REQUEST['launch_id'], new Example_Database());

$members = $launch->get_nrps()->get_members();

$json = json_encode($members);
?>

<div class="container">
    <button type="button" class="btn btn-secondary btn-lg btn-block">TGLE: Tools for Group Learning Environment for Instructor</button>

<div id="app">
    name: {{name}} <br>
    context: {{context}} <br>
    loginid: {{loginid}} <br>

    Learner members retrieved by NRPS:
        <div v-for="(member, index) in members" v-bind:key="index">
            <span v-if="member.roles[0] == 'Learner'" > {{index}}: {{member.roles[0]}} {{ member.family_name }} {{member.given_name}} <br> </span>
        </div>

    授業日：
    <vuejs-datepicker
            :value="this.default"
            :format="DatePickerFormat"></vuejs-datepicker>
</div>

</div>

<!--<script src="https://jp.vuejs.org/js/vue.js"></script>-->
<script src="https://cdn.jsdelivr.net/npm/vue@2/dist/vue.js"></script>
<!--<script src="main.js"></script>-->
<script>
    Vue.config.devtools = true;
    var app = new Vue({
        el: '#app',
        data: {
            name: 'Hello Vue from tiny_instructor.php!',
            context:'',
            loginid:'',
            members:[],
            default: '2021-04-01',
            DatePickerFormat: 'yyyy-MM-dd'
          },
        components: {
            'vuejs-datepicker':vuejsDatepicker
        }
    });

    app.name = "<?= $launch->get_launch_data()['name']; ?>";
    app.context = "<?= $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/context']['title']; ?>";
    app.loginid = "<?= $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/ext']['user_username']; ?>";
    app.members = <?= $json; ?>;   //ダブルクオート不要
</script>

</body>
</html>