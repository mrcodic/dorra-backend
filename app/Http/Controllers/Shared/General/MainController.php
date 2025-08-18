<?php

namespace App\Http\Controllers\Shared\General;


use App\Enums\Product\UnitEnum;
use App\Http\Controllers\Controller;
use App\Http\Requests\Dimension\StoreDimensionRequest;
use App\Http\Resources\{CategoryResource,
    CountryCodeResource,
    CountryResource,
    Design\DesignResource,
    FolderResource,
    MediaResource,
    StateResource,
    TagResource,
    TeamResource,
    Template\TypeResource};
use App\Models\CountryCode;
use App\Models\GlobalAsset;
use App\Models\Type;
use App\Repositories\Interfaces\{CountryRepositoryInterface, DimensionRepositoryInterface, StateRepositoryInterface};
use App\Services\CategoryService;
use App\Services\DesignService;
use App\Services\FolderService;
use App\Services\TagService;
use App\Services\TeamService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Log;
use Illuminate\Support\Facades\Response;
use Spatie\MediaLibrary\MediaCollections\Models\Media;


class MainController extends Controller
{
    public function __construct(
        public CountryRepositoryInterface   $countryRepository,
        public StateRepositoryInterface     $stateRepository,
        public CategoryService              $categoryService,
        public TagService                   $tagService,
        public DesignService                $designService,
        public FolderService                $folderService,
        public DimensionRepositoryInterface $dimensionRepository,
        public TeamService                   $teamService,

    )
    {
    }

    public function removeMedia(Media $media)
    {
        deleteMediaById($media->uuid);
        return Response::api();
    }

    public function countries()
    {
        return Response::api(data: CountryResource::collection($this->countryRepository->all()));
    }

    public function states()
    {
        return Response::api(data: StateResource::collection($this->stateRepository->getWithFilters()));
    }

    public function countryCodes()
    {
        return Response::api(data: CountryCodeResource::collection(CountryCode::all()));
    }

    public function subCategories()
    {
        return Response::api(data: CategoryResource::collection($this->categoryService->getSubCategories()));
    }

    public function adminCheck()
    {
        if (auth()->check()) {
            return response()->json(['message' => 'authenticated.'], 200);
        }
    }

    public function tags()
    {
        return Response::api(data: TagResource::collection($this->tagService->getAll(columns: ['id', 'name'])));

    }

    public function units()
    {
        return Response::api(data: UnitEnum::toArray());

    }

    public function templateTypes()
    {
        return Response::api(data: TypeResource::collection(Type::all(['id', 'value'])));

    }

    public function convertFabricJson(Request $request)
    {
        $jsonPath = storage_path('app/fabric-json.json');
        $tempPngPath = storage_path('app/fabric-rendered.png');
        $nodeScriptPath = base_path('fabric-renderer/renderFabric.js');

        try {
            file_put_contents($jsonPath, $request->design_data);
            $cmd = "\"C:\\Program Files\\nodejs\\node.exe\" {$nodeScriptPath} {$jsonPath} {$tempPngPath} 2>&1";

            exec($cmd, $output, $returnVar);

            if ($returnVar !== 0) {
                Log::error('Fabric render job failed', ['cmd' => $cmd, 'output' => implode("\n", $output)]);
                throw new \Exception("Failed to render PNG from Fabric JSON");
            }

            if (!file_exists($tempPngPath)) {
                Log::error('Rendered PNG file missing after node script', ['path' => $tempPngPath]);
                throw new \Exception("Rendered PNG file not found");
            }

            $asset = GlobalAsset::create([
                'title' => 'Fabric Render',
                'type' => 'design-preview'
            ]);

            $assetMedia = $asset->addMedia($tempPngPath)->toMediaCollection('fabric');

        } finally {
            if (file_exists($jsonPath)) {
                unlink($jsonPath);
            }
            if (file_exists($tempPngPath)) {
                unlink($tempPngPath);
            }
        }
        return Response::api(data: MediaResource::make($assetMedia));
    }

    public function addMedia(Request $request, $id=null)
    {
//        $global  = GlobalAsset::create(['title' => $request->title, 'type' => $request->type]);
////        $model = ($request->resource)::find($id);
        $media = handleMediaUploads($request->allFiles(),null,$request->collection_name, clearExisting: true);
        return Response::api(data: MediaResource::make($media));
    }

    public function trash()
    {
        return Response::api(data: [
            'designs' => DesignResource::collection($this->designService->trash()),
            'folders' => FolderResource::collection($this->folderService->trash()),
            'teams' => TeamResource::collection($this->teamService->trash())
        ]);

    }

    public function storeDimension(StoreDimensionRequest $request)
    {
        return Response::api(data: ['id' => uniqid()]);
    }


}
