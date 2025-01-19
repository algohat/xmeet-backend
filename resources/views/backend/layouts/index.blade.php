<!DOCTYPE html>
<html lang="en">

<head>
    <meta charset="utf-8">
    <meta http-equiv="X-UA-Compatible" content="IE=edge">
    <meta name="viewport" content="width=device-width,initial-scale=1">
    <title>Admin Dashboard</title>
    <link rel="icon" href="https://muktir71news.com/public/backend/logo/1760507521463742.png" type="image/gif"
          sizes="16x16">
    <link href="{{ asset("backend/admindashboard") }}/css/style.css" rel="stylesheet">
    <link href="{{ asset("public/backend/admindashboard") }}/plugins/summernote/dist/summernote.css" rel="stylesheet">
    <link rel="stylesheet" type="text/css" href="{{ asset("backend/admindashboard/my") }}/toastr.css">
    <script src="{{ asset("backend/admindashboard/my") }}/jquery.min.js"></script>
    <link rel="stylesheet" href="{{ asset("backend/admindashboard/my") }}/uikit.min.css"/>
    <link href="{{ asset("backend/admindashboard/my") }}/select2.min.css" rel="stylesheet"/>
    <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/notiflix@3.2.6/dist/notiflix-3.2.6.min.css">


    <style type="text/css">
        label {
            color: #585858 !important;
            font-size: 13px;
        }

        .card-title {
            font-size: 20px;
        }

        .nk-sidebar .metismenu li {
            line-height: 15px;
        }

        .nk-sidebar .metismenu a {
            font-weight: 500;

        }

        .select2-container--default .select2-selection--single {
            height: 45px !important;
            border: none;
            border: 1px solid #e1e1e1;
            border-radius: 0px;
        }

        .select2-container--default .select2-selection--single .select2-selection__rendered {
            line-height: 40px;
        }

        .select2-container--default .select2-selection--single .select2-selection__arrow {
            height: 40px;
        }

        .select2-container--default .select2-search--dropdown .select2-search__field:focus {
            border: 0px solid #fff !important;
        }

        .dropdown-item:hover {
            background: darkred;
            color: #fff;
        }

        .nk-sidebar .metismenu > li.active > a {
            background: #259c9d !important;
            color: #fff !important;
        }

        .nk-sidebar .metismenu > li:focus span, .nk-sidebar .metismenu > li.active span {
            color: #fff !important;
        }

        .nk-sidebar .metismenu > li:focus i, .nk-sidebar .metismenu > li.active i {
            color: #fff !important;
        }

        .nk-sidebar .metismenu a:active, .nk-sidebar .metismenu a.active {
            color: #259c9d !important;
        }

        a:hover {
            text-decoration: none;
        }

        .dataTables_filter input {
            border: 1px solid lightgray !important;
            height: 30px !important;
        }

        .bg-primary {
            background: #259c9d !important;
        }

        .btn-primary {
            background: #259c9d !important;
            border: 0;
        }

        .nav-header {
            background: #259c9d !important;
        }

        .text-primary {
            color: #259c9d !important;
        }


    </style>


</head>
<body>


<div id="preloader">
    <div class="loader">
        <svg class="circular" viewBox="25 25 50 50">
            <circle class="path" cx="50" cy="50" r="20" fill="none" stroke-width="3" stroke-miterlimit="10"/>
        </svg>
    </div>
</div>

<div id="main-wrapper">

    <div class="nav-header">
        <div class="brand-logo">
            <a href="{{ route('admin.dashboard') }}">
                <b class="logo-abbr text-white">D</b>
                <span class="logo-compact"></span>
                <span class="brand-title">
            <h4 style="color: #fff;" class="text-uppercase"><b>{{ (Auth('admin')->user()->name) }}</b></h4>
        </span>
            </a>
        </div>
    </div>

    <div class="header">
        <div class="header-content clearfix">

            <div class="nav-control">
                <div class="hamburger">
                    <span class="toggle-icon"><i class="icon-menu"></i></span>
                </div>

                <b class="text-primary text-uppercase" style="font-size: 16px;">Welcome To Admin Panel</b>
            </div>


            <div class="header-right">


                <ul class="clearfix">


                    <li class="icons dropdown">
                        <div class="user-img c-pointer position-relative" data-toggle="dropdown">
                            <span class="activity active"></span>
                            <img src="https://i.ibb.co/8b8PG14/images.png" height="40" width="40" alt="">
                        </div>
                        <div class="drop-down dropdown-profile animated fadeIn dropdown-menu">
                            <div class="dropdown-content-body">
                                <ul>

                                    <li>

                                        <form method="post" action="{{ route('admin.logout') }}">
                                            @csrf
                                            <button class="btn btn-primary w-100"><i class="icon-key"></i>
                                                <span>Logout</span></button>
                                        </form>


                                    </li>
                                </ul>
                            </div>
                        </div>
                    </li>
                </ul>
            </div>
        </div>
    </div>


    @include('backend.layouts.sidebar')

    @yield('content')


</div>


<script src="{{ asset("backend/admindashboard") }}/plugins/common/common.min.js"></script>
<script src="{{ asset("backend/admindashboard") }}/js/custom.min.js"></script>
<script src="{{ asset("backend/admindashboard") }}/js/settings.js"></script>
<script src="{{ asset("backend/admindashboard/my") }}/select2.min.js"></script>
<script src="{{ asset("backend/admindashboard/my") }}/toastr.min.js"></script>

<script>
    @if(Session::has('messege'))
    var type = "{{Session::get('alert-type','info')}}"
    switch (type) {
        case 'info':
            toastr.info("{{ Session::get('messege') }}");
            break;
        case 'success':
            toastr.success("{{ Session::get('messege') }}");
            break;
        case 'warning':
            toastr.warning("{{ Session::get('messege') }}");
            break;
        case 'error':
            toastr.error("{{ Session::get('messege') }}");
            break;
    }
    @endif
</script>


<script src="{{ asset("backend/admindashboard") }}/plugins/tables/js/jquery.dataTables.min.js"></script>
<script src="{{ asset("backend/admindashboard") }}/plugins/tables/js/datatable/dataTables.bootstrap4.min.js"></script>
<script src="{{ asset("backend/admindashboard") }}/plugins/tables/js/datatable-init/datatable-basic.min.js"></script>


<script type="text/javascript">
    (function ($) {
        "use strict"

        new quixSettings({
            sidebarPosition: "fixed",
            headerPosition: "fixed"
        });

    })(jQuery);

</script>


<script type="text/javascript">

    $(document).ready(function () {
        $('.myselect').select2();
    });

</script>


<script src="{{ asset("backend/admindashboard/my") }}/uikit.min.js"></script>
<script src="{{ asset("backend/admindashboard/my") }}/uikit-icons.min.js"></script>
<script src="{{ asset("backend/admindashboard/my") }}/sweetalert2@11.js"></script>

<script src="https://cdn.jsdelivr.net/npm/notiflix@3.2.6/dist/notiflix-3.2.6.min.js"></script>


<script>
    function confirmDelete(id) {
        Notiflix.Confirm.show(
            'Delete Confirmation',
            'Are you sure you ?',
            'Yes',
            'No',
            function () {
                document.getElementById('delete-form-' + id).submit();
            },
            function () {
                Notiflix.Notify.warning('Delete Canceled');
            });

    }
</script>


@if (session('success'))
    <script>
        Notiflix.Notify.success("{{ session('success') }}");
    </script>
@endif

@if (session('warning'))
    <script>
        Notiflix.Notify.warning("{{ session('error') }}");
    </script>
@endif

@if (session('error'))
    <script>
        Notiflix.Notify.failure("{{ session('error') }}");
    </script>
@endif

@if ($errors->any())
    @foreach ($errors->all() as $error)
        <script>
            Notiflix.Notify.failure("{{ $error }}");
        </script>
    @endforeach
@endif


</body>
</html>

