@php use Illuminate\Support\Facades\DB; @endphp

@extends('backend.layouts.index')
@section('content')

    <div class="content-body">
        <div class="container-fluid mt-3">


            <div class="col-lg-12">
                <div class="card">
                    <div class="card-body p-0">

                        <div class="row p-3 align-items-center">
                            <div class="col-7">
                                <strong>All Users Information</strong>
                            </div>


                            <div class="table-responsive">
                                <table class="table table-bordered zero-configuration">
                                    <thead>
                                    <tr class="bg-primary text-white">
                                        <th>SL.</th>
                                        <th>Personal Info.</th>
                                        <th>Post Code</th>
                                        <th>Email</th>
                                        <th>Interest</th>
                                        <th>Subscription</th>
                                        <th>Action</th>
                                    </tr>
                                    </thead>


                                    @if(isset($users))
                                        @foreach($users as $key=>$item)
                                            <tr>
                                                <td>{{ $key+1 }}</td>
                                                <th>
                                                    Name: {{ $item->name }}<br>
                                                    Phone: {{ $item->phone }}<br>
                                                    Age: {{ $item->age }}<br>

                                                </th>
                                                <td>{{ $item->post_code }}</td>
                                                <td>{{ $item->email }}</td>
                                                <th>
                                                    @foreach(explode(',', $item->interest) as $interest)
                                                        <button
                                                            class="btn btn-primary btn-sm">{{ trim($interest) }}</button>
                                                    @endforeach
                                                </th>

                                                <td>
                                                    @php
                                                      $package = DB::table('subscriptions')
                                                      ->where('user_id',$item->id)
                                                      ->latest()
                                                      ->where('status','active')
                                                      ->first();

                                                    @endphp

                                                    @if(isset($package))
                                                    @if ($package->start_date <= now() && $package->end_date >= now() && $package->status == 'active')
                                                        <span class="btn btn-primary btn-sm">Active</span>
                                                        <div class="mt-2">
                                                            <b> End Date: {{ $package->end_date }}</b>
                                                        </div>
                                                    @else
                                                        <span class="btn btn-warning text-white btn-sm">Inactive</span>
                                                    @endif
                                                    @endif

                                                </td>

                                                <td>
                                                    <a class="btn btn-danger btn-sm text-white"
                                                       onclick="return confirmDelete({{ $item->id  }})">
                                                        <i class="fa fa-trash"></i>
                                                    </a>

                                                    <form id="delete-form-{{ $item->id }}"
                                                          action="{{ route('user.delete', $item->id) }}" method="POST"
                                                          style="display: none;">
                                                        @csrf
                                                        @method('DELETE')
                                                    </form>


                                                </td>

                                            </tr>

                                        @endforeach
                                    @endif

                                </table>
                            </div>
                        </div>

                    </div>
                </div>
            </div>
        </div>

    </div>

@endsection


