<!DOCTYPE html>
<html lang="ja">
<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title>TGLE</title>
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

$launch = LTI\LTI_Message_Launch::from_cache($_REQUEST['launch_id'], new Example_Database());

$user_id = $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/ext']['user_username'];
$course_id = $launch->get_launch_data()['https://purl.imsglobal.org/spec/lti/claim/context']['label'];

echo "<div class='text-center'>Course ID: " . $course_id . "(暫定表示)</div>";
echo "<div class='text-center'>User ID: " . $user_id . "(暫定表示)</div>";
//echo "<div class='text-center'>Name: " . $launch->get_launch_data()['name'] . "</div>";
//echo "<div class='text-center'>Mail: " . $launch->get_launch_data()['email'] . "</div>";
?>

<button type="button" class="btn btn-secondary btn-lg btn-block">TGLE: Tools for Group Learning Environment for
    Learner
</button>

<div id="app">
    <h1>登録済レッスン一覧</h1>
    <div v-for='(lesson,index) in lessons'>
        <input type="radio" id="index" :value="lesson.id" v-model="radioSelect">
        <label :for="index"> {{lesson.lessontitle}}</label>
    </div>
    <!--    </form>-->
    <div>はじめにキーワードを入力したいレッスンをラジオボタンで選択してください。</div>
    <div>Select : {{radioSelect}}</div>
    <hr>
    <!--    参考　https://blog.capilano-fw.com/?p=7431-->
    追加をクリックしてkeywordを入力してください。（最大5件。あと<span v-text="remainingTextCount"></span>件入力できます。）<br>
    <div v-for="(text,index) in keyword">
        <input ref="keyword" type="text" v-model="keyword[index]" @keypress.shift.enter="addInput">
        <button type="button" @click="removeInput(index)">削除</button>
    </div>
    <button type="button" @click="addInput" v-if="!isTextMax">追加</button>

    <br><br>
    すべてのkeywordを入力したら送信をクリックしてください。<br>
    <button type="button" @click="onSubmit" v-if="isTextMin">送信</button>

    <br>次のKeywordが登録されました。</br>
    <div v-text="keyword_cb"></div>

    <h1>グループ確認</h1>
    <button type="button" @click="getGroup(radioSelect)">Show Learners' Group</button>
    <div v-for='learner_group in learner_groups' :key = 'learner_group'>
        {{learner_group.user}}{{learner_group.group}}
    </div>
<!--    <div v-for='learner_group in learner_groups'>-->
<!--        {{learner_group.user}}{{learner_group.group}}-->
<!--    </div>-->

</div>
<script src="https://cdn.jsdelivr.net/npm/vue@2.6.11"></script>
<script src="https://cdnjs.cloudflare.com/ajax/libs/axios/0.19.2/axios.min.js"></script>

<script>
    new Vue({
        el: '#app',
        data: {
            radioSelect: "",
            label: "",
            status: "",
            academicyear: 0,
            lessons: [],

            keyword: [], // 複数入力のデータ（配列）
            maxTextCount: 5, // 👈 追加
            keyword_cb: [],

            learner_groups:[],
        },
        mounted() {
            this.getTitle();
        },
        methods: {
            getTitle() {
                var date = new Date();
                date.setMonth(date.getMonth() - 3);
                this.academicyear = date.getFullYear();

                const params_get = {
                    label: 'u3003',
                    academicyear: this.academicyear,
                    status: 'active'
                };
                axios.get('http://localhost:8000/api/getlesson', {params: params_get})
                    .then(response => this.lessons = response.data)
                    .catch(error => console.log(error))
            },
            addInput() {
                if (this.isTextMax) { // 最大件数に達している場合は何もしない
                    return;
                }
                this.keyword.push(''); // 配列に１つ空データを追加する
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
                    "lessonid": this.radioSelect,
                    "role": 'learner',
                    "status": 'active'
                };
                axios.post('http://localhost:8000/api/postkeyword', params)
                    .then(response => this.keyword_cb = response.data['keyword'])
                    .catch(error => console.log(error))
            },

            getGroup(id) {
                const params_get = {
                    lessonid: id,
                    user_id: "<?= $user_id;?>",
                    role: 'learner'
                };
                axios.get('http://localhost:8000/api/getgroup', {params: params_get})
                    .then(response => this.learner_groups = response.data)
                    .catch(error => console.log(error))
            },


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
