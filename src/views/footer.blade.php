<footer class="app-footer main-footer">
    <!-- To the right -->
    <div class="float-end d-none d-sm-inline">
        {{ cbLang('powered_by') }} {{Session::get('appname')}}
    </div>
    <!-- Default to the left -->
    <strong>{{ cbLang('copyright') }} &copy; <?php echo date('Y') ?>. {{ cbLang('all_rights_reserved') }} .</strong>
</footer>
