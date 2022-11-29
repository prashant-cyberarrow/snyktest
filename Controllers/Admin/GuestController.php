<?php

namespace App\Http\Controllers\Admin;

use Image;
use App\Guest;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ImageTrait;
use App\Http\Traits\UserLogTrait;
use App\Http\Controllers\Controller;

class GuestController extends Controller
{
    use UserLogTrait;
    use ImageTrait;

    public function index()
    {
        if (request()->session()->has('ajaximage')) {
            $image = request()->session()->get('ajaximage');
            @unlink('uploads/guests/' . $image);
        }
        $guests = Guest::where('delete_status','0')->orderBy('id', 'desc')->paginate(10);
        return view('admin.list.guest',compact('guests'));
    }

    public function fetch(Request $request)
    {
        $guestName = $request->guestName;

        $guests = Guest::when($guestName,function($query,$guestName){
            return $query->where("title","LIKE","%$guestName%");
        })->orderBy('id', 'desc')->paginate(10);

        return view('admin.list.ajaxlist.guest', compact('guests'));
    }

    public function AjaxImageUpload(Request $request)
    {
        $formImage = "image";
        $files = $request->file('image');
        $this->imageUpload($request, $files, 'guest', 'guests', $formImage);
    }

    public function create()
    {
        return view('admin.form.guest');
    }

    public function store(Request $request,Guest $guest)
    {
        //validate the form
        $this->validate(request(), [
            'title' => 'required',
            'designation' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email' => 'required|email|unique:tbl_guests,email,'.$guest->id,
        ]);

        //create and save category
        $guest = new Guest();

        $guest->title = request('title');
        $guest->nepali_title = request('nepali_title');
        $guest->designation = request('designation');
        $guest->description = request('description');
        $guest->slug = Str::slug(request('title'), '-').rand(0,999);
        // $guest->slug_url = request('slug_url');
        // $guest->image = request('image');
        $guest->phone = request('phone');
        $guest->email = request('email');
        $guest->address = request('address');
        $guest->facebook = request('facebook');
        $guest->twitter = request('twitter');
        $guest->publish_status = request('publish_status');
        $guest->image = request()->session()->get('ajaximage');

        // $file = request()->file('image');

        // if($file != null) {

        //     $image_name = "guests-".time().".".$file->clientExtension();

        //     // open an image file
        //     $img = Image::make($file);

        //     // save image in desired format
        //     $img->save('uploads/'.'guests/'.$image_name);

        //     $guest->image = $image_name;
        // }

        $guest->save();

        $this->storeUserLog('Guest',$guest->id,$guest->title,'create');

        $request->session()->forget('ajaximage');

        if($request->ajax)
        return response()->json($guest);
        else
        return redirect('/ps-admin/guests')->with('success','Guest created successfully.');
    }

    public function view($id)
    {
        $guest = Guest::find($id);
        return view('admin.pages.guest',compact('guest'));
    }

    public function edit($id)
    {  
        $guest = Guest::find($id);
        return view('admin.form.guest',compact('guest'));
    }

    public function update(Request $request,$id)
    {
        $guest = Guest::find($id);

        $this->validate(request(), [
            'title' => 'required',
            'designation' => 'required',
            'phone' => 'required',
            'address' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email' => 'required|email|unique:tbl_guests,email,'.$id,
        ]);

        $file = request()->file('image');
        if ($file != null) {
            $image = $guest->image;
            @unlink('uploads/' . 'guests/' . $image);
            $data1 = ([
                'image' => request()->session()->get('ajaximage'),
            ]);
            Guest::where('id', $id)->update($data1);
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
        //     $image = $guest->image;
        //     @unlink('uploads/'.'guests/'.$image);

        //     $image_name = "guests-".time().".".$file->clientExtension();

        //     // open an image file
        //     $img = Image::make($file);

        //     $img->save('uploads/'.'guests/'.$image_name);

        //     $data1 = (['image' => $image_name]);
        //     Guest::where('id', $id)->update($data1);
        // }

        Guest::where('id', $id)->update($data);

        $this->storeUserLog('Guest',$guest->id,$guest->title,'update');

        $request->session()->forget('ajaximage');

        return redirect('/ps-admin/guests')->with('success','Guest updated successfully.');
    }

    public function destroy($id)
    {
        $guest = Guest::where('id', $id)->first();

        if(isset($guest))
        {
            // $image = $admin->image;
            // @unlink('uploads/'.'admins/'.$image);
            $data = ([
                'delete_status' => '1',
            ]);
            //deleting admin
            Guest::where('id', $id)->update($data);

            $this->storeUserLog('Guest',$guest->id,$guest->title,'delete');
            return redirect('/ps-admin/guests')->with('success','Guest deleted successfully.');
        }

        return redirect('/ps-admin/guests')->with('error','Guest deletion failed.');
    }
}
