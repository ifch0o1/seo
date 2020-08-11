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
                <div class="panel panel-bordered" id="vue">
                    <div class="panel-body">
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
                                <span>Title Tags</span>
                                <input class="form-control" type="text" v-model="titleTags">

                                <br>

                                <span>AIDA Tags</span>
                                <input class="form-control" type="text" v-model="selectedTags">
                                <span v-for="tag in tags" class="mr-2 my-2 badge badge-lg badge-primary cursor-pointer" @click="toggleTag(tag.id)">#@{{tag.id}} ( @{{tag.name}} )</span>
                            </div>
                        </div>

                        <div class="row position-sticky" style="top:0">
                            <div class="col-md-8">

                                <div class="w-full text-center" v-if="loadingKw || loadingPosts">
                                    <x-preloader></x-preloader>
                                </div>

                                <h4 class="my-8" v-if="savedPosts.length">Generated Posts</h4>
                                <table class="table-auto text-lg w-full" v-if="savedPosts.length">
                                    <thead class="select-none">
                                        <tr class="bg-gray-200">
                                            <th class="px-4 py-2 cursor-pointer">Posts</th>
                                        </tr>
                                    </thead>
                                    <tbody>
                                        <tr v-for="post in savedPosts">
                                            <td class="border px-4 py-2">
                                                {{-- GENERATE NEW TITLE --}}
                                                <div class="w-full text-center">
                                                    <span class="cursor-pointer" @click="reGenerateTitle(post)" title="Refresh Title">
                                                        <i class="voyager-refresh"></i> Title
                                                    </span>
                                                </div>

                                                {{-- SHOWING TITLE --}}
                                                <h3 class="text-2xl mb-16" v-html="post.title"></h3>

                                                {{-- ADD NEW IMAGE --}}
                                                <div class="w-full text-center">
                                                    <span class="cursor-pointer px-5" @click="addImage(post)" title="Add Image">
                                                        <i class="voyager-plus"></i> Image
                                                    </span>

                                                    <a class="cursor-pointer px-5" target="_blank" :href="'/admin/aida-posts/' + post.id + '/edit'" title="Edit post">
                                                        <i class="voyager-pen"></i> Post
                                                    </a>

                                                    <span class="cursor-pointer px-5" title="Approve" v-if="!post.approved" @click="changePostStatus(post, 1)">
                                                        <i class="voyager-check"></i> Approve
                                                    </span>

                                                    <span class="cursor-pointer px-5" title="Disapprove" v-if="post.approved" @click="changePostStatus(post, 0)">
                                                        <i class="voyager-x"></i> Disapprove
                                                    </span>
                                                </div>

                                                {{-- VIEW POST TEXT --}}
                                                <div v-html="post.text"></div>
                                            </td>
                                        </tr>
                                    </tbody>
                                </table>

                                <h4 class="my-8" v-if="keywords.length && !loadingKw">Approved keywords</h4>
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
                                        <tr v-if="sortedKeywords.length" v-for="(kw, index) in sortedKeywords">
                                            <td class="border px-4 py-2 select-none">
                                                <input type="checkbox" :value="kw.id" class="custom-browse-checkbox" v-model="selectedKeywords[kw.id]">
                                            </td>
                                            <td class="border px-4 py-2">@{{kw.keyword}}
                                                <span class="hover-icon-1 inline-block">
                                                    <i 
                                                    @click="liveEditWord(kw)"
                                                    class="voyager-pen table-text-icon"
                                                    title="Inline edit"></i>
                                                </span>
                                            </td>
                                            <td class="border px-4 py-2">@{{kw.money_rank}}</td>
                                            <td class="border px-4 py-2">@{{kw.used || '0'}} times</td>
                                        </tr>
                                    </tbody>
                                </table>
                                <p class="text-lg" v-if="!keywords.length && !loadingKw">No approved keywords found</p>
                            </div>

                            <div class="col-md-4">

                                <div class="bg-gray-300 p-3 mt-4">
                                    <h4 class="mt-4 text-xl">Available Image Folders:</h4>
                                    <p class="my-2 underline" v-for="folder in folders">@{{folder}}</p>
                                </div>

                                <div class="mt-5">
                                    <h3 class="my-4 text-xl font-bold">Image Properties:</h3>
                                    <div class="form-group">
                                        <label>Render Custom text in Image</label>
                                        <textarea 
                                            v-model="customImageText"
                                            type="text"
                                            class="form-control resize-none block w-full"
                                            placeholder="Custom text"
                                            rows="2">
                                        </textarea>
                                    </div>

                                    <div class="form-group">
                                        <label>Custom Style</label>
                                        <textarea 
                                            v-model="customImageCss" type="text" class="form-control resize-none block w-full" rows="2">
                                        </textarea>
                                    </div>

                                    <div class="row">
                                        <div class="form-group col-lg-6">
                                            <label>Crop Horizontal (1-10)</label>
                                            <input type="number" class="form-control" min="1" max="10" v-model="cropImageY">
                                        </div>

                                        <div class="form-group col-lg-6">
                                            <label>Crop Vertical (1-10)</label>
                                            <input type="number" class="form-control" min="1" max="10" v-model="cropImageX">
                                        </div>
                                    </div>
                                </div>

                                <div class="w-full">
                                    <div class="presubmit-active-radios w-full select-none">
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="generate_activated" id="generate_activated1" value="0" v-model="generate_activated" checked>
                                                Generate as Not approved (Deactivated)
                                            </label>
                                        </div>
    
                                        <div class="radio">
                                            <label>
                                                <input type="radio" name="generate_activated" id="generate_activated2" v-model="generate_activated" value="1">
                                                Generate as Approved
                                            </label>
                                        </div>
                                    </div>
                                </div>
                                <div class="w-full">
                                    <span class="btn btn-success w-100" @click="start">Generate</span>
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
        var industries = {!! json_encode($industries) ?? [] !!};
        var clients = {!! json_encode($clients) ?? [] !!};
        var tags = {!! json_encode($tags) ?? [] !!};

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
                selectedTags: 'img-office,1,2,3,4',
                titleTags: '7',
                keywords: [],
                selectedKeywords: {},
                currentSort:'money_rank',
                currentSortDir:'desc',
                loadingKw: false,
                savedPosts: [],
                generate_activated: 0,
                loadingPosts: false,
                folders: {!! json_encode($folders) ?? [] !!},
                customImageText: '',
                customImageCss: 'max-width: 100%; max-height: 100%;',
                cropImageY: '8',
                cropImageX: '0',
                editingPost: {}
            },
            methods: {
                start() {
                    if (!this.validate()) {
                        return false;
                    }

                    if (this.loadingPosts) return;

                    this.savedPosts = [];

                    let tagIds = this.selectedTags.split(',').map(tag => tag.trim()).filter(val => val);
                    let titleTagIds = this.titleTags.split(',').map(tag => tag.trim()).filter(val => val);
                    let keywordIds = Object.keys(this.selectedKeywords);
                    let selectedKeywordIds = keywordIds.filter(val => this.selectedKeywords[val]);

                    this.loadingPosts = true;
                    $.post('/api/aida_posts/generate', {
                        industry: this.industry_id,
                        client: this.client_id,
                        tagIds,
                        selectedKeywordIds,
                        titleTagIds,
                        generate_activated: this.generate_activated,
                        customImageText: this.customImageText,
                        customImageCss: this.customImageCss,
                        cropImageY: this.cropImageY,
                        cropImageX: this.cropImageX,
                    }).done(savedPosts => {
                        this.loadingPosts = false;
                        if (savedPosts && savedPosts.length) {
                            this.savedPosts = savedPosts;
                        }
                    }).fail(err => {
                        this.loadingPosts = false;
                    });
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
                    if (!Object.keys(this.selectedKeywords).length) {errors.push(errorFiels['keywords'])}

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
                reGenerateTitle(post) {
                    let titleTagsArr = this.titleTags.split(',').filter(val => !!val);

                    $.ajax({url: `/aida_posts/reGenerateTitle/${post.id}`, data: {
                        // GET RANDOM TAG
                        tag: titleTagsArr[Math.floor(Math.random() * titleTagsArr.length)]
                    }}).done(freshPost => { 
                        post.title = freshPost.title;
                    });
                },
                addImage(post) {
                    /** Getting the img-... tag from aida tags */
                    let tag = this.selectedTags.split(',').find(element => element.indexOf('img-') > -1);
                    /** Determinate the folder name. */
                    let folder = tag.split('-').splice(1, tag.length - 1).join('-');

                    $.ajax({url: `/aida_posts/addImage/${post.id}`, data: {
                        customImageText: this.customImageText,
                        customImageCss: this.customImageCss,
                        cropImageY: this.cropImageY,
                        cropImageX: this.cropImageX,
                        folder,
                    }}).done(freshPost => {
                        post.text = freshPost.text;
                    })
                },

                /**
                 * @param approvalStatus can be 0 or 1 (approved or disapproved)
                 */
                changePostStatus(post, approvalStatus) {
                    $.ajax({
                        method: "PUT",
                        url: `/api/aida_posts/${post.id}`,
                        data: { approved: approvalStatus }
                    }).done(freshPost => {
                        post.approved = +freshPost.approved;
                        this.$forceUpdate();
                    });
                },
                liveEditWord(keywordObj) {
                    Swal.fire({
                        title: `Editing Keyword #${keywordObj.id}`,
                        input: "textarea",
                        inputValue: keywordObj.keyword,
                        inputAttributes: {
                            autocapitalize: "off",
                            id: 'swal_kw_input'
                        },
                        showCancelButton: true,
                        customClass: "swal-wide",
                        confirmButtonText: "Save",
                        showLoaderOnConfirm: true,
                        preConfirm: (keyword) => {
                            if (!keyword) alert("No keyword value");

                            let data = {};
                            data['keyword'] = keyword
                            $.ajax({
                                method: "PUT",
                                url: `/api/keywords/${keywordObj.id}`,
                                data
                            })
                            .done((res) => {
                                keywordObj.keyword = keyword;
                            })
                            .fail((res) => {
                                console.log('fail');
                                Swal.fire({
                                    icon: 'error',
                                    title: 'Error occured',
                                    text: "Cannot save the keyword. Capture the network request for more info",
                                })
                            })
                        },
                        allowOutsideClick: () => !Swal.isLoading(),
                    })
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
                        this.selectedKeywords[val.id] = ev.target.checked;
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