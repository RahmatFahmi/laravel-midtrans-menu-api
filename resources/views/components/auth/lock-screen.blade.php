<x-auth.layout title="Lock Screen">
    <div class="card mt-4 card-bg-fill">

        <div class="card-body p-4">
            <div class="text-center mt-2">
                <h5 class="text-primary">Lock Screen</h5>
                <p class="text-muted">Enter your password to unlock the screen!</p>
            </div>
            <div class="user-thumb text-center">
                <img src="{{ asset('assets/images/users/avatar-1.jpg') }}"
                    class="rounded-circle img-thumbnail avatar-lg material-shadow" alt="thumbnail">
                <h5 class="font-size-15 mt-3">{{ Auth::user()->name }}</h5>
            </div>
            <div class="p-2 mt-4">
                <form action="{{ route('unlock') }}" method="POST">
                    @csrf
                    <div class="position-relative auth-pass-inputgroup mb-3">
                        <input type="password" name="password"
                            class="form-control pe-5 password-input @error('password') is-invalid @enderror"
                            placeholder="Enter password" id="password-input" required autocomplete="off">

                        <button
                            class="btn btn-link position-absolute end-0 top-0 text-decoration-none text-muted password-addon material-shadow-none"
                            type="button" id="password-addon">
                            <i class="ri-eye-fill align-middle"></i>
                        </button>

                        @error('password')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-2 mt-4">
                        <button class="btn btn-success w-100" type="submit">Buka Kunci</button>
                    </div>
                </form>


            </div>
        </div>
        <!-- end card body -->
    </div>
    <!-- end card -->

    <div class="mt-4 text-center">
        <p class="mb-0">Not you ? return
            <a href="javascript:void(0);"
                onclick="event.preventDefault(); document.getElementById('logout-form').submit();"
                class="fw-semibold text-primary text-decoration-underline">
                Signin
            </a>
        </p>
    </div>

    <form id="logout-form" action="{{ route('logout') }}" method="POST" style="display: none;">
        @csrf
    </form>
</x-auth.layout>
