@extends('layouts.dashboard')
@section('title', 'Add Pet')
@section('content')
<h1 class="font-display text-4xl font-bold">Add a pet</h1>
<form method="POST" action="{{ route('owner.pets.store') }}" enctype="multipart/form-data" class="surface mt-8 max-w-2xl space-y-4 p-6">
    @csrf
    <div><label class="text-sm font-medium">Name</label><input name="name" value="{{ old('name') }}" class="field mt-1" required></div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="text-sm font-medium">Species</label><input name="species" value="{{ old('species') }}" class="field mt-1" required></div>
        <div><label class="text-sm font-medium">Breed</label><input name="breed" value="{{ old('breed') }}" class="field mt-1"></div>
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <div><label class="text-sm font-medium">Age</label><input type="number" name="age" value="{{ old('age') }}" class="field mt-1"></div>
        <div><label class="text-sm font-medium">Gender</label><input name="gender" value="{{ old('gender') }}" class="field mt-1"></div>
    </div>
    <div><label class="text-sm font-medium">Medical history</label><textarea name="medical_history" rows="4" class="field mt-1">{{ old('medical_history') }}</textarea></div>
    <div><label class="text-sm font-medium">Photo</label><input type="file" name="photo" accept="image/*" class="field mt-1"></div>
    <button class="btn-primary">Save pet</button>
</form>
@endsection
