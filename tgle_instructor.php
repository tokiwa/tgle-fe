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

    <style>
        h1 {
            font-size: 120%;
            color: #000000;
            font-weight: bold;
            margin-top: 1em;
        }
    </style>
</head>

<body>

<?php
require_once __DIR__ . '/vendor/autoload.php';
require_once __DIR__ . '/db/example_database.php';

use \IMSGlobal\LTI;

$role = "instructor";
$launch = LTI\LTI_Message_Launch::from_cache($_REQUEST['launch_id'], new Example_Database());

$members = $launch->get_nrps()->get_members();
$json = json_encode($members);

$course_id = $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/context']['label'];
$user_id = $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/ext']['user_username'];

// tgle3-43
//echo "<div class='text-center'>Course ID: " . $course_id . "(暫定表示)</div>";
//echo "<div class='text-center'>User ID: " . $user_id . "</div>";
//echo "<div class='text-center'>Role: " . $role . "(暫定表示)</div>";
/*label: "<?= $course_id ;?>",　<= Vueの中ではこのようにしてLTIで獲得した変数を参照できる。*/
?>

<button type="button" class="btn btn-secondary btn-lg btn-block">TGLE: Tools for Group Learning Environment for
    Instructor
</button>

<div id="app">
    <h1>レッスン登録</h1>
    新規レッスンを登録します。 (例：グループ学習 yyyy-mm-dd) <br>
    <input v-model="lessontitle" type="text">
    <button type="button" @click="submitTitle">登録</button>
    <br>
    <div v-text="lessontitle_cb"></div>

    <h1>登録済レッスン一覧</h1>
    レッスンを選択してください。<br>
    <div v-for='(lesson,index) in lessons'>
        <input type="radio" id="index" :value="lesson.id" v-model="radioSelect">
        <label :for="index"> {{lesson.lessontitle}}</label>
    </div>
<!--    <div>レッスンを選択してください。Select : {{radioSelect}}</div>-->

    <h1>受講生キーワード確認</h1>
    受講生が投稿したキーワードを確認します。<br>
    <button type="button" @click="getLearnerKeyword(radioSelect)">受講生キーワード一覧</button>
    <div v-for='learner_keyword in learner_keywords'>
        {{learner_keyword.user}}: {{learner_keyword.keyword}}
    </div>

    <h1>教員キーワード確認</h1>
    教員が投稿したキーワードを確認します。<br>
    <button type="button" @click="getInstructorKeyword(radioSelect)">教員キーワード一覧</button>
    <div v-for='instructor_keyword in instructor_keywords'>
        {{instructor_keyword.keyword}}
    </div>

    <h1>教員キーワード入力</h1>
    追加をクリックしてkeywordを入力してください。（最大5件。あと<span v-text="remainingTextCount"></span>件入力できます。）<br>
    <div v-for="(text,index) in keyword">
        <input ref="keyword" type="text" v-model="keyword[index]" @keypress.shift.enter="addInput">
        <button type="button" @click="removeInput(index)">削除</button>
    </div>
    <button type="button" @click="addInput" v-if="!isTextMax">追加</button><br>
    すべてのkeywordを入力したら送信をクリックしてください。<br>
    <button type="button" @click="onSubmit" v-if="isTextMin">送信</button>

<!--    <br>次のKeywordが登録されました。</br>-->
<!--    <div v-text="keyword_cb"></div>-->

    <h1>グループ形成</h1>
    教員および受講生のキーワードを確認後、グループを形成します。<br>
    <button type="button" @click="mkGroup(radioSelect)">グループ形成</button>

    <h1>グループ確認</h1>
    形成されたグループを確認します。<br>
    <button type="button" @click="getGroup(radioSelect)">グループ構成</button>
    <div v-for='learner_group in learner_groups'>
        {{learner_group.user}}: {{learner_group.group}}
    </div>



</div>

<script src="https://cdn.jsdelivr.net/npm/vue@2.6.11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>

    new Vue({
        el: '#app',
        data: {
            //keyword
            radioSelect: "",
            keyword: [], // 複数入力のデータ（配列）
            maxTextCount: 5, // 👈 追加
            keyword_cb: [],
            //lesson
            lessontitle: "",
            lessontitle_cb: "",
            lessons_cb: [],
            label: "",
            academicyear: 0,
            lessons: [],
            learner_keywords: [],
            instructor_keywords: [],
            learner_groups:[],
            check0: 'check write'
        },
        mounted() {
            this.getTitle();
        },
        methods: {
            addInput() {
                if (this.isTextMax) { // 最大件数に達している場合は何もしない
                    return;
                }
                this.keyword.push(''); // 配列に１つ空データを追加する

                // 👇 追加された入力ボックスにフォーカスする
                Vue.nextTick(() => {
                    const maxIndex = this.keyword.length - 1;
                    this.$refs['keyword'][maxIndex].focus();
                });
            },
            removeInput(index) {
                this.keyword.splice(index, 1); // 👈 該当するデータを削除
            },
            onSubmit() {
                const params = {
                    keyword: this.keyword,
                    "course": "<?= $course_id;?>",
                    "userid": "<?= $user_id;?>",
                    // "lessonid":1
                    "lessonid": this.radioSelect,
                    "role": 'instructor',
                    "status": 'active'
                };
                axios.post('http://localhost:8000/api/postkeyword', params)
                    .then(response => this.keyword_cb = response.data['keyword'])
                    .catch(error => console.log(error))
            },
            //Lesson 作成および登録済確認
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
                // window.onload = ()=>{
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

            getLearnerKeyword(id) {
                const params_get = {
                    lessonid: id,
                    "role": 'learner'
                };
                axios.get('http://localhost:8000/api/getkeyword', {params: params_get})
                    .then(response => this.learner_keywords = response.data)
                    .catch(error => console.log(error))
            },

            getInstructorKeyword(id) {
                const params_get = {
                    lessonid: id,
                    "role": 'instructor'
                };
                axios.get('http://localhost:8000/api/getkeyword', {params: params_get})
                    .then(response => this.instructor_keywords = response.data)
                    .catch(error => console.log(error))
            },

            getGroup(id) {
                const params_get = {
                    lessonid: id,
                    user_id: "<?= $user_id;?>",
                    role: 'instructor'
                };
                axios.get('http://localhost:8000/api/getgroup', {params: params_get})
                    .then(response => this.learner_groups = response.data)
                    .catch(error => console.log(error))
            },

            mkGroup(id) {
                const params_get = {
                    lessonid: id
                };
                axios.get('http://localhost:8000/api/mkgroup', {params: params_get})
                    .then(response => this.group_settings = response.data)
                    .catch(error => console.log(error))
            }

        },
        computed: {
            isTextMin() {
                return (this.keyword.length >= 1);
            },
            isTextMax() {
                return (this.keyword.length >= this.maxTextCount);
            },
            remainingTextCount() {
                return this.maxTextCount - this.keyword.length; // 追加できる残り件数
            }
        }
    });

</script>

</body>
</html>