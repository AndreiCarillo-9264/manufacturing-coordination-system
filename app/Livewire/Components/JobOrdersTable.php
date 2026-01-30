<?php

namespace App\Livewire\Components;

use App\Models\JobOrder;
use Illuminate\Database\Eloquent\Collection;
use Livewire\Component;
use Livewire\WithPagination;

class JobOrdersTable extends Component
{
    use WithPagination;

    #[Computed]
    public function jobOrders()
    {
        return JobOrder::with(['product', 'encodedBy'])
            ->latest()
            ->paginate(15);
    }

    #[\Livewire\Attributes\On('job-order-created')]
    #[\Livewire\Attributes\On('job-order-status-changed')]
    public function refreshJobOrders()
    {
        $this->resetPage();
    }

    public function render()
    {
        return view('livewire.components.job-orders-table', [
            'jobOrders' => $this->jobOrders,
        ]);
    }
}
