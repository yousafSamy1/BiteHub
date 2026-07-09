@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">

    <div class="mb-4 text-white">
        <a href="{{ route('kitchen.plans') }}" class="text-primary d-flex align-items-center gap-2 mb-2" style="font-size: 0.9rem; text-decoration: none;">
            <i data-feather="arrow-left" style="width:16px"></i> Back to Plans
        </a>
        <h3 class="fw-bold">Edit Subscription Plan</h3>
        <p class="text-muted">Update your offering to better suit your needs.</p>
    </div>

    <div class="row">
        <div class="col-lg-8">
            <div class="card bg-dark border-0 shadow-sm p-4" style="background: #1e293b; border-radius: 16px;">
                <form action="{{ route('kitchen.plans.update', $plan->KitchenPlanID) }}" method="POST">
                    @csrf
                    
                    <div class="mb-4">
                        <label class="form-label text-custom-muted fw-bold mb-2">Plan Title</label>
                        <input type="text" name="title" class="form-control bg-dark border-secondary text-white @error('title') is-invalid @enderror" 
                               value="{{ old('title', $plan->Title) }}" required>
                        @error('title') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

                    <div class="row mb-4">
                        <div class="col-md-3">
                            <label class="form-label text-custom-muted fw-bold mb-2">Price (EGP)</label>
                            <input type="number" step="0.01" name="price" class="form-control bg-dark border-secondary text-white @error('price') is-invalid @enderror" 
                                   value="{{ old('price', $plan->Price) }}" required>
                            @error('price') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-custom-muted fw-bold mb-2">Billing Period</label>
                            <select name="plan_time" class="form-select bg-dark border-secondary text-white @error('plan_time', $plan->PlanTime) is-invalid @enderror" required>
                                <option value="Daily" {{ old('plan_time', $plan->PlanTime) == 'Daily' ? 'selected' : '' }}>Daily</option>
                                <option value="Weekly" {{ old('plan_time', $plan->PlanTime) == 'Weekly' ? 'selected' : '' }}>Weekly</option>
                                <option value="Monthly" {{ old('plan_time', $plan->PlanTime) == 'Monthly' ? 'selected' : '' }}>Monthly</option>
                            </select>
                            @error('plan_time') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-custom-muted fw-bold mb-2">Meals Per Day</label>
                            <select name="meals_per_day" class="form-select bg-dark border-secondary text-white @error('meals_per_day') is-invalid @enderror" required>
                                <option value="1" {{ old('meals_per_day', $plan->MealsPerDay) == '1' ? 'selected' : '' }}>1 Meal</option>
                                <option value="2" {{ old('meals_per_day', $plan->MealsPerDay) == '2' ? 'selected' : '' }}>2 Meals</option>
                                <option value="3" {{ old('meals_per_day', $plan->MealsPerDay) == '3' ? 'selected' : '' }}>3 Meals</option>
                            </select>
                            @error('meals_per_day') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                        <div class="col-md-3">
                            <label class="form-label text-custom-muted fw-bold mb-2">Status</label>
                            <select name="status" class="form-select bg-dark border-secondary text-white @error('status') is-invalid @enderror" required>
                                <option value="Active" {{ old('status', $plan->Status) == 'Active' ? 'selected' : '' }}>Active</option>
                                <option value="Inactive" {{ old('status', $plan->Status) == 'Inactive' ? 'selected' : '' }}>Inactive</option>
                            </select>
                            @error('status') <div class="invalid-feedback">{{ $message }}</div> @enderror
                        </div>
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-custom-muted fw-bold mb-3 d-block">Included Menu Items <span class="text-danger">*</span></label>
                        <div class="row g-3">
                            @php $selectedItems = $plan->menuItems->pluck('MenuItemID')->toArray(); @endphp
                            @foreach($menuItems as $item)
                            <div class="col-md-6 col-lg-4">
                                <label class="item-selection-card d-block cursor-pointer position-relative">
                                    <input type="checkbox" name="menu_items[]" value="{{ $item->MenuItemID }}" class="position-absolute opacity-0" style="left:0;top:0" 
                                           {{ in_array($item->MenuItemID, old('menu_items', $selectedItems)) ? 'checked' : '' }}>
                                    <div class="card bg-dark border-secondary p-3 h-100 selection-ui" style="border: 1px solid rgba(255,255,255,0.1) !important; transition: all 0.3s ease;">
                                        <div class="d-flex align-items-center gap-3">
                                            @if($item->images->count() > 0)
                                                <img src="{{ asset('upload/item_images/'.$item->images->first()->Image) }}" style="width:40px;height:40px;border-radius:8px;object-fit:cover">
                                            @else
                                                <div class="bg-secondary rounded" style="width:40px;height:40px;display:flex;align-items:center;justify-content:center">🍱</div>
                                            @endif
                                            <div class="text-truncate">
                                                <div class="fw-bold text-white small">{{ $item->ItemName }}</div>
                                                <div class="text-success small">{{ number_format($item->ItemPrice, 2) }} EGP</div>
                                            </div>
                                        </div>
                                    </div>
                                </label>
                            </div>
                            @endforeach
                        </div>
                        @error('menu_items') <div class="text-danger small mt-2">{{ $message }}</div> @enderror
                    </div>

                    <div class="mb-4">
                        <label class="form-label text-custom-muted fw-bold mb-2">Description / What's Included</label>
                        <textarea name="description" rows="3" class="form-control bg-dark border-secondary text-white @error('description') is-invalid @enderror">{{ old('description', $plan->Description) }}</textarea>
                        @error('description') <div class="invalid-feedback">{{ $message }}</div> @enderror
                    </div>

<style>
    .item-selection-card input:checked + .selection-ui {
        border-color: var(--primary) !important;
        background: rgba(255, 107, 53, 0.1) !important;
        box-shadow: 0 0 15px rgba(255, 107, 53, 0.2);
    }
    .item-selection-card:hover .selection-ui {
        border-color: rgba(255, 107, 53, 0.5) !important;
    }
</style>

                    <div class="d-flex justify-content-end gap-3 mt-4">
                        <a href="{{ route('kitchen.plans') }}" class="btn btn-outline-secondary px-4 py-2">Cancel</a>
                        <button type="submit" class="btn btn-info px-5 py-2 fw-bold text-white rounded-pill">Update Plan</button>
                    </div>
                </form>
            </div>
        </div>
    </div>

</div>
@endsection
