<?php

namespace App\Http\Controllers\Dashboard;

use App\Enums\Mockup\TypeEnum;
use App\Http\Controllers\Base\DashboardController;


use App\Http\Resources\MockupResource;

use App\Models\Mockup;
use App\Models\Template;
use App\Repositories\Interfaces\CategoryRepositoryInterface;
use App\Repositories\Interfaces\TemplateRepositoryInterface;
use App\Repositories\Interfaces\TypeRepositoryInterface;
use App\Services\Mockup\TemplateMockupGenerator;
use App\Services\MockupService;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;
use App\Http\Requests\Mockup\{StoreMockupRequest, UpdateMockupEditorRequest, UpdateMockupRequest};


class MockupController extends DashboardController
{
    public function __construct(
        public MockupService              $mockupService,
        public CategoryRepositoryInterface $categoryRepository,
        public TypeRepositoryInterface    $typeRepository,
        public TemplateRepositoryInterface $templateRepository,

    )
    {
        parent::__construct($mockupService);
        $this->storeRequestClass = new StoreMockupRequest();
        $this->updateRequestClass = new UpdateMockupRequest();
        $this->indexView = 'mockups.index';
        $this->createView = 'mockups.create';
        $this->editView = 'mockups.edit';
        $this->usePagination = true;
        $this->resourceTable = 'mockups';
        $this->assoiciatedData = [
            'shared' => [
                'products' => $this->categoryRepository->query()->whereHasMockup(true)->get(['id', 'name']),
                'types' => $this->typeRepository->query()->get(['id', 'name']),
            ],

        ];
        $this->methodRelations = [
            'index' => ['types','products'],
        ];
        $this->resourceClass = MockupResource::class;
    }

    public function index()
    {

        $data = $this->service->getAll($this->getRelations('index'), $this->usePagination, perPage: request('per_page', 16));

        $associatedData = $this->getAssociatedData('index');

        if (request()->ajax()) {
            $cards = view('dashboard.partials.filtered-mockups', compact('data', 'associatedData'))->render();

            $pagination = '';
            if ($data instanceof \Illuminate\Pagination\LengthAwarePaginator) {
                $pagination = '<div class="mt-2 px-1">' .
                    $data->withQueryString()->links('pagination::bootstrap-5')->render() .
                    '</div>';
            }

            return Response::api(data: [
                'cards' => $cards,
                'pagination' => $pagination,
                'total' => is_countable($data) ? count($data) : $data->total(),
                'data' => MockupResource::collection($data),
            ]);
        } elseif (request()->expectsJson()) {
            return Response::api(data: MockupResource::collection($data->load('types'))->response()->getData(true));
        } else {
            return view("dashboard.mockups.index", get_defined_vars());
        }

    }

    public function mockupTypes()
    {
        return Response::api(data: TypeEnum::toArray());
    }

    public function recentMockups()
    {
        $recentMockups = $this->mockupService->recentMockups();
        return Response::api(data: MockupResource::collection($recentMockups));

    }

    public function showAndUpdateRecent($id)
    {
        $mockup = $this->mockupService->showAndUpdateRecent($id);
        return Response::api(data: MockupResource::make($mockup));

    }

    public function destroyRecentMockup($id)
    {
        $mockup = $this->mockupService->destroyRecentMockup($id);
        return Response::api(data: MockupResource::make($mockup));
    }

    public function updateEditorData(UpdateMockupEditorRequest $request, $id)
    {
        $mockup = $this->mockupService->updateEditorData($request->validated(), $id);
        return Response::api(data: MockupResource::make($mockup));

    }
    public function removeColor(Request $request)
    {
        $data = $request->validate([
            'category_id' => ['required', 'integer'],
            'template_id' => ['required', 'string'],
            'color'       => ['required', 'string'],
        ]);
        $this->mockupService->removeColor($data);
        return Response::api();
    }

    public function generateTemplateFiles(Request $request,
        Mockup $mockup, TemplateMockupGenerator $generator) {
        $colors = collect(
            $mockup->colors_across_templates ?? []
        )
            ->filter()
            ->values()
            ->all();

        if (empty($colors)) {
            return Response::api(
                message: "No colors found."
            );
        }

        $generator->generate($mockup, $colors, force: $request->boolean('force')
        );

        return Response::api(
            message: "Mockup generation started successfully"
        );
    }

    public function removeAcrossTemplateColor(
        Mockup $mockup,
        string $hex,
        TemplateMockupGenerator $generator,
        ?Template $template = null
    ) {
        $hex = strtolower(trim($hex));
        $hex = str_starts_with($hex, '#') ? $hex : '#' . $hex;

        if ($template) {
            $generator->removeDeletedColors(
                mockup: $mockup,
                removedHexes: [$hex],
                template: $template
            );

            return Response::api(
                message: 'Color removed from this template successfully.'
            );
        }

        $newColors = collect($mockup->colors_across_templates ?? [])
            ->filter()
            ->reject(fn ($color) => strtolower(trim($color)) === $hex)
            ->values()
            ->all();

        $mockup->colors_across_templates = $newColors;
        $mockup->save();

        $generator->removeDeletedColors(
            mockup: $mockup,
            removedHexes: [$hex]
        );

        return Response::api(message: 'Color removed successfully.');
    }

}
