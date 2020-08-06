@extends('voyager::master')

@section('page_title', "Manage Authentication Tokens")

@section('page_header')
    <div class="container-fluid">
        <h1 class="page-title">
            <i class="voyager-key"></i> Manage Authentication Tokens
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
                        <h4 class="h3 col-12">Client Tokens</h4>

                        <div class="row">
                            <div class="col-md-12">
                                <span>Client</span>
                                <select2 :options="clients" v-model="client_id" @input="clientChange">
                                    <option disabled value="">Select one</option>
                                </select2>
                            </div>

                            <div class="col-md-12">
                                <span @click="createNewToken" v-if="client_id" class="btn btn-success">Create new token for this client</span>
                            </div>

                            <div class="col-md-12" v-if="new_tokens.length">
                                Newly created tokens
                                <p v-for="token in new_tokens" class="bg-green-200">#@{{token.id}} - <b>@{{token.token}}</b> @{{token.active ? 'active' : 'not active'}} (@{{token.type}})</p>
                            </div>

                            <div class="col-md-12" v-if="client_tokens.length">
                                Tokens
                                <p v-for="token in client_tokens">
                                    #@{{token.id}} - 
                                    <b>@{{token.token}}</b> 
                                    @{{token.active ? 'active' : 'not active'}} 
                                    (@{{token.type}})
                                    {{-- <i class="voyager-trash cursor-pointer ml-6 text-red-500" title="Deactivate" @click="deactivate(token)"></i> --}}
                                </p>
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

        var clients = {!! json_encode($clients) ?? [] !!};
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
                clients: select2_clients,
                client_tokens: [],
                client_id: undefined,
                new_tokens: []
            },
            methods: {
                clientChange(val) {
                    Swal.fire("Loading...");
                    $.ajax({method: "GET", url: `/admin/manage_tokens/${this.client_id}`}).done(res => {
                        this.new_tokens = [];
                        this.client_tokens = res;
                        Swal.close();
                    });
                },
                createNewToken() {
                    if (!this.client_id)
                        Swal.fire({title: 'Select client first.', icon: 'warning'});
                    else {
                        $.ajax({method: "POST", url: "/admin/manage_tokens", data: {
                            client_id: this.client_id,
                            type: "api_auth",
                        }}).done(res => {
                            this.new_tokens.push(res);
                        })
                    }
                }
            }
        })
    </script>
@endsection