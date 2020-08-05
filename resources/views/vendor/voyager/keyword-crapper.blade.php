@extends('voyager::master')

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
                                    <label for="" class="control-label">Keywords</label>
                                    <textarea type="text" rows="10" v-model="keywords" placeholder="Keywords" class="form-control"></textarea>
                                </div>

                                <div class="form-group">
                                    <input type="checkbox" id="only_insert" v-model='insertMultipleWordsOnly'>
                                    <label for="only_insert">Only insert these words (MULTIPLE INSERT)</label>
                                </div>

                                <div class="form-group" v-if="!insertMultipleWordsOnly">
                                    <label for="" class="control-label">Level</label>
                                    <input type="text" v-model="level" max="2" placeholder="Level" class="form-control">
                                </div>

                                <div class="form-group">
                                    <label for="" class="control-label">Industry</label>
                                    <select2 :options="industries" v-model="industry">
                                        <option disabled value="">Select one</option>
                                    </select2>
                                </div>

                                <div class="form-group" v-if="!insertMultipleWordsOnly">
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
                                    <button v-if="!insertMultipleWordsOnly" type="button" class="btn btn-success" @click="start()">Crap it</button>
                                    <button v-if="insertMultipleWordsOnly" type="button" class="btn btn-success" @click="insertWords()">Insert these words</button>
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

    @include('libs.vue')

    <script>
        var cyrilicS = 'абвгдежзийклмнопстуфхцчшщюя'
        var latinS = 'qwertyuiopasdfghjklzxcvbnm'
        var numbers = '1234567890'

        var industries = JSON.parse(`{!! json_encode($industries) !!}`);

        var select2_industries = industries.map((val) => ({id: val.id, text: val.name}))

        var vm = new Vue({
            el: "#vue",
            data: {
                loading: false,
                keywords: "",
                level: 0,
                ready: false,
                industry: '',
                industries: select2_industries,
                symbols: cyrilicS + latinS + numbers,
                insertMultipleWordsOnly: false
            },
            methods: {
                start() {
                    if (!this.keywords) {
                        alert('no keywords.')
                        return false
                    }

                    let keywordsArr = this.keywords.split(/\r?\n/g)

                    this.loading = true;
                    this.ready = false;

                    for (keyword of keywordsArr) {
                        $.post('{{ env('SELENIUM_SERVER_ADDRESS') }}/api/custom_python_test', {
                            "keyword": keyword.replace(/\s+/g, '_').toLowerCase(),
                            "level": this.level,
                            "symbols": this.symbols,
                            "industry": this.industry
                        }).done((res) => {
                            if (res.indexOf('___NO_DATA_EXCEPTION___') !== -1) {
                                this.ready = false;

                                Swal.fire({
                                    icon: 'error',
                                    title: 'No data.',
                                    text: 'The google did not return any suggestions for this keyword! Sorry :(',
                                    footer: 'Test it your self to catch the problem.'
                                })

                            } else if (res.indexOf('___INVALID_JSON___') !== -1) {
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Server error.',
                                    text: 'Server error occured',
                                    footer: 'The pipe connection broken'
                                })
                            }
                            else {
                                this.keywords = '';
                            }

                            console.log(res)
                        }).fail((res) => {
                            this.loading = false;
                            console.error(res)
                            alert(res)
                        })
                    }

                    $(document).ajaxStop(() => {
                        this.loading = false;
                        this.ready = true;
                    })
                },
                insertWords() {
                    if (!this.keywords) {
                        alert('no keywords.')
                        return false
                    }

                    let keywordsArr = this.keywords.split(/\r?\n/g)
                    this.loading = true

                    $.ajax({method: 'POST', url:"/api/store_keywords", data: {
                        keywords: keywordsArr,
                        industry: this.industry
                    }}).done(res => {
                        this.loading = false;
                        this.keywords = '';
                        Swal.fire({title: "Good to go!", icon: "success"});
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