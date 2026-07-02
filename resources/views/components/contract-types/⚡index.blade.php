

<?php

use Livewire\Component;
use Flux\Flux;
use App\Models\ContractTypes;

new class extends Component {

    public $contractType;
    public $contract_duration;
    public $contract_ert;
    
    public function save(){
        
        $contractTypedata = [
            'contract_type' => $this->contractType,
            'contract_duration' => $this->contract_duration,
            'contract_ert' => $this->contract_ert,
        ]; 

        ContractTypes::create($contractTypedata);

        Flux::toast(
        heading: 'Changes saved',
        text: 'Your changes have been saved.',
        variant: 'success');

        $this->reset();

        flux::modal('create-contract-type')->close();

    }

    public function render()
    {

        $contractTypes = ContractTypes::paginate(10);

        return view('components.contract-types.⚡index', [
            'contractTypes' => $contractTypes,
        ]);

    }

};
?>

<div class="space-y-4">
    <div class="">
        <flux:heading size="xl">Contract Types</flux:heading>
        <flux:text class="mb-2">Manage contract types</flux:text>
        <flux:separator />
    </div>
    <div class="flex items-center gap-3 mb-1">
        <flux:modal.trigger name="create-contract-type">
            <flux:button size="sm" variant="primary" color="sky">Add Contract Type</flux:button>
        </flux:modal.trigger>
    </div>

    <flux:table :paginate="$contractTypes">
        <flux:table.columns>
            <flux:table.column>Contract Type</flux:table.column>
            <flux:table.column>Contract Duration</flux:table.column>
            <flux:table.column>Contract ERT</flux:table.column>
        </flux:table.columns>
        <flux:table.rows>
            @forelse ($contractTypes as $contractType)
                <flux:table.row>
                    <flux:table.cell>
                        @switch($contractType->contract_type)
                            @case('Employment Contract')
                                <flux:badge variant="primary" color="amber">Employment Contract</flux:badge>
                                @break
                            @case('Temporary Lighting Contract')
                                <flux:badge variant="primary" color="indigo">Temporary Lighting Contract</flux:badge>
                                @break
                            @case('Rental Contract')
                                <flux:badge variant="primary" color="fuchsia">Rental Contract</flux:badge>
                                @break
                            @case('Infrastructure Contract')
                                <flux:badge variant="primary" color="emerald">Infrastructure Contract</flux:badge>
                                @break
                            @case('Goods Contract')
                                <flux:badge variant="primary" color="blue">Goods Contract</flux:badge>
                                @break
                            @case('Service and Consultancy Contract')
                                <flux:badge variant="primary" color="teal">Service and Consultancy Contract</flux:badge>
                                @break
                            @case('Power Suppliers Contract (LONG TERM)')
                                <flux:badge variant="primary" color="lime">Power Suppliers Contract (LONG TERM)</flux:badge>
                                @break
                            @case('Power Suppliers Contract (SHORT TERM)')
                                <flux:badge variant="primary" color="zinc">Power Suppliers Contract (SHORT TERM)</flux:badge>
                                @break
                            @case('Transformer Rental Contract')
                                <flux:badge variant="primary" color="red">Transformer Rental Contract</flux:badge>
                                @break
                        
                            @default
                                
                        @endswitch
                    </flux:table.cell>
                    <flux:table.cell>{{ $contractType->contract_duration ?? '--' }}</flux:table.cell>
                    <flux:table.cell>{{ $contractType->contract_ert ?? '--' }}</flux:table.cell>
                </flux:table.row>
            @empty
                <flux:table.row>
                    <flux:table.cell colspan="3" class="text-center">
                        No contract types found.
                    </flux:table.cell>
                </flux:table.row>
            @endforelse
</flux:table.row>
        </flux:table.>



    {{-- contract type modal --}}
    <flux:modal name="create-contract-type" class="md:w-96">
        <form wire:submit="save">
            <div class="space-y-6">
                <div>
                    <flux:heading size="lg">Create Contract Type</flux:heading>
                    <flux:text class="mt-1">Add new contract type.</flux:text>
                </div>
                <flux:input label="Contract Type" wire:model="contractType" placeholder="Enter contract type" />
                <flux:input label="Contract Duration" wire:model="contract_duration" placeholder="Enter contract duration" />
                <flux:input label="Contract ERT" wire:model="contract_ert" placeholder="Enter contract threshold" />
                <div class="flex">
                    <flux:spacer />
                    <flux:button type="submit" variant="primary" class="cursor-pointer">Save</flux:button>
                </div>
            </div>
            </form>
    </flux:modal>

</div>

