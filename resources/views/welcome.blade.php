<h1 class="title">Upload .skel and .atlas</h1>
@if ($errors->any())
    <div class="notification is-danger is-light">
        <ul>
            @foreach ($errors->all() as $error)
                <li>{{ $error }}</li>
            @endforeach
        </ul>
    </div>
@endif

<form action="{{ route('convert') }}" method="POST" enctype="multipart/form-data">
    @csrf
    <label for="skel_upload">
        Skel file:
        <input type="file" id="skel_upload" name="skel_upload"/>
    </label>
    <br>
    <br>
    <label for="atlas_upload">
        Atlas file:
        <input type="file" id="atlas_upload" name="atlas_upload"/>
    </label>
    <br>
    <br>
    <label for="png_upload">
        PNG file:
        <input type="file" id="png_upload" name="png_upload"/>
    </label>
    <button type="submit">Convert</button>
</form>
