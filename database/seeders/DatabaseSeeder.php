<?php

namespace Database\Seeders;

use App\Models\Collection;
use App\Models\Department;
use App\Models\Page;
use App\Models\Product;
use App\Models\ProductImage;
use App\Models\ProductVariant;
use App\Models\Setting;
use App\Models\User;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    public function run(): void
    {
        Setting::updateOrCreate(['key'=>'store'],['value'=>[
            'name'=>env('TISHLA_STORE_NAME','Tishla by Purnika Sales'),
            'domain'=>env('TISHLA_DOMAIN','https://www.tishla.com'),
            'currency'=>env('TISHLA_CURRENCY','INR'),
            'country'=>'IN','state'=>'Gujarat','city'=>'Surat',
            'support_email'=>env('TISHLA_SUPPORT_EMAIL','purnikasales@gmail.com'),
            'support_whatsapp'=>env('TISHLA_WHATSAPP','919574716712'),
        ]]);
        Setting::updateOrCreate(['key'=>'checkout'],['value'=>['guest_checkout'=>true,'cod_enabled'=>true,'online_payment_enabled'=>false,'minimum_order_value'=>0]]);
        Setting::updateOrCreate(['key'=>'tax'],['value'=>['gst_rate'=>(float)env('TISHLA_GST_RATE',5),'default_shipping_charge'=>0]]);
        foreach ([['Sarees','sarees'],['Lehengas','lehengas'],['Kurtis & Sets','kurtis-sets'],['Suits','suits'],['Gowns','gowns'],['Dupattas','dupattas'],['Blouses','blouses'],['Accessories','accessories']] as $i=>$row) Department::updateOrCreate(['slug'=>$row[1]],['name'=>$row[0],'sort_order'=>($i+1)*10,'is_active'=>true]);
        foreach (['New Arrivals'=>'new-arrivals','Bestsellers'=>'bestsellers','Wedding Edit'=>'wedding-edit','Festive Edit'=>'festive-edit','Party Edit'=>'party-edit','Ready to Ship'=>'ready-to-ship','Sale'=>'sale'] as $name=>$slug) Collection::updateOrCreate(['slug'=>$slug],['name'=>$name,'sort_order'=>10,'is_active'=>true,'is_featured'=>in_array($slug,['new-arrivals','bestsellers','wedding-edit'],true)]);
        foreach ([['about','About Tishla'],['shipping','Shipping & Delivery'],['returns','Returns'],['faq','Frequently Asked Questions'],['size-guide','Size Guide'],['contact','Contact Tishla']] as [$slug,$title]) Page::updateOrCreate(['slug'=>$slug],['title'=>$title,'excerpt'=>'Tishla by Purnika Sales · Surat','status'=>'published','published_at'=>now()]);
        if (env('TISHLA_ADMIN_PASSWORD')) {
            User::updateOrCreate(['email'=>env('TISHLA_ADMIN_EMAIL','admin@tishla.com')],[
                'name'=>env('TISHLA_ADMIN_NAME','Shakti Develops'),
                'password'=>Hash::make(env('TISHLA_ADMIN_PASSWORD')),
                'role'=>'admin','is_active'=>true,
            ]);
        }
        if (! filter_var(env('TISHLA_DEMO_DATA',true),FILTER_VALIDATE_BOOLEAN)) return;
        $seed=[
            ['TS-NOOR-001','noor-dola-silk-saree','Noor Dola Silk Saree','Saree','Dola Silk',2499,'Bestseller','https://images.unsplash.com/photo-1594633312681-425c7b97ccd1?auto=format&fit=crop&w=1200&q=88'],
            ['TS-HER-002','zari-heritage-silk','Zari Heritage Silk Saree','Saree','Pure Silk',3899,'New','https://images.unsplash.com/photo-1610030469983-98e550d6193c?auto=format&fit=crop&w=1200&q=88'],
            ['TS-PET-003','petal-organza-party-set','Petal Organza Party Saree','Saree','Organza',2799,'Trending','https://images.unsplash.com/photo-1583391733956-6c78276477e2?auto=format&fit=crop&w=1200&q=88'],
            ['TS-AUR-004','aurora-georgette-drape','Aurora Georgette Drape','Saree','Georgette',2199,'New','https://images.unsplash.com/photo-1621784563330-caee0b138a00?auto=format&fit=crop&w=1200&q=88'],
            ['TS-RBL-005','royal-brocade-lehenga','Royal Brocade Lehenga Set','Lehenga','Brocade',6499,'Exclusive','https://images.unsplash.com/photo-1610189012906-5e132fbbc77f?auto=format&fit=crop&w=1200&q=88'],
            ['TS-MAR-006','marigold-embroidered-kurti-set','Marigold Embroidered Kurti Set','Kurti Set','Viscose',1899,'Best Seller','https://images.unsplash.com/photo-1600185365483-26d7a4cc7519?auto=format&fit=crop&w=1200&q=88'],
            ['TS-GWN-007','ivory-evening-gown','Ivory Evening Gown','Gown','Crepe',4999,'Limited','https://images.unsplash.com/photo-1539008835657-9e8e9680c956?auto=format&fit=crop&w=1200&q=88'],
            ['TS-SUT-008','midnight-sequin-suit','Midnight Sequin Suit','Suit','Georgette',3199,'Party Edit','https://images.unsplash.com/photo-1591369822096-ffd140ec948f?auto=format&fit=crop&w=1200&q=88']
        ];
        foreach($seed as $i=>$row){
            [$sku,$slug,$name,$type,$fabric,$price,$badge,$image]=$row;
            $deptSlug=match($type){'Lehenga'=>'lehengas','Kurti Set'=>'kurtis-sets','Gown'=>'gowns','Suit'=>'suits',default=>'sarees'};
            $dept=Department::where('slug',$deptSlug)->first();
            $p=Product::updateOrCreate(['sku'=>$sku],[
                'slug'=>$slug,'name'=>$name,'short_description'=>'An occasion-ready Tishla piece with a modern Indian point of view.',
                'description'=>'Craft-led fashion designed for festive wardrobes, celebrations and elevated everyday dressing.',
                'department_id'=>$dept?->id,'fabric'=>$fabric,'product_type'=>$type,'shoot_type'=>'Model Shoot',
                'base_price'=>$price,'gst_rate'=>env('TISHLA_GST_RATE',5),'status'=>'active','featured'=>$i<6,'min_order_qty'=>1,'product_badge'=>$badge,
            ]);
            ProductVariant::updateOrCreate(['sku'=>$sku.'-DEFAULT'],['product_id'=>$p->id,'name'=>'Default','price'=>$price,'is_active'=>true]);
            ProductImage::updateOrCreate(['product_id'=>$p->id,'public_url'=>$image],['alt_text'=>$name,'sort_order'=>0,'is_primary'=>true]);
        }
    }
}
