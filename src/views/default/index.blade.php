@extends('crudbooster::admin_template')

@section('content')

    @if($index_statistic)
        <div id='box-statistic' class='row'>
            @foreach($index_statistic as $stat)
                <div class="{{ ($stat['width'])?:'col-sm-3' }}">
                    <div class="small-box bg-{{ $stat['color']?:'red' }}">
                        <div class="inner">
                            <h3>{{ $stat['count'] }}</h3>
                            <p>{{ $stat['label'] }}</p>
                        </div>
                        <div class="icon">
                            <i class="{{ $stat['icon'] }}"></i>
                        </div>
                    </div>
                </div>
            @endforeach
        </div>
    @endif

    @if(!is_null($pre_index_html) && !empty($pre_index_html))
        {!! $pre_index_html !!}
    @endif


    @if(g('return_url'))
        <p><a href='{{g("return_url")}}'><i class='fa fa-chevron-circle-{{ cbLang('left') }}'></i>
                &nbsp; {{cbLang('form_back_to_list',['module'=>urldecode(g('label'))])}}</a></p>
    @endif

    @if($parent_table)
        <div class="card mb-3">
            <div class="card-header">
                <h3 class="card-title fs-6 mb-0"><i class='fa fa-bars'></i> {{ ucwords(urldecode(g('label'))) }}</h3>
            </div>
            <div class="card-body p-0 table-responsive">
                <table class='table table-sm mb-0'>
                    <tbody>
                    @foreach(explode(',',urldecode(g('parent_columns'))) as $c)
                        <tr>
                            <th class="ps-3" style="width: 25%">
                                @if(urldecode(g('parent_columns_alias')))
                                    {{explode(',',urldecode(g('parent_columns_alias')))[$loop->index]}}
                                @else
                                    {{  ucwords(str_replace('_',' ',$c)) }}
                                @endif
                            </th>
                            <td> {{ $parent_table->$c }}</td>
                        </tr>
                    @endforeach
                    </tbody>
                </table>
            </div>
        </div>
    @endif

    @php
        // Active search, filters and sorting, shown as chips that each remove one of them.
        $cbQuery = Request::query();
        $cbChipUrl = function (callable $change) use ($cbQuery) {
            $params = $cbQuery;
            $change($params);
            unset($params['page'], $params['lasturl']);
            $query = http_build_query($params);
            return Request::url().($query ? '?'.$query : '');
        };
        $cbOperators = [
            'like' => cbLang('filter_like'), 'not like' => cbLang('filter_not_like'), '=' => '=', '!=' => '≠',
            '>=' => '≥', '<=' => '≤', '>' => '>', '<' => '<', 'in' => cbLang('filter_in'), 'not in' => cbLang('filter_not_in'),
            'between' => cbLang('filter_between'), 'empty' => cbLang('filter_empty_or_null'),
        ];
        $cbLabels = collect($columns)->pluck('label', 'field_with');
        $cbChips = [];
        if (Request::get('q')) {
            $cbChips[] = ['label' => cbLang('filter_search').': '.Request::get('q'), 'url' => $cbChipUrl(function (&$p) { unset($p['q']); })];
        }
        foreach ((array) Request::get('filter_column') as $field => $filter) {
            $label = $cbLabels[$field] ?? $field;
            $type = $filter['type'] ?? '';
            $value = $filter['value'] ?? '';
            if ($type && ($type == 'empty' || (is_array($value) ? array_filter($value, 'strlen') : strlen($value)))) {
                $shown = is_array($value) ? implode(' – ', $value) : $value;
                $cbChips[] = [
                    'label' => trim($label.' '.($cbOperators[$type] ?? $type).' '.($type == 'empty' ? '' : $shown)),
                    'url' => $cbChipUrl(function (&$p) use ($field) { unset($p['filter_column'][$field]['type'], $p['filter_column'][$field]['value']); }),
                ];
            }
            if (! empty($filter['sorting'])) {
                $cbChips[] = [
                    'label' => cbLang('grid_sorted_by').' '.$label.' '.($filter['sorting'] == 'desc' ? '↓' : '↑'),
                    'url' => $cbChipUrl(function (&$p) use ($field) { unset($p['filter_column'][$field]['sorting']); }),
                ];
            }
        }
        $cbActiveFilters = collect((array) Request::get('filter_column'))->filter(fn ($f) => ! empty($f['type']) || ! empty($f['sorting']))->count();
        $cbClearAllUrl = $cbChipUrl(function (&$p) { unset($p['q'], $p['filter_column']); });
        $cbHasBulk = $button_bulk_action && (($button_delete && CRUDBooster::isDelete()) || $button_selected);
    @endphp

    <div class="card cb-grid-card">
        <div class="card-header cb-grid-toolbar">
            <div class="d-flex flex-wrap align-items-center gap-2">
                @if($cbHasBulk)
                    <div class="selected-action dropdown">
                        <button type="button" class="btn btn-default btn-sm dropdown-toggle" data-bs-toggle="dropdown" aria-expanded="false"
                                id="btn_bulk_action" disabled>
                            <i class='fa fa-check-square-o'></i> {{cbLang("button_selected_action")}}
                            <span class="badge text-bg-primary ms-1 cb-selected-count d-none">0</span>
                        </button>
                        <ul class="dropdown-menu">
                            @if($button_delete && CRUDBooster::isDelete())
                                <li><a class="dropdown-item text-danger" href="javascript:void(0)" data-name='delete' title='{{cbLang('action_delete_selected')}}'><i
                                                class="fa fa-trash fa-fw"></i> {{cbLang('action_delete_selected')}}</a></li>
                            @endif

                            @if($button_selected)
                                @foreach($button_selected as $button)
                                    <li><a class="dropdown-item" href="javascript:void(0)" data-name='{{$button["name"]}}' title='{{$button["label"]}}'><i
                                                    class="fa fa-{{$button['icon']}} fa-fw"></i> {{$button['label']}}</a></li>
                                @endforeach
                            @endif

                        </ul><!--end-dropdown-menu-->
                    </div><!--end-selected-action-->
                    <span class="text-body-secondary small cb-selected-text d-none"></span>
                @endif

                <div class="card-tools ms-auto d-flex flex-wrap align-items-center gap-2">

                    <form method='get' class="cb-grid-search" action='{{Request::url()}}' role="search">
                        <div class="input-group input-group-sm">
                            <span class="input-group-text bg-transparent"><i class="fa fa-search"></i></span>
                            <input type="search" name="q" value="{{ Request::get('q') }}" class="form-control"
                                   placeholder="{{cbLang('filter_search')}}" aria-label="{{cbLang('filter_search')}}" title="{{cbLang('filter_search')}} ( / )"/>
                            {!! CRUDBooster::getUrlParameters(['q']) !!}
                            @if(Request::get('q'))
                                <?php
                                $parameters = Request::all();
                                unset($parameters['q']);
                                $cbResetQuery = urldecode(http_build_query($parameters));
                                $cbResetQuery = ($cbResetQuery) ? "?".$cbResetQuery : "";
                                ?>
                                <button type='button' onclick='location.href="{{ CRUDBooster::mainpath().$cbResetQuery}}"'
                                        title="{{cbLang('button_reset')}}" aria-label="{{cbLang('button_reset')}}" class='btn btn-default'><i class='fa fa-times'></i></button>
                            @endif
                        </div>
                    </form>

                    @if($button_filter)
                        <a href="javascript:void(0)" id='btn_advanced_filter' data-url-parameter='{{$build_query}}'
                           title='{{cbLang('filter_dialog_title')}}' class="btn btn-sm btn-default {{(Request::get('filter_column'))?'active':''}}">
                            <i class="fa fa-filter"></i> {{cbLang("button_filter")}}
                            @if($cbActiveFilters)
                                <span class="badge text-bg-primary ms-1">{{ $cbActiveFilters }}</span>
                            @endif
                        </a>
                    @endif

                    <form method='get' id='form-limit-paging' class="d-flex align-items-center gap-1" action='{{Request::url()}}'>
                        {!! CRUDBooster::getUrlParameters(['limit']) !!}
                        <label for="cb-grid-limit" class="small text-body-secondary fw-normal mb-0 text-nowrap">{{ cbLang('grid_show') }}</label>
                        <select onchange="$('#form-limit-paging').submit()" name='limit' id="cb-grid-limit" class='form-select form-select-sm'>
                            @foreach([5, 10, 20, 25, 50, 100, 200] as $cbLimit)
                                <option {{($limit==$cbLimit)?'selected':''}} value='{{ $cbLimit }}'>{{ $cbLimit }}</option>
                            @endforeach
                        </select>
                    </form>

                </div>
            </div>

            @if($cbChips)
                <div class="cb-grid-chips d-flex flex-wrap align-items-center gap-2 mt-2">
                    @foreach($cbChips as $chip)
                        <a href="{{ $chip['url'] }}" class="badge rounded-pill text-bg-light border text-decoration-none fw-normal cb-chip"
                           title="{{ cbLang('button_reset') }}">
                            {{ $chip['label'] }} <i class="fa fa-times ms-1"></i>
                        </a>
                    @endforeach
                    @if(count($cbChips) > 1)
                        <a href="{{ $cbClearAllUrl }}" class="small">{{ cbLang('grid_clear_filters') }}</a>
                    @endif
                </div>
            @endif
        </div>
        @include("crudbooster::default.table", ['cbClearAllUrl' => $cbChips ? $cbClearAllUrl : null])
    </div>

    @if(!is_null($post_index_html) && !empty($post_index_html))
        {!! $post_index_html !!}
    @endif

@endsection
