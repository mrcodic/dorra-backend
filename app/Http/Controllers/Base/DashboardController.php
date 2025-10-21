<?php

namespace App\Http\Controllers\Base;

use App\Http\Controllers\Controller;
use App\Services\BaseService;
use Illuminate\Contracts\View\{Factory, View};
use Illuminate\Foundation\Application;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Response;


class DashboardController extends Controller
{
    private const BASE_FOLDER = 'dashboard.';
    protected $storeRequestClass;
    protected $updateRequestClass;
    protected $indexView;
    protected $createView;
    protected $editView;
    protected $showView;
    protected $assoiciatedData = [];
    protected $relationsToStore = [];
    protected $usePagination = false;
    protected $successMessage;
    protected $resourceClass;
    protected string $resourceTable;
    protected bool $mergeSharedVariables = true;

    protected array $methodRelations = [
        'index' => [],
        'show' => [],
        'edit' => [],
        'update' => [],
        'store' => [],
    ];


    public function __construct(public BaseService $service)
    {
    }

    /**
     * Display a listing of the resource.
     */
    public function index()
    {
        $data = $this->service->getAll($this->getRelations('index'), $this->usePagination);
        $associatedData = $this->getAssociatedData('index');
        if (request()->expectsJson())
        {
            dd($data);
            return Response::api(data: $this->resourceClass ? $this->resourceClass::collection($data) : $data);
        }
        return view(self::BASE_FOLDER . "$this->indexView", get_defined_vars());
    }

    protected function getRelations(string $method): array
    {
        return $this->methodRelations[$method] ?? [];
    }

    protected function getAssociatedData(string $context = 'shared'): array
    {
        $shared = $this->assoiciatedData['shared'] ?? [];
        $custom = $this->assoiciatedData[$context] ?? [];
        return $this->mergeSharedVariables ? array_merge($shared, $custom) : $custom;

    }

    /**
     * Show the form for creating a new resource.
     */
    public function create(): View|Application|Factory
    {
        $associatedData = $this->getAssociatedData('create');
        return view(self::BASE_FOLDER . "{$this->createView}", get_defined_vars());
    }

    /**
     * Store a newly created resource in storage.
     */
    public function store(Request $request)
    {
        $validatedData = $request->validate($this->storeRequestClass->rules());
        $model = $this->service->storeResource($validatedData, $this->relationsToStore, $this->getRelations('store'));
        return $this->resourceClass ? Response::api(data: $this->resourceClass::make($model))
            : Response::api(data: $model);
    }

    /**
     * Display the specified resource.
     */
    public function show(string $id)
    {
        $model = $this->service->showResource($id, $this->getRelations('show'));
        $associatedData = $this->getAssociatedData('show');
        if (request()->ajax()) {
            return Response::api(data: $model);
        }
        if (request()->expectsJson()) {
            return $this->resourceClass ? Response::api(data: $this->resourceClass::make($model))
                : Response::api(data: $model);
        }
        return view(self::BASE_FOLDER . "{$this->showView}", get_defined_vars());
    }

    /**
     * Show the form for editing the specified resource.
     */
    public function edit(string $id): View|Application|Factory
    {
        $model = $this->service->showResource($id, $this->getRelations('edit'));

        $associatedData = $this->getAssociatedData('edit');
        return view(self::BASE_FOLDER . "{$this->editView}", get_defined_vars());
    }

    /**
     * Update the specified resource in storage.
     */
    public function update(Request $request, string $id)
    {
        $validatedData = $request->validate($this->updateRequestClass->rules($id));
        $resource = $this->service->updateResource($validatedData, $id, $this->getRelations('update'));
        return $this->resourceClass ? Response::api(data: $this->resourceClass::make($resource))
            : Response::api(data: $resource);

    }

    /**
     * Remove the specified resource from storage.
     */
    public function destroy(string $id)
    {
        $model = $this->service->showResource($id);
        $this->service->deleteResource($id);
        return Response::api(data: $model);
    }

    public function bulkDelete(Request $request)
    {
        $request->validate([
            'ids' => 'required|array',
            'ids.*' => 'exists:' . $this->resourceTable . ',id'
        ]);
        $this->service->bulkDeleteResources($request->ids);
        return Response::api(data: $request->ids);

    }
}
