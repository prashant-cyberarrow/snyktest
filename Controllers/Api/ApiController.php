<?php

namespace App\Http\Controllers\Api;

use App\Content;
use App\Category;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class ApiController extends Controller
{
    protected function getCategory(){
        $cat_url = [
            'breaking-news',
            'main-news',
            'politics',
            'economy',
            'crime',
            'health',
            'thought',
            'industry',
            'miscellaneous',
            'education',
            'tourism',
            'lifestyle',
            'entertainment',
            'international',
            'good-governance',
            'share',
            'auto',
            'agriculture',
            'information-technology',
            'sport',
            'photo-feature',
            'country',
            'infrastructure',
            'province',
            'agriculture',
            'abroad',

            'province-1',
            'province-2',
            'bagamati',
            'gandaki',
            'lumbini',
            'karnali',
            'sudurpaschim'
        ];

        return Category::select(
            'id',
            'category_name',
            'nepali_title',
            'category_url',
            'external_link',
            'category_id'
        )
        ->status()
        ->whereIn('category_url',$cat_url)
        ->get();
    }

    public function getNavabarCategories(){
        $navbarCategories = Category::select(
            'id',
            'category_name',
            'nepali_title',
            'category_url',
            'external_link',
            'category_id'
        )->status()->where('show_on_menu','1')->orderBy('position','asc')->limit(10)->get();
        

        $data['navbarCategories'] = $navbarCategories;

        return api_response($data, true, "Navbar Category fetched successfully", 200);
    }

    public function getHomePageData(){
        $todayDate = \Carbon\Carbon::now()->format('Y-m-d H:i:s');
        // dd($todayDate);
        $categories = $this->getCategory();
        // dd(now());
        if(isset($categories) && $categories->count()){
            foreach($categories as $category){
                $category->category_news_items = Content::select('tbl_contents.id','tbl_contents.title','tbl_contents.slug','tbl_contents.short_content','tbl_contents.content','tbl_contents.parallex_img','tbl_contents.publish_time')
                        ->join('category_content','category_content.content_id','tbl_contents.id')
                        ->where('category_content.category_id',$category->id)
                        ->where('tbl_contents.publish_status','1')
                        ->where('tbl_contents.delete_status','0')
                        ->where('tbl_contents.publish_time','<=', $todayDate)
                        ->limit(10)
                        ->orderBy('tbl_contents.publish_time','DESC')
                        ->get();
            }
        }
        
        $categoryBreaking = $categories->firstWhere('category_url','breaking-news');
        if($categoryBreaking)
        $categoryBreaking->category_news_items = $categoryBreaking->category_news_items->take(2);

        $categoryMainNews = $categories->firstWhere('category_url', 'main-news');
        if($categoryMainNews)
        $categoryMainNews->category_news_items = $categoryMainNews->category_news_items->take(5);

        $categoryPolitics = $categories->firstWhere('category_url', 'politics');
        if($categoryPolitics)
        $categoryPolitics->category_news_items = $categoryPolitics->category_news_items->take(7);

        $categoryEconomy = $categories->firstWhere('category_url', 'economy');
        if($categoryEconomy)
        $categoryEconomy->category_news_items = $categoryEconomy->category_news_items->take(8);

        $categoryInterview = $categories->firstWhere('category_url', 'crime'); //interview ma crime taneko xa
        if($categoryInterview)
        $categoryInterview->category_news_items = $categoryInterview->category_news_items->take(8);

        $categoryHealth = $categories->firstWhere('category_url', 'health');
        if($categoryHealth)
        $categoryHealth->category_news_items = $categoryHealth->category_news_items->take(8);

        $categoryThought = $categories->firstWhere('category_url', 'thought');
        if($categoryThought)
        $categoryThought->category_news_items = $categoryThought->category_news_items->take(4);

        $categoryIndustry = $categories->firstWhere('category_url', 'industry');
        if($categoryIndustry)
        $categoryIndustry->category_news_items = $categoryIndustry->category_news_items->take(8);

        $categorymiscellaneous = $categories->firstWhere('category_url', 'miscellaneous');
        if($categorymiscellaneous)
        $categorymiscellaneous->category_news_items = $categorymiscellaneous->category_news_items->take(8);

        $categoryeducation = $categories->firstWhere('category_url', 'education');
        if($categoryeducation)
        $categoryeducation->category_news_items = $categoryeducation->category_news_items->take(8);

        $categoryTourism = $categories->firstWhere('category_url', 'tourism');
        if($categoryTourism)
        $categoryTourism->category_news_items = $categoryTourism->category_news_items->take(8);

        $categoryLifestyle = $categories->firstWhere('category_url', 'lifestyle');
        if($categoryLifestyle)
        $categoryLifestyle->category_news_items = $categoryLifestyle->category_news_items->take(8);

        $categoryEntertainment = $categories->firstWhere('category_url', 'entertainment');
        if($categoryEntertainment)
        $categoryEntertainment->category_news_items = $categoryEntertainment->category_news_items->take(8);

        $categoryInternational = $categories->firstWhere('category_url', 'international');
        if($categoryInternational)
        $categoryInternational->category_news_items = $categoryInternational->category_news_items->take(8);

        $categoryGoodGovernance = $categories->firstWhere('category_url', 'good-governance');
        if($categoryGoodGovernance)
        $categoryGoodGovernance->category_news_items = $categoryGoodGovernance->category_news_items->take(8);

        $categoryShare = $categories->firstWhere('category_url', 'share');
        if($categoryShare)
        $categoryShare->category_news_items = $categoryShare->category_news_items->take(8);

        $categoryAuto = $categories->firstWhere('category_url', 'auto');
        if($categoryAuto)
        $categoryAuto->category_news_items = $categoryAuto->category_news_items->take(8);

        $categoryAgriculture = $categories->firstWhere('category_url', 'agriculture');
        if($categoryAgriculture)
        $categoryAgriculture->category_news_items = $categoryAgriculture->category_news_items->take(8);

        $categoryInformationTechnology = $categories->firstWhere('category_url', 'information-technology');
        if($categoryInformationTechnology)
        $categoryInformationTechnology->category_news_items = $categoryInformationTechnology->category_news_items->take(8);

        $categorySport = $categories->firstWhere('category_url', 'sport');
        if($categorySport)
        $categorySport->category_news_items = $categorySport->category_news_items->take(8);

        $categoryPhotoFeature = $categories->firstWhere('category_url', 'photo-feature');
        if($categoryPhotoFeature)
        $categoryPhotoFeature->category_news_items = $categoryPhotoFeature->category_news_items->take(8);

        $categoryProvince1 = $categories->firstWhere('category_url', 'province-1');
        if($categoryProvince1)
        $categoryProvince1->category_news_items = $categoryProvince1->category_news_items->take(8);

        $categoryProvince2 = $categories->firstWhere('category_url', 'province-2');
        if($categoryProvince2)
        $categoryProvince2->category_news_items = $categoryProvince2->category_news_items->take(8);

        $categoryBagmati = $categories->firstWhere('category_url', 'bagamati');
        if($categoryBagmati)
        $categoryBagmati->category_news_items = $categoryBagmati->category_news_items->take(8);

        $categoryGandaki = $categories->firstWhere('category_url', 'gandaki');
        if($categoryGandaki)
        $categoryGandaki->category_news_items = $categoryGandaki->category_news_items->take(8);

        $categoryLumbini = $categories->firstWhere('category_url', 'lumbini');
        if($categoryLumbini)
        $categoryLumbini->category_news_items = $categoryLumbini->category_news_items->take(8);

        $categoryKarnali = $categories->firstWhere('category_url', 'karnali');
        if($categoryKarnali)
        $categoryKarnali->category_news_items = $categoryKarnali->category_news_items->take(8);

        $categorySudurpaschim = $categories->firstWhere('category_url', 'sudurpaschim');
        if($categorySudurpaschim)
        $categorySudurpaschim->category_news_items = $categorySudurpaschim->category_news_items->take(8);

        $videoContents = Content::status()->where('is_video','1')->orderBy('id','desc')->limit(10)->get();
        foreach($videoContents as $key => $row){
            $row->video_embed = "https://www.youtube.com/embed/".strip_tags($row->video); 
        }

        $data['categoryBreaking'] = $categoryBreaking;
        $data['categoryMainNews'] = $categoryMainNews;
        $data['categoryPolitics'] = $categoryPolitics;
        $data['categoryEconomy'] = $categoryEconomy;

        $data['categoryInterview'] = $categoryInterview;
        $data['categoryHealth'] = $categoryHealth;
        $data['categoryThought'] = $categoryThought;
        $data['categoryIndustry'] = $categoryIndustry;

        $data['categorymiscellaneous'] = $categorymiscellaneous;
        $data['categoryeducation'] = $categoryeducation;
        $data['categoryTourism'] = $categoryTourism;
        $data['categoryLifestyle'] = $categoryLifestyle;

        $data['categoryEntertainment'] = $categoryEntertainment;
        $data['categoryInternational'] = $categoryInternational;
        $data['categoryGoodGovernance'] = $categoryGoodGovernance;
        $data['categoryShare'] = $categoryShare;

        $data['categoryAuto'] = $categoryAuto;
        $data['categoryAgriculture'] = $categoryAgriculture;
        $data['categoryInformationTechnology'] = $categoryInformationTechnology;
        $data['categorySport'] = $categorySport;

        $data['categoryPhotoFeature'] = $categoryPhotoFeature;
        $data['categoryProvince1'] = $categoryProvince1;
        $data['categoryProvince2'] = $categoryProvince2;
        $data['categoryBagmati'] = $categoryBagmati;

        $data['categoryGandaki'] = $categoryGandaki;
        $data['categoryLumbini'] = $categoryLumbini;
        $data['categoryKarnali'] = $categoryKarnali;
        $data['categorySudurpaschim'] = $categorySudurpaschim;

        $data['videoContents'] = $videoContents;

        return api_response($data, true, "Home Page Data fetched successfully", 200);
    }

    public function categoryNews(Request $request){
        $slug = $request->category_url;
        $category = Category::where('category_url', $slug)->first();
        if (!$category) return api_response($slug, false, "Category not found", 404);
        // $sideNews = Content::where('category_content.category_id', '!=', $category->id)->latest()->limit(5)->get();
        $categoryContents = Content::select('tbl_contents.*')
                        ->join('category_content','category_content.content_id','tbl_contents.id')
                        ->where('category_content.category_id',$category->id)
                        ->where('tbl_contents.publish_status','1')
                        ->where('tbl_contents.delete_status','0')
                        ->orderBy('tbl_contents.publish_time','DESC')
                        ->paginate(50);

        $categoryContents = mapPageItems($categoryContents, 'categoryContents');

        $data['categoryContents'] = $categoryContents;

        return api_response($data, true, "Category news fetched successfully", 200);
    }

    public function getnewsdetails(Request $request){
        $slug = $request->slug;
        $news = Content::status()->where('slug', $slug)->first();
        $news->video_embed = "https://www.youtube.com/embed/".strip_tags($news->video); 

        if (!$news) return api_response($slug, false, "News not found", 404);

        $news1 = Content::status()->where('slug', $slug)->first();
        $relatedNews = $news1->categories->map(function ($item) use ($news) {
            return $item->contents->where('id', '!=', $news->id);
        })->flatten()->shuffle()->unique('id')->take(5);
        
        // $news->relatedNews = $relatedNews;
        $reporter = $news1->reporters->first();
        $news->reporterName = $reporter->nepali_title ? $reporter->nepali_title : $reporter->title;


        // $sideNews = Content::where('slug', '!=', $slug)->latest()->limit(5)->get();
        // $trendingNews = Content::status()->orderBy('view_count', 'desc')->limit(4)->get();
        // $tags = $news->tags;
        // $reaction = $news->reaction;
        // $reporter = $news->reporters->first();
    

        $news->increment('view_count');
        $news->increment('trending_count');

        $data['news'] = $news;
        $data['relatedNews'] = $relatedNews;
        // $data['relatedNews'] = $relatedNews;

        return api_response($data, true, "News fetched successfully", 200);
    }
}
