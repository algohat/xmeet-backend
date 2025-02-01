@extends('backend.layouts.index')
@section('content')
    <style type="text/css">
        .card {
            background: #259c9d !important;
            box-shadow: rgba(0, 0, 0, 0.24) 0px 3px 8px;
        }
    </style>



    <div class="content-body">

        <div class="container-fluid mt-3">

            <div class="row">

                <div class="col-lg-3 col-sm-6">
                    <div class="card gradient-1">
                        <div class="card-body p-4">
                            <h3 class="card-title text-white">Total Users</h3>
                            <div class="d-inline-block">
                                <h2 class="text-white font-weight-bold">{{ $users }}</h2>
                            </div>
                            <span class="float-right display-5 opacity-5"><i class="fa fa-users"></i></span>
                        </div>
                    </div>
                </div>

                <div class="col-lg-3 col-sm-6">
                    <div class="card gradient-1">
                        <div class="card-body p-4">
                            <h3 class="card-title text-white">Total Chat Opened</h3>
                            <div class="d-inline-block">
                                <h2 class="text-white font-weight-bold">{{ $total_chat }}</h2>
                            </div>
                            <span class="float-right display-5 opacity-5"><i class="fa fa-comments"></i></span>
                        </div>
                    </div>
                </div>



            </div>


        </div>

    </div>
@endsection
