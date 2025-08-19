<?php

namespace App\Http\Controllers\Api\V1\User\Folder;

use App\Http\Controllers\Controller;

use Illuminate\Validation\Rule;
use App\Http\Requests\Folder\{StoreFolderRequest, UpdateFolderRequest};
use App\Http\Resources\FolderResource;
use App\Models\Design;
use App\Models\Folder;
use App\Services\FolderService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;


class FolderController extends Controller
{
    public function __construct(public FolderService $folderService)
    {
    }

    public function index()
    {
        $folders = $this->folderService->getUserFolders();
        return Response::api(data: FolderResource::collection($folders->load(['designs.owner', 'designs.product'])));
    }

    public function store(StoreFolderRequest $request)
    {
        $this->folderService->storeResource($request->all());
        return Response::api();
    }

    public function update(UpdateFolderRequest $request,$id)
    {
        $this->folderService->updateResource($request->validated(),$id);
        return Response::api();
    }

    public function show($id)
    {
        $folder = $this->folderService->showResource($id, ['designs','designs.product.category','designs.owner']);
        return Response::api(data: FolderResource::make($folder));
    }

    public function assignDesignsToFolder(Request $request)
    {
        $validatedData = $request->validate([
            'folder_id' => ['required', 'integer', 'exists:folders,id',],
            'designs' => ['required', 'array'],
            'designs.*' => ['nullable', 'string', 'exists:designs,id',
//                function ($attribute, $value, $fail) {
//                $design = Design::find($value);
//                if ($design && !$design->users()->pluck('id')->contains(auth('sanctum')->id())) {
//                    $fail("The selected design does not belong to you or you are not a member of this design.");
//                }
//            }
            function ($attribute, $value, $fail) use($request) {
                $design = Design::find($value);
                if ($design && !$design->folders()->pluck('id')->contains($request->folder_id)) {
                    $fail("The selected design already added to that folder.");
                }
            }
            ]
        ]);
        $this->folderService->assignDesignsToFolder($validatedData);
        return Response::api();
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'folders' => ['required', 'array'],
            'folders.*' => ['nullable', 'integer', 'exists:folders,id', function ($attribute, $value, $fail) {
                $folder = Folder::find($value);
                if ($folder && $folder->user_id != auth('sanctum')->id()) {
                    $fail("The selected Folder does not belong to you");
                }
            }]
        ]);
        $this->folderService->bulkDeleteResources($request->folders);
        return Response::api();
    }

    public function bulkForceDelete(Request $request)
    {
        $request->validate([
            'folders' => ['required', 'array'],
            'folders.*' => ['nullable', 'integer', 'exists:folders,id', function ($attribute, $value, $fail) {
                $folder = Folder::find($value);
                if ($folder && $folder->user_id != auth('sanctum')->id()) {
                    $fail("The selected Folder does not belong to you");
                }
            }]
        ]);
        $this->folderService->bulkForceDeleteResources($request->folders);
        return Response::api();
    }
    public function bulkRestore(Request $request)
    {
        $request->validate([
            'folders' => ['required', 'array'],
            'folders.*' => ['nullable', 'integer', 'exists:folders,id', function ($attribute, $value, $fail) {
                $folder = Folder::find($value);
                if ($folder && $folder->user_id != auth('sanctum')->id()) {
                    $fail("The selected Folder does not belong to you");
                }
            }]
        ]);
        $this->folderService->bulkRestore($request->folders);
        return Response::api();
    }

    public function bulkDeleteDesigns(Request $request,$id)
    {
        $validatedData = $request->validate(['designs' => ['required', 'array'],
            'designs.*' => ['required', 'string', 'exists:designs,id',
                Rule::exists('designs', 'id')->whereNull('deleted_at')],
            function ($attribute, $value, $fail) use ($id) {
                $exists = Design::whereKey($value)
                    ->whereNull('deleted_at')
                    ->whereHas('teams', function ($query) use ($id) {
                        $query->where('teams.id', $id);
                    })
                    ->exists();

                if (! $exists) {
                    $fail("The selected design ({$value}) does not belong to this team or no longer exists.");
                }
            }
        ]);
        $this->folderService->bulkDeleteDesigns($validatedData,$id);
        return Response::api();


    }
}
