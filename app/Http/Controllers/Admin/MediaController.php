<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\MediaAsset;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Storage;
class MediaController extends Controller
{
 public function index(){return view('admin.media.index',['media'=>MediaAsset::latest()->paginate(40)]);}
 public function store(Request $request){
  $data=$request->validate(['file'=>['required','image','mimes:jpg,jpeg,png,webp,gif','max:5120'],'alt_text'=>['nullable','string','max:220']]);
  $path=$data['file']->store('','public');
  MediaAsset::create(['storage_provider'=>'local','public_url'=>asset('uploads/'.$path),'alt_text'=>$data['alt_text']??$data['file']->getClientOriginalName(),'mime_type'=>$data['file']->getMimeType(),'file_size'=>$data['file']->getSize()]);
  return back()->with('success','Media uploaded.');
 }
 public function destroy(MediaAsset $media){
  if(str_contains($media->public_url,'/uploads/')){$path=ltrim((string)parse_url($media->public_url,PHP_URL_PATH),'/');$relative=preg_replace('#^uploads/#','',$path);if($relative)Storage::disk('public')->delete($relative);}
  $media->delete();return back()->with('success','Media removed.');
 }
}
