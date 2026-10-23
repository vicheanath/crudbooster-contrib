@if(Request::input('fileformat') == 'pdf')
    <h3>{{Request::input('filename')}}</h3>
@endif
<table border='1' width='100%' cellpadding='3' cellspacing="0" style='border-collapse: collapse;font-size:12px'>
    <thead>
    <tr>
        <?php
        foreach ($columns as $col) {

            if (Request::get('columns')) {
                if (! in_array($col['name'], Request::get('columns'))) {
                    continue;
                }
            }
            echo "<th style='background:#eeeeee'>".e($col['label'])."</th>";
        }
        ?>
    </tr>
    </thead>
    <tbody>
    @if(count($result)==0)
        <tr class='table-warning'>
            <td colspan='{{count($columns)+1}}' align="center">No Data Avaliable</td>
        </tr>
    @else
        @foreach($result as $row)
            <tr>
                <?php
                foreach ($columns as $col) {

                    if (Request::get('columns')) {
                        if (! in_array($col['name'], Request::get('columns'))) {
                            continue;
                        }
                    }

                    $value = @$row->{$col['field']};
                    $title = @$row->{$title_field};
                    // XLS and CSV are built by parsing this HTML, so cell values are escaped: a bare "&" or "<" made it fail.
                    $isPdf = Request::input('fileformat') == 'pdf';

                    if (@$col['image']) {
                        if ($value == '') {
                            $value = "http://placehold.it/50x50&text=NO+IMAGE";
                        }
                        $pic = (strpos($value, 'http://') !== FALSE) ? $value : asset($value);
                        $pic_small = $pic;
                        if ($isPdf) {
                            echo "<td><a data-lightbox='roadtrip' rel='group_{{$table}}' title='".e($col['label'].': '.$title)."' href='".e($pic)."'><img class='rounded-circle' width='40px' height='40px' src='".e($pic_small)."'/></a></td>";
                        } else {
                            echo "<td>".e($pic)."</td>";
                        }
                    } elseif (@$col['download']) {
                        $url = (strpos($value, 'http://') !== FALSE) ? $value : asset($value);
                        echo "<td><a class='btn btn-sm btn-primary' href='".e($url)."' target='_blank' title='Download File'>Download</a></td>";
                    } else {

                        //limit character
                        if ($col['str_limit']) {
                            $value = trim(strip_tags($value));
                            $value = str_limit($value, $col['str_limit']);
                        }

                        if (! $isPdf) {
                            $value = e($value);
                        }

                        if ($col['nl2br']) {
                            $value = nl2br($value);
                        }

                        if ($isPdf) {
                            if (! empty($col['callback_php'])) {

                                foreach ($row as $k => $v) {
                                    $col['callback_php'] = str_replace("[".$k."]", $v, $col['callback_php']);
                                }
                                @eval("\$value = ".$col['callback_php'].";");
                            }

                            //New method for callback
                            if (isset($col['callback'])) {
                                $value = call_user_func($col['callback'], $row);
                            }
                        }

                        echo "<td>".$value."</td>";
                    }
                }
                ?>
            </tr>
        @endforeach
    @endif
    </tbody>
</table>
<script type="text/php">
    if ( isset($pdf) ) {
        $font = Font_Metrics::get_font("helvetica", "bold");
        $pdf->page_text(36, 18, "Page {PAGE_NUM} of {PAGE_COUNT}", $font, 6, array(0,0,0));
    }

</script>