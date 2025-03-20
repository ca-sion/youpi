<?php

namespace App\Http\Controllers;

use App\Models\Resource;
use Illuminate\Http\RedirectResponse;

class ResourceController extends Controller
{
    /**
     * View the resource' share url.
     */
    public function share(string $resource): RedirectResponse
    {
        $resource = Resource::findOrFail($resource);

        return redirect($resource->shareUrl);
    }
}
