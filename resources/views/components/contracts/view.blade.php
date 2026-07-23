<?php

use App\Models\Contracts;
use Livewire\Component;

new class extends Component
{
    public Contracts $contract;

    public function mount(Contracts $contract)
    {
        $this->contract = $contract;
    }
};

?>

<!--
This is a starter template for the redesigned Contract View page.
Continue by replacing the body with the redesigned sections:
1. Premium Header
2. Contract Overview + Summary
3. Timeline
4. PDF Preview Modal
5. Activity
-->

<div class="space-y-6">
    <flux:heading size="xl">{{ $contract->contract_name }}</flux:heading>
    <flux:text>Redesigned Contract View (Starter)</flux:text>
</div>
