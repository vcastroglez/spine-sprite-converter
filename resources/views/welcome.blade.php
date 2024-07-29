{{--https://oc.kinkoid.com/index.php/apps/files/?dir=/hh_pictures_content_dev/hentai/girls/1/animations/ava0&fileid=12286063--}}
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
    <br>
    <br>
    <label for="frames">
        Amount of frames:
        <input type="number" id="frames" name="frames" value="20" min="0"/>
    </label>
    <br>
    <br>
    <button type="submit">Convert</button>
</form>
