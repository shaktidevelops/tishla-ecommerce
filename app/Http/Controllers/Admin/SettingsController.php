<?php

namespace App\Http\Controllers\Admin;

use App\Http\Controllers\Controller;
use App\Models\Setting;
use Illuminate\Http\Request;

class SettingsController extends Controller
{
    public function index()
    {
        $settings=Setting::orderBy('key')->get()->keyBy('key');
        return view('admin.settings.index',compact('settings'));
    }

    public function update(Request $request)
    {
        $data=$request->validate([
            'store_name'=>'required|string|max:200',
            'support_email'=>'required|email|max:160',
            'support_whatsapp'=>'required|string|max:30',
            'domain'=>'required|string|max:255',
            'gst_rate'=>'required|numeric|min:0|max:100',
            'shipping_charge'=>'required|numeric|min:0',
            'guest_checkout'=>'nullable|boolean',
            'cod_enabled'=>'nullable|boolean',
            'online_payment_enabled'=>'nullable|boolean',
            'care_instructions'=>'required|string|max:5000',
            'shipping_notes'=>'required|string|max:5000',
        ]);

        Setting::updateOrCreate(['key'=>'store'],['value'=>[
            'name'=>$data['store_name'],'domain'=>$data['domain'],
            'support_email'=>$data['support_email'],'support_whatsapp'=>$data['support_whatsapp'],
        ]]);
        Setting::updateOrCreate(['key'=>'tax'],['value'=>[
            'gst_rate'=>(float)$data['gst_rate'],'default_shipping_charge'=>(float)$data['shipping_charge'],
        ]]);
        Setting::updateOrCreate(['key'=>'checkout'],['value'=>[
            'guest_checkout'=>(bool)($data['guest_checkout']??false),
            'cod_enabled'=>(bool)($data['cod_enabled']??false),
            'online_payment_enabled'=>(bool)($data['online_payment_enabled']??false),
        ]]);
        Setting::updateOrCreate(['key'=>'commerce_defaults'],['value'=>[
            'care_instructions'=>$data['care_instructions'],
            'shipping_notes'=>$data['shipping_notes'],
        ]]);

        return back()->with('success','Store settings and shared commerce copy saved.');
    }
}
