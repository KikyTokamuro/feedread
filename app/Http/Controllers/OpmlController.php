<?php

namespace App\Http\Controllers;

use App\Jobs\RefreshFeed;
use App\Services\OpmlService;
use Illuminate\Http\RedirectResponse;
use Illuminate\Http\Request;
use Illuminate\Http\Response;
use Illuminate\View\View;
use InvalidArgumentException;

class OpmlController extends Controller
{
    public function __construct(
        protected OpmlService $opmlService
    ) {}

    /**
     * Show the import / export page.
     */
    public function index(Request $request): View
    {
        $feedCount = $request->user()->feeds()->count();

        return view('opml.index', compact('feedCount'));
    }

    /**
     * Download every feed of the signed in user as OPML.
     */
    public function export(Request $request): Response
    {
        $xml = $this->opmlService->export($request->user());

        return response($xml, 200, [
            'Content-Type' => 'text/x-opml; charset=UTF-8',
            'Content-Disposition' => 'attachment; filename="feedread-'.now()->format('Y-m-d').'.opml"',
        ]);
    }

    /**
     * Import feeds from an uploaded OPML file.
     */
    public function import(Request $request): RedirectResponse
    {
        // The content is the real validation here, so the upload is only
        // checked for size before being parsed as XML.
        $request->validate([
            'file' => ['required', 'file', 'max:5120'],
        ]);

        try {
            $result = $this->opmlService->import(
                $request->user(),
                (string) $request->file('file')->get()
            );
        } catch (InvalidArgumentException $e) {
            return back()->withErrors(['file' => $e->getMessage()]);
        }

        $status = sprintf(
            'Imported %d feed(s). Skipped %d duplicate(s), rejected %d invalid entr(ies).',
            $result['imported'],
            $result['skipped'],
            $result['invalid']
        );

        // Newly imported feeds have never been fetched. Queue them when a real
        // queue is configured instead of making the request do the work.
        if (config('queue.default') !== 'sync') {
            $request->user()->feeds()
                ->whereNull('last_refreshed_at')
                ->get()
                ->each(fn ($feed) => RefreshFeed::dispatch($feed));

            $status .= ' Articles will be collected in the background.';
        } elseif ($result['imported'] > 0) {
            $status .= ' Run "php artisan feeds:refresh" (or wait for the scheduler) to collect articles.';
        }

        return redirect()->route('opml.index')->with('status', $status);
    }
}
