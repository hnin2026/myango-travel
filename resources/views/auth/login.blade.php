<x-guest-layout>

    <form method="POST" action="{{ route('login') }}">

        @csrf

        {{-- EMAIL --}}
        <div>

            <label for="email">
                Email
            </label>

            <input
                id="email"
                type="email"
                name="email"
                value="{{ old('email') }}"
                required
                autofocus
                autocomplete="username"
                placeholder="Enter your email"
            >

            @error('email')
                <div class="text-danger mt-1">
                    {{ $message }}
                </div>
            @enderror

        </div>

        {{-- PASSWORD --}}
        <div>

            <label for="password">
                Password
            </label>

            <div class="password-wrapper">

                <input
                    id="password"
                    type="password"
                    name="password"
                    required
                    autocomplete="current-password"
                    placeholder="Enter your password"
                >

                <i
                    class="bi bi-eye password-toggle"
                    id="togglePassword"
                ></i>

            </div>

            @error('password')
                <div class="text-danger mt-1">
                    {{ $message }}
                </div>
            @enderror

        </div>

        <div class="remember-row">

    <div class="remember-me">

        <input
            id="remember_me"
            type="checkbox"
            name="remember"
        >

        <label for="remember_me">
            Remember me
        </label>

    </div>

    @if (Route::has('password.request'))

        <a
            href="{{ route('password.request') }}"
            class="forgot-link"
        >
            Forgot password?
        </a>

    @endif

    </div>

        {{-- LOGIN BUTTON --}}
        <button type="submit">

            Log in

        </button>

    </form>

    <script>

        const togglePassword =
            document.getElementById('togglePassword');

        const password =
            document.getElementById('password');

        togglePassword.addEventListener('click', function () {

            if (password.type === 'password') {

                password.type = 'text';

                this.classList.remove('bi-eye');

                this.classList.add('bi-eye-slash');

            } else {

                password.type = 'password';

                this.classList.remove('bi-eye-slash');

                this.classList.add('bi-eye');
            }
        });

    </script>

</x-guest-layout>