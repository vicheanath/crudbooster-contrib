@php
    // The columns that get a cell, and the cells in front of them (bulk checkbox, numbering).
    $cbVisibleColumns = array_values(array_filter($columns, fn ($col) => ($col['visible'] ?? null) !== false));
    $cbLeadingCells = ($button_bulk_action ? 1 : 0) + ($show_numbering ? 1 : 0);
    $cbShowActionColumn = $button_table_action && (CRUDBooster::isUpdate() || CRUDBooster::isDelete() || CRUDBooster::isRead());
    $cbColspan = count($cbVisibleColumns) + $cbLeadingCells + ($cbShowActionColumn ? 1 : 0);
    $cbSortColumn = Request::get('filter_column');
@endphp

@push('bottom')
    <script type="text/javascript">
        $(function () {
            var $table = $('#table_dashboard');
            var $boxes = function () { return $table.find('tbody .checkbox'); };
            var lastToggled = null;

            // Selection: count, bulk button, "select all" state and row highlight.
            function updateSelection() {
                var $all = $boxes();
                var count = $all.filter(':checked').length;
                $all.each(function () {
                    $(this).closest('tr').toggleClass('cb-row-selected', this.checked);
                });
                $('#btn_bulk_action').prop('disabled', count === 0);
                $('.cb-selected-count').text(count).toggleClass('d-none', count === 0);
                $('.cb-selected-text').text(count ? '{{ cbLang('grid_selected', ['count' => '__COUNT__']) }}'.replace('__COUNT__', count) : '').toggleClass('d-none', count === 0);
                var checkall = document.getElementById('checkall');
                if (checkall) {
                    checkall.checked = count > 0 && count === $all.length;
                    checkall.indeterminate = count > 0 && count < $all.length;
                }
                $(".btn-delete-selected").toggleClass("disabled", count === 0);
            }

            // Shift-click checks or clears every row between this one and the last one toggled.
            function applyRange(box, shiftKey) {
                var $all = $boxes();
                if (shiftKey && lastToggled && lastToggled !== box) {
                    var from = $all.index(lastToggled), to = $all.index(box);
                    $all.slice(Math.min(from, to), Math.max(from, to) + 1).prop('checked', box.checked);
                }
                lastToggled = box;
            }

            $table.on('click', 'tbody .checkbox', function (e) {
                applyRange(this, e.shiftKey);
                updateSelection();
            });

            // A click anywhere on a row (not on a link, button or field) toggles its checkbox.
            $table.on('click', 'tbody tr', function (e) {
                if ($(e.target).closest('a, button, input, select, textarea, label, img, .button_action, .dropdown-menu').length) return;
                if (window.getSelection && String(window.getSelection())) return;
                var box = $(this).find('.checkbox').get(0);
                if (!box) return;
                box.checked = !box.checked;
                applyRange(box, e.shiftKey);
                $(box).trigger('change');
                updateSelection();
            });

            $("#table_dashboard #checkall").click(function () {
                var is_checked = $(this).is(":checked");
                $("#table_dashboard .checkbox").prop("checked", !is_checked).trigger("click");
                updateSelection();
            });

            updateSelection();

            $('.selected-action ul li a').click(function () {
                var name = $(this).data('name');
                $('#form-table input[name="button_name"]').val(name);
                var title = $(this).attr('title');
                var count = $boxes().filter(':checked').length;

                swal({
                        title: "{{cbLang("confirmation_title")}}",
                        text: "{{cbLang("alert_bulk_action_button")}} " + title + " (" + count + ")",
                        type: "warning",
                        showCancelButton: true,
                        confirmButtonColor: "#008D4C",
                        confirmButtonText: "{{cbLang('confirmation_yes')}}",
                        closeOnConfirm: false,
                        showLoaderOnConfirm: true
                    },
                    function () {
                        $('#form-table').submit();
                    });

            })

            $('table tbody tr .button_action a').click(function (e) {
                e.stopPropagation();
            })

            // "/" focuses the search box, as on most data tables.
            $(document).on('keydown', function (e) {
                if (e.key !== '/' || e.ctrlKey || e.metaKey || e.altKey) return;
                if ($(e.target).closest('input, textarea, select, [contenteditable], .modal').length) return;
                var $search = $('.cb-grid-search input[name=q]');
                if ($search.length) {
                    e.preventDefault();
                    $search.trigger('focus').get(0).select();
                }
            });
        });
    </script>
@endpush

<form id='form-table' method='post' action='{{CRUDBooster::mainpath("action-selected")}}'>
    <input type='hidden' name='button_name' value=''/>
    <input type='hidden' name='_token' value='{{csrf_token()}}'/>
    <div id="box-body-table" class="card-body p-0 table-responsive">
        <table id='table_dashboard' class="table table-hover table-striped align-middle mb-0 cb-grid">
            <thead>
            <tr>
                @if($button_bulk_action)
                    <th class="cb-grid-check"><input type='checkbox' id='checkall' class="form-check-input" aria-label="Select all rows"/></th>
                @endif
                @if($show_numbering)
                    <th class="cb-grid-number">{{ cbLang('no') }}</th>
                @endif
                <?php
                foreach ($cbVisibleColumns as $col) {
                    $colname = $col['label'];
                    $field = $col['field_with'];
                    $width = (isset($col['width'])) ? $col['width'] : "auto";
                    $style = (isset($col['style'])) ? $col['style'] : "";
                    $sorting = $cbSortColumn[$field]['sorting'] ?? null;

                    if ($sorting == 'asc') {
                        $url = CRUDBooster::urlFilterColumn($field, 'sorting', 'desc');
                        $link = "<a href='$url' class='cb-sort is-sorted' title='Click to sort descending'>$colname <i class='fa fa-sort-asc'></i></a>";
                        $aria = 'ascending';
                    } elseif ($sorting == 'desc') {
                        $url = CRUDBooster::urlFilterColumn($field, 'sorting', 'asc');
                        $link = "<a href='$url' class='cb-sort is-sorted' title='Click to sort ascending'>$colname <i class='fa fa-sort-desc'></i></a>";
                        $aria = 'descending';
                    } else {
                        $url = CRUDBooster::urlFilterColumn($field, 'sorting', 'asc');
                        $link = "<a href='$url' class='cb-sort' title='Click to sort ascending'>$colname <i class='fa fa-sort'></i></a>";
                        $aria = 'none';
                    }

                    echo "<th width='$width' $style aria-sort='$aria'>$link</th>";
                }
                ?>

                @if($cbShowActionColumn)
                    <th width='{{ isset($button_action_width)? $button_action_width :"auto"}}' class="text-end">{{cbLang("action_label")}}</th>
                @endif
            </tr>
            </thead>
            <tbody>
            @if(count($result)==0)
                <tr class="cb-grid-empty">
                    <td colspan='{{ $cbColspan }}'>
                        <div class="text-center text-body-secondary py-5">
                            <i class='fa fa-inbox fa-3x mb-3 d-block opacity-50'></i>
                            <div class="fw-semibold">{{cbLang("table_data_not_found")}}</div>
                            @if(! empty($cbClearAllUrl))
                                <div class="small mt-1">{{ cbLang('grid_no_results_hint') }}</div>
                                <a href="{{ $cbClearAllUrl }}" class="btn btn-sm btn-default mt-3"><i class="fa fa-times"></i> {{ cbLang('grid_clear_filters') }}</a>
                            @elseif($button_add && CRUDBooster::isCreate())
                                <a href="{{ CRUDBooster::mainpath('add').'?return_url='.urlencode(Request::fullUrl()).'&parent_id='.g('parent_id').'&parent_field='.$parent_field }}"
                                   class="btn btn-sm btn-success mt-3"><i class="fa fa-plus-circle"></i> {{cbLang('action_add_data')}}</a>
                            @endif
                        </div>
                    </td>
                </tr>
            @endif

            @foreach($html_contents['html'] as $i=>$hc)

                @if($table_row_color)
                    <?php $tr_color = NULL;?>
                    @foreach($table_row_color as $trc)
                        <?php
                        $query = $trc['condition'];
                        $color = $trc['color'];
                        $row = $html_contents['data'][$i];
                        foreach ($row as $key => $val) {
                            $query = str_replace("[".$key."]", '"'.$val.'"', $query);
                        }

                        @eval("if($query) {
                                          \$tr_color = \$color;
                                      }");
                        ?>
                    @endforeach
                    <?php echo "<tr class='$tr_color'>";?>
                @else
                    <tr>
                @endif

                    @foreach($hc as $j=>$h)
                        @php
                            $cbCellColumn = $cbVisibleColumns[$j - $cbLeadingCells] ?? null;
                            $cbCellClass = match (true) {
                                $button_bulk_action && $j === 0 => 'cb-grid-check',
                                $show_numbering && $j === $cbLeadingCells - 1 => 'cb-grid-number',
                                $button_table_action && $j === count($hc) - 1 => 'cb-grid-actions text-end',
                                default => '',
                            };
                        @endphp
                        <td class="{{ $cbCellClass }}" {!! ($cbCellColumn && $cbCellClass === '') ? ($cbCellColumn['style'] ?? '') : '' !!}>{!! $h !!}</td>
                    @endforeach
                </tr>
            @endforeach
            </tbody>
        </table>
    </div>
</form><!--END FORM TABLE-->

<?php
$from = $result->count() ? ($result->perPage() * $result->currentPage() - $result->perPage() + 1) : 0;
$to = $result->perPage() * $result->currentPage() - $result->perPage() + $result->count();
$total = $result->total();
?>
<div class="card-footer cb-grid-footer d-flex flex-wrap align-items-center justify-content-between gap-2">
    <div class="small text-body-secondary">{{ cbLang("filter_rows_total") }}
        : {{ $from }} {{ cbLang("filter_rows_to") }} {{ $to }} {{ cbLang("filter_rows_of") }} {{ $total }}</div>
    <div>{!! urldecode(str_replace("/?","?",$result->appends(Request::all())->render('pagination::bootstrap-4'))) !!}</div>
</div>

@if($columns)
    @push('bottom')
        <script>
            $(function () {
                $('.btn-filter-data').click(function () {
                    $('#filter-data').modal('show');
                })

                $('.btn-export-data').click(function () {
                    $('#export-data').modal('show');
                })

                var toggle_advanced_report_boolean = 1;
                $(".toggle_advanced_report").click(function () {

                    if (toggle_advanced_report_boolean == 1) {
                        $("#advanced_export").slideDown();
                        $(this).html("<i class='fa fa-minus-square-o'></i> {{cbLang('export_dialog_show_advanced')}}");
                        toggle_advanced_report_boolean = 0;
                    } else {
                        $("#advanced_export").slideUp();
                        $(this).html("<i class='fa fa-plus-square-o'></i> {{cbLang('export_dialog_show_advanced')}}");
                        toggle_advanced_report_boolean = 1;
                    }

                })

                // Export dialog: tick or clear every column at once.
                $('.cb-export-columns-all').click(function () {
                    $('#export-data input[name="columns[]"]').prop('checked', $(this).data('check'));
                })

                $('#btn_advanced_filter').click(function () {
                    $('#advanced_filter_modal').modal('show');
                })

                $(".filter-combo").change(function () {
                    var n = $(this).val();
                    var p = $(this).parents('.row-filter-combo');
                    var type_data = $(this).attr('data-type');
                    var filter_value = p.find('.filter-value');

                    p.find('.between-group').hide();
                    p.find('.between-group').find('input').prop('disabled', true);
                    filter_value.val('').show().focus();
                    switch (n) {
                        default:
                            filter_value.removeAttr('placeholder').val('').prop('disabled', true);
                            p.find('.between-group').find('input').prop('disabled', true);
                            break;
                        case 'like':
                        case 'not like':
                            filter_value.attr('placeholder', '{{cbLang("filter_eg")}} : {{cbLang("filter_lorem_ipsum")}}').prop('disabled', false);
                            break;
                        case 'asc':
                            filter_value.prop('disabled', true).attr('placeholder', '{{cbLang("filter_sort_ascending")}}');
                            break;
                        case 'desc':
                            filter_value.prop('disabled', true).attr('placeholder', '{{cbLang("filter_sort_descending")}}');
                            break;
                        case '=':
                            filter_value.prop('disabled', false).attr('placeholder', '{{cbLang("filter_eg")}} : {{cbLang("filter_lorem_ipsum")}}');
                            break;
                        case '>=':
                            filter_value.prop('disabled', false).attr('placeholder', '{{cbLang("filter_eg")}} : 1000');
                            break;
                        case '<=':
                            filter_value.prop('disabled', false).attr('placeholder', '{{cbLang("filter_eg")}} : 1000');
                            break;
                        case '>':
                            filter_value.prop('disabled', false).attr('placeholder', '{{cbLang("filter_eg")}} : 1000');
                            break;
                        case '<':
                            filter_value.prop('disabled', false).attr('placeholder', '{{cbLang("filter_eg")}} : 1000');
                            break;
                        case '!=':
                            filter_value.prop('disabled', false).attr('placeholder', '{{cbLang("filter_eg")}} : {{cbLang("filter_lorem_ipsum")}}');
                            break;
                        case 'in':
                            filter_value.prop('disabled', false).attr('placeholder', '{{cbLang("filter_eg")}} : {{cbLang("filter_lorem_ipsum_dolor_sit")}}');
                            break;
                        case 'not in':
                            filter_value.prop('disabled', false).attr('placeholder', '{{cbLang("filter_eg")}} : {{cbLang("filter_lorem_ipsum_dolor_sit")}}');
                            break;
                        case 'between':
                            filter_value.val('').hide();
                            p.find('.between-group input').prop('disabled', false);
                            p.find('.between-group').show().focus();
                            p.find('.filter-value-between').prop('disabled', false);
                            break;
                    }
                })

                /* Remove disabled when reload page and input value is filled */
                $(".filter-value").each(function () {
                    var v = $(this).val();
                    if (v != '') $(this).prop('disabled', false);
                })

            })
        </script>

        <!-- MODAL FOR SORTING DATA-->
        <div class="modal fade" tabindex="-1" role="dialog" id='advanced_filter_modal' aria-labelledby="advanced_filter_title">
            <div class="modal-dialog modal-lg modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="advanced_filter_title"><i class='fa fa-filter'></i> {{cbLang("filter_dialog_title")}}</h5>
                        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
                    </div>
                    <form method='get' action=''>
                        <div class="modal-body">
                            <?php foreach($columns as $key => $col):?>
                            <?php if (isset($col['image']) || isset($col['download']) || $col['visible'] === FALSE) continue;?>
                            <?php
                            // Compare type names the same way on every database: SQLite says INTEGER, numeric or tinyint(1).
                            $col['type_data'] = strtolower(preg_replace('/\(.*$/', '', (string) $col['type_data']));
                            if (in_array($col['type_data'], ['numeric', 'real', 'number'])) $col['type_data'] = 'decimal';
                            ?>

                            <div class='cb-filter-row'>

                                <div class='row-filter-combo row g-2 align-items-center'>

                                    <div class="col-md-3">
                                        <label class="fw-semibold mb-0">{{$col['label']}}</label>
                                    </div>

                                    <div class='col-md-3 col-sm-6'>
                                        <select name='filter_column[{{$col["field_with"]}}][type]' data-type='{{$col["type_data"]}}'
                                                class="filter-combo form-select form-select-sm" aria-label="{{$col['label']}} {{cbLang("filter_select_operator_type")}}">
                                            <option value=''>** {{cbLang("filter_select_operator_type")}}</option>
                                            @if(in_array($col['type_data'],['string','varchar','text','char']))
                                                <option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'like')?"selected":"" }} value='like'>{{cbLang("filter_like")}}</option> @endif
                                            @if(in_array($col['type_data'],['string','varchar','text','char']))
                                                <option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'not like')?"selected":"" }} value='not like'>{{cbLang("filter_not_like")}}</option>@endif

                                            <option typeallow='all'
                                                    {{ (CRUDBooster::getTypeFilter($col["field_with"]) == '=')?"selected":"" }} value='='>{{cbLang("filter_equal_to")}}</option>
                                            @if(in_array($col['type_data'],['int','integer','smallint','tinyint','mediumint','bigint','double','float','decimal','time']))
                                                <option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == '>=')?"selected":"" }} value='>='>{{cbLang("filter_greater_than_or_equal")}}</option>@endif
                                            @if(in_array($col['type_data'],['int','integer','smallint','tinyint','mediumint','bigint','double','float','decimal','time']))
                                                <option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == '<=')?"selected":"" }} value='<='>{{cbLang("filter_less_than_or_equal")}}</option>@endif
                                            @if(in_array($col['type_data'],['int','integer','smallint','tinyint','mediumint','bigint','double','float','decimal','time']))
                                                <option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == '<')?"selected":"" }} value='<'>{{cbLang("filter_less_than")}}</option>@endif
                                            @if(in_array($col['type_data'],['int','integer','smallint','tinyint','mediumint','bigint','double','float','decimal','time']))
                                                <option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == '>')?"selected":"" }} value='>'>{{cbLang("filter_greater_than")}}</option>@endif
                                            <option typeallow='all'
                                                    {{ (CRUDBooster::getTypeFilter($col["field_with"]) == '!=')?"selected":"" }} value='!='>{{cbLang("filter_not_equal_to")}}</option>
                                            <option typeallow='all'
                                                    {{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'in')?"selected":"" }} value='in'>{{cbLang("filter_in")}}</option>
                                            <option typeallow='all'
                                                    {{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'not in')?"selected":"" }} value='not in'>{{cbLang("filter_not_in")}}</option>
                                            @if(in_array($col['type_data'],['date','time','datetime','int','integer','smallint','tinyint','mediumint','bigint','double','float','decimal','timestamp']))
                                                <option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'between')?"selected":"" }} value='between'>{{cbLang("filter_between")}}</option>@endif
                                            <option {{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'empty')?"selected":"" }} value='empty'>{{cbLang("filter_empty_or_null")}}</option>
                                        </select>
                                    </div>


                                    <div class='col-md-4 col-sm-6'>
                                        <input type='text' class='filter-value form-control form-control-sm'
                                               style="{{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'between')?"display:none":"display:block"}}"
                                               disabled name='filter_column[{{$col["field_with"]}}][value]' aria-label="{{$col['label']}}"
                                               value='{{ (!is_array(CRUDBooster::getValueFilter($col["field_with"])))?CRUDBooster::getValueFilter($col["field_with"]):"" }}'>

                                        <div class='row g-1 between-group'
                                             style="{{ (CRUDBooster::getTypeFilter($col["field_with"]) == 'between')?"display:flex":"display:none" }}">
                                            <div class='col-6'>
                                                <div class='input-group input-group-sm {{ ($col["type_data"] == "time")?"bootstrap-timepicker":"" }}'>
                                                    <span class="input-group-text">{{cbLang("filter_from")}}</span>
                                                    <input
                                                            {{ (CRUDBooster::getTypeFilter($col["field_with"]) != 'between')?"disabled":"" }}
                                                            type='text'
                                                            class='filter-value-between form-control {{ in_array($col["type_data"],["date","datetime","timestamp"]) ? "datepicker" : ((in_array($col["type_data"],["time"])) ? "timepicker" : "") }}'
                                                            {{ in_array($col["type_data"],["date","datetime","timestamp","time"]) ? "readonly" : "" }}
                                                            placeholder='{{$col["label"]}} {{cbLang("filter_from")}}'
                                                            name='filter_column[{{$col["field_with"]}}][value][]'
                                                            value='<?php
                                                                $value = CRUDBooster::getValueFilter($col["field_with"]);
                                                                echo (CRUDBooster::getTypeFilter($col["field_with"]) == 'between') ? $value[0] : "";
                                                            ?>'>
                                                </div>
                                            </div>
                                            <div class='col-6'>
                                                <div class='input-group input-group-sm {{ ($col["type_data"] == "time")?"bootstrap-timepicker":"" }}'>
                                                    <span class="input-group-text">{{cbLang("filter_to")}}</span>
                                                    <input
                                                            {{ (CRUDBooster::getTypeFilter($col["field_with"]) != 'between')?"disabled":"" }}
                                                            type='text'
                                                            class='filter-value-between form-control {{ in_array($col["type_data"],["date","datetime","timestamp"]) ? "datepicker" : (in_array($col["type_data"],["time"]) ? "timepicker" : "" )}}'
                                                            {{ in_array($col["type_data"],["date","datetime","timestamp","time"]) ? "readonly": "" }}
                                                            placeholder='{{$col["label"]}} {{cbLang("filter_to")}}'
                                                            name='filter_column[{{$col["field_with"]}}][value][]'
                                                            value='<?php
                                                                $value = CRUDBooster::getValueFilter($col["field_with"]);
                                                                echo (CRUDBooster::getTypeFilter($col["field_with"]) == 'between') ? $value[1] : "";
                                                            ?>'>
                                                </div>
                                            </div>
                                        </div>
                                    </div>


                                    <div class='col-md-2'>
                                        <select class='form-select form-select-sm' name='filter_column[{{$col["field_with"]}}][sorting]' aria-label="{{$col['label']}} {{cbLang("filter_sorting")}}">
                                            <option value=''>{{cbLang("filter_sorting")}}</option>
                                            <option {{ (CRUDBooster::getSortingFilter($col["field_with"]) == 'asc')?"selected":"" }} value='asc'>{{cbLang("filter_ascending")}}</option>
                                            <option {{ (CRUDBooster::getSortingFilter($col["field_with"]) == 'desc')?"selected":"" }} value='desc'>{{cbLang("filter_descending")}}</option>
                                        </select>
                                    </div>

                                </div>

                            </div>
                            <?php endforeach;?>

                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-default" type="button" data-bs-dismiss="modal">{{cbLang("button_close")}}</button>
                            <button class="btn btn-default btn-reset" type="reset"
                                    onclick='location.href="{{Request::get("lasturl")}}"'>{{cbLang("button_reset")}}</button>
                            <button class="btn btn-primary btn-submit" type="submit">{{cbLang("button_submit")}}</button>
                        </div>
                        {!! CRUDBooster::getUrlParameters(['filter_column','lasturl']) !!}
                        <input type="hidden" name="lasturl" value="{{Request::get('lasturl')?Request::get('lasturl'):Request::fullUrl()}}">
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
        </div>

        <!-- MODAL FOR EXPORT DATA-->
        <div class="modal fade" tabindex="-1" role="dialog" id='export-data' aria-labelledby="export_data_title">
            <div class="modal-dialog modal-dialog-scrollable">
                <div class="modal-content">
                    <div class="modal-header">
                        <h5 class="modal-title" id="export_data_title"><i class='fa fa-download'></i> {{cbLang("export_dialog_title")}}</h5>
                        <button class="btn-close" aria-label="Close" type="button" data-bs-dismiss="modal"></button>
                    </div>

                    <form method='post' target="_blank" action='{{ CRUDBooster::mainpath("export-data?t=".time()) }}'>
                        <input type="hidden" name="_token" value="{{ csrf_token() }}">
                        {!! CRUDBooster::getUrlParameters() !!}
                        <div class="modal-body">
                            <div class="mb-3">
                                <label class="form-label" for="export_filename">{{cbLang("export_dialog_filename")}}</label>
                                <input type='text' name='filename' id="export_filename" class='form-control' required value='Report {{ $module_name }} - {{date("d M Y")}}'/>
                                <div class='form-text'>
                                    {{cbLang("export_dialog_help_filename")}}
                                </div>
                            </div>

                            <div class="row g-3 mb-3">
                                <div class="col-sm-6">
                                    <label class="form-label" for="export_fileformat">{{cbLang("export_dialog_format_export")}}</label>
                                    <select name='fileformat' id="export_fileformat" class='form-select'>
                                        @foreach(app(\crocodicstudio\crudbooster\export\ExporterRegistry::class)->labels() as $format => $formatLabel)
                                            <option value='{{$format}}'>{{$formatLabel}}</option>
                                        @endforeach
                                    </select>
                                </div>
                                <div class="col-sm-6">
                                    <label class="form-label" for="export_limit">{{cbLang("export_dialog_maxdata")}}</label>
                                    <input type='number' name='limit' id="export_limit" class='form-control' required value='100' max="100000" min="1"/>
                                </div>
                                <div class="col-12 form-text mt-1">{{cbLang("export_dialog_help_maxdata")}}</div>
                            </div>

                            <div class='mb-3'>
                                <div class="d-flex align-items-center justify-content-between mb-1">
                                    <label class="form-label mb-0">{{cbLang("export_dialog_columns")}}</label>
                                    <div class="small">
                                        <a href="javascript:void(0)" class="cb-export-columns-all" data-check="true">{{ cbLang('export_select_all') }}</a>
                                        &middot;
                                        <a href="javascript:void(0)" class="cb-export-columns-all" data-check="false">{{ cbLang('export_select_none') }}</a>
                                    </div>
                                </div>
                                <div class="row row-cols-2 g-1">
                                    @foreach($columns as $col)
                                        <div class="col">
                                            <div class='form-check'>
                                                <input type='checkbox' class="form-check-input" checked name='columns[]' id="export_col_{{ $loop->index }}"
                                                       value='{{$col["name"]}}'>
                                                <label class="form-check-label" for="export_col_{{ $loop->index }}">{{$col["label"]}}</label>
                                            </div>
                                        </div>
                                    @endforeach
                                </div>
                            </div>

                            <p class="mb-2"><a href='javascript:void(0)' class='toggle_advanced_report'><i
                                            class='fa fa-plus-square-o'></i> {{cbLang("export_dialog_show_advanced")}}</a></p>

                            <div id='advanced_export' style='display: none'>
                                <div class="row g-3">
                                    <div class="col-sm-6">
                                        <label class="form-label" for="export_page_size">{{cbLang("export_dialog_page_size")}}</label>
                                        <select class='form-select' name='page_size' id="export_page_size">
                                            <option <?=($setting->default_paper_size == 'Letter') ? "selected" : ""?> value='Letter'>Letter</option>
                                            <option <?=($setting->default_paper_size == 'Legal') ? "selected" : ""?> value='Legal'>Legal</option>
                                            <option <?=($setting->default_paper_size == 'Ledger') ? "selected" : ""?> value='Ledger'>Ledger</option>
                                            <?php for($i = 0;$i <= 8;$i++):
                                            $select = ($setting->default_paper_size == 'A'.$i) ? "selected" : "";
                                            ?>
                                            <option <?=$select?> value='A{{$i}}'>A{{$i}}</option>
                                            <?php endfor;?>

                                            <?php for($i = 0;$i <= 10;$i++):
                                            $select = ($setting->default_paper_size == 'B'.$i) ? "selected" : "";
                                            ?>
                                            <option <?=$select?> value='B{{$i}}'>B{{$i}}</option>
                                            <?php endfor;?>
                                        </select>
                                        <div class='form-check mt-1'>
                                            <input type='checkbox' class="form-check-input" name='default_paper_size' id="export_default_paper_size" value='1'/>
                                            <label class="form-check-label small" for="export_default_paper_size">{{cbLang("export_dialog_set_default")}}</label>
                                        </div>
                                    </div>

                                    <div class="col-sm-6">
                                        <label class="form-label" for="export_page_orientation">{{cbLang("export_dialog_page_orientation")}}</label>
                                        <select class='form-select' name='page_orientation' id="export_page_orientation">
                                            <option value='potrait'>Potrait</option>
                                            <option value='landscape'>Landscape</option>
                                        </select>
                                    </div>
                                </div>
                            </div>

                        </div>
                        <div class="modal-footer">
                            <button class="btn btn-default" type="button" data-bs-dismiss="modal">{{cbLang("button_close")}}</button>
                            <button class="btn btn-primary btn-submit" type="submit"><i class='fa fa-download'></i> {{cbLang('button_submit')}}</button>
                        </div>
                    </form>
                </div>
                <!-- /.modal-content -->
            </div>
        </div>
    @endpush
@endif
