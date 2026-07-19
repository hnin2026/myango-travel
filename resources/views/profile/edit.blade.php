<x-app-layout>
    <x-slot name="header">
        <div class="d-flex justify-content-between align-items-center w-100">
            <div>
                <h1 class="page-title mb-1">Profile Settings</h1>
                <p class="text-muted mb-0">Update your account credentials and personal details</p>
            </div>
        </div>
    </x-slot>

    <div class="container-fluid px-0 py-4">
        
        <div class="row g-4">
            
            {{-- Update Profile Info --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-1">Profile Information</h5>
                        <p class="text-muted small mb-4">Update your account's profile information and email address.</p>
                        
                        <form method="post" action="{{ route('profile.update') }}">
                            @csrf
                            @method('patch')

                            <div class="mb-3">
                                <x-input-label for="name" :value="__('Name')" />
                                <x-text-input id="name" name="name" type="text" :value="old('name', $user->name)" required autocomplete="name" />
                                <x-input-error :messages="$errors->get('name')" />
                            </div>

                            <div class="mb-3">
                                <x-input-label for="email" :value="__('Email Address')" />
                                <x-text-input id="email" name="email" type="email" :value="old('email', $user->email)" required autocomplete="username" />
                                <x-input-error :messages="$errors->get('email')" />
                            </div>

                            <div class="mb-3">
                                <x-input-label for="role" :value="__('Assigned Role')" />
                                <x-text-input id="role" type="text" :value="ucfirst($user->role)" disabled readonly class="bg-light text-muted" />
                                <div class="form-text text-muted" style="font-size: 11px;">Your account role cannot be changed from the profile settings.</div>
                            </div>

                            <div class="mt-4">
                                <x-primary-button>
                                    Save Changes
                                </x-primary-button>
                                @if (session('status') === 'profile-updated')
                                    <span class="text-success small ms-2"><i class="bi bi-check-circle-fill"></i> Saved!</span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

            {{-- Update Password --}}
            <div class="col-lg-6">
                <div class="card shadow-sm border-0 h-100" style="border-radius: 16px;">
                    <div class="card-body p-4">
                        <h5 class="fw-bold text-dark mb-1">Update Password</h5>
                        <p class="text-muted small mb-4">Ensure your account is using a long, random password to stay secure.</p>
                        
                        <form method="post" action="{{ route('password.update') }}">
                            @csrf
                            @method('put')

                            <div class="mb-3">
                                <x-input-label for="update_password_current_password" :value="__('Current Password')" />
                                <x-text-input id="update_password_current_password" name="current_password" type="password" required autocomplete="current-password" />
                                <x-input-error :messages="$errors->updatePassword->get('current_password')" />
                            </div>

                            <div class="mb-3">
                                <x-input-label for="update_password_password" :value="__('New Password')" />
                                <x-text-input id="update_password_password" name="password" type="password" required autocomplete="new-password" />
                                <x-input-error :messages="$errors->updatePassword->get('password')" />
                            </div>

                            <div class="mb-3">
                                <x-input-label for="update_password_password_confirmation" :value="__('Confirm Password')" />
                                <x-text-input id="update_password_password_confirmation" name="password_confirmation" type="password" required autocomplete="new-password" />
                                <x-input-error :messages="$errors->updatePassword->get('password_confirmation')" />
                            </div>

                            <div class="mt-4">
                                <x-primary-button>
                                    Update Password
                                </x-primary-button>
                                @if (session('status') === 'password-updated')
                                    <span class="text-success small ms-2"><i class="bi bi-check-circle-fill"></i> Updated!</span>
                                @endif
                            </div>
                        </form>
                    </div>
                </div>
            </div>

        </div>

    </div>
</x-app-layout>
