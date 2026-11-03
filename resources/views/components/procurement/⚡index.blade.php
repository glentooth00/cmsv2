<?php

use Livewire\Component;
use Livewire\WithPagination;
use App\Models\Procurement;
use Flux\Flux;

new class extends Component
{
    use WithPagination;

    public $mode = '';

    public function render()
    {
        return view('components.procurement.⚡index', [
            'modes' => Procurement::latest()->paginate(10),
        ]);
    }

    public function saveProcurementMode()
    {
        $this->validate([
            'mode' => 'required|string|max:255|unique:procurements,mode',
        ]);

        Procurement::create([
            'mode' => $this->mode,
        ]);

        $this->reset('mode');
        $this->resetValidation();

        Flux::modal('add-procurement')->close();

        Flux::toast(
            heading: 'Procurement Mode Created',
            text: 'Procurement mode has been successfully saved.',
            variant: 'success'
        );
    }

    public function confirmDelete($id)
    {
        $mode = Procurement::findOrFail($id);

        $modeName = $mode->mode;

        $mode->delete();

        Flux::toast(
            heading: 'Procurement Mode Deleted',
            text: "\"{$modeName}\" has been deleted successfully.",
            variant: 'success'
        );
    }
};

?>

<div>

    <div class="space-y-6">

        {{-- Header --}}
        <div class="flex flex-col gap-4 md:flex-row md:items-center md:justify-between">

            <div>
                <h1 class="text-2xl font-bold tracking-tight">
                    Procurement Modes
                </h1>

                <p class="text-sm text-zinc-500">
                    Manage procurement modes used by the system.
                </p>
            </div>

            <flux:button
                color="green"
                icon="plus"
                x-on:click="$flux.modal('add-procurement').show()">
                Add Procurement Mode
            </flux:button>

        </div>

        {{-- Table --}}
        <flux:table :paginate="$modes" sticky>

            <flux:table.columns>
                <flux:table.column>Procurement Mode</flux:table.column>
                <flux:table.column>Created</flux:table.column>
                <flux:table.column align="end">Actions</flux:table.column>
            </flux:table.columns>

            <flux:table.rows>

                @forelse($modes as $mode)

                    <flux:table.row :key="$mode->id">

                        <flux:table.cell>
                            {{ $mode->mode }}
                        </flux:table.cell>

                        <flux:table.cell>
                            {{ $mode->created_at->format('F d, Y') }}
                        </flux:table.cell>

                        <flux:table.cell align="end">

                            <div class="flex items-center justify-end gap-2">

                                <flux:button
                                    size="sm"
                                    variant="ghost"
                                    icon="pencil">
                                    Edit
                                </flux:button>

                                <flux:button
                                    size="sm"
                                    variant="danger"
                                    icon="trash"
                                    wire:click="confirmDelete({{ $mode->id }})">
                                    Delete
                                </flux:button>

                            </div>

                        </flux:table.cell>

                    </flux:table.row>

                @empty

                    <flux:table.row>

                        <flux:table.cell colspan="3" class="py-8 text-center text-zinc-500">
                            No procurement modes found.
                        </flux:table.cell>

                    </flux:table.row>

                @endforelse

            </flux:table.rows>

        </flux:table>

    </div>

    {{-- Add Procurement Modal --}}
    <flux:modal
        name="add-procurement"
        class="md:w-96">

        <div class="space-y-6">

            <div>

                <flux:heading size="lg">
                    Add Procurement Mode
                </flux:heading>

                <flux:text class="mt-2">
                    Enter the procurement mode below.
                </flux:text>

            </div>

            <flux:input
                label="Procurement Mode"
                placeholder="e.g. Public Bidding"
                wire:model.defer="mode"
            />

            @error('mode')
                <p class="text-sm text-red-500">{{ $message }}</p>
            @enderror

            <div class="flex justify-end gap-2">

                <flux:button
                    variant="ghost"
                    x-on:click="$flux.modal('add-procurement').hide()">
                    Cancel
                </flux:button>

                <flux:button
                    color="green"
                    icon="check"
                    wire:click="saveProcurementMode">
                    Save
                </flux:button>

            </div>

        </div>

    </flux:modal>

</div>