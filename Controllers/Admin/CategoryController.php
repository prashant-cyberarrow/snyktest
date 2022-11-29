<?php

namespace App\Http\Controllers\Admin;

use App\Category;
use App\Http\Traits\ImageTrait;
use App\Http\Traits\ArrayOfCategoryTrait;
use App\Http\Traits\UserLogTrait;
use Illuminate\Http\Request;
use App\Http\Controllers\Controller;

class CategoryController extends Controller
{
    use ImageTrait, UserLogTrait, ArrayOfCategoryTrait;

    private $selectCategoryId = 0;

    public function __construct()
    {
        $this->middleware('auth:web');
    }

    public function AjaxImageUpload(Request $request)
    {
        $formImage = "image";
        $files = $request->file('image');
        $this->imageUpload($request, $files, 'category', 'categories', $formImage);
    }

    private function checkParentChildRelation($category, $checkId)
    {
        $finalArray = $this->getArrayOfCategory($category->category_slug);
        foreach ($finalArray as $arr) {
            if ($arr == $checkId) {
                return "failure";
            }
        }
        return "success";
    }

    public function categoryTree($parent_id = 0, $sub_mark = '', $htmlOption = null)
    {
        $categories = Category::where('category_id', $parent_id)->where('delete_status', '0')->where('publish_status', '1')->get();
        if (count($categories) > 0) {
            $tes = "";
            foreach ($categories as $row) {
                if ($row->id == $this->selectCategoryId) {
                    $tes = " selected";
                } else {
                    $tes = "";
                }
                $htmlOption .= '<option value="' . $row->id . '"' . $tes . '>' . $sub_mark . $row->category_name . '</option>';
                $htmlOption .= $this->categoryTree($row->id, $sub_mark . '&nbsp&nbsp&nbsp&nbsp');
            }
        }
        return  $htmlOption;
    }

    public function index(Request $request)
    {
        if ($request->session()->has('ajaximage')) {
            $image = $request->session()->get('ajaximage');
            @unlink('uploads/' . 'categories/' . $image);
        }
        $categories = Category::where('delete_status', '0')->orderBy('id','asc')->paginate(20);

        $z = 0;
        foreach ($categories as $row) {
            $parent_content = Category::where('publish_status', '1')->where('delete_status', '0')->where('id', $row->category_id)->first();
            if ($parent_content == null) {
                $categories[$z]->setAttribute('parent_category', "रूत");
            } else {
                $categories[$z]->setAttribute('parent_category', $parent_content->nepali_title);
            }
            $z++;
        }
        return view('admin.list.category', compact('categories'));
    }

    public function fetch(Request  $request)
    {
        $categoryName = $request->categoryName;
        $parentCategoryName = $request->parentCategoryName;

        $categories = Category::where('delete_status', '0')->orderBy('id','asc')
            ->when($categoryName, function ($query, $categoryName) {
                return $query->where("nepali_title", "LIKE", "%$categoryName%");
            })
            ->when($parentCategoryName, function ($query, $parentCategoryName) {
                $category_list = Category::where('tbl_categories.category_name', "LIKE", "%$parentCategoryName%")->get();
                $arr = [];
                foreach ($category_list as $data) {
                    array_push($arr, $data->id);
                }
                return $query->whereIn('tbl_categories.category_id', $arr);
            })
            ->paginate(20);

        $z = 0;
        foreach ($categories as $row) {
            $parent_content = Category::where('publish_status', '1')->where('delete_status', '0')->where('id', $row->category_id)->first();
            if ($parent_content == null) {
                $categories[$z]->setAttribute('parent_category', "रूत");
            } else {
                $categories[$z]->setAttribute('parent_category', $parent_content->nepali_title);
            }
            $z++;
        }
        return view('admin.list.ajaxlist.category', compact('categories'));
    }

    public function create()
    {
        $categories = Category::where('category_id', '0')->with('child')->get();
        $htmlOption = $this->categoryTree();

        return view('admin.form.category', compact('categories', 'htmlOption'));
    }

    public function store(Request $request)
    {
        // dd($request);
        $this->validate(request(), [
            'category_name' => 'required',
            // 'category_url' => 'required',
            'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120'
        ]);

        $cat = new Category();

        $cat->category_id = request('category_id');
        $cat->position = request('position');
        $cat->category_name = request('category_name');
        $cat->nepali_title = request('nepali_title');
        $cat->description = request('description');
        $cat->category_url = str_slug(request('category_url'));
        $cat->image = $request->session()->get('ajaximage');
        $cat->external_link = request('external_link');
        $cat->show_mobile = request('show_mobile');
        $cat->show_mobile_menu = request('show_mobile_menu');
        $cat->show_on_menu = request('show_on_menu');

        $cat->publish_status = request('publish_status');
        $cat->meta_title = request('meta_title');
        $cat->meta_keywords = request('meta_keywords');
        $cat->meta_description = request('meta_description');

        $cat->save();

        $request->session()->forget('ajaximage');

        $this->storeUserLog('Category', $cat->id, $cat->category_name, 'create');

        return redirect('/ps-admin/categories')->with('success', 'Category created successfully.');
    }

    public function edit(Request $request, $id)
    {
        $selectCategory = Category::where('id', $id)->first();
        $this->selectCategoryId = $selectCategory->category_id;
        $htmlOption = $this->categoryTree();

        return view('admin.form.category', compact('selectCategory', 'htmlOption'));
    }

    public function update(Request $request, $id)
    {
        $category = Category::where('id', $id)->first();

        $checkId = $request->category_id; //to check parent child relation

        $manualValidation = $this->checkParentChildRelation($category, $checkId);
        if ($manualValidation == "success") {

            $this->validate(request(), [

                'category_name' => 'required',
                // 'category_url' => 'required',
                'image' => 'image|mimes:jpeg,png,jpg,gif,svg|max:5120'

            ]);

            $data = ([
                'category_name' => request('category_name'),
                'nepali_title' => request('nepali_title'),
                'category_id' => request('category_id'),
                'position' => request('position'),
                'category_url' => request('category_url'),
                'description' => request('description'),
                // 'image' => request('image'),
                'external_link' => request('external_link'),
                'show_mobile_menu' => request('show_mobile_menu'),
                'show_on_menu' => request('show_on_menu'),
                'meta_title' => request('meta_title'),
                'meta_keywords' => request('meta_keywords'),
                'meta_description' => request('meta_description'),
                'publish_status' => request('publish_status')
            ]);

            $file = request()->file('image');
            if ($file != null) {
                $image = $category->image;
                @unlink('uploads/categories/'.$image);
                $data1 = ([
                    'image' => $request->session()->get('ajaximage'),
                ]);
                Category::where('id', $id)->update($data1);
            }

            Category::where('id', $id)->update($data);

            $request->session()->forget('ajaximage');

            $this->storeUserLog('Category', $category->id, $category->category_name, 'update');

            return redirect('/ps-admin/categories')->with('success', 'Category updated successfully.');
        } else {
            return back()->with('error', 'Parent Category cannot be move inside child category, first move child category to other category or root category.');
        }
    }

    public function destroy($id)
    {
        $category = Category::where('id', $id)->first();

        if (isset($category)) {
            // $image = $admin->image;
            // @unlink('uploads/'.'admins/'.$image);
            $data = ([
                'delete_status' => '1',
            ]);
            //deleting admin
            category::where('id', $id)->update($data);

            $this->storeUserLog('Category', $category->id, $category->category_name, 'delete');
            return redirect('/ps-admin/categories')->with('success', 'category deleted successfully.');
        }

        return redirect('/ps-admin/categories')->with('error', 'Category deletion failed.');
    }
}
