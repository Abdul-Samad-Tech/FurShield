@extends('layouts.dashboard')
@section('title', 'Add Adoptable Pet')
@section('content')
<h1 class="font-display text-4xl font-bold">List an adoptable pet</h1>
<form method="POST" action="{{ route('shelter.listings.store') }}" enctype="multipart/form-data" class="surface mt-8 max-w-2xl space-y-4 p-6">
    @csrf
    <input name="pet_name" class="field" placeholder="Pet name" required>
    <div class="grid gap-4 sm:grid-cols-2">
        <input name="species" class="field" placeholder="Species" required>
        <input name="breed" class="field" placeholder="Breed">
    </div>
    <div class="grid gap-4 sm:grid-cols-2">
        <input type="number" name="age" class="field" placeholder="Age">
        <input name="gender" class="field" placeholder="Gender">
    </div>
    <input name="health_status" class="field" placeholder="Health status">
    <textarea name="description" rows="4" class="field" placeholder="Description"></textarea>
    <input type="file" name="photo" accept="image/*" class="field">
    <button class="btn-primary">Publish listing</button>
</form>
@endsection
