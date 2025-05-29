@foreach ($data as $template)
    <div class="col-md-6 col-lg-4 col-xxl-4 custom-4-per-row">
        <div class="position-relative" style="box-shadow: 0px 4px 6px 0px #4247460F;">
            <input type="checkbox" class="form-check-input position-absolute top-0 start-0 m-1 category-checkbox" value="{{ $template->id }}" name="specifications[]">
            <div style="background-color: #F4F6F6;height:200px">
                <img src="{{ $template->getFirstMediaUrl('templates') }}" class="mx-auto d-block " style="height:100%; width: auto;" alt="Template Image">
            </div>
            <div class="card-body text-start p-2">
                <h6 class="fw-bold mb-1 text-black fs-3">{{ $template->getTranslation('name', app()->getLocale()) }}</h6>
                <p class="small mb-1">{{ $template->product->getTranslation('name', app()->getLocale()) }}</p>
                <div class="d-flex flex-wrap justify-content-start gap-1 mb-2">
                    @foreach($template->product->tags as $tag)
                        <span class="badge rounded-pill text-black p-75" style="background-color: #FCF8FC;">{{ $tag->getTranslation('name', app()->getLocale()) }}</span>
                    @endforeach
                </div>
                <div class="d-flex justify-content-between align-items-center mb-2 px-1">
                    <div class="d-flex" style="gap: 5px;">
                        <div class="rounded-circle" style="width: 15px; height: 15px; background-color: #FF5733;"></div>
                        <div class="rounded-circle" style="width: 15px; height: 15px; background-color: #33B5FF;"></div>
                        <div class="rounded-circle" style="width: 15px; height: 15px; background-color: #9B59B6;"></div>
                    </div>
                    <span class="badge text-dark p-75" style="background-color: #CED5D4">{{ $template->status->label() }}</span>
                </div>
                <hr class="my-2">
                <div class="d-flex justify-content-center gap-1">
                    <a class="btn btn-outline-secondary text-black" href="{{ config('services.editor_url').$template->id }}">Show</a>
                    <button class="btn btn-outline-secondary text-black">Edit</button>
                    <button class="btn btn-outline-danger open-delete-template-modal" data-bs-toggle="modal" data-bs-target="#deleteTemplateModal" data-id="{{ $template->id }}">Delete</button>
                </div>
            </div>
        </div>
    </div>
@endforeach
