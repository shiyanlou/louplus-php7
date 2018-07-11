<?php

namespace App\Http\Controllers;

use App\Website;
use App\Jobs\ProcessCrawler;
use Illuminate\Http\Request;

class WebsiteController extends Controller
{
    /**
     * @param Request $request
     */
    public function index(Request $request)
    {
        $websites = Website::paginate(10);

        return view('website.index', ['websites' => $websites]);
    }

    /**
     * @param Request $request
     * @param int $id
     */
    public function show(Request $request, $id)
    {
        if (null === $website = Website::find($id)) {
            abort(404);
        }

        return view('website.show', ['website' => $website]);
    }

    /**
     * @param Request $request
     */
    public function create(Request $request)
    {
        $validatedData = $request->validate([
            'name' => 'required|max:255',
            'uri' => 'required|active_url|unique:websites|max:255',
        ]);

        $uri = $request->input('uri');
        
        $website = new Website();
        $website->name = $request->input('name');
        $website->uri = $request->input('uri');
        $website->status = Website::STATUS_NEW;
        $website->save();

        return redirect()->route('website.index')->with('message', 'Create website success');
    }

    /**
     * @param Request $request
     * @param int $id
     */
    public function delete(Request $request, int $id)
    {
        if (null === $website = Website::find($id)) {
            abort(404);
        }

        $website->delete();

        return redirect()->route('website.index')->with('message', 'Delete website success');
    }

    /**
     * @param Request $request
     * @param int $id
     */
    public function crawl(Request $request, int $id)
    {
        if (null === $website = Website::find($id)) {
            abort(404);
        }

        $website->status = Website::STATUS_RUNNING;
        $website->save();

        ProcessCrawler::dispatch($website);

        // Direct execution, left for easy debug.
        //$client = resolve('WebsiteCrawler\Client');

        //$client->getExecutor()->getUriMap()->selectCurrentDB($website->id);
        //$client->getExecutor()->getUriMap()->flushCurrentDB();
        //$client->getContext()->setWebsiteId($website->id);

        //$client->crawl($website->uri);

        return redirect()->route('website.index');
    }
}
