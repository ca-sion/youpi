<?php

namespace App\Http\Controllers;

use App\Models\Document;
use Illuminate\View\View;
use App\Enums\DocumentType;
use Illuminate\Http\Response;
use Barryvdh\DomPDF\Facade\Pdf;
use Illuminate\Http\RedirectResponse;
use Artesaos\SEOTools\Facades\SEOMeta;
use Artesaos\SEOTools\Facades\OpenGraph;

class DocumentController extends Controller
{
    /**
     * Show the page for a given document.
     */
    public function show(string $document): View|RedirectResponse
    {
        $document = Document::findOrFail($document);

        SEOMeta::setTitle($document->name.' ('.$document->identifier.')');
        OpenGraph::setTitle($document->name.' ('.$document->identifier.')');

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

        if ($document->type == DocumentType::TRAVEL) {
            $pdf = PDF::loadView('documents.pdf-travel', compact('document'))
                ->setPaper('a4', 'portrait')
                ->setWarnings(false)
                ->set_option('isPhpEnabled', true);
        } else {
            $pdf = PDF::loadView('documents.pdf', compact('document'))
                ->setPaper('a4', 'portrait')
                ->setWarnings(false)
                ->set_option('isPhpEnabled', true);
        }

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
