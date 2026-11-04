<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Gate;
use Illuminate\Support\Facades\Storage;
use Symfony\Component\HttpFoundation\Response;

class DocumentController extends Controller
{
    /**
     * Download or view private document through policy check
     */
    public function show(Request $request, Document $document): Response
    {
        Gate::authorize('view', $document);

        if (! Storage::disk('local')->exists($document->path)) {
            abort(404, 'Berkas dokumen tidak ditemukan di server.');
        }

        $headers = [
            'Content-Type' => $document->mime,
            'Content-Disposition' => ($request->boolean('download') ? 'attachment' : 'inline') . '; filename="' . addslashes($document->original_name) . '"',
        ];

        return response()->file(Storage::disk('local')->path($document->path), $headers);
    }
}

