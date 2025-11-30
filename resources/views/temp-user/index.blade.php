<!DOCTYPE html>
<html lang="en">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Temporary User System</title>
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
</head>
<body>
    <div class="container mt-5">
        <div class="row justify-content-center">
            <div class="col-md-6">
                @if(session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
                    </div>
                @endif

                @if(!session('temp_username'))
                    <div class="card">
                        <div class="card-header">
                            <h4 class="mb-0">Create Temporary User</h4>
                        </div>
                        <div class="card-body">
                            <form method="POST" action="{{ route('temp-user.create') }}">
                                @csrf
                                <div class="mb-3">
                                    <label for="username" class="form-label">Username</label>
                                    <input 
                                        type="text" 
                                        class="form-control @error('username') is-invalid @enderror" 
                                        id="username" 
                                        name="username" 
                                        maxlength="20"
                                        pattern="[a-zA-Z0-9]+"
                                        value="{{ old('username') }}"
                                        required
                                    >
                                    <div class="form-text">
                                        Alphanumeric characters only, up to 20 characters
                                    </div>
                                    @error('username')
                                        <div class="invalid-feedback">{{ $message }}</div>
                                    @enderror
                                </div>
                                <button type="submit" class="btn btn-primary">Create User</button>
                            </form>
                        </div>
                    </div>
                @else
                    <div class="card">
                        <div class="card-header bg-success text-white">
                            <h4 class="mb-0">Welcome!</h4>
                        </div>
                        <div class="card-body">
                            <p class="mb-3">You are logged in as: <strong>{{ session('temp_username') }}</strong></p>
                            <form method="POST" action="{{ route('temp-user.logout') }}">
                                @csrf
                                <button type="submit" class="btn btn-danger">Logout</button>
                            </form>
                        </div>
                    </div>
                @endif
            </div>
        </div>
    </div>

    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
</body>
</html>