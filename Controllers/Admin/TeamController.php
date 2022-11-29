<?php

namespace App\Http\Controllers\Admin;

use Image;
use App\Team;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\ImageTrait;
use App\Http\Traits\UserLogTrait;
use App\Http\Controllers\Controller;

class TeamController extends Controller
{
    use UserLogTrait,ImageTrait;

    public function __construct()
    {
        $this->middleware('auth:web');
    }

    public function AjaxImageUpload(Request $request)
    {
        $formImage = "image";
        $files = $request->file('image');
        $this->imageUpload($request, $files, 'team', 'teams', $formImage);
    }

    public function index(Request $request)
    {
        if ($request->session()->has('ajaximage')) {
            $image = $request->session()->get('ajaximage');
            @unlink('uploads/teams/' . $image);
        }

        $teams = Team::where('delete_status','0')->orderBy('id', 'desc')->paginate(10);
        return view('admin.list.team',compact('teams'));
    }

    public function fetch(Request $request)
    {
        $teamName = $request->teamName;

        $teams = Team::when($teamName,function($query,$teamName){
            return $query->where("title","LIKE","%$teamName%");
        })->orderBy('id', 'desc')->paginate(10);

        return view('admin.list.ajaxlist.team', compact('teams'));
    }

    public function create()
    {
        return view('admin.form.team');
    }

    public function store(Request $request,Team $team)
    {
        //validate the form
        $this->validate(request(), [

            'title' => 'required',
            'designation' => 'required',
            'description' => 'required',
            // 'slug' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'address' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email' => 'required|email|unique:tbl_teams,email,'.$team->id,

        ]);
        //dd('wait');
        //create and save category
        $team = new Team();

        $team->title = request('title');
        $team->nepali_title = request('nepali_title');
        $team->designation = request('designation');
        $team->description = request('description');
        $team->slug = Str::slug(request('title'), '-').rand(0,999);
        $team->slug_url = request('slug_url');
        // $team->image = request('image');
        $team->phone = request('phone');
        $team->email = request('email');
        $team->address = request('address');
        $team->facebook = request('facebook');
        $team->twitter = request('twitter');
        $team->publish_status = request('publish_status');
        $team->image = request()->session()->get('ajaximage');

        // $file = request()->file('image');

        // if($file != null) {

        //     $image_name = "teams-".time().".".$file->clientExtension();

        //     // open an image file
        //     $img = Image::make($file);

        //     // save image in desired format
        //     $img->save('uploads/'.'teams/'.$image_name);

        //     $team->image = $image_name;
        // }

        $team->save();

        $request->session()->forget('ajaximage');

        $this->storeUserLog('Team',$team->id,$team->title,'create');

        return redirect('/ps-admin/teams')->with('success','Team created successfully.');
    }

    public function view($id)
    {  
        $team = Team::find($id);
        return view('admin.pages.team',compact('team'));
    }

    public function edit($id)
    {  
        $team = Team::find($id);
        return view('admin.form.team',compact('team'));
    }

    public function update(Request $request,$id)
    {
        $team = Team::find($id);

        $this->validate(request(), [

            'title' => 'required',
            'designation' => 'required',
            'description' => 'required',
            // 'slug' => 'required',
            'phone' => 'required',
            'email' => 'required',
            'address' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            'email' => 'required|email|unique:tbl_teams,email,'.$id,

        ]);

        $data = ([
            'title' => request('title'),
            'nepali_title' => request('nepali_title'),
            'designation' => request('designation'),
            'description' => request('description'),
            'slug' => Str::slug(request('title'), '-').rand(0,999),
            'slug_url' => request('slug_url'),
            // 'image' => request('image'),
            'phone' => request('phone'),
            'email' => request('email'),
            'address' => request('address'),
            'facebook' => request('facebook'),
            'twitter' => request('twitter'),
            'publish_status' => request('publish_status')
        ]);

        $file = request()->file('image');

        if($file != null) {

            //deleting previous image
            $image = $team->image;
            @unlink('uploads/'.'teams/'.$image);

            $image_name = "teams-".time().".".$file->clientExtension();

            // open an image file
            $img = Image::make($file);

            $img->save('uploads/'.'teams/'.$image_name);

            $data1 = (['image' => $image_name]);
            Team::where('id', $id)->update($data1);
        }

        Team::where('id', $id)->update($data);

        $request->session()->forget('ajaximage');

        $this->storeUserLog('Team',$team->id,$team->title,'update');

        return redirect('/ps-admin/teams')->with('success','Team updated successfully.');
    }

    public function destroy($id)
    {
        $team = Team::where('id', $id)->first();

        if(isset($team))
        {
            // $image = $admin->image;
            // @unlink('uploads/'.'admins/'.$image);
            $data = ([
                'delete_status' => '1',
            ]);
            //deleting admin
            Team::where('id', $id)->update($data);

            $this->storeUserLog('Team',$team->id,$team->title,'delete');
            return redirect('/ps-admin/teams')->with('success','Team deleted successfully.');
        }

        return redirect('/ps-admin/teams')->with('error','Team deletion failed.');
    }
}
