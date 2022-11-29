<?php

namespace App\Http\Controllers\Admin;

use App\ShortLink;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ShortLinkController extends Controller
{
    public function generateShortURL()
    {
        $result = base_convert(rand(10000,999999),10,36);

        $data = ShortLink::whereShort($result)->first();

        if($data != null)
        {
            $this->generateShortURL();
        }

        return $result;
    }

    public function shortLinkForm()
    {
        return view('admin.form.shortlink');
    }

    public function short(Request $request)
    {
        $url = ShortLink::whereUrl($request->url)->first();

        if($url == null)
        {
            $short = $this->generateShortURL();
            ShortLink::create([
                'url' => $request->url,
                'short' => $short,
                'short_url' => 'https://nectar/'.$short
            ]);
            $url = ShortLink::whereUrl($request->url)->first();
        }
        return view('admin.form.shortlinkresult',compact('url'));
    }

    public function shortLink($link)
    {
        $url = ShortLink::whereShort($link)->first();
        return redirect($url->url);
    }
}
