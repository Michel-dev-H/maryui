<?php

use App\Models\User;
use App\Models\Country;
use Illuminate\Support\Collection;
use Illuminate\Database\Eloquent\Builder;
use Illuminate\Pagination\LengthAwarePaginator;
use Livewire\Volt\Component;
use Mary\Traits\Toast;
use Livewire\WithPagination;

new class extends Component {
    
    use WithPagination;

    use Toast;

    public string $search = '';

    public bool $drawer = false;

    public array $sortBy = ['column' => 'id', 'direction' => 'asc'];

    public bool $myModal1 = false;

    // Clear filters
    public function clear(): void
    {
        $this->reset();
        $this->resetPage(); 
        $this->success('Filters cleared.', position: 'toast-bottom');
    }

    public function delete(User $user): void
    {
        $user->delete();
        $this->warning("$user->name deleted", 'Good bye!', position: 'toast-bottom');
    }

    // Table headers
    public function headers(): array
    {
        return [
            ['key' => 'avatar', 'label' => '', 'class' => 'w-1'],
            ['key' => 'id', 'label' => '#', 'class' => 'w-1'],
            ['key' => 'name', 'label' => 'Name', 'class' => 'w-64'],
            ['key' => 'country_name', 'label' => 'Country', 'class' => 'hidden lg:table-cell'],
            ['key' => 'email', 'label' => 'E-mail', 'sortable' => false],
        ];
    }

    /**
     * For demo purpose, this is a static collection.
     *
     * On real projects you do it with Eloquent collections.
     * Please, refer to maryUI docs to see the eloquent examples.
     */
    


    public function users(): LengthAwarePaginator
    {
    return User::query()
        ->withAggregate('country', 'name')
        ->when($this->search,     fn(Builder $q) => $q->where('name', 'like', "%$this->search%"))
        ->when($this->country_id, fn(Builder $q) => $q->where('country_id', $this->country_id))
        ->orderBy(...array_values($this->sortBy))
        ->paginate(5);
    }

    public function with(): array
    {
        return [
            'users'     => $this->users(),
            'headers'   => $this->headers(),
            'countries' => Country::all(),
        ];
    }

    public function updated($property): void
    {
        if (! is_array($property) && $property != "") {
            $this->resetPage();
        }
    }

    //Create a public property
    public int $country_id =0;

} 

?>

<div>
    <!-- HEADER -->
    <x-header title="Users" separator progress-indicator>
        <x-slot:middle class="!justify-end">
            <x-input placeholder="Search..." wire:model.live.debounce="search" clearable icon="o-magnifying-glass" />
        </x-slot:middle>
        <x-slot:actions>
            <x-button label="Filters" @click="$wire.drawer = true" responsive icon="o-funnel" badge-classes="badge-warning" />
            <x-button label="create" link="/users/create" responsive icon="o-plus" class="btn-primary" />
        </x-slot:actions>
    </x-header>

    <!-- TABLE  -->
    <x-card shadow>
        <x-table :headers="$headers" :rows="$users" :sort-by="$sortBy" with-pagination link="users/{id}/edit">
            @scope('cell_avatar', $user)
            <x-avatar image="{{ $user->avatar ?? '/empty-user.jpg' }}" class="!w-10" />
            @endscope
            @scope('actions', $user)
            <x-button icon="o-trash" wire:click="delete({{ $user['id'] }})" wire:confirm="Are you sure?" spinner class="btn-ghost btn-sm text-error" />
            @endscope
            @scope('actions', $user)
            <x-button label="Open" @click="$wire.myModal1 = true" />
            @endscope
        </x-table>
    </x-card>

    <!-- FILTER DRAWER -->
    <x-drawer wire:model="drawer" title="Filters" right separator with-close-button class="lg:w-1/3">
        <div class="grid gap-5">
            <x-input placeholder="Search..." wire:model.live.debounce="search" icon="o-magnifying-glass" @keydown.enter="$wire.drawer = false" />
            <x-select placeholder="Country" wire:model.live="country_id" :options="$countries" icon="o-flag" placeholder-value="0" /> 
            <x-slot:actions>
                <x-button label="Reset" icon="o-x-mark" wire:click="clear" spinner />
                <x-button label="Done" icon="o-check" class="btn-primary" @click="$wire.drawer = false" />
            </x-slot:actions>
        </div>
    </x-drawer>

    {{-- Modal --}}
    <x-modal wire:model="myModal1" title="Hey" class="backdrop-blur">
        Press `ESC`, click outside or click `CANCEL` to close.

        <x-slot:actions>
            <x-button label="Cancel" @click="$wire.myModal1 = false" />
        </x-slot:actions>
    </x-modal>
</div>
