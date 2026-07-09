@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="book-open" class="me-2"></i>My Menu Items</h4>
</div>

{{-- Add Item Form --}}
<div class="card mb-4">
<div class="card-header"><strong>Add New Item</strong></div>
<div class="card-body">
<form method="POST" action="{{ route('kitchen.menu.store') }}" class="row g-3" enctype="multipart/form-data">
@csrf
<div class="col-md-3">
    <label class="form-label">Item Name *</label>
    <input name="ItemName" class="form-control" required>
</div>
<div class="col-md-2">
    <label class="form-label">Price (EGP) *</label>
    <input type="number" step="0.01" name="ItemPrice" class="form-control" required>
</div>
<div class="col-md-2">
    <label class="form-label text-success">Discount Price (Optional)</label>
    <input type="number" step="0.01" name="DiscountPrice" class="form-control" placeholder="0.00">
</div>
<div class="col-md-3">
    <label class="form-label">Category</label>
    <select name="CategoryID" class="form-select" onchange="toggleNewCat(this,'new-cat-add')">
        <option value="">None</option>
        @foreach($categories as $cat)
        <option value="{{ $cat->CategoryID }}">{{ $cat->Name }}</option>
        @endforeach
        <option value="__new__">➕ Add New Category</option>
    </select>
    <input type="text" id="new-cat-add" name="NewCategoryName" class="form-control mt-2 d-none" placeholder="Type new category name...">
</div>
<div class="col-md-2">
    <label class="form-label">Description</label>
    <input name="Description" class="form-control" placeholder="Short description...">
</div>
<div class="col-md-3">
    <label class="form-label">Ingredients</label>
    <input name="Ingredients" class="form-control" placeholder="Key ingredients...">
</div>
<div class="col-md-2">
    <label class="form-label">Portion Size</label>
    <input name="PortionSize" class="form-control" placeholder="e.g. 500g, 1 Person">
</div>
<div class="col-md-2">
    <label class="form-label">Calories</label>
    <input type="number" name="Calories" class="form-control" placeholder="450">
</div>
<div class="col-md-2">
    <label class="form-label">Protein (g)</label>
    <input type="number" name="Protein" class="form-control" placeholder="30">
</div>
<div class="col-md-2">
    <label class="form-label">Carbs (g)</label>
    <input type="number" name="Carbs" class="form-control" placeholder="40">
</div>
<div class="col-md-2">
    <label class="form-label">Fats (g)</label>
    <input type="number" name="Fats" class="form-control" placeholder="15">
</div>
<div class="col-md-2">
    <label class="form-label">Prep Time (min)</label>
    <input type="number" name="PrepTime" class="form-control" placeholder="30">
</div>
<div class="col-md-12 mb-3">
    <label class="form-label">Tags (Select all that apply)</label>
    <select name="tags[]" class="form-select select2-tags" multiple>
        @foreach($tags as $category => $categoryTags)
            <optgroup label="{{ $category }}">
                @foreach($categoryTags as $tag)
                    <option value="{{ $tag->id }}">{{ $tag->name }}</option>
                @endforeach
            </optgroup>
        @endforeach
    </select>
</div>
<div class="col-md-3">
    <label class="form-label">Images</label>
    <input type="file" name="images[]" class="form-control" multiple accept="image/*">
</div>
<div class="col-auto">
    <button class="btn btn-primary"><i data-feather="plus" style="width:14px"></i> Add Item</button>
</div>
</form>
</div>
</div>

{{-- Items Table --}}
<div class="card">
<div class="card-body p-0">
<div class="table-responsive">
<table class="table table-hover mb-0">
<thead class="table-light">
    <tr><th>#</th><th>Image</th><th>Name</th><th>Description</th><th>Price</th><th>Status</th><th>Actions</th></tr>
</thead>
<tbody>
@forelse($items as $item)
<tr>
    <td>{{ $item->MenuItemID }}</td>
    <td>
        @if($item->images->count() > 0)
            @php
                $img = $item->images->first()->Image;
                $url = str_starts_with($img, 'http') ? $img : asset('upload/item_images/'.$img);
            @endphp
            <img src="{{ $url }}" alt="Item Image" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px;">
        @else
            @php
                $mappedImg = 'grills.png'; // Default
                $in = strtolower($item->ItemName);
                if(str_contains($in, 'koshari')) $mappedImg = 'koshari.png';
                elseif(str_contains($in, 'mahshi') || str_contains($in, 'stuffed')) $mappedImg = 'mahshi.png';
                elseif(str_contains($in, 'foul') || str_contains($in, 'falafel')) $mappedImg = 'foul_falafel.png';
                elseif(str_contains($in, 'pasta') || str_contains($in, 'macarona')) $mappedImg = 'pasta.png';
                elseif(str_contains($in, 'molokhia') || str_contains($in, 'green') || str_contains($in, 'healthy')) $mappedImg = 'healthy.png';
                elseif(str_contains($in, 'fish') || str_contains($in, 'seafood')) $mappedImg = 'seafood.png';
                elseif(str_contains($in, 'dessert') || str_contains($in, 'sweet') || str_contains($in, 'kunafa') || str_contains($in, 'cake')) $mappedImg = 'sweets.png';
                elseif(str_contains($in, 'soup')) $mappedImg = 'soup.png';
                elseif(str_contains($in, 'juice') || str_contains($in, 'mango') || str_contains($in, 'drink')) $mappedImg = 'drinks.png';
                elseif(str_contains($in, 'wedding') || str_contains($in, 'corporate') || str_contains($in, 'package')) $mappedImg = 'packages.png';
                
                $fallbackUrl = url('upload/website_assets/'.$mappedImg);
            @endphp
            <img src="{{ $fallbackUrl }}" alt="Thematic Fallback" style="width: 50px; height: 50px; object-fit: cover; border-radius: 4px; opacity: 0.8;">
        @endif
    </td>
    <td><strong>{{ $item->ItemName }}</strong></td>
    <td class="text-muted" style="font-size:.85rem">{{ Str::limit($item->Description, 50) }}</td>
    <td>
        {{ number_format($item->ItemPrice, 2) }} EGP
        @if($item->DiscountPrice)
            <br><span class="badge bg-success" style="font-size:0.7rem">Sale: {{ number_format($item->DiscountPrice, 2) }} EGP</span>
        @endif
    </td>
    <td>
        <span class="badge bg-{{ $item->Status === 'Available' ? 'success' : 'secondary' }}">{{ $item->Status }}</span>
    </td>
    <td>
        {{-- Toggle --}}
        <form method="POST" action="{{ route('kitchen.menu.toggle', $item->MenuItemID) }}" class="d-inline">@csrf
            <button class="btn btn-sm btn-outline-{{ $item->Status === 'Available' ? 'warning' : 'success' }}">
                {{ $item->Status === 'Available' ? 'Disable' : 'Enable' }}
            </button>
        </form>

        {{-- Edit Modal Trigger --}}
        <button class="btn btn-sm btn-outline-primary" data-bs-toggle="modal" data-bs-target="#editItem{{ $item->MenuItemID }}">
            <i data-feather="edit-2" style="width:13px"></i>
        </button>

        {{-- Delete --}}
        <form method="POST" action="{{ route('kitchen.menu.delete', $item->MenuItemID) }}" class="d-inline" onsubmit="return confirm('Delete item?')">
            @csrf @method('DELETE')
            <button class="btn btn-sm btn-outline-danger"><i data-feather="trash-2" style="width:13px"></i></button>
        </form>
    </td>
</tr>

{{-- Edit Modal --}}
<div class="modal fade" id="editItem{{ $item->MenuItemID }}" tabindex="-1">
<div class="modal-dialog">
<div class="modal-content">
<div class="modal-header"><h5 class="modal-title">Edit Item</h5><button type="button" class="btn-close" data-bs-dismiss="modal"></button></div>
<form method="POST" action="{{ route('kitchen.menu.update', $item->MenuItemID) }}" enctype="multipart/form-data">@csrf
<div class="modal-body">
    <div class="mb-3"><label class="form-label">Name</label>
        <input name="ItemName" class="form-control" value="{{ $item->ItemName }}" required></div>
    <div class="row">
        <div class="col-md-6 mb-3"><label class="form-label">Price (EGP)</label>
            <input type="number" step="0.01" name="ItemPrice" class="form-control" value="{{ $item->ItemPrice }}" required></div>
        <div class="col-md-6 mb-3"><label class="form-label text-success">Discount Price (Optional)</label>
            <input type="number" step="0.01" name="DiscountPrice" class="form-control" value="{{ $item->DiscountPrice }}"></div>
    </div>
    <div class="mb-3"><label class="form-label">Description</label>
        <textarea name="Description" class="form-control" rows="2">{{ $item->Description }}</textarea></div>
    <div class="mb-3"><label class="form-label">Ingredients</label>
        <textarea name="Ingredients" class="form-control" rows="2">{{ $item->Ingredients }}</textarea></div>
    <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Portion Size</label>
            <input name="PortionSize" class="form-control" value="{{ $item->PortionSize }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Calories</label>
            <input type="number" name="Calories" class="form-control" value="{{ $item->Calories }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Prep Time (min)</label>
            <input type="number" name="PrepTime" class="form-control" value="{{ $item->PrepTime }}"></div>
    </div>
    <div class="row">
        <div class="col-md-4 mb-3"><label class="form-label">Protein (g)</label>
            <input type="number" name="Protein" class="form-control" value="{{ $item->Protein }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Carbs (g)</label>
            <input type="number" name="Carbs" class="form-control" value="{{ $item->Carbs }}"></div>
        <div class="col-md-4 mb-3"><label class="form-label">Fats (g)</label>
            <input type="number" name="Fats" class="form-control" value="{{ $item->Fats }}"></div>
    </div>
    <div class="mb-3">
        <label class="form-label">Tags (Select all that apply)</label>
        <select name="tags[]" class="form-select select2-tags" multiple style="width: 100%;">
            @php $itemTagIds = $item->tags->pluck('id')->toArray(); @endphp
            @foreach($tags as $category => $categoryTags)
                <optgroup label="{{ $category }}">
                    @foreach($categoryTags as $tag)
                        <option value="{{ $tag->id }}" @selected(in_array($tag->id, $itemTagIds))>{{ $tag->name }}</option>
                    @endforeach
                </optgroup>
            @endforeach
        </select>
    </div>
    <div class="mb-3"><label class="form-label">Category</label>
        <select name="CategoryID" class="form-select" onchange="toggleNewCat(this,'new-cat-{{ $item->MenuItemID }}')">
            <option value="">None</option>
            @foreach($categories as $cat)
            <option value="{{ $cat->CategoryID }}" @selected($item->CategoryID == $cat->CategoryID)>
                {{ $cat->Name }}
            </option>
            @endforeach
            <option value="__new__">➕ Add New Category</option>
        </select>
        <input type="text" id="new-cat-{{ $item->MenuItemID }}" name="NewCategoryName" class="form-control mt-2 d-none" placeholder="Type new category name...">
    </div>
    <div class="mb-3">
        <label class="form-label">Upload New Images (Replaces existing)</label>
        <input type="file" name="images[]" class="form-control" multiple accept="image/*">
    </div>
    @if($item->images->count() > 0)
    <div class="mb-3">
        <label class="form-label d-block">Current Images</label>
        <div class="d-flex flex-wrap gap-2">
            @foreach($item->images as $img)
                @php
                    $url = str_starts_with($img->Image, 'http') ? $img->Image : asset('upload/item_images/'.$img->Image);
                @endphp
                <img src="{{ $url }}" style="width: 60px; height: 60px; object-fit: cover; border-radius: 4px; border: 1px solid #ddd;">
            @endforeach
        </div>
    </div>
    @endif
</div>
<div class="modal-footer">
    <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
    <button type="submit" class="btn btn-primary">Save Changes</button>
</div>
</form>
</div></div></div>
@empty
<tr><td colspan="6" class="text-center text-muted py-4">No menu items yet. Add your first item above!</td></tr>
@endforelse
</tbody>
</table>
</div>
</div>
@if($items->hasPages())
<div class="card-footer">{{ $items->links() }}</div>
@endif
</div>
</div>
@endsection

@push('custom-scripts')
<link href="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/css/select2.min.css" rel="stylesheet" />
<script src="https://cdn.jsdelivr.net/npm/select2@4.1.0-rc.0/dist/js/select2.min.js"></script>
<script>
$(document).ready(function() {
    $('.select2-tags').select2({
        placeholder: "Select tags",
        allowClear: true
    });
});

function toggleNewCat(select, inputId) {
    if (select.value === '__new__') {
        window.location.href = "{{ route('kitchen.categories') }}";
    } else {
        const input = document.getElementById(inputId);
        if (input) {
            input.classList.add('d-none');
            input.required = false;
            input.value = '';
        }
    }
}
</script>
@endpush
