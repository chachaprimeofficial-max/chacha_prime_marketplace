<?php

namespace App\Http\Controllers\Customer;

use App\Http\Controllers\Controller;
use App\Services\ReturnService;
use Illuminate\Http\Request;

class ReturnController extends Controller
{
    public function store(Request $request, ReturnService $returns)
    {
        $data = $request->validate([
            'order_id' => ['required','integer'], 'order_item_id' => ['required','integer'],
            'type' => ['required','in:refund,replacement'], 'reason' => ['required','string','max:1000'],
            'shipping_label' => ['nullable','file','mimes:pdf,jpg,jpeg,png','max:10240'],
            'media.*' => ['nullable','file','mimes:jpg,jpeg,png,mp4,mov,webm','max:51200'],
        ]);
        $media = [];
        foreach ($request->file('media', []) as $file) $media[] = $file->store('returns/media', 'public');
        $label = $request->file('shipping_label')?->store('returns/labels', 'private');
        $id = $returns->create(auth()->id(), $data['order_id'], $data['order_item_id'], $data['type'], $data['reason'], $media, $label);
        return back()->with('success', 'Return/replacement request #' . $id . ' submitted.');
    }
}
