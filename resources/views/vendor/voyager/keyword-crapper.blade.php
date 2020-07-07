@extends('voyager::master')

@section('head')
    <script src="{{ asset('js/libs/vue.js') }}"></script>
@stop

@section('page_title', "Keyword Crapper")

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-archive"></i> Keyword Crapper
        </h1>
    </div>
@stop

@section('content')
    @include('voyager::alerts')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body" id="vue">
                        <h3 class="h3 col-12">Параметри:</h3>

                        <div class="alert alert-info flex items-center justify-around" v-if="loading">
                            <strong class="text-3xl">Processing</strong>
                            <x-preloader></x-preloader>
                        </div>

                        <div v-if="ready" class="alert alert-success text-2xl">Ready</div>


                        <form v-if="!loading">
                            <div class="col-6">
                                <div class="form-group">
                                    <label for="" class="control-label">Keyword</label>
                                    <input type="text" v-model="keyword" placeholder="Keyword" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="" class="control-label">Level</label>
                                    <input type="text" v-model="level" placeholder="Level" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="" class="control-label">
                                        Символи
                                        <span class="badge badge-primary cursor-pointer" @click="inputLatin">Кирилица</span>
                                        <span class="badge badge-primary cursor-pointer" @click="inputCyrilic">Латиница</span>
                                        <span class="badge badge-primary cursor-pointer" @click="addNumbers">С цифри</span>
                                        <span class="badge badge-primary cursor-pointer" @click="removeNumbers">Без цифри</span>
                                    </label>
                                    <input type="text" v-model="symbols" placeholder="Level" class="form-control">
                                </div>

                                <div class="form-group">
                                    <button type="button" class="btn btn-success" @click="start()">Crap it</button>
                                </div>
                            </div>
                        </form>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascript')
<script>
    var cyrilicS = 'абвгдежзийклмнопстуфхцчшщюя'
    var latinS = 'qwertyuiopasdfghjklzxcvbnm1234567890'
    var numbers = '1234567890'

    var vm = new Vue({
        el: "#vue",
        data: {
            loading: false,
            keyword: "",
            level: 0,
            ready: false,
            symbols: cyrilicS + latinS + numbers,
        },
        methods: {
            start() {
                if (!this.keyword) alert('no keyword.')
                this.loading = true;
                this.ready = false;

                $.post('http://www.seo-tracktor.com/api/custom_python_test', {
                    "_token": "{{ csrf_token() }}",
                    "keyword": this.keyword.replace(" ", "_"),
                    "level": this.level
                }).done((res) => {
                    this.loading = false;
                    this.ready = true;
                    this.keyword = '';

                    console.log(res)
                })
            },
            inputLatin() {
                this.symbols = latinS
            },
            inputCyrilic() {
                this.symbols = cyrilicS
            },
            addNumbers() {
                this.symbols = removeDuplicateCharacters(this.symbols + numbers);
            },
            removeNumbers() {
                this.symbols = this.symbols.replace(/\d+/g, '');
            }
        },
    })
</script>
@endsection