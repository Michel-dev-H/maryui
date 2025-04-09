<?php

use App\Models\User; 
use App\Models\Country;
use App\Models\Language;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule; 
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new class extends Component {
    
    use Toast, WithFileUploads;

    public User $user;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('required')]
    public string $password = '';

    #[Rule('sometimes')]
    public ?string $bio = null;

    #[Rule('sometimes')]
    public ?int $country_id = null;

    #[Rule('required')]
    public array $my_languages = [];

    #[Rule('nullable|image|max:1024')]
    public $photo;

    public function with(): array
    {
        return [
            'countries' => Country::all(),
            'languages' => Language::all()
        ];
    }

    public function save(): void
    {
        $data =$this->validate();

        $this->user = User::create($data);

        $this->user->languages()->sync($this->my_languages);

        if ($this->photo) {
            $url = $this->photo->store('users', 'public');
            $this->user->update(['avatar' => "/storage/$url"]);
        }

        $this->success('User created with success.', redirectTo:'/users');
    }

}; ?>

<div>
    <x-header title="Create User" />

    <div class="grid gap-5">
        <div>

            <x-form wire:submit="save" class="lg:grid-cols-2">
                <div>
                    <x-file label="Avatar" wire:model="photo" accept="image/png, image/jpeg" crop-after-change>
                        <img src="/empty-user.jpg" class="h-36 rounded-lg">
                    </x-file>

                    <x-input label="Name" wire:model="name" />
                    <x-input label="Password" wire:model="password" type="password" />
                    <x-input label="Email" wire:model="email" />
                    <x-select label="Country" wire:model="country_id" :options="$countries" placeholder="---" />
                    <x-choices-offline label="My Languages" wire:model="my_languages" 
                        :options="$languages" searchable />
                </div>
                <div>
                    <x-editor wire:model="bio" label="Biography" hint="describe your biography." />

                    <x-slot:actions>
                        <x-button label="Cancel" link="/users" />
                        <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
                    </x-slot:actions>
                </div>
            </x-form>
        </div>
    </div>
</div>
