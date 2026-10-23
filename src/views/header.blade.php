<!-- Main Header -->
<nav class="app-header main-header navbar navbar-expand bg-body">
    <div class="container-fluid">

        <!-- Sidebar toggle button-->
        <ul class="navbar-nav">
            <li class="nav-item">
                <a href="#" class="nav-link sidebar-toggle" data-lte-toggle="sidebar" role="button">
                    <i class="fa fa-bars"></i>
                    <span class="visually-hidden">Toggle navigation</span>
                </a>
            </li>
        </ul>

        <!-- Navbar Right Menu -->
        <ul class="navbar-nav ms-auto navbar-custom-menu">

            <li class="nav-item dropdown notifications-menu">
                <a href="#" class="nav-link" data-bs-toggle="dropdown" title='Notifications' aria-expanded="false">
                    <i id='icon_notification' class="fa fa-bell-o"></i>
                    <span id='notification_count' class="navbar-badge badge text-bg-danger" style="display:none">0</span>
                </a>
                <div id='list_notifications' class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <span class="dropdown-item dropdown-header header">{{cbLang("text_no_notification")}}</span>
                    <div class="dropdown-divider"></div>
                    <!-- inner menu: contains the actual data -->
                    <div class="menu">
                        <a href="#" class="dropdown-item">
                            <em>{{cbLang("text_no_notification")}}</em>
                        </a>
                    </div>
                    <div class="dropdown-divider"></div>
                    <a href="{{route('NotificationsControllerGetIndex')}}" class="dropdown-item dropdown-footer footer">{{cbLang("text_view_all_notification")}}</a>
                </div>
            </li>

            <!-- User Account Menu -->
            <li class="nav-item dropdown user-menu">
                <!-- Menu Toggle Button -->
                <a href="#" class="nav-link dropdown-toggle" data-bs-toggle="dropdown">
                    <!-- The user's initials in the navbar-->
                    @include('crudbooster::components.user_initials', ['class' => 'user-image'])
                    <!-- The username is hidden on small devices so only the initials appear. -->
                    <span class="d-none d-md-inline">{{ CRUDBooster::myName() }}</span>
                </a>
                <ul class="dropdown-menu dropdown-menu-lg dropdown-menu-end">
                    <!-- The user's initials in the menu -->
                    <li class="user-header">
                        @include('crudbooster::components.user_initials', ['class' => 'img-circle'])
                        <p>
                            {{ CRUDBooster::myName() }}
                            <small>{{ CRUDBooster::myPrivilegeName() }}</small>
                            <small><em><?php echo date('d F Y')?></em></small>
                        </p>
                    </li>

                    <!-- Menu Footer-->
                    <li class="user-footer d-flex justify-content-between align-items-center">
                        <a href="{{ route('AdminCmsUsersControllerGetProfile') }}" class="btn btn-default btn-flat"><i
                                    class='fa fa-user'></i> {{cbLang("label_button_profile")}}</a>
                        <div class="d-flex gap-1">
                            <a title='Lock Screen' href="{{ route('getLockScreen') }}" class='btn btn-default btn-flat'><i class='fa fa-key'></i></a>
                            <a href="javascript:void(0)" onclick="swal({
                                    title: '{{cbLang('alert_want_to_logout')}}',
                                    type:'info',
                                    showCancelButton:true,
                                    allowOutsideClick:true,
                                    confirmButtonColor: '#DD6B55',
                                    confirmButtonText: '{{cbLang('button_logout')}}',
                                    cancelButtonText: '{{cbLang('button_cancel')}}',
                                    closeOnConfirm: false
                                    }, function(){
                                    location.href = '{{ route("getLogout") }}';

                                    });" title="{{cbLang('button_logout')}}" class="btn btn-danger btn-flat"><i class='fa fa-power-off'></i></a>
                        </div>
                    </li>
                </ul>
            </li>
        </ul>
    </div>
</nav>
