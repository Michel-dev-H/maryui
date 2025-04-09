<?php

use App\Models\User; 
use App\Models\Country;
use App\Models\Language;
use Livewire\Volt\Component;
use Livewire\Attributes\Rule; 
use Livewire\WithFileUploads;
use Mary\Traits\Toast;

new class extends Component {
    //We will use it later
    use Toast, WithFileuploads;

    //Component parameter
    public User $user;

    #[Rule('required')]
    public string $name = '';

    #[Rule('required|email')]
    public string $email = '';

    #[Rule('sometimes')]
    public ?int $country_id = null;

    #[Rule('nullable|image|max:1024')]
    public $photo;

    #[Rule('required')]
    public array $my_languages = [];

    #[Rule('sometimes')]
    public ?string $bio = null;

    public function mount(): void
    {
        $this->fill($this->user);

        $this->my_languages = $this->user->languages->pluck('id')->all();
    }

    public function with(): array
    {
        return [
            'countries' => Country::all(),
            'languages' => Language::all(),
        ];
    }
    
    public function save(): void
    {
        $data = $this->validate();

        $this->user->update($data);

        $this->user->languages()->sync($this->my_languages);

        if ($this->photo) {
            $url = $this->photo->store('users', 'public');
            $this->user->update(['avatar' => "/storage/$url"]);
        }

        $this->success('User updated with success.', redirectTo: '/users');
    }
};
?>

<div>
    <x-header title="Update {{ $user->name }}" separator />
        
    <div class="grid gap-5 lg:grid-cols-2">
        <div>
            <x-form wire:submit="save">
                
                <x-file label="Avatar" wire:model="photo" accept="image/png, image/jpeg" crop-after-change>
                    <img src="{{ $user->avatar ?? '/empty-user.jpg' }}" class="h-36 rounded-lg">
                </x-file>

                <x-input label="Name" wire:model="name" />
                <x-input label="Email" wire:model="email" />
                <x-select label="country" wire:model="country_id" :options="$countries" placeholder="---" />

                <x-choices-offline
                    label="My Languages"
                    wire:model="my_languages"
                    :options="$languages"
                    serachable />
                
                <x-editor wire:model="bio" label="Biography" hint="the great biography" />

                <x-slot:actions>
                    <x-button label="Cancel" link="/users" />
                    <x-button label="Save" icon="o-paper-airplane" spinner="save" type="submit" class="btn-primary" />
                </x-slot:actions>
            </x-form>
        </div>
        <div>
            <img src="/storage/users/img/control-pana.png" width="300" class="mx-auto">
        </div>
    </div>
</div>
