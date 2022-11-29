<?php

namespace App\Http\Controllers\Admin;

use App\Tag;
use Illuminate\Support\Str;
use Illuminate\Http\Request;
use App\Http\Traits\UserLogTrait;
use App\Http\Controllers\Controller;

class TagController extends Controller
{
    use UserLogTrait;

    public function __construct()
    {
        $this->middleware('auth:web');
    }

    public function index(Request $request)
    {
        $tags = Tag::orderBy('id','desc')->where('delete_status','0')->paginate(10);
        return view('admin.list.tag',compact('tags'));
    }

    public function fetch(Request $request)
    {
        $tagName = $request->tagName;

        $tags = Tag::when($tagName,function($query,$tagName){
            return $query->where("tag_title","LIKE","%$tagName%");
        })->orderBy('id', 'desc')->paginate(10);

        return view('admin.list.ajaxlist.tag', compact('tags'));
    }

    public function create()
    {
        return view('admin.form.tag');
    }

    public function store(Request $request)
    {
        //validate the form
        $this->validate(request(), [
            'tag_title' => ['required', 'unique:tbl_tags', 'max:191'],
        ]);

        $tag = new Tag();

        $tag->tag_title = request('tag_title');
        $tag->tag_url = Str::slug(request('tag_title'), '-');
        $tag->nepali_title = request('nepali_title');
        $tag->tag_body = request('tag_body');
        $tag->publish_status = request('publish_status');
        $tag->featured_status = request('featured_status') ?? '0';

        $tag->save();

        $this->storeUserLog('Tag',$tag->id,$tag->tag_title,'create');

        if($request->ajax)
        return response()->json($tag);
        else
        return redirect('/ps-admin/tags')->with('success','Tag created successfully.');
    }

    public function edit($id)
    {
        $tag = Tag::where('id', $id)->first();
        return view('admin.form.tag',compact('tag'));
    }

    public function update(Request $request, $id)
    {

        $tag = Tag::where('id', $id)->first();

        $this->validate(request(), [
            'tag_title' => 'required|unique:tbl_tags,tag_title,'.$tag->id,
        ]);
        
        $data = ([
            'tag_title' => request('tag_title'),
            'tag_url' => Str::slug(request('tag_title'), '-'),
            'nepali_title' => request('nepali_title'),
            'tag_body' => request('tag_body'),
            'publish_status' => request('publish_status'),
            'featured_status' => request('featured_status')
        ]);

        Tag::where('id', $id)->update($data);

        $this->storeUserLog('Tag',$tag->id,$tag->tag_title,'update');

        return redirect('/ps-admin/tags')->with('success','Tag updated successfully.');
    }

    public function destroy($id)
    {
        $tag = Tag::where('id', $id)->first();

        if(isset($tag))
        {
            $data = ([
                'delete_status' => '1',
            ]);

            Tag::where('id', $id)->update($data);
            
            $this->storeUserLog('Tag',$tag->id,$tag->tag_title,'delete');

            return redirect('/ps-admin/tags')->with('success','Tag deleted successfully.');
        }
        return redirect('/ps-admin/tags')->with('error','Tag deletion failed.');
    }

    public function ajaxStore()
    {    
        $tag = new Tag();
        $tag->title = request('title');
        $tag->slug = \Str::slug(request('slug'));
        $tag->publish_status = request('publish_status');
        $tag->featured_status = request('featured_status');

        $tag->save();

    }
}
