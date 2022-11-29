<?php

namespace App\Http\Controllers\Admin;

use Image;
use App\Tag;
use App\User;
use App\Guest;
use App\Photo;
use App\Content;
use App\Category;
use App\Reporter;
use Illuminate\Http\Request;
use App\Http\Traits\ImageTrait;
use App\Http\Traits\UserLogTrait;
use App\Http\Controllers\Controller;
use App\Reaction;
use Illuminate\Support\Facades\View;
use Illuminate\Support\Facades\Validator;

class ContentController extends Controller
{
    use ImageTrait, UserLogTrait;

    public function __construct()
    {
        $this->middleware('auth:web');
    }

    public function AjaxImageUpload(Request $request)
    {
        $formImage = "parallex_img";
        $files = $request->file('parallex_img');
        $this->imageUpload($request, $files, 'content', 'contents', $formImage);
    }

    public function index(Request $request)
    {
        $type_id = $request->id;
        $type_name = $request->type;

        if ($request->session()->has('ajaximage')) {
            $image = $request->session()->get('ajaximage');
            @unlink('uploads/contents/' . $image);
        }

        $contents = Content::where('delete_status', '0')

            ->when($type_name == "Tag", function () use ($type_id) {
                return Tag::where('id', $type_id)->first()->contents->sortByDesc('id');
            })
            ->when($type_name == "Reporter", function () use ($type_id) {
                return Reporter::where('id', $type_id)->first()->contents->sortByDesc('id');
            })
            ->when($type_name == "Guest", function () use ($type_id) {
                return Guest::where('id', $type_id)->first()->contents->sortByDesc('id');
            })
            ->when($type_name == "User", function () use ($type_id) {
                return User::admins()->where('id', $type_id)->first()->contentCreates->sortByDesc('id');
            })
            ->when($type_name == "Category", function () use ($type_id) {
                return Category::where('id', $type_id)->first()->contents->sortByDesc('id');
            })
            ->when($type_name == null, function ($query) {
                return $query->orderBy('id', 'desc');
            })->paginate(10);


        return view('admin.list.content', compact('contents', 'type_name', 'type_id'));
    }

    public function fetch(Request $request)
    {
        $contentName = $request->contentName;
        $type_id = $request->id;
        $type_name = $request->type;

        // dd($type_id, $type_name);

        // $contents = Content::when($contentName, function ($query, $contentName) {
        //     return $query->where("title", "LIKE", "%$contentName%");
        // })->orderBy('id', 'desc')->paginate(10);

        $contents = Content::where('delete_status', '0')

            ->when($type_name == "Tag", function () use ($request) {
                return Tag::where('id', $request->id)->first()->contents()->where("title", "LIKE", "%$request->contentName%")->orderBy('id', 'desc');
            })
            ->when($type_name == "Reporter", function () use ($request) {
                return Reporter::where('id', $request->id)->first()->contents()->where("title", "LIKE", "%$request->contentName%")->orderBy('id', 'desc');
            })
            ->when($type_name == "Guest", function () use ($request) {
                return Guest::where('id', $request->id)->first()->contents()->where("title", "LIKE", "%$request->contentName%")->orderBy('id', 'desc');
            })
            ->when($type_name == "User", function () use ($request) {
                return User::admins()->where('id', $request->id)->first()->contentCreates()->where("tbl_contents.title", "LIKE", "%$request->contentName%")->orderBy('id', 'desc');
            })
            ->when($type_name == "Category", function () use ($request) {
                return Category::where('id', $request->id)->first()->contents()->where("title", "LIKE", "%$request->contentName%")->orderBy('id', 'desc');
            })
            ->when($type_name == null, function ($query) use ($contentName) {
                return $query->where("title", "LIKE", "%$contentName%")->orderBy('id', 'desc');
            })->paginate(10);

        // $response = [
        //     (string)View::make('admin.list.ajaxlist.content', compact('contents', 'type_name', 'type_id'))
        // ];
        // return response()->json($response);

        return view('admin.list.ajaxlist.content', compact('contents', 'type_name', 'type_id'));
    }

    public function create()
    {
        $reporters = Reporter::where('delete_status', '0')->where('publish_status', '1')->get();
        $guests = Guest::where('delete_status', '0')->where('publish_status', '1')->get();
        $tags = Tag::where('delete_status', '0')->where('publish_status', '1')->get();
        $categories = Category::where('delete_status', '0')->where('publish_status', '1')->get();
        return view('admin.form.content', compact('categories', 'reporters', 'guests', 'tags'));
    }

    public function store(Request $request, Guest $guest)
    {
        //validate the form
        $this->validate(request(), [

            'title' => 'required',
            'content' => 'required',
            // 'designation' => 'required',

        ]);
        //dd('wait');
        //create and save category
        $content = new Content();

        $content->title = request('title');
        $content->slug = request('slug') ?? rand(00000000, 99999999);
        $content->short_content = request('short_content');
        // $content->tag_line = request('tag_line');
        $content->is_fixed = request('is_fixed');
        $content->is_fixed = request('is_fixed');
        $content->is_video = request('is_video');
        $content->video = request('video');
        $content->date_line = request('date_line');
        $content->content = request('content');
        $content->external_url = request('external_url');
        $content->publish_time = date('Y-m-d H:i:s', strtotime(request('publish_time')));
        $content->show_date = request('publish_time');

        $content->is_photo = '0';
        $content->is_video = request('is_video');
        $content->parallex_img = request('parallex_img');
        $content->image_caption = request('image_caption');
        $content->is_special = request('is_special');
        $content->is_flash = request('is_flash');
        $content->second_heading = request('second_heading');
        $content->is_mob_notification = request('is_mob_notification');

        $content->meta_title = request('meta_title');
        $content->meta_keywords = request('meta_keywords');
        $content->meta_description = request('meta_description');

        $content->publish_status = request('publish_status');

        $content->save();

        $photos = request()->file('multi_image');
        if ($photos != null) {
            foreach ($photos as $key => $pics) {
                $v = Validator::make(['photo' => request()->file('multi_image')[$key]], [
                    'photo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ]);
                if ($v->fails()) {
                    return back()->withErrors($v)->withInput();
                }

                $photo = new Photo();
                $image_name = "content(" . $key . ")-" . time() . "." . $pics->clientExtension();
                // open an image file
                $img = Image::make($pics);
                // save image in desired format
                $img->save('uploads/' . 'photos/' . $image_name);
                $photo->image = $image_name;
                $photo->imageable_id = $content->id;
                $photo->imageable_type = 'App\Content';
                $photo->save();
            }
        }

        $request->session()->forget('ajaximage');

        $cat_ids = request('category_id');
        $content->categories()->attach($cat_ids);

        $reporter_ids = request('reporter_id');
        $content->reporters()->attach($reporter_ids);

        $guest_ids = request('guest_id');
        $content->guests()->attach($guest_ids);

        $tag_ids = request('tag_id');
        $content->tags()->attach($tag_ids);

        Reaction::create([
            'content_id' => $content->id,
            'happy' => 0,
            'sad' => 0,
            'wow' => 0,
            'haha' => 0,
            'angry' => 0
        ]);

        $this->storeUserLog('Content', $content->id, $content->title, 'create');

        return redirect('/ps-admin/contents')->with('success', 'Content created successfully.');
    }

    public function edit($id)
    {
        $content = Content::where('id', $id)->first();
        // dump($content);
        // dd($content->userUpdates);
        $photos = $content->photos;
        // dd($photos);
        $reporters = Reporter::where('delete_status', '0')->where('publish_status', '1')->get();
        $guests = Guest::where('delete_status', '0')->where('publish_status', '1')->get();
        $tags = Tag::where('delete_status', '0')->where('publish_status', '1')->get();
        $categories = Category::where('delete_status', '0')->where('publish_status', '1')->get();
        
        return view('admin.form.content', compact('content', 'photos', 'categories', 'reporters', 'guests', 'tags'));
    }

    public function view($id)
    {
        $content = Content::where('id', $id)->first();
        // dd($content);
        // dd($content->userUpdates);
        $photos = $content->photos;
        // dd($photos);
        $reporters = Reporter::where('delete_status', '0')->where('publish_status', '1')->get();
        $guests = Guest::where('delete_status', '0')->where('publish_status', '1')->get();
        $tags = Tag::where('delete_status', '0')->where('publish_status', '1')->get();
        $categories = Category::where('delete_status', '0')->where('publish_status', '1')->get();

        return view('admin.pages.content', compact('content', 'photos', 'categories', 'reporters', 'guests', 'tags'));
    }

    public function update(Request $request, $id)
    {
        $content = Content::where('id', $id)->first();

        $this->validate(request(), [
            'title' => 'required',
            'content' => 'required',
            // 'news_date' => 'required',
            // 'news_excerpt' => 'required',
            // 'news_body' => 'required',
            // 'featured_img' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
            // 'parallex_img' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $data = ([
            'title' => request('title'),
            'short_content' => request('short_content'),
            // 'tag_line' => request('tag_line'),
            'is_fixed' => request('is_fixed'),
            'is_front' => request('is_front'),
            'is_photo' => '0',
            'parallex_img' => request('parallex_img'),
            'is_video' => request('is_video'),
            'video' => request('video'),
            'date_line' => request('date_line'),
            'content' => request('content'),
            'external_url' => request('external_url'),
            'publish_time' => date('Y-m-d H:i:s', strtotime(request('publish_time'))),
            'show_date' => request('publish_time'),
            'image_caption' => request('image_caption'),
            'is_special' => request('is_special'),
            'is_flash' => request('is_flash'),
            'second_heading' => request('second_heading'),
            'is_mob_notification' => request('is_mob_notification'),
            'meta_title' => request('meta_title'),
            'meta_keywords' => request('meta_keywords'),
            'meta_description' => request('meta_description'),
            'publish_status' => request('publish_status'),
        ]);
        // dd($data);
        $file = request()->file('parallex_img');
        if ($file != null) {
            $image = $content->parallex_img;
            @unlink('uploads/' . 'contents/' . $image);
            $data1 = ([
                'parallex_img' => $request->session()->get('ajaximage'),
            ]);
            Content::where('id', $id)->update($data1);
        }

        Content::where('id', $id)->update($data);

        $photos = request()->file('multi_image');
        if ($photos != null) {
            foreach ($photos as $key => $pics) {
                $v = Validator::make(['photo' => request()->file('multi_image')[$key]], [
                    'photo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
                ]);
                if ($v->fails()) {
                    return back()->withErrors($v)->withInput();
                }

                $photo = new Photo();
                $image_name = "content(" . $key . ")-" . time() . "." . $pics->clientExtension();
                // open an image file
                $img = Image::make($pics);
                // save image in desired format
                $img->save('uploads/' . 'photos/' . $image_name);
                $photo->image = $image_name;
                $photo->imageable_id = $content->id;
                $photo->imageable_type = 'App\Content';
                $photo->save();
            }
        }

        $cat_ids = request('category_id');
        $content->categories()->sync($cat_ids);

        $reporter_ids = request('reporter_id');
        $content->reporters()->sync($reporter_ids);

        $guest_ids = request('guest_id');
        $content->guests()->sync($guest_ids);

        $tag_ids = request('tag_id');
        $content->tags()->sync($tag_ids);

        $request->session()->forget('ajaximage');

        $this->storeUserLog('Content', $content->id, $content->title, 'update');

        return redirect('/ps-admin/contents')->with('success', 'Content updated successfully.');
    }

    public function destroy($id)
    {
        $content = Content::where('id', $id)->firstOrFail();
        if ($content) {
            Content::where('id', $id)->update(['delete_status' => '1']);

            $this->storeUserLog('Content', $content->id, $content->title, 'delete');

            return redirect('/ps-admin/contents')->with('success', 'Content Deleted Successfully!');
        }
        return redirect('/ps-admin/contents')->with('error', 'Content Not Found');
    }

    public function deleteSingeImage(Request $request)
    {
        $id = $request->imageId;

        $photo = Photo::where('id', $id)->first();
        // dd($photo->image);

        if ($photo != null) {
            @unlink('uploads/' . 'photos/' . $photo->image);
            Photo::where('id', $id)->delete();
        }

        return response()->json([
            'status' => "success",
            'message' => "Image deleted"
        ]);
    }
}
