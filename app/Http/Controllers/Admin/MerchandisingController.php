<?php
namespace App\Http\Controllers\Admin;
use App\Http\Controllers\Controller;
use App\Models\Collection;
use App\Models\Department;
class MerchandisingController extends Controller
{
 public function index(){return view('admin.merchandising.index',['departments'=>Department::withCount('products')->orderBy('sort_order')->get(),'collections'=>Collection::withCount('products')->orderBy('sort_order')->get()]);}
}
