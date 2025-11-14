<?php

namespace App\Http\Controllers;

use Illuminate\Http\Request;
use Proxy\Proxy;
use Proxy\Http\Request as ProxyRequest;
use Proxy\Plugin\CookiePlugin;
use Proxy\Plugin\HeaderRewritePlugin;
use Proxy\Plugin\ProxifyPlugin;
use Proxy\Plugin\StreamPlugin;

class IframeController extends Controller
{
    public function proxy(Request $request)
    {
        $url = url_decrypt($request->get('q'));

        if (!filter_var($url, FILTER_VALIDATE_URL)) {
            return response('Invalid URL.', 400);
        }

        $proxy = new Proxy();
        $proxy->addSubscriber(new HeaderRewritePlugin());
        $proxy->addSubscriber(new StreamPlugin());
        $proxy->addSubscriber(new CookiePlugin());
        $proxy->addSubscriber(new ProxifyPlugin());
        
        try {
            $req = ProxyRequest::createFromGlobals();
            $req->get->clear();
            $response = $proxy->forward($req, $url);

            $response->send();
        } catch (\Exception $e) {
            return response('Proxy error: ' . $e->getMessage(), 500);
        }
    }
}
