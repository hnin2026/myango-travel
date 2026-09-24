<x-guest-layout>

    <form method="POST" action="{{ route('login') }}">

        @csrf

        {{-- INVALID CREDENTIALS / AUTH ERROR NOTICE --}}
        @if ($errors->any())
            <div class="auth-error-notice" role="alert">
                <i class="bi bi-exclamation-circle-fill"></i>
                <div>
                    @if ($errors->has('email') && ($errors->first('email') === __('auth.failed') || $errors->first('email') === 'These credentials do not match our records.' || $errors->first('email') === 'The provided credentials are incorrect.'))
                        The provided credentials are incorrect.
                    @else
                        {{ $errors->first('email') ?: $errors->first('password') ?: $errors->first() }}
                    @endif
                </div>
            </div>
        @endif

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
                class="{{ $errors->has('email') ? 'is-invalid' : '' }}"
            >

            @error('email')
                @if ($message !== __('auth.failed') && $message !== 'These credentials do not match our records.' && $message !== 'The provided credentials are incorrect.')
                    <div class="auth-field-error">
                        {{ $message }}
                    </div>
                @endif
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
                    class="{{ $errors->has('password') || ($errors->has('email') && ($errors->first('email') === __('auth.failed') || $errors->first('email') === 'These credentials do not match our records.' || $errors->first('email') === 'The provided credentials are incorrect.')) ? 'is-invalid' : '' }}"
                >

                <i
                    class="bi bi-eye password-toggle"
                    id="togglePassword"
                ></i>

            </div>

            @error('password')
                <div class="auth-field-error">
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