@php
    use Illuminate\Support\Facades\Storage;
@endphp



<!DOCTYPE html>
<html lang="ar">
<head>
    <meta charset="UTF-8">
    <title>صور الشقة #{{ $apartment->id }}</title>
    <style>
        body {
            font-family: Arial, sans-serif;
        }
        .images {
            display: flex;
            gap: 15px;
            flex-wrap: wrap;
        }
        img {
            width: 250px;
            height: 180px;
            object-fit: cover;
            border-radius: 8px;
            border: 2px solid #ddd;
        }
    </style>
</head>
<body>

<h2>صور الشقة رقم {{ $apartment->id }}</h2>

<div class="images">
    @foreach ($apartment->images as $image)
        <img
            <img src="{{ Storage::disk('apartment')->url($image->path) }}">

            alt="Apartment Image"
        >
    @endforeach
</div>

</body>
</html>
