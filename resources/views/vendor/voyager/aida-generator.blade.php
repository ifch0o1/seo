@extends('voyager::master')

@section('page_title', "AIDA Generator")

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-archive"></i> AIDA Generator
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
                        <h4 class="h3 col-12">General parameters</h4>

                        <div class="row">
                            <div class="col-md-4">
                                <span>Industry</span>
                                <select2 @input="industryChange" :options="industries" v-model="industry_id">
                                    <option disabled value="">Select one</option>
                                </select2>
                            </div>
                            <div class="col-md-4">
                                <span>Client</span>
                                <select2 :options="clients" v-model="client_id">
                                    <option disabled value="">Select one</option>
                                </select2>
                            </div>
                            <div class="col-md-4">
                                <span>AIDA Tags</span>
                                <input class="form-control" type="text" v-model="selectedTags">
                                <span v-for="tag in tags" class="mr-2 my-2 badge badge-lg badge-primary cursor-pointer" @click="toggleTag(tag.id)">#@{{tag.id}} ( @{{tag.name}} )</span>
                            </div>
                        </div>

                        <div class="row">
                            <div class="col-md-8">
                                <table class="table-auto text-lg font-semibold w-full" v-if="keywords.length && !loadingKw">
                                    <thead class="select-none">
                                        <tr class="bg-gray-200">
                                            <th class="px-4 py-2">
                                                <input type="checkbox" @change="toggelAllKeywords($event)" class="custom-browse-checkbox">
                                            </th>
                                            <th class="px-4 py-2 cursor-pointer" @click="sort('keyword')">Keyword  <i class="voyager-sort"></i> </th>
                                            <th class="px-4 py-2 cursor-pointer" @click="sort('money_rank')">Money R. <i class="voyager-sort"></i> </th>
                                            <th class="px-4 py-2 cursor-pointer" @click="sort('used')">Used <i class="voyager-sort"></i> </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="(kw, index) in sortedKeywords">
                                            <td class="border px-4 py-2 select-none">
                                                <input type="checkbox" value="@{{kw.id}}" class="custom-browse-checkbox" v-model="keywordsSeleted[kw.id]">
                                            </td>
                                            <td class="border px-4 py-2">@{{kw.keyword}}</td>
                                            <td class="border px-4 py-2">@{{kw.money_rank}}</td>
                                            <td class="border px-4 py-2">XXX</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p class="text-lg" v-if="!keywords.length && !loadingKw">No approved keywords found</p>

                                <div class="w-full text-center" v-if="loadingKw">
                                    <x-preloader></x-preloader>
                                </div>
                            </div>

                            <div class="col-md-4">
                                <div class="w-full">
                                    <div class="presubmit-active-radios w-full select-none">
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="generate_activated" id="generate_activated1" value="0" checked>
                                                Generate as Not approved (Deactivated)
                                            </label>
                                        </div>
    
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="generate_activated" id="generate_activated2" value="1">
                                                Generate as Approved
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-full">
                                    <span class="btn btn-success" @click="start">Generate</span>
                                </div>
                            </div>
                        </div>
                    </div>
                </div>
            </div>
        </div>
    </div>
@stop

@section('javascript')

    @include('libs.vue')
    
    <script>
        var industries = JSON.parse(`{!! json_encode($industries) !!}`);
        var clients = JSON.parse(`{!! json_encode($clients) !!}`);
        var tags = JSON.parse(`{!! json_encode($tags) !!}`);

        var select2_industries = industries.map((val) => ({id: val.id, text: val.name}))

        var select2_clients = clients.map((val) => ({id: val.id, text: `${val.name} (${val.site})`}))

        /**####################################################################################
         * #                                                                                  #
         * #                    VUE INSTANCE STARTING                                         #
         * #                                                                                  #
         * ####################################################################################
         */
        var vm = new Vue({
            el: "#vue",
            data: {
                loading: false,
                ready: false,
                industry_id: '',
                industries: select2_industries,
                client_id: '',
                clients: select2_clients,
                tags: tags,
                selectedTags: "",
                keywords: [],
                keywordsSeleted: {},
                currentSort:'money_rank',
                currentSortDir:'desc',
                loadingKw: false
            },
            methods: {
                start() {
                    if (!this.validate()) {
                        return false;
                    }


                },
                validate() {
                    const errorFiels = {
                        industry_id: "Industry",
                        client_id: "Client",
                        tags: "AIDA Tags",
                        keywords: "Keywords"
                    }

                    let errors = []
                    let errorText = '';

                    if (!this.industry_id) {errors.push(errorFiels['industry_id'])}
                    if (!this.client_id) {errors.push(errorFiels['client_id'])}
                    if (!this.selectedTags) {errors.push(errorFiels['tags'])}
                    if (!Object.keys(this.keywordsSeleted).length) {errors.push(errorFiels['keywords'])}

                    if (errors.length) {
                        errorText = errors.join(', ');

                        Swal.fire({
                            title: "Missing required fields",
                            text: errorText,
                            icon: 'warning'
                        })

                        return false
                    } else {
                        return true
                    }
                },
                industryChange(val) {
                    this.loadingKw = true;
                    $.ajax({
                        method: "GET",
                        url: "/api/keywords",
                        data: {
                            industry_id: this.industry_id
                        }
                    }).done((keywords) => {
                        this.keywords = keywords;
                    }).always(() => {this.loadingKw = false})
                },
                toggleTag(id) {
                    let selectedTagsArr = this.selectedTags.split(',')
                    selectedTagsArr = selectedTagsArr.map(tag => tag.trim())
                    let currentIndexOfTag = selectedTagsArr.indexOf( String(id) );
                    if (currentIndexOfTag === -1) {
                        selectedTagsArr.push(id)
                    } else {
                        selectedTagsArr.splice(currentIndexOfTag, 1);
                    }
                    this.selectedTags = selectedTagsArr.filter(item => item).join(',');
                },
                toggelAllKeywords(ev) {
                    for(val of this.keywords) {
                        this.keywordsSeleted[val.id] = ev.target.checked;
                        this.$forceUpdate()
                    }
                },
                sort:function(s) {
                    //if s == current sort, reverse
                    if(s === this.currentSort) {
                        this.currentSortDir = this.currentSortDir==='asc'?'desc':'asc';
                    }
                    this.currentSort = s;
                }
            },
            computed:{
                sortedKeywords:function() {
                    return this.keywords.sort((a,b) => {
                        let modifier = 1;
                        if(this.currentSortDir === 'desc') modifier = -1;
                        if(a[this.currentSort] < b[this.currentSort]) return -1 * modifier;
                        if(a[this.currentSort] > b[this.currentSort]) return 1 * modifier;
                        return 0;
                    });
                }
            }
        })
    </script>
@endsection