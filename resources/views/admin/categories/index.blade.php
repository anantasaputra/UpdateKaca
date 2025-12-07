@extends('admin.layouts.app')

@section('title', 'Categories Management')

@section('content')
<div class="page-header d-flex justify-content-between align-items-center">
    <div>
        <h1><i class="bi bi-folder me-2"></i> Categories Management</h1>
        <p class="text-muted">Manage frame categories</p>
    </div>
    <a href="{{ route('admin.categories.create') }}" class="btn btn-primary">
        <i class="bi bi-plus-circle"></i> Add New Category
    </a>
</div>

<!-- Categories List -->
<div class="card border-0 shadow-sm">
    <div class="card-body p-0">
        <div class="table-responsive">
            <table class="table table-hover align-middle mb-0">
                <thead class="bg-light">
                    <tr>
                        <th class="ps-4">ID</th>
                        <th>Name</th>
                        <th>Description</th>
                        <th class="text-center">Frames</th>
                        <th class="text-center">Status</th>
                        <th class="text-center pe-4">Actions</th>
                    </tr>
                </thead>
                <tbody>
                    @forelse($categories as $category)
                    <tr>
                        <td class="ps-4"><strong>#{{ $category->id }}</strong></td>
                        <td>
                            <div class="d-flex align-items-center">
                                <i class="bi bi-folder fs-4 text-primary me-2"></i>
                                <strong>{{ $category->name }}</strong>
                            </div>
                        </td>
                        <td>
                            <small class="text-muted">
                                {{ Str::limit($category->description, 50) ?: '-' }}
                            </small>
                        </td>
                        <td class="text-center">
                            <span class="badge bg-secondary">{{ $category->frames_count }} frames</span>
                        </td>
                        <td class="text-center">
                            @if($category->is_active)
                                <span class="badge bg-success">
                                    <i class="bi bi-check-circle"></i> Active
                                </span>
                            @else
                                <span class="badge bg-secondary">
                                    <i class="bi bi-x-circle"></i> Inactive
                                </span>
                            @endif
                        </td>
                        <td class="text-center pe-4">
                            <div class="btn-group btn-group-sm">
                                <a href="{{ route('admin.categories.edit', $category) }}" 
                                   class="btn btn-outline-primary" title="Edit">
                                    <i class="bi bi-pencil"></i>
                                </a>
                                
                                <form action="{{ route('admin.categories.toggle', $category) }}" 
                                      method="POST" class="d-inline">
                                    @csrf
                                    <button type="submit" 
                                            class="btn {{ $category->is_active ? 'btn-outline-warning' : 'btn-outline-success' }}"
                                            title="{{ $category->is_active ? 'Deactivate' : 'Activate' }}">
                                        <i class="bi bi-{{ $category->is_active ? 'x' : 'check' }}-circle"></i>
                                    </button>
                                </form>
                                
                                <form action="{{ route('admin.categories.destroy', $category) }}" 
                                      method="POST" 
                                      class="d-inline"
                                      onsubmit="return confirm('Are you sure? This category has {{ $category->frames_count }} frames.')">
                                    @csrf
                                    @method('DELETE')
                                    <button type="submit" 
                                            class="btn btn-outline-danger" 
                                            title="Delete"
                                            {{ $category->frames_count > 0 ? 'disabled' : '' }}>
                                        <i class="bi bi-trash"></i>
                                    </button>
                                </form>
                            </div>
                        </td>
                    </tr>
                    @empty
                    <tr>
                        <td colspan="6" class="text-center py-5">
                            <i class="bi bi-folder text-muted" style="font-size: 3rem;"></i>
                            <p class="text-muted mt-2 mb-0">No categories found</p>
                            <a href="{{ route('admin.categories.create') }}" class="btn btn-primary btn-sm mt-2">
                                <i class="bi bi-plus-circle"></i> Add Category
                            </a>
                        </td>
                    </tr>
                    @endforelse
                </tbody>
            </table>
        </div>
    </div>
    
    @if($categories->hasPages())
    <div class="card-footer bg-white border-top">
        {{ $categories->links() }}
    </div>
    @endif
</div>
@endsection
