<?php

namespace App\Livewire;

use Livewire\Component;
use App\Models\Resource;

class SuccessResource extends Component
{
    public Resource $resource;

    public function render()
    {
        $resource = $this->resource;

        return view('livewire.success-resource', compact('resource'));
    }
}
