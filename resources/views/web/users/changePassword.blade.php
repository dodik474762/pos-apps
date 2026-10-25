<div class="row">
    <div class="col-12">
        <div class="page-title-box d-sm-flex align-items-center justify-content-between">
            <h4 class="mb-sm-0">Change Password</h4>

            <div class="page-title-right">
                <ol class="breadcrumb m-0">
                    <li class="breadcrumb-item"><a href="javascript: void(0);">Settings</a></li>
                    <li class="breadcrumb-item active">Change Password</li>
                </ol>
            </div>
        </div>
    </div>
</div>

<div class="row justify-content-center">
    <div class="col-xxl-5 col-lg-7">
        <div class="card">
            <div class="card-body p-4">
                <div class="d-flex align-items-center gap-3 mb-4">
                    <div class="avatar-md flex-shrink-0">
                        <span class="avatar-title rounded-circle bg-primary-subtle text-primary fs-20">
                            {{ strtoupper(substr((string) session('username'), 0, 1)) }}
                        </span>
                    </div>
                    <div>
                        <h5 class="mb-1">{{ session('username') }}</h5>
                        <p class="text-muted mb-0">{{ session('nama_lengkap') }}</p>
                    </div>
                </div>

                @if (session('success'))
                    <div class="alert alert-success alert-dismissible fade show" role="alert">
                        <i class="ri-check-line me-1"></i> {{ session('success') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if (session('error'))
                    <div class="alert alert-danger alert-dismissible fade show" role="alert">
                        <i class="ri-error-warning-line me-1"></i> {{ session('error') }}
                        <button type="button" class="btn-close" data-bs-dismiss="alert" aria-label="Close"></button>
                    </div>
                @endif

                @if ($errors->any())
                    <div class="alert alert-danger" role="alert">
                        <ul class="mb-0 ps-3">
                            @foreach ($errors->all() as $error)
                                <li>{{ $error }}</li>
                            @endforeach
                        </ul>
                    </div>
                @endif

                <form method="POST" action="{{ route('user.changePassword.submit') }}" autocomplete="off">
                    @csrf

                    <div class="mb-3">
                        <label class="form-label" for="new_password">New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control @error('new_password') is-invalid @enderror"
                                id="new_password" name="new_password" placeholder="Enter new password"
                                autocomplete="new-password" required autofocus>
                            <button class="btn btn-outline-secondary" type="button" data-toggle-password="new_password"
                                aria-label="Show new password">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                        <div class="form-text">Minimal 8 karakter.</div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label" for="new_password_confirmation">Confirm New Password</label>
                        <div class="input-group">
                            <input type="password" class="form-control" id="new_password_confirmation"
                                name="new_password_confirmation" placeholder="Repeat new password"
                                autocomplete="new-password" required>
                            <button class="btn btn-outline-secondary" type="button"
                                data-toggle-password="new_password_confirmation" aria-label="Show password confirmation">
                                <i class="ri-eye-line"></i>
                            </button>
                        </div>
                    </div>

                    <div class="d-flex flex-wrap justify-content-end gap-2">
                        <a href="{{ url('dashboard') }}" class="btn btn-light">Cancel</a>
                        <button type="submit" class="btn btn-success">
                            <i class="ri-save-line me-1"></i> Update Password
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>
</div>

<script>
    document.querySelectorAll('[data-toggle-password]').forEach(function (button) {
        button.addEventListener('click', function () {
            var input = document.getElementById(button.getAttribute('data-toggle-password'));

            if (!input) {
                return;
            }

            var show = input.type === 'password';
            input.type = show ? 'text' : 'password';
            button.setAttribute('aria-label', show ? 'Hide password' : 'Show password');
            button.innerHTML = show ? '<i class="ri-eye-off-line"></i>' : '<i class="ri-eye-line"></i>';
        });
    });
</script>
