<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Login</title>

    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap.min.css') }}">
    <link rel="stylesheet" href="{{ asset('assets/css/bootstrap-icons.min.css') }}">
</head>

<body class="bg-light">

<div class="container">
    <div class="row justify-content-center align-items-center vh-100">

        <div class="col-md-4">

            <div class="card shadow">

                <div class="card-header text-center bg-primary text-white">
                    <h4>Daily Activity Management System</h4>
                </div>

                <div class="card-body">

                    <form method="POST" action="{{ route('login') }}">
                        @csrf

                        <div class="mb-3">
                            <label>Email</label>
                            <input type="email" name="email" class="form-control" value="{{ old('email') }}" required>      
                        </div>

                        <div class="mb-3">
                            <label>Password</label>
                            <input type="password" name="password" class="form-control" required>       
                        </div>

                        @error('email')
                            <div class="text-danger mb-3">{{ $message }}</div>
                        @enderror

                        <button class="btn btn-primary w-100">
                            Login
                        </button>

                    </form>

                </div>

            </div>

        </div>

    </div>
</div>

<script src="{{ asset('assets/js/bootstrap.bundle.min.js') }}"></script>

</body>
</html>