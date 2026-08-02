<?php

namespace App\Livewire\Admin\LocationsServices;

use App\Livewire\Admin\Concerns\InteractsWithAdminToast;
use App\Models\County;
use App\Models\Township;
use Illuminate\Validation\Rule;
use Livewire\Component;

class Index extends Component
{
    use InteractsWithAdminToast;

    public bool $readyToLoad = true;
    public ?int $selectedCountyId = null;
    public bool $showCountyModal = false;
    public ?int $editingCountyId = null;
    public string $countyName = '';
    public ?string $countyDescription = null;
    public int $countySortOrder = 0;
    public bool $countyIsActive = true;
    public bool $showTownshipModal = false;
    public ?int $editingTownshipId = null;
    public ?int $townshipCountyId = null;
    public string $townshipName = '';
    public int $townshipSortOrder = 0;
    public bool $townshipIsActive = true;
    public bool $showDeleteModal = false;
    public ?string $pendingDeleteType = null;
    public ?int $pendingDeleteId = null;
    public ?string $pendingDeleteLabel = null;

    public function loadData(): void
    {
        $this->readyToLoad = true;
    }

    public function selectCounty(int $id): void
    {
        $this->selectedCountyId = $id;
        $this->townshipCountyId = $id;
        $this->resetTownshipForm();
    }

    public function openCreateCountyModal(): void
    {
        $this->resetCountyForm();
        $this->showCountyModal = true;
    }

    public function openEditCountyModal(int $id): void
    {
        $county = County::query()->findOrFail($id);

        $this->editingCountyId = $county->id;
        $this->countyName = $county->name;
        $this->countyDescription = $county->description;
        $this->countySortOrder = (int) $county->sort_order;
        $this->countyIsActive = (bool) $county->is_active;
        $this->selectedCountyId = $county->id;
        $this->townshipCountyId = $county->id;
        $this->showCountyModal = true;
    }

    public function closeCountyModal(): void
    {
        $this->showCountyModal = false;
        $this->resetCountyForm();
    }

    public function saveCounty(): void
    {
        $isEditing = $this->editingCountyId !== null;

        $validated = $this->validate([
            'countyName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('counties', 'name')->ignore($this->editingCountyId),
            ],
            'countyDescription' => ['nullable', 'string'],
            'countySortOrder' => ['required', 'integer', 'min:0'],
            'countyIsActive' => ['boolean'],
        ]);

        $county = $this->editingCountyId
            ? County::query()->findOrFail($this->editingCountyId)
            : new County();

        $county->fill([
            'name' => $validated['countyName'],
            'description' => $validated['countyDescription'],
            'sort_order' => $validated['countySortOrder'],
            'is_active' => $validated['countyIsActive'],
        ]);
        $county->save();

        $this->selectedCountyId = $county->id;
        $this->townshipCountyId = $county->id;
        $this->dispatch('locations-county-selected', countyId: $county->id, countyName: $county->name);
        $this->dispatch('locations-refresh-townships', countyId: $county->id);
        $this->toastSuccess($isEditing ? 'County updated.' : 'County created.', 'Locations');
        $this->closeCountyModal();
    }

    public function promptDeleteCounty(int $id): void
    {
        $county = County::query()->findOrFail($id);

        $this->pendingDeleteType = 'county';
        $this->pendingDeleteId = $county->id;
        $this->pendingDeleteLabel = $county->name;
        $this->showDeleteModal = true;
    }

    public function openCreateTownshipModal(?int $countyId = null): void
    {
        $this->resetTownshipForm();
        if ($countyId) {
            $this->selectedCountyId = $countyId;
        }
        $this->townshipCountyId = $this->selectedCountyId;
        $this->showTownshipModal = true;
    }

    public function openEditTownshipModal(int $id): void
    {
        $township = Township::query()->findOrFail($id);

        $this->editingTownshipId = $township->id;
        $this->townshipCountyId = (int) $township->county_id;
        $this->townshipName = $township->name;
        $this->townshipSortOrder = (int) $township->sort_order;
        $this->townshipIsActive = (bool) $township->is_active;
        $this->selectedCountyId = (int) $township->county_id;
        $this->showTownshipModal = true;
    }

    public function closeTownshipModal(): void
    {
        $this->showTownshipModal = false;
        $this->resetTownshipForm();
    }

    public function saveTownship(): void
    {
        $isEditing = $this->editingTownshipId !== null;

        if (! $this->townshipCountyId && ! $this->selectedCountyId) {
            $this->toastWarning('Select a county first.', 'Locations');
            return;
        }

        $countyId = (int) ($this->townshipCountyId ?? $this->selectedCountyId);
        County::query()->findOrFail($countyId);

        $validated = $this->validate([
            'townshipName' => [
                'required',
                'string',
                'max:255',
                Rule::unique('townships', 'name')
                    ->where(fn ($query) => $query->where('county_id', $countyId))
                    ->ignore($this->editingTownshipId),
            ],
            'townshipSortOrder' => ['required', 'integer', 'min:0'],
            'townshipIsActive' => ['boolean'],
        ]);

        $township = $this->editingTownshipId
            ? Township::query()->findOrFail($this->editingTownshipId)
            : new Township();

        $township->fill([
            'county_id' => $countyId,
            'name' => $validated['townshipName'],
            'sort_order' => $validated['townshipSortOrder'],
            'is_active' => $validated['townshipIsActive'],
        ]);
        $township->save();

        $this->selectedCountyId = $countyId;
        $countyName = County::query()->whereKey($countyId)->value('name');
        $this->dispatch('locations-county-selected', countyId: $countyId, countyName: $countyName);
        $this->dispatch('locations-refresh-townships', countyId: $countyId);
        $this->toastSuccess($isEditing ? 'Township updated.' : 'Township created.', 'Locations');
        $this->closeTownshipModal();
    }

    public function promptDeleteTownship(int $id): void
    {
        $township = Township::query()->findOrFail($id);

        $this->pendingDeleteType = 'township';
        $this->pendingDeleteId = $township->id;
        $this->pendingDeleteLabel = $township->name;
        $this->showDeleteModal = true;
    }

    public function cancelDelete(): void
    {
        $this->showDeleteModal = false;
        $this->pendingDeleteType = null;
        $this->pendingDeleteId = null;
        $this->pendingDeleteLabel = null;
    }

    public function confirmDelete(): void
    {
        if (! $this->pendingDeleteType || ! $this->pendingDeleteId) {
            return;
        }

        if ($this->pendingDeleteType === 'county') {
            $deletedCountyId = $this->pendingDeleteId;
            County::query()->findOrFail($deletedCountyId)->delete();
            $this->dispatch('locations-county-deleted', countyId: $deletedCountyId);

            if ($this->selectedCountyId === $deletedCountyId) {
                $this->selectedCountyId = null;
                $this->townshipCountyId = null;
                $this->dispatch('locations-county-selected', countyId: null);
                $this->dispatch('locations-refresh-townships', countyId: null);
            }

            if ($this->editingCountyId === $deletedCountyId) {
                $this->closeCountyModal();
            }

            $this->toastSuccess('County deleted.', 'Locations');
        }

        if ($this->pendingDeleteType === 'township') {
            $township = Township::query()->findOrFail($this->pendingDeleteId);
            $township->delete();

            if ($this->editingTownshipId === $this->pendingDeleteId) {
                $this->closeTownshipModal();
            }

            $this->dispatch('locations-refresh-townships', countyId: (int) $township->county_id);
            $this->toastSuccess('Township deleted.', 'Locations');
        }

        $this->cancelDelete();
    }

    public function render()
    {
        $townships = collect();
        $counties = collect();

        if ($this->readyToLoad) {
            $counties = County::query()
                ->withCount('townships')
                ->orderBy('sort_order')
                ->orderBy('name')
                ->get();

            if ($this->selectedCountyId) {
                $townships = Township::query()
                    ->where('county_id', $this->selectedCountyId)
                    ->orderBy('sort_order')
                    ->orderBy('name')
                    ->get();
            }
        }

        return view('livewire.admin.locations-services.index', compact('counties', 'townships'))
            ->layout('layouts.admin');
    }

    public function resetCountyForm(): void
    {
        $this->editingCountyId = null;
        $this->countyName = '';
        $this->countyDescription = null;
        $this->countySortOrder = 0;
        $this->countyIsActive = true;
        $this->resetValidation([
            'countyName',
            'countyDescription',
            'countySortOrder',
            'countyIsActive',
        ]);
    }

    public function resetTownshipForm(): void
    {
        $this->editingTownshipId = null;
        $this->townshipName = '';
        $this->townshipSortOrder = 0;
        $this->townshipIsActive = true;
        $this->resetValidation([
            'townshipName',
            'townshipSortOrder',
            'townshipIsActive',
        ]);
    }
}
