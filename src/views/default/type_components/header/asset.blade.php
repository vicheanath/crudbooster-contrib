@push('head')
    <style type="text/css">
        .form-divider {
            padding: 10px 0px 10px 0px;
            margin-bottom: 10px;
            border-bottom: 1px solid #dddddd;
        }

        .header-title {
            cursor: pointer;
        }
    </style>
@endpush
@push('bottom')
    <script type="text/javascript">
        $(function () {
            if (typeof event_header_click === 'undefined') {
                event_header_click = true;
                $(document).on("click", ".header-title", function () {
                    var index = $(this).attr('id').replace("header", "");
                    var handel = $(this);
                    var parent = $(this).parent('.card-body, .box-body');
                    var first_group = parent.find(".header-group-" + index + ":first").is(":hidden");
                    if (first_group) {
                        parent.find(".header-group-" + index).slideDown(function () {
                            handel.find(".icon i").attr('class', 'fa fa-minus-square-o');
                            handel.attr("title", "Click here to slide up");
                        });
                    } else {
                        parent.find(".header-group-" + index).slideUp(function () {
                            handel.find(".icon i").attr('class', 'fa fa-plus-square-o');
                            handel.attr("title", "Click here to expand");
                        });
                    }
                })
                // Sections start open; 'collapsed' => true starts one folded (a click toggles it).
                $(".header-title[data-collapsed='true']").click();
            }


        })
    </script>
@endpush