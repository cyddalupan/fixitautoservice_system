@extends('layouts.app')

@section('title', 'New Service')

@section('content')
<div class="container mx-auto p-6 max-w-2xl">
    <div class="flex items-center justify-between mb-4">
        <h1 class="text-2xl font-bold">New Service</h1>
        <a href="{{ route('admin.services.index') }}" class="text-blue-600 hover:underline">← Back</a>
    </div>

    @if ($errors->any())
        <div class="mb-4 bg-red-100 border border-red-400 text-red-700 px-4 py-3 rounded">
            <ul class="list-disc pl-5">
                @foreach ($errors->all() as $error)
                    <li>{{ $error }}</li>
                @endforeach
            </ul>
        </div>
    @endif

    <form action="{{ route('admin.services.store') }}" method="POST" class="bg-white rounded shadow p-6 space-y-4">
        @csrf
        <div>
            <label class="block text-sm font-medium text-gray-700">Name *</label>
            <input type="text" name="name" value="{{ old('name') }}" required
                class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Category</label>
            <input type="text" name="category" value="{{ old('category') }}"
                class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Default Price (₱) *</label>
            <input type="number" step="0.01" min="0" name="default_price" value="{{ old('default_price') }}" required
                class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">
        </div>
        <div>
            <label class="block text-sm font-medium text-gray-700">Description</label>
            <textarea name="description" rows="3" class="mt-1 block w-full border border-gray-300 rounded px-3 py-2">{{ old('description') }}</textarea>
        </div>
        <div class="flex items-center">
            <input type="checkbox" name="is_active" value="1" checked class="h-4 w-4 text-blue-600 rounded">
            <label class="ml-2 text-sm text-gray-700">Active</label>
        </div>
        <div class="flex items-center space-x-3">
            <button type="submit" class="px-4 py-2 bg-blue-600 text-white rounded hover:bg-blue-700">Save Service</button>
            <a href="{{ route('admin.services.index') }}" class="px-4 py-2 bg-gray-200 rounded hover:bg-gray-300">Cancel</a>
        </div>
    </form>
</div>
@endsection
