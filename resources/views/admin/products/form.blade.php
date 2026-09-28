@extends('layouts.admin')
@section('content')
<div class="mx-auto max-w-6xl space-y-6">
  <div><p class="text-sm font-semibold uppercase tracking-[0.2em] text-blue-600">Catalog Management</p><h1 class="mt-1 text-3xl font-bold">{{ isset($item) ? 'Edit Product' : 'Create Product' }}</h1></div>
  @if(session('success'))<div class="rounded-2xl border border-emerald-200 bg-emerald-50 p-4 text-emerald-800">{{ session('success') }}</div>@endif
  @if($errors->any())<div class="rounded-2xl border border-red-200 bg-red-50 p-4 text-red-800"><ul class="list-disc pl-5">@foreach($errors->all() as $error)<li>{{ $error }}</li>@endforeach</ul></div>@endif
  <form method="POST" action="{{ isset($item) ? route('admin.products.update',$item->id) : route('admin.products.store') }}" class="space-y-6">
    @csrf @if(isset($item)) @method('PUT') @endif
    <div class="grid gap-6 lg:grid-cols-3">
      <section class="lg:col-span-2 rounded-3xl border bg-white p-6 shadow-sm space-y-5">
        <h2 class="text-lg font-bold">Product Identity</h2>
        <div class="grid gap-4 sm:grid-cols-2">
          <label class="block"><span class="text-sm font-semibold">Product Name</span><input name="name" value="{{ old('name',$item->name ?? '') }}" required class="mt-2 w-full rounded-xl border p-3"></label>
          <label class="block"><span class="text-sm font-semibold">Storefront Title</span><input name="title" value="{{ old('title',$item->title ?? '') }}" required class="mt-2 w-full rounded-xl border p-3"></label>
          <label class="block"><span class="text-sm font-semibold">SKU</span><input name="sku" value="{{ old('sku',$item->sku ?? '') }}" required class="mt-2 w-full rounded-xl border p-3 font-mono"></label>
          <label class="block"><span class="text-sm font-semibold">Category</span><select name="category_id" class="mt-2 w-full rounded-xl border p-3"><option value="">Select category</option>@foreach($categories as $category)<option value="{{ $category->id }}" @selected(old('category_id',$item->category_id ?? '')==$category->id)>{{ $category->name }}</option>@endforeach</select></label>
        </div>
        <label class="block"><span class="text-sm font-semibold">Short Description</span><textarea name="short_description" rows="3" class="mt-2 w-full rounded-xl border p-3">{{ old('short_description',$item->short_description ?? '') }}</textarea></label>
        <label class="block"><span class="text-sm font-semibold">Full Description</span><textarea name="description" rows="7" class="mt-2 w-full rounded-xl border p-3">{{ old('description',$item->description ?? '') }}</textarea></label>
      </section>
      <aside class="space-y-6">
        <section class="rounded-3xl border bg-white p-6 shadow-sm space-y-4"><h2 class="font-bold">Status</h2><select name="status" class="w-full rounded-xl border p-3"><option value="draft" @selected(old('status',$item->status ?? 'active')==='draft')>Draft</option><option value="active" @selected(old('status',$item->status ?? 'active')==='active')>Active</option><option value="inactive" @selected(old('status',$item->status ?? '')==='inactive')>Inactive</option><option value="out_of_stock" @selected(old('status',$item->status ?? '')==='out_of_stock')>Out of Stock</option></select></section>
        <section class="rounded-3xl border bg-white p-6 shadow-sm"><h2 class="font-bold">QR Identity</h2><p class="mt-2 text-sm text-slate-500">QR payload is automatically tied to the product SKU.</p>@if(isset($item))<code class="mt-3 block break-all rounded-xl bg-slate-50 p-3 text-xs">{{ $item->qr_payload }}</code>@endif</section>
      </aside>
    </div>
    <section class="rounded-3xl border bg-white p-6 shadow-sm"><h2 class="text-lg font-bold">Commercial Settings</h2><div class="mt-5 grid gap-4 sm:grid-cols-2 lg:grid-cols-4"><label><span class="text-sm font-semibold">Retail Price</span><input type="number" step="0.01" min="0" name="retail_price" value="{{ old('retail_price',$item->retail_price ?? '') }}" required class="mt-2 w-full rounded-xl border p-3"></label><label><span class="text-sm font-semibold">Wholesale Price</span><input type="number" step="0.01" min="0" name="wholesale_price" value="{{ old('wholesale_price',$item->wholesale_price ?? '') }}" class="mt-2 w-full rounded-xl border p-3"></label><label><span class="text-sm font-semibold">Group-buy Price</span><input type="number" step="0.01" min="0" name="group_buying_price" value="{{ old('group_buying_price',$item->group_buying_price ?? '') }}" class="mt-2 w-full rounded-xl border p-3"></label><label><span class="text-sm font-semibold">Stock Quantity</span><input type="number" min="0" name="stock_qty" value="{{ old('stock_qty',$item->stock_qty ?? 0) }}" required class="mt-2 w-full rounded-xl border p-3"></label></div></section>
    <section class="rounded-3xl border bg-white p-6 shadow-sm"><h2 class="text-lg font-bold">SEO</h2><div class="mt-5 grid gap-4 md:grid-cols-2"><input name="seo_title" placeholder="SEO title" value="{{ old('seo_title',$item->seo_title ?? '') }}" class="rounded-xl border p-3"><textarea name="seo_description" rows="3" placeholder="SEO description" class="rounded-xl border p-3">{{ old('seo_description',$item->seo_description ?? '') }}</textarea></div></section>
    <div class="flex justify-end"><button class="rounded-2xl bg-slate-950 px-6 py-3 font-bold text-white">{{ isset($item) ? 'Save Product' : 'Create Product' }}</button></div>
  </form>
</div>
@endsection
