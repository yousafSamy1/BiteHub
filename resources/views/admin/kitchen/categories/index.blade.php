@extends('admin.admin_dashboard')
@section('admin')
<div class="page-content">
<div class="d-flex justify-content-between align-items-center flex-wrap grid-margin">
    <h4 class="mb-0"><i data-feather="tag" class="me-2"></i>Categories</h4>
</div>

@if(session('message'))
<div class="alert alert-{{ session('alert-type') === 'success' ? 'success' : (session('alert-type') === 'error' ? 'danger' : 'warning') }} alert-dismissible fade show">
    {{ session('message') }}
    <button type="button" class="btn-close" data-bs-dismiss="alert"></button>
</div>
@endif

<div class="row g-4">

    {{-- Add New Category Card --}}
    <div class="col-md-4">
        <div class="card h-100">
            <div class="card-header"><strong><i data-feather="plus-circle" style="width:16px" class="me-1"></i>Add New Category</strong></div>
            <div class="card-body">
                <form method="POST" action="{{ route('kitchen.categories.store') }}">
                    @csrf
                    <div class="mb-3">
                        <label class="form-label">Category Name <span class="text-danger">*</span></label>
                        <input type="text" name="CategoryName"
                               class="form-control @error('CategoryName') is-invalid @enderror"
                               placeholder="e.g. Main Meals" value="{{ old('CategoryName') }}" required>
                        @error('CategoryName')
                            <div class="invalid-feedback">{{ $message }}</div>
                        @enderror
                    </div>
                    <div class="mb-3">
                        <label class="form-label">Description</label>
                        <textarea name="Description" class="form-control" rows="3"
                                  placeholder="Optional description...">{{ old('Description') }}</textarea>
                    </div>
                    <button type="submit" class="btn btn-primary w-100">
                        <i data-feather="save" style="width:14px"></i> Save Category
                    </button>
                </form>
            </div>
        </div>
    </div>

    {{-- Categories Table --}}
    <div class="col-md-8">
        <div class="card">
            <div class="card-header d-flex justify-content-between align-items-center">
                <strong>All Categories ({{ $categories->total() }})</strong>
                <form method="GET" action="{{ route('kitchen.categories') }}" class="d-flex gap-2">
                    <input type="text" name="search" class="form-control form-control-sm"
                           placeholder="Search..." value="{{ request('search') }}">
                    <button class="btn btn-sm btn-outline-secondary">Go</button>
                    @if(request('search'))
                        <a href="{{ route('kitchen.categories') }}" class="btn btn-sm btn-outline-danger">✕</a>
                    @endif
                </form>
            </div>
            <div class="card-body p-0">
                <div class="table-responsive">
                    <table class="table table-hover mb-0">
                        <thead class="table-light">
                            <tr>
                                <th style="width:60px">#</th>
                                <th>Name</th>
                                <th>Description</th>
                                <th>Items</th>
                                <th style="width:130px">Actions</th>
                            </tr>
                        </thead>
                        <tbody>
                        @forelse($categories as $cat)
                        <tr>
                            <td class="text-muted">{{ $cat->CategoryID }}</td>
                            <td><strong>{{ $cat->Name }}</strong></td>
                            <td class="text-muted" style="font-size:.85rem">{{ Str::limit($cat->Description, 60) ?: '—' }}</td>
                            <td><span class="badge bg-secondary">{{ $cat->menuItems()->count() }}</span></td>
                            <td>
                                {{-- Edit Trigger --}}
                                <button class="btn btn-sm btn-outline-primary"
                                        data-bs-toggle="modal" data-bs-target="#editCat{{ $cat->CategoryID }}">
                                    <i data-feather="edit-2" style="width:13px"></i>
                                </button>

                                {{-- Delete --}}
                                <form method="POST" action="{{ route('kitchen.categories.delete', $cat->CategoryID) }}"
                                      class="d-inline">
                                    @csrf @method('DELETE')
                                    @if($cat->menuItems()->count() > 0)
                                        <button type="button" class="btn btn-sm btn-outline-danger"
                                                onclick="Swal.fire({
                                                    title: 'Cannot Delete',
                                                    text: 'This category cannot be deleted because it is associated with items.',
                                                    icon: 'error',
                                                    confirmButtonColor: '#ff6b35',
                                                    background: '#1a1a1b',
                                                    color: '#fff'
                                                })">
                                            <i data-feather="trash-2" style="width:13px"></i>
                                        </button>
                                    @else
                                        <button type="button" class="btn btn-sm btn-outline-danger confirm-submit"
                                                data-message="Are you sure you want to delete category '{{ $cat->Name }}'?"
                                                data-icon="warning">
                                            <i data-feather="trash-2" style="width:13px"></i>
                                        </button>
                                    @endif
                                </form>
                            </td>
                        </tr>

                        {{-- Edit Modal --}}
                        <div class="modal fade" id="editCat{{ $cat->CategoryID }}" tabindex="-1">
                        <div class="modal-dialog">
                        <div class="modal-content">
                            <div class="modal-header">
                                <h5 class="modal-title">Edit Category: <em>{{ $cat->Name }}</em></h5>
                                <button type="button" class="btn-close" data-bs-dismiss="modal"></button>
                            </div>
                            <form method="POST" action="{{ route('kitchen.categories.update', $cat->CategoryID) }}">@csrf
                            <div class="modal-body">
                                <div class="mb-3">
                                    <label class="form-label">Category Name <span class="text-danger">*</span></label>
                                    <input type="text" name="CategoryName" class="form-control"
                                           value="{{ $cat->Name }}" required>
                                </div>
                                <div class="mb-3">
                                    <label class="form-label">Description</label>
                                    <textarea name="Description" class="form-control" rows="3">{{ $cat->Description }}</textarea>
                                </div>
                            </div>
                            <div class="modal-footer">
                                <button type="button" class="btn btn-secondary" data-bs-dismiss="modal">Cancel</button>
                                <button type="submit" class="btn btn-primary">Save Changes</button>
                            </div>
                            </form>
                        </div></div></div>

                        @empty
                        <tr><td colspan="5" class="text-center text-muted py-4">No categories found.</td></tr>
                        @endforelse
                        </tbody>
                    </table>
                </div>
            </div>
            @if($categories->hasPages())
            <div class="card-footer">{{ $categories->links() }}</div>
            @endif
        </div>
    </div>

</div>
</div>
@endsection
