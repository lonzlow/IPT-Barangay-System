<?php

namespace App\Http\Controllers;

use App\Models\Signature;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;

class SignatureController extends Controller
{
    public function store(Request $request)
    {
        $request->validate([
            'official_id' => ['required', 'uuid', 'exists:officials,id'],
            'label' => ['nullable', 'string', 'max:100'],
            'signature' => ['required', 'image', 'max:2048'],
        ]);

        $file = $request->file('signature');
        $path = $file->store('signatures', 'public');

        $sig = Signature::create([
            'official_id' => $request->input('official_id'),
            'label' => $request->input('label'),
            'path' => $path,
            'uploaded_by' => auth()->id(),
        ]);

        $sig->load('official.resident');

        return response()->json([
            'message' => 'Signature uploaded',
            'signature' => array_merge($sig->toArray(), [
                'url' => Storage::disk('public')->url($path),
            ]),
        ], 201);
    }

    public function destroy(Signature $signature)
    {
        // remove file
        try {
            Storage::disk('public')->delete($signature->path);
        } catch (\Exception $e) {
            // swallow
        }

        $signature->delete();

        return response()->json(['message' => 'Signature deleted']);
    }
}
