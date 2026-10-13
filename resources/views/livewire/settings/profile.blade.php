<?php

use App\Models\User;
use Illuminate\Support\Facades\Auth;
use Illuminate\Support\Facades\Session;
use Illuminate\Support\Facades\Storage;
use Illuminate\Validation\Rule;
use Livewire\Volt\Component;
use Livewire\WithFileUploads;

new class extends Component {
    use WithFileUploads;

    public string $name = '';
    public string $email = '';
    public string $intro = '';
    public $avatar = null;

    /**
     * Mount the component.
     */
    public function mount(): void
    {
        $this->name = Auth::user()->name;
        $this->email = Auth::user()->email;
        $this->intro = Auth::user()->intro ?? '';
    }

    /**
     * Update the profile information for the currently authenticated user.
     */
    public function updateProfileInformation(): void
    {
        $user = Auth::user();

        $validated = $this->validate([
            'name' => ['required', 'string', 'min:2', 'max:30', Rule::unique(User::class)->ignore($user->id)],

            'email' => [
                'required',
                'string',
                'lowercase',
                'email',
                'max:255',
                Rule::unique(User::class)->ignore($user->id)
            ],

            'intro' => ['nullable', 'string', 'max:300'],
            'avatar' => ['nullable', 'image', 'mimes:jpg,jpeg,png,gif,webp', 'max:2048', 'dimensions:max_width=4000,max_height=4000'],
        ]);

        $user->fill([
            'name' => $validated['name'],
            'email' => $validated['email'],
            'intro' => $validated['intro'] ?: null,
        ]);

        if ($this->avatar) {
            $this->deleteAvatarFile($user);
            $user->avatar_path = $this->avatar->store('avatars', 'public');
            $this->avatar = null;
        }

        if ($user->isDirty('email')) {
            $user->email_verified_at = null;
        }

        $user->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    /**
     * Remove the current user's profile image.
     */
    public function removeAvatar(): void
    {
        $user = Auth::user();

        $this->deleteAvatarFile($user);
        $user->forceFill(['avatar_path' => null])->save();

        $this->dispatch('profile-updated', name: $user->name);
    }

    private function deleteAvatarFile(User $user): void
    {
        if ($user->avatar_path) {
            Storage::disk('public')->delete($user->avatar_path);
        }
    }

    /**
     * Send an email verification notification to the current user.
     */
    public function resendVerificationNotification(): void
    {
        $user = Auth::user();

        if ($user->hasVerifiedEmail()) {
            $this->redirectIntended(default: route('dashboard', absolute: false));

            return;
        }

        $user->sendEmailVerificationNotification();

        Session::flash('status', 'verification-link-sent');
    }
}; ?>

<section class="w-full">
    @include('partials.settings-heading')

    <x-settings.layout :heading="__('Profile')" :subheading="__('Update your name and email address')">
        <form wire:submit="updateProfileInformation" class="my-6 w-full space-y-6">
            <div class="flex items-center gap-4">
                <flux:avatar size="xl" :src="$avatar?->isPreviewable() ? $avatar->temporaryUrl() : auth()->user()->avatarUrl()" :initials="auth()->user()->initials()" />

                <div class="flex flex-col gap-2">
                    <flux:input type="file" wire:model="avatar" accept="image/jpeg,image/png,image/gif,image/webp" :label="__('Profile image')" />
                    @if (auth()->user()->avatar_path)
                        <flux:button size="sm" variant="subtle" wire:click="removeAvatar">{{ __('Remove image') }}</flux:button>
                    @endif
                </div>
            </div>

            <flux:input :value="auth()->user()->username" :label="__('Username')" readonly disabled />

            <flux:input wire:model="name" label="{{ __('Nickname') }}" type="text" name="name" required autofocus autocomplete="nickname" />

            <div>
                <flux:input wire:model="email" label="{{ __('Email') }}" type="email" name="email" required autocomplete="email" />

                @if (auth()->user() instanceof \Illuminate\Contracts\Auth\MustVerifyEmail &&! auth()->user()->hasVerifiedEmail())
                    <div>
                        <p class="mt-2 text-sm text-gray-800">
                            {{ __('Your email address is unverified.') }}

                            <button
                                wire:click.prevent="resendVerificationNotification"
                                class="rounded-md text-sm text-gray-600 underline hover:text-gray-900 focus:outline-hidden focus:ring-2 focus:ring-indigo-500 focus:ring-offset-2"
                            >
                                {{ __('Click here to re-send the verification email.') }}
                            </button>
                        </p>

                        @if (session('status') === 'verification-link-sent')
                            <p class="mt-2 text-sm font-medium text-green-600">
                                {{ __('A new verification link has been sent to your email address.') }}
                            </p>
                        @endif
                    </div>
                @endif
            </div>

            <flux:textarea wire:model="intro" :label="__('About me')" rows="3" maxlength="300" />

            <div class="flex items-center gap-4">
                <div class="flex items-center justify-end">
                    <flux:button variant="primary" type="submit" class="w-full">{{ __('Save') }}</flux:button>
                </div>

                <x-action-message class="me-3" on="profile-updated">
                    {{ __('Saved.') }}
                </x-action-message>
            </div>
        </form>

        <livewire:settings.delete-user-form />
    </x-settings.layout>
</section>
