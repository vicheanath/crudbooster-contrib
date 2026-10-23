<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', App::getLocale()) }}" data-bs-theme="light">
<head>
    <meta charset="UTF-8">
    <meta content='width=device-width, initial-scale=1' name='viewport'>
    <title>API Documentation</title>
    <!-- AdminLTE 4 (Bootstrap 5.3), Font Awesome and the Bootstrap 3 compatibility layer -->
    @include('crudbooster::partials.theme_styles')
    <!-- jQuery and Bootstrap 5; the page script below uses jQuery -->
    @include('crudbooster::partials.theme_scripts')
</head>
<body>
<div class="container">
    <div class="page-header">
        <h1>API Documentation {{ get_setting("appname") }}</h1>
    </div>

    <div class='card'>

        <div class='card-body'>

            <style>
                .table-api tbody tr td a {
                    color: #db0e00;
                    font-family: arial;
                }
            </style>

            <script>
                $(function () {
                    $(".link_name_api").click(function () {
                        $(".detail_api").slideUp();
                        $(this).parent("td").find(".detail_api").slideDown();
                    })
                    $(".selected_text").each(function () {
                        var n = $(this).text();
                        if (n.indexOf('api_') == 0) {
                            $(this).attr('class', 'selected_text text-danger');
                        }
                    })
                })
            </script>

            <div class='form-group'>
                <label>API BASE URL</label>
                <input type='text' readonly class='form-control' title='Hanya klik dan otomatis copy to clipboard (kecuali Safari)'
                       onClick="this.setSelectionRange(0, this.value.length); document.execCommand('copy');" value='{{url('api')}}'/>
            </div>
            <table class='table table-striped table-api table-bordered'>
                <thead>
                <tr class='table-info'>
                    <th width='2%'>No</th>
                    <th>API Name
                        <span class='float-end'>
                      <a class='btn btn-xs btn-warning' target="_blank" href='{{url("download-documentation-postman")}}'>Export For POSTMAN <sup>Beta</sup></a>
                    </span>
                    </th>
                </tr>
                </thead>
                <tbody>
                <?php $no = 0;?>
                <tr>
                    <td>{{ ++$no  }}</td>
                    <td>
                        <a href='javascript:void(0)' title='API Authentication' style='color:red !important;' class='link_name_api'>Authentication (Request Token)</a> &nbsp;
                        <div class='detail_api' style='display:none'>
                            <table class='table table-bordered'>
                                <tr>
                                    <td width='12%'><strong>URL</strong></td>
                                    <td><input title='Click and copied !' type='text' class='form-control' readonly
                                               onClick="this.setSelectionRange(0, this.value.length); document.execCommand('copy');"
                                               value="/get-token"/></td>
                                </tr>
                                <tr>
                                    <td><strong>METHOD</strong></td>
                                    <td>POST</td>
                                </tr>
                                <tr>
                                    <td><strong>PARAMETER</strong></td>
                                    <td>
                                        <table class='table table-bordered table-hover'>
                                            <thead>
                                            <tr class='table-active'>
                                                <th width="3%">No</th>
                                                <th width="5%">Type</th>
                                                <th>Parameter Names</th>
                                                <th>Description / Validate / Rule</th>
                                                <th>Mandatory</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <tr>
                                                <td>1</td>
                                                <td width="5%"><em>String</em></td>
                                                <td>secret</td>
                                                <td></td>
                                                <td>Yes</td>
                                            </tr>
                                            </tbody>
                                        </table>
                                    </td>
                                </tr>
                                <tr>
                                    <td><strong>RESPONSE</strong></td>
                                    <td>
                                        <table class='table table-bordered table-hover'>
                                            <thead>
                                            <tr class='table-active'>
                                                <th width="3%">No</th>
                                                <th width="5%">Type</th>
                                                <th>Response Names</th>
                                            </tr>
                                            </thead>
                                            <tbody>
                                            <?php $i = 1;?>
                                            <tr>
                                                <td>1</td>
                                                <td><em>integer</em></td>
                                                <td>api_status</td>
                                            </tr>
                                            <tr>
                                                <td>2</td>
                                                <td><em>string</em></td>
                                                <td>api_message</td>
                                            </tr>
                                            <tr>
                                                <td>3</td>
                                                <td><em>object</em></td>
                                                <td>data</td>
                                            </tr>
                                            <tr>
                                                <td>3.1</td>
                                                <td><em>string</em></td>
                                                <td>access_token</td>
                                            </tr>
                                            <tr>
                                                <td>3.2</td>
                                                <td><em>integer</em></td>
                                                <td>expiry</td>
                                            </tr>
                                            </tbody>
                                        </table>

                                    </td>
                                </tr>
                            </table>
                        </div>
                    </td>
                </tr>
                @foreach($apis as $api)
                    <?php
                    $parameters = $api->parameterList();
                    $responses = $api->responseList();
                    ?>
                    <tr>
                        <td><?= ++$no;?></td>
                        <td>
                            <a href='javascript:void(0)' title='API {{$ac->nama}}' style='color:#009fe3' class='link_name_api'><?=$api->nama;?></a> &nbsp;
                            <div class='detail_api' style='display:none'>
                                <table class='table table-bordered'>
                                    <tr>
                                        <td width='12%'><strong>URL</strong></td>
                                        <td><input title='Click and copied !' type='text' class='form-control' readonly
                                                   onClick="this.setSelectionRange(0, this.value.length); document.execCommand('copy');"
                                                   value="/{{$api->permalink}}"/></td>
                                    </tr>
                                    <tr>
                                        <td><strong>METHOD</strong></td>
                                        <td>{{strtoupper($api->method_type)}}</td>
                                    </tr>
                                    <tr>
                                        <td><strong>HEADERS</strong></td>
                                        <td>
                                            <table class="table table-bordered table-hover">
                                                <thead>
                                                <tr class="table-active">
                                                    <th>Name</th>
                                                    <th>Value</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <tr>
                                                    <td>Authorization</td>
                                                    <td>Bearer <span style="color:red">{access_token}</span></td>
                                                </tr>
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>PARAMETER</strong></td>
                                        <td>
                                            <table class='table table-bordered table-hover'>
                                                <thead>
                                                <tr class='table-active'>
                                                    <th width="3%">No</th>
                                                    <th width="5%">Type</th>
                                                    <th>Parameter Names</th>
                                                    <th>Description / Validate / Rule</th>
                                                    <th>Mandatory</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $i = 0;?>
                                                @foreach($parameters as $param)
                                                    @if($param['used'])
                                                        <?php
                                                        $param_exception = ['in', 'not_in', 'digits_between'];
                                                        if ($param['config'] && substr($param['config'], 0, 1) != '*' && ! in_array($param['type'], $param_exception)) continue;?>
                                                        <tr>
                                                            <td>{{++$i}}</td>
                                                            <td width="5%"><em>{{$param['type']}}</em></td>
                                                            <td>{{$param['name']}}</td>
                                                            <td>

                                                                @if(substr($param['config'],0,1) == '*')
                                                                    <span class='text-info'>{{substr($param['config'],1)}}</span>
                                                                @else
                                                                    {{$param['config']}}
                                                                @endif

                                                            </td>
                                                            <td>{!! ($param['required'])?"<span class='badge text-bg-primary'>REQUIRED</span>":"<span class='badge text-bg-secondary'>OPTIONAL</span>"!!}</td>
                                                        </tr>
                                                    @endif
                                                @endforeach
                                                @if($i == 0)
                                                    <tr>
                                                        <td colspan='4' align="center"><i class='fa fa-search'></i> There is no parameter</td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>
                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>RESPONSE</strong></td>
                                        <td>
                                            <table class='table table-bordered table-hover'>
                                                <thead>
                                                <tr class='table-active'>
                                                    <th width="3%">No</th>
                                                    <th width="5%">Type</th>
                                                    <th>Response Names</th>
                                                </tr>
                                                </thead>
                                                <tbody>
                                                <?php $i = 0;?>
                                                <tr>
                                                    <td>{{ ++$i }}</td>
                                                    <td><em>integer</em></td>
                                                    <td>api_status</td>
                                                </tr>
                                                <tr>
                                                    <td>{{ ++$i }}</td>
                                                    <td><em>string</em></td>
                                                    <td>api_message</td>
                                                </tr>

                                                @if($api->aksi == 'list')
                                                    <tr class='table-active'>
                                                        <td>{{ ++$i }}</td>
                                                        <td>Array</td>
                                                        <td><strong>data</strong></td>
                                                    </tr>
                                                @endif

                                                @if($api->aksi == 'detail')
                                                    <tr class='table-active'>
                                                        <td>{{ ++$i }}</td>
                                                        <td>Object</td>
                                                        <td><strong>data</strong></td>
                                                    </tr>
                                                @endif

                                                @php $e = 0; @endphp
                                                @if($api->aksi == 'list' || $api->aksi == 'detail')
                                                    @foreach($responses as $resp)
                                                        @if($resp['used'])
                                                            <tr>
                                                                <td>{{$i.".".(++$e)}}</td>
                                                                <td width="5%"><em>{{$resp['type']}}</em></td>
                                                                <td>{{ ($api->aksi=='list')?'- ':'' }} {{$resp['name']}}</td>
                                                            </tr>
                                                        @endif
                                                    @endforeach
                                                @endif

                                                @if($api->aksi == 'save_add')
                                                    <tr>
                                                        <td width="5%">{{ ++$i }}</td>
                                                        <td><em>integer</em></td>
                                                        <td>id</td>
                                                    </tr>
                                                @endif
                                                </tbody>
                                            </table>

                                        </td>
                                    </tr>
                                    <tr>
                                        <td><strong>DESCRIPTION</strong></td>
                                        <td><em>{!! $api->keterangan !!}</em></td>
                                    </tr>
                                </table>
                            </div>
                        </td>
                    </tr>
                @endforeach

                </tbody>
            </table>


        </div><!--END BODY-->
    </div><!--END BOX-->

    <hr>
    <div align="center">
        &copy; Copyright {{date('Y')}}. All Right Reserved. API Documentation
    </div>


</div>
</body>
</html>