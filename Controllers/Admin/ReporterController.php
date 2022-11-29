<?php

namespace App\Http\Controllers\Admin;

use Image;
use App\Reporter;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ImageTrait;
use App\Http\Traits\UserLogTrait;
use App\Http\Controllers\Controller;

class ReporterController extends Controller
{
    use UserLogTrait;
    use ImageTrait;

    public function index()
    {
        if (request()->session()->has('ajaximage')) {
            $image = request()->session()->get('ajaximage');
            @unlink('uploads/reporters/' . $image);
        }
        $reporters = Reporter::where('delete_status','0')->orderBy('id', 'desc')->paginate(10);
        return view('admin.list.reporter',compact('reporters'));
    }

    public function fetch(Request $request)
    {
        $reporterName = $request->reporterName;

        $reporters = Reporter::when($reporterName,function($query,$reporterName){
            return $query->where("title","LIKE","%$reporterName%");
        })->orderBy('id', 'desc')->paginate(10);

        return view('admin.list.ajaxlist.reporter', compact('reporters'));
    }

    public function AjaxImageUpload(Request $request)
    {
        $formImage = "image";
        $files = $request->file('image');
        $this->imageUpload($request, $files, 'reporter', 'reporters', $formImage);
    }

    public function create()
    {
        return view('admin.form.reporter');
    }

    public function store(Request $request,Reporter $reporter)
    {
        //validate the form
        $this->validate(request(), [
            'title' => 'required',
            'designation' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email' => 'required|email|unique:tbl_reporters,email,'.$reporter->id,
        ]);
        //dd('wait');
        //create and save category
        $reporter = new Reporter();

        $reporter->title = request('title');
        $reporter->nepali_title = request('nepali_title');
        $reporter->designation = request('designation');
        $reporter->description = request('description');
        $reporter->slug = Str::slug(request('title'), '-').rand(0,999);
        // $reporter->slug_url = request('slug_url');
        // $reporter->image = request('image');
        $reporter->phone = request('phone');
        $reporter->email = request('email');
        $reporter->address = request('address');
        $reporter->facebook = request('facebook');
        $reporter->twitter = request('twitter');
        $reporter->publish_status = request('publish_status');

        // dd
        // if($request->featured_status)
        // {

        // }

        // $file = request()->file('image');

        // if($file != null) {

        //     $image_name = "reporters-".time().".".$file->clientExtension();

        //     // open an image file
        //     $img = Image::make($file);

        //     // save image in desired format
        //     $img->save('uploads/'.'reporters/'.$image_name);

        //     $reporter->image = $image_name;
        // }

        $reporter->image = $request->session()->get('ajaximage');

        $reporter->save();

        if($request->featured_status == 1)
        {
            $curReporter = Reporter::where('featured_status','1')->first();
            if(isset($curReporter))
            {
                $data = ([
                    'featured_status' => '0',
                ]);
                Reporter::where('id', $curReporter->id)->update($data);
            }

            $data1 = ([
                'featured_status' => '1',
            ]);
            Reporter::where('id', $reporter->id)->update($data1);
        }
        
        $request->session()->forget('ajaximage');

        $this->storeUserLog('Reporter',$reporter->id,$reporter->title,'create');

        if($request->ajax)
        return response()->json($reporter);
        else
        return redirect('/ps-admin/reporters')->with('success','Reporter created successfully.');
    }

    public function view($id)
    {
        $reporter = Reporter::find($id);
        return view('admin.pages.reporter',compact('reporter'));
    }

    public function edit($id)
    {  
        $reporter = Reporter::find($id);
        return view('admin.form.reporter',compact('reporter'));
    }

    public function update($id, Request $request)
    {
        $reporter = Reporter::find($id);

        $this->validate(request(), [
            'title' => 'required',
            'designation' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email' => 'required|email|unique:tbl_reporters,email,'.$id,
        ]);

        $file = request()->file('image');
        if ($file != null) {
            $image = $reporter->image;
            @unlink('uploads/' . 'reporters/' . $image);
            $data1 = ([
                'image' => $request->session()->get('ajaximage'),
            ]);
            Reporter::where('id', $id)->update($data1);
        }

        $data = ([
            'title' => request('title'),
            'nepali_title' => request('nepali_title'),
            'designation' => request('designation'),
            'description' => request('description'),
            'slug' => Str::slug(request('title'), '-').rand(0,999),
            // 'slug_url' => request('slug_url'),
            // 'image' => request('image'),
            'phone' => request('phone'),
            'email' => request('email'),
            'address' => request('address'),
            'facebook' => request('facebook'),
            'twitter' => request('twitter'),
            'publish_status' => request('publish_status')
        ]);

        // $file = request()->file('image');

        // if($file != null) {

        //     //deleting previous image
        //     $image = $reporter->image;
        //     @unlink('uploads/'.'reporters/'.$image);

        //     $image_name = "reporters-".time().".".$file->clientExtension();

        //     // open an image file
        //     $img = Image::make($file);

        //     $img->save('uploads/'.'reporters/'.$image_name);

        //     $data1 = (['image' => $image_name]);
        //     Reporter::where('id', $id)->update($data1);
        // }

        Reporter::where('id', $id)->update($data);

        if($request->featured_status == 1)
        {
            $curReporter = Reporter::where('featured_status','1')->first();
            if(isset($curReporter))
            {
                $data = ([
                    'featured_status' => '0',
                ]);
                Reporter::where('id', $curReporter->id)->update($data);
            }

            $data1 = ([
                'featured_status' => '1',
            ]);
            Reporter::where('id', $reporter->id)->update($data1);
        }

        $this->storeUserLog('Reporter',$reporter->id,$reporter->title,'update');

        $request->session()->forget('ajaximage');

        return redirect('/ps-admin/reporters')->with('success','Reporter updated successfully.');
    }

    public function destroy($id)
    {
        $reporter = Reporter::where('id', $id)->first();

        if(isset($reporter))
        {
            // $image = $admin->image;
            // @unlink('uploads/'.'admins/'.$image);
            $data = ([
                'delete_status' => '1',
            ]);
            //deleting admin
            Reporter::where('id', $id)->update($data);

            $this->storeUserLog('Reporter',$reporter->id,$reporter->title,'delete');
            return redirect('/ps-admin/reporters')->with('success','Reporter deleted successfully.');
        }

        return redirect('/ps-admin/reporters')->with('error','Reporter deletion failed.');
    }
}
