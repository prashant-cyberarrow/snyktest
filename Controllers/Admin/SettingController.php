<?php

namespace App\Http\Controllers\Admin;

use Illuminate\Http\Request;
use App\Http\Controllers\Controller;
use App\Http\Traits\ImageTrait;
use App\Setting;
use Image;

class SettingController extends Controller
{
    use ImageTrait;
    public function __construct()
    {
        $this->middleware('auth:web');
    }

    public function AjaxImageUpload(Request $request)
    {
        $formImage = "site_logo";
        $files = $request->file('site_logo');
        $this->imageUpload($request, $files, 'setting', 'settings', $formImage);
    }

    public function create(Request $request)
    {
        $setting = Setting::first();
        return view('admin.form.setting', compact('setting'));
    }

    public function store(Request $request)
    {
        //validate the form
        $this->validate(request(), [
            'site_name' => 'required',
            'site_url' => 'required',
            'site_logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        //create and save category
        $setting = new Setting();

        $setting->site_name = request('site_name');
        $setting->address = request('address');
        $setting->phone = request('phone');
        $setting->email = request('email');
        $setting->site_url = request('site_url');
        $setting->breaking_count = request('breaking_count');
        $setting->facebook = request('facebook');
        $setting->linkedin = request('linkedin');
        $setting->twitter = request('twitter');
        $setting->instagram = request('instagram');
        $setting->youtube = request('youtube');
        $setting->viber = request('viber');
        $setting->whatsapp = request('whatsapp');
        $setting->map_link = request('map_link');
        $setting->map_embed_link = request('map_embed_link');
       
        $setting->meta_title = request('meta_title');
        $setting->meta_keyword = request('meta_keyword');
        $setting->meta_description = request('meta_description');
        $setting->fb_id = request('fb_id');
        $setting->og_type = request('og_type');

        $setting->ht_1 = request('ht_1');
        $setting->ht_2 = request('ht_2');
        $setting->ht_3 = request('ht_3');
        $setting->ht_4 = request('ht_4');
        $setting->ht_5 = request('ht_5');
        $setting->registration_number = request('registration_number');

        $setting->site_logo = $request->session()->get('ajaximage');

        $file = request()->file('favicon');

        if ($file != null) {
            $img_name = 'favicon-' . time() . '.' . $file->clientExtension();

            $img = Image::make($file);

            $img->save('uploads/settings/' . $img_name);

            $setting->favicon = $img_name;
        }

        $file = request()->file('banner_image');

        if ($file != null) {
            $img_name = 'default_image-' . time() . '.' . $file->clientExtension();

            $img = Image::make($file);

            $img->save('uploads/settings/' . $img_name);

            $setting->banner_image = $img_name;
        }

        $setting->save();

        //redirect to dashboard
        return redirect('/ps-admin/settings')->with('success', 'Setting created successfully.');
    }

    public function update(Request $request, $id)
    {
        $setting = Setting::find($id);

        //validate the form
        $this->validate(request(), [
            'site_name' => 'required',
            'site_url' => 'required',
            // 'site_logo' => 'image|mimes:jpeg,png,jpg,gif,svg|max:2048',
        ]);

        $file = request()->file('site_logo');
        if ($file != null) {
            // dd('file not null');
            $image = $setting->site_logo;
            @unlink('uploads/' . 'settings/' . $image);
            $data1 = ([
                'site_logo' => $request->session()->get('ajaximage'),
            ]);
            Setting::where('id', $id)->update($data1);
        }

        $file1 = request()->file('favicon');

        if ($file1 != null) {
            $image = $setting->favicon;
            @unlink('uploads/settings/' . $image);

            $img_name = 'favicon-' . time() . '.' . $file1->clientExtension();
            $img = Image::make($file1);
            $img->save('uploads/settings/' . $img_name);

            $data3 = (['favicon' => $img_name]);

            Setting::where('id', $id)->update($data3);
        }

        $file1 = request()->file('banner_image');

        if ($file1 != null) {
            $image = $setting->banner_image;
            @unlink('uploads/settings/' . $image);

            $img_name = 'default_image-' . time() . '.' . $file1->clientExtension();
            $img = Image::make($file1);
            $img->save('uploads/settings/' . $img_name);

            $data3 = (['banner_image' => $img_name]);

            Setting::where('id', $id)->update($data3);
        }

        $data = ([
            'site_name' => request('site_name'),
            'address' => request('address'),
            'phone' => request('phone'),
            'email' => request('email'),
            'site_url' => request('site_url'),
            'breaking_count' => request('breaking_count'),
            'linkedin' => request('linkedin'),
            'facebook' => request('facebook'),
            'twitter' => request('twitter'),
            'instagram' => request('instagram'),
            'youtube' => request('youtube'),
            'viber' => request('viber'),
            'whatsapp' => request('whatsapp'),
            'map_embed_link' => request('map_embed_link'),
            'map_link' => request('map_link'),
            
            'meta_title' => request('meta_title'),
            'meta_keyword' => request('meta_keyword'),
            'meta_description' => request('meta_description'),
            'og_type' => request('og_type'),
            'fb_id' => request('fb_id'),

            'ht_1' => request('ht_1'),
            'ht_2' => request('ht_2'),
            'ht_3' => request('ht_3'),
            'ht_4' => request('ht_4'),
            'ht_5' => request('ht_5'),
            'registration_number' => request('registration_number')
        ]);

        Setting::where('id', $id)->update($data);

        //redirect to dashboard
        return redirect('/ps-admin/settings')->with('success', 'Setting updated successfully.');
    }
}
