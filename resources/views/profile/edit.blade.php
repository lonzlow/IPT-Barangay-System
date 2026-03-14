@extends('layouts.app') {{-- palitan kung iba name ng layout mo --}}

@section('title', 'Profile')
@section('page-title', 'Profile')

@section('content')

    <div class="row justify-content-center">

        <div class="col-lg-8">

            {{-- UPDATE PROFILE --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    @include('profile.partials.update-profile-information-form')
                </div>
            </div>

            {{-- UPDATE PASSWORD --}}
            <div class="card mb-4 shadow-sm">
                <div class="card-body">
                    @include('profile.partials.update-password-form')
                </div>
            </div>

            {{-- DELETE ACCOUNT --}}
            <div class="card mb-4 shadow-sm border-danger">
                <div class="card-body">
                    @include('profile.partials.delete-user-form')
                </div>
            </div>

        </div>

    </div>

@endsection