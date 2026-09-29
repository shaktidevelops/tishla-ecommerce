<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration {
 public function up():void {
  Schema::create('settings',function(Blueprint $t){$t->string('key',100)->primary();$t->json('value');$t->timestamps();});
  Schema::create('pages',function(Blueprint $t){$t->uuid('id')->primary();$t->string('slug',160)->unique();$t->string('title',200);$t->string('excerpt',500)->nullable();$t->longText('body_html')->nullable();$t->string('template_key',50)->default('story');$t->string('status',30)->default('draft');$t->timestamp('published_at')->nullable();$t->timestamps();});
  Schema::create('banners',function(Blueprint $t){$t->uuid('id')->primary();$t->string('name',160);$t->string('eyebrow',100)->nullable();$t->string('title',220)->nullable();$t->text('description')->nullable();$t->string('image_url',700)->nullable();$t->string('mobile_image_url',700)->nullable();$t->string('cta_label',100)->nullable();$t->string('cta_url',500)->nullable();$t->unsignedInteger('sort_order')->default(0);$t->boolean('is_active')->default(true);$t->timestamps();});
  Schema::create('home_sections',function(Blueprint $t){$t->string('section_key',100)->primary();$t->string('eyebrow',120)->nullable();$t->string('title',220)->nullable();$t->json('content_json')->nullable();$t->unsignedInteger('sort_order')->default(0);$t->boolean('is_active')->default(true);$t->timestamps();});
  Schema::create('navigation_menus',function(Blueprint $t){$t->uuid('id')->primary();$t->string('code',80)->unique();$t->string('name',120);$t->string('location',50);$t->timestamps();});
  Schema::create('navigation_items',function(Blueprint $t){$t->uuid('id')->primary();$t->uuid('menu_id')->index();$t->uuid('parent_id')->nullable();$t->string('label',120);$t->string('url',500)->nullable();$t->string('item_type',40)->default('link');$t->unsignedInteger('sort_order')->default(0);$t->boolean('is_active')->default(true);$t->timestamps();});
  Schema::create('lookbooks',function(Blueprint $t){$t->uuid('id')->primary();$t->string('slug',160)->unique();$t->string('title',200);$t->text('description')->nullable();$t->string('cover_image_url',700)->nullable();$t->string('status',30)->default('draft');$t->timestamps();});
  Schema::create('lookbook_items',function(Blueprint $t){$t->uuid('id')->primary();$t->uuid('lookbook_id')->index();$t->uuid('product_id')->nullable();$t->string('image_url',700);$t->string('caption',220)->nullable();$t->unsignedInteger('sort_order')->default(0);$t->timestamps();});
  Schema::create('blog_posts',function(Blueprint $t){$t->uuid('id')->primary();$t->string('slug',180)->unique();$t->string('title',220);$t->string('excerpt',500)->nullable();$t->longText('body_html')->nullable();$t->string('cover_image_url',700)->nullable();$t->string('status',30)->default('draft');$t->timestamp('published_at')->nullable();$t->timestamps();});
  Schema::create('faq_entries',function(Blueprint $t){$t->uuid('id')->primary();$t->string('category',100)->nullable();$t->string('question',300);$t->longText('answer_html');$t->unsignedInteger('sort_order')->default(0);$t->boolean('is_active')->default(true);$t->timestamps();});
  Schema::create('seo_meta',function(Blueprint $t){$t->uuid('id')->primary();$t->string('entity_type',80);$t->uuid('entity_id');$t->string('meta_title',220)->nullable();$t->string('meta_description',320)->nullable();$t->string('canonical_url',700)->nullable();$t->string('og_image_url',700)->nullable();$t->timestamps();$t->index(['entity_type','entity_id']);});
  Schema::create('audit_log',function(Blueprint $t){$t->bigIncrements('id');$t->uuid('user_id')->nullable()->index();$t->string('action',80);$t->string('entity_type',80)->nullable();$t->string('entity_id',100)->nullable();$t->json('before_json')->nullable();$t->json('after_json')->nullable();$t->string('ip_address',45)->nullable();$t->text('user_agent')->nullable();$t->timestamps();});
 }
 public function down():void{foreach(['audit_log','seo_meta','faq_entries','blog_posts','lookbook_items','lookbooks','navigation_items','navigation_menus','home_sections','banners','pages','settings'] as $x)Schema::dropIfExists($x);}
};
