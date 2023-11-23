<?php

namespace App\Http\Controllers;

use App\Models\Document;
use App\Models\Trainer;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\View\View;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Response;

class DocumentController extends Controller
{
    /**
     * Show the page for a given document.
     */
    public function show(string $document): View|RedirectResponse
    {
        $document = Document::findOrFail($document);

        SEOMeta::setTitle($document->name);
        OpenGraph::setTitle($document->name);

        return redirect(route('documents.pdf', compact('document')));

        return view('documents.show', [
            'document' => $document,
        ]);
    }

    /**
     * Show the pdf for a given document.
     */
    public function pdf(string $document): Response
    {
        $document = Document::findOrFail($document);

        $pdf = PDF::loadView('documents.pdf', compact('document'))
            ->setPaper('a4', 'portrait')
            ->setWarnings(false)
            ->set_option("isPhpEnabled", true);

        return $pdf->stream($document->slugName.'-'.str($document->name)->slug('_', 'fr').'.pdf');
    }
    /**
     * Show the page for all documents.
     */
    public function index(): View
    {
        $documents = Document::whereNotIn('type', ['letter', 'travel'])
        ->orderBy('id', 'desc')
        ->get();

        SEOMeta::setTitle('Documents');
        OpenGraph::setTitle('Documents');

        return view('documents.index', [
            'documents' => $documents,
        ]);
    }
}
