<?php

namespace App\Http\Controllers\Admin;

use App\Portal;
use App\LinkSetup;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class LinkSetupController extends Controller
{
    private $site_html;

    public function linkGenerate(Request $request)
    {
        // dd($request->rss_link);
        $url = $request->rss_link;
        $portal_id = $request->portal_id;

        $ch = curl_init();

        curl_setopt($ch, CURLOPT_AUTOREFERER, TRUE);
        curl_setopt($ch, CURLOPT_HEADER, 0);
        curl_setopt($ch, CURLOPT_RETURNTRANSFER, 1);
        curl_setopt($ch, CURLOPT_URL, $url);
        curl_setopt($ch, CURLOPT_FOLLOWLOCATION, TRUE);

        $xml = curl_exec($ch);
        curl_close($ch);

        $xml = simplexml_load_string($xml, "SimpleXMLElement", LIBXML_NOCDATA);
        $json = json_encode($xml);
        $array = json_decode($json, TRUE);

        $collection = collect($array['channel']['item']);

        // dump($xml);
        // dd($collection);
        return view('admin.list.continue-link-generate', compact('collection', 'array', 'portal_id'));

        // return redirect()->route('admin.continue-link-generate',['id' => $array]);
        // return redirect()->route('admin.continue-link-generate')->with(compact('array'));
        // return redirect()->route('admin.continue-link-generate', $collection);

        dd($array);
        if ($array == true) {

            $news = $array['channel']['item'];
            dd($news);
            for ($i = 0; $i < count($news); $i++) {
                dump($news[$i]);
                // $graph = OpenGraph::fetch($news[$i]['link']);
                // dd($graph);

                $site_url = $news[$i]['link'];
                // dd($site_url);
                $site_html =  file_get_contents($site_url);
                $matches = null;
                preg_match_all('~<\s*meta\s+property="(og:[^"]+)"\s+content="([^"]*)~i', $site_html, $matches);
                $ogtags = array();
                for ($i = 0; $i < count($matches[1]); $i++) {
                    $ogtags[$matches[1][$i]] = $matches[2][$i];
                }
                dd($ogtags['og:image']);
                $news[$i]['image'] = $ogtags['og:image'];
                //yeta
            }
            dd($news);
        }
    }

    public function submitLinkGenerate(Request $request)
    {
        $checked = $request->check;
        $portal_id = $request->portal_id;
        // dump($portal_id);

        foreach ($checked as $row) {
            // dump($request->title[$row]);
            // dump($request->link[$row]);
            // dump($request->description[$row]);

            $site_url = $request->link[$row];

            $ch = curl_init();
            curl_setopt($ch, CURLOPT_TIMEOUT, 1);
            curl_setopt($ch, CURLOPT_URL, $site_url);
            curl_setopt($ch, CURLOPT_RETURNTRANSFER, TRUE);
            if (FALSE === ($retval = curl_exec($ch))) {
                error_log(curl_error($ch));
            } else {
                $this->site_html = $retval;
            }

            // if (!@file_get_contents($site_url))
            // {
            //     return back();
            // }else{
            //     $site_html =  file_get_contents($site_url);
            // }

            $matches = null;
            preg_match_all('~<\s*meta\s+property="(og:[^"]+)"\s+content="([^"]*)~i', $this->site_html, $matches);
            $ogtags = array();
            for ($i = 0; $i < count($matches[1]); $i++) {
                $ogtags[$matches[1][$i]] = $matches[2][$i];
            }

            LinkSetup::Create([
                'portal_id' => $portal_id,
                'title' => $request->title[$row],
                'description' => $request->description[$row],
                'link' => $request->link[$row],
                'image' => $ogtags['og:image'] ?? '',
            ]);
        }

        return "Success!";
    }

    public function continueLinkGenerate()
    {
        dd("wait");
    }

    public function index()
    {
        $portalLists = LinkSetup::get();
    }

    public function create()
    {
        $portals = Portal::where('publish_status', '1')->where('delete_status', '0')->get();
        return view('admin.form.link-setup', compact('portals'));
    }
}
