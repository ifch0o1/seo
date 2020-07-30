@extends('voyager::master')

@section('page_title', "Keyword Ranking Settings and Assignments")

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-archive"></i> Keyword Ranking
        </h1>
    </div>
@stop

@section('content')
    @include('voyager::alerts')
    <div class="page-content container-fluid">
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered" id="vue">
                    <div class="panel-body">
                        <h4 class="h3 col-12" v-if="view == 'settings'">
                            Keyword Ranking Settings <span class="text-primary" v-if="industryName">(Industry: @{{industryName}})</span>
                        </h4>
                        <h4 class="h3 col-12" v-if="view == 'ranking'">
                            Keyword Ranking Statistics <span class="text-primary" v-if="industryName">(Industry: @{{industryName}})</span>
                        </h4>

                        <div class="row">
                            <div class="col-md-4">
                                <span>Client</span>
                                <select2 :options="clients" v-model="client_id" @input="clientChange">
                                    <option disabled value="">Select one</option>
                                </select2>
                            </div>

                            <div class="col-md-6">
                                <div>Show panel:</div>

                                <button class="btn rounded-full mr-4" 
                                @click="view = 'ranking'"
                                :class="[view == 'ranking' ? 'bg-blue-400 text-white' : 'border-gray-300 hover:bg-blue-400']">
                                        <i class="voyager-eye"></i>
                                        View Ranking
                                </button>

                                <button class="btn rounded-full" 
                                @click="view = 'settings'"
                                :class="[view == 'settings' ? 'bg-blue-400 text-white' : 'border-gray-300 hover:bg-blue-400']">
                                        <i class="voyager-settings"></i>
                                        Select Words for Ranking
                                </button>

                                {{-- <button class="btn rounded-full" 
                                @click="updateRanking">
                                        <i class="voyager-refresh"></i>
                                    Update Ranking
                                </button> --}}
                            </div>
                        </div>

                        <div class="row" v-if="view == 'settings'">
                            <div class="col-lg-8">

                                <div class="w-full text-center" v-if="loadingKw">
                                    <x-preloader></x-preloader>
                                </div>

                                <h4 class="my-8" v-if="keywords.length && !loadingKw">Approved keywords</h4>
                                <table class="table-auto text-lg font-semibold w-full" v-if="keywords.length && !loadingKw">
                                    <thead class="select-none">
                                        <tr class="bg-gray-200">
                                            <th class="px-4 py-2">
                                                <input 
                                                type="checkbox" @change="toggelAllKeywords($event)" 
                                                disabled 
                                                class="custom-browse-checkbox">
                                            </th>
                                            <th class="px-4 py-2 cursor-pointer" @click="sort('keyword')">Keyword  <i class="voyager-sort"></i> </th>
                                            <th class="px-4 py-2 cursor-pointer" @click="sort('money_rank')">Money R. <i class="voyager-sort"></i> </th>
                                            <th class="px-4 py-2 cursor-pointer" @click="sort('used')">Used <i class="voyager-sort"></i> </th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-if="sortedKeywords.length" v-for="(kw, index) in sortedKeywords">
                                            <td class="border px-4 py-2 select-none">
                                                <input type="checkbox" :value="kw.id" @change="toggleKeywordRanking" class="custom-browse-checkbox" v-model="selectedKeywords[kw.id]">
                                            </td>
                                            <td class="border px-4 py-2">@{{kw.keyword}}</td>
                                            <td class="border px-4 py-2">@{{kw.money_rank}}</td>
                                            <td class="border px-4 py-2">@{{kw.used || '0'}} times</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p class="text-lg" v-if="!keywords.length && !loadingKw">No approved keywords found</p>
                            </div>
                        </div>

                        <div class="row" v-if="view == 'ranking'">
                            <div class="col-md-12">
                                <table class="table-auto text-lg font-semibold w-full" v-if="rankings.length && !loadingRankings">
                                    <thead class="select-none">
                                        <tr class="bg-gray-200">
                                            <th class="px-4 py-2 cursor-pointer">Keyword </th>
                                            <th class="px-4 py-2 cursor-pointer">Position.</th>
                                            <th class="px-4 py-2 cursor-pointer">Change</th>
                                            <th class="px-4 py-2 cursor-pointer">Link</th>
                                            <th class="px-4 py-2 cursor-pointer">Date</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="r in rankings">
                                            <td class="border px-4 py-2">@{{r.keyword}}</td>
                                            <td class="border px-4 py-2">
                                                <div class="w-full flex justify-between border-0">
                                                    <span>
                                                        <span v-if="r.position">
                                                            <i class="voyager-search text-xl text-grey-600" title="Organic position"></i>
                                                            
                                                            @{{r.position}}
                                                        </span>
                                                    </span>
                                                    
                                                    <span>
                                                        <span v-if="r.ad_position">
                                                            <i class="voyager-dollar text-xl text-green-600" title="Ad position"></i>
                                                            
                                                            @{{r.ad_position}}
                                                        </span>
                                                    </span>
                                                </div>
                                            </td>
                                            <td class="border px-4 py-2">
                                                <div style="align-items: center">
                                                    @{{r.change || '-'}}
                                                    <i class="text-4xl align-middle"
                                                    :class="{
                                                            'voyager-double-up text-green-600': r.change_type == 'raise' && r.change > 1,
                                                            'voyager-double-down text-red-600': r.change_type == 'fall' && r.change > 1,
                                                            'voyager-angle-up text-green-600': r.change_type == 'raise' && r.change == 1,
                                                            'voyager-angle-down text-red-600': r.change_type == 'fall' && r.change == 1
                                                        }
                                                    "></i>
                                                </div>
                                            </td>
                                            <td class="border px-4 py-2">@{{r.link}}</td>
                                            <td class="border px-4 py-2">@{{r.created_at}}</td>
                                        </tr>
                                    </tbody>
                                </table>
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
        // var industries = JSON.parse(`{!! json_encode($industries) !!}`);
        
        // var select2_industries = industries.map((val) => ({id: val.id, text: val.name}))
        
        var clients = {!! json_encode($clients) !!}
        var select2_clients = clients.map((val) => ({id: val.id, text: `${val.name} (${val.site})`}))

        /**
         * ####################################################################################
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
                client_id: '',
                clients: select2_clients,
                keywords: [],
                selectedKeywords: {},
                currentSort:'money_rank',
                currentSortDir:'desc',
                loadingKw: false,
                view: 'ranking',
                industryName: '',
                rankings: [],
                loadingRankings: false
            },
            methods: {
                industryChange(val) {
                    this.loadingKw = true;
                    $.ajax({
                        method: "GET",
                        url: "/api/keywords",
                        data: {
                            industry_id: val,
                            // client_id: this.client_id
                        }
                    }).done((keywords) => {
                        this.keywords = keywords;
                    }).always(() => {this.loadingKw = false})

                    this.getSelectedForRankingWords();
                },
                getSelectedForRankingWords() {
                    $.get(`/api/client_keyword_href/${this.client_id}`).done(selectedWordHrefs => {
                        if (selectedWordHrefs) {
                            for(href of selectedWordHrefs) {
                                this.selectedKeywords[href.keyword_id] = true;
                            }
                        }
                    });
                },
                clientChange(val) {
                    /** 
                     * Loading keyword hrefs 
                     */
                    $.get(`/api/client/${val}`).done(client => {
                        if (client && client.industry_id) {
                            /** 
                             * Call the industry change
                             * 
                             * To get the Keywords
                             */
                            this.industryChange(client.industry_id);

                            $.get(`/api/industry/${client.industry_id}`).done(industry => {
                                this.industryName = industry.name;
                            })
                        } else {
                            Swal.fire("This client has no attached industry");
                        }
                    });

                    /** 
                     * Loading ranking data
                     */
                    $.get(`/api/client_keywords_ranking/${val}`).done(rankings => {
                        rankings.forEach(val => {val.link = decodeURIComponent(val.link)})
                        this.rankings = rankings.sort(r => {
                            return -(+r.change || 0 + +r.position || 0 + +r.ad_position || 0)
                        })
                    })
                },
                toggleTag(id) {
                    let selectedTagsArr = this.selectedTags.split(',')
                    selectedTagsArr = selectedTagsArr.map(tag => tag.trim())
                    let currentIndexOfTag = selectedTagsArr.indexOf( String(id) )
                    if (currentIndexOfTag === -1) {
                        selectedTagsArr.push(id)
                    } else {
                        selectedTagsArr.splice(currentIndexOfTag, 1);
                    }
                    this.selectedTags = selectedTagsArr.filter(item => item).join(',');
                },
                toggelAllKeywords(ev) {
                    for(val of this.keywords) {
                        this.selectedKeywords[val.id] = ev.target.checked;
                        this.$forceUpdate()
                    }
                },
                toggleKeywordRanking(ev) {
                    let checked = ev.target.checked;
                    if (checked) {
                        $.post('/api/client_keyword_href', {
                            client_id: this.client_id,
                            keyword_id: ev.target.value
                        }).done(res => {
                            console.log(res);
                        })
                    } else {
                        $.ajax({
                            url: '/api/client_keyword_href',
                            method: "DELETE",
                            data: {
                                client_id: this.client_id,
                                keyword_id: ev.target.value
                            }
                        }).done(res => {
                            console.log(res);
                        });
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