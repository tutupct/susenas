<!DOCTYPE html>
<html lang="{{ str_replace('_', '-', app()->getLocale()) }}">

<head>
    <meta charset="utf-8">
    <meta name="viewport" content="width=device-width, initial-scale=1">

    <title>Login - {{ config('app.name', 'Validasi Susenas') }}</title>

    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.8/dist/css/bootstrap.min.css" rel="stylesheet">
</head>

<body class="bg-light">

    <div class="container">
        <div class="row justify-content-center align-items-center min-vh-100">
            <div class="col-md-5 col-lg-4">

                <div class="card border-0 shadow-sm">
                    <div class="card-body p-4">

                        <div class="text-center mb-4">
                            <h1 class="h4 mb-1">
                                Validasi Susenas
                            </h1>

                            <p class="text-muted mb-0">
                                Silakan login untuk melanjutkan.
                            </p>
                        </div>

                        @if ($errors->any())
                            <div class="alert alert-danger">
                                {{ $errors->first() }}
                            </div>
                        @endif

                        <form action="{{ route('login.attempt') }}" method="POST">
                            @csrf

                            <div class="mb-3">
                                <label for="email" class="form-label">
                                    Email
                                </label>

                                <input type="email" id="email" name="email" value="{{ old('email') }}"
                                    class="form-control @error('email') is-invalid @enderror" required autofocus>

                                @error('email')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>

                            <div class="mb-3">
                                <label for="password" class="form-label">
                                    Password
                                </label>

                                <input type="password" id="password" name="password"
                                    class="form-control @error('password') is-invalid @enderror" required>

                                @error('password')
                                    <div class="invalid-feedback">
                                        {{ $message }}
                                    </div>
                                @enderror
                            </div>


                            <button type="submit" class="btn btn-primary w-100">
                                Login
                            </button>
                        </form>

                    </div>
                </div>

            </div>
        </div>
    </div>

</body>

</html>
