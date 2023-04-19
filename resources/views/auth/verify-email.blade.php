@extends('layouts.auth')

@section('main')
    <form class="form-signin" action="/email/verification-notification" method="post">
        @csrf
        <img class="mb-4 app-logo" src="{{ asset('/bower_components/admin-lte/dist/img/AdminLTELogo.png') }}" alt="">
        <h1 class="h3 mb-3 font-weight-normal">My Elbum</h1>
        <h2 class="h5 mb-3 font-weight-normal">- vérification d'email -</h2>
        <div class="form-group">
            {{ __("Vous n'avez pas reçu le mail ou l'avez perdu")}} ?
        </div>
        <div class="form-group">
            <button type="submit" class="btn btn-block btn-rounded btn-primary">
                <i class="fa fa-asterisk mr-10"></i> {{ __('Cliquez pour renvoyer le mail de vérification')}}
            </button>
        </div>
    </form>
@endsection

