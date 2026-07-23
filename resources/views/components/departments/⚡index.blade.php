<?php 
use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Departments;

new class extends Component
{
    use WithPagination;

    public $department_name = '';
    public $department_id;
    public $editing = false;

    public function render()
    {
        return view('components.departments.⚡index', [
            'departments' => Departments::latest()->paginate(10),
        ]);
    }

    public function saveDepartment()
    {
        $this->validate([
            'department_name' => 'required|string|max:255|unique:departments,department_name',
        ]);

        Departments::create([
            'department_name' => $this->department_name,
        ]);

        $this->reset('department_name');
        $this->resetValidation();

        Flux::modal('add-department')->close();

        Flux::toast(
            heading: 'Department Created',
            text: 'Department has successfully saved.',
            variant: 'success'
        );
    }

    public function confirmDelete($id)
    {
        $department = Departments::findOrFail($id);

        $departmentName = $department->department_name;

        $department->delete();

        Flux::toast(
            heading: 'Department Deleted',
            text: "\"{$departmentName}\" has been deleted successfully.",
            variant: 'success'
        );
    }
};
?>

<div>

    <div class="space-y-6">

        {{-- Page Header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Departments
                </h1>

                <p class="text-sm text-zinc-500">
                    Manage departments and organizational units.
                </p>
            </div>

            <div class="flex items-center gap-2">

                {{-- <flux:input
                    icon="magnifying-glass"
                    placeholder="Search..."
                    wire:model.live.debounce.300ms="search"
                /> --}}

                <flux:button
                    color="green"
                    icon="plus"
                    x-on:click="$flux.modal('add-department').show()">
                    Add Department
                </flux:button>

            </div>

        </div>

    <flux:table :paginate="$departments" sticky>

        <flux:table.columns>
            <flux:table.column>Department Name</flux:table.column>
            <flux:table.column align="end">Actions</flux:table.column>
        </flux:table.columns>

        <flux:table.rows>

            @forelse($departments as $department)

                <flux:table.row :key="$department->id">

                    <flux:table.cell>
                        {{ $department->department_name }}
                    </flux:table.cell>

                    <flux:table.cell align="end">

                        <div class="flex items-center justify-end gap-2">

                            <flux:button
                                size="sm"
                                variant="ghost"
                                icon="pencil"
                                wire:click="editDepartment({{ $department->id }})">
                                Edit
                            </flux:button>

                            <flux:button
                                size="sm"
                                variant="danger"
                                icon="trash"
                                wire:click="confirmDelete({{ $department->id }})">
                                Delete
                            </flux:button>

                        </div>

                    </flux:table.cell>

                </flux:table.row>

            @empty

                <flux:table.row>

                    <flux:table.cell colspan="2" class="text-center py-5 text-zinc-500">
                        No departments found.
                    </flux:table.cell>

                </flux:table.row>

            @endforelse

        </flux:table.rows>

    </flux:table>

    </div>

    {{-- Modal --}}
    <flux:modal name="add-department" class="md:w-96">

        <div class="space-y-6">

            <div>
                <flux:heading size="lg">
                    Add Department
                </flux:heading>

                <flux:text class="mt-2">
                    Enter the department name below.
                </flux:text>
            </div>

            <flux:input
                label="Department Name"
                placeholder="e.g. Information Technology"
                wire:model.defer="department_name"
            />

            @error('department_name')
                <p class="text-sm text-red-500">{{ $message }}</p>
            @enderror

            <div class="flex justify-end gap-2">

                <flux:button
                    variant="ghost"
                    x-on:click="$flux.modal('add-department').hide()">
                    Cancel
                </flux:button>

                <flux:button
                    color="green"
                    icon="check"
                    wire:click="saveDepartment">
                    Save
                </flux:button>

            </div>

        </div>

    </flux:modal>

</div>