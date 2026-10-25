<?php

namespace CrudBooster\Http\Controllers;

use CrudBooster\Pages\Page;
use Illuminate\Http\Request;
use Illuminate\Routing\Controller;

class PageController extends Controller
{
    public function show(Request $request)
    {
        $page = $this->page($request);

        return view('cb::pages.page', [
            'page' => $page,
            'data' => method_exists($page, 'data') ? app()->call([$page, 'data']) : [],
            'page_title' => $page::getTitle(),
        ]);
    }

    public function submit(Request $request)
    {
        return app()->call([$this->page($request), 'submit']);
    }

    private function page(Request $request): Page
    {
        $page = app($request->route('page'));
        abort_unless($page->can(), 403);

        return $page;
    }
}
