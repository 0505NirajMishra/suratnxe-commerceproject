<?php
namespace App\Http\Controllers;
use App\Models\{Category,Coupon,Enquiry,Order,Product,ProductImage,Setting,User}; use Illuminate\Http\Request; use Illuminate\Support\Facades\Hash; use Illuminate\Support\Str; use Illuminate\Support\Facades\Storage; use Illuminate\Validation\Rule;
class AdminController extends Controller {
 public function login()
 {
  if(auth('admin')->user()?->isAdmin()){return redirect()->route('admin.dashboard');}
  return response()->view('admin.login')->withHeaders([
   'Cache-Control'=>'no-store, no-cache, must-revalidate, max-age=0',
   'Pragma'=>'no-cache',
   'Expires'=>'0',
  ]);
 }
 public function authenticate(Request $r)
 {
  $d=$r->validate(['email'=>'required|email','password'=>'required']);
  if(!User::where('email',$d['email'])->where('role','admin')->exists()){return back()->withErrors(['email'=>'Invalid admin credentials.'])->onlyInput('email');}
  $csrfToken=$r->session()->token();
  $guard=auth('admin');
  if(!$guard->attempt($d)){return back()->withErrors(['email'=>'Invalid admin credentials.']);}
  $r->session()->put('_token',$csrfToken);
  if(!$guard->user()->isAdmin()){
   $guard->logout();
   return back()->withErrors(['email'=>'This account does not have administrator access.']);
  }
  $r->session()->put('admin_last_activity',now()->timestamp);
  return redirect()->route('admin.dashboard');
 }
 public function activity(){return response()->noContent();}
 public function dashboard(){return view('admin.dashboard',['stats'=>['products'=>Product::count(),'orders'=>Order::count(),'customers'=>User::where('role','customer')->count(),'revenue'=>Order::where('payment_status','Paid')->sum('total')],'orders'=>Order::latest()->take(8)->get()]);}
 public function products(Request $r){$products=Product::with('category')->when($r->q,fn($q)=>$q->where('name','like','%'.$r->q.'%')->orWhere('sku','like','%'.$r->q.'%'))->latest()->paginate(15)->withQueryString();return view('admin.products',compact('products'));}
 public function productCreate(){return view('admin.product-form',['product'=>new Product,'categories'=>Category::orderBy('name')->get(),'coupons'=>Coupon::orderBy('code')->get()]);} public function productEdit(Product $product){$product->load('images');return view('admin.product-form',['product'=>$product,'categories'=>Category::orderBy('name')->get(),'coupons'=>Coupon::orderBy('code')->get()]);}
 public function productSave(Request $r,?Product $product=null)
 {
  $product=$product?:new Product;
  $data=$r->validate([
   'category_id'=>'required|exists:categories,id',
   'coupon_id'=>'nullable|exists:coupons,id',
   'name'=>'required|string|max:190',
   'sku'=>['required','string','max:80',Rule::unique('products','sku')->ignore($product->id)],
   'price'=>'required|numeric|min:0',
   'mrp'=>'nullable|numeric|min:0|gte:price',
   'stock'=>'required|integer|min:0',
   'width'=>'required|string|max:30',
   'fabric'=>'required|string|max:120',
   'usage'=>'required|string|max:190',
   'care'=>'required|string|max:190',
   'colors'=>'required|string|max:190',
   'description'=>'required|string|max:5000',
   'status'=>'required|in:active,inactive',
   'tags'=>'nullable|string|max:255',
   'images'=>[$product->exists?'nullable':'required','array','min:'.($product->exists?0:1),'max:10'],
   'images.*'=>'image|mimes:jpg,jpeg,png,webp|max:5120',
  ]);
  $images=$r->file('images',[]);
  unset($data['images']);
  $data['status']=$data['status']==='inactive'?'draft':'active';
  $data['slug']=Str::slug($data['name']).'-'.Str::lower(Str::random(5));
  $data['tags']=array_values(array_filter(array_map('trim',explode(',', $data['tags']??''))));
  $product->fill($data);
  $product->save();
  foreach($images as $image){
   $path=$image->store('products','public');
   if(!$path) return back()->withErrors(['images'=>'One or more fabric images could not be saved.'])->withInput();
   $product->images()->create(['path'=>$path,'sort_order'=>$product->images()->count()]);
  }
  return redirect()->route('admin.products')->with('success','Fabric saved successfully.');
 }
 public function productDelete(Product $product){$product->delete();return back()->with('success','Fabric deleted.');}
 public function categories(){return view('admin.categories',['categories'=>Category::withCount('products')->orderBy('sort_order')->paginate(15)->withQueryString()]);}
 public function categorySave(Request $r)
 {
  $data=$r->validate([
   'name'=>'required|string|max:120',
   'code'=>['required','string','max:10',Rule::unique('categories','code')],
   'description'=>'nullable|string|max:1000',
   'image'=>'required|image|mimes:jpg,jpeg,png,webp|max:5120',
   'is_active'=>'nullable|boolean',
  ]);
  $path=$r->file('image')->store('categories','public');
  if(!$path) return back()->withErrors(['image'=>'Unable to save this category image.'])->withInput();
  $data['image']=$path;
  $data['slug']=Str::slug($data['name']).'-'.Str::lower(Str::random(5));
  $data['sort_order']=Category::max('sort_order')+1;
  $data['is_active']=$r->boolean('is_active');
  Category::create($data);
  return back()->with('success','Category created.');
 }
 public function categoryUpdate(Request $r,Category $category)
 {
  $data=$r->validate([
   'name'=>'required|string|max:120',
   'code'=>['required','string','max:10',Rule::unique('categories','code')->ignore($category->id)],
   'description'=>'nullable|string|max:1000',
   'image'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
   'sort_order'=>'required|integer|min:0|max:65535',
   'is_active'=>'nullable|boolean',
  ]);
  if($image=$r->file('image')){
   $path=$image->store('categories','public');
   if(!$path) return back()->withErrors(['image'=>'Unable to save this category image.'])->withInput();
   if($category->image) Storage::disk('public')->delete($category->image);
   $data['image']=$path;
  }
  $data['slug']=Str::slug($data['name']).'-'.Str::lower(Str::random(5));
  $data['is_active']=$r->boolean('is_active');
  $category->update($data);
  return back()->with('success','Category updated.');
 }
 public function categoryDelete(Category $category){if($category->image) Storage::disk('public')->delete($category->image);$category->delete();return back()->with('success','Category deleted with its products.');}
 public function orders(Request $r){$orders=$this->orderQuery($r)->with(['items','timeline'])->paginate(15)->withQueryString();return view('admin.orders',compact('orders'));}
 public function ordersExport(Request $r)
 {
  $orders=$this->orderQuery($r)->with('items')->get();
  return response()->streamDownload(function() use($orders){
   $output=fopen('php://output','w');
   fwrite($output,"\xEF\xBB\xBF");
   fputcsv($output,['Order number','Customer','Email','Phone','Address','Items','Subtotal','Discount','Shipping','Tax','Total','Payment method','Payment status','Order status','Courier','Tracking number','Placed at']);
   foreach($orders as $order){
    $address=$order->address??[];
    $addressText=implode(', ',array_filter([$address['line1']??null,$address['line2']??null,$address['landmark']??null,$address['city']??null,$address['state']??null,$address['pin']??null]));
    $itemsText=$order->items->map(fn($item)=>$item->name.' × '.$item->qty)->implode('; ');
    fputcsv($output,[$order->order_no,$order->name,$order->email,$order->phone,$addressText,$itemsText,$order->subtotal,$order->discount,$order->shipping,$order->tax,$order->total,$order->payment_method,$order->payment_status,$order->status,$order->courier,$order->tracking_no,$order->created_at->toDateTimeString()]);
   }
   fclose($output);
  },'surat-nx-orders-'.now()->format('Ymd-His').'.csv',['Content-Type'=>'text/csv; charset=UTF-8']);
 }
 public function orderUpdate(Request $r,Order $order){$d=$r->validate(['status'=>['required',Rule::in(Order::STATUSES)],'courier'=>'nullable|string|max:120','tracking_no'=>'nullable|string|max:120','payment_status'=>'required|in:Pending,Paid,COD,Failed,Refunded']);$order->update(collect($d)->except('status')->all());app(\App\Services\OrderService::class)->setStatus($order,$d['status']);return back()->with('success','Order updated.');}
 public function customers(Request $r){$customers=User::where('role','customer')->withCount('orders')->when($r->q,fn($q)=>$q->where(fn($search)=>$search->where('name','like','%'.$r->q.'%')->orWhere('email','like','%'.$r->q.'%')))->latest()->paginate(20)->withQueryString();return view('admin.customers',compact('customers'));}
 public function customer(User $customer){abort_unless($customer->role==='customer',404);$customer->load(['addresses','orders.items','orders.timeline']);return view('admin.customer',compact('customer'));}
 private function orderQuery(Request $r){return Order::query()->when($r->filled('status'),fn($q)=>$q->where('status',(string)$r->input('status')))->when($r->filled('q'),fn($q)=>$q->where(fn($x)=>$x->where('order_no','like','%'.$r->q.'%')->orWhere('email','like','%'.$r->q.'%')->orWhere('name','like','%'.$r->q.'%')))->latest();}
 public function coupons(){return view('admin.coupons',['coupons'=>Coupon::latest()->get()]);} public function couponSave(Request $r){$d=$r->validate(['code'=>'required|string|max:50|unique:coupons,code','type'=>'required|in:percent,flat','value'=>'required|numeric|min:0','min_order'=>'nullable|numeric|min:0','max_discount'=>'nullable|numeric|min:0','description'=>'nullable|string|max:190']);$d['code']=strtoupper($d['code']);Coupon::create($d);return back()->with('success','Coupon created.');} public function couponDelete(Coupon $coupon){$coupon->delete();return back()->with('success','Coupon deleted.');}
 public function enquiries(){return view('admin.enquiries',['enquiries'=>Enquiry::latest()->paginate(20)]);} public function enquiryUpdate(Enquiry $enquiry){$enquiry->update(['status'=>$enquiry->status==='New'?'Replied':'New']);return back()->with('success','Enquiry status updated.');}
 public function settings(){return view('admin.settings',['settings'=>Setting::all_()]);} public function settingsSave(Request $r){$d=$r->validate(['name'=>'required|string|max:120','tagline'=>'nullable|string|max:190','email'=>'required|email','phone'=>'required|string|max:30','address'=>'required|string|max:500','location'=>'required|string|max:120','free_ship'=>'required|numeric|min:0','ship_fee'=>'required|numeric|min:0','express_fee'=>'required|numeric|min:0','gst'=>'required|numeric|min:0|max:100','hero_kicker'=>'required|string|max:190','hero_title'=>'required|string|max:190','hero_subtitle'=>'required|string|max:500','feature_quality_title'=>'required|string|max:120','feature_quality_copy'=>'required|string|max:190','feature_custom_title'=>'required|string|max:120','feature_custom_copy'=>'required|string|max:190','feature_shipping_title'=>'required|string|max:120','feature_shipping_copy'=>'required|string|max:190','home_categories_title'=>'required|string|max:120','home_categories_link'=>'required|string|max:120','home_featured_title'=>'required|string|max:120','home_featured_copy'=>'required|string|max:500','home_custom_title'=>'required|string|max:190','home_custom_copy'=>'required|string|max:1000','home_custom_button'=>'required|string|max:120','home_why_title'=>'required|string|max:190','home_why_copy'=>'required|string|max:500','why_source_title'=>'required|string|max:120','why_source_copy'=>'required|string|max:500','why_quality_title'=>'required|string|max:120','why_quality_copy'=>'required|string|max:500','why_shipping_title'=>'required|string|max:120','why_shipping_copy'=>'required|string|max:500','why_payment_title'=>'required|string|max:120','why_payment_copy'=>'required|string|max:500','home_bulk_title'=>'required|string|max:190','home_bulk_copy'=>'required|string|max:500','home_bulk_button'=>'required|string|max:120','about_title'=>'required|string|max:190','about_intro'=>'required|string|max:2000','about_quality_title'=>'required|string|max:120','about_quality_copy'=>'required|string|max:500','about_collection_title'=>'required|string|max:120','about_collection_copy'=>'required|string|max:500','about_service_title'=>'required|string|max:120','about_service_copy'=>'required|string|max:500','about_cta_title'=>'required|string|max:190','about_cta_copy'=>'required|string|max:500','policy_shipping'=>'required|string|max:3000','policy_returns'=>'required|string|max:3000','policy_privacy'=>'required|string|max:3000','policy_terms'=>'required|string|max:3000','footer_description'=>'required|string|max:1000','social_facebook'=>'nullable|url|max:255','social_instagram'=>'nullable|url|max:255','social_youtube'=>'nullable|url|max:255']);Setting::put($d);return back()->with('success','Settings saved.');}
 public function deliverySettingsSave(Request $request)
 {
  $data = $request->validate([
   'standard_delivery_min_days' => 'required|integer|min:1|max:90',
   'standard_delivery_max_days' => 'required|integer|min:1|max:90|gte:standard_delivery_min_days',
   'express_delivery_min_days' => 'required|integer|min:1|max:90',
   'express_delivery_max_days' => 'required|integer|min:1|max:90|gte:express_delivery_min_days',
  ]);
  Setting::put($data);
  return back()->with('success', 'Delivery timelines saved.');
 }
 public function contactSettingsSave(Request $request)
 {
  $data = $request->validate([
   'phone' => 'required|string|max:30',
   'address' => 'required|string|max:500',
   'location' => 'required|string|max:120',
   'whatsapp' => 'required|string|max:30',
   'business_hours' => 'required|string|max:2000',
   'map_url' => 'required|url|max:500',
   'youtube_video' => 'required|url|max:500',
   'social_facebook' => 'nullable|url|max:255',
   'social_instagram' => 'nullable|url|max:255',
   'social_x' => 'nullable|url|max:255',
   'social_linkedin' => 'nullable|url|max:255',
   'social_youtube' => 'nullable|url|max:255',
  ]);

  Setting::put($data);

  return back()->with('success', 'Contact details and social links saved.');
 }
 public function reports(){ $monthly=Order::selectRaw('YEAR(created_at) as yr, MONTH(created_at) as mon, COUNT(*) as orders, SUM(total) as sales')->where('created_at','>=',now()->subMonths(11)->startOfMonth())->groupBy('yr','mon')->orderBy('yr')->orderBy('mon')->get(); $statuses=Order::selectRaw('status, COUNT(*) as total')->groupBy('status')->get(); return view('admin.reports',compact('monthly','statuses')); }
 public function profile(){return view('admin.profile');}
 public function profileSave(Request $r)
 {
  $user=$r->user();
  $data=$r->validate([
   'name'=>'required|string|max:120',
   'email'=>['required','email','max:190',Rule::unique('users','email')->ignore($user->id)],
   'phone'=>'nullable|string|max:20',
   'profile_photo'=>'nullable|image|mimes:jpg,jpeg,png,webp|max:5120',
   'password'=>'nullable|confirmed|min:8',
   'current_password'=>'required_with:password|string',
  ]);
  $photo=$r->file('profile_photo');
  unset($data['profile_photo']);
  if(empty($data['password'])) unset($data['password']);
  elseif(!Hash::check($data['current_password'],$user->password)) return back()->withErrors(['current_password'=>'Current password is incorrect.'])->withInput();
  unset($data['current_password']);
  if($photo){
   $path=$photo->store('profile-photos','public');
   if(!$path) return back()->withErrors(['profile_photo'=>'Unable to save the profile picture.'])->withInput();
   if($user->profile_photo_path) Storage::disk('public')->delete($user->profile_photo_path);
   $data['profile_photo_path']=$path;
  }
  $user->update($data);
  return back()->with('success','Admin profile updated.');
 }
}
