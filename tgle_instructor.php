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

$course_id = $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/context']['label'];
echo "<div class='text-center'>Course ID: " . $course_id . "(暫定的に表示)</div>";
/*label: "<?= $course_id ;?>",　<= Vueの中ではこのようにしてLTIで獲得した変数を参照できる。*/

?>

<button type="button" class="btn btn-secondary btn-lg btn-block">TGLE: Tools for Group Learning Environment for
    Instructor
</button>

<div id="app">

    <div id="app">
        グループ学習タイトル登録<br>
        <input v-model="lessontitle" type="text">
        <button type="button" @click="submitTitle">登録</button>
        <br>
        <div v-text="lessontitle_cb"></div>
        <p></p>
        <button type="button" @click="getTitle">登録済レッスン一覧表示</button>
        <div v-for='item in lessons'>
            <!--        {{item.id}}:{{item.lessontitle}} <button type="button" @click="getKeyword(item.id)">Detail</button>-->
            {{item.lessontitle}}
            <button type="button" @click="getKeyword(item.id)">Detail</button>
        </div>
        <hr>
        <label>Detail表示コンソール</label>
        <div v-for='keyword in keywords'>
            {{keyword.user}}{{keyword.keyword}}
        </div>
    </div>
</div>

<script src="https://cdn.jsdelivr.net/npm/vue@2.6.11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>

    new Vue({
        el: '#app',
        data: {
            lessontitle: "",
            lessontitle_cb: "",
            lessons_cb: [],
            label: "",
            academicyear: 0,
            lessons: [],
            keywords: [],
            check0: 'check write',
        },
        methods: {
            submitTitle() {
                var date = new Date();
                date.setMonth(date.getMonth() - 3);
                this.academicyear = date.getFullYear();
                //console.log(this.academicyear);
                const params = {
                    lessontitle: this.lessontitle,
                    // label: 'u3003',
                    label: "<?= $course_id;?>",
                    academicyear: this.academicyear
                };
                axios.post('http://localhost:8000/api/postlesson', params)
                    // .then(response => this.title_cb = response.data['title'])
                    .then(response => {
                        this.lessontitle_cb = "正常に登録されました。";
                        console.log('status:', response.status);
                    })
                    .catch(error => console.log(error))
            },

            getTitle() {
                var date = new Date();
                date.setMonth(date.getMonth() - 3);
                this.academicyear = date.getFullYear();

                const params_get = {
                    // label:'u3003',
                    label: "<?= $course_id;?>",
                    academicyear: this.academicyear,
                    status: 'active'
                };
                axios.get('http://localhost:8000/api/getlesson', {params: params_get})
                    // axios.get('http://localhost:8000/api/getlesson')
                    .then(response => this.lessons = response.data)
                    .catch(error => console.log(error))
            },

            getKeyword(id) {
                const params_get = {
                    lessonid: id
                };
                axios.get('http://localhost:8000/api/getkeyword', {params: params_get})
                    .then(response => this.keywords = response.data)
                    .catch(error => console.log(error))
            }


        }
    });

</script>

</body>
</html>