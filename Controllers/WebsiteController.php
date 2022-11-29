<?php

namespace App\Http\Controllers;

use DB;
use Mail;
use App\Mail\UserCommentEmail;
use Illuminate\Http\Request;
use App\Advertisement;
use App\Category;
use App\Comment;
use App\Content;
use App\Information;
use App\Reaction;
use App\Reporter;
use App\Setting;
use App\Tag;
use App\Team;
use Carbon\Carbon;
use Illuminate\Support\Facades\Session;

class WebsiteController extends Controller
{
    public function index()
    {
        //Publish Time Check
        // $date = now();
        // $day = Carbon::parse($date)->format('l');
        // if($day == "Saturday"){ // Trending count reset and set status to 1 i.e updated on saturday
        //     $content = Content::status()->first();
        //     if($content->trending_status == '0') // Check if trending status has been updated on saturday or not
        //     {
        //         DB::table('tbl_contents')->update(['trending_status' => '1']); //O 1 means trending status updated(1) on saturday or not(0)
        //         DB::table('tbl_contents')->update(['trending_count' => '1']);
        //     }
        // }

        // if($day == "Sunday"){ // Trending status reset so that next saturday count can be reset
        //     $content = Content::status()->first();
        //     if($content->trending_status == '1') // Check if trending status has been reset on sunday or not
        //     {
        //         DB::table('tbl_contents')->update(['trending_status' => '0']);
        //     }
        // }

        // $unpublished_news = Content::where('publish_time', '<', now())->where('publish_status', '0')->get();
        // foreach ($unpublished_news as $news) {
        //     $news->publish_status = '1';
        //     $news->save();
        // }

        return view('website.index');
    }

    public function page($page_url)
    {
        $page = Information::status()->where('information_url', $page_url)->first();
        // $sideNews = Content::status()->latest()->limit(3)->get();
        $sideNews = '';
        switch ($page->information_type) {
            case 'about':
                return view('website.about', compact('page', 'sideNews'));
            case 'contact':
                return view('website.contact', compact('page', 'sideNews'));
            case 'team':
                $teams = Team::status()->get();
                return view('website.team', compact('page', 'teams', 'sideNews'));
            case 'privacy':
                return view('website.privacy', compact('page', 'sideNews'));
            case 'advertisement':
                return view('website.ad', compact('page', 'sideNews'));
            case 'page':
                return view('website.converter', compact('page', 'sideNews'));
            default:
                return view('errors.website404');
        }
    }

    public function news($slug)
    {
        $news = Content::status()->where('slug', $slug)->first();
        if (!$news) return view('errors.website404');

        $text = $news->content;

        if (strlen($text) > 200) {
            $half = strpos($text, " ",  strlen($text) / 2);
            $secondHalf = substr($text, $half);
            $firstHalf = substr($text, 0, $half);
        }

        $comments = $news->comments;

        $relatedNews = $news->categories->map(function ($item) use ($news) {
            return $item->contents->where('id', '!=', $news->id);
        })->flatten()->shuffle()->unique('id')->take(4);

        // dd($relatedNews);

        $sideNews = Content::where('slug', '!=', $slug)->latest()->limit(6)->get();
        $trendingNews = Content::status()->orderBy('view_count', 'desc')->limit(7)->get();
        $tags = $news->tags;
        $reaction = $news->reaction;
        // dd($reaction);
        $reporter = $news->reporters->first();

        $news->increment('view_count');
        $news->increment('trending_count');

        return view('website.news', compact('news','text','firstHalf', 'secondHalf', 'reporter', 'comments', 'relatedNews', 'sideNews', 'trendingNews', 'tags', 'reaction'));
    }

    public function category($slug)
    {
        $category = Category::where('category_url', $slug)->first();
        if (!$category) return view('errors.website404');
        // $sideNews = Content::where('category_content.category_id', '!=', $category->id)->latest()->limit(5)->get();
        $categoryContents = Content::select('tbl_contents.*')
                        ->join('category_content','category_content.content_id','tbl_contents.id')
                        ->where('category_content.category_id',$category->id)
                        ->where('tbl_contents.publish_status','1')
                        ->where('tbl_contents.delete_status','0')
                        ->orderBy('tbl_contents.publish_time','DESC')
                        ->paginate(29);

        $bool = false;
        $subCategories = '';
        if (count($category->child)) {
            $bool = true;
            $subCategories = Category::where('category_id', $category->id)->get();
            foreach($subCategories as $row){
                $row->category_news_items = Content::select('tbl_contents.*')
                        ->join('category_content','category_content.content_id','tbl_contents.id')
                        ->where('category_content.category_id',$row->id)
                        ->where('tbl_contents.publish_status','1')
                        ->where('tbl_contents.delete_status','0')
                        ->limit(10)
                        ->orderBy('tbl_contents.publish_time','DESC')
                        ->get();
            }
        }

        $template = $bool ? 'website.category' : 'website.category';

        if($slug == 'online-talk'){
            $categoryContents = Content::status()->where('is_video','1')->orderBy('id','desc')->limit(20)->get();
            return view('website.videocategory', compact('category', 'categoryContents','subCategories'));
        }
        else
            return view($template, compact('category', 'categoryContents','subCategories'));
    }

    public function tag($tag_url)
    {
        $tag = Tag::where('tag_url', $tag_url)->first();
        $contents = $tag->contents()->status()->paginate(20);
        // dd($tag,$contents);
        // $sideNews = Content::status()->whereNotIn('slug', $tag->contents->pluck('slug'))->limit(5)->get();
        // $trendingNews = Content::status()->whereNotIn('slug', $contents->pluck('slug'))->orderBy('view_count', 'desc')->limit(4)->get();
        // $tags = Tag::status()->has('contents')->limit(10)->get();

        return view('website.tag', compact('tag', 'contents'));
    }

    public function reporter($reporter_url)
    {
        $reporter = Reporter::where('slug', $reporter_url)->first();
        $contents = $reporter->contents()->status()->paginate(20);
        // $sideNews = Content::status()->whereNotIn('slug', $reporter->contents->pluck('slug'))->limit(5)->get();
        // $trendingNews = Content::status()->orderBy('view_count', 'desc')->limit(4)->get();
        // $tags = Tag::status()->has('contents')->limit(10)->get();

        return view('website.reporter', compact('reporter', 'contents'));
    }

    public function staticCategory(Request $request)
    {
        if (request()->is('latest')) {
            $contents = Content::status()->where('is_special', '1')->paginate(10);
            $title = 'ताजा समाचार';
        } else if (request()->is('featured')) {
            $contents = Content::status()->where('is_special', '1')->paginate(10);
            $title = 'फिचर्द';
        } else if (request()->is('trending')) {
            $contents = Content::status()->orderBy('trending_count', 'desc')->paginate(10);
            $title = 'ट्रेण्डिङ';
        } else if (request()->is('video')) {
            $contents = Content::status()->where('is_video','1')->orderBy('view_count', 'desc')->paginate(10);
            $title = 'भिडियो';
        } else {
            $news = Content::where('id', $request->id)->first();
            $contents = $news->categories->map(function ($item) use ($news) {
                return $item->contents->where('id', '!=', $news->id);
            })->flatten()->shuffle()->unique('id')->paginate(10);
            $title = 'सम्बन्धित समाचार';
        }

        $sideNews = Content::status()->whereNotIn('slug', $contents->pluck('slug'))->limit(5)->get();
        $trendingNews = Content::status()->whereNotIn('slug', $contents->pluck('slug'))->orderBy('trending_count', 'desc')->limit(4)->get();
        $tags = Tag::status()->has('contents')->limit(10)->get();

        return view('website.static-category', compact('title', 'contents', 'sideNews', 'trendingNews', 'tags'));
    }

    public function search(Request $request)
    {
        $keyword = $request->keyword;
        $startDate = $request->start_date;
        $endDate = $request->end_date;

        $contents = Content::status()
            ->when($startDate, function ($query) use ($request) {
                return $query->whereBetween('publish_time', [$request->start_date, $request->end_date]);
            })
            ->where("title", "LIKE", "%$keyword%")
            ->paginate(20);
            // dd($keyword);
        // $sideNews = Content::status()->limit(5)->get();
        // $tags = Tag::status()->has('contents')->limit(10)->get();

        return view('website.search', compact('keyword', 'contents'));
    }

    public function comment(Request $request)
    {
        $this->validate($request, [
            'name' => 'required',
            'email' => 'required|email',
            'comment' => 'required'
        ]);

        $secret = env('RECAPTCHA_SECRET_KEY');
        $captcha = $request->input('g-recaptcha-response');
        $response = json_decode(file_get_contents("https://www.google.com/recaptcha/api/siteverify?secret=$secret&response=" . $captcha . "&remoteip=" . $_SERVER['REMOTE_ADDR']), true);

        if ($response['success'] == false) {

            $comment = new Comment();
            $comment->content_id = $request->content_id;
            $comment->name = $request->name;
            $comment->email = $request->email;
            $comment->comment = $request->comment;

            $comment->save();

            return back()->with('error', 'You are spammer !');
        } else {
            $comment = new Comment();
            $comment->content_id = $request->content_id;
            $comment->name = $request->name;
            $comment->email = $request->email;
            $comment->comment = $request->comment;

            $comment->save();

            $data = array(
                'name' => $request->name,
                'email' => $request->email,
                'comment' => $request->comment,
                'content_title' => $comment->contentTitle($request->content_id)
            );

            $customerAddress = $request->email;

            Mail::to($customerAddress)->send(new UserCommentEmail($data));

            return back()->with('success', 'तपाईंको प्रतिक्रियाको लागि धन्यवाद !!!');
        }
    }

    public function react(Request $request)
    {
        $reaction = Reaction::where('content_id', $request->content_id)->first();
        // dd($reaction);
        if (session()->get('id') != $request->content_id) {

            $reaction->increment($request->type);
            session()->put('id', $request->content_id);
        }

        // $arr = [];
        // if(session()->has('id'))
        // {
        //     $session = session()->get('id');
        //     dd($session);
        //     foreach($session as $item ){
        //         dump($item);
        //         array_push($arr,$item);
        //     }
        //     session()->forget('id');
        // }
        // array_push($arr,$request->content_id);

        // dd(session()->get('id'));
        // session()->forget('id');


        return view('website.ajax.reaction', compact('reaction'));
    }

    public function countAdvertisement($id)
    {
        $advertisement = Advertisement::find($id);
        $advertisement->increment('view_count');

        return redirect($advertisement->link);
    }
}
