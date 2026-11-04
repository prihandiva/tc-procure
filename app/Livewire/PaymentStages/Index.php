<?php

namespace App\Livewire\PaymentStages;

use App\Models\AuditLog;
use App\Models\PaymentStage;
use Illuminate\Support\Facades\Gate;
use Livewire\Attributes\Layout;
use Livewire\Component;

#[Layout('layouts.app')]
class Index extends Component
{
    public string $name = '';
    public int $sort_order = 1;
    public bool $is_final = false;
    public bool $is_active = true;

    public ?int $editingStageId = null;
    public bool $showModal = false;

    public function rules(): array
    {
        return [
            'name' => ['required', 'string', 'max:255'],
            'sort_order' => ['required', 'integer', 'min:1'],
            'is_final' => ['boolean'],
            'is_active' => ['boolean'],
        ];
    }

    public function create(): void
    {
        Gate::authorize('create', PaymentStage::class);

        $this->reset(['name', 'sort_order', 'is_final', 'is_active', 'editingStageId']);
        $maxOrder = PaymentStage::max('sort_order') ?? 0;
        $this->sort_order = $maxOrder + 1;
        $this->is_active = true;
        $this->showModal = true;
    }

    public function edit(PaymentStage $stage): void
    {
        Gate::authorize('update', $stage);

        $this->editingStageId = $stage->id;
        $this->name = $stage->name;
        $this->sort_order = $stage->sort_order;
        $this->is_final = $stage->is_final;
        $this->is_active = $stage->is_active;
        $this->showModal = true;
    }

    public function save(): void
    {
        $this->validate();

        if ($this->editingStageId) {
            $stage = PaymentStage::findOrFail($this->editingStageId);
            Gate::authorize('update', $stage);

            $old = $stage->toArray();
            $stage->update([
                'name' => $this->name,
                'sort_order' => $this->sort_order,
                'is_final' => $this->is_final,
                'is_active' => $this->is_active,
            ]);

            AuditLog::log(
                action: 'payment_stage.updated',
                auditable: $stage,
                newValues: $stage->toArray(),
                oldValues: $old,
            );

            session()->flash('success', "Tahapan '{$stage->name}' berhasil diperbarui.");
        } else {
            Gate::authorize('create', PaymentStage::class);

            $stage = PaymentStage::create([
                'name' => $this->name,
                'sort_order' => $this->sort_order,
                'is_final' => $this->is_final,
                'is_active' => $this->is_active,
            ]);

            AuditLog::log(
                action: 'payment_stage.created',
                auditable: $stage,
                newValues: $stage->toArray(),
            );

            session()->flash('success', "Tahapan baru '{$stage->name}' berhasil ditambahkan.");
        }

        $this->showModal = false;
        $this->reset(['name', 'sort_order', 'is_final', 'is_active', 'editingStageId']);
    }

    public function toggleActive(PaymentStage $stage): void
    {
        Gate::authorize('update', $stage);

        $stage->update(['is_active' => ! $stage->is_active]);

        AuditLog::log(
            action: 'payment_stage.status_toggled',
            auditable: $stage,
            newValues: ['is_active' => $stage->is_active],
            oldValues: ['is_active' => ! $stage->is_active],
        );
    }

    public function render()
    {
        Gate::authorize('viewAny', PaymentStage::class);

        $stages = PaymentStage::ordered()->get();

        return view('livewire.payment-stages.index', [
            'stages' => $stages,
            'canManage' => auth()->user()->isSuperAdmin(),
        ]);
    }
}

