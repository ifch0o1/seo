@extends('voyager::master')

@section('head')
    <script src="{{ asset('js/libs/vue.js') }}"></script>

    <script type="text/x-template" id="select2-template">
        <select>
            <slot></slot>
        </select>
    </script>
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
                                    <label for="" class="control-label">Industry</label>
                                    <select2 :options="industries" v-model="industry"></select2>
                                    {{-- <select class="form-control select2" name="industry" v-model="industry" id="industry">
                                        <option value="" selected='selected'>Без индустрия</option>
                                        @foreach ($industries as $industry)
                                            <option value="{{ $industry->id }}"> {{ $industry->name }} </option>
                                        @endforeach
                                    </select> --}}
                                </div>

                                <div class="form-group">
                                    <label for="" class="control-label">
                                        Symbols
                                        <span class="badge badge-info cursor-pointer" @click="inputCyrilic">Cyrilic</span>
                                        <span class="badge badge-primary cursor-pointer" @click="inputLatin">Latin</span>
                                        <span class="badge badge-success cursor-pointer" @click="addNumbers">Add Numbers</span>
                                        <span class="badge badge-danger cursor-pointer" @click="removeNumbers">Remove Numbers</span>
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
    Vue.component("select2", {
        props: ["options", "value"],
        template: "#select2-template",
        mounted: function () {
            var vm = this;
            $(this.$el)
                // init select2
                .select2({ data: this.options, width: '100%' })
                .val(this.value)
                .trigger("change")
                // emit event on change.
                .on("change", function () {
                    vm.$emit("input", this.value);
                });
        },
        watch: {
            value: function (value) {
                // update value
                $(this.$el).val(value).trigger("change");
            },
            options: function (options) {
                // update options
                $(this.$el).empty().select2({ data: options });
            },
        },
        destroyed: function () {
            $(this.$el).off().select2("destroy");
        },
    });

    Vue.config.devtools = true;

    var cyrilicS = 'абвгдежзийклмнопстуфхцчшщюя'
    var latinS = 'qwertyuiopasdfghjklzxcvbnm'
    var numbers = '1234567890'

    var industries = JSON.parse(`{!! json_encode($industries) !!}`);

    var select2_industries = industries.map((val) => {
        return {id: val.id, text: val.name}
    })

    var vm = new Vue({
        el: "#vue",
        data: {
            loading: false,
            keyword: "",
            level: 0,
            ready: false,
            industry: '',
            industries: select2_industries,
            symbols: cyrilicS + latinS + numbers
        },
        methods: {
            start() {
                if (!this.keyword) {
                    alert('no keyword.')
                    return false
                }
                
                this.loading = true;
                this.ready = false;

                $.post('http://79.124.39.68/api/custom_python_test', {
                    "keyword": this.keyword.replace(/\s+/g, '_').toLowerCase(),
                    "level": this.level,
                    "symbols": this.symbols,
                    "industry": this.industry
                }).done((res) => {
                    this.loading = false;
                    this.ready = true;
                    this.keyword = '';

                    console.log(res)
                }).fail((res) => {
                    this.loading = false;
                    console.error(res)
                    alert(res)
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