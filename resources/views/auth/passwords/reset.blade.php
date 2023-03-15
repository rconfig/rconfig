@extends('layouts.login')
@section('content')
<div class="row">
        <div class="col-sm-8 col-sm-offset-2 col-md-6 col-md-offset-3 col-lg-6 col-lg-offset-3">
           <header class="login-pf-page-header">
              <img class="login-pf-brand" src="/images/rConfig_white_horizontal_128px.png" alt=" logo" />

           </header>
           <div class="row">
              <div class="col-sm-10 col-sm-offset-1 col-md-8 col-md-offset-2 col-lg-8 col-lg-offset-2">
                 <div class="card-pf">
                     <header class="login-pf-header">
                         <h1>{{ __('Reset Password') }}</h1>
                     </header>
                     <div class="card-body">
                        @if (session('status'))
                            <div class="alert alert-success" role="alert">
                                {{ session('status') }}
                            </div>
                        @endif

                        <form method="POST" action="{{ route('password.update') }}">
                            @csrf
                            <input type="hidden" name="token" value="{{ $token }}">

                            <div class="form-group row">
                                <div class="col-md-12">
                                    <input id="email" type="email" class="form-control{{ $errors->has('email') ? ' is-invalid' : '' }}  input-lg" name="email" placeholder="Email address" value="{{ $email ?? old('email') }}" required autofocus>
                                    @if ($errors->has('email'))
                                        <span class="invalid-feedback text-danger" role="alert">
                                            <strong>{{ $errors->first('email') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>
                            <div class="form-group row">
                                <div class="col-md-12">
                                    <input id="password" type="password" class="form-control{{ $errors->has('password') ? ' is-invalid' : '' }}  input-lg" name="password" placeholder="New Password" required>
                                    @if ($errors->has('password'))
                                        <span class="invalid-feedback text-danger" role="alert">
                                            <strong>{{ $errors->first('password') }}</strong>
                                        </span>
                                    @endif
                                </div>
                            </div>

                            <div class="form-group row">
                                <div class="col-md-12">
                                    <input id="password-confirm" type="password" class="form-control{{ $errors->has('password-confirm') ? ' is-invalid' : '' }}  input-lg" name="password-confirm" placeholder="Confirm Password" required>
                                </div>
                            </div>

                            <button type="submit" class="btn btn-primary btn-block btn-lg">{{ __('Reset Password') }}</button>
                        </form>
                    </div>
                </div>
           <!-- card -->
            <footer class="login-pf-page-footer">
                <ul class="login-pf-page-footer-links list-unstyled">
                    <li><a class="login-pf-page-footer-link" target="_blank" href="https://www.rconfig.com/terms">Terms of Use</a></li>
                    <li><a class="login-pf-page-footer-link" target="_blank" href="https://www.rconfig.com/license">License</a></li>
                    <li><a class="login-pf-page-footer-link" target="_blank" href="http://help.rconfig.com">Support</a></li>
                </ul>
            </footer>
            </div>
            <!-- col -->
        </div>
        <!-- row -->
        </div><!-- col -->
     </div><!-- row -->
@endsection
