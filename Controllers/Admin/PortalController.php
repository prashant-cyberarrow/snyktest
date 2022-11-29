<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\ImageTrait;
use App\Http\Traits\UserLogTrait;
use App\Portal;

class PortalController extends Controller
{
    use ImageTrait, UserLogTrait;

    public function __construct()
    {
        $this->middleware('auth:web');
    }

    public function AjaxImageUpload(Request $request)
    {
        $formImage = "logo";
        $files = $request->file('logo');
        $this->imageUpload($request, $files, 'portal', 'portals', $formImage);
    }

    public function index()
    {
        if (request()->session()->has('ajaximage')) {
            $image = request()->session()->get('ajaximage');
            @unlink('uploads/portals/' . $image);
        }
        $portals = Portal::orderBy('id', 'desc')->where('delete_status', '0')->paginate(10);
        return view('admin.list.portal', compact('portals'));
    }

    public function fetch(Request $request)
    {
        $portalName = $request->portalName;

        $portals = Portal::when($portalName, function ($query, $portalName) {
            return $query->where("name", "LIKE", "%$portalName%");
        })->orderBy('id', 'desc')->paginate(10);

        return view('admin.list.ajaxlist.portal', compact('portals'));
    }

    public function create()
    {
        return view('admin.form.portal');
    }

    public function store()
    {
        $this->validate(request(), [
            'name' => 'required',
            'link' => 'required',
            'rss_link' => 'required',
            'logo' => 'image|mimes:jpg,jpeg,svg,png,gif|max:2048'
        ]);

        $portal =  new Portal();

        $portal->name = request('name');
        $portal->contact = request('contact');
        $portal->link = request('link');
        $portal->rss_link = request('rss_link');
        $portal->facebook = request('facebook');
        $portal->twitter = request('twitter');
        $portal->youtube = request('youtube');
        $portal->linkedin = request('linkedin');
        $portal->instagram = request('instagram');
        $portal->publish_status =  request('publish_status');

        $portal->logo = request()->session()->get('ajaximage');

        $portal->save();

        request()->session()->forget('ajaximage');

        $this->storeUserLog('Portal', $portal->id,$portal->title, 'create');

        return redirect('/ps-admin/portals')->with('success', 'Portal added successsfully');
    }

    public function edit($id)
    {
        $portal = Portal::where('id', $id)->first();
        return view('admin.form.portal', compact('portal'));
    }

    public function update($id)
    {
        $portal = Portal::where('id', $id)->first();

        $this->validate(request(), [
            'name' => 'required',
            'link' => 'required',
            'rss_link' => 'required',
            'logo' => 'image|mimes:jpeg,jpg,svg,png,gif|max:2048'
        ]);

        $file = request()->file('logo');
        if ($file != null) {
            $image = $portal->logo;
            @unlink('uploads/portals/' . $image);
            Portal::where('id', $id)->update([
                'logo' => request()->session()->get('ajaximage'),
            ]);
        }

        Portal::where('id', $id)->update([
            'name' => request('name'),
            'contact' => request('contact'),
            'link' => request('link'),
            'rss_link' => request('rss_link'),
            'facebook' => request('facebook'),
            'twitter' => request('twitter'),
            'youtube' => request('youtube'),
            'linkedin' => request('linkedin'),
            'instagram' => request('instagram'),
            'publish_status' => request('publish_status')
        ]);

        request()->session()->forget('ajaximage');

        $this->storeUserLog('Portal', $portal->id,$portal->name, 'update');

        return redirect('/ps-admin/portals')->with('success', 'Portal updated successfully');
    }

    public function destroy($id)
    {
        $portal = Portal::where('id', $id)->first();

        if (isset($portal)) {
            $data = ([
                'delete_status' => '1',
            ]);

            Portal::where('id', $id)->update($data);
            return redirect('/ps-admin/portals')->with('success', 'Portal deleted successfully.');
        }

        $this->storeUserLog('Portal', $portal->id,$portal->name, 'delete');

        return redirect('/ps-admin/portals')->with('error', 'Portal delete failed.');
    }

    // public function removeImage($id)
    // {
    //     $advertisement = Advertisement::where('id', $id)->first();

    //     if (isset($advertisement)) {
    //         //removing  image from folder
    //         $image = $advertisement->image;
    //         @unlink('uploads/notices/' . $image);

    //         //removing image from data base
    //         $data2 = (['image' => null]);
    //         Advertisement::where('id', $id)->update($data2);
    //         return back()->with('success', 'Photo deletion Success.');
    //     }
    //     return back()->with('error', 'Photo deletion failed.');
    // }
}
