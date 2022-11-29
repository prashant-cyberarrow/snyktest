<?php

namespace App\Http\Controllers\Admin;

use App\Information;
use Illuminate\Http\Request;
use App\Http\Traits\ImageTrait;
use App\Http\Controllers\Controller;
use App\Http\Traits\UserLogTrait;

class InformationController extends Controller
{
    use ImageTrait,UserLogTrait;

    public function __construct()
    {
        $this->middleware('auth:web');
    }

    public function AjaxImageUpload(Request $request)
    {
        $formImage = "featured_img";
        $files = $request->file('featured_img');
        // dd($files);
        $this->imageUpload($request, $files, 'feature', 'informations', $formImage);
    }

    public function index(Request $request)
    {
        if ($request->session()->has('ajaximage')) {
            $image = $request->session()->get('ajaximage');
            @unlink('uploads/' . 'informations/' . $image);
        }
        $informations = Information::orderBy('id', 'desc')->where('delete_status', '0')->paginate(10);
        return view('admin.list.information', compact('informations'));
    }

    public function fetch(Request $request)
    {
        $informationName = $request->informationName;

        $informations = Information::when($informationName,function($query,$informationName){
            return $query->where("information_title","LIKE","%$informationName%");
        })->orderBy('id', 'desc')->paginate(10);

        return view('admin.list.ajaxlist.information', compact('informations'));
    }

    public function create()
    {
        $informations = Information::where('delete_status', '0')->where('parent_id', 0)->get();
        // dd($informations);
        return view('admin.form.information', compact('informations'));
    }

    public function store(Request $request)
    {
        //validate the form
        $this->validate(request(), [
            'information_title' => 'required',
            'information_type' => 'required',
            'featured_img' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        //create and save category
        $information = new Information();

        $information->information_title = request('information_title');
        $information->nepali_title = request('nepali_title');
        $information->information_type = request('information_type');
        $information->information_body = request('information_body');
        $information->information_url = str_slug($request->information_title);
        $information->external_link = request('external_link');
        $information->information_icon = request('information_icon');
        $information->parent_id = request('parent_id');
        $information->position = request('position');
        $information->meta_title = request('meta_title');
        $information->meta_keyword = request('meta_keyword');
        $information->meta_description = request('meta_description');
        $information->publish_status = request('publish_status');
        $information->show_on_menu = request('show_on_menu');
        $information->featured_img = $request->session()->get('ajaximage');

        $information->save();

        $request->session()->forget('ajaximage');

        $this->storeUserLog('Information',$information->id ,$information->information_title,'create');

        //redirect to dashboard
        return redirect('/ps-admin/informations')->with('success', 'Information created successfully.');
    }

    public function edit($id)
    {
        $informations = Information::where('delete_status', '0')->where('parent_id', 0)->get();
        $information = Information::where('id', $id)->first();
        // dd($information->products);
        return view('admin.form.information', compact('information', 'informations'));
    }

    public function update(Request $request, $id)
    {
        $information = Information::where('id', $id)->first();
        //validate the form
        $this->validate(request(), [
            'information_title' => 'required',
            'information_type' => 'required',
            'featured_img' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $simage = request()->file('featured_img');

        if ($simage != null) {
            $image = $information->featured_img;
            @unlink('uploads/' . 'informations/' . $image);

            $data1 = ([
                'featured_img' => $request->session()->get('ajaximage'),
            ]);
            Information::where('id', $id)->update($data1);
        }

        $data = ([
            'information_title' => request('information_title'),
            'nepali_title' => request('nepali_title'),
            'information_type' => request('information_type'),
            'information_body' => request('information_body'),
            'information_url' => str_slug($request->information_title),
            'parent_id' => request('parent_id'),
            'position' => request('position'),
            'external_link' => request('external_link'),
            'information_icon' => request('information_icon'),
            'meta_title' => request('meta_title'),
            'meta_keyword' => request('meta_keyword'),
            'meta_description' => request('meta_description'),
            'publish_status' => request('publish_status'),
            'show_on_menu' => request('show_on_menu'),
        ]);

        Information::where('id', $id)->update($data);
        
        $request->session()->forget('ajaximage');
        //redirect to dashboard

        $this->storeUserLog('Information',$information->id,$information->information_title,'update');

        return redirect('/ps-admin/informations')->with('success', 'Information updated successfully.');
    }

    public function destroy($id)
    {
        $information = Information::where('id', $id)->first();

        if (isset($information)) {
            $data = ([
                'delete_status' => '1',
            ]);

            //deleting admin
            Information::where('id', $id)->update($data);
            return redirect('/ps-admin/informations')->with('success', 'Information deleted successfully.');
        }

        $this->storeUserLog('Information',$information->id,$information->information_title,'delete');

        return redirect('/ps-admin/informations')->with('error', 'Information not found.');
    }

    public function removeFeature($information)
    {
        $photo = Information::where('featured_img', $information)->first();

        if (isset($photo)) {
            $image = $photo->featured_img;
            @unlink('uploads/informations/' . $image);

            $data2 = (['featured_img' => null]);
            Information::where('featured_img', $information)->update($data2);
            return back()->with('success', 'Photo deletion Success.');
        }
        return back()->with('error', 'Photo deletion failed.');
    }
}
