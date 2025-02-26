@extends('voyager::master')

@section('page_title', __('voyager::generic.viewing').' '.$dataType->getTranslatedAttribute('display_name_plural'))

@section('page_header')

    <style>
        table .delete {
            display: none;
        }
    </style>

    <div class="container-fluid">
        <h1 class="page-title">
            <i class="{{ $dataType->icon }}"></i> {{ $dataType->getTranslatedAttribute('display_name_plural') }}
        </h1>
        @can('add', app($dataType->model_name))
            <a href="{{ route('voyager.'.$dataType->slug.'.create') }}" class="btn btn-success btn-add-new">
                <i class="voyager-plus"></i> <span>{{ __('voyager::generic.add_new') }}</span>
            </a>
        @endcan
        @can('delete', app($dataType->model_name))
            @include('voyager::partials.bulk-delete')

            {{-- IVO CUSTOM DELETE NOT APPROVED START --}}

            <a class="btn btn-warning" id="not_approved_delete" data-toggle="modal" data-target="#custom_delete_modal"><i class="voyager-trash"></i> <span>Hide (delete) all not approved</span></a>

            {{-- IVO CUSTOM DELETE NOT APPROVED END --}}
        @endcan
        @can('edit', app($dataType->model_name))
            @if(isset($dataType->order_column) && isset($dataType->order_display_column))
                <a href="{{ route('voyager.'.$dataType->slug.'.order') }}" class="btn btn-primary btn-add-new">
                    <i class="voyager-list"></i> <span>{{ __('voyager::bread.order') }}</span>
                </a>
            @endif
        @endcan
        @can('delete', app($dataType->model_name))
            @if($usesSoftDeletes)
                <input type="checkbox" @if ($showSoftDeleted) checked @endif id="show_soft_deletes" data-toggle="toggle" data-on="{{ __('voyager::bread.soft_deletes_off') }}" data-off="{{ __('voyager::bread.soft_deletes_on') }}">
            @endif
        @endcan
        @foreach($actions as $action)
            @if (method_exists($action, 'massAction'))
                @include('voyager::bread.partials.actions', ['action' => $action, 'data' => null])
            @endif
        @endforeach
        @include('voyager::multilingual.language-selector')
    </div>

    <div class="container-fluid" id="filtersVue">
        <div class="row">
            <div class="col-md-3">
                <span>Industry filter</span>
                <select2-ajax name="industry_filter" :options="industries" :value="industry" @input="industryFilter">
                    <option value="">All</option>
                </select2-ajax>
            </div>

            <div class="col-md-3">
                <span>Client filter</span>
                <select2-ajax name="client_filter" :options="clients" :value="client" @input="clientFilter">
                    <option value="">All</option>
                </select2-ajax>
            </div>
        </div>
    </div>
@stop

@section('content')
    <div class="page-content browse container-fluid">
        @include('voyager::alerts')
        <div class="row">
            <div class="col-md-12">
                <div class="panel panel-bordered">
                    <div class="panel-body">
                        @if ($isServerSide)
                            <form method="get" class="form-search">
                                <div id="search-input">
                                    <div class="col-2">
                                        <select id="search_key" name="key">
                                            @foreach($searchNames as $key => $name)
                                                <option value="{{ $key }}" @if($search->key == $key || (empty($search->key) && $key == $defaultSearchKey)) selected @endif>{{ $name }}</option>
                                            @endforeach
                                        </select>
                                    </div>
                                    <div class="col-2">
                                        <select id="filter" name="filter">
                                            <option value="contains" @if($search->filter == "contains") selected @endif>contains</option>
                                            <option value="equals" @if($search->filter == "equals") selected @endif>=</option>
                                        </select>
                                    </div>
                                    <div class="input-group col-md-12">
                                        <input type="text" class="form-control" placeholder="{{ __('voyager::generic.search') }}" name="s" value="{{ $search->value }}">
                                        <span class="input-group-btn">
                                            <button class="btn btn-info btn-lg" type="submit">
                                                <i class="voyager-search"></i>
                                            </button>
                                        </span>
                                    </div>
                                </div>
                                @if (Request::has('sort_order') && Request::has('order_by'))
                                    <input type="hidden" name="sort_order" value="{{ Request::get('sort_order') }}">
                                    <input type="hidden" name="order_by" value="{{ Request::get('order_by') }}">
                                @endif
                            </form>
                        @endif
                        <div class="table-responsive" id="vue-tabl">
                            <table id="dataTable" class="table table-hover">
                                <thead>
                                    <tr>
                                        @if($showCheckboxColumn)
                                            <th>
                                                <input type="checkbox" class="select_all">
                                            </th>
                                        @endif
                                        @foreach($dataType->browseRows as $row)
                                        <th>
                                            @if ($isServerSide)
                                                <a href="{{ $row->sortByUrl($orderBy, $sortOrder) }}">
                                            @endif
                                            {{ $row->getTranslatedAttribute('display_name') }}
                                            @if ($isServerSide)
                                                @if ($row->isCurrentSortField($orderBy))
                                                    @if ($sortOrder == 'asc')
                                                        <i class="voyager-angle-up pull-right"></i>
                                                    @else
                                                        <i class="voyager-angle-down pull-right"></i>
                                                    @endif
                                                @endif
                                                </a>
                                            @endif
                                        </th>
                                        @endforeach
                                        <th class="actions text-right">{{ __('voyager::generic.actions') }}</th>
                                    </tr>
                                </thead>
                                <tbody>
                                    @foreach($dataTypeContent as $data)
                                    <tr>
                                        @if($showCheckboxColumn)
                                            <td>
                                                <input type="checkbox" name="row_id" id="checkbox_{{ $data->getKey() }}" value="{{ $data->getKey() }}">
                                            </td>
                                        @endif
                                        @foreach($dataType->browseRows as $row)
                                            @php
                                            if ($data->{$row->field.'_browse'}) {
                                                $data->{$row->field} = $data->{$row->field.'_browse'};
                                            }
                                            @endphp
                                            <td>
                                                @if (isset($row->details->view))
                                                    @include($row->details->view, ['row' => $row, 'dataType' => $dataType, 'dataTypeContent' => $dataTypeContent, 'content' => $data->{$row->field}, 'action' => 'browse', 'view' => 'browse', 'options' => $row->details])
                                                @elseif($row->type == 'image')
                                                    <img src="@if( !filter_var($data->{$row->field}, FILTER_VALIDATE_URL)){{ Voyager::image( $data->{$row->field} ) }}@else{{ $data->{$row->field} }}@endif" style="width:100px">
                                                @elseif($row->type == 'relationship')
                                                    @include('voyager::formfields.relationship', ['view' => 'browse','options' => $row->details])
                                                @elseif($row->type == 'select_multiple')
                                                    @if(property_exists($row->details, 'relationship'))

                                                        @foreach($data->{$row->field} as $item)
                                                            {{ $item->{$row->field} }}
                                                        @endforeach

                                                    @elseif(property_exists($row->details, 'options'))
                                                        @if (!empty(json_decode($data->{$row->field})))
                                                            @foreach(json_decode($data->{$row->field}) as $item)
                                                                @if (@$row->details->options->{$item})
                                                                    {{ $row->details->options->{$item} . (!$loop->last ? ', ' : '') }}
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            {{ __('voyager::generic.none') }}
                                                        @endif
                                                    @endif

                                                    @elseif($row->type == 'multiple_checkbox' && property_exists($row->details, 'options'))
                                                        @if (@count(json_decode($data->{$row->field})) > 0)
                                                            @foreach(json_decode($data->{$row->field}) as $item)
                                                                @if (@$row->details->options->{$item})
                                                                    {{ $row->details->options->{$item} . (!$loop->last ? ', ' : '') }}
                                                                @endif
                                                            @endforeach
                                                        @else
                                                            {{ __('voyager::generic.none') }}
                                                        @endif

                                                @elseif(($row->type == 'select_dropdown' || $row->type == 'radio_btn') && property_exists($row->details, 'options'))

                                                

                                                    @if($row->type == 'select_dropdown')
                                                        {{-- IVO SELECT DROPDOWN --}}
                                                        <select 
                                                        onchange="select_dropdown_updateRow(this)" 

                                                        row-field="{{ $row->field }}" 
                                                        row-model="{{$dataType->name}}"
                                                        row-id="{{ $data->getKey() }}"

                                                        class="form-control"
                                                        >
                                                            <option value="">Choose</option>
                                                            @foreach($row->details->options as $key => $value)
                                                                <option 
                                                                value="{{$key}}"
                                                                @if($data->{$row->field} && $key == $row->details->options->{$data->{$row->field}})
                                                                    selected
                                                                @endif
                                                                >
                                                                    {{$value}}
                                                                </option>
                                                            @endforeach
                                                        </select>
                                                    {{-- END IVO SELECT DROPDOWN --}}
                                                    @else
                                                        {!! $row->details->options->{$data->{$row->field}} ?? '' !!}
                                                    @endif

                                                @elseif($row->type == 'date' || $row->type == 'timestamp')
                                                    @if ( property_exists($row->details, 'format') && !is_null($data->{$row->field}) )
                                                        {{ \Carbon\Carbon::parse($data->{$row->field})->formatLocalized($row->details->format) }}
                                                    @else
                                                        {{ $data->{$row->field} }}
                                                    @endif
                                                @elseif($row->type == 'checkbox')

                                                    {{-- IVO CUSTOM CHECKBOX 
                                                        (controlled in custom.js) 
                                                    --}}

                                                    <input type="checkbox" 
                                                        class="custom-browse-checkbox"
                                                        onchange="checkbox_updateRow(this)"
                                                        @if($data->{$row->field})
                                                            checked
                                                        @endif
                                                        row-field="{{ $row->field }}" 
                                                        row-model="{{$dataType->name}}"
                                                        row-id="{{ $data->getKey() }}"
                                                        value="{{ $data->getKey() }}"
                                                    >

                                                    {{-- END IVO CUSTOM CHECKBOX --}}

                                                    {{-- OLD CHECKBOX - default by the theme --}}

                                                    {{-- @if(property_exists($row->details, 'on') && property_exists($row->details, 'off'))
                                                        @if($data->{$row->field})
                                                            <span class="label label-info">{{ $row->details->on }}</span>
                                                        @else
                                                            <span class="label label-primary">{{ $row->details->off }}</span>
                                                        @endif
                                                    @else
                                                    {{ $data->{$row->field} }}
                                                    @endif --}}

                                                    {{-- END OLD CHECKBOX --}}
                                                @elseif($row->type == 'color')
                                                    <span class="badge badge-lg" style="background-color: {{ $data->{$row->field} }}">{{ $data->{$row->field} }}</span>
                                                @elseif($row->type == 'text')

                                                    @include('voyager::multilingual.input-hidden-bread-browse')
                                                    <div row-text-content-parent>
                                                        <span row-text-content>
                                                            {{ mb_strlen( $data->{$row->field} ) > 200 ? mb_substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field} }}
                                                        </span>

                                                        <div class="py-1 my-2 border-t border-gray-300"></div>
                                                        
                                                        {{-- IVO CUSTOM EDIT TEXT FIELD --}}

                                                        @if(property_exists($row->details, 'textEditable'))
                                                            <span class="hover-icon-1 inline-block pr-2">
                                                                <i 
                                                                row-field="{{ $row->field }}"
                                                                row-model="{{$dataType->name}}"
                                                                row-id="{{ $data->getKey() }}"
    
                                                                onclick="text_updateRow(this)"
    
                                                                class="voyager-pen table-text-icon"
                                                                title="Inline edit"></i>
                                                            </span>
                                                        @endif

                                                        {{-- IVO CUSTOM EDIT TEXT FIELD END --}}

                                                        {{-- CUSTOM FOR KEYWORDS SCREEN ONLY --}}

                                                        @if($row->field == 'keyword')
                                                            <span class="hover-icon-1 inline-block pr-2" @click="findBottomSuggestions(`{{ $data->getKey() }}`, `{{ $data->{$row->field} }}`, $event)">
                                                                <i class="voyager-search table-text-icon" 
                                                                @if($data->searched_for_bottom_suggestions == 1)
                                                                    style="opacity: 0.3"
                                                                @endif
                                                                title="Get bottom suggestions"></i>
                                                            </span>

                                                            <span class="hover-icon-1 inline-block pr-2 align-bottom cursor-pointer" @click="getRelatedKeywords(`{{ $data->getKey() }}`, `{{ $data->{$row->field} }}`, 'BG:bg', $event)">
                                                                <img 
                                                                    src="{{ Storage::url('public/icons/bulgaria.png') }}" 
                                                                    alt="Bulgarian related search" 
                                                                    title="Get BG related keywords"
                                                                    style="
                                                                        width: 32px;
                                                                        @if($data->searched_for_related_kws == 1)
                                                                            opacity: 0.3;
                                                                        @endif
                                                                    "
                                                                >
                                                            </span>
                                                            <span class="hover-icon-1 inline-block pr-2 align-bottom cursor-pointer" @click="getRelatedKeywords(`{{ $data->getKey() }}`, `{{ $data->{$row->field} }}`, 'US:en', $event)">
                                                                <img 
                                                                    src="{{ Storage::url('public/icons/english.png') }}" 
                                                                    alt="English related search" 
                                                                    title="Get US related keywords"
                                                                    style="
                                                                        width: 32px;
                                                                        @if($data->searched_for_related_kws == 1)
                                                                            opacity: 0.3;
                                                                        @endif
                                                                    "
                                                                >
                                                            </span>
                                                        @endif

                                                        {{-- END CUSTOM FOR KEYWORDS SCREEN ONLY --}}
                                                    </div>

                                                @elseif($row->type == 'text_area')
                                                    @include('voyager::multilingual.input-hidden-bread-browse')
                                                    <div>{{ mb_strlen( $data->{$row->field} ) > 200 ? mb_substr($data->{$row->field}, 0, 200) . ' ...' : $data->{$row->field} }}</div>
                                                @elseif($row->type == 'file' && !empty($data->{$row->field}) )
                                                    @include('voyager::multilingual.input-hidden-bread-browse')
                                                    @if(json_decode($data->{$row->field}) !== null)
                                                        @foreach(json_decode($data->{$row->field}) as $file)
                                                            <a href="{{ Storage::disk(config('voyager.storage.disk'))->url($file->download_link) ?: '' }}" target="_blank">
                                                                {{ $file->original_name ?: '' }}
                                                            </a>
                                                            <br/>
                                                        @endforeach
                                                    @else
                                                        <a href="{{ Storage::disk(config('voyager.storage.disk'))->url($data->{$row->field}) }}" target="_blank">
                                                            Download
                                                        </a>
                                                    @endif
                                                @elseif($row->type == 'rich_text_box')
                                                    @include('voyager::multilingual.input-hidden-bread-browse')
                                                    <div>{{ mb_strlen( strip_tags($data->{$row->field}, '<b><i><u>') ) > 200 ? mb_substr(strip_tags($data->{$row->field}, '<b><i><u>'), 0, 200) . ' ...' : strip_tags($data->{$row->field}, '<b><i><u>') }}</div>
                                                @elseif($row->type == 'coordinates')
                                                    @include('voyager::partials.coordinates-static-image')
                                                @elseif($row->type == 'multiple_images')
                                                    @php $images = json_decode($data->{$row->field}); @endphp
                                                    @if($images)
                                                        @php $images = array_slice($images, 0, 3); @endphp
                                                        @foreach($images as $image)
                                                            <img src="@if( !filter_var($image, FILTER_VALIDATE_URL)){{ Voyager::image( $image ) }}@else{{ $image }}@endif" style="width:50px">
                                                        @endforeach
                                                    @endif
                                                @elseif($row->type == 'media_picker')
                                                    @php
                                                        if (is_array($data->{$row->field})) {
                                                            $files = $data->{$row->field};
                                                        } else {
                                                            $files = json_decode($data->{$row->field});
                                                        }
                                                    @endphp
                                                    @if ($files)
                                                        @if (property_exists($row->details, 'show_as_images') && $row->details->show_as_images)
                                                            @foreach (array_slice($files, 0, 3) as $file)
                                                            <img src="@if( !filter_var($file, FILTER_VALIDATE_URL)){{ Voyager::image( $file ) }}@else{{ $file }}@endif" style="width:50px">
                                                            @endforeach
                                                        @else
                                                            <ul>
                                                            @foreach (array_slice($files, 0, 3) as $file)
                                                                <li>{{ $file }}</li>
                                                            @endforeach
                                                            </ul>
                                                        @endif
                                                        @if (count($files) > 3)
                                                            {{ __('voyager::media.files_more', ['count' => (count($files) - 3)]) }}
                                                        @endif
                                                    @elseif (is_array($files) && count($files) == 0)
                                                        {{ trans_choice('voyager::media.files', 0) }}
                                                    @elseif ($data->{$row->field} != '')
                                                        @if (property_exists($row->details, 'show_as_images') && $row->details->show_as_images)
                                                            <img src="@if( !filter_var($data->{$row->field}, FILTER_VALIDATE_URL)){{ Voyager::image( $data->{$row->field} ) }}@else{{ $data->{$row->field} }}@endif" style="width:50px">
                                                        @else
                                                            {{ $data->{$row->field} }}
                                                        @endif
                                                    @else
                                                        {{ trans_choice('voyager::media.files', 0) }}
                                                    @endif
                                                @else
                                                    @include('voyager::multilingual.input-hidden-bread-browse')
                                                    <span>{{ $data->{$row->field} }}</span>
                                                @endif
                                            </td>
                                        @endforeach

                                        <td class="no-sort no-click bread-actions">
                                            @foreach($actions as $action)
                                                @if (!method_exists($action, 'massAction'))
                                                    @include('voyager::bread.partials.actions', ['action' => $action])
                                                @endif
                                            @endforeach
                                        </td>

                                    </tr>
                                    @endforeach
                                </tbody>
                            </table>

                            <div class="modal fade" tabindex="-1" role="dialog" id="bottom_suggestions_modal">
                                <div class="modal-dialog" role="document">
                                    <div class="modal-content">
                                        <div class="modal-header">
                                            <button type="button" class="close" data-dismiss="modal" aria-label="Close"><span
                                                    aria-hidden="true">&times;</span></button>
                                            <h4 class="modal-title">Keywords for approval</h4>
                                        </div>
                                        <div class="modal-body">
                                            <p>List of bottom suggestions from google:</p>

                                            <ul v-if="!loadingBottomSuggestions">
                                                <li v-for="kw in keywords">
                                                    <input :id="'sug_approval_' + kw.id" type="checkbox" v-model="keywordsSelectedForApproval" :value="kw.id">
                                                    <label :for="'sug_approval_' + kw.id" class="font-bold">@{{ kw.keyword }}</label>
                                                </li>
                                            </ul>

                                            <div class="" v-else class="text-center">
                                                <span>
                                                    <x-preloader></x-preloader>
                                                </span>
                                            </div>

                                        </div>
                                        <div class="modal-footer">
                                            <button type="button" class="btn btn-default" data-dismiss="modal">Close</button>
                                            <button type="button" class="btn btn-primary" @click="approveSelectedKeywords">Save changes</button>
                                        </div>
                                    </div><!-- /.modal-content -->
                                </div><!-- /.modal-dialog -->
                            </div><!-- /.modal -->
                        </div>
                        @if ($isServerSide)
                            <div class="pull-left">
                                <div role="status" class="show-res" aria-live="polite">{{ trans_choice(
                                    'voyager::generic.showing_entries', $dataTypeContent->total(), [
                                        'from' => $dataTypeContent->firstItem(),
                                        'to' => $dataTypeContent->lastItem(),
                                        'all' => $dataTypeContent->total()
                                    ]) }}</div>
                            </div>
                            <div class="pull-right">
                                {{ $dataTypeContent->appends([
                                    's' => $search->value,
                                    'filter' => $search->filter,
                                    'key' => $search->key,
                                    'order_by' => $orderBy,
                                    'sort_order' => $sortOrder,
                                    'showSoftDeleted' => $showSoftDeleted,
                                ])->links() }}
                            </div>
                        @endif
                    </div>
                </div>

                @can('add', app($dataType->model_name))
                    <a href="{{ route('voyager.'.$dataType->slug.'.create') }}" class="btn btn-success btn-add-new">
                        <i class="voyager-plus"></i> <span>{{ __('voyager::generic.add_new') }}</span>
                    </a>
                @endcan
                @can('delete', app($dataType->model_name))
                    {{-- IVO CUSTOM DELETE NOT APPROVED START --}}

                    <a class="btn btn-warning" id="not_approved_delete" data-toggle="modal" data-target="#custom_delete_modal"><i class="voyager-trash"></i> <span>Hide (delete) all not approved</span></a>

                    {{-- IVO CUSTOM DELETE NOT APPROVED END --}}
                @endcan
                @can('edit', app($dataType->model_name))
                    @if(isset($dataType->order_column) && isset($dataType->order_display_column))
                        <a href="{{ route('voyager.'.$dataType->slug.'.order') }}" class="btn btn-primary btn-add-new">
                            <i class="voyager-list"></i> <span>{{ __('voyager::bread.order') }}</span>
                        </a>
                    @endif
                @endcan
                @can('delete', app($dataType->model_name))
                    @if($usesSoftDeletes)
                        <span class="btn btn-default click_upper_show_deleted_button">Toggle Deleted</span>
                    @endif
                @endcan
            </div>
        </div>
    </div>

    {{-- Single delete modal --}}
    <div class="modal modal-danger fade" tabindex="-1" id="delete_modal" role="dialog">
        <div class="modal-dialog">
            <div class="modal-content">
                <div class="modal-header">
                    <button type="button" class="close" data-dismiss="modal" aria-label="{{ __('voyager::generic.close') }}"><span aria-hidden="true">&times;</span></button>
                    <h4 class="modal-title"><i class="voyager-trash"></i> {{ __('voyager::generic.delete_question') }} {{ strtolower($dataType->getTranslatedAttribute('display_name_singular')) }}?</h4>
                </div>
                <div class="modal-footer">
                    <form action="#" id="delete_form" method="POST">
                        {{ method_field('DELETE') }}
                        {{ csrf_field() }}
                        <input type="submit" class="btn btn-danger pull-right delete-confirm" value="{{ __('voyager::generic.delete_confirm') }}">
                    </form>
                    <button type="button" class="btn btn-default pull-right" data-dismiss="modal">{{ __('voyager::generic.cancel') }}</button>
                </div>
            </div><!-- /.modal-content -->
        </div><!-- /.modal-dialog -->
    </div><!-- /.modal -->

    <x-delete-modal></x-delete-modal>
@stop

@section('css')
@if(!$dataType->server_side && config('dashboard.data_tables.responsive'))
    <link rel="stylesheet" href="{{ voyager_asset('lib/css/responsive.dataTables.min.css') }}">
@endif
@stop

@section('javascript')
    <!-- DataTables -->
    @if(!$dataType->server_side && config('dashboard.data_tables.responsive'))
        <script src="{{ voyager_asset('lib/js/dataTables.responsive.min.js') }}"></script>
    @endif
    <script>
        $(document).ready(function () {
            @if (!$dataType->server_side)
                var table = $('#dataTable').DataTable({!! json_encode(
                    array_merge([
                        "order" => $orderColumn,
                        "language" => __('voyager::datatable'),
                        "columnDefs" => [['targets' => -1, 'searchable' =>  false, 'orderable' => false]],
                    ],
                    config('voyager.dashboard.data_tables', []))
                , true) !!});
            @else
                $('#search-input select').select2({
                    minimumResultsForSearch: Infinity
                });
            @endif

            @if ($isModelTranslatable)
                $('.side-body').multilingual();
                //Reinitialise the multilingual features when they change tab
                $('#dataTable').on('draw.dt', function(){
                    $('.side-body').data('multilingual').init();
                })
            @endif
            $('.select_all').on('click', function(e) {
                $('input[name="row_id"]').prop('checked', $(this).prop('checked')).trigger('change');
            });
        });


        var deleteFormAction;
        $('td').on('click', '.delete', function (e) {
            $('#delete_form')[0].action = '{{ route('voyager.'.$dataType->slug.'.destroy', '__id') }}'.replace('__id', $(this).data('id'));
            $('#delete_modal').modal('show');
        });

        @if($usesSoftDeletes)
            @php
                $params = [
                    's' => $search->value,
                    'filter' => $search->filter,
                    'key' => $search->key,
                    'order_by' => $orderBy,
                    'sort_order' => $sortOrder,
                ];
            @endphp
            $(function() {
                $('#show_soft_deletes').change(function() {
                    if ($(this).prop('checked')) {
                        $('#dataTable').before('<a id="redir" href="{{ (route('voyager.'.$dataType->slug.'.index', array_merge($params, ['showSoftDeleted' => 1]), true)) }}"></a>');
                    }else{
                        $('#dataTable').before('<a id="redir" href="{{ (route('voyager.'.$dataType->slug.'.index', array_merge($params, ['showSoftDeleted' => 0]), true)) }}"></a>');
                    }

                    $('#redir')[0].click();
                })
            })
        @endif
        $('input[name="row_id"]').on('change', function () {
            var ids = [];
            $('input[name="row_id"]').each(function() {
                if ($(this).is(':checked')) {
                    ids.push($(this).val());
                }
            });
            $('.selected_ids').val(ids);
        });
    </script>


    {{-- IVO SCRIPTS --}}

    @include('libs.vue')

    <script>
        $('.click_upper_bulk_delete_button').on('click', () => {
            $('#bulk_delete_btn').click();
        })
        $('.click_upper_show_deleted_button').on('click', () => {
            $('.toggle').first().find('.toggle-handle').click();
        })


        let urlClient = getParams(window.location.href).client

        let filtersVue = new Vue({
            el: "#filtersVue",
            data: {
                industries: [],
                industry: '{{ $search->value }}' || '',
                clients: [],
                client: +urlClient || ""
            },
            methods: {
                industryFilter(val) {
                    if (val) {
                        window.location.search = `?key=industry_id&filter=equals&s=${val}`
                    } else {
                        window.location.search = ``
                    }
                },
                clientFilter(val) {
                    let clientObj = this.clients.find(client => client.id == val);

                    if (val && clientObj.industry_id) {
                        window.location.search = `?key=industry_id&filter=equals&s=${clientObj.industry_id}&client=${clientObj.id}`
                    } else {
                        window.location.search = `?client=${val}`
                    }
                }
            },
            mounted() {
                $.get('/api/industry')
                    .done((res) => {
                        this.industries = [{id: '', text: "All"}]
                            .concat(res.map((val) => ( {id: val.id, text: val.name} )));
                    })
                
                $.get('/api/client')
                    .done((res) => {
                        this.clients = [{id: '', text: "All"}]
                            .concat(res.map((val) => ( {id: val.id, text: val.name, industry_id: val.industry_id} )));
                    })
            }
        })

        let tableVue = new Vue({
            el: '#vue-tabl',
            data: {
                keywords: [],
                keywordsSelectedForApproval: [],
                loadingBottomSuggestions: false
            },
            methods: {
                findBottomSuggestions(id, keyword, ev) {
                    // this.openBottomSuggestionsModal();
                    let that = $(ev.target);

                    /** Do not search again */
                    if (that.attr('bot_suggestion_searched'))
                        return

                    that.css('opacity', '0.3').attr('bot_suggestion_searched', true)

                    this.loadingBottomSuggestions = true;
                    $.ajax({method: "GET", url: `/api/get_bottom_keywords/${id}`})
                        .done(res => {
                            toastr.success(`Successful scrapped bottom keywords for keyword ${keyword}`)
                            if (res) {
                                this.keywords = res;
                            }
                        })
                        .always(() => {this.loadingBottomSuggestions = false});
                },
                getRelatedKeywords(id, keyword, lang, ev) {
                    let that = $(ev.target);

                    /** Do not search again */
                    if (that.attr(`related_keywords_${id}_searched`))
                        return

                    that.css('opacity', '0.3').attr(`related_keywords_${id}_searched`, true)

                    $.ajax({
                        method: "GET", url: '/api/send_get_related_keywords_to_local_server',
                        data: {
                            keyword,
                            lang,
                            id
                        }
                    }).done(res => {
                        console.log(res);
                        if (res && res.length) {
                            toastr.success(`Successful scrapped RELATED KEYWORDS for keyword ${keyword}`, `${res.length} new keywords inserted.`);
                        }
                    })
                },
                /** deprecated (removed) */
                openBottomSuggestionsModal() {
                    $('#bottom_suggestions_modal').modal('show');
                },
                /** deprecated (removed) */
                closeBottomSuggestionsModal() {
                    $('#bottom_suggestions_modal').modal('hide');
                },
                approveSelectedKeywords() {
                    for (kwId of this.keywordsSelectedForApproval) {
                        $.ajax({method: "PUT", url: `/api/keywords/${kwId}`, data: {
                            "admin_accepted": 1
                        }});
                        this.closeBottomSuggestionsModal();
                    }
                }
            }
        })

        let custom_delete_vue = new Vue({
            el: "#custom_delete_modal",
            data: {
                delete_name: `All NOT approved Keywords`,
                delete_text: `This action will process all not approved keywords with soft delete action. The items will exist in the database but they will be hidden.`,
                deleting: false,
                getDeleteCount: $(`[row-field=admin_accepted]:not(:checked)`).length
            },
            methods: {
                updateDeleteCount() {
                    this.getDeleteCount = $(`[row-field=admin_accepted]:not(:checked)`).length
                },
                confirmDelete(ev) {
                    if (this.deleting) return;
                    this.deleting = true;

                    let deleteQueAjaxes = [];

                    Swal.fire({
                        showCancelButton: false,
                        showConfirmButton: false,
                        title: "Deleting...",
                        allowOutsideClick: false
                    });

                    $(`[row-field=admin_accepted]:not(:checked)`).each((i, el) => {
                        let deleteId = el.getAttribute('row-id');

                        let thisAjax = $.ajax({method: "DELETE", url: `/api/keywords/${deleteId}`}).done(res => {
                            $('#custom_delete_modal').modal('hide')
                        })
                        deleteQueAjaxes.push(thisAjax);
                    });

                    $.when(...deleteQueAjaxes).then(() => {
                        Swal.close();
                        window.location.reload();
                    });
                }
            },
            computed: {

            }
        })

        $('.custom-browse-checkbox').on('change', ev => {
            custom_delete_vue.updateDeleteCount()
        });
    </script>
@stop